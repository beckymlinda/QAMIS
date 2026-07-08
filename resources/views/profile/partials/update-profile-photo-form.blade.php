<section>
    <header>
        <h2 class="text-lg font-medium text-[#0f2744]">Profile photo</h2>
        <p class="mt-1 text-sm text-gray-600">This photo appears in the top navigation bar and discussion forums.</p>
    </header>

    <div class="mt-6 flex flex-col items-start gap-6 sm:flex-row sm:items-center">
        <x-user-avatar :user="$user" size="xl" class="shadow-md" />

        <div class="flex-1 space-y-4">
            <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label for="photo" class="block text-sm font-medium text-gray-700">Upload a new photo</label>
                    <input
                        id="photo"
                        name="photo"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#0f2744] file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#1a3a5c]"
                    >
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG or WebP · Max 2 MB</p>
                    @error('photo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-[#8cc63f] px-4 py-2 text-sm font-semibold text-[#0f2744] hover:bg-[#7ab535]">
                    <i class="bi bi-upload" aria-hidden="true"></i> Save photo
                </button>
            </form>

            @if($user->hasProfilePhoto())
                <form method="POST" action="{{ route('profile.photo.destroy') }}" onsubmit="return confirm('Remove your profile photo?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700">Remove photo</button>
                </form>
            @endif
        </div>
    </div>
</section>
