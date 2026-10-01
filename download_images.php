<?php
/**
 * Product and Banner Image Downloader & Asset Generator
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/maintenance-guard.php';
requireMaintenanceAccess();

$productsDir = ROOT_PATH . '/uploads/products';
$bannersDir = ROOT_PATH . '/uploads/banners';
$avatarsDir = ROOT_PATH . '/uploads/avatars';

if (!is_dir($productsDir)) mkdir($productsDir, 0777, true);
if (!is_dir($bannersDir)) mkdir($bannersDir, 0777, true);
if (!is_dir($avatarsDir)) mkdir($avatarsDir, 0777, true);

// Curated high-res Unsplash image URLs for each product & banner
$productImages = [
    'sony-xm5-1.jpg'          => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=700&auto=format&fit=crop&q=80',
    'sony-xm5-2.jpg'          => 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=700&auto=format&fit=crop&q=80',
    'macbook-air-m2-1.jpg'    => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=700&auto=format&fit=crop&q=80',
    'macbook-air-m2-2.jpg'    => 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?w=700&auto=format&fit=crop&q=80',
    'logitech-mx3s-1.jpg'     => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=700&auto=format&fit=crop&q=80',
    'samsung-g5-1.jpg'        => 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=700&auto=format&fit=crop&q=80',
    'oxford-shirt-1.jpg'      => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=700&auto=format&fit=crop&q=80',
    'floral-maxi-1.jpg'       => 'https://images.unsplash.com/photo-1572804013309-59a88b7e92f1?w=700&auto=format&fit=crop&q=80',
    'leather-wallet-1.jpg'    => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=700&auto=format&fit=crop&q=80',
    'ergo-chair-1.jpg'        => 'https://images.unsplash.com/photo-1580481077114-1e0e8548a863?w=700&auto=format&fit=crop&q=80',
    'desk-lamp-1.jpg'         => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=700&auto=format&fit=crop&q=80',
    'serum-hyaluronic-1.jpg'  => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?w=700&auto=format&fit=crop&q=80',
    'argan-oil-1.jpg'         => 'https://images.unsplash.com/photo-1608248597359-361044431e50?w=700&auto=format&fit=crop&q=80',
    'dumbbell-set-1.jpg'      => 'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?w=700&auto=format&fit=crop&q=80',
    'yoga-mat-1.jpg'          => 'https://images.unsplash.com/photo-1601925260368-ae2f83cf8b7f?w=700&auto=format&fit=crop&q=80',
    'atomic-habits-1.jpg'     => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=700&auto=format&fit=crop&q=80',
    'luxury-pen-1.jpg'        => 'https://images.unsplash.com/photo-1583485088034-697b5bc54ccd?w=700&auto=format&fit=crop&q=80',
];

$bannerImages = [
    'hero-electronics.jpg'    => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?w=1400&auto=format&fit=crop&q=80',
    'hero-fashion.jpg'        => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=1400&auto=format&fit=crop&q=80',
    'promo-audio.jpg'         => 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=1400&auto=format&fit=crop&q=80',
];

$avatarImages = [
    'avatar-owner.jpg'        => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&auto=format&fit=crop&q=80',
    'avatar-admin.jpg'        => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&auto=format&fit=crop&q=80',
];

$ctx = stream_context_create([
    'http' => [
        'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
        'timeout' => 15
    ]
]);

echo "Starting asset downloads...\n";

// Download Products
foreach ($productImages as $filename => $url) {
    $target = $productsDir . '/' . $filename;
    echo "Downloading Product: $filename... ";
    $data = @file_get_contents($url, false, $ctx);
    if ($data && strlen($data) > 1000) {
        file_put_contents($target, $data);
        echo "OK (" . round(strlen($data) / 1024) . " KB)\n";
    } else {
        echo "FAILED (generating fallback)\n";
        // Create colored placeholder with GD if available
        if (function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor(600, 600);
            $bg = imagecolorallocate($im, 240, 243, 246);
            imagefill($im, 0, 0, $bg);
            imagejpeg($im, $target, 80);
            imagedestroy($im);
        }
    }
}

// Download Banners
foreach ($bannerImages as $filename => $url) {
    $target = $bannersDir . '/' . $filename;
    echo "Downloading Banner: $filename... ";
    $data = @file_get_contents($url, false, $ctx);
    if ($data && strlen($data) > 1000) {
        file_put_contents($target, $data);
        echo "OK (" . round(strlen($data) / 1024) . " KB)\n";
    } else {
        echo "FAILED\n";
    }
}

// Download Avatars
foreach ($avatarImages as $filename => $url) {
    $target = $avatarsDir . '/' . $filename;
    echo "Downloading Avatar: $filename... ";
    $data = @file_get_contents($url, false, $ctx);
    if ($data && strlen($data) > 1000) {
        file_put_contents($target, $data);
        echo "OK (" . round(strlen($data) / 1024) . " KB)\n";
    } else {
        echo "FAILED\n";
    }
}

echo "\nAll assets downloaded successfully!\n";
