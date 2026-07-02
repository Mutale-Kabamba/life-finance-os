<?php

namespace App\Filament\Resources\CashAtHandResource\Pages;

use App\Filament\Resources\CashAtHandResource;
use App\Models\Business;
use App\Models\CashAtHand;
use App\Models\LedgerAccount;
use App\Services\Accounting\CashAtHandService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;

class ListCashAtHand extends ListRecords
{
    protected static string $resource = CashAtHandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('depositToBank')
                ->label('Deposit to bank')
                ->icon('heroicon-o-building-library')
                ->color('warning')
                ->form([
                    Forms\Components\Select::make('business_id')
                        ->label('Business')
                        ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                        ->default(fn () => Business::query()->where('user_id', auth()->id())->value('id'))
                        ->required()
                        ->live()
                        ->searchable(),
                    Forms\Components\Select::make('counter_account_id')
                        ->label('Bank / asset account')
                        ->options(fn (Forms\Get $get) => $get('business_id')
                            ? LedgerAccount::query()->where('business_id', $get('business_id'))->where('type', 'asset')->orderBy('code')->pluck('name', 'id')
                            : [])
                        ->required()
                        ->searchable(),
                    Forms\Components\TextInput::make('amount')->numeric()->prefix('ZMW')->required()->minValue(0.01),
                    Forms\Components\DatePicker::make('date')->required()->default(now()),
                    Forms\Components\Textarea::make('description'),
                ])
                ->action(function (array $data): void {
                    $balance = CashAtHand::getBalanceAsAt((int) $data['business_id'], now()->toDateString());

                    if ((float) $data['amount'] > $balance) {
                        Notification::make()
                            ->danger()
                            ->title('Insufficient drawer balance')
                            ->body('Available: ZMW ' . number_format($balance, 2))
                            ->send();

                        return;
                    }

                    app(CashAtHandService::class)->depositToBank([
                        'business_id'        => (int) $data['business_id'],
                        'amount'             => $data['amount'],
                        'date'               => $data['date'],
                        'description'        => $data['description'] ?? 'Deposit to bank',
                        'counter_account_id' => $data['counter_account_id'],
                        'user_id'            => auth()->id(),
                    ]);

                    Notification::make()->success()->title('Deposited to bank')->send();
                }),

            Actions\Action::make('reconcileDay')
                ->label('Reconcile day')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    Forms\Components\Select::make('business_id')
                        ->label('Business')
                        ->options(fn () => Business::query()->where('user_id', auth()->id())->pluck('name', 'id'))
                        ->default(fn () => Business::query()->where('user_id', auth()->id())->value('id'))
                        ->required()
                        ->searchable(),
                    Forms\Components\DatePicker::make('date')->required()->default(now()),
                    Forms\Components\TextInput::make('actual_balance')
                        ->label('Counted cash')->numeric()->prefix('ZMW')->required(),
                    Forms\Components\Textarea::make('notes'),
                ])
                ->action(function (array $data): void {
                    $reconciliation = app(CashAtHandService::class)->reconcileDaily(
                        (int) $data['business_id'],
                        Carbon::parse($data['date']),
                        (float) $data['actual_balance'],
                        $data['notes'] ?? null,
                    );

                    Notification::make()
                        ->title($reconciliation->status === 'reconciled' ? 'Balanced' : 'Variance found')
                        ->body('Variance: ZMW ' . number_format((float) $reconciliation->variance, 2))
                        ->{$reconciliation->status === 'reconciled' ? 'success' : 'warning'}()
                        ->send();
                }),
        ];
    }
}
