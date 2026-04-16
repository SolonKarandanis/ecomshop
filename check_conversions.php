<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- PHP EXTENSIONS ---\n";
echo "GD: " . (extension_loaded('gd') ? "ENABLED" : "MISSING") . "\n";
echo "Imagick: " . (extension_loaded('imagick') ? "ENABLED" : "MISSING") . "\n";

echo "\n--- MEDIA LIBRARY CONFIG ---\n";
echo "Disk: " . config('media-library.disk_name') . "\n";
echo "Image Driver: " . config('media-library.image_driver') . "\n";
echo "Queue Connection: " . config('media-library.queue_connection_name') . "\n";
echo "Queue Conversions by Default: " . (config('media-library.queue_conversions_by_default') ? "YES" : "NO") . "\n";

echo "\n--- QUEUE CONFIG ---\n";
echo "Default Queue: " . config('queue.default') . "\n";

echo "\n--- TEST IMAGE PROCESSING (GD) ---\n";
if (extension_loaded('gd')) {
    $img = imagecreatetruecolor(100, 100);
    if ($img) {
        echo "Successfully created a test image resource using GD.\n";
        imagedestroy($img);
    } else {
        echo "Failed to create a test image resource using GD.\n";
    }
} else {
    echo "GD not loaded, cannot test image processing.\n";
}

echo "\n--- SUMMARY ---\n";
if (config('media-library.queue_conversions_by_default') && config('queue.default') === 'sync') {
    echo "WARNING: Conversions are queued but the queue is set to 'sync'. This SHOULD work, but sometimes it depends on the environment. If it doesn't, try setting QUEUE_CONVERSIONS_BY_DEFAULT=false in your .env.\n";
} elseif (config('media-library.queue_conversions_by_default') && config('queue.default') !== 'sync') {
     echo "NOTICE: Conversions are queued on '".config('queue.default')."'. If you don't have a worker running (like 'php artisan queue:work'), conversions will NEVER be generated.\n";
}
