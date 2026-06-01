@php
$role=auth()->user()->role ?? 'public';
$menus=['admin'=>[['admin.dashboard','fa-gauge','Overview'],['admin.users.index','fa-users','Users'],['admin.lost-items.index','fa-magnifying-glass','Lost Items'],['admin.found-items.index','fa-box-open','Found Items'],['admin.claims.index','fa-file-signature','Claims'],['admin.categories.index','fa-tags','Categories'],['admin.logs','fa-clock-rotate-left','Activity Logs'],['admin.reports','fa-chart-column','Reports']],'staff'=>[['staff.dashboard','fa-gauge','Overview'],['staff.found-items.create','fa-plus','Post Found'],['staff.found-items.index','fa-box-open','My Found Items'],['staff.claims.index','fa-inbox','Claims Inbox'],['staff.lost-reports.index','fa-magnifying-glass','Lost Reports']],'student'=>[['student.dashboard','fa-gauge','Overview'],['student.lost-items.create','fa-plus','Report Lost'],['student.lost-items.index','fa-list','My Lost Reports'],['student.browse','fa-box-open','Browse Found'],['student.claims.index','fa-file-signature','My Claims'],['notifications','fa-bell','Notifications']]];
$unread=auth()->check()?auth()->user()->notifications()->where('is_read',false)->count():0;
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','TMC Lost and Found')</title>
    <meta name="description" content="@yield('meta_description','Secure campus item reporting and claims management.')">
    <meta name="theme-color" content="#1A3C6E">

    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/tmc-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/tmc-logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">

    <!-- Open Graph / Twitter -->
    <meta property="og:title" content="@yield('title','TMC Lost and Found')">
    <meta property="og:description" content="@yield('meta_description','Secure campus item reporting and claims management.')">
    <meta property="og:image" content="{{ asset('images/tmc-logo.png') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    @vite(['resources/css/app.css','resources/js/app.js'])

    <style>
    /* Accessibility helpers and mobile off-canvas sidebar */
    .skip-link{position:fixed;left:12px;top:12px;background:#fff;color:#111;padding:.5rem .75rem;border-radius:6px;z-index:9999;transform:translateY(-110%);transition:transform .18s ease}
    .skip-link:focus{transform:none;outline:3px solid #F4A800}
    :focus-visible{outline:3px solid #F4A800;outline-offset:2px}

    /* Mobile sidebar off-canvas */
    @media(max-width:991px){
      body.sidebar-open .sidebar{position:fixed;left:0;top:0;height:100vh;z-index:1045;transform:translateX(0);transition:transform .22s ease}
      .sidebar{transform:translateX(-100%);transition:transform .22s ease;box-shadow:0 18px 40px rgba(0,0,0,.4)}
      .mobile-toggle{display:inline-flex}
      .sidebar{width:320px}
      .main-pane{position:relative;z-index:1}
    }
    .mobile-toggle{display:none;background:none;border:0;color:inherit;font-size:1.1rem}
    </style>
</head>
<body class="{{ auth()->check() ? 'app-page' : 'public-page' }}">
@if(auth()->check())<a class="skip-link" href="#main-content">Skip to content</a><div class="app-shell"><aside class="sidebar" role="navigation" aria-label="Main menu"><div class="d-flex align-items-center gap-3 mb-4 px-2"><img loading="lazy" class="brand-logo" src="{{ asset('images/tmc-logo.png') }}" alt="Trinidad Municipal College logo"><div><div class="brand-title fs-5 lh-sm">TMC</div><small class="text-white-50">Lost and Found</small></div></div>@foreach($menus[$role]??[] as [$route,$icon,$label])<a class="{{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}"><i class="fa-solid {{ $icon }} fa-fw"></i><span>{{ $label }}</span></a>@endforeach</aside><main id="main-content" class="main-pane"><nav class="topbar d-flex justify-content-between align-items-center"><div><button id="mobile-sidebar-toggle" class="mobile-toggle d-lg-none" aria-label="Toggle menu"><i class="fa-solid fa-bars"></i></button><strong>@yield('title','TMC Lost and Found')</strong></div><div class="d-flex align-items-center gap-3"><a href="{{ route('notifications') }}" class="btn btn-light position-relative" aria-label="Notifications"><i class="fa-solid fa-bell"></i><span data-notification-count class="position-absolute top-0 start-100 translate-middle badge bg-danger {{ $unread ? '' : 'd-none' }}">{{ $unread }}</span><span id="notification-sr" class="visually-hidden" aria-live="polite">You have {{ $unread }} unread notifications</span></a><div class="dropdown"><button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">@if(auth()->user()->profile_photo)<img loading="lazy" class="avatar-sm me-1" src="{{ asset('storage/'.auth()->user()->profile_photo) }}" alt="">@else<i class="fa-solid fa-user-circle me-1"></i>@endif{{ auth()->user()->name }}</button><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fa-solid fa-user fa-fw me-2"></i>Profile</a></li><li><form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item"><i class="fa-solid fa-right-from-bracket fa-fw me-2"></i>Logout</button></form></li></ul></div></div></nav><div class="content-wrap">@include('partials.flash')@yield('content')</div></main></div>@else <a class="skip-link" href="#main-content">Skip to content</a>@include('partials.flash')<main id="main-content" class="public-shell">@yield('content')</main>@endif
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script><script src="https://cdn.jsdelivr.net/npm/chart.js"></script>@stack('scripts')</body></html>
