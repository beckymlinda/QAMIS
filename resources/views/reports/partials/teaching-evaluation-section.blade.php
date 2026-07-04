@foreach($groups as $group)
    <h3>{{ $group['title'] }} @if($group['average'])<span class="avg">(Avg: {{ number_format($group['average'], 2) }}/5)</span>@endif</h3>
    <table>
        <thead>
            <tr>
                <th style="width:55%">Statement</th>
                <th style="width:10%">Avg</th>
                <th style="width:10%">n</th>
                <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th>
            </tr>
        </thead>
        <tbody>
            @foreach($group['questions'] as $question)
                <tr>
                    <td>{{ $question['text'] }}</td>
                    <td>{{ $question['average'] !== null ? number_format($question['average'], 2) : '—' }}</td>
                    <td>{{ $question['count'] }}</td>
                    @for($rating = 1; $rating <= 5; $rating++)
                        <td>{{ $question['distribution'][$rating] ?? 0 }}</td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
@endforeach
