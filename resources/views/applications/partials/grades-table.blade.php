@php
    use App\Support\CertificateGrades;
    $totalPoints = CertificateGrades::totalPoints($application->certificate_grades);
@endphp

<table class="min-w-full text-sm">
    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
        <tr>
            <th class="px-4 py-3 font-semibold">Subject</th>
            <th class="px-4 py-3 font-semibold text-right">Points</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-100">
        @forelse($application->certificate_grades ?? [] as $row)
            <tr>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $row['subject'] ?? '—' }}</td>
                <td class="px-4 py-3 text-right font-semibold text-brand-primary">{{ $row['points'] ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="2" class="px-4 py-6 text-center text-gray-500">No grades recorded.</td></tr>
        @endforelse
    </tbody>
    @if($totalPoints > 0)
        <tfoot class="bg-gray-50">
            <tr>
                <td class="px-4 py-3 text-sm font-bold text-gray-700">Total points</td>
                <td class="px-4 py-3 text-right text-sm font-bold text-brand-primary">{{ $totalPoints }}</td>
            </tr>
        </tfoot>
    @endif
</table>
