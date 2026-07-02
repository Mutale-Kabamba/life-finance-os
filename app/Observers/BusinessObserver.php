<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Business;
use App\Services\Accounting\ChartOfAccountsService;

class BusinessObserver
{
    public function __construct(private readonly ChartOfAccountsService $chartOfAccounts)
    {
    }

    public function created(Business $business): void
    {
        $this->chartOfAccounts->seedDefaults($business);
    }
}
