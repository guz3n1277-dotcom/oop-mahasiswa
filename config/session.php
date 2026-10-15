<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Pastikan session ID yang tidak valid ditolak
    ini_set('session.use_strict_mode', '1');

    // Periksa apakah koneksi menggunakan HTTPS
    $isHttps = !empty($_SERVER['HTTPS']) &&
               $_SERVER['HTTPS'] !== 'off';

    // Atur keamanan cookie session sebelum memulai session
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}