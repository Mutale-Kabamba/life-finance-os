<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot framework.
$kernel->handle(Illuminate\Http\Request::create('/admin/login', 'GET'));

// Enter the admin panel context so filament() helpers resolve.
Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel('admin'));

$user = App\Models\User::query()->whereNotNull('email_verified_at')->first();
Illuminate\Support\Facades\Auth::loginUsingId($user->id);
Filament\Facades\Filament::setTenant(null);

try {
    $theme = view('filament.sidebar-theme')->render();
    echo 'THEME view OK, bytes='.strlen($theme).', has border css: '.(str_contains($theme, 'border-inline-start-color') ? 'YES' : 'no').PHP_EOL;

    $footer = view('filament.sidebar-footer')->render();
    echo 'FOOTER view OK, bytes='.strlen($footer).PHP_EOL;
    echo 'FOOTER has lfos-sidebar-user: '.(str_contains($footer, 'lfos-sidebar-user') ? 'YES' : 'no').PHP_EOL;
    echo 'FOOTER has avatar/name for '.$user->email.': '.(str_contains($footer, 'lfos-user-name') ? 'YES' : 'no').PHP_EOL;
} catch (\Throwable $e) {
    echo 'ERROR: '.$e->getMessage().PHP_EOL.$e->getFile().':'.$e->getLine().PHP_EOL;
}
