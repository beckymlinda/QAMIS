<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\InstitutionWebsiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InstitutionWebsiteSettingsController extends Controller
{
    public function edit(Institution $institution): View
    {
        $this->authorize('update', $institution);

        $settings = InstitutionWebsiteSetting::forInstitution($institution);
        $settings->load('institution');

        return view('settings.website.edit', compact('institution', 'settings'));
    }

    public function update(Request $request, Institution $institution): RedirectResponse
    {
        $this->authorize('update', $institution);

        $settings = InstitutionWebsiteSetting::forInstitution($institution);

        $validated = $request->validate([
            'slug' => 'required|string|max:80|alpha_dash|unique:institution_website_settings,slug,'.$settings->id,
            'school_name' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'hero_features' => 'nullable|array',
            'hero_features.*' => 'nullable|string|max:255',
            'about_content' => 'nullable|string',
            'team_members' => 'nullable|array',
            'team_members.*.name' => 'nullable|string|max:255',
            'team_members.*.role' => 'nullable|string|max:255',
            'team_members.*.photo' => 'nullable|image|max:2048',
            'team_members.*.existing_photo' => 'nullable|string',
            'remove_team_members' => 'nullable|array',
            'remove_team_members.*' => 'integer',
            'programs_intro' => 'nullable|string',
            'application_intro' => 'nullable|string',
            'application_payment_instructions' => 'nullable|string',
            'application_requirements' => 'nullable|string',
            'application_upload_max_mb' => 'nullable|integer|min:1|max:50',
            'footer_address' => 'nullable|string|max:500',
            'footer_phone' => 'nullable|string|max:50',
            'footer_email' => 'nullable|email|max:255',
            'footer_extra' => 'nullable|string',
            'primary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo' => 'nullable|image|max:2048',
            'slider_images' => 'nullable',
            'slider_images.*' => 'nullable|image|max:4096',
            'remove_slider' => 'nullable|array',
            'remove_slider.*' => 'integer',
        ]);

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store(
                'institutions/'.$institution->id.'/website',
                'public'
            );
        }

        $sliderPaths = $settings->slider_images ?? [];

        if ($request->filled('remove_slider')) {
            foreach ($request->input('remove_slider', []) as $index) {
                $index = (int) $index;
                if (isset($sliderPaths[$index])) {
                    Storage::disk('public')->delete($sliderPaths[$index]);
                    unset($sliderPaths[$index]);
                }
            }
            $sliderPaths = array_values($sliderPaths);
        }

        if ($request->hasFile('slider_images')) {
            $uploads = $request->file('slider_images');
            $uploads = is_array($uploads) ? $uploads : [$uploads];

            foreach ($uploads as $file) {
                if (count($sliderPaths) >= 6) {
                    break;
                }

                if ($file && $file->isValid()) {
                    $sliderPaths[] = $file->store('institutions/'.$institution->id.'/website/slider', 'public');
                }
            }
        }

        $teamMembers = $this->syncTeamMembers($request, $institution, $settings->team_members ?? []);

        $settings->update([
            'slug' => $validated['slug'],
            'school_name' => $validated['school_name'] ?? null,
            'tagline' => $validated['tagline'] ?? null,
            'hero_description' => $validated['hero_description'] ?? null,
            'hero_features' => array_values(array_filter($validated['hero_features'] ?? [])),
            'about_content' => $validated['about_content'] ?? null,
            'team_members' => $teamMembers,
            'programs_intro' => $validated['programs_intro'] ?? null,
            'application_intro' => $validated['application_intro'] ?? null,
            'application_payment_instructions' => $validated['application_payment_instructions'] ?? null,
            'application_requirements' => $validated['application_requirements'] ?? null,
            'application_upload_max_mb' => $validated['application_upload_max_mb'] ?? 10,
            'footer_address' => $validated['footer_address'] ?? null,
            'footer_phone' => $validated['footer_phone'] ?? null,
            'footer_email' => $validated['footer_email'] ?? null,
            'footer_extra' => $validated['footer_extra'] ?? null,
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'logo_path' => $validated['logo_path'] ?? $settings->logo_path,
            'slider_images' => $sliderPaths,
        ]);

        return redirect()
            ->route('settings.website.edit', $institution)
            ->with('success', 'Website settings saved.');
    }

    public function togglePublish(Institution $institution): RedirectResponse
    {
        $this->authorize('update', $institution);

        $settings = InstitutionWebsiteSetting::forInstitution($institution);
        $settings->update(['is_published' => ! $settings->is_published]);

        return redirect()
            ->route('settings.website.edit', $institution)
            ->with('success', $settings->is_published
                ? 'Your website is now published and visible to the public.'
                : 'Your website has been unpublished.');
    }

    public function destroyLogo(Institution $institution): RedirectResponse
    {
        $this->authorize('update', $institution);

        $settings = InstitutionWebsiteSetting::forInstitution($institution);

        if ($settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->update(['logo_path' => null]);
        }

        return back()->with('success', 'Logo removed.');
    }

    /** @param  array<int, array{name?: string, role?: string, photo?: string}>  $existing */
    protected function syncTeamMembers(Request $request, Institution $institution, array $existing): array
    {
        $removeIndices = array_map('intval', $request->input('remove_team_members', []));
        $input = $request->input('team_members', []);
        $members = [];

        foreach ($input as $index => $memberData) {
            if (in_array((int) $index, $removeIndices, true)) {
                $photoPath = $memberData['existing_photo'] ?? ($existing[$index]['photo'] ?? null);
                if ($photoPath) {
                    Storage::disk('public')->delete($photoPath);
                }

                continue;
            }

            $photoPath = $memberData['existing_photo'] ?? null;
            $uploaded = $request->file("team_members.{$index}.photo");

            if ($uploaded && $uploaded->isValid()) {
                if ($photoPath) {
                    Storage::disk('public')->delete($photoPath);
                }
                $photoPath = $uploaded->store('institutions/'.$institution->id.'/website/team', 'public');
            }

            $name = trim($memberData['name'] ?? '');
            $role = trim($memberData['role'] ?? '');

            if ($name === '' && $role === '' && ! $photoPath) {
                continue;
            }

            $members[] = [
                'name' => $name,
                'role' => $role,
                'photo' => $photoPath,
            ];
        }

        return $members;
    }
}
