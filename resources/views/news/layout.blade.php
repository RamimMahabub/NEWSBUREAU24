<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'NewsBureau24 | নিউজ ব্যুরো ২৪')</title>
    <meta name="description" content="@yield('description', $locale === 'bn' ? 'নিউজ ব্যুরো ২৪ — তথ্য, অনুসন্ধান ও জনস্বার্থের সাংবাদিকতা।' : 'NewsBureau24 — facts, investigations and journalism in the public interest.')">
    <link rel="canonical" href="{{ url()->current() }}">
    @if(isset($article) && $translated)
    <link rel="alternate" hreflang="{{ $otherLocale }}" href="{{ $article->url($otherLocale) }}">
    @elseif(request()->routeIs('home'))
    <link rel="alternate" hreflang="bn" href="{{ route('home', 'bn') }}"><link rel="alternate" hreflang="en" href="{{ route('home', 'en') }}">
    @endif
    <meta property="og:type" content="{{ isset($article) ? 'article' : 'website' }}"><meta property="og:title" content="{{ isset($article) ? $article->text('title') : 'NewsBureau24' }}"><meta property="og:image" content="{{ isset($article) ? $article->image() : asset('images/masthead.webp') }}">
    @if(\App\Models\Article::where('is_demo',true)->exists())<meta name="robots" content="noindex">@endif
    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('head')
</head>
<body x-data="{ menu: false, search: false }">
<a class="skip" href="#main">{{ $locale === 'bn' ? 'মূল সংবাদে যান' : 'Skip to content' }}</a>
<header>
    <div class="utility wrap"><time datetime="{{ now()->toDateString() }}">{{ now()->locale($locale)->translatedFormat('l, j F Y') }}</time><div class="utility-links">
        <a href="{{ route('page', [$locale,'about']) }}">{{ __('ui.about') }}</a><a href="{{ route('page', [$locale,'contact']) }}">{{ __('ui.contact') }}</a><a href="#newsletter">Newsletter</a>
        @foreach(['bn'=>'বাংলা','en'=>'EN'] as $lang=>$label)
            @if(!isset($article) || $lang === $locale || $translated)
            <a class="language {{ $locale === $lang ? 'active' : '' }}" lang="{{ $lang }}" href="{{ isset($article) ? $article->url($lang) : route('home',$lang) }}">{{ $label }}</a>
            @endif
        @endforeach
        <button class="icon-button" @click="search=!search" :aria-expanded="search" aria-controls="search-panel" aria-label="{{ __('ui.search') }}">⌕</button>
    </div></div>
    <div class="masthead wrap"><div class="masthead-tools"><button class="icon-button" @click="menu=!menu" :aria-expanded="menu" aria-controls="mobile-menu" aria-label="{{ $locale === 'bn' ? 'মেনু' : 'Menu' }}">☰</button><button class="icon-button" @click="search=!search" aria-label="{{ __('ui.search') }}">⌕</button></div>
        <a class="brand" href="{{ route('home',$locale) }}"><img src="{{ asset('images/masthead.webp') }}" width="2172" height="724" alt="NewsBureau24 — Facts | Investigations | A safer Bangladesh — নিউজ ব্যুরো ২৪" fetchpriority="high"></a>
        <a class="tip-button" href="{{ route('page',[$locale,'tips']) }}"><span aria-hidden="true">➤</span><span>{{ __('ui.tips') }}<small>Submit a Tip</small></span></a>
    </div>
    <form id="search-panel" class="search-panel wrap" x-show="search" x-cloak action="{{ route('search',$locale) }}" @keydown.escape.window="search=false"><label for="global-search">{{ __('ui.search') }}</label><input id="global-search" name="q" maxlength="150" placeholder="{{ __('ui.search') }}…"><button class="red-button">{{ __('ui.search') }} →</button></form>
    <nav class="primary-nav" aria-label="{{ $locale === 'bn' ? 'প্রধান বিভাগ' : 'Main navigation' }}"><div class="wrap nav-items">
    @foreach(['latest','national','crime','courts','investigations','districts','special-reports','video','data-maps','opinion'] as $key)
        <a class="{{ request('category') === $key ? 'selected' : '' }}" href="{{ route('search', ['locale'=>$locale] + ($key === 'latest' ? [] : ['category'=>$key])) }}">{{ __('ui.'.$key) }}<small>{{ ['latest'=>'Latest','national'=>'National','crime'=>'Crime','courts'=>'Courts','investigations'=>'Investigations','districts'=>'Districts','special-reports'=>'Special Reports','video'=>'Video','data-maps'=>'Data & Maps','opinion'=>'Opinion'][$key] }}</small></a>
    @endforeach
    </div></nav>
    <nav id="mobile-menu" class="mobile-menu wrap" x-show="menu" x-cloak aria-label="More"><a href="{{ route('home',$locale) }}">{{ __('ui.back') }}</a><a href="{{ route('page',[$locale,'standards']) }}">{{ __('ui.standards') }}</a><a href="/newsroom">Newsroom CMS ↗</a></nav>
</header>
<main id="main" class="wrap">
    @if(\App\Models\Article::where('is_demo',true)->exists())<div class="demo-notice">{{ __('ui.demo') }}</div>@endif
    @if(request()->boolean('preview'))<div class="notice">DRAFT PREVIEW — only visible to authorized editors</div>@endif
    @if(session('message'))<div class="notice" role="status">{{ session('message') }}</div>@endif
    @if($errors->any())<div class="notice error" role="alert">{{ $errors->first() }}</div>@endif
    @yield('content')
</main>
<footer class="footer wrap"><a href="{{ route('home',$locale) }}"><img src="{{ asset('images/masthead.webp') }}" width="240" height="80" alt="NewsBureau24" loading="lazy"></a><div class="footer-links">@foreach(['about','standards','corrections','privacy','contact'] as $p)<a href="{{ route('page',[$locale,$p]) }}">{{ __('ui.'.$p) }}</a>@endforeach<a href="/newsroom">Newsroom ↗</a></div><p>© {{ now()->year }} NewsBureau24 · FACTS | INVESTIGATIONS | A SAFER BANGLADESH</p></footer>
</body></html>
