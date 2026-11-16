@props(['status'])
@php($colors = ['pending' => 'bg-amber-50 text-amber-800', 'in_progress' => 'bg-indigo-50 text-indigo-800', 'done' => 'bg-emerald-50 text-emerald-800'])
<span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $colors[$status] ?? '' }}">{{ App\Models\Task::STATUSES[$status] ?? $status }}</span>
