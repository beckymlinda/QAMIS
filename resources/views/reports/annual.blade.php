<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Annual Report</title><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px}h1,h2{color:#1e3a8a}table{width:100%;border-collapse:collapse;margin:12px 0}th,td{border:1px solid #ccc;padding:6px}</style></head>
<body>
@include('reports.partials.logo-header', [
    'title' => 'Annual Report / Institutional Audit',
    'subtitle' => $institution->name,
    'meta' => 'Year: '.$year,
])
<h2>2. Mission</h2><p>{{ $snapshot['profile']['mission'] ?? '—' }}</p>
<h2>3. Vision</h2><p>{{ $snapshot['profile']['vision'] ?? '—' }}</p>
<h2>4. Values</h2><p>{{ $snapshot['profile']['core_values'] ?? '—' }}</p>
<h2>6. Faculties, Programmes and Mode of Delivery</h2>
<table><tr><th>Programme</th><th>Level</th><th>Status</th></tr>
@foreach($snapshot['programmes'] ?? [] as $programme)
<tr><td>{{ $programme['name'] }}</td><td>{{ $programme['level'] }}</td><td>{{ $programme['nche_accreditation_status'] }}</td></tr>
@endforeach
</table>
<h2>18. Quality Assurance Activities</h2>
@foreach($snapshot['compliance'] ?? [] as $c)
<p>Compliance: {{ $c['compliance_status'] ?? '' }} — Recommendation: {{ $c['accreditation_recommendation'] ?? '' }}</p>
@endforeach
</body></html>
