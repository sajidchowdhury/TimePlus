<?php

class SMS {
    private $apiKey;
    private $baseUrl;
    private $balanceUrl;

    public function __construct() {
        $this->apiKey = '$2y$10$p3zBCl6SGUczBFmgEOlE8ujRDxKyVnPWNaxquQOY.cExIZFkzIH92';
        $this->baseUrl = "http://portal.jadusms.com/smsapi/";
        $this->balanceUrl = "http://portal.jadusms.com/api/balance";
    }

    /**
     * Send SMS (Non-Masking or Masking)
     */
    public function sendSMS($mobile, $message, $masking = false, $maskingID = null) {
        // Encode message for URL safety
        $message = urlencode($message);

        // Validate mobile number format (remove spaces and ensure correct format)
        $mobile = preg_replace('/\s+/', '', trim($mobile));

        // Build API URL
        $url = $this->baseUrl . ($masking ? "masking" : "non-masking");
        $url .= "?api_key=" . urlencode($this->apiKey);
        $url .= "&smsType=text";
        $url .= "&mobileNo=" . urlencode($mobile);
        $url .= "&smsContent=" . $message;

        // Add masking ID if required
        if ($masking && $maskingID) {
            $url .= "&maskingID=" . urlencode($maskingID);
        }

        // Execute request and return JSON response
        return $this->executeRequest($url);
    }

    /**
     * Get SMS Credit Balance
     */
    public function getBalance() {
        $url = "{$this->balanceUrl}?api_key=" . urlencode($this->apiKey);
        
        $response = $this->executeRequest($url);

        // If the response is an array, return an error
        if (is_array($response)) {
            return "0.00";
        }

        // Extract the numeric balance using regex
        preg_match('/\d+(\.\d+)?/', $response, $matches);

        return isset($matches[0]) ? $matches[0] : "0.00";
    }

    /**
     * Execute HTTP Request using cURL
     */
    private function executeRequest($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        // Check for cURL error
        if ($error) {
            return json_encode(["status" => "error", "message" => $error]);
        }

        // Check response content
        if (strpos($response, 'SMS Send Success') !== false) {
            return json_encode(["status" => "success", "message" => "SMS Sent Successfully"]);
        } else {
            return json_encode(["status" => "error", "message" => $response]);
        }
    }
}
?>
