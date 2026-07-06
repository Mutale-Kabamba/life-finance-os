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

class FinancialReports extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Business Finance';
    protected static ?string $navigationParentItem = 'Business Reports';
    protected static ?string $navigationLabel = 'Financial Reports';
    protected static ?int $navigationSort = 23;
    protected static string $view = 'filament.pages.financial-reports';

    public ?array $data = [];

    /** @var array<string, float>|null */
    public ?array $incomeStatement = null;

    /** @var array<string, float>|null */
    public ?array $balanceSheet = null;

    /** @var array<string, mixed>|null */
    public ?array $trialBalance = null;

    public function mount(): void
    {
        $this->form->fill([
            'business_id' => Business::query()->where('user_id', auth()->id())->value('id'),
            'start'       => now()->startOfYear()->toDateString(),
            'end'         => now()->endOfYear()->toDateString(),
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
                DatePicker::make('start')->label('From')->required(),
                DatePicker::make('end')->label('To')->required(),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function generate(): void
    {
        $data = $this->form->getState();

        if (empty($data['business_id'])) {
            return;
        }

        $service = app(LedgerSummaryService::class);
        $businessId = (int) $data['business_id'];

        $this->incomeStatement = $service->incomeStatement($businessId, $data['start'], $data['end']);
        $this->balanceSheet = $service->balanceSheet($businessId, $data['end']);
        $this->trialBalance = $service->trialBalance($businessId, $data['start'], $data['end']);
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

                    if (! $business || ! $this->incomeStatement || ! $this->balanceSheet || ! $this->trialBalance) {
                        return null;
                    }

                    return app(ReportPdfBuilder::class)->downloadFinancialReports(
                        $business,
                        (string) $data['start'],
                        (string) $data['end'],
                        $this->incomeStatement,
                        $this->balanceSheet,
                        $this->trialBalance,
                    );
                }),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return (bool) auth()->user()?->profile?->hasFeature('has_business');
    }
}
