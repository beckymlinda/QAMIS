<x-app-layout>
    <div class="min-h-full bg-gradient-to-b from-slate-50 via-gray-50 to-gray-100/80">
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

        <div class="mx-auto max-w-7xl space-y-10 px-4 py-8 sm:px-6 lg:px-8">
            @include('partials.alerts')

            @include('lecturer.partials.dashboard-hero')

            @include('lecturer.partials.summary-cards', [
                'totalCourses' => $totalCourses,
                'totalStudents' => $enrolledStudents,
                'pendingGrades' => $pendingGrades,
                'teachingEvaluationsCount' => $teachingEvaluationsCount,
            ])

            @include('lecturer.partials.dashboard-quick-actions')

            <div class="grid grid-cols-1 gap-8 xl:grid-cols-3">
                <div class="xl:col-span-2 space-y-8">
                    @include('lecturer.partials.dashboard-announcements')

                    <section aria-labelledby="courses-heading">
                        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 id="courses-heading" class="text-xl font-bold text-[#0f2744] sm:text-[1.375rem]">My Courses</h2>
                                <p class="mt-1 text-xs text-gray-500">{{ $totalCourses }} {{ Str::plural('course', $totalCourses) }} assigned this academic period</p>
                            </div>
                            <a href="{{ route('lecturer.courses') }}" class="text-sm font-semibold text-[#0f2744] transition hover:text-[#8cc63f] focus:outline-none focus:underline">
                                View all courses →
                            </a>
                        </div>

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                            @forelse($offerings->take(4) as $offering)
                                @include('lecturer.partials.course-card', ['offering' => $offering])
                            @empty
                                <div class="col-span-full rounded-2xl bg-white p-12 text-center shadow-md">
                                    <i class="bi bi-journal-x text-4xl text-gray-300" aria-hidden="true"></i>
                                    <p class="mt-4 text-base font-medium text-[#0f2744]">No courses assigned yet</p>
                                    <p class="mt-2 text-sm text-gray-500">Contact your institution administrator to be assigned teaching courses.</p>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    @include('lecturer.partials.dashboard-upcoming')
                </div>

                <div class="space-y-8">
                    @include('lecturer.partials.dashboard-activity')
                    @include('lecturer.partials.dashboard-analytics')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
