<?php

namespace App\Filament\Resources\Articles\Pages;

use App\Filament\Resources\Articles\ArticleResource;
use App\Services\ArticleWriter;
use App\Support\CleanHtml;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Tiptap\Editor;

class EditArticle extends EditRecord
{
    protected static string $resource = ArticleResource::class;

    #[Locked]
    public int $baseVersion = 0;

    public ?string $autosavedAt = null;

    protected function afterFill(): void
    {
        $this->baseVersion = $this->getRecord()->version;
    }

    public function updated($property): void
    {
        if (! str_starts_with($property, 'data.')) {
            return;
        }
        Gate::authorize('update', $this->getRecord());
        $data = $this->data;
        foreach (['bn', 'en'] as $locale) {
            if (isset($data['body'][$locale])) {
                $html = is_array($data['body'][$locale]) ? (new Editor)->setContent($data['body'][$locale])->getHTML() : $data['body'][$locale];
                $data['body'][$locale] = CleanHtml::sanitize($html);
            }
        }
        DB::table('editor_drafts')->updateOrInsert(['article_id' => $this->getRecord()->id, 'user_id' => auth()->id()],
            ['data' => json_encode($data), 'base_version' => $this->baseVersion, 'updated_at' => now(), 'created_at' => now()]);
        $this->autosavedAt = now()->format('H:i:s');
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $saved = app(ArticleWriter::class)->save($record, $data, auth()->user(), $this->baseVersion);
        $this->baseVersion = $saved->version;

        return $record->refresh();
    }

    public function getSubheading(): ?string
    {
        return $this->autosavedAt ? 'Working copy autosaved at '.$this->autosavedAt.'. Save changes to apply it to the article.' : 'Your working copy autosaves separately. Use Save changes to apply it; use Restore autosave after a connection interruption.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('restoreAutosave')->label('Restore autosave')->requiresConfirmation()->action(function () {
                Gate::authorize('update', $this->getRecord());
                $draft = DB::table('editor_drafts')->where('article_id', $this->getRecord()->id)->where('user_id', auth()->id())->first();
                if (! $draft) {
                    Notification::make()->title('No autosaved working copy')->warning()->send();

                    return;
                }
                $this->form->fill(json_decode($draft->data, true));
                $this->baseVersion = $draft->base_version;
                // Keep the working copy's original version to prevent overwriting a newer edit.
                Notification::make()->title('Working copy restored. Review it before saving.')->success()->send();
            }),
            DeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
