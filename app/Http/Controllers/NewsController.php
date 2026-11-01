<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Frontpage;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function home(Request $request, string $locale)
    {
        app()->setLocale($locale);
        $frontpage = Frontpage::where('state', 'live')->latest('id')->first();
        if ($request->boolean('preview')) {
            abort_unless($request->user()?->canPublish(), 403);
            $frontpage = Frontpage::where('state', 'draft')->first() ?? $frontpage;
        }
        $articles = Article::published($locale)->cards()->with(['media', 'user'])->latest("published_{$locale}_at")->limit(60)->get();
        $layout = $frontpage?->layout ?? [];
        $pinned = Article::published($locale)->cards()->with(['media', 'user'])->whereIn('id', array_merge($layout['lead'] ?? [], $layout['top'] ?? []))->get();
        $articles = $articles->concat($pinned)->unique('id');
        $ordered = collect($layout['lead'] ?? [])->merge($layout['top'] ?? [])->map(fn ($id) => $articles->firstWhere('id', $id))->filter();
        $top = $ordered->concat($articles->whereNotIn('id', $ordered->pluck('id')))->take(5)->values();

        return response()->view('news.home', compact('articles', 'top', 'locale', 'layout'))
            ->header('Cache-Control', $request->boolean('preview') ? 'private, no-store' : 'public, max-age=0, must-revalidate');
    }

    public function article(string $locale, string $slug)
    {
        app()->setLocale($locale);
        $article = Article::published($locale)->with(['media', 'user'])->where('slug', $slug)->firstOrFail();
        $otherLocale = $locale === 'bn' ? 'en' : 'bn';
        $translated = Article::published($otherLocale)->whereKey($article->id)->exists();
        $related = Article::published($locale)->cards()->where('category', $article->category)->whereKeyNot($article->id)->with('media')->latest()->limit(3)->get();

        return view('news.article', compact('locale', 'article', 'related', 'translated', 'otherLocale'));
    }

    public function search(Request $request, string $locale)
    {
        app()->setLocale($locale);
        $data = $request->validate(['q' => 'nullable|string|max:150', 'category' => 'nullable|in:'.implode(',', array_keys(Article::CATEGORIES)), 'district' => 'nullable|string|max:80']);
        $query = Article::published($locale)->cards()->with(['media', 'user']);
        if ($data['q'] ?? null) {
            $query->where("title->{$locale}", 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $data['q']).'%');
        }
        if ($data['category'] ?? null) {
            $query->where('category', $data['category']);
        }
        if ($data['district'] ?? null) {
            $query->where('district', $data['district']);
        }
        $results = $query->latest("published_{$locale}_at")->paginate(12)->withQueryString();

        return view('news.search', compact('results', 'locale', 'data'));
    }

    public function page(string $locale, string $page)
    {
        app()->setLocale($locale);
        abort_unless(in_array($page, ['about', 'contact', 'standards', 'corrections', 'privacy', 'tips', 'newsletter'], true), 404);

        return view('news.page', compact('locale', 'page'));
    }

    public function subscribe(Request $request, string $locale)
    {
        $data = $request->validate(['email' => 'required|email:rfc|max:254', 'consent' => 'accepted']);
        DB::table('newsletter_subscribers')->updateOrInsert(['email' => mb_strtolower($data['email'])],
            ['locale' => $locale, 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('message', $locale === 'bn' ? 'আপনার আগ্রহ নিবন্ধিত হয়েছে। নিউজলেটার চালু হলে জানানো হবে।' : 'Your interest is registered. Delivery will begin when the newsletter launches.');
    }

    public function media(Request $request, Media $media)
    {
        $isPublic = $media->articles()->published('bn')->exists() || $media->articles()->published('en')->exists();
        abort_unless($isPublic || ($request->user() && ($request->user()->canPublish() || $request->user()->id === $media->user_id)), 404);
        abort_unless(Storage::disk('local')->exists($media->path), 404);

        return response()->file(Storage::disk('local')->path($media->path), ['Content-Type' => $media->mime,
            'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => $isPublic ? 'public, max-age=3600' : 'private, no-store']);
    }
}
