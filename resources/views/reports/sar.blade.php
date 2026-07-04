<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Self-Assessment Report — {{ $institution->name }}</title><style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;line-height:1.45;color:#111;margin:0;padding:0}
h1{font-size:18px;color:#0f2744;margin:0 0 4px;text-align:center;text-transform:uppercase}
h2{font-size:14px;color:#0f2744;margin:22px 0 8px;border-bottom:2px solid #0f2744;padding-bottom:4px}
h3{font-size:12px;color:#0f2744;margin:14px 0 6px}
h4{font-size:11px;color:#333;margin:10px 0 4px;font-weight:bold}
p{margin:6px 0}
table{width:100%;border-collapse:collapse;margin:10px 0 14px;font-size:10px}
th,td{border:1px solid #999;padding:6px;text-align:left;vertical-align:top}
th{background:#e8eef4;color:#0f2744;font-weight:bold}
.table-caption{font-size:10px;font-weight:bold;color:#0f2744;margin:12px 0 4px}
.muted{color:#666;font-style:italic}
.center{text-align:center}
.bullet-list{margin:6px 0 6px 18px;padding:0}
.bullet-list li{margin:4px 0}
.page-break{page-break-before:always}
pre{white-space:pre-wrap;font-family:inherit;margin:0}
.cover-meta{text-align:center;color:#444;margin:4px 0}
.aggregate-row td{font-weight:bold;background:#f3f4f6}
</style></head>
<body>

@include('reports.partials.logo-header', [
    'title' => strtoupper($institution->name),
    'subtitle' => 'Institution and Programme Accreditation Self-Assessment Report (SAR)',
    'meta' => ($snapshot['year'] ?? date('Y')).' | Generated '.now()->format('F Y'),
])

@php $profile = $snapshot['profile'] ?? null; @endphp

<h2>Executive Summary</h2>
@if(!empty($profile['executive_summary']))
<pre>{{ $profile['executive_summary'] }}</pre>
@else
<p class="muted">Add executive summary under Report Data → Report narrative.</p>
@endif

@if(!empty($snapshot['summary_table_rows']))
<p class="table-caption">Table 1: Summary observations and scores for the institution and programmes</p>
<table>
<tr>
    <th style="width:5%">Sn</th>
    <th style="width:22%">Area of assessment</th>
    <th style="width:10%">Aggregate Score</th>
    <th style="width:33%">Observations</th>
    <th style="width:30%">Recommendation</th>
</tr>
@foreach($snapshot['summary_table_rows'] as $i => $row)
<tr>
    <td>{{ $i + 1 }}</td>
    <td>{{ $row['name'] }}</td>
    <td>{{ $row['aggregate_score'] }}</td>
    <td>{{ $row['observations'] }}</td>
    <td>{{ $row['recommendation'] }}</td>
</tr>
@endforeach
</table>
@endif

<h2>List of Abbreviations and Acronyms</h2>
@if(!empty($profile['abbreviations_acronyms']))
<pre>{{ $profile['abbreviations_acronyms'] }}</pre>
@else
<p class="muted">Not provided.</p>
@endif

<h2>1.0 Introduction</h2>
<h3>1.1 Approach</h3>
@if(!empty($profile['introduction_approach']))
<pre>{{ $profile['introduction_approach'] }}</pre>
@else
<p class="muted">Not provided.</p>
@endif

<h3>1.2 Assessment Team</h3>
@if(!empty($profile['assessment_team_composition']))
<pre>{{ $profile['assessment_team_composition'] }}</pre>
@else
<p class="muted">Not provided.</p>
@endif

<h2>2.0 Institutional Background, Governance Structures and Management</h2>
<h3>2.1 Institutional Background</h3>
<p>{{ $profile['background_narrative'] ?? $profile['core_function'] ?? 'Not provided.' }}</p>

<h3>2.2 Vision, Mission, and Core Values</h3>
<p><strong>Vision:</strong> {{ $profile['vision'] ?? '—' }}</p>
<p><strong>Mission:</strong> {{ $profile['mission'] ?? '—' }}</p>
<p><strong>Core Values:</strong> {{ $profile['core_values'] ?? '—' }}</p>

<h3>2.3 Governance Structure</h3>
@php use App\Support\GovernanceBodyType; $governance = collect($snapshot['governance'] ?? []); @endphp
@foreach(GovernanceBodyType::labels() as $type => $label)
@if($governance->has($type) && count($governance[$type]) > 0)
<h4>{{ $label }}</h4>
<table>
<tr><th>Name</th><th>Gender</th><th>Qualification</th><th>Designation</th><th>Specialization</th></tr>
@foreach($governance[$type] as $member)
<tr>
<td>{{ $member['name'] ?? '' }}</td>
<td>{{ ucfirst($member['gender'] ?? '—') }}</td>
<td>{{ $member['qualification'] ?? '—' }}</td>
<td>{{ $member['designation'] ?? '—' }}</td>
<td>{{ $member['specialization'] ?? '—' }}</td>
</tr>
@endforeach
</table>
@endif
@endforeach

<h3>2.4 Policies, Procedures and Guidelines</h3>
@if(!empty($profile['policies_procedures_summary']))
<pre>{{ $profile['policies_procedures_summary'] }}</pre>
@else
<p class="muted">Not provided.</p>
@endif

<h3>2.5 Academic Staff Profile</h3>
@php $staffRows = collect($snapshot['staff_members'] ?? []); @endphp
@if($staffRows->isNotEmpty())
<table>
<tr><th>Name</th><th>Programme</th><th>Gender</th><th>Qualification</th><th>Rank</th><th>Employment</th></tr>
@foreach($staffRows as $staff)
<tr>
<td>{{ $staff['name'] ?? '' }}</td>
<td>{{ $staff['programme']['name'] ?? 'Institution-wide' }}</td>
<td>{{ ucfirst($staff['gender'] ?? '—') }}</td>
<td>{{ $staff['qualification'] ?? '—' }}</td>
<td>{{ $staff['rank'] ?? '—' }}</td>
<td>{{ ucfirst(str_replace('-', ' ', $staff['employment_type'] ?? '—')) }}</td>
</tr>
@endforeach
</table>
@else
<p class="muted">No staff records.</p>
@endif

<h3>2.6 Student Profile</h3>
@php $studentRows = collect($snapshot['student_enrolments'] ?? []); @endphp
@if($studentRows->isNotEmpty())
<table>
<tr><th>Programme / Qualification</th><th>Male</th><th>Female</th><th>Total</th></tr>
@foreach($studentRows as $row)
<tr>
<td>{{ $row['programme']['name'] ?? ($row['qualification_type'] ?? '—') }}</td>
<td>{{ $row['male_count'] ?? 0 }}</td>
<td>{{ $row['female_count'] ?? 0 }}</td>
<td>{{ ($row['male_count'] ?? 0) + ($row['female_count'] ?? 0) }}</td>
</tr>
@endforeach
</table>
@else
<p class="muted">No student enrolment data.</p>
@endif

<div class="page-break"></div>

<h2>3.0 Institutional Assessment</h2>
@php $inst = $snapshot['institutional_assessment'] ?? null; @endphp

@if($inst)
<h3>3.1 Strengths and Areas for Improvement</h3>
@if(!empty($inst['strengths_improvement_rows']))
<p class="table-caption">Table 18: Strengths and areas for improvement</p>
<table>
<tr><th style="width:5%">SN</th><th style="width:18%">Area Assessed</th><th style="width:38%">Strengths</th><th style="width:39%">Areas for improvement</th></tr>
@foreach($inst['strengths_improvement_rows'] as $row)
<tr>
<td>{{ $row['sn'] }}</td>
<td>{{ $row['area'] }}</td>
<td>@if(!empty($row['strengths']))<ul class="bullet-list">@foreach($row['strengths'] as $s)<li>{{ $s }}</li>@endforeach</ul>@else—@endif</td>
<td>@if(!empty($row['improvements']))<ul class="bullet-list">@foreach($row['improvements'] as $s)<li>{{ $s }}</li>@endforeach</ul>@else—@endif</td>
</tr>
@endforeach
</table>
@else
<p class="muted">Complete scoring to populate strengths and improvement areas.</p>
@endif

@if(!empty($inst['narrative_recommendations']))
<h3>Recommendations</h3>
<ul class="bullet-list">
@foreach($inst['narrative_recommendations'] as $rec)
<li>{{ $rec }}</li>
@endforeach
</ul>
@endif

<p class="table-caption">Table 19: Institutional Assessment Scores</p>
<table>
<tr>
    <th>SN</th>
    <th>Area Assessed</th>
    <th>Assessment Areas</th>
    <th>Comment on critical Areas</th>
    <th>Aggregate score</th>
    <th>Recommendation</th>
</tr>
@foreach($inst['section_score_rows'] ?? [] as $row)
<tr>
    <td>{{ $row['sn'] }}</td>
    <td>{{ $row['area'] }}</td>
    <td>{{ $row['assessment_areas'] }}</td>
    <td>{{ $row['critical_comment'] }}</td>
    <td>{{ $row['aggregate_score'] }}</td>
    <td>{{ $row['recommendation'] }}</td>
</tr>
@endforeach
<tr class="aggregate-row">
    <td colspan="4">Aggregate Score</td>
    <td>{{ number_format($inst['overall_average'], 2) }}</td>
    <td>{{ $inst['overall_recommendation'] }}</td>
</tr>
</table>
@else
<p class="muted">No institutional self-assessment completed.</p>
@endif

<h2>Grading and Interpretation of Assessment Scores</h2>
<p>The grading scale used was as follows: 0 = Poor/Unavailable; 1 = Insufficient; 2 = Satisfactory; 3 = Good; and 4 = Excellent. All items marked with an asterisk (*) are mandatory requirements at the accreditation stage.</p>
<p class="table-caption">Table 8: Interpretation of the accreditation scores</p>
<table>
<tr><th>Score range</th><th>Rating</th><th>Accreditation outcome</th></tr>
<tr><td>1 – 1.99</td><td>Not satisfactory</td><td>Not accredited / Withdrawal of Accreditation.</td></tr>
<tr><td>2 – 2.99</td><td>Satisfactory</td><td>Accredited with conditions (provided all starred items have a minimum score of 3).</td></tr>
<tr><td>3 – 4</td><td>Excellent</td><td>Accreditation</td></tr>
</table>

<div class="page-break"></div>

<h2>4.0 Programme Assessment</h2>
@if(count($snapshot['programme_assessments'] ?? []) > 0)
<p>{{ count($snapshot['programme_assessments']) }} academic programme(s) were assessed covering programme design, delivery, resources, staff complement, admission, assessment, quality enhancement, and related areas.</p>
@endif

@forelse($snapshot['programme_assessments'] ?? [] as $index => $pa)
<h3>4.{{ $index + 1 }} {{ $pa['programme']['name'] ?? $pa['title'] }}</h3>

@if(!empty($pa['strengths_improvement_rows']))
<p class="table-caption">Strengths and areas for improvement — {{ $pa['programme']['name'] ?? $pa['title'] }}</p>
<table>
<tr><th>Sn.</th><th>Area Assessed</th><th>Strengths</th><th>Areas for improvement</th></tr>
@foreach($pa['strengths_improvement_rows'] as $row)
<tr>
<td>{{ $row['sn'] }}</td>
<td>{{ $row['area'] }}</td>
<td>@if(!empty($row['strengths']))<ul class="bullet-list">@foreach($row['strengths'] as $s)<li>{{ $s }}</li>@endforeach</ul>@else—@endif</td>
<td>@if(!empty($row['improvements']))<ul class="bullet-list">@foreach($row['improvements'] as $s)<li>{{ $s }}</li>@endforeach</ul>@else—@endif</td>
</tr>
@endforeach
</table>
@endif

@if(!empty($pa['narrative_recommendations']))
<h4>Recommendations</h4>
<ul class="bullet-list">
@foreach($pa['narrative_recommendations'] as $rec)
<li>{{ $rec }}</li>
@endforeach
</ul>
@endif

<p class="table-caption">Score and recommendation — {{ $pa['programme']['name'] ?? $pa['title'] }}</p>
<table>
<tr>
    <th>Sn.</th>
    <th>Area Assessed</th>
    <th>Scores</th>
    <th>Comments pertaining to critical areas</th>
    <th>Recommendation</th>
</tr>
@foreach($pa['section_score_rows'] ?? [] as $row)
<tr>
    <td>{{ $row['sn'] }}</td>
    <td>{{ $row['area'] }}</td>
    <td>{{ $row['aggregate_score'] }}</td>
    <td>{{ $row['critical_comment'] }}</td>
    <td>{{ $row['recommendation'] }}</td>
</tr>
@endforeach
<tr class="aggregate-row">
    <td colspan="2">Aggregate Score</td>
    <td>{{ number_format($pa['overall_average'], 2) }}</td>
    <td colspan="2">{{ $pa['overall_recommendation'] }}</td>
</tr>
</table>

@if(!empty($pa['staff']))
<p class="table-caption">Academic Staff Complement — {{ $pa['programme']['name'] ?? $pa['title'] }}</p>
<table>
<tr><th>Name</th><th>Qualification</th><th>Rank</th><th>Employment Status</th></tr>
@foreach($pa['staff'] as $staff)
<tr>
<td>{{ $staff['name'] ?? '' }}</td>
<td>{{ $staff['qualification'] ?? '—' }}</td>
<td>{{ $staff['rank'] ?? '—' }}</td>
<td>{{ ucfirst(str_replace('-', ' ', $staff['employment_type'] ?? '—')) }}</td>
</tr>
@endforeach
</table>
@endif

@empty
<p class="muted">No programme self-assessments recorded.</p>
@endforelse

<p class="center muted" style="margin-top:30px"><strong>End of Report</strong></p>

</body></html>
