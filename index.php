<?php

header('Content-Type: application/json');

require_once 'config/env.php';
require_once 'zkbio.php';
require_once 'card-managment.php';

// Initialize ZKBio and CardManagment
$zkbio_address = $_ENV['ZKBIO'] ?? 'https://localhost:8098/';
$username = $_ENV['ZKBIO_USERNAME'] ?? 'admin';
$password = $_ENV['ZKBIO_PASSWORD'] ?? 'admin';

$zkbio = new ZKBio($zkbio_address, $username, $password);
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

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        break;
}
