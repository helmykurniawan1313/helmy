{{-- Photo tile: full-bleed image with a legible caption over a scrim --}}
@php $t = $p['banner']['tiles'][$key]; @endphp
<figure class="tile img-tile reveal {{ $class ?? '' }}">
    <img src="{{ asset($image) }}" alt="{{ $t['alt'] }}" loading="lazy" decoding="async" width="1400" height="933">
    <figcaption>
        <span class="img-tile-kicker">{{ $t['kicker'] }}</span>
        <span class="img-tile-title">{{ $t['title'] }}</span>
    </figcaption>
</figure>
