<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Dropping extension_requests table...\n";

DB::statement('DROP TABLE IF EXISTS extension_requests');

echo "✓ Table dropped successfully\n";
