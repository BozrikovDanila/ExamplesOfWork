<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\TemplateView;
use App\Filament\Resources\TemplateResource\Pages;
use App\Models\Template;
use App\Services\TemplateFileMacros;
use Closure;
use Filament\Actions\StaticAction;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $modelLabel = 'шаблон';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Название шаблона')
                    ->unique(ignoreRecord: true)
                    ->afterStateUpdated(function ($state) {
                        return trim($state);
                    })
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2)
                    ->live(),
                Forms\Components\Toggle::make('rotated')
                    ->default(1)
                    ->label('Альбомная')
                    ->columnSpan(2)
                    ->required()
                    ->inline(false)
                    ->onColor('primary')
                    ->offColor('danger')
                    ->onIcon('heroicon-o-check')
                    ->offIcon('heroicon-o-minus')
                    ->live(),
                Forms\Components\Fieldset::make()->label('Макросы из файла')->schema([
                    Forms\Components\Section::make('Макросы')
                        ->description('Заполните описание макросов')
                        ->schema(function (Forms\Get $get, $livewire): array {
                            if (!$get('templates_file')) {
                                $livewire->temporaryState = $get('macros') ?? [];

                                return [Forms\Components\Placeholder::make('')->content('Загрузите файл шаблона')->columnSpan(2)];
                            }

                            $inputs = [];

                            foreach (array_keys($get('macros') ?? []) as $field_name) {
                                $inputs[] = Forms\Components\TextInput::make("macros.$field_name")
                                    ->label(strtoupper($field_name))
                                    ->validationAttribute(strtoupper($field_name))
                                    ->name($field_name)
                                    ->placeholder("Например: макрос $field_name - это имя участника; и тп")
                                    ->required();
                            }

                            return $inputs;
                        })
                        ->collapsible()
                        ->columnSpan(2)
                        ->headerActions([
                            Action::make('open_template_modal')
                                ->label('Предпросмотр шаблона')
                                ->icon('heroicon-o-eye')
                                ->color('green')
                                ->modalWidth('7xl')
                                ->modalHeading(static fn (Get $get) => $get('name') ? 'Предпросмотр "' . trim($get('name')) . '"' : 'Предпросмотр шаблона')
                                ->form(function ($form, Get $get) {
                                    $templatesFile = $get('templates_file');
                                    $rotated = $get('rotated');

                                    $file = $templatesFile ? array_pop($templatesFile) : null;

                                    if ($file && is_string($file)) {
                                        $fileName = $file;
                                    } else {
                                        $fileName = $file ? 'livewire-tmp/' . $file->getFileName() : null;
                                    }

                                    return [
                                        'template' => TemplateView::make('template')
                                            ->viewData([
                                                'templatesFile' => $fileName,
                                                'templatesMacros' => [],
                                                'templatesRotated' => $rotated,
                                            ]),
                                    ];
                                })
                                ->modalCancelAction(false)
                                ->modalSubmitAction(false)
                                ->modalFooterActions([
                                    StaticAction::make('cancel')
                                        ->button()
                                        ->label('Закрыть')
                                        ->close()
                                        ->color('green'),
                                ])
                                ->visible(fn (Get $get) => $get('templates_file')),
                        ]),
                    Forms\Components\FileUpload::make('templates_file')
                        ->label('Файл шаблона')
                        ->maxFiles(1)
                        ->visibility('private')
                        ->rule(fn (): Closure => function (string $attribute, $value, Closure $fail) {
                            $fileMacros = new TemplateFileMacros($value->get());

                            if (!$fileMacros->getLeftRequired()) {
                                return;
                            }

                            $leftMacros = implode(', ', $fileMacros->getLeftRequiredHash());
                            $fail("Загрузите файл с корректными макросами, нужно добавить $leftMacros");
                        })
                        ->acceptedFileTypes(['text/html', 'application/octet-stream'])
                        ->directory('templates')
                        ->required()
                        ->downloadable()
                        ->afterStateUpdated(function ($state, Forms\Set $set, $livewire) {
                            if (is_string($state)) {
                                return;
                            }
                            $fileMacros = new TemplateFileMacros($state?->get());

                            if ($fileMacros->getLeftRequired()) {
                                $leftMacros = implode(', ', $fileMacros->getLeftRequiredHash());

                                Notification::make()
                                    ->title('В шаблоне не хватает обязательных макросов')
                                    ->body("Добавьте макросы $leftMacros
                                    и загрузите файл заново")
                                    ->persistent()
                                    ->danger()
                                    ->send();
                            }

                            if ($livewire->temporaryState) {
                                $filledMacros = array_replace(
                                    $fileMacros->getShowFill(),
                                    array_intersect_key(
                                        $livewire->temporaryState,
                                        $fileMacros->getShowFill()
                                    )
                                );

                                $set('macros', $filledMacros);
                                $livewire->temporaryState = [];
                            } else {
                                $set('macros', $fileMacros->getShowFill());
                            }
                        })
                        ->columnSpan(2),
                ])->columns(4),
            ])->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('Не найдено шаблонов')
            ->emptyStateDescription('Добавьте первый шаблон для старта')
            ->searchPlaceholder('Поиск по названию')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable(),
                Tables\Columns\IconColumn::make('rotated')
                    ->label('Альбомная')
                    ->color('primary')
                    ->boolean(),
                Tables\Columns\TextColumn::make('templates_file')
                    ->label('Файл шаблона')
                    ->alignCenter()
                    ->action(static fn (Template $record) => Storage::download($record->templates_file, (str_replace(' ', '_', trim($record->name ?? 'Template'))) . '.html'))
                    ->icon('heroicon-s-document-arrow-down')
                    ->extraAttributes([
                        'class' => 'custom-hover-highlight',
                    ])
                    ->color('primary')
                    ->formatStateUsing(static fn (Template $record) => (str_replace(' ', '_', trim($record->name ?? 'Template'))) . '.html')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Дата редактирования')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('ID', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('open_template_modal')
                    ->label('')
                    ->icon('heroicon-s-eye')
                    ->size(ActionSize::ExtraLarge)
                    ->color('green')
                    ->modalHeading(static fn (Template $record) => $record->name ? 'Предпросмотр "' . trim($record->name) . '"' : 'Предпросмотр шаблона')
                    ->modalWidth('7xl')
                    ->form(function (Template $record) {
                        $templatesFile = $record->templates_file;
                        $templatesMacros = $record->macros;
                        $rotated = $record->rotated;

                        return [
                            'template' => TemplateView::make('template')
                                ->viewData([
                                    'templatesFile' => $templatesFile,
                                    'templatesMacros' => [],
                                    'templatesRotated' => $rotated,
                                ]),
                        ];
                    })
                    ->modalCancelAction(false)
                    ->modalSubmitAction(false)
                    ->modalFooterActions([
                        StaticAction::make('cancel')
                            ->button()
                            ->label('Закрыть')
                            ->close()
                            ->color('green'),
                    ])
                    ->visible(fn (Template $record) => $record->templates_file),
                Tables\Actions\DeleteAction::make()
                    ->hidden(function (Template $record) {
                        return $record->events->isNotEmpty();
                    })
                    ->label(''),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'view' => Pages\ViewTemplate::route('/{record}'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }

    public static function getPluralLabel(): ?string
    {
        $locale = app()->getLocale();
        if ($locale === 'ru') {
            return 'Шаблоны';
        }

        return 'Templates';
    }
}
