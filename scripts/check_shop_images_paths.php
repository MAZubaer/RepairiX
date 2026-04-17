<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

$rows = DB::table('shop_images')
    ->select('image_id', 'shop_id', 'type', 'image_path', 'created_at')
    ->orderBy('image_id')
    ->get();

if ($rows->isEmpty()) {
    echo "No rows found in shop_images.\n";
    exit(0);
}

$invalid = 0;
$missing = 0;

foreach ($rows as $r) {
    $path = (string) $r->image_path;

    $hasLeadingSlash = str_starts_with($path, '/');
    $hasStoragePrefix = str_starts_with($path, 'storage/');
    $looksRelative = !$hasLeadingSlash && !$hasStoragePrefix;
    $exists = Storage::disk('public')->exists($path);

    if (!$looksRelative) {
        $invalid++;
    }
    if (!$exists) {
        $missing++;
    }

    echo json_encode([
        'image_id' => $r->image_id,
        'shop_id' => $r->shop_id,
        'type' => $r->type,
        'image_path' => $path,
        'format_ok' => $looksRelative,
        'exists_on_public_disk' => $exists,
    ], JSON_UNESCAPED_SLASHES) . PHP_EOL;
}

echo "---- SUMMARY ----\n";
echo "Total rows: " . $rows->count() . "\n";
echo "Invalid format rows: " . $invalid . "\n";
echo "Missing files rows: " . $missing . "\n";
