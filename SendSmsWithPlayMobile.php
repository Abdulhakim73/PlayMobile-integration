<?php

class SendSmsWithPlayMobile
{
    const SUCCESS = 200;
    const FAILED = 400;

    private $message;
    private $messageId;
    private $phone;
    private $spend;
    private $username;
    private $password;
    private $endpoint;

    public function __construct($message, $phone, $username, $password, $endpoint)
    {
        $this->message = $message;
        $this->messageId = rand(100000000, 999999999);
        $this->phone = $phone;
        $this->username = $username;
        $this->password = $password;
        $this->endpoint = $endpoint;
    }

    public function send()
    {
        $validation = $this->customValidation();
        if ($validation['status'] === self::SUCCESS) {
            $calculation = $this->calculationSendSms($this->message);
            if ($calculation['status'] === self::SUCCESS) {
                return $this->sendMessage($this->message);
            } else {
                return ['status' => self::FAILED, 'result' => $calculation['result']];
            }
        } else {
            return ['status' => self::FAILED, 'result' => $validation['result']];
        }
    }

    private function customValidation()
    {
        if (strlen($this->phone) !== 9) {
            return ['status' => self::FAILED, 'result' => "Wrong phone number!"];
        }
        if (empty($this->message)) {
            return ['status' => self::FAILED, 'result' => "There is no message!"];
        }
        return ['status' => self::SUCCESS, 'result' => null];
    }

    private function calculationSendSms($message)
    {
        try {
            $length = strlen($message);
            if ($length) {
                $this->spend = ceil($length / 153); // SMS segmentation logic
                return ['status' => self::SUCCESS, 'result' => null];
            } else {
                return ['status' => self::FAILED, 'result' => "There is no message!"];
            }
        } catch (\Exception $e) {
            return ['status' => self::FAILED, 'result' => "Oops, something went wrong!"];
        }
    }

    private function sendMessage($message)
    {
        $URL = $this->endpoint . '/send/';
        try {
            $headers = [
                'Content-type: application/json; charset=UTF-8',
                'Authorization: Basic ' . base64_encode($this->username . ":" . $this->password),
            ];
            $payload = [
                "messages" => [
                    [
                        "recipient" => "998" . $this->phone,
                        "message-id" => (string)$this->messageId,
                        "sms" => [
                            "originator" => "2408",
                            "content" => [
                                "text" => $message,
                            ]
                        ]
                    ]
                ]
            ];

            $contextOptions = [
                'http' => [
                    'header' => implode("\r\n", $headers),
                    'method' => 'POST',
                    'content' => json_encode($payload),
                ],
            ];
            $context = stream_context_create($contextOptions);
            $result = file_get_contents($URL, false, $context);

            if ($result === FALSE) {
                return ['status' => self::FAILED, 'result' => "The SMS was not sent due to an error by the SMS service!"];
            }
            return ['status' => self::SUCCESS, 'result' => "SMS sent successfully!"];

        } catch (Exception $e) {
            return ['status' => self::FAILED, 'result' => "An error occurred in the SMS service!"];
        }
    }
}

