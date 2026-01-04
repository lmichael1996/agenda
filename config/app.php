<?php
/**
 * Application Configuration
 */

return [
    'name' => 'Agenda',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => getenv('APP_DEBUG') === 'true',
    'url' => getenv('APP_URL') ?: 'http://localhost:3000',
    'timezone' => 'Europe/Rome',
    'locale' => 'it_IT',
    
    // Paths
    'paths' => [
        'root' => dirname(__DIR__),
        'public' => dirname(__DIR__) . '/public',
        'storage' => dirname(__DIR__) . '/storage',
        'views' => dirname(__DIR__) . '/src/Views',
    ],
];
