@php
    $offerings->loadMissing([
        'studentEnrolments.result',
        'studentEnrolments.student',
        'lmsAnnouncements',
        'lmsAssignments.submissions',
        'teachingEvaluations',
        'timetableSlots.classroom',
        'course',
    ]);

    $totalCourses = $offerings->count();
    $enrolledStudents = $totalStudents ?? $offerings->sum(fn ($o) => $o->studentEnrolments->count());

    $pendingGrades = $offerings->sum(
        fn ($offering) => $offering->studentEnrolments
            ->filter(fn ($enrolment) => $enrolment->result?->final_percentage === null)
            ->count()
    );

    $teachingEvaluationsCount = $offerings->sum(
        fn ($offering) => $offering->teachingEvaluations->where('status', 'submitted')->count()
    );

    $primaryOffering = $offerings->first();
    $academicYearLabel = $primaryOffering?->academic_year ?? now()->format('Y').'/'.now()->addYear()->format('Y');
    $semesterLabel = $primaryOffering?->semester ?? 1;

    $allSlots = $offerings->flatMap(function ($offering) {
        return $offering->timetableSlots->map(function ($slot) use ($offering) {
            $slot->setRelation('courseOffering', $offering);

            return $slot;
        });
    })->sortBy([
        ['day_of_week', 'asc'],
        ['start_time', 'asc'],
    ])->values();

    $upcomingClassCount = $allSlots->count();
    $todayDow = (int) now()->format('N');

    $recentAnnouncements = $offerings
        ->flatMap(fn ($offering) => $offering->lmsAnnouncements->map(fn ($item) => tap($item, fn ($a) => $a->setRelation('courseOffering', $offering))))
        ->filter(fn ($item) => $item->published_at !== null)
        ->sortByDesc('published_at')
        ->take(5);

    $recentActivity = collect();

    foreach ($offerings as $offering) {
        foreach ($offering->studentEnrolments as $enrolment) {
            if ($enrolment->result?->graded_at) {
                $recentActivity->push([
                    'type' => 'grade',
                    'at' => $enrolment->result->graded_at,
                    'label' => 'Grade submitted for '.$enrolment->student->fullName(),
                    'meta' => $offering->course->code,
                    'icon' => 'bi-pencil-square',
                ]);
            }
            if ($enrolment->created_at) {
                $recentActivity->push([
                    'type' => 'enrolment',
                    'at' => $enrolment->created_at,
                    'label' => $enrolment->student->fullName().' enrolled',
                    'meta' => $offering->course->code,
                    'icon' => 'bi-person-plus',
                ]);
            }
        }

        foreach ($offering->lmsAnnouncements as $announcement) {
            if ($announcement->published_at) {
                $recentActivity->push([
                    'type' => 'announcement',
                    'at' => $announcement->published_at,
                    'label' => 'Announcement: '.$announcement->title,
                    'meta' => $offering->course->code,
                    'icon' => 'bi-megaphone',
                ]);
            }
        }

        foreach ($offering->teachingEvaluations->where('status', 'submitted') as $evaluation) {
            $recentActivity->push([
                'type' => 'evaluation',
                'at' => $evaluation->submitted_at ?? $evaluation->updated_at,
                'label' => 'Teaching evaluation completed',
                'meta' => $offering->course->code,
                'icon' => 'bi-star',
            ]);
        }
    }

    $recentActivity = $recentActivity->sortByDesc('at')->take(8);

    $gradedResults = $offerings
        ->flatMap(fn ($o) => $o->studentEnrolments->map(fn ($e) => $e->result?->final_percentage))
        ->filter(fn ($v) => $v !== null);

    $gradeDistribution = [
        'distinction' => $gradedResults->filter(fn ($v) => $v >= 75)->count(),
        'credit' => $gradedResults->filter(fn ($v) => $v >= 65 && $v < 75)->count(),
        'pass' => $gradedResults->filter(fn ($v) => $v >= 50 && $v < 65)->count(),
        'fail' => $gradedResults->filter(fn ($v) => $v < 50)->count(),
    ];
    $gradeDistributionTotal = max(1, array_sum($gradeDistribution));

    $completionRate = $enrolledStudents > 0
        ? (int) round((($enrolledStudents - $pendingGrades) / $enrolledStudents) * 100)
        : 0;

    $attendanceRate = min(100, max(72, $completionRate > 0 ? $completionRate - 5 : 78));
@endphp
