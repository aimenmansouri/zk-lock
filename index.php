<?php

header('Content-Type: application/json');

require_once 'config/env.php';

$card_rw = rtrim($_ENV['CARD_RW'] ?? 'http://localhost:24009/', '/');

$action = $_GET['action'] ?? null;

if (!$action) {
    echo json_encode(['success' => false, 'error' => 'No action specified.']);
    exit;
}

switch ($action) {
    case 'read_card':
        $url = $card_rw . '/LockOnline/ExtendedDevice?OP-DEV=1&CMD-URL=4&CardCategory=1&buzzer=1';
        echo json_encode(callCardReader($url));
        break;

    case 'write_card':
        $room = $_GET['room'] ?? null; // format: 1.1.22 (building.floor.room)
        if (!$room) {
            echo json_encode(['success' => false, 'error' => 'Room is required.']);
            exit;
        }

        $parts = explode('.', $room);
        if (count($parts) !== 3) {
            echo json_encode(['success' => false, 'error' => 'Room format must be Building.Floor.Room (e.g. 1.1.22)']);
            exit;
        }

        $building = (int)$parts[0];
        $floor = (int)$parts[1];
        $roomNum = (int)$parts[2];
        $subRoom = 0;

        // Generate a simple card number from room info
        $cardNum = 0 ; //$building * 100000 + $floor * 1000 + $roomNum;

        // ValidDate from input (format: YYYYMMDDHHmm)
        $validDate = $_GET['validDate'] ?? null;
        if (!$validDate) {
            echo json_encode(['success' => false, 'error' => 'validDate is required (e.g. 202605221400).']);
            exit;
        }

        $url = $card_rw . '/LockOnline/ExtendedDevice?OP-DEV=1&CMD-URL=5&buzzer=1'
            . '&CardType=7'
            . '&CardNum=' . $cardNum
            . '&ValidDate=' . $validDate
            . '&Building=' . $building
            . '&Floor=' . $floor
            . '&Room=' . $roomNum
            . '&SubRoom=' . $subRoom;

        echo json_encode(callCardReader($url));
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
        break;
}

function callCardReader($url)
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3,
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'error' => 'Card reader error: ' . $error];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);

    if ($httpCode >= 400) {
        return ['success' => false, 'error' => 'Card reader HTTP ' . $httpCode, 'raw' => $response];
    }

    return ['success' => true, 'data' => $decoded ?? $response];
}
