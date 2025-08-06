<?php

namespace Tests\Feature;

use App\Filament\Resources\TemplateResource;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Template;
use App\Services\DataBuilder;
use Filament\Actions\DeleteAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\AdminTestCase;

class AdminTemplateTest extends AdminTestCase
{
    public function test_the_template_access(): void
    {
        $this->get(TemplateResource::getUrl())->assertSuccessful();
    }

    public function test_the_template_create_page(): void
    {
        $this->get(TemplateResource::getUrl('create'))->assertSuccessful();
    }

    public function test_the_template_edit_page(): void
    {
        $template = Template::factory()->create();
        $this->get(TemplateResource::getUrl('edit', [
            'record' => $template,
        ]))->assertSuccessful();

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }
    }

    public function test_the_template_view_page(): void
    {
        $template = Template::factory()->create();
        $this->get(TemplateResource::getUrl('view', [
            'record' => $template,
        ]))->assertSuccessful();
        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }
    }

    public function test_the_template_view_with_event_page(): void
    {
        $template = Template::factory()->has(Event::factory())->create();
        $this->get(TemplateResource::getUrl('view', [
            'record' => $template,
        ]))->assertSuccessful();

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }
    }

    public function test_the_template_add(): void
    {
        $template = Template::factory()->make();

        $file = UploadedFile::fake()->createWithContent('template.html', Storage::disk('local')->get($template->templates_file));
        $data = Livewire::test(TemplateResource\Pages\CreateTemplate::class)->setProperty(
            'data.templates_file',
            $file,
        )->fillForm(
            [
                'name' => $template->name,
                'rotated' => $template->rotated,
                'macros' => $template->macros,
            ]
        )->call('create')->assertHasNoFormErrors();

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }

        if (isset($data->getData()['data']['templates_file'][0]) && Storage::disk('local')->exists($data->getData()['data']['templates_file'][0])) {
            Storage::disk('local')->delete($data->getData()['data']['templates_file'][0]);
        }
    }

    public function test_the_template_add_error(): void
    {
        $template = Template::factory()->make();

        $file = UploadedFile::fake()->createWithContent('template-error.html', Storage::disk('local')->get('tests/template-error.html'));
        Livewire::test(TemplateResource\Pages\CreateTemplate::class)->setProperty(
            'data.templates_file',
            $file,
        )->fillForm(
            [
                'name' => $template->name,
                'rotated' => $template->rotated,
                'macros' => $template->macros,
            ]
        )->call('create')->assertHasFormErrors();

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }

        if (Storage::disk('local')->exists($file->getRealPath())) {
            Storage::disk('local')->delete($file->getRealPath());
        }
    }

    public function test_the_template_get_edit(): void
    {
        $template = Template::factory()->create();

        Livewire::test(
            TemplateResource\Pages\EditTemplate::class,
            [
                'record' => $template->getRouteKey(),
            ]
        )->assertFormSet([
            'name' => $template->name,
            'rotated' => $template->rotated,
            'macros' => $template->macros,
        ])->assertActionExists(DeleteAction::class);

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }
    }

    public function test_the_template_delete(): void
    {
        $template = Template::factory()->create();

        Livewire::test(
            TemplateResource\Pages\EditTemplate::class,
            [
                'record' => $template->getRouteKey(),
            ]
        )->callAction(DeleteAction::class);

        $this->assertModelMissing($template);

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }
    }

    public function test_the_template_edit_with_certificates(): void
    {
        $template = Template::factory()->has(Event::factory()->has(Certificate::factory()))->create();
        $certNumber = $template->events->first()->certificates->first()->number;
        DataBuilder::createPdf($certNumber);

        Livewire::test(TemplateResource\Pages\EditTemplate::class, [
            'record' => $template->getRouteKey(),
        ])->setProperty(
            'data.templates_file',
            UploadedFile::fake()->createWithContent('template.html', Storage::disk('local')->get($template->templates_file))
        )->setActionData([
            'name' => $template->name,
            'rotated' => $template->rotated,
            'macros' => $template->macros,
        ])->call('save')
            ->assertHasNoFormErrors();

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }

        if (Storage::disk('local')->exists(DataBuilder::filePath($certNumber))) {
            Storage::disk('local')->delete(DataBuilder::filePath($certNumber));
        }
    }

    public function test_the_edit_template_delete_with_event_page(): void
    {
        $template = Template::factory()->has(Event::factory())->create();

        Livewire::test(TemplateResource\Pages\EditTemplate::class, [
            'record' => $template->getRouteKey(),
        ])->assertActionDisabled(DeleteAction::class);

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }
    }

    public function test_the_view_template_delete_with_event_page(): void
    {
        $template = Template::factory()->has(Event::factory())->create();

        Livewire::test(
            TemplateResource\Pages\ListTemplates::class,
        )->assertTableActionDisabled(DeleteAction::class, $template);

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }
    }

    public function test_the_view_template_delete_page(): void
    {
        $template = Template::factory()->create();

        Livewire::test(
            TemplateResource\Pages\ListTemplates::class,
        )->callTableAction(DeleteAction::class, $template);

        $this->assertModelMissing($template);

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }
    }

    public function test_the_template_delete_with_event_page(): void
    {
        $template = Template::factory()->has(Event::factory())->create();

        Livewire::test(
            TemplateResource\Pages\EditTemplate::class,
            [
                'record' => $template->getRouteKey(),
            ]
        )->assertActionDisabled(DeleteAction::class);

        if (Storage::disk('local')->exists($template->templates_file)) {
            Storage::disk('local')->delete($template->templates_file);
        }
    }

    public function test_the_template_after_save_deletes_certificate_files_and_updates_macros(): void
    {
        $certificate = Certificate::factory()->create();

        $pdfPath = DataBuilder::filePath($certificate->number);
        $imgPath = DataBuilder::imageFilePath($certificate->number);

        Storage::disk('local')->put($pdfPath, 'pdf #PARTICIPANT# #EVENT# #DATE#');
        Storage::disk('local')->put($imgPath, 'img');

        Livewire::test(TemplateResource\Pages\EditTemplate::class, [
            'record' => $certificate->event->template->getRouteKey(),
        ])->call('save');

        $this->assertFalse(Storage::disk('local')->exists($pdfPath), 'PDF file should be deleted');
        $this->assertFalse(Storage::disk('local')->exists($imgPath), 'Image file should be deleted');

        if (Storage::disk('local')->exists($certificate->event->template->templates_file)) {
            Storage::disk('local')->delete($certificate->event->template->templates_file);
        }

        Storage::disk('local')->delete($pdfPath);
        Storage::disk('local')->delete($imgPath);

    }
}
