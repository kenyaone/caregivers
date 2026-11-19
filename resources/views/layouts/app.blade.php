<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('noc.brand') }}</title>
    <meta name="description" content="{{ $description ?? 'Find verified caregivers in Kenya for elderly care, children, live-in care and disability support, or find caregiving work near you.' }}">
    <meta name="theme-color" content="#0f766e">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/care.css') }}?v={{ filemtime(public_path('css/care.css')) }}">
    @includeIf('partials.pwa-head')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased flex flex-col">
@php($u = auth()->user())
<div class="cgi-only cgi-block" style="background:linear-gradient(90deg,#fbbf24,#fcd34d);color:#1c1917">
    <div style="max-width:72rem;margin:0 auto;padding:.55rem 1rem;display:flex;align-items:center;justify-content:center;gap:.75rem;flex-wrap:wrap;font-size:.875rem;text-align:center">
        <span><span aria-hidden="true">&#128242;</span> <b>Get the CareGivers254 app</b><span class="hidden sm:inline"> &middot; free, works on any phone, instant alerts</span></span>
        <button type="button" data-cg-install class="cgi-btn" style="display:inline-flex;background:#0f766e;color:#fff;padding:.35rem .9rem;font-size:.8rem">Install now</button>
    </div>
</div>
<header class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-lg text-teal-800">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-teal-700 text-white text-sm">&hearts;</span>
            CareGivers<span class="text-amber-500">254</span>
        </a>
        <button type="button" data-cg-install class="cgi-only cgi-btn cgi-pill cgi-pulse" style="margin-left:auto"><span aria-hidden="true">&#128242;</span> Install app</button>
        <input type="checkbox" id="nav" class="peer hidden">
        <label for="nav" class="md:hidden p-2 -mr-2 cursor-pointer" aria-label="Menu">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </label>
        <nav class="hidden peer-checked:flex md:flex absolute md:static top-16 inset-x-0 bg-white md:bg-transparent border-b md:border-0 flex-col md:flex-row md:items-center gap-1 md:gap-5 p-4 md:p-0 text-sm font-medium">
            <a href="{{ route('caregivers.index') }}" class="nav-link">Find a caregiver</a>
            <a href="{{ route('jobs.index') }}" class="nav-link">Care jobs</a>
            @guest
                <a href="{{ route('pricing') }}" class="nav-link">Pricing</a>
                <a href="{{ route('login') }}" class="nav-link">Log in</a>
                <a href="{{ route('register', ['as' => 'caregiver']) }}" class="btn btn-primary">I'm a caregiver</a>
            @else
                @if($u->isCaregiver())
                    <a href="{{ route('caregiver.dashboard') }}" class="nav-link">Dashboard</a>
                    <a href="{{ route('caregiver.requests') }}" class="nav-link">Requests</a>
                    <a href="{{ route('caregiver.skills') }}" class="nav-link">Skills</a>
                    <a href="{{ route('caregiver.profile') }}" class="nav-link">Profile</a>
                @elseif($u->isFamily())
                    <a href="{{ route('family.dashboard') }}" class="nav-link">Dashboard</a>
                    <a href="{{ route('family.jobs.create') }}" class="nav-link">Post a job</a>
                    <a href="{{ route('family.contacts') }}" class="nav-link">My caregivers</a>
                @else
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">Admin</a>
                    <a href="{{ route('admin.verifications') }}" class="nav-link">Verifications</a>
                    <a href="{{ route('admin.payments') }}" class="nav-link">Payments</a>
                    <a href="{{ route('admin.reports') }}" class="nav-link">Reports</a>
                @endif
                @php($unread = $u->unreadNotifications()->count())
                <a href="{{ route('notifications') }}" class="nav-link">Alerts @if($unread)<span class="ml-1 rounded-full bg-amber-500 px-1.5 text-xs text-white">{{ $unread }}</span>@endif</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-link">Log out</button></form>
            @endguest
        </nav>
    </div>
</header>

<main class="flex-1">
    @if(session('status') || $errors->any())
        <div class="max-w-6xl mx-auto px-4 pt-6 space-y-2">
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
            @if($errors->any())<div class="alert alert-error"><ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        </div>
    @endif
    @yield('content')
</main>

<footer class="bg-slate-900 text-slate-400 text-sm mt-16">
    <div class="max-w-6xl mx-auto px-4 py-10 grid gap-6 md:grid-cols-3">
        <div>
            <div class="font-bold text-white">CareGivers254</div>
            <p class="mt-2">Connecting Kenyan families with verified, background-checked caregivers.</p>
        </div>
        <div class="space-y-1">
            <a class="block hover:text-white" href="{{ route('caregivers.index') }}">Find a caregiver</a>
            <a class="block hover:text-white" href="{{ route('jobs.index') }}">Care jobs</a>
            <a class="block hover:text-white" href="{{ route('pricing') }}">Pricing</a>
            <a class="block hover:text-white" href="{{ route('terms') }}">Terms & privacy</a>
        </div>
        <div class="space-y-1">
            @if(config('noc.support_phone'))<div>Call/WhatsApp: {{ config('noc.support_phone') }}</div>@endif
            <div>&copy; {{ date('Y') }} CareGivers254 · An Afya Yako service</div>
        </div>
    </div>
</footer>
@includeIf('partials.pwa')
</body>
</html>
