<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CorrectiveActionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\ExternalEvaluatorController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\InstitutionProfileController;
use App\Http\Controllers\OrgUnitController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::get('/evaluator/accept/{token}', [ExternalEvaluatorController::class, 'accept'])->name('evaluator.accept');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/search', SearchController::class)->name('search');

    Route::post('/institutions/select', [InstitutionController::class, 'select'])->name('institutions.select');
    Route::resource('institutions', InstitutionController::class)->except(['destroy']);
    Route::get('institutions/{institution}/profile', [InstitutionProfileController::class, 'edit'])->name('institutions.profile.edit');
    Route::put('institutions/{institution}/profile', [InstitutionProfileController::class, 'update'])->name('institutions.profile.update');

    Route::resource('org-units', OrgUnitController::class)->only(['index', 'create', 'store']);
    Route::resource('programmes', ProgrammeController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('assessments', AssessmentController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('assessments/{assessment}/score', [AssessmentController::class, 'score'])->name('assessments.score');
    Route::post('assessments/{assessment}/transition', [AssessmentController::class, 'transition'])->name('assessments.transition');

    Route::resource('evidence', EvidenceController::class)->only(['index', 'create', 'store']);

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('reports/{report}/download/{format}', [ReportController::class, 'download'])->name('reports.download');
    Route::post('reports/sar', [ReportController::class, 'generateSar'])->name('reports.sar');
    Route::post('reports/annual', [ReportController::class, 'generateAnnual'])->name('reports.annual');

    Route::get('corrective-actions', [CorrectiveActionController::class, 'index'])->name('corrective-actions.index');
    Route::post('corrective-actions', [CorrectiveActionController::class, 'store'])->name('corrective-actions.store');
    Route::patch('corrective-actions/{correctiveAction}', [CorrectiveActionController::class, 'update'])->name('corrective-actions.update');

    Route::post('evaluator/invite', [ExternalEvaluatorController::class, 'invite'])->name('evaluator.invite');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
