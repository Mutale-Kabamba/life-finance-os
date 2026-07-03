<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\StockMovement;

class StockMovementObserver
{
    /**
     * Apply the movement to the product's stock-on-hand.
     */
    public function created(StockMovement $movement): void
    {
        $movement->inventory?->increment('quantity_on_hand', $movement->signedQuantity());
    }

    /**
     * Reverse the movement when it is deleted.
     */
    public function deleted(StockMovement $movement): void
    {
        $movement->inventory?->decrement('quantity_on_hand', $movement->signedQuantity());
    }
}
