<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentFeePayment;
use App\Services\StudentFeesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentFeePaymentController extends Controller
{
    public function __construct(
        protected StudentFeesService $fees,
    ) {}

    protected function studentRecord(): Student
    {
        $student = auth()->user()?->studentProfile;
        abort_unless($student, 403, 'No student profile linked to this account.');

        return $student;
    }

    public function fees(): View
    {
        $student = $this->studentRecord();
        $summary = $this->fees->summary($student);

        return view('student.fees', compact('student', 'summary'));
    }

    public function storePayment(Request $request): RedirectResponse
    {
        $student = $this->studentRecord();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_reference' => 'nullable|string|max:100',
            'receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $payment = $this->fees->storePayment($student, $validated, $request->file('receipt'));

        return redirect()
            ->route('student.fees')
            ->with('success', 'Payment receipt submitted. Your projected balance after approval is '.StudentFeesService::formatMoney((float) $payment->balance_after).'. Awaiting finance approval.');
    }

    public function previewReceipt(StudentFeePayment $payment): Response|StreamedResponse
    {
        abort_unless($payment->student_id === $this->studentRecord()->id, 403);

        return $this->receiptResponse($payment);
    }

    public function approve(Request $request, Student $student, StudentFeePayment $payment): RedirectResponse
    {
        $this->authorize('update', $student);
        abort_unless($payment->student_id === $student->id, 404);

        $validated = $request->validate(['admin_notes' => 'nullable|string|max:1000']);
        $this->fees->approve($payment, $validated['admin_notes'] ?? null);

        return back()->with('success', 'Payment receipt approved.');
    }

    public function reject(Request $request, Student $student, StudentFeePayment $payment): RedirectResponse
    {
        $this->authorize('update', $student);
        abort_unless($payment->student_id === $student->id, 404);

        $validated = $request->validate(['admin_notes' => 'nullable|string|max:1000']);
        $this->fees->reject($payment, $validated['admin_notes'] ?? null);

        return back()->with('success', 'Payment receipt rejected.');
    }

    public function adminPreviewReceipt(Student $student, StudentFeePayment $payment): Response|StreamedResponse
    {
        $this->authorize('view', $student);
        abort_unless($payment->student_id === $student->id, 404);

        return $this->receiptResponse($payment);
    }

    protected function receiptResponse(StudentFeePayment $payment): Response|StreamedResponse
    {
        abort_unless($payment->receipt_path && Storage::disk('local')->exists($payment->receipt_path), 404);

        $mime = Storage::disk('local')->mimeType($payment->receipt_path) ?: 'application/octet-stream';

        return response(Storage::disk('local')->get($payment->receipt_path), 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($payment->receipt_path).'"',
        ]);
    }
}
