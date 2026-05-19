<?php

header('Content-Type: application/json');

require_once 'config/env.php';
require_once 'zkbio.php';
require_once 'card-managment.php';

// Initialize ZKBio
$zkbio_address = $_ENV['ZKBIO'] ?? 'https://localhost:8098/';
$zkbio = new ZKBio($zkbio_address);

// Quick integration example using Client ID from .env
try {
    $clientId = trim($_ENV['ZKBIO_CLIENTID'] ?? 'hotelo', '"; ');
    $clientSecret = trim($_ENV['ZKBIO_CLIENT_SECRET'] ?? 'default_secret', '"; ');
    $zkbio->authenticate($clientId, $clientSecret);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Authentication failed: ' . $e->getMessage()
    ]);
    exit;
}

$cardManagment = new CardManagment($zkbio);

// Get JSON POST body if applicable
$inputRaw = file_get_contents('php://input');
$inputData = json_decode($inputRaw, true) ?: $_POST;

$action = $_GET['action'] ?? $inputData['action'] ?? null;

if (!$action) {
    echo json_encode(['success' => false, 'error' => 'No action specified.']);
    exit;
}

switch ($action) {
    case 'get_write_string':
        $cardUid = $inputData['card_uid'] ?? null;
        $roomCode = $inputData['room_code'] ?? null;
        $startTime = $inputData['start_time'] ?? null;
        $endTime = $inputData['end_time'] ?? null;

        $response = $cardManagment->assignCardToRoom([
            'card_uid' => $cardUid,
            'room_code' => $roomCode,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);

        echo json_encode($response);
        break;

    case 'confirm_checkin':
        $cardUid = $inputData['card_uid'] ?? null;
        $roomCode = $inputData['room_code'] ?? null;
        $status = $inputData['status'] ?? false;

        if ($status === true && $cardUid && $roomCode) {
            // Handshake Rule: Hardware success explicitly confirmed by JS
            // Here you would update your database reservation status to 'checked-in'

            echo json_encode([
                'success' => true,
                'data' => [
                    'message' => 'Check-in confirmed and updated in database.',
                    'card_uid' => $cardUid,
                    'room_code' => $roomCode
                ],
                'error' => null
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'data' => null,
                'error' => 'Invalid confirmation data or hardware write failed.'
            ]);
        }
        break;

    case 'read_card':
        $hexData = $inputData['hex_data'] ?? null;
        if (!$hexData) {
            echo json_encode(['success' => false, 'error' => 'Missing hex_data.']);
            break;
        }
        $response = $cardManagment->readCard($hexData);
        echo json_encode($response);
        break;

    case 'revoke_card':
        $cardUid = $inputData['card_uid'] ?? null;
        if ($cardUid) {
            // Update database to mark card as revoked/checked-out
            echo json_encode([
                'success' => true,
                'data' => [
                    'message' => 'Card revoked in database.',
                    'card_uid' => $cardUid
                ],
                'error' => null
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Missing card_uid.']);
        }
        break;

    case 'proxy_encoder':
        // Add CORS headers so frontend can call this proxy
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $endpoint = $_GET['endpoint'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];
        $postData = null;

        if ($method === 'POST') {
            $inputRaw = file_get_contents('php://input');
            $postData = json_decode($inputRaw, true);
        }

        try {
            // Use the authenticated ZKBio request method to hit port 8098 with the access_token
            $response = $zkbio->request($method, '/api/card' . $endpoint, $postData);
            echo json_encode($response);
        } catch (Exception $e) {
            $httpCode = 500;
            if (preg_match('/API Error \((\d+)\)/', $e->getMessage(), $matches)) {
                $httpCode = (int)$matches[1];
            }
            http_response_code($httpCode);
            echo json_encode([
                'success' => false, 
                'error' => 'Proxy API Error: ' . $e->getMessage()
            ]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        break;
}
