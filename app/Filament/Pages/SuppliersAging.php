<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Business;
use App\Support\ReportPdfBuilder;
use App\Services\Accounting\LedgerSummaryService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class SuppliersAging extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationParentItem = 'Business Reports';
    protected static ?string $navigationLabel = 'Suppliers Aging';
    protected static ?int $navigationSort = 24;
    protected static string $view = 'filament.pages.suppliers-aging';

    public ?array $data = [];

    /** @var array<string, mixed>|null */
    public ?array $report = null;

    public function mount(): void
    {
        $this->form->fill([
            'business_id' => Business::query()->where('user_id', auth()->id())->value('id'),
            'as_of'       => now()->toDateString(),
        ]);

        $this->generate();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('business_id')
                    ->label('Business')
                    ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                    ->required(),
                DatePicker::make('as_of')->label('As of')->required(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        if (empty($data['business_id'])) {
            return;
        }

        $this->report = app(LedgerSummaryService::class)
            ->suppliersAging((int) $data['business_id'], $data['as_of']);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Generate')
                ->icon('heroicon-o-arrow-path')
                ->action('generate'),
            Action::make('downloadPdf')
                ->label('PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $this->generate();

                    $data = $this->form->getState();
                    $business = Business::query()->where('user_id', auth()->id())->find((int) ($data['business_id'] ?? 0));

                    if (! $business || ! $this->report) {
                        return null;
                    }

                    return app(ReportPdfBuilder::class)->downloadSuppliersAging(
                        $business,
                        (string) $data['as_of'],
                        $this->report,
                    );
                }),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_business');
    }
}
