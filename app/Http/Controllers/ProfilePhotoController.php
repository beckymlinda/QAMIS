<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilePhotoController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $user = $request->user();
        $user->deleteProfilePhoto();

        $path = $request->file('photo')->store('profile-photos/'.$user->id, 'public');
        $user->update(['profile_photo_path' => $path]);

        return back()->with('success', 'Profile photo updated.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->deleteProfilePhoto();

        return back()->with('success', 'Profile photo removed.');
    }
}
