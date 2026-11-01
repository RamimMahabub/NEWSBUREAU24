<?php

namespace Tests\Feature;

use App\Filament\Pages\FrontpageBuilder;
use App\Filament\Resources\Articles\Pages\CreateArticle;
use App\Filament\Resources\Articles\Pages\EditArticle;
use App\Models\Article;
use App\Models\Frontpage;
use App\Models\User;
use App\Policies\MediaPolicy;
use App\Services\ArticleWriter;
use App\Services\FrontpageEditor;
use App\Services\MediaUploader;
use App\Support\CleanHtml;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class NewsroomTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'editor'): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function data(array $overrides = []): array
    {
        return array_replace_recursive(['title' => ['bn' => 'জনস্বার্থে অনুসন্ধান', 'en' => 'An investigation in the public interest'],
            'body' => ['bn' => '<p>যাচাইকৃত প্রতিবেদন।</p>', 'en' => '<p>Verified reporting.</p>'],
            'summary' => ['bn' => 'সংক্ষিপ্তসার', 'en' => 'Summary'], 'category' => 'investigations', 'status' => 'draft'], $overrides);
    }

    private function article(User $user, array $data = []): Article
    {
        return app(ArticleWriter::class)->save(null, $this->data($data), $user);
    }

    public function test_reporter_creates_review_draft_and_editor_publishes_bangla_only(): void
    {
        $reporter = $this->user('reporter');
        $editor = $this->user();
        $this->actingAs($reporter);
        $article = $this->article($reporter, ['status' => 'review']);
        $this->get($article->url('bn'))->assertNotFound();
        $this->actingAs($editor);
        $article = app(ArticleWriter::class)->save($article, $this->data(['status' => 'published', 'published_bn_at' => now()->toDateTimeString()]), $editor, 1);
        $this->get($article->url('bn'))->assertOk()->assertSee('জনস্বার্থে অনুসন্ধান')->assertSee('যাচাইকৃত প্রতিবেদন।');
        $this->get($article->url('en'))->assertNotFound();
        $this->get('/bn')->assertOk()->assertSee('জনস্বার্থে অনুসন্ধান');
        $this->assertSame(2, $article->revisions()->count());
    }

    public function test_reporter_cannot_publish_even_with_forged_form_data(): void
    {
        $reporter = $this->user('reporter');
        $this->actingAs($reporter);
        $this->expectException(HttpException::class);
        $this->article($reporter, ['status' => 'published', 'published_bn_at' => now()->toDateTimeString()]);
    }

    public function test_reporter_cannot_edit_another_reporters_story_or_live_homepage(): void
    {
        $one = $this->user('reporter');
        $two = $this->user('reporter');
        $article = $this->article($one);
        $this->actingAs($two)->get('/newsroom/articles/'.$article->id.'/edit')->assertNotFound();
        $this->get('/newsroom/frontpage-builder')->assertForbidden();
        $this->get('/bn?preview=1')->assertForbidden();
    }

    public function test_upload_decodes_reencodes_and_remains_private_until_published(): void
    {
        Storage::fake('local');
        $editor = $this->user();
        $this->actingAs($editor);
        $file = UploadedFile::fake()->image('evidence.jpg', 1200, 800);
        $media = app(MediaUploader::class)->upload($file, $editor);
        $this->assertSame('image/webp', $media->mime);
        Storage::disk('local')->assertExists($media->path);
        $this->assertSame($media->id, app(MediaUploader::class)->upload($file, $editor)->id);
        $this->get('/media/'.$media->id)->assertOk();
        $this->app['auth']->forgetGuards();
        $this->get('/media/'.$media->id)->assertNotFound();
        $this->article($editor, ['media_id' => $media->id, 'status' => 'published', 'published_bn_at' => now()->toDateTimeString()]);
        $this->get('/media/'.$media->id)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertFalse((new MediaPolicy)->delete($editor, $media));
    }

    public function test_disguised_executable_upload_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(MediaUploader::class)->upload(UploadedFile::fake()->createWithContent('photo.jpg', '<?php echo "unsafe";'), $this->user());
    }

    public function test_rich_text_uses_an_allowlist_to_remove_active_content(): void
    {
        $clean = CleanHtml::sanitize('<p onclick="alert(1)">Safe</p><script>alert(2)</script><a href="javascript:alert(3)">Bad</a><iframe src="https://evil.test"></iframe><img src="https://evil.test/x" onerror="alert(4)"><h2>Heading</h2>');
        foreach (['onclick', '<script', 'javascript:', 'iframe', 'onerror', 'evil.test'] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $clean);
        }
        $this->assertStringContainsString('<h2>Heading</h2>', $clean);
    }

    public function test_version_conflict_does_not_overwrite_another_editors_changes(): void
    {
        $editor = $this->user();
        $this->actingAs($editor);
        $article = $this->article($editor);
        app(ArticleWriter::class)->save($article, $this->data(['title' => ['bn' => 'প্রথম সম্পাদনা']]), $editor, 1);
        try {
            app(ArticleWriter::class)->save($article, $this->data(['title' => ['bn' => 'পুরোনো সম্পাদনা']]), $editor, 1);
            $this->fail('Expected a version conflict');
        } catch (ValidationException $e) {
            $this->assertSame('প্রথম সম্পাদনা', $article->fresh()->title['bn']);
        }
    }

    public function test_frontpage_draft_publish_history_and_stale_version_rejection(): void
    {
        $editor = $this->user();
        $this->actingAs($editor);
        $one = $this->article($editor, ['status' => 'published', 'published_bn_at' => now()->toDateTimeString()]);
        $two = $this->article($editor, ['status' => 'published', 'published_bn_at' => now()->toDateTimeString()]);
        Frontpage::create(['state' => 'live', 'layout' => ['lead' => [$one->id], 'top' => [$two->id]]]);
        $service = app(FrontpageEditor::class);
        $draft = $service->draft($editor);
        $layout = ['lead' => [$two->id], 'top' => [$one->id]];
        $saved = $service->save($layout, $draft->version, $editor);
        $this->assertSame([$one->id], Frontpage::where('state', 'live')->first()->layout['lead']);
        $service->save($layout, $saved->version, $editor, true);
        $this->assertSame([$two->id], Frontpage::where('state', 'live')->first()->layout['lead']);
        $this->assertSame(1, Frontpage::where('state', 'archived')->count());
        $this->expectException(ValidationException::class);
        $service->save($layout, 1, $editor, true);
    }

    public function test_unpublished_story_cannot_be_added_to_frontpage(): void
    {
        $editor = $this->user();
        $article = $this->article($editor);
        app(FrontpageEditor::class)->draft($editor);
        $this->expectException(ValidationException::class);
        app(FrontpageEditor::class)->save(['lead' => [$article->id], 'top' => []], 1, $editor);
    }

    public function test_scheduler_publishes_due_article_and_keeps_future_english_private(): void
    {
        $editor = $this->user();
        $article = $this->article($editor, ['status' => 'scheduled', 'published_bn_at' => now()->subMinute()->toDateTimeString(), 'published_en_at' => now()->addDay()->toDateTimeString()]);
        $this->artisan('articles:publish-due')->assertSuccessful();
        $this->assertSame('published', $article->fresh()->status);
        $this->get($article->url('bn'))->assertOk();
        $this->get($article->url('en'))->assertNotFound();
        $this->artisan('articles:publish-due')->expectsOutput('Published 0 due articles.')->assertSuccessful();
    }

    public function test_filament_form_creates_database_backed_story(): void
    {
        $reporter = $this->user('reporter');
        $this->actingAs($reporter);
        Livewire::test(CreateArticle::class)->fillForm($this->data())->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('articles', ['user_id' => $reporter->id, 'status' => 'draft']);
    }

    public function test_editor_autosave_is_recoverable_without_changing_live_article(): void
    {
        $editor = $this->user();
        $this->actingAs($editor);
        $article = $this->article($editor);
        $component = Livewire::test(EditArticle::class, ['record' => $article->id]);
        $component->set('data.title.bn', 'সংরক্ষিত খসড়া')->assertHasNoErrors();
        $this->assertDatabaseHas('editor_drafts', ['article_id' => $article->id, 'user_id' => $editor->id]);
        $this->assertSame('জনস্বার্থে অনুসন্ধান', $article->fresh()->title['bn']);
        Livewire::test(EditArticle::class, ['record' => $article->id])->callAction('restoreAutosave')->assertFormSet(['title.bn' => 'সংরক্ষিত খসড়া']);
    }

    public function test_frontpage_livewire_supports_keyboard_placement_and_restoration(): void
    {
        $editor = $this->user();
        $this->actingAs($editor);
        $one = $this->article($editor, ['status' => 'published', 'published_bn_at' => now()->toDateTimeString()]);
        $two = $this->article($editor, ['status' => 'published', 'published_bn_at' => now()->toDateTimeString()]);
        $original = Frontpage::create(['state' => 'live', 'layout' => ['lead' => [$one->id], 'top' => [$two->id]]]);
        Livewire::test(FrontpageBuilder::class)->call('addToLead', $two->id)->call('saveDraft')->assertHasNoErrors()->call('publish')->assertHasNoErrors()->call('restore', $original->id)->assertSet('positions.lead', [$one->id]);
    }

    public function test_search_seo_and_newsletter_consent(): void
    {
        $editor = $this->user();
        $article = $this->article($editor, ['status' => 'published', 'published_bn_at' => now()->toDateTimeString()]);
        $this->get('/bn/search?q=জনস্বার্থে&category=investigations')->assertOk()->assertSee('জনস্বার্থে অনুসন্ধান');
        $this->get($article->url('bn'))->assertOk()->assertSee('application/ld+json', false)->assertSee('NewsArticle');
        $this->post('/bn/newsletter', ['email' => 'reader@example.test'])->assertSessionHasErrors('consent');
        $this->post('/bn/newsletter', ['email' => 'reader@example.test', 'consent' => 1])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'reader@example.test', 'locale' => 'bn']);
    }
}
