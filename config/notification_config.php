<?php
// Keep real keys in config/notification_config.local.php.
// Resend: https://resend.com
// Semaphore: https://semaphore.co

$localConfig = __DIR__ . '/notification_config.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

if (!defined('RESEND_API_KEY')) {
    define('RESEND_API_KEY', 'PASTE_RESEND_API_KEY_HERE');
}

if (!defined('RESEND_FROM_EMAIL')) {
    define('RESEND_FROM_EMAIL', 'City Vet <onboarding@resend.dev>');
}

if (!defined('SEMAPHORE_API_KEY')) {
    define('SEMAPHORE_API_KEY', 'PASTE_SEMAPHORE_API_KEY_HERE');
}

if (!defined('SEMAPHORE_SENDER_NAME')) {
    define('SEMAPHORE_SENDER_NAME', '');
}
