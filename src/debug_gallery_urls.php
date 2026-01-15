<?php
if (!defined('BASE')) {
    define('BASE', __DIR__ . '/../');
}
require_once __DIR__ . '/config/Constants.php';
require_once BASE . 'vendor/autoload.php';

use App\Config\EloquentBootstrap;
use App\Models\Gallery;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(BASE);
$dotenv->load();

EloquentBootstrap::boot();

$output = "Debug: Checking Gallery URLs\n";
$output .= "APP_URL from env: " . ($_ENV['APP_URL'] ?? 'Not Set') . "\n";

$galleries = Gallery::all();
foreach ($galleries as $g) {
    $output .= "ID: " . $g->id . "\n";
    $output .= "Cover: " . $g->cover_image . "\n";
    $output .= "Images: " . json_encode($g->images) . "\n";
    $output .= "-------------------\n";
}

file_put_contents(__DIR__ . '/debug_output.txt', $output);
echo "Done.";

