<?php

namespace App\Providers;

use App\Models\Business;
use App\Models\BudgetItem;
use App\Models\DebtPayment;
use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\LedgerTransaction;
use App\Models\ReceivablePayment;
use App\Models\SavingsTransaction;
use App\Models\StockMovement;
use App\Observers\AccountObserver;
use App\Observers\AccountTransactionObserver;
use App\Observers\BudgetItemObserver;
use App\Observers\BusinessObserver;
use App\Observers\DebtPaymentObserver;
use App\Observers\LedgerTransactionObserver;
use App\Observers\ReceivablePaymentObserver;
use App\Observers\SavingsTransactionObserver;
use App\Observers\StockMovementObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LedgerTransaction::observe(LedgerTransactionObserver::class);
        Business::observe(BusinessObserver::class);
        StockMovement::observe(StockMovementObserver::class);
        DebtPayment::observe(DebtPaymentObserver::class);
        ReceivablePayment::observe(ReceivablePaymentObserver::class);
        BudgetItem::observe(BudgetItemObserver::class);
        SavingsTransaction::observe(SavingsTransactionObserver::class);
        AccountTransaction::observe(AccountTransactionObserver::class);
        Account::observe(AccountObserver::class);
    }
}
