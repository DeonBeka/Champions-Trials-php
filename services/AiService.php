<?php
class AiService {

    private $apiKey;

    public function __construct() {
        $config = include __DIR__ . "/../config/config.example.php";
        $this->apiKey = $config["OPENAI_API_KEY"];
    }

    public function analyzeImage($imageFilePath) {
        if (!file_exists($imageFilePath)) {
            throw new Exception("Image file does not exist: $imageFilePath");
        }

        $imageData = base64_encode(file_get_contents($imageFilePath));

        $url = "https://api.openai.com/v1/chat/completions";

        $payload = [
            "model" => "gpt-4o-mini",
            "messages" => [
                [
                    "role" => "user",
                    "content" => [
                        [
                            "type" => "image_base64",
                            "image_base64" => [
                                "base64" => $imageData
                            ]
                        ],
                        [
                            "type" => "text",
                            "text" =>
                                "Analyze this lesson image and do the following:\n" .
                                "1. Extract clear and structured study notes.\n" .
                                "2. Create a 5-question quiz with answers.\n" .
                                "Format output EXACTLY as:\n" .
                                "NOTES:\n<notes here>\n\nQUIZ:\n<quiz here>"
                        ]
                    ]
                ]
            ],
            "temperature" => 0.3,
            "max_tokens" => 800
        ];

        $headers = [
            "Content-Type: application/json",
            "Authorization: " . "Bearer " . $this->apiKey,
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        if ($response === false) {
            throw new Exception("Curl error: " . curl_error($curl));
        }

        curl_close($curl);

        return json_decode($response, true);
    }
}



