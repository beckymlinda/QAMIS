<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\CourseOffering;
use App\Models\Programme;
use App\Models\TimetableSlot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimetableSchedulingService
{
    public const LUNCH_START = '12:00';

    public const LUNCH_END = '13:00';

    public const DEFAULT_SESSION_MINUTES = 120;

    public const SCHEDULING_DAYS = 5;

    public function validateSlot(
        Programme $programme,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $classroomId,
        ?string $venueName,
        int $courseOfferingId,
        ?int $ignoreSlotId = null,
    ): ?string {
        $startTime = $this->normalizeTime($startTime);
        $endTime = $this->normalizeTime($endTime);

        if ($this->timesOverlap($startTime, $endTime, self::LUNCH_START, self::LUNCH_END)) {
            return 'Class sessions cannot overlap the lunch break (12:00–13:00).';
        }

        $offering = CourseOffering::with(['course', 'lecturer'])->find($courseOfferingId);
        if (! $offering || $offering->course?->programme_id !== $programme->id) {
            return 'Invalid course offering for this programme.';
        }

        $existingSlots = $this->programmeSlots($programme, $ignoreSlotId);

        foreach ($existingSlots as $existing) {
            if ((int) $existing->day_of_week !== $dayOfWeek) {
                continue;
            }

            if (! $this->timesOverlap($startTime, $endTime, $existing->start_time, $existing->end_time)) {
                continue;
            }

            if ($existing->course_offering_id === $courseOfferingId) {
                return 'This course already has a session at that time.';
            }

            if ($classroomId && $existing->classroom_id === $classroomId) {
                $room = Classroom::find($classroomId);

                return 'Room conflict: '.($room?->name ?? 'Selected room').' is already booked at that time ('.$existing->courseOffering?->course?->code.').';
            }

            if ($classroomId && $existing->classroom_id === null && $venueName && strcasecmp(trim($venueName), trim($existing->venue_name ?? '')) === 0) {
                return 'Venue conflict: that location is already booked at that time.';
            }

            if (! $classroomId && $venueName && $existing->classroom_id === null
                && strcasecmp(trim($venueName), trim($existing->venue_name ?? '')) === 0) {
                return 'Venue conflict: that location is already booked at that time.';
            }

            $existingLecturerId = $existing->courseOffering?->staff_member_id;
            if ($offering->staff_member_id && $existingLecturerId === $offering->staff_member_id) {
                $lecturerName = $offering->lecturer?->name ?? 'The lecturer';

                return "Lecturer conflict: {$lecturerName} is already teaching {$existing->courseOffering?->course?->code} at that time.";
            }
        }

        if ($classroomId) {
            $institutionConflict = TimetableSlot::query()
                ->when($ignoreSlotId, fn ($q) => $q->where('id', '!=', $ignoreSlotId))
                ->where('day_of_week', $dayOfWeek)
                ->where('classroom_id', $classroomId)
                ->whereHas('courseOffering', fn ($q) => $q->where('institution_id', $programme->institution_id))
                ->with('courseOffering.course')
                ->get()
                ->first(fn ($slot) => $this->timesOverlap($startTime, $endTime, $slot->start_time, $slot->end_time)
                    && $slot->courseOffering?->course?->programme_id !== $programme->id);

            if ($institutionConflict) {
                $room = Classroom::find($classroomId);

                return 'Room conflict: '.($room?->name ?? 'Selected room').' is used by another programme at that time.';
            }
        }

        return null;
    }

    /**
     * @return array{created: int, unscheduled: list<string>, message: string}
     */
    public function autoGenerate(
        Programme $programme,
        Collection $classrooms,
        string $dayStart,
        string $dayEnd,
        bool $replaceExisting = true,
        int $sessionMinutes = self::DEFAULT_SESSION_MINUTES,
        int $rotationPass = 0,
    ): array {
        $dayStart = $this->normalizeTime($dayStart);
        $dayEnd = $this->normalizeTime($dayEnd);

        if ($classrooms->isEmpty()) {
            return [
                'created' => 0,
                'unscheduled' => [],
                'message' => 'Add classrooms in the Venues tab before auto-generating a timetable.',
            ];
        }

        $offerings = CourseOffering::query()
            ->whereHas('course', fn ($q) => $q->where('programme_id', $programme->id))
            ->with(['course', 'lecturer', 'timetableSlots'])
            ->get()
            ->sortBy(fn (CourseOffering $o) => $o->course?->code ?? '')
            ->values();

        $offerings = $this->rotateCollection($offerings, $rotationPass);
        $classrooms = $this->rotateCollection($classrooms->values(), $rotationPass);

        if ($offerings->isEmpty()) {
            return [
                'created' => 0,
                'unscheduled' => [],
                'message' => 'No course offerings to schedule. Create offerings first.',
            ];
        }

        $windows = $this->buildDailyWindows($dayStart, $dayEnd, $sessionMinutes);
        if ($windows !== [] && $rotationPass > 0) {
            $offset = $rotationPass % count($windows);
            $windows = array_merge(array_slice($windows, $offset), array_slice($windows, 0, $offset));
        }
        if ($windows === []) {
            return [
                'created' => 0,
                'unscheduled' => $offerings->map(fn ($o) => $o->course?->code ?? 'Unknown')->all(),
                'message' => 'No valid time windows between the chosen start and end hours (lunch 12:00–13:00 is reserved).',
            ];
        }

        $created = 0;
        $unscheduled = [];
        $workingSlots = $replaceExisting
            ? collect()
            : $this->programmeSlots($programme)->values();

        DB::transaction(function () use ($programme, $offerings, $classrooms, $windows, $replaceExisting, $rotationPass, &$created, &$unscheduled, &$workingSlots): void {
            if ($replaceExisting) {
                TimetableSlot::query()
                    ->whereIn('course_offering_id', $offerings->pluck('id'))
                    ->delete();
            }

            foreach ($offerings as $index => $offering) {
                if (! $replaceExisting && $offering->timetableSlots->isNotEmpty()) {
                    continue;
                }

                $preferredDay = (($index + $rotationPass) % self::SCHEDULING_DAYS) + 1;

                $placed = $this->placeOffering(
                    $programme,
                    $offering,
                    $classrooms,
                    $windows,
                    $workingSlots,
                    $preferredDay,
                );

                if ($placed) {
                    $created++;
                } else {
                    $unscheduled[] = $offering->course?->code ?? 'Unknown course';
                }
            }
        });

        $isRegeneration = $rotationPass > 0;

        $message = $created > 0
            ? ($isRegeneration
                ? "Regenerated {$created} timetable slot".($created === 1 ? '' : 's').' with a new day and room allocation.'
                : "Auto-generated {$created} timetable slot".($created === 1 ? '' : 's').'.')
            : 'Could not schedule any courses with the available rooms and time windows.';

        if ($unscheduled !== []) {
            $message .= ' Could not place: '.implode(', ', $unscheduled).'.';
        }

        return [
            'created' => $created,
            'unscheduled' => $unscheduled,
            'message' => $message,
        ];
    }

    public function timesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        $startA = $this->normalizeTime($startA);
        $endA = $this->normalizeTime($endA);
        $startB = $this->normalizeTime($startB);
        $endB = $this->normalizeTime($endB);

        return $startA < $endB && $startB < $endA;
    }

    /**
     * @return list<array{start: string, end: string}>
     */
    public function buildDailyWindows(string $dayStart, string $dayEnd, int $sessionMinutes): array
    {
        $dayStart = $this->normalizeTime($dayStart);
        $dayEnd = $this->normalizeTime($dayEnd);
        $windows = [];

        foreach ([
            [$dayStart, self::LUNCH_START],
            [self::LUNCH_END, $dayEnd],
        ] as [$periodStart, $periodEnd]) {
            if ($periodStart >= $periodEnd) {
                continue;
            }

            $cursor = $this->toMinutes($periodStart);
            $periodEndMinutes = $this->toMinutes($periodEnd);

            while ($cursor + $sessionMinutes <= $periodEndMinutes) {
                $windows[] = [
                    'start' => $this->fromMinutes($cursor),
                    'end' => $this->fromMinutes($cursor + $sessionMinutes),
                ];
                $cursor += $sessionMinutes;
            }
        }

        return $windows;
    }

    protected function rotateCollection(Collection $items, int $rotationPass): Collection
    {
        if ($rotationPass === 0 || $items->isEmpty()) {
            return $items->values();
        }

        $offset = $rotationPass % $items->count();

        return $items->slice($offset)->concat($items->take($offset))->values();
    }

    protected function placeOffering(
        Programme $programme,
        CourseOffering $offering,
        Collection $classrooms,
        array $windows,
        Collection $workingSlots,
        int $preferredDay = 1,
    ): bool {
        $days = $this->orderedWeekdays($preferredDay, $workingSlots);

        foreach ($days as $day) {
            foreach ($windows as $window) {
                foreach ($classrooms as $classroom) {
                    $conflict = $this->validateAgainstSlots(
                        $day,
                        $window['start'],
                        $window['end'],
                        $classroom->id,
                        null,
                        $offering,
                        $workingSlots,
                    );

                    if ($conflict !== null) {
                        continue;
                    }

                    $slot = TimetableSlot::create([
                        'course_offering_id' => $offering->id,
                        'classroom_id' => $classroom->id,
                        'day_of_week' => $day,
                        'start_time' => $window['start'],
                        'end_time' => $window['end'],
                        'session_type' => 'lecture',
                    ]);

                    $slot->setRelation('courseOffering', $offering);
                    $workingSlots->push($slot);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Prefer the assigned weekday, then least-used days, then the rest of the week.
     *
     * @return list<int>
     */
    protected function orderedWeekdays(int $preferredDay, Collection $workingSlots): array
    {
        $preferredDay = max(1, min(self::SCHEDULING_DAYS, $preferredDay));

        $loadByDay = [];
        for ($day = 1; $day <= self::SCHEDULING_DAYS; $day++) {
            $loadByDay[$day] = $workingSlots->where('day_of_week', $day)->count();
        }

        $days = range(1, self::SCHEDULING_DAYS);

        usort($days, function (int $a, int $b) use ($preferredDay, $loadByDay): int {
            if ($a === $preferredDay && $b !== $preferredDay) {
                return -1;
            }

            if ($b === $preferredDay && $a !== $preferredDay) {
                return 1;
            }

            return $loadByDay[$a] <=> $loadByDay[$b] ?: $a <=> $b;
        });

        return $days;
    }

    protected function validateAgainstSlots(
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $classroomId,
        ?string $venueName,
        CourseOffering $offering,
        Collection $existingSlots,
    ): ?string {
        foreach ($existingSlots as $existing) {
            if ((int) $existing->day_of_week !== $dayOfWeek) {
                continue;
            }

            if (! $this->timesOverlap($startTime, $endTime, $existing->start_time, $existing->end_time)) {
                continue;
            }

            if ($existing->course_offering_id === $offering->id) {
                return 'duplicate offering';
            }

            if ($classroomId && $existing->classroom_id === $classroomId) {
                return 'room conflict';
            }

            $existingLecturerId = $existing->courseOffering?->staff_member_id;
            if ($offering->staff_member_id && $existingLecturerId === $offering->staff_member_id) {
                return 'lecturer conflict';
            }
        }

        return null;
    }

    protected function programmeSlots(Programme $programme, ?int $ignoreSlotId = null): Collection
    {
        return TimetableSlot::query()
            ->when($ignoreSlotId, fn ($q) => $q->where('id', '!=', $ignoreSlotId))
            ->whereHas('courseOffering.course', fn ($q) => $q->where('programme_id', $programme->id))
            ->with(['courseOffering.course', 'courseOffering.lecturer'])
            ->get();
    }

    protected function normalizeTime(string $time): string
    {
        return substr($time, 0, 5);
    }

    protected function toMinutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $this->normalizeTime($time)));

        return ($hours * 60) + $minutes;
    }

    protected function fromMinutes(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
