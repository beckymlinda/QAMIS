<section aria-labelledby="analytics-heading">
    <div class="mb-5">
        <h2 id="analytics-heading" class="text-xl font-bold text-[#0f2744] sm:text-[1.375rem]">Analytics Overview</h2>
        <p class="mt-1 text-xs text-gray-500">Visual summary placeholders — derived from current course data</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl bg-white p-6 shadow-md">
            <div class="flex items-center gap-2">
                <i class="bi bi-person-check-fill text-[#8cc63f]" aria-hidden="true"></i>
                <h3 class="text-sm font-semibold text-[#0f2744]">Attendance</h3>
            </div>
            <p class="mt-4 text-[2.125rem] font-bold text-[#0f2744]">{{ $attendanceRate }}%</p>
            <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-gray-100" role="progressbar" aria-valuenow="{{ $attendanceRate }}" aria-valuemin="0" aria-valuemax="100">
                <div class="lecturer-progress-fill h-full rounded-full bg-gradient-to-r from-[#0f2744] to-[#1a3a5c]" style="width: {{ $attendanceRate }}%"></div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Estimated session engagement</p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-md">
            <div class="flex items-center gap-2">
                <i class="bi bi-graph-up-arrow text-[#8cc63f]" aria-hidden="true"></i>
                <h3 class="text-sm font-semibold text-[#0f2744]">Course Completion</h3>
            </div>
            <p class="mt-4 text-[2.125rem] font-bold text-[#0f2744]">{{ $completionRate }}%</p>
            <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-gray-100" role="progressbar" aria-valuenow="{{ $completionRate }}" aria-valuemin="0" aria-valuemax="100">
                <div class="lecturer-progress-fill h-full rounded-full bg-gradient-to-r from-[#8cc63f] to-[#6fa832]" style="width: {{ $completionRate }}%"></div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Students with recorded grades</p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-md">
            <div class="flex items-center gap-2">
                <i class="bi bi-pie-chart-fill text-[#8cc63f]" aria-hidden="true"></i>
                <h3 class="text-sm font-semibold text-[#0f2744]">Grade Distribution</h3>
            </div>
            <div class="mt-5 space-y-3">
                @foreach([
                    ['label' => 'Distinction (75%+)', 'count' => $gradeDistribution['distinction'], 'color' => 'bg-[#0f2744]'],
                    ['label' => 'Credit (65–74%)', 'count' => $gradeDistribution['credit'], 'color' => 'bg-[#8cc63f]'],
                    ['label' => 'Pass (50–64%)', 'count' => $gradeDistribution['pass'], 'color' => 'bg-blue-400'],
                    ['label' => 'Below 50%', 'count' => $gradeDistribution['fail'], 'color' => 'bg-amber-400'],
                ] as $band)
                    @php $pct = (int) round(($band['count'] / $gradeDistributionTotal) * 100); @endphp
                    <div>
                        <div class="mb-1 flex justify-between text-xs">
                            <span class="text-gray-600">{{ $band['label'] }}</span>
                            <span class="font-medium text-[#0f2744]">{{ $band['count'] }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                            <div class="lecturer-progress-fill h-full rounded-full {{ $band['color'] }}" style="width: {{ max($pct, $band['count'] > 0 ? 8 : 0) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
