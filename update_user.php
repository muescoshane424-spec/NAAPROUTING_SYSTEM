<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'admin@naap.org')->first();
if ($user) {
    $user->update(['username' => 'admin']);
    echo "Username updated to: " . $user->username . PHP_EOL;
} else {
    echo "User not found" . PHP_EOL;
}
