<?php
// File: scripts/clean_logs.php
// CLI helper to rotate and prune log archives for site_selection logs.

if (PHP_SAPI !== 'cli') {
    echo "This script is intended for CLI use only.\n";
    exit(1);
}

$root = dirname(__DIR__);
$config = require $root . '/config/config.php';

$relative = $config['logging']['site_selection_log_file'] ?? 'logs/site_selection.csv';
$logFile = $root . '/' . ltrim($relative, '/\\');
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    echo "Log directory does not exist: $logDir\n";
    exit(0);
}

$rotateDaily = $config['logging']['rotate_daily'] ?? false;
$maxSize = $config['logging']['max_size_bytes'] ?? 0;
$maxArchives = $config['logging']['max_archives'] ?? 7;

echo "Cleaning logs for: $logFile\n";

if (is_file($logFile)) {
    clearstatcache(true, $logFile);
    if ($rotateDaily) {
        $fileMTime = filemtime($logFile);
        $startOfToday = strtotime('today');
        if ($fileMTime !== false && $fileMTime < $startOfToday) {
            $archive = $logFile . '.' . date('Ymd_His', $fileMTime) . '.csv';
            if (@rename($logFile, $archive)) {
                echo "Rotated (daily) to: $archive\n";
            }
        }
    }
    if ($maxSize > 0 && filesize($logFile) >= $maxSize) {
        $archive = $logFile . '.' . date('Ymd_His') . '.csv';
        if (@rename($logFile, $archive)) {
            echo "Rotated (size) to: $archive\n";
        }
    }

    // Prune archives
    $pattern = $logFile . '.*.csv';
    $archives = glob($pattern) ?: [];
    usort($archives, function ($a, $b) { return filemtime($b) <=> filemtime($a); });
    if (count($archives) > $maxArchives) {
        $toDelete = array_slice($archives, $maxArchives);
        foreach ($toDelete as $f) {
            if (@unlink($f)) {
                echo "Deleted old archive: $f\n";
            }
        }
    }
} else {
    echo "No active log file to rotate.\n";
}

echo "Done.\n";
