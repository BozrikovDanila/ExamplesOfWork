<?php

namespace App\Filament\Resources\TemplateResource\Pages;

use App\Filament\Resources\TemplateResource;
use App\Services\TemplateFileMacros;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateTemplate extends CreateRecord
{
    protected static string $resource = TemplateResource::class;

    protected static bool $canCreateAnother = false;

    public array $temporaryState = [];

    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? self::getUrl();
    }

    public function getTitle(): string|Htmlable
    {
        return view('filament.required-macros', [
            'macros' => implode(', ', TemplateFileMacros::getRequiredHash()),
            'countMacros' => count(TemplateFileMacros::getRequiredHash()),
        ]);
    }

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }
}
