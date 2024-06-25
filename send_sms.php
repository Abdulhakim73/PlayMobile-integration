<?php

require 'SendSmsWithPlayMobile.php';

$username = getenv('PLAYMOBILE_USERNAME');
$password = getenv('PLAYMOBILE_PASSWORD');
$endpoint = getenv('PLAYMOBILE_ENDPOINT');

$message = $_POST['message'] ?? '';
$phone = $_POST['phone'] ?? '';

if (empty($message) || empty($phone)) {
    echo json_encode(['status' => 400, 'result' => 'Message and phone number are required']);
    exit;
}

$smsService = new SendSmsWithPlayMobile($message, $phone, $username, $password, $endpoint);
$response = $smsService->send();

