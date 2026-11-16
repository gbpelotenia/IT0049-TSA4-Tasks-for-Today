@props(['title' => 'Tasks for Today'])
<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title }} · Tasks for Today</title>@vite(['resources/css/app.css', 'resources/js/app.js'])
</head><body class="font-sans antialiased bg-slate-50 text-slate-900">
<div class="min-h-screen flex flex-col">
<header class="bg-white border-b border-slate-200"><nav aria-label="Main navigation" class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex flex-wrap items-center justify-between gap-4">
<a href="{{ route('welcome') }}" class="font-bold text-lg flex items-center gap-2"><span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-indigo-600 text-white" aria-hidden="true">✓</span> Tasks for Today</a>
<div class="flex flex-wrap items-center gap-x-5 gap-y-3 text-sm font-medium">
@foreach (['tasks.index' => 'Task List', 'profile' => 'Profile', 'about' => 'About'] as $route => $label)
<a href="{{ route($route) }}" @if(request()->routeIs($route)) aria-current="page" @endif class="{{ request()->routeIs($route) ? 'text-indigo-700 underline underline-offset-4' : 'text-slate-600 hover:text-indigo-700' }}">{{ $label }}</a>
@endforeach
@auth<a href="{{ route('profile.edit') }}" class="text-slate-600 hover:text-indigo-700">My account</a><form action="{{ route('logout') }}" method="POST">@csrf<button class="text-slate-600 hover:text-indigo-700" type="submit">Log out</button></form>
@else<a href="{{ route('login') }}" class="text-indigo-700">Log in</a><a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Register</a>@endauth
</div></nav></header>
<main class="w-full max-w-6xl mx-auto px-4 sm:px-6 py-10 flex-1">
@if (session('status'))<div role="status" class="mb-6 border border-emerald-200 bg-emerald-50 text-emerald-900 rounded-xl p-4">{{ session('status') }}</div>@endif
@if ($errors->has('mail'))<div role="alert" class="mb-6 bg-red-50 text-red-800 rounded-xl p-4">{{ $errors->first('mail') }}</div>@endif
{{ $slot }}
</main>
<footer class="border-t border-slate-200 bg-white"><div class="max-w-6xl mx-auto px-4 sm:px-6 py-5 text-sm text-slate-500 flex flex-wrap gap-2 justify-between"><span>Tasks for Today</span><span>Plan your day. Track your progress.</span></div></footer>
</div></body></html>
