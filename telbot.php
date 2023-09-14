<?php
$telegramBotToken = '6126309842:AAHoqzr9KLFWuOK4g_07KNXHW35S3c7MvQs';
$openAiApiKey = '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';

// Get the incoming update data as JSON
$update = file_get_contents('php://input');

// Decode the JSON data
$update = json_decode($update, true);

// Check if the update contains a message
if (isset($update['message'])) {
    $message = $update['message'];
    $chatId = $message['chat']['id'];
    $text = $message['text'];

    // Send the user's message to GPT-3 for a response
    $response = generateResponse($text, $openAiApiKey);

    // Send the response back to the user
    sendMessage($chatId, $response, $telegramBotToken);
}

function generateResponse($input, $apiKey) {
    $data = [
        'prompt' => $input,
        'max_tokens' => 1024 // Adjust as needed
    ];

    $ch = curl_init('https://api.openai.com/v1/engines/text-davinci-003/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response, true);

    return $response['choices'][0]['text'];
}

function sendMessage($chatId, $message, $botToken) {
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ],
    ];

    $context = stream_context_create($options);
    file_get_contents($url, false, $context);
}
