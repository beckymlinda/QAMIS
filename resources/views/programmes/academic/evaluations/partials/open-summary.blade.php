<div class="space-y-6">
    @forelse($items as $item)
        <div>
            <h4 class="font-medium text-[#0f2744] mb-2">{{ $item['question'] }}</h4>
            @forelse($item['comments'] as $comment)
                <p class="text-sm text-gray-700 border-l-2 border-[#8cc63f] pl-3 mb-2">{{ $comment }}</p>
            @empty
                <p class="text-sm text-gray-500">No comments submitted.</p>
            @endforelse
        </div>
    @empty
        <p class="text-sm text-gray-500">No open-ended questions configured.</p>
    @endforelse
</div>
