<section aria-labelledby="announcements-heading">
    <div class="mb-5 flex items-center justify-between">
        <div>
            <h2 id="announcements-heading" class="text-xl font-bold text-[#0f2744] sm:text-[1.375rem]">Announcements</h2>
            <p class="mt-1 text-xs text-gray-500">Recent updates from your courses</p>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow-md">
        @forelse($recentAnnouncements as $announcement)
            <div class="flex gap-4 border-b border-gray-100 py-4 first:pt-0 last:border-b-0 last:pb-0">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#8cc63f]/15 text-[#0f2744]">
                    <i class="bi bi-megaphone-fill" aria-hidden="true"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="font-semibold text-[#0f2744]">{{ $announcement->title }}</p>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $announcement->courseOffering->course->code }}</span>
                    </div>
                    <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $announcement->body }}</p>
                    <p class="mt-2 text-xs text-gray-400">{{ $announcement->published_at?->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <div class="flex items-start gap-4 py-2">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#0f2744]/5 text-[#0f2744]">
                    <i class="bi bi-info-circle-fill" aria-hidden="true"></i>
                </div>
                <div>
                    <p class="font-semibold text-[#0f2744]">Welcome to your teaching workspace</p>
                    <p class="mt-1 text-sm text-gray-600">Manage course content, assignments, announcements, discussions, and learning analytics through the integrated LMS.</p>
                </div>
            </div>
        @endforelse
    </div>
</section>
