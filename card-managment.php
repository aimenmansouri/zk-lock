<?php

require_once 'zkbio.php';

interface CardManagmentInterface
{
    public function assignCardToRoom($cardData);
    public function readCard($rawHexData);
    public function deleteCard($cardId);
}

class CardManagment implements CardManagmentInterface
{
    private $zkbio;

    public function __construct(ZKBio $zkbio) {
        $this->zkbio = $zkbio;
    }

    public function assignCardToRoom($cardData)
    {
        try {
            // Validate required fields
            if (empty($cardData['card_uid']) || empty($cardData['room_code']) || empty($cardData['start_time']) || empty($cardData['end_time'])) {
                throw new Exception("Missing required card data for room assignment.");
            }

            // Call the ZK CVSecurity API to get the encrypted hex data
            $hexData = $this->zkbio->getOfflineWriteString(
                $cardData['card_uid'],
                $cardData['room_code'],
                $cardData['start_time'],
                $cardData['end_time'],
                $cardData['copy_counter'] ?? 1
            );

            return [
                'success' => true,
                'data' => [
                    'hex_data' => $hexData,
                    'card_uid' => $cardData['card_uid'],
                    'room_code' => $cardData['room_code']
                ],
                'error' => null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    public function readCard($rawHexData)
    {
        try {
            if (empty($rawHexData)) {
                throw new Exception("Missing hex data for reading card.");
            }

            $parsedData = $this->zkbio->parseOfflineCardString($rawHexData);

            return [
                'success' => true,
                'data' => $parsedData,
                'error' => null
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    public function deleteCard($cardId)
    {
        // In offline lock systems, 'deleting' a card usually means issuing a checkout card or clearing sectors via the encoder.
        return [
            'success' => false,
            'data' => null,
            'error' => "Method deleteCard not fully implemented for offline hardware locks."
        ];
    }
}