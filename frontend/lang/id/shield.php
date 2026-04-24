<?php

return [
    'challenge' => [
        'title' => 'Memverifikasi Koneksi',
        'message' => 'Kami mengamankan sesi Anda untuk melindungi dari ancaman otomatis. Ini hanya akan memakan waktu sejenak.',
        'steps' => [
            'analyze' => 'Analisis',
            'solve' => 'Selesaikan',
            'verify' => 'Verifikasi',
        ],
        'status' => [
            'initializing' => 'Menginisialisasi pemeriksaan keamanan...',
            'verifying' => 'Memverifikasi koneksi...',
            'analyzing' => 'Menganalisis metrik keamanan...',
            'finalizing' => 'Finalisasi verifikasi...',
            'verified' => 'Terverifikasi! Melanjutkan...',
            'failed' => 'Verifikasi gagal. Mencoba kembali...',
        ],
        'retry' => 'Coba Lagi',
    ],
];
