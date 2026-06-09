<?php
// File: api/logs.php
session_start();

// Restrict direct access unless APP_ENTRY defined
if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Akses Ditolak.');
}

$config = require __DIR__ . '/../config/config.php';
$enabled = $config['logging']['enable_site_selection_log'] ?? false;

// Determine log directory and base file
$relative = $config['logging']['site_selection_log_file'] ?? 'logs/site_selection.csv';
$logPath = dirname(__DIR__) . '/' . ltrim($relative, '/\\');
$logDir = dirname($logPath);

// Simple HTTP Basic auth using credentials from config
$adminUser = $config['logging']['admin_user'] ?? 'admin';
$adminPass = $config['logging']['admin_pass'] ?? '';

$authUser = null;
$authPass = null;
if (isset($_SERVER['PHP_AUTH_USER'])) {
    $authUser = $_SERVER['PHP_AUTH_USER'];
    $authPass = $_SERVER['PHP_AUTH_PW'] ?? '';
} else {
    $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if ($hdr && stripos($hdr, 'basic ') === 0) {
        $cred = base64_decode(substr($hdr, 6));
        if ($cred !== false) {
            [$u, $p] = array_pad(explode(':', $cred, 2), 2, '');
            $authUser = $u;
            $authPass = $p;
        }
    }
}

if ($authUser !== $adminUser || $authPass !== $adminPass) {
    header('WWW-Authenticate: Basic realm="Logs"');
    http_response_code(401);
    exit('Autentikasi diperlukan.');
}

// Ensure log dir exists
if (!is_dir($logDir)) {
    http_response_code(404);
    exit('Tidak ada log.');
}

$file = isset($_GET['file']) ? basename($_GET['file']) : '';
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

if ($action === 'download' && $file !== '') {
    $target = $logDir . '/' . $file;
    if (!is_file($target)) {
        http_response_code(404);
        exit('File tidak ditemukan.');
    }
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . basename($target) . '"');
    readfile($target);
    exit;
}

if ($action === 'view' && $file !== '') {
    $target = $logDir . '/' . $file;
    if (!is_file($target)) {
        http_response_code(404);
        exit('File tidak ditemukan.');
    }
    header('Content-Type: text/plain; charset=utf-8');
    readfile($target);
    exit;
}

// Default: list available CSV logs
$files = glob($logDir . '/*.csv');
usort($files, function ($a, $b) { return filemtime($b) <=> filemtime($a); });

?><!doctype html>
<html><head><meta charset="utf-8"><title>Logs</title></head><body>
<h1>Site Selection Logs</h1>
<p>Logging enabled: <?php echo $enabled ? 'yes' : 'no'; ?></p>
<ul>
<?php foreach ($files as $f): $name = basename($f); ?>
  <li><?php echo htmlspecialchars($name); ?> — <a href="?action=view&file=<?php echo urlencode($name); ?>">view</a> | <a href="?action=download&file=<?php echo urlencode($name); ?>">download</a></li>
<?php endforeach; ?>
</ul>
</body></html>
