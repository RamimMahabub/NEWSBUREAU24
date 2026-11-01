<?php

namespace App\Filament\Pages;

use App\Models\Article;
use App\Models\Frontpage;
use App\Services\FrontpageEditor;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Locked;

class FrontpageBuilder extends Page
{
    protected string $view = 'filament.pages.frontpage-builder';

    protected static ?string $navigationLabel = 'Frontpage';

    protected static ?string $title = 'The front page';

    protected static ?int $navigationSort = 3;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-newspaper';

    public array $positions = ['lead' => [], 'top' => []];

    #[Locked]
    public int $version = 0;

    public string $search = '';

    public array $undoStack = [];

    public array $redoStack = [];

    public string $saveStatus = 'Draft loaded';

    public static function canAccess(): bool
    {
        return auth()->user()?->canPublish() ?? false;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
        $draft = app(FrontpageEditor::class)->draft(auth()->user());
        $this->positions = $draft->layout;
        $this->version = $draft->version;
    }

    public function updateLayout(array $layout): void
    {
        $layout = app(FrontpageEditor::class)->validate($layout, auth()->user());
        $this->undoStack[] = $this->positions;
        $this->undoStack = array_slice($this->undoStack, -20);
        $this->redoStack = [];
        $this->positions = $layout;
        $this->saveStatus = 'Unsaved changes';
    }

    public function addToLead(int $id): void
    {
        $top = array_values(array_filter(array_merge($this->positions['lead'], $this->positions['top']), fn ($value) => $value !== $id));
        $this->updateLayout(['lead' => [$id], 'top' => array_slice($top, 0, 4)]);
    }

    public function addToTop(int $id): void
    {
        if (in_array($id, array_merge($this->positions['lead'], $this->positions['top']))) {
            return;
        }
        $this->updateLayout(['lead' => $this->positions['lead'], 'top' => array_slice(array_merge($this->positions['top'], [$id]), -4)]);
    }

    public function move(int $index, int $direction): void
    {
        abort_unless(in_array($direction, [-1, 1], true), 422);
        $layout = $this->positions;
        $target = $index + $direction;
        if (isset($layout['top'][$index],$layout['top'][$target])) {
            [$layout['top'][$index],$layout['top'][$target]] = [$layout['top'][$target], $layout['top'][$index]];
            $this->updateLayout($layout);
        }
    }

    public function remove(int $id): void
    {
        $layout = $this->positions;
        $layout['top'] = array_values(array_filter($layout['top'], fn ($value) => $value !== $id));
        $this->updateLayout($layout);
    }

    public function undo(): void
    {
        abort_unless(static::canAccess(), 403);
        if ($this->undoStack) {
            $this->redoStack[] = $this->positions;
            $this->positions = array_pop($this->undoStack);
            $this->saveStatus = 'Unsaved changes';
        }
    }

    public function redo(): void
    {
        abort_unless(static::canAccess(), 403);
        if ($this->redoStack) {
            $this->undoStack[] = $this->positions;
            $this->positions = array_pop($this->redoStack);
            $this->saveStatus = 'Unsaved changes';
        }
    }

    public function saveDraft(): void
    {
        $this->persist(false);
    }

    public function publish(): void
    {
        $this->persist(true);
    }

    protected function persist(bool $publish): void
    {
        $draft = app(FrontpageEditor::class)->save($this->positions, $this->version, auth()->user(), $publish);
        $this->version = $draft->version;
        $this->saveStatus = ($publish ? 'Published' : 'Draft saved').' at '.now()->format('H:i:s');
        Notification::make()->title($publish ? 'Front page published' : 'Draft saved')->success()->send();
    }

    public function restore(int $id): void
    {
        abort_unless(static::canAccess(), 403);
        $version = Frontpage::whereIn('state', ['archived', 'live'])->findOrFail($id);
        $this->updateLayout($version->layout);
        Notification::make()->title('Previous layout restored into your working copy. Save or publish when ready.')->success()->send();
    }

    protected function getViewData(): array
    {
        $selected = collect(array_merge($this->positions['lead'], $this->positions['top']));

        return ['selected' => Article::with('media')->whereIn('id', $selected)->get()->keyBy('id'),
            'library' => Article::published('bn')->with('media')->when($this->search, fn ($q) => $q->where('title->bn', 'like', '%'.mb_substr($this->search, 0, 150).'%'))->latest()->limit(20)->get(),
            'history' => Frontpage::whereIn('state', ['archived', 'live'])->latest('id')->limit(10)->get()];
    }
}
