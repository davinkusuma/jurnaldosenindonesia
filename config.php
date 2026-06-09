<?php
// File: config/config.php
// Konfigurasi disimpan dalam PHP agar dieksekusi, bukan disajikan sebagai plain text

// Mencegah akses langsung ke file ini
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    http_response_code(403);
    exit('Akses Ditolak.');
}

return [
    'serpapi' => [
        'api_key' => 'a97bcc0992909512b60caf62afc72efc3dce572a2d73998364258099867868ea',
        'engine'  => 'google_scholar',
        'hl'      => 'id',
        'gl'      => 'id'
    ],
    'logging' => [
        'enable_site_selection_log' => true,
        // relative to project root
        'site_selection_log_file' => 'logs/site_selection.csv',
        // Rotation: rotate daily and/or when file exceeds size (bytes)
        'rotate_daily' => true,
        'max_size_bytes' => 5242880, // 5 MB
        // Keep last N archive files when rotating
        'max_archives' => 7,
        // Admin credentials for simple log viewer (change in production)
        'admin_user' => 'admin',
        'admin_pass' => 'N7r$w9Kz!pQ4xVb2Ys@8LtF'
    ],
    'sites' => [
        [
            'label'  => 'Jurnal Dosen Indonesia',
            'url'    => 'https://jurnaldosenindonesia.com/Jurnal',
            'api_key'=> '5f0a500bc6b175c039ac414a5a5343aa'
        ],
    ],
    'security' => [
        'max_query_length' => 255,
        'rate_limit_requests' => 30, // Maksimal 30 permintaan per menit per IP
        'rate_limit_window' => 60   // Window waktu dalam detik
    ]
];