<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\BudgetItem;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\IncomeReceipt;
use App\Models\LedgerTransaction;
use App\Models\ReceivablePayment;
use App\Models\SavingsTransaction;
use App\Models\StockMovement;
use App\Observers\AccountObserver;
use App\Observers\AccountTransactionObserver;
use App\Observers\BudgetItemObserver;
use App\Observers\BusinessObserver;
use App\Observers\DebtObserver;
use App\Observers\DebtPaymentObserver;
use App\Observers\IncomeReceiptObserver;
use App\Observers\LedgerTransactionObserver;
use App\Observers\ReceivablePaymentObserver;
use App\Observers\SavingsTransactionObserver;
use App\Observers\StockMovementObserver;
use App\Http\Responses\FilamentLogoutResponse;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponse::class, FilamentLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LedgerTransaction::observe(LedgerTransactionObserver::class);
        Business::observe(BusinessObserver::class);
        Debt::observe(DebtObserver::class);
        StockMovement::observe(StockMovementObserver::class);
        DebtPayment::observe(DebtPaymentObserver::class);
        IncomeReceipt::observe(IncomeReceiptObserver::class);
        ReceivablePayment::observe(ReceivablePaymentObserver::class);
        BudgetItem::observe(BudgetItemObserver::class);
        SavingsTransaction::observe(SavingsTransactionObserver::class);
        AccountTransaction::observe(AccountTransactionObserver::class);
        Account::observe(AccountObserver::class);
    }
}
