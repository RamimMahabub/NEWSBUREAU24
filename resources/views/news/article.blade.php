@extends('news.layout')
@section('title', $article->text('title').' | NewsBureau24')
@section('description', $article->text('summary'))
@push('head')
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'NewsArticle','headline'=>$article->text('title'),'description'=>$article->text('summary'),'image'=>[$article->image()],'datePublished'=>$article->{'published_'.$locale.'_at'}->toIso8601String(),'dateModified'=>$article->updated_at->toIso8601String(),'author'=>['@type'=>'Person','name'=>$article->user->name],'publisher'=>['@type'=>'Organization','name'=>'NewsBureau24'],'inLanguage'=>$locale,'mainEntityOfPage'=>$article->url()], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) !!}</script>
@endpush
@section('content')
<article class="article-page"><a class="category-label" href="{{ route('search',['locale'=>$locale,'category'=>$article->category]) }}">{{ __('ui.'.$article->category) }}</a><h1>{{ $article->text('title') }}</h1><p class="article-summary">{{ $article->text('summary') }}</p><div class="article-byline">{{ $article->is_demo ? __('ui.sample') : $article->user->name }} · <time datetime="{{ $article->{'published_'.$locale.'_at'}->toIso8601String() }}">{{ $article->{'published_'.$locale.'_at'}->locale($locale)->translatedFormat('j F Y, H:i') }}</time> · {{ max(1, (int)ceil(count(preg_split('/\s+/u',strip_tags($article->text('body'))))/180)) }} {{ __('ui.minutes') }}</div>
@if($article->is_demo)<div class="notice">{{ __('ui.demo') }}</div>@endif
<figure><img class="article-cover" src="{{ $article->image() }}" width="850" height="480" alt="{{ $article->media?->alt[$locale] ?? ($article->is_demo ? __('ui.sample') : $article->text('title')) }}" fetchpriority="high"><figcaption>{{ $article->media?->credit ?? ($article->is_demo ? __('ui.sample').' · Supplied visual reference' : '') }}</figcaption></figure>
@if($article->category==='video' && $article->is_demo)<div class="notice">{{ $locale==='bn' ? 'এটি ভিডিও প্রতিবেদনের নকশার নমুনা। কোনো ভিডিও ফাইল সরবরাহ করা হয়নি।' : 'This is a video story layout sample. No video file was supplied.' }}</div>@endif
<div class="article-body">{!! \App\Support\CleanHtml::sanitize($article->text('body')) !!}</div>
@if($translated)<p><a class="red-button" href="{{ $article->url($otherLocale) }}">{{ $otherLocale==='en' ? 'Read in English' : 'বাংলায় পড়ুন' }} →</a></p>@endif
<div style="margin-top:35px"><x-section-heading :name="__('ui.related')" english="RELATED REPORTING"/>@foreach($related as $story)<x-story :story="$story"/>@endforeach</div></article>
@endsection
