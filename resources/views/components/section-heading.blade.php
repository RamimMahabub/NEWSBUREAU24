@props(['name','english','href'=>null])
<div class="section-heading"><h2>{{ $name }} <span>{{ $english }}</span></h2>@if($href)<a href="{{ $href }}">{{ __('ui.all') }} →</a>@endif</div>
