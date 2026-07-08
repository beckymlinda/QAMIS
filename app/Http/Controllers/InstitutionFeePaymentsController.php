<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use App\Models\Student;
use App\Services\StudentFeesService;
use App\Support\InstitutionScope;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionFeePaymentsController extends Controller
{
    public function __construct(
        protected StudentFeesService $fees,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);

        $institutionId = InstitutionScope::institutionId();
        abort_unless($institutionId, 403, 'Select an institution to view fee payments.');

        $filters = [
            'programme_id' => $request->integer('programme_id') ?: null,
            'year_of_study' => $request->integer('year_of_study') ?: null,
            'payment_status' => $request->string('payment_status')->toString() ?: 'all',
            'income_period' => $request->string('income_period')->toString() ?: 'all',
            'student_id' => $request->integer('student_id') ?: null,
        ];

        if ($filters['payment_status'] === '') {
            $filters['payment_status'] = 'all';
        }
        if ($filters['income_period'] === '') {
            $filters['income_period'] = 'all';
        }

        $report = $this->fees->institutionReport($institutionId, $filters);

        if ($filters['student_id']) {
            $report['rows'] = $report['rows']->filter(
                fn ($row) => $row['student']->id === $filters['student_id']
            )->values();
        }

        $programmes = Programme::query()
            ->where('institution_id', $institutionId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $yearsOfStudy = Student::query()
            ->where('institution_id', $institutionId)
            ->distinct()
            ->orderBy('year_of_study')
            ->pluck('year_of_study');

        $allStudents = Student::query()
            ->where('institution_id', $institutionId)
            ->when($filters['programme_id'], fn ($q, $id) => $q->where('programme_id', $id))
            ->when($filters['year_of_study'], fn ($q, $y) => $q->where('year_of_study', $y))
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'student_number']);

        return view('fee-payments.index', compact('report', 'filters', 'programmes', 'yearsOfStudy', 'allStudents'));
    }
}
