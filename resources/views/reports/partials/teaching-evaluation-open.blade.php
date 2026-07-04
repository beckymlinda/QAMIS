@forelse($items as $item)
    <h3>{{ $item['question'] }}</h3>
    @forelse($item['comments'] as $comment)
        <p class="comment">{{ $comment }}</p>
    @empty
        <p class="muted">No comments submitted.</p>
    @endforelse
@empty
    <p class="muted">No open-ended questions configured.</p>
@endforelse
