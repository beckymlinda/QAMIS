<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Students' Evaluation of Teaching Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; line-height: 1.45; }
        h1, h2, h3 { color: #0f2744; margin: 0 0 8px; }
        h1 { font-size: 18px; }
        h2 { font-size: 14px; margin-top: 18px; border-bottom: 1px solid #8cc63f; padding-bottom: 4px; }
        h3 { font-size: 12px; margin-top: 12px; }
        p { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0 16px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .meta { color: #666; font-size: 10px; margin-bottom: 14px; }
        .legend { background: #f9fafb; border: 1px solid #ddd; padding: 8px 10px; margin: 10px 0 16px; }
        .avg { font-weight: bold; color: #0f2744; }
        .section-label { font-size: 13px; font-weight: bold; color: #0f2744; margin-top: 16px; }
        .course-block { page-break-inside: avoid; margin-bottom: 18px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
        .comment { margin: 0 0 6px; padding-left: 10px; border-left: 2px solid #8cc63f; }
        .muted { color: #666; }
        .summary-box { background: #0f2744; color: #fff; padding: 10px 12px; margin: 12px 0; }
        .summary-box strong { color: #8cc63f; }
    </style>
</head>
<body>
@include('reports.partials.logo-header', [
    'title' => "Students' Evaluation of Teaching Questionnaire",
    'subtitle' => $programme->name,
    'meta' => $institution->name.' · '.$period->title.' · '.$period->academic_year.' Semester '.$period->semester,
])

<p class="meta">Generated {{ $generated_at->format('d M Y H:i') }} · {{ $total_submissions }} anonymous submission{{ $total_submissions === 1 ? '' : 's' }}</p>

<div class="legend">
    <strong>Rating scale (5-point Likert):</strong>
    @foreach($likert_labels as $value => $label)
        {{ $value }} = {{ $label }}@if(!$loop->last); @endif
    @endforeach
</div>

<div class="summary-box">
    <strong>Programme summary</strong> — {{ $total_submissions }} responses across {{ $offerings->where('response_count', '>', 0)->count() }} course{{ $offerings->where('response_count', '>', 0)->count() === 1 ? '' : 's' }}
</div>

<h2>A. Course Evaluation — Programme Summary</h2>
@include('reports.partials.teaching-evaluation-section', ['groups' => $programme_sections['course']])

<h2>B. Lecturer Evaluation — Programme Summary</h2>
@include('reports.partials.teaching-evaluation-section', ['groups' => $programme_sections['lecturer']])

<h2>Open-Ended Responses — Programme Summary</h2>
@include('reports.partials.teaching-evaluation-open', ['items' => $programme_sections['open']])

@foreach($offerings as $item)
    @if($item['response_count'] > 0)
        <div class="course-block">
            <h2>{{ $item['course_code'] }} — {{ $item['course_title'] }}</h2>
            <p class="muted">Lecturer: {{ $item['lecturer_name'] }} · {{ $item['response_count'] }} response{{ $item['response_count'] === 1 ? '' : 's' }}</p>

            <div class="section-label">A. Course Evaluation</div>
            @include('reports.partials.teaching-evaluation-section', ['groups' => $item['sections']['course']])

            <div class="section-label">B. Lecturer Evaluation</div>
            @include('reports.partials.teaching-evaluation-section', ['groups' => $item['sections']['lecturer']])

            <div class="section-label">Open-Ended Questions</div>
            @include('reports.partials.teaching-evaluation-open', ['items' => $item['sections']['open']])
        </div>
    @endif
@endforeach

@if($total_submissions === 0)
    <p class="muted">No submitted evaluations were recorded for this programme during this period.</p>
@endif
</body>
</html>
