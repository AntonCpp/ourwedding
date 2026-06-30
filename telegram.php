<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$name = trim($input['name'] ?? '');
$attending = trim($input['attending'] ?? '');
$wishes = trim($input['wishes'] ?? '');

if (!$name || !$attending) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Заполните имя и вариант присутствия']);
    exit;
}

$message = "🆕 Новое подтверждение на свадьбу!\n\n"
    . "Имя: " . $name . "\n"
    . "Присутствие: " . $attending;
if ($wishes) {
    $message .= "\nПожелания: " . $wishes;
}

$botToken = '8842430576:AAEYE-O3GzePlvCRmFIhmjkrhEIHK_zEom8';
$chatIds = ['654058837', '1237038312'];

$allOk = true;
$errors = [];

foreach ($chatIds as $chatId) {
    $ch = curl_init('https://api.telegram.org/bot' . $botToken . '/sendMessage');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'chat_id' => $chatId,
            'text' => $message,
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode !== 200) {
        $allOk = false;
        $errors[] = "Chat $chatId: HTTP $httpCode";
    }
    curl_close($ch);
}

if ($allOk) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => implode('; ', $errors)]);
}
