<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Template;
use App\Services\DataBuilder;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditTemplate extends EditRecord
{
    protected static string $resource = TemplateResource::class;

    public array $temporaryState = [];

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->hidden(function (Template $record) {
                    return $record->events->isNotEmpty();
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /**
         * @var Template $record
         */
        $record = $this->record;

        if ($data['templates_file'] !== $record->templates_file) {
            if (\Storage::exists($record->templates_file)) {
                \Storage::delete($record->templates_file);
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /**
         * @var Template $record
         */
        $record = $this->record;
        $record->events->each(function (Event $event) use ($record) {
            $event->certificates->each(function (Certificate $certificate) use ($record) {
                if (\Storage::exists(DataBuilder::filePath($certificate->number))) {
                    \Storage::delete(DataBuilder::filePath($certificate->number));
                }

                if (\Storage::exists(DataBuilder::imageFilePath($certificate->number))) {
                    \Storage::delete(DataBuilder::imageFilePath($certificate->number));
                }

                $certificate->filled_macros += array_fill_keys(array_keys($record->macros), '');
                $certificate->save();
            });
        });
    }

    public function getRecordTitle(): string|Htmlable
    {
        return 'шаблона';
    }

    protected function getFormActions(): array
    {
        $certs = [];
        $events = $this->record?->events ?? [];
        foreach ($events as $event) {
            if ($ids = $event->certificates?->pluck('id')->toArray()) {
                $certs = $ids;
                break;
            }
        }
        if ($certs) {
            return [
                Action::make('saveWithConfirmation')
                    ->label('Сохранить')
                    ->requiresConfirmation()
                    ->modalHeading('Подтвердите сохранение')
                    ->modalDescription('Если шаблоном были добавлены новые макросы, то в связанных сертификатах они будут заполнены, как пустые. Продолжить?')
                    ->modalIconColor('secondary')
                    ->action(function () {
                        $this->save();
                    })->keyBindings(['mod+s']),
                $this->getCancelFormAction(),
            ];
        }

        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
