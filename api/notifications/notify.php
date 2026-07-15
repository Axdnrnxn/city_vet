<?php
require_once dirname(__DIR__, 2) . '/config/notification_config.php';

function notificationConfigReady($value, $placeholder) {
    return !empty($value) && $value !== $placeholder;
}

function postJson($url, $headers, $payload) {
    if (!function_exists('curl_init')) {
        return ["sent" => false, "message" => "PHP cURL extension is not enabled."];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 20
    ]);

    $body = curl_exec($ch);
    $error = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $error) {
        return ["sent" => false, "message" => $error ?: "Connection failed."];
    }

    return [
        "sent" => $statusCode >= 200 && $statusCode < 300,
        "message" => $body,
        "status_code" => $statusCode
    ];
}

function postForm($url, $payload) {
    if (!function_exists('curl_init')) {
        return ["sent" => false, "message" => "PHP cURL extension is not enabled."];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($payload),
        CURLOPT_TIMEOUT => 20
    ]);

    $body = curl_exec($ch);
    $error = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false || $error) {
        return ["sent" => false, "message" => $error ?: "Connection failed."];
    }

    return [
        "sent" => $statusCode >= 200 && $statusCode < 300,
        "message" => $body,
        "status_code" => $statusCode
    ];
}

function sendResendEmail($to, $subject, $plainMessage, $htmlMessage = null) {
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return ["sent" => false, "message" => "Invalid recipient email."];
    }

    if (!notificationConfigReady(RESEND_API_KEY, 'PASTE_RESEND_API_KEY_HERE')) {
        return ["sent" => false, "message" => "Resend API key is not configured."];
    }

    $payload = [
        "from" => RESEND_FROM_EMAIL,
        "to" => [$to],
        "subject" => $subject,
        "text" => $plainMessage
    ];

    if ($htmlMessage) {
        $payload["html"] = $htmlMessage;
    }

    return postJson(
        'https://api.resend.com/emails',
        [
            'Authorization: Bearer ' . RESEND_API_KEY,
            'Content-Type: application/json'
        ],
        $payload
    );
}

function normalizePhilippineMobile($phone) {
    $digits = preg_replace('/\D+/', '', $phone ?? '');
    if (strlen($digits) === 11 && substr($digits, 0, 2) === '09') {
        return '63' . substr($digits, 1);
    }
    if (strlen($digits) === 12 && substr($digits, 0, 2) === '63') {
        return $digits;
    }
    return $digits;
}

function sendSemaphoreSms($phone, $message) {
    $number = normalizePhilippineMobile($phone);
    if (!$number || strlen($number) < 10) {
        return ["sent" => false, "message" => "Invalid mobile number."];
    }

    if (!notificationConfigReady(SEMAPHORE_API_KEY, 'PASTE_SEMAPHORE_API_KEY_HERE')) {
        return ["sent" => false, "message" => "Semaphore API key is not configured."];
    }

    $payload = [
        "apikey" => SEMAPHORE_API_KEY,
        "number" => $number,
        "message" => $message
    ];

    if (!empty(SEMAPHORE_SENDER_NAME)) {
        $payload["sendername"] = SEMAPHORE_SENDER_NAME;
    }

    return postForm('https://api.semaphore.co/api/v4/messages', $payload);
}
