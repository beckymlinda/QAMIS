<?php

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\CorrectiveActionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EvidenceController;
use App\Http\Controllers\ExternalEvaluatorController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\InstitutionProfileController;
use App\Http\Controllers\InstitutionReportDataController;
use App\Http\Controllers\OrgUnitController;
use App\Http\Controllers\ProgrammeController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StudentManagementController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return view('welcome');
    }

    return redirect(auth()->user()->homeRoute());
})->name('welcome');

Route::get('/evaluator/accept/{token}', [ExternalEvaluatorController::class, 'accept'])->name('evaluator.accept');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/search', SearchController::class)->name('search');

    Route::post('/institutions/select', [InstitutionController::class, 'select'])->name('institutions.select');
    Route::resource('institutions', InstitutionController::class)->except(['destroy']);
    Route::get('institutions/{institution}/profile', [InstitutionProfileController::class, 'edit'])->name('institutions.profile.edit');
    Route::put('institutions/{institution}/profile', [InstitutionProfileController::class, 'update'])->name('institutions.profile.update');

    Route::get('institutions/{institution}/report-data', [InstitutionReportDataController::class, 'index'])->name('institutions.report-data.index');
    Route::put('institutions/{institution}/report-data', [InstitutionReportDataController::class, 'update'])->name('institutions.report-data.update');
    Route::post('institutions/{institution}/report-data/governance-members', [InstitutionReportDataController::class, 'storeGovernanceMember'])->name('institutions.report-data.governance.store');
    Route::delete('institutions/{institution}/report-data/governance-members/{governanceMember}', [InstitutionReportDataController::class, 'destroyGovernanceMember'])->name('institutions.report-data.governance.destroy');
    Route::post('institutions/{institution}/report-data/staff-members', [InstitutionReportDataController::class, 'storeStaffMember'])->name('institutions.report-data.staff.store');
    Route::delete('institutions/{institution}/report-data/staff-members/{staffMember}', [InstitutionReportDataController::class, 'destroyStaffMember'])->name('institutions.report-data.staff.destroy');
    Route::post('institutions/{institution}/report-data/student-enrolments', [InstitutionReportDataController::class, 'storeStudentEnrolment'])->name('institutions.report-data.students.store');
    Route::delete('institutions/{institution}/report-data/student-enrolments/{studentEnrolment}', [InstitutionReportDataController::class, 'destroyStudentEnrolment'])->name('institutions.report-data.students.destroy');

    Route::get('institution-data', function () {
        $institutionId = auth()->user()?->institution_id;
        abort_unless($institutionId, 403);

        return redirect()->route('institutions.report-data.index', $institutionId);
    })->name('institution-data');

    Route::resource('org-units', OrgUnitController::class)->only(['index', 'create', 'store']);
    Route::resource('programmes', ProgrammeController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);

    Route::get('programmes/{programme}/academic', [\App\Http\Controllers\ProgrammeAcademicController::class, 'index'])->name('programmes.academic.index');
    Route::post('programmes/{programme}/courses', [\App\Http\Controllers\ProgrammeAcademicController::class, 'storeCourse'])->name('programmes.courses.store');
    Route::put('programmes/{programme}/courses/{course}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'updateCourse'])->name('programmes.courses.update');
    Route::delete('programmes/{programme}/courses/{course}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'destroyCourse'])->name('programmes.courses.destroy');
    Route::post('programmes/{programme}/offerings', [\App\Http\Controllers\ProgrammeAcademicController::class, 'storeOffering'])->name('programmes.offerings.store');
    Route::put('programmes/{programme}/offerings/{offering}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'updateOffering'])->name('programmes.offerings.update');
    Route::delete('programmes/{programme}/offerings/{offering}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'destroyOffering'])->name('programmes.offerings.destroy');
    Route::post('programmes/{programme}/students', [\App\Http\Controllers\ProgrammeAcademicController::class, 'storeStudent'])->name('programmes.students.store');
    Route::put('programmes/{programme}/students/{student}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'updateStudent'])->name('programmes.students.update');
    Route::delete('programmes/{programme}/students/{student}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'destroyStudent'])->name('programmes.students.destroy');
    Route::post('programmes/{programme}/timetable-slots', [\App\Http\Controllers\ProgrammeAcademicController::class, 'storeTimetableSlot'])->name('programmes.timetable-slots.store');
    Route::post('programmes/{programme}/timetable/auto-generate', [\App\Http\Controllers\ProgrammeAcademicController::class, 'autoGenerateTimetable'])->name('programmes.timetable.auto-generate');
    Route::put('programmes/{programme}/timetable-slots/{slot}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'updateTimetableSlot'])->name('programmes.timetable-slots.update');
    Route::delete('programmes/{programme}/timetable-slots/{slot}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'destroyTimetableSlot'])->name('programmes.timetable-slots.destroy');
    Route::post('programmes/{programme}/evaluation-periods', [\App\Http\Controllers\ProgrammeAcademicController::class, 'storeEvaluationPeriod'])->name('programmes.evaluation-periods.store');
    Route::get('programmes/{programme}/evaluation-periods/{period}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'showEvaluationPeriod'])->name('programmes.evaluation-periods.show');
    Route::get('programmes/{programme}/evaluation-periods/{period}/report', [\App\Http\Controllers\ProgrammeAcademicController::class, 'downloadEvaluationReport'])->name('programmes.evaluation-periods.report');
    Route::post('programmes/{programme}/classrooms', [\App\Http\Controllers\ProgrammeAcademicController::class, 'storeClassroom'])->name('programmes.classrooms.store');
    Route::put('programmes/{programme}/classrooms/{classroom}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'updateClassroom'])->name('programmes.classrooms.update');
    Route::post('programmes/{programme}/lecturers', [\App\Http\Controllers\ProgrammeAcademicController::class, 'storeLecturer'])->name('programmes.lecturers.store');
    Route::put('programmes/{programme}/lecturers/{staffMember}', [\App\Http\Controllers\ProgrammeAcademicController::class, 'updateLecturer'])->name('programmes.lecturers.update');

    Route::middleware('role:lecturer')->prefix('lecturer')->name('lecturer.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\LecturerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [\App\Http\Controllers\LecturerPortalController::class, 'profile'])->name('profile');
        Route::get('/courses', [\App\Http\Controllers\LecturerPortalController::class, 'courses'])->name('courses');
        Route::get('/timetable', [\App\Http\Controllers\LecturerPortalController::class, 'timetable'])->name('timetable');
        Route::get('/evaluations', [\App\Http\Controllers\LecturerPortalController::class, 'evaluations'])->name('evaluations');
        Route::get('/offerings/{offering}/students', [\App\Http\Controllers\LecturerPortalController::class, 'students'])->name('offerings.students');
        Route::get('/offerings/{offering}/grade', [\App\Http\Controllers\LecturerPortalController::class, 'gradeForm'])->name('offerings.grade');
        Route::post('/offerings/{offering}/grade', [\App\Http\Controllers\LecturerPortalController::class, 'storeGrades'])->name('offerings.grade.store');

        Route::prefix('offerings/{offering}/lms')->name('lms.')->group(function () {
            Route::get('/', [\App\Http\Controllers\LecturerLmsController::class, 'show'])->name('show');
            Route::get('/outline', [\App\Http\Controllers\LecturerLmsController::class, 'outline'])->name('outline');
            Route::put('/outline', [\App\Http\Controllers\LecturerLmsController::class, 'updateOutline'])->name('outline.update');
            Route::post('/outline/items', [\App\Http\Controllers\LecturerLmsController::class, 'storeOutlineItem'])->name('outline.items.store');
            Route::put('/outline/items/{item}', [\App\Http\Controllers\LecturerLmsController::class, 'updateOutlineItem'])->name('outline.items.update');
            Route::delete('/outline/items/{item}', [\App\Http\Controllers\LecturerLmsController::class, 'destroyOutlineItem'])->name('outline.items.destroy');
            Route::get('/outline/items/{item}/download', [\App\Http\Controllers\LecturerLmsController::class, 'downloadOutlineItem'])->name('outline.items.download');
            Route::get('/content', [\App\Http\Controllers\LecturerLmsController::class, 'content'])->name('content');
            Route::post('/modules', [\App\Http\Controllers\LecturerLmsController::class, 'storeModule'])->name('modules.store');
            Route::put('/modules/{module}', [\App\Http\Controllers\LecturerLmsController::class, 'updateModule'])->name('modules.update');
            Route::delete('/modules/{module}', [\App\Http\Controllers\LecturerLmsController::class, 'destroyModule'])->name('modules.destroy');
            Route::post('/modules/{module}/materials', [\App\Http\Controllers\LecturerLmsController::class, 'storeMaterial'])->name('materials.store');
            Route::delete('/materials/{material}', [\App\Http\Controllers\LecturerLmsController::class, 'destroyMaterial'])->name('materials.destroy');
            Route::get('/materials/{material}/download', [\App\Http\Controllers\LecturerLmsController::class, 'downloadMaterial'])->name('materials.download');
            Route::get('/announcements', [\App\Http\Controllers\LecturerLmsController::class, 'announcements'])->name('announcements');
            Route::post('/announcements', [\App\Http\Controllers\LecturerLmsController::class, 'storeAnnouncement'])->name('announcements.store');
            Route::post('/announcements/{announcement}/publish', [\App\Http\Controllers\LecturerLmsController::class, 'publishAnnouncement'])->name('announcements.publish');
            Route::delete('/announcements/{announcement}', [\App\Http\Controllers\LecturerLmsController::class, 'destroyAnnouncement'])->name('announcements.destroy');
            Route::get('/assignments', [\App\Http\Controllers\LecturerLmsController::class, 'assignments'])->name('assignments');
            Route::post('/assignments', [\App\Http\Controllers\LecturerLmsController::class, 'storeAssignment'])->name('assignments.store');
            Route::put('/assignments/{assignment}', [\App\Http\Controllers\LecturerLmsController::class, 'updateAssignment'])->name('assignments.update');
            Route::delete('/assignments/{assignment}', [\App\Http\Controllers\LecturerLmsController::class, 'destroyAssignment'])->name('assignments.destroy');
            Route::get('/assignments/{assignment}/attachment', [\App\Http\Controllers\LecturerLmsController::class, 'downloadAssignmentAttachment'])->name('assignments.attachment');
            Route::get('/assignments/{assignment}/submissions', [\App\Http\Controllers\LecturerLmsController::class, 'submissions'])->name('assignments.submissions');
            Route::get('/submissions/{submission}', [\App\Http\Controllers\LecturerLmsController::class, 'showSubmission'])->name('submissions.show');
            Route::get('/submissions/{submission}/file', [\App\Http\Controllers\LecturerLmsController::class, 'downloadSubmissionFile'])->name('submissions.file');
            Route::get('/submissions/{submission}/preview', [\App\Http\Controllers\LecturerLmsController::class, 'previewSubmissionFile'])->name('submissions.preview');
            Route::get('/submissions/{submission}/marked', [\App\Http\Controllers\LecturerLmsController::class, 'downloadMarkedSubmission'])->name('submissions.marked');
            Route::post('/submissions/{submission}/grade', [\App\Http\Controllers\LecturerLmsController::class, 'gradeSubmission'])->name('submissions.grade');
            Route::get('/discussions', [\App\Http\Controllers\LecturerLmsController::class, 'discussions'])->name('discussions');
            Route::post('/discussions', [\App\Http\Controllers\LecturerLmsController::class, 'storeDiscussion'])->name('discussions.store');
            Route::get('/discussions/{discussion}', [\App\Http\Controllers\LecturerLmsController::class, 'showDiscussion'])->name('discussions.show');
            Route::post('/discussions/{discussion}/posts', [\App\Http\Controllers\LecturerLmsController::class, 'storeDiscussionPost'])->name('discussions.posts.store');
            Route::get('/analytics', [\App\Http\Controllers\LecturerLmsController::class, 'analytics'])->name('analytics');
        });
    });

    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\StudentPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [\App\Http\Controllers\StudentPortalController::class, 'profile'])->name('profile');
        Route::get('/timetable', [\App\Http\Controllers\StudentPortalController::class, 'timetable'])->name('timetable');
        Route::get('/courses', [\App\Http\Controllers\StudentPortalController::class, 'courses'])->name('courses');
        Route::post('/courses/register', [\App\Http\Controllers\StudentPortalController::class, 'registerCourse'])->name('courses.register');
        Route::delete('/courses/{offering}', [\App\Http\Controllers\StudentPortalController::class, 'dropCourse'])->name('courses.drop');
        Route::get('/exam-results', [\App\Http\Controllers\StudentPortalController::class, 'examResults'])->name('exam-results');
        Route::get('/evaluations', [\App\Http\Controllers\StudentPortalController::class, 'evaluations'])->name('evaluations');
        Route::get('/evaluations/{offering}', [\App\Http\Controllers\StudentPortalController::class, 'showEvaluation'])->name('evaluations.show');
        Route::post('/evaluations/{offering}', [\App\Http\Controllers\StudentPortalController::class, 'submitEvaluation'])->name('evaluations.submit');

        Route::get('/notifications', [\App\Http\Controllers\StudentLmsController::class, 'notifications'])->name('notifications');
        Route::get('/notifications/{notification}/read', [\App\Http\Controllers\StudentLmsController::class, 'readNotification'])->name('notifications.read');

        Route::prefix('lms/{offering}')->name('lms.')->group(function () {
            Route::get('/', [\App\Http\Controllers\StudentLmsController::class, 'show'])->name('show');
            Route::get('/content', [\App\Http\Controllers\StudentLmsController::class, 'content'])->name('content');
            Route::get('/assignments', [\App\Http\Controllers\StudentLmsController::class, 'assignments'])->name('assignments');
            Route::get('/assignments/{assignment}', [\App\Http\Controllers\StudentLmsController::class, 'showAssignment'])->name('assignments.show');
            Route::post('/assignments/{assignment}/submit', [\App\Http\Controllers\StudentLmsController::class, 'submitAssignment'])->name('assignments.submit');
            Route::get('/submissions/{submission}/marked', [\App\Http\Controllers\StudentLmsController::class, 'downloadMarkedSubmission'])->name('submissions.marked');
            Route::get('/discussions', [\App\Http\Controllers\StudentLmsController::class, 'discussions'])->name('discussions');
            Route::post('/discussions', [\App\Http\Controllers\StudentLmsController::class, 'storeDiscussion'])->name('discussions.store');
            Route::get('/discussions/{discussion}', [\App\Http\Controllers\StudentLmsController::class, 'showDiscussion'])->name('discussions.show');
            Route::post('/discussions/{discussion}/posts', [\App\Http\Controllers\StudentLmsController::class, 'storeDiscussionPost'])->name('discussions.posts.store');
            Route::get('/materials/{material}/download', [\App\Http\Controllers\StudentLmsController::class, 'downloadMaterial'])->name('materials.download');
        });
    });

    Route::get('students/{student}/courses', [StudentManagementController::class, 'courses'])->name('students.courses');
    Route::resource('students', StudentManagementController::class);

    Route::get('assessments/institution', [AssessmentController::class, 'institutionIndex'])->name('assessments.institution.index');
    Route::get('assessments/programme', [AssessmentController::class, 'programmeIndex'])->name('assessments.programme.index');
    Route::resource('assessments', AssessmentController::class)->only(['index', 'create', 'store', 'show', 'edit', 'destroy']);
    Route::get('assessments/{assessment}/sections/{section}', [AssessmentController::class, 'showSection'])->name('assessments.sections.show');
    Route::post('assessments/{assessment}/score', [AssessmentController::class, 'score'])->name('assessments.score');
    Route::put('assessments/{assessment}/recommendations', [AssessmentController::class, 'updateRecommendations'])->name('assessments.recommendations.update');
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
