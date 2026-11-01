@props(['story','variant'=>'compact'])
<article class="story {{ $variant }}">
    <a class="story-image" href="{{ $story->url() }}"><img src="{{ $story->image() }}" alt="{{ $story->media?->alt[app()->getLocale()] ?? ($story->is_demo ? __('ui.sample') : $story->text('title')) }}" width="400" height="260" loading="lazy"></a>
    <div><a class="category-label" href="{{ route('search',['locale'=>app()->getLocale(),'category'=>$story->category]) }}">{{ __('ui.'.$story->category) }}</a><h3><a href="{{ $story->url() }}">{{ $story->text('title') }}</a></h3><p class="metadata">{{ $story->is_demo ? __('ui.sample') : $story->user->name }} <span>·</span> {{ $story->{'published_'.app()->getLocale().'_at'}?->format('H:i') }}</p></div>
</article>
