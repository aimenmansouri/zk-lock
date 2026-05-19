<?php
// Prevent unauthorized caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZK-Lock | Premium Provisioning</title>
    <!-- Use Outfit font for a modern, sleek appearance -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern CSS resets and premium styling */
        :root {
            --bg-color: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.6);
            --glass-border: rgba(255, 255, 255, 0.08);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent: #8b5cf6;
            --danger: #ef4444;
            --info: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Outfit', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            background-image: radial-gradient(circle at top right, rgba(59, 130, 246, 0.15), transparent 40%),
                radial-gradient(circle at bottom left, rgba(139, 92, 246, 0.15), transparent 40%);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .container {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            z-index: 10;
        }

        /* Glassmorphism Card */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header p {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 300;
        }

        .tabs {
            display: flex;
            gap: 0.5rem;
            background: rgba(15, 23, 42, 0.6);
            padding: 0.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
        }

        .tab {
            flex: 1;
            padding: 0.75rem;
            text-align: center;
            border-radius: 12px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
            color: var(--text-muted);
        }

        .tab.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .tab-content {
            display: none;
            animation: fadeIn 0.4s;
        }

        .tab-content.active {
            display: block;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .form-control {
            width: 100%;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            color: var(--text-main);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: rgba(59, 130, 246, 0.5);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        ::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.6;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        ::-webkit-calendar-picker-indicator:hover {
            opacity: 1;
        }

        .btn {
            width: 100%;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 1.1rem;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .btn-issue {
            background: linear-gradient(135deg, var(--primary), var(--accent));
        }

        .btn-issue:hover:not(:disabled) {
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
            transform: translateY(-3px);
        }

        .btn-read {
            background: linear-gradient(135deg, var(--info), #059669);
        }

        .btn-read:hover:not(:disabled) {
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5);
            transform: translateY(-3px);
        }

        .btn-revoke {
            background: linear-gradient(135deg, var(--danger), #b91c1c);
        }

        .btn-revoke:hover:not(:disabled) {
            box-shadow: 0 10px 25px -5px rgba(239, 68, 68, 0.5);
            transform: translateY(-3px);
        }

        .btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            background: rgba(71, 85, 105, 0.8);
            box-shadow: none;
            transform: translateY(0);
        }

        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Status and Progress Updates */
        .status-container {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 500;
            display: none;
            animation: fadeIn 0.4s;
            text-align: center;
            word-wrap: break-word;
        }

        .status-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .status-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .status-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #60a5fa;
            text-align: left;
        }

        .status-info pre {
            margin-top: 0.5rem;
            white-space: pre-wrap;
            font-size: 0.85rem;
            color: #cbd5e1;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="glass-card">
            <div class="header">
                <h1>ZK-Lock</h1>
                <p>Secure Access Management</p>
            </div>

            <div class="tabs">
                <div class="tab active" data-target="issue">Issue</div>
                <div class="tab" data-target="read">Read</div>
                <div class="tab" data-target="revoke">Revoke</div>
            </div>

            <!-- Issue Tab -->
            <div id="tab-issue" class="tab-content active">
                <form id="issueForm">
                    <div class="form-group">
                        <label for="roomCode">Room Number</label>
                        <input type="text" id="roomCode" class="form-control" placeholder="e.g. 101" required
                            autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="startTime">Check-In Time</label>
                        <input type="datetime-local" id="startTime" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="endTime">Check-Out Time</label>
                        <input type="datetime-local" id="endTime" class="form-control" required>
                    </div>
                    <button type="submit" id="btnIssue" class="btn btn-issue">
                        <span class="btn-text">Issue Keycard</span>
                        <div class="spinner"></div>
                    </button>
                </form>
            </div>

            <!-- Read Tab -->
            <div id="tab-read" class="tab-content">
                <p style="color: var(--text-muted); margin-bottom: 1.5rem; text-align: center;">Place the card on the
                    encoder to read its data.</p>
                <button id="btnRead" class="btn btn-read">
                    <span class="btn-text">Read Card</span>
                    <div class="spinner"></div>
                </button>
            </div>

            <!-- Revoke Tab -->
            <div id="tab-revoke" class="tab-content">
                <p style="color: var(--text-muted); margin-bottom: 1.5rem; text-align: center;">Place the card on the
                    encoder to revoke (clear) its access.</p>
                <button id="btnRevoke" class="btn btn-revoke">
                    <span class="btn-text">Revoke Card</span>
                    <div class="spinner"></div>
                </button>
            </div>

            <div id="statusMessage" class="status-container"></div>
        </div>
    </div>

    <script src="frontend.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Tabs Logic
            const tabs = document.querySelectorAll('.tab');
            const contents = document.querySelectorAll('.tab-content');
            const statusMsg = document.getElementById('statusMessage');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));

                    tab.classList.add('active');
                    document.getElementById(`tab-${tab.dataset.target}`).classList.add('active');

                    statusMsg.style.display = 'none'; // hide status when switching tabs
                });
            });

            // Smart defaults
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            const checkIn = new Date(now);
            checkIn.setHours(14, 0, 0, 0);
            const checkOut = new Date(now);
            checkOut.setDate(checkOut.getDate() + 1);
            checkOut.setHours(12, 0, 0, 0);

            document.getElementById('startTime').value = checkIn.toISOString().slice(0, 16);
            document.getElementById('endTime').value = checkOut.toISOString().slice(0, 16);
        });

        function formatDateTime(datetimeLocalValue) {
            if (!datetimeLocalValue) return '';
            return datetimeLocalValue.replace('T', ' ') + ':00';
        }

        const encoder = new HotelLockEncoder('../index.php', '../index.php?action=proxy_encoder&endpoint=');

        function resetBtn(btnId, originalText) {
            const btn = document.getElementById(btnId);
            btn.disabled = false;
            btn.querySelector('.btn-text').textContent = originalText;
            btn.querySelector('.spinner').style.display = 'none';
        }

        function setBtnLoading(btnId) {
            const btn = document.getElementById(btnId);
            btn.disabled = true;
            btn.querySelector('.btn-text').textContent = 'Processing...';
            btn.querySelector('.spinner').style.display = 'block';
            document.getElementById('statusMessage').style.display = 'none';
        }

        function showStatus(message, type, extraData = null) {
            const statusMsg = document.getElementById('statusMessage');
            statusMsg.className = `status-container status-${type}`;
            if (extraData) {
                statusMsg.innerHTML = `<strong>${message}</strong><pre>${JSON.stringify(extraData, null, 2)}</pre>`;
            } else {
                statusMsg.textContent = message;
            }
            statusMsg.style.display = 'block';
        }

        // ISSUE
        document.getElementById('issueForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const roomCode = document.getElementById('roomCode').value.trim();
            const startTime = formatDateTime(document.getElementById('startTime').value);
            const endTime = formatDateTime(document.getElementById('endTime').value);

            setBtnLoading('btnIssue');
            try {
                const success = await encoder.provisionCard(roomCode, startTime, endTime);
                if (success) {
                    showStatus(`✅ Keycard ready for Room ${roomCode}`, 'success');
                    document.getElementById('roomCode').value = '';
                } else {
                    showStatus('❌ Provisioning failed.', 'error');
                }
            } catch (error) {
                showStatus('❌ ' + error.message, 'error');
            } finally {
                resetBtn('btnIssue', 'Issue Keycard');
            }
        });

        // READ
        document.getElementById('btnRead').addEventListener('click', async () => {
            setBtnLoading('btnRead');
            try {
                const data = await encoder.readCard();
                showStatus('✅ Card Read Successfully', 'info', data);
            } catch (error) {
                showStatus('❌ ' + error.message, 'error');
            } finally {
                resetBtn('btnRead', 'Read Card');
            }
        });

        // REVOKE
        document.getElementById('btnRevoke').addEventListener('click', async () => {
            setBtnLoading('btnRevoke');
            try {
                const success = await encoder.revokeCard();
                if (success) {
                    showStatus('✅ Card successfully revoked.', 'success');
                }
            } catch (error) {
                showStatus('❌ ' + error.message, 'error');
            } finally {
                resetBtn('btnRevoke', 'Revoke Card');
            }
        });
    </script>

</body>

</html>