<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Self-Assessment Report</title><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px}h1,h2{color:#1e3a8a}table{width:100%;border-collapse:collapse;margin:12px 0}th,td{border:1px solid #ccc;padding:6px;text-align:left}</style></head>
<body>
<h1>Self-Assessment Report</h1>
<h2>{{ $institution->name }}</h2>
<p>Reporting Year: {{ $snapshot['year'] ?? date('Y') }}</p>
@if($snapshot['profile'] ?? null)
<h2>1. Institutional Background</h2>
<p><strong>Vision:</strong> {{ $snapshot['profile']['vision'] ?? '' }}</p>
<p><strong>Mission:</strong> {{ $snapshot['profile']['mission'] ?? '' }}</p>
@endif
@if($snapshot['institutional_assessment'] ?? null)
<h2>4. Institutional Assessment</h2>
<table><tr><th>Area</th><th>Aggregate Score</th></tr>
@foreach(($snapshot['institutional_assessment']['section_summaries'] ?? []) as $summary)
<tr><td>{{ $summary['section']['title'] ?? 'Section' }}</td><td>{{ $summary['aggregate_score'] ?? '—' }}</td></tr>
@endforeach
</table>
@endif
<h2>5. Programme Assessments</h2>
@foreach($snapshot['programme_assessments'] ?? [] as $pa)
<p><strong>{{ $pa['programme']['name'] ?? 'Programme' }}</strong> — Compliance: {{ $pa['compliance_result']['compliance_status'] ?? 'Pending' }}</p>
@endforeach
</body></html>
