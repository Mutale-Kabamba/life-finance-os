<?php

namespace App\Filament\Resources\SavingsGoalResource\Pages;

use App\Filament\Resources\SavingsGoalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSavingsGoal extends CreateRecord
{
    protected static string $resource = SavingsGoalResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array { $data['user_id'] = auth()->id(); return $data; }
}
