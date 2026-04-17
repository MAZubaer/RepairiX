<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use App\Models\User;
$users = User::orderBy('id', 'desc')->take(10)->get();
foreach($users as $u) {
    echo "id={$u->id} name={$u->name} email={$u->email} phone={$u->phone} role={$u->role}\n";
}
