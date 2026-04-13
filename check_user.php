<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::where('email', 'admin@naap.org')->first();
if ($user) {
    echo "User ID: " . $user->id . PHP_EOL;
    echo "Name: " . $user->name . PHP_EOL;
    echo "Email: " . $user->email . PHP_EOL;
    echo "Username: " . ($user->username ?? 'NULL') . PHP_EOL;
} else {
    echo "User not found" . PHP_EOL;
}
