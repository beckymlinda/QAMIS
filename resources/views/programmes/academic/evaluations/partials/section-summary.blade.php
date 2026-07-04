@foreach($groups as $group)
    <div class="mb-6 last:mb-0">
        <h4 class="font-semibold text-[#0f2744] mb-2">
            {{ $group['title'] }}
            @if($group['average'])
                <span class="text-sm font-normal text-gray-500">· Avg {{ number_format($group['average'], 2) }}/5</span>
            @endif
        </h4>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left">Statement</th>
                        <th class="px-3 py-2 text-left">Average</th>
                        <th class="px-3 py-2 text-left">Responses</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['questions'] as $question)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ $question['text'] }}</td>
                            <td class="px-3 py-2 font-medium">{{ $question['average'] !== null ? number_format($question['average'], 2) : '—' }}</td>
                            <td class="px-3 py-2 text-gray-500">{{ $question['count'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
