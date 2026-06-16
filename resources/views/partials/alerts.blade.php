@if (session('success'))
    <div class="mb-4 bg-green-100 text-green-800 p-4 rounded-lg">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 bg-red-100 text-red-800 p-4 rounded-lg">{{ session('error') }}</div>
@endif
@if ($errors->any())
    <div class="mb-4 bg-red-100 text-red-800 p-4 rounded-lg">
        <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif
