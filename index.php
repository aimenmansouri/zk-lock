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
        $endpoint = $_GET['endpoint'] ?? '';
        $method = $_SERVER['REQUEST_METHOD'];
        $url = 'http://127.0.0.1:8088/api/card' . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if ($method === 'POST') {
            $inputRaw = file_get_contents('php://input');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $inputRaw);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            curl_close($ch);
            echo json_encode(['success' => false, 'error' => 'Proxy Connection Error: ' . $error_msg]);
            break;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        http_response_code($httpCode);
        echo $response;
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        break;
}
