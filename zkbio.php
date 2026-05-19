<?php

class ZKBio
{
    private $zkbio_address;
    private $token;
    private $authMode = 'none'; // 'jwt' or 'access_token'

    public function __construct($zkbio_address)
    {
        $this->zkbio_address = rtrim($zkbio_address, '/');
    }

    /**
     * Make an authenticated API request to ZKBio CVSecurity.
     */
    public function request($method, $endpoint, $data = null)
    {
        $url = $this->zkbio_address . $endpoint;

        // If using static access_token mode, append it as a query parameter
        if ($this->authMode === 'access_token' && $this->token) {
            $url .= (strpos($url, '?') !== false ? '&' : '?') . 'access_token=' . $this->token;
        }

        $ch = curl_init($url);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        // If using JWT mode, send token in Authorization header
        if ($this->authMode === 'jwt' && $this->token) {
            $headers[] = 'Authorization: JWT ' . $this->token;
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false, // For local HTTPS instances
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true
        ];

        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: " . $error_msg);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $error = $decoded['detail'] ?? $decoded['message'] ?? 'Unknown API Error';
            throw new Exception("API Error ({$httpCode}): " . $error);
        }

        return $decoded;
    }

    /**
     * Authenticate using JWT mode (username + password).
     * This calls POST /jwt-api-token-auth/ with admin credentials.
     */
    public function authenticateJWT($username, $password)
    {
        $url = $this->zkbio_address . '/jwt-api-token-auth/';
        $ch = curl_init($url);

        $payload = json_encode([
            'username' => $username,
            'password' => $password
        ]);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            throw new Exception("cURL Error: " . $error_msg);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $error = $decoded['detail'] ?? $decoded['message'] ?? 'Unknown API Error';
            throw new Exception("JWT Auth Error ({$httpCode}) at {$effectiveUrl}: " . $error);
        }

        if (isset($decoded['token'])) {
            $this->token = $decoded['token'];
            $this->authMode = 'jwt';
            return true;
        }

        throw new Exception("JWT Auth failed: No token in response. HTTP {$httpCode}. URL: {$effectiveUrl}. Response: " . $response);
    }

    /**
     * Authenticate using static access_token mode (API Client from ZKBio UI).
     * No HTTP request needed — the token is generated in the ZKBio CVSecurity
     * API Authorization panel and passed directly as a query parameter.
     */
    public function authenticate($clientId, $clientSecret)
    {
        // The ZKBio CVSecurity API documentation states:
        // "access_token: API access token is to check whether the requested 
        //  permission is allowed or denied."
        // This is a static token generated in the ZKBio UI. No login call needed.
        $this->token = $clientSecret;
        $this->authMode = 'access_token';
        return true;
    }

    public function getOfflineWriteString($cardUid, $roomCode, $startTime, $endTime, $copyCounter = 1)
    {
        try {
            if (!$this->token) {
                throw new Exception("Not authenticated. Call authenticate() first.");
            }

            // Placeholder route: Replace with exact ZK CVSecurity API route
            $response = $this->request('POST', '/api/v1/hotel/offline_card/write/', [
                'card_uid' => $cardUid,
                'room_code' => $roomCode,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'copy_counter' => $copyCounter
            ]);

            if (isset($response['hex_data'])) {
                return $response['hex_data'];
            }

            throw new Exception("Failed to retrieve offline write string.");
        } catch (Exception $e) {
            error_log("ZKBio getOfflineWriteString Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function parseOfflineCardString($rawHexData)
    {
        try {
            if (!$this->token) {
                throw new Exception("Not authenticated. Call authenticate() first.");
            }

            // Placeholder route: Replace with exact ZK CVSecurity API route
            $response = $this->request('POST', '/api/v1/hotel/offline_card/parse/', [
                'hex_data' => $rawHexData
            ]);

            return $response;
        } catch (Exception $e) {
            error_log("ZKBio parseOfflineCardString Error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getzkbio_address()
    {
        return $this->zkbio_address;
    }
}