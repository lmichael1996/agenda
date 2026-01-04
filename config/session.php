<?php
/**
 * Session Configuration
 */

return [
    'lifetime' => getenv('SESSION_LIFETIME') ?: 7200, // 2 ore
    'expire_on_close' => false,
    'cookie' => [
        'name' => 'agenda_session',
        'path' => '/',
        'domain' => null,
        'secure' => getenv('SESSION_SECURE') === 'true', // true per HTTPS
        'httponly' => true,
        'samesite' => 'Lax', // Lax, Strict, None
    ],
    'strict_mode' => true,
    'regenerate_on_login' => true,
];
