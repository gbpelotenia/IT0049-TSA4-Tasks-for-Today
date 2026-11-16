<x-site-layout title="Welcome">
<section class="rounded-3xl bg-slate-900 p-8 sm:p-14 text-white">
<p class="text-indigo-200 font-semibold text-sm tracking-widest uppercase">A little structure for your day</p>
<h1 class="text-4xl sm:text-5xl font-bold leading-tight mt-5 max-w-2xl">Today's tasks,<br>one clear view.</h1>
<p class="mt-6 text-lg text-slate-300 max-w-xl">Keep track of what matters, follow your progress, and celebrate each completed task.</p>
<div class="mt-8 flex flex-wrap gap-4"><a href="{{ route('tasks.index') }}" class="rounded-lg px-5 py-3 bg-indigo-500 hover:bg-indigo-400 font-semibold">Explore tasks →</a>@guest<a href="{{ route('login') }}" class="rounded-lg border border-slate-600 px-5 py-3 hover:bg-slate-800">Log in to manage</a>@else<a href="{{ route('tasks.create') }}" class="rounded-lg border border-slate-600 px-5 py-3 hover:bg-slate-800">Create a task</a>@endguest</div>
</section>
<section class="grid sm:grid-cols-3 gap-6 mt-8" aria-label="Features">
@foreach (['01' => ['Plan with purpose', 'Create tasks with a clear description, priority, and due date.'], '02' => ['See your progress', 'Move from pending to in progress to done, one task at a time.'], '03' => ['Close the loop', 'A completion email is generated when a task changes to done.']] as $number => [$heading, $description])
<div class="rounded-2xl bg-white border border-slate-200 p-6"><p class="text-indigo-600 font-bold">{{ $number }}</p><h2 class="mt-4 text-lg font-semibold">{{ $heading }}</h2><p class="mt-2 text-slate-600 leading-relaxed">{{ $description }}</p></div>
@endforeach</section>
</x-site-layout>
