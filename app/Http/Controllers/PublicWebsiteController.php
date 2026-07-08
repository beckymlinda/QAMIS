<?php

namespace App\Http\Controllers;

use App\Models\InstitutionWebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicWebsiteController extends Controller
{
    protected function settings(string $slug): InstitutionWebsiteSetting
    {
        $settings = InstitutionWebsiteSetting::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->with('institution.programmes.orgUnit')
            ->firstOrFail();

        return $settings;
    }

    public function home(string $slug): View
    {
        $website = $this->settings($slug);

        return view('website.home', compact('website'));
    }

    public function about(string $slug): View
    {
        $website = $this->settings($slug);

        return view('website.about', compact('website'));
    }

    public function programs(string $slug): View
    {
        $website = $this->settings($slug);
        $programmes = $website->institution->programmes;

        return view('website.programs', compact('website', 'programmes'));
    }

    public function applications(string $slug): View
    {
        $website = $this->settings($slug);
        $programmes = $website->institution->programmes->filter(fn ($p) => $p->isOpenForApplications());

        return view('website.applications', compact('website', 'programmes'));
    }

    public function portal(string $slug): View
    {
        $website = $this->settings($slug);

        return view('website.portal', compact('website'));
    }

    public function preview(Request $request, InstitutionWebsiteSetting $website): View
    {
        abort_unless(auth()->check(), 403);
        abort_unless(
            auth()->user()->institution_id === $website->institution_id
            && auth()->user()->can('update', $website->institution),
            403
        );

        $website->load('institution.programmes.orgUnit');
        $preview = true;

        return view('website.home', compact('website', 'preview'));
    }
}
