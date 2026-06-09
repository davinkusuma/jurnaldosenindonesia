<?php
// File: api/search.php
// Backend handler dengan validasi ketat, sanitasi, dan rate limiting

session_start();

// Mencegah akses langsung jika tidak melalui router atau validasi internal
if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit(json_encode(['error' => 'Akses Ditolak.'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
}

$config = require __DIR__ . '/../config/config.php';

// Default SerpApi config
$apiKey = $config['serpapi']['api_key'] ?? '';
$engine = $config['serpapi']['engine'] ?? 'google_scholar';
$hl = $config['serpapi']['hl'] ?? 'id';
$gl = $config['serpapi']['gl'] ?? 'id';

// Helper: log site selection to a simple log file (non-blocking)
function log_site_selection(array $site, string $selector): void
{
    global $config;
    $enabled = $config['logging']['enable_site_selection_log'] ?? false;
    if (!$enabled) {
        return;
    }
    $relative = $config['logging']['site_selection_log_file'] ?? 'logs/site_selection.csv';
    $logFile = dirname(__DIR__) . '/' . ltrim($relative, '/\\');
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $time = date('c');
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $matchedLabel = $site['label'] ?? '';
    $matchedUrl = $site['url'] ?? '';

    // Rotation settings
    $rotateDaily = $config['logging']['rotate_daily'] ?? false;
    $maxSize = $config['logging']['max_size_bytes'] ?? 0;

    // Rotate by day
    if (is_file($logFile)) {
        clearstatcache(true, $logFile);
        if ($rotateDaily) {
            $fileMTime = filemtime($logFile);
            $startOfToday = strtotime('today');
            if ($fileMTime !== false && $fileMTime < $startOfToday) {
                $archive = $logFile . '.' . date('Ymd_His', $fileMTime) . '.csv';
                @rename($logFile, $archive);
            }
        }
        // Rotate by size
        if ($maxSize > 0 && filesize($logFile) >= $maxSize) {
            $archive = $logFile . '.' . date('Ymd_His') . '.csv';
            @rename($logFile, $archive);
        }
        // Prune old archives, keep only the newest N archives
        $maxArchives = $config['logging']['max_archives'] ?? 7;
        if ($maxArchives > 0) {
            $pattern = $logFile . '.*.csv';
            $archives = glob($pattern) ?: [];
            usort($archives, function ($a, $b) { return filemtime($b) <=> filemtime($a); });
            if (count($archives) > $maxArchives) {
                $toDelete = array_slice($archives, $maxArchives);
                foreach ($toDelete as $f) {
                    @unlink($f);
                }
            }
        }
    }

    // Ensure CSV header exists
    $needHeader = !is_file($logFile) || filesize($logFile) === 0;
    $fp = @fopen($logFile, 'a');
    if ($fp === false) {
        return;
    }
    if ($needHeader) {
        fputcsv($fp, ['time', 'selector', 'matched_label', 'matched_url', 'client_ip', 'user_agent']);
    }
    fputcsv($fp, [$time, $selector, $matchedLabel, $matchedUrl, $clientIp, $userAgent]);
    fclose($fp);
}

// Allow selecting a configured site (by label) to override API key or other options
// Support selecting a configured site via `site_label`, `site_url`, or `site` (alias)
$siteLabel = isset($_GET['site_label']) ? trim($_GET['site_label']) : '';
$siteUrl = isset($_GET['site_url']) ? trim($_GET['site_url']) : '';
$siteAlias = isset($_GET['site']) ? trim($_GET['site']) : '';

$matched = false;
// Match by exact URL when provided (validate first)
if ($siteUrl !== '' && filter_var($siteUrl, FILTER_VALIDATE_URL)) {
    foreach ($config['sites'] ?? [] as $site) {
        if (!empty($site['url']) && rtrim(strtolower($site['url']), '/') === rtrim(strtolower($siteUrl), '/')) {
            $apiKey = $site['api_key'] ?? $apiKey;
            $engine = $site['engine'] ?? $engine;
            $hl = $site['hl'] ?? $hl;
            $gl = $site['gl'] ?? $gl;
            $matched = true;
            // Log selection
            try {
                log_site_selection($site, $siteUrl);
            } catch (Throwable $e) {
                // ignore logging failures
            }
            break;
        }
    }
}

// If not matched by URL, try label or alias (exact then partial substring)
if (!$matched) {
    $selector = $siteLabel !== '' ? $siteLabel : $siteAlias;
    if ($selector !== '') {
        // 1) Exact label match (case-insensitive)
        foreach ($config['sites'] ?? [] as $site) {
            if (isset($site['label']) && strcasecmp($site['label'], $selector) === 0) {
                $apiKey = $site['api_key'] ?? $apiKey;
                $engine = $site['engine'] ?? $engine;
                $hl = $site['hl'] ?? $hl;
                $gl = $site['gl'] ?? $gl;
                $matched = true;
                try {
                    log_site_selection($site, $selector);
                } catch (Throwable $e) {
                    // ignore logging failures
                }
                break;
            }
        }

        // 2) If still not matched, try partial substring match against label only
        if (!$matched) {
            foreach ($config['sites'] ?? [] as $site) {
                $label = $site['label'] ?? '';
                if ($label !== '' && stripos($label, $selector) !== false) {
                    $apiKey = $site['api_key'] ?? $apiKey;
                    $engine = $site['engine'] ?? $engine;
                    $hl = $site['hl'] ?? $hl;
                    $gl = $site['gl'] ?? $gl;
                    $matched = true;
                    try {
                        log_site_selection($site, $selector);
                    } catch (Throwable $e) {
                        // ignore logging failures
                    }
                    break;
                }
            }
        }
    }
}

// Jika menggunakan wrapper resmi SerpApi di PHP, contoh penggunaan:
// require 'path/to/google-search-results.php';
// require 'path/to/restclient.php';
//
// $query = [
//     'engine' => 'google_scholar_case_law',
//     'case_id' => '14798399380223656729',
// ];
//
// $search = new GoogleSearch('$apiKey');
// $result = $search->get_json($query);
// $case_results = $result->case_results;

$maxLen = $config['security']['max_query_length'] ?? 255;
$maxReq = $config['security']['rate_limit_requests'] ?? 30;
$window = $config['security']['rate_limit_window'] ?? 60;

header('Content-Type: application/json; charset=utf-8');

// 1. Validasi dan Sanitasi Input
$authorId = isset($_GET['author_id']) ? trim($_GET['author_id']) : '';
$caseId = isset($_GET['case_id']) ? trim($_GET['case_id']) : '';
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($authorId !== '') {
    if (!preg_match('/^[A-Za-z0-9_-]+$/', $authorId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Author ID tidak valid.'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }
} elseif ($caseId !== '') {
    if (!preg_match('/^[0-9]+$/', $caseId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Case ID tidak valid.'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }
} else {
    if (empty($query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Query pencarian tidak boleh kosong.'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }

    if (mb_strlen($query, 'UTF-8') > $maxLen) {
        http_response_code(400);
        echo json_encode(['error' => 'Query pencarian terlalu panjang.'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }

    // Hanya izinkan karakter teks Unicode, angka, spasi, dan tanda baca dasar untuk mencegah injection
    if (!preg_match('/^[\p{L}\p{N}\s\-_.,\'\"]+$/u', $query)) {
        http_response_code(400);
        echo json_encode(['error' => 'Karakter tidak valid dalam query pencarian.'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        exit;
    }
}

// 2. Rate Limiting Sederhana (berbasis Session/IP)
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitKey = 'rate_limit_' . md5($ip);
$currentTime = time();

if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = ['count' => 1, 'reset' => $currentTime + $window];
} else {
    if ($currentTime > $_SESSION[$rateLimitKey]['reset']) {
        $_SESSION[$rateLimitKey] = ['count' => 1, 'reset' => $currentTime + $window];
    } else {
        $_SESSION[$rateLimitKey]['count']++;
        if ($_SESSION[$rateLimitKey]['count'] > $maxReq) {
            http_response_code(429);
            echo json_encode(['error' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
            exit;
        }
    }
}

// 3. Eksekusi API dengan Konfigurasi cURL yang Aman
if ($authorId !== '') {
    // Contoh: https://serpapi.com/search.json?engine=google_scholar_author&author_id=LSsXyncAAAAJ
    $apiUrl = sprintf(
        'https://serpapi.com/search.json?engine=google_scholar_author&author_id=%s&api_key=%s',
        urlencode($authorId),
        urlencode($apiKey)
    );
} elseif ($caseId !== '') {
    // Contoh: https://serpapi.com/search.json?engine=google_scholar_case_law&case_id=14798399380223656729
    $apiUrl = sprintf(
        'https://serpapi.com/search.json?engine=google_scholar_case_law&case_id=%s&api_key=%s',
        urlencode($caseId),
        urlencode($apiKey)
    );
} else {
    $apiUrl = sprintf(
        'https://serpapi.com/search.json?engine=%s&q=%s&hl=%s&gl=%s&api_key=%s',
        urlencode($engine),
        urlencode($query),
        urlencode($hl),
        urlencode($gl),
        urlencode($apiKey)
    );
}

$curlHandle = curl_init();
curl_setopt($curlHandle, CURLOPT_URL, $apiUrl);
curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curlHandle, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curlHandle, CURLOPT_TIMEOUT, 15); // Timeout ketat
curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($curlHandle, CURLOPT_USERAGENT, 'JurnalDosenIndonesia/1.0');

$responseBody = curl_exec($curlHandle);
$httpStatusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
$curlError = curl_error($curlHandle);
curl_close($curlHandle);

// 4. Output Encoding yang Aman
$jsonFlags = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;

if ($httpStatusCode === 200 && $responseBody) {
    // Pastikan respons dari SerpApi juga valid JSON sebelum diteruskan
    $decoded = json_decode($responseBody, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($decoded, $jsonFlags);
    } else {
        http_response_code(502);
        echo json_encode(['error' => 'Respons dari upstream tidak valid.'], $jsonFlags);
    }
} else {
    http_response_code($httpStatusCode ?: 500);
    echo json_encode([
        'error' => 'Gagal menghubungi layanan pencarian.',
        'details' => $curlError ?: 'Unknown error'
    ], $jsonFlags);
}