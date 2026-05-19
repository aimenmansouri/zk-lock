<?php

class ZKBio
{
    private $zkbio_address;
    private $token;

    public function __construct($zkbio_address)
    {
        $this->zkbio_address = rtrim($zkbio_address, '/');
    }

    private function request($method, $endpoint, $data = null)
    {
        $url = $this->zkbio_address . $endpoint;
        $ch = curl_init($url);

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($this->token) {
            $headers[] = 'Authorization: JWT ' . $this->token;
        }

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CONNECTTIMEOUT => 2, // Strict 2s connect timeout
            CURLOPT_TIMEOUT => 5, // Strict 5s execution timeout
            CURLOPT_SSL_VERIFYPEER => false, // For local HTTPS instances
            CURLOPT_SSL_VERIFYHOST => false
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

    public function authenticate($clientId, $clientSecret)
    {
        try {
            $url = 'https://127.0.0.1:8098/api/v1/api-client-auth/';
            $ch = curl_init($url);

            $payload = json_encode([
                "client_id" => $clientId,
                "client_secret" => $clientSecret
            ]);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            // Disable SSL verification for localhost development
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

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
                throw new Exception("Authentication API Error ({$httpCode}): " . $error);
            }

            if (isset($decoded['token'])) {
                $this->token = $decoded['token'];
                return true;
            }

            throw new Exception("Authentication failed: No token returned.");
        } catch (Exception $e) {
            error_log("ZKBio Auth Error: " . $e->getMessage());
            throw $e;
        }
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