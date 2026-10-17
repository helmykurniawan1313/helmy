@php
    app()->setLocale(request()->is('id', 'id/*') ? 'id' : 'en');
    $p = config('portfolio') + __('portfolio');
    $p['meta']['title'] = '404 · '.$p['ui']['not_found'].' | '.$p['name'];
@endphp
@extends('layouts.app')

@section('robots', 'noindex, follow')

@section('content')
<section class="banner hero" aria-labelledby="nf-title">
    <span class="glow g1"></span>
    <span class="glow g2"></span>
    <div class="container">
        <h1 id="nf-title" class="display-lg mb-3">{{ $p['ui']['not_found'] }}</h1>
        <p class="lead-xl mb-4">{{ $p['ui']['not_found_desc'] }}</p>
        <a href="{{ route($p['locales'][app()->getLocale()]['route']) }}" class="island-btn island-btn-primary">
            <i class="ph-light ph-arrow-left"></i> {{ $p['ui']['back_home'] }}
        </a>
    </div>
</section>
@endsection
