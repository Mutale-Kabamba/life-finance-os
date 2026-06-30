<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Reusable CSV import/export table actions.
 *
 * Usage in a resource table():
 *   ->headerActions([
 *       CsvActions::export(['name' => 'Name', 'amount' => 'Amount'], 'expenses'),
 *       CsvActions::import(Expense::class, ['name' => 'Name', 'amount' => 'Amount'],
 *           fn () => ['user_id' => auth()->id()], ['amount']),
 *   ])
 */
class CsvActions
{
    /**
     * @param array<string, string> $columns  field => CSV header label
     */
    public static function export(array $columns, string $filename = 'export'): Action
    {
        return Action::make('exportCsv')
            ->label('Export CSV')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->action(function ($livewire) use ($columns, $filename): StreamedResponse {
                $query = $livewire->getFilteredTableQuery();
                $name  = $filename . '-' . now()->format('Ymd_His') . '.csv';

                return response()->streamDownload(function () use ($query, $columns): void {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, array_values($columns));

                    $query->chunk(500, function ($records) use ($out, $columns): void {
                        foreach ($records as $record) {
                            $row = [];
                            foreach (array_keys($columns) as $field) {
                                $value = data_get($record, $field);
                                $row[] = $value instanceof \DateTimeInterface
                                    ? $value->format('Y-m-d')
                                    : $value;
                            }
                            fputcsv($out, $row);
                        }
                    });

                    fclose($out);
                }, $name, ['Content-Type' => 'text/csv']);
            });
    }

    /**
     * @param class-string                 $model
     * @param array<string, string>        $columns   field => CSV header label
     * @param Closure|array<string, mixed> $defaults  attributes applied to every row (e.g. user_id)
     * @param array<int, string>           $numeric   fields to cast to float
     */
    public static function import(string $model, array $columns, Closure|array $defaults = [], array $numeric = []): Action
    {
        return Action::make('importCsv')
            ->label('Import CSV')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('gray')
            ->form([
                Forms\Components\FileUpload::make('file')
                    ->label('CSV file')
                    ->acceptedFileTypes(['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'])
                    ->disk('local')
                    ->directory('csv-imports')
                    ->required(),
                Forms\Components\Placeholder::make('expected')
                    ->label('Expected column headers')
                    ->content(implode(', ', array_values($columns))),
            ])
            ->action(function (array $data) use ($model, $columns, $defaults, $numeric): void {
                $relative = is_array($data['file']) ? reset($data['file']) : $data['file'];
                $path = Storage::disk('local')->path($relative);

                if (! is_file($path)) {
                    Notification::make()->title('Could not read the uploaded file.')->danger()->send();

                    return;
                }

                $handle = fopen($path, 'r');
                $header = fgetcsv($handle);

                if (! $header) {
                    fclose($handle);
                    Notification::make()->title('The file appears to be empty.')->danger()->send();

                    return;
                }

                $labelToField = array_flip($columns);
                $indexToField = [];
                foreach ($header as $index => $label) {
                    $label = trim((string) $label);
                    if (isset($labelToField[$label])) {
                        $indexToField[$index] = $labelToField[$label];
                    }
                }

                $base = is_callable($defaults) ? $defaults() : $defaults;
                $created = 0;
                $skipped = 0;

                while (($row = fgetcsv($handle)) !== false) {
                    if (count(array_filter($row, fn ($v) => $v !== null && $v !== '')) === 0) {
                        continue;
                    }

                    $attributes = $base;
                    foreach ($indexToField as $index => $field) {
                        $value = $row[$index] ?? null;
                        if (in_array($field, $numeric, true)) {
                            $value = ($value === null || $value === '') ? 0 : (float) $value;
                        }
                        $attributes[$field] = $value;
                    }

                    try {
                        $model::create($attributes);
                        $created++;
                    } catch (\Throwable) {
                        $skipped++;
                    }
                }

                fclose($handle);
                Storage::disk('local')->delete($relative);

                Notification::make()
                    ->title("Imported {$created} record(s)." . ($skipped ? " Skipped {$skipped}." : ''))
                    ->success()
                    ->send();
            });
    }
}
