<?php
// File: index.php
// Entry point / Router utama dengan security headers global

// 1. Matikan display errors di production untuk mencegah informasi server bocor
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// 2. Definisikan konstanta untuk memvalidasi akses internal
define('APP_ENTRY', true);

// 3. Security Headers Global
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; font-src 'self' https://cdn.jsdelivr.net; img-src 'self' data: https:; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

// 4. Routing Sederhana
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($requestUri, '/api/search.php') !== false || (isset($_GET['route']) && $_GET['route'] === 'api/search')) {
    // Arahkan ke backend API
    require __DIR__ . '/api/search.php';
} else {
    // Arahkan ke frontend view
    require __DIR__ . '/view/index.php';
}