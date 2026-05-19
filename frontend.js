/**
 * ZKTECO LH6500 Offline Lock Integration
 * Frontend 3-way async workflow for card provisioning
 */

class HotelLockEncoder {
    constructor(backendUrl = '/index.php', localEncoderUrl = 'http://127.0.0.1:8088/api/card') {
        this.backendUrl = backendUrl;
        this.localEncoderUrl = localEncoderUrl;
    }

    /**
     * Triggers the full 3-way workflow to encode a hotel card.
     * @param {string} roomCode 
     * @param {string} startTime - Format: YYYY-MM-DD HH:mm:ss
     * @param {string} endTime - Format: YYYY-MM-DD HH:mm:ss
     */
    async provisionCard(roomCode, startTime, endTime) {
        try {
            console.log("Starting card provisioning for room:", roomCode);

            // Step 1: Read physical hardware UID from the local encoder
            console.log("Step 1: Reading hardware card UID...");
            const uidResponse = await this._readCardUid();
            if (!uidResponse.success || !uidResponse.uid) {
                throw new Error("Failed to read Card UID. Is a card placed on the encoder?");
            }
            const cardUid = uidResponse.uid;
            console.log("Card UID read successfully:", cardUid);

            // Step 2: Fetch the encrypted hex string from our PHP backend
            console.log("Step 2: Requesting encrypted hex data from backend...");
            const backendResponse = await this._fetchWriteStringFromBackend(cardUid, roomCode, startTime, endTime);
            if (!backendResponse.success || !backendResponse.data || !backendResponse.data.hex_data) {
                throw new Error("Backend failed to generate hex data: " + (backendResponse.error || 'Unknown error'));
            }
            const hexData = backendResponse.data.hex_data;
            console.log("Hex data received from backend.");

            // Step 3: Write the hex string to the physical card via local encoder
            console.log("Step 3: Writing hex data to physical card...");
            const writeResponse = await this._writeSector(hexData);
            if (!writeResponse.success) {
                throw new Error("Failed to write data to the physical card. Please try again.");
            }
            console.log("Physical card written successfully.");

            // Step 4: Confirm check-in success with backend (Handshake Rule)
            console.log("Step 4: Confirming hardware write success with backend...");
            const confirmResponse = await this._confirmCheckinWithBackend(cardUid, roomCode);
            if (!confirmResponse.success) {
                throw new Error("Hardware write succeeded, but backend confirmation failed: " + confirmResponse.error);
            }

            console.log("✅ Card successfully provisioned and checked-in.");
            return true;

        } catch (error) {
            console.error("❌ Card Provisioning Error:", error.message);
            // In a real UI, you'd trigger a modal or toast notification here.
            alert("Provisioning Error: " + error.message);
            return false;
        }
    }

    async _readCardUid() {
        const response = await fetch(`${this.localEncoderUrl}/readUid`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error(`Local encoder HTTP error: ${response.status}`);
        return await response.json();
    }

    async _writeSector(hexData) {
        const response = await fetch(`${this.localEncoderUrl}/writeSector`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ hex_data: hexData })
        });
        if (!response.ok) throw new Error(`Local encoder HTTP error: ${response.status}`);
        return await response.json();
    }

    async _fetchWriteStringFromBackend(cardUid, roomCode, startTime, endTime) {
        const response = await fetch(this.backendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'get_write_string',
                card_uid: cardUid,
                room_code: roomCode,
                start_time: startTime,
                end_time: endTime
            })
        });
        if (!response.ok) throw new Error(`Backend HTTP error: ${response.status}`);
        return await response.json();
    }

    async _confirmCheckinWithBackend(cardUid, roomCode) {
        const response = await fetch(this.backendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'confirm_checkin',
                card_uid: cardUid,
                room_code: roomCode,
                status: true // Hardware confirmed
            })
        });
        if (!response.ok) throw new Error(`Backend HTTP error: ${response.status}`);
        return await response.json();
    }
}

// Example UI Hook
// document.getElementById('issueCardBtn').addEventListener('click', async () => {
//     const encoder = new HotelLockEncoder();
//     await encoder.provisionCard('101', '2026-05-18 14:00:00', '2026-05-20 12:00:00');
// });
