<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms;
use Illuminate\Contracts\Support\Htmlable;

class CompanySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-printer';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Printing';
    protected static ?string $title = 'Printing Settings';
    protected static string $view = 'filament.pages.company-settings';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->can('settings.manage');
    }

    public function mount(): void
    {
        $c = auth()->user()->company;
        $this->form->fill([
            'print_paper'       => $c->print_paper ?? 'a4',
            'print_orientation' => $c->print_orientation ?? 'portrait',
            'print_margin_mm'   => $c->print_margin_mm ?? 12,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('print_paper')
                ->label('Paper Size')
                ->options([
                    'a4'    => 'A4 (210×297mm)',
                    'letter'=> 'Letter (8.5×11")',
                    'a5'    => 'A5 (148×210mm)',
                    'pos80' => 'Thermal 80mm',
                    'pos58' => 'Thermal 58mm',
                ])->required(),
            Forms\Components\Select::make('print_orientation')
                ->label('Orientation')
                ->options(['portrait'=>'Portrait','landscape'=>'Landscape'])->required(),
            Forms\Components\TextInput::make('print_margin_mm')
                ->label('Margin (mm)')
                ->numeric()->minValue(0)->maxValue(25)->default(12)->required(),
            Forms\Components\View::make('prints._preview_hint')->columnSpanFull(),
        ])->statePath('data')->columns(3);
    }

    public function save(): void
    {
        $c = auth()->user()->company;
        $c->update([
            'print_paper'       => $this->data['print_paper'],
            'print_orientation' => $this->data['print_orientation'],
            'print_margin_mm'   => (int) $this->data['print_margin_mm'],
        ]);

        $this->notify('success', 'Printing settings saved.');
    }

    public function getHeading(): string|Htmlable
    {
        return self::$title;
    }
}
