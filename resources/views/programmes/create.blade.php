<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-gray-800">Register Programme</h2></x-slot>
    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('programmes.store') }}" class="bg-white p-6 rounded-lg shadow space-y-4">
            @csrf
            <div><label class="block text-sm font-medium">Name</label><input name="name" class="mt-1 w-full rounded-md border-gray-300" required></div>
            <div><label class="block text-sm font-medium">Department</label><select name="org_unit_id" class="mt-1 w-full rounded-md border-gray-300"><option value="">—</option>@foreach($orgUnits as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach</select></div>
            <div><label class="block text-sm font-medium">Level</label><select name="level" class="mt-1 w-full rounded-md border-gray-300" required><option value="certificate">Certificate</option><option value="diploma">Diploma</option><option value="bachelor">Bachelor</option><option value="master">Master</option><option value="doctorate">Doctorate</option></select></div>
            <div><label class="block text-sm font-medium">Delivery Modes</label>
                <div class="mt-2 space-x-4">@foreach(['fulltime','parttime','distance','elearning','weekend'] as $mode)<label><input type="checkbox" name="delivery_modes[]" value="{{ $mode }}"> {{ ucfirst($mode) }}</label>@endforeach</div>
            </div>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Register</button>
        </form>
    </div>
</x-app-layout>
