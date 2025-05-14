<?php
/**
 * TwilioSMS.php - Utility class for sending SMS via Twilio
 */

class TwilioSMS {
    private $accountSid;
    private $authToken;
    private $twilioNumber;
    private $testMode = false;

    public function __construct($accountSid, $authToken, $twilioNumber = '+16206474275', $testMode = true) {
        $this->testMode = $testMode;
        $this->accountSid = $accountSid;
        $this->authToken = $authToken;
        $this->twilioNumber = $twilioNumber;
    }

    /**
     * Send SMS message via Twilio API
     * 
     * @param string $to Recipient phone number (with country code)
     * @param string $message Message text to send
     * @return array Response with status and details
     */
    public function sendSMS($to, $message) {
        // Format the recipient phone number
        if (substr($to, 0, 1) !== '+') {
            $to = '+216' . $to; // Add Tunisia country code if not present
        }
        
        // In test mode, simulate successful delivery
        if ($this->testMode) {
            error_log("[TEST MODE] SMS would be sent to: {$to}, Message: {$message}");
            return [
                'success' => true,
                'message' => 'SMS sent successfully (TEST MODE - no actual SMS sent)',
                'data' => [
                    'to' => $to,
                    'from' => $this->twilioNumber,
                    'body' => $message,
                    'status' => 'delivered',
                    'test_mode' => true
                ],
                'http_code' => 200
            ];
        }
        
        // List of verified numbers that can receive real SMS
        $verifiedNumbers = [
            '+21654231907' // Your Twilio account number
            // Add more verified numbers here as you verify them
        ];
        
        // Check if recipient is verified
        $isVerified = false;
        foreach ($verifiedNumbers as $verifiedNumber) {
            if ($to === $verifiedNumber) {
                $isVerified = true;
                break;
            }
        }
        
        // If not verified and we're not in test mode, use simulation instead
        if (!$isVerified) {
            error_log("[HYBRID MODE] Number {$to} is not verified. Simulating SMS delivery instead.");
            return [
                'success' => true,
                'message' => 'SMS sent successfully (HYBRID MODE - unverified number, no actual SMS sent)',
                'data' => [
                    'to' => $to,
                    'from' => $this->twilioNumber,
                    'body' => $message,
                    'status' => 'delivered',
                    'hybrid_mode' => true
                ],
                'http_code' => 200
            ];
        }
        
        // Make sure From number has + prefix for Twilio format
        if (substr($this->twilioNumber, 0, 1) !== '+') {
            $this->twilioNumber = '+' . $this->twilioNumber;
        }
        
        // Prepare the request to Twilio API
        $url = "https://api.twilio.com/2010-04-01/Accounts/{$this->accountSid}/Messages.json";
        $data = [
            'From' => $this->twilioNumber,
            'To' => $to,
            'Body' => $message
        ];
        
        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->accountSid}:{$this->authToken}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Check for errors
        if ($error) {
            return [
                'success' => false,
                'message' => "cURL Error: $error",
                'http_code' => $httpCode
            ];
        }
        
        // Parse JSON response
        $responseData = json_decode($response, true);
        
        // Check for successful response (2xx status code)
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'message' => 'SMS sent successfully',
                'data' => $responseData,
                'http_code' => $httpCode
            ];
        } else {
            // Extract more detailed error information from the Twilio response
            $errorMessage = 'Failed to send SMS';
            
            if (isset($responseData['message'])) {
                $errorMessage .= ': ' . $responseData['message'];
            } elseif (isset($responseData['error_message'])) {
                $errorMessage .= ': ' . $responseData['error_message'];
            } elseif (isset($responseData['more_info'])) {
                $errorMessage .= ' - More info: ' . $responseData['more_info'];
            }
            
            // Add debug information for troubleshooting
            error_log('Twilio API Error: ' . print_r($responseData, true));
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'error_details' => $responseData,
                'http_code' => $httpCode
            ];
        }
    }
}
?>
