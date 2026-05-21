<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZK-Lock</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f172a;
            --glass-bg: rgba(30, 41, 59, 0.6);
            --glass-border: rgba(255, 255, 255, 0.08);
            --primary: #3b82f6;
            --accent: #8b5cf6;
            --text: #f8fafc;
            --muted: #94a3b8;
            --success: #10b981;
            --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
        body {
            background: var(--bg);
            background-image: radial-gradient(circle at top right, rgba(59,130,246,0.15), transparent 40%),
                              radial-gradient(circle at bottom left, rgba(139,92,246,0.15), transparent 40%);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2.5rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            position: relative;
            overflow: hidden;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }
        h1 {
            text-align: center;
            font-size: 1.8rem;
            margin-bottom: 0.4rem;
            background: linear-gradient(to right, #60a5fa, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .subtitle { text-align: center; color: var(--muted); font-size: 0.9rem; margin-bottom: 2rem; }
        .tabs {
            display: flex;
            gap: 0.5rem;
            background: rgba(15,23,42,0.6);
            padding: 0.5rem;
            border-radius: 14px;
            margin-bottom: 1.5rem;
        }
        .tab {
            flex: 1;
            padding: 0.7rem;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            color: var(--muted);
            transition: all 0.3s;
        }
        .tab.active { background: var(--primary); color: white; box-shadow: 0 4px 15px rgba(59,130,246,0.3); }
        .panel { display: none; }
        .panel.active { display: block; }
        label { display: block; font-size: 0.8rem; font-weight: 500; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; }
        input {
            width: 100%;
            background: rgba(15,23,42,0.6);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 0.9rem 1rem;
            color: var(--text);
            font-size: 1rem;
            outline: none;
            margin-bottom: 1.2rem;
            transition: border-color 0.3s;
        }
        input:focus { border-color: rgba(59,130,246,0.5); box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
        .btn {
            width: 100%;
            border: none;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover:not(:disabled) { transform: translateY(-2px); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-write { background: linear-gradient(135deg, var(--primary), var(--accent)); }
        .btn-write:hover:not(:disabled) { box-shadow: 0 8px 20px rgba(59,130,246,0.4); }
        .btn-read { background: linear-gradient(135deg, var(--success), #059669); }
        .btn-read:hover:not(:disabled) { box-shadow: 0 8px 20px rgba(16,185,129,0.4); }
        .status {
            margin-top: 1.2rem;
            padding: 0.9rem;
            border-radius: 10px;
            font-size: 0.9rem;
            display: none;
            word-break: break-word;
        }
        .status.ok { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.2); color: #34d399; }
        .status.err { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); color: #f87171; }
        .status pre { margin-top: 0.5rem; white-space: pre-wrap; font-size: 0.8rem; color: #cbd5e1; }
    </style>
</head>
<body>
<div class="card">
    <h1>ZK-Lock</h1>
    <p class="subtitle">Card Reader</p>

    <div class="tabs">
        <div class="tab active" data-panel="write">Write Card</div>
        <div class="tab" data-panel="read">Read Card</div>
    </div>

    <!-- Write -->
    <div id="panel-write" class="panel active">
        <label for="room">Room (Building.Floor.Room)</label>
        <input type="text" id="room" placeholder="e.g. 1.1.22" autocomplete="off">
        <label for="validDate">Valid Until</label>
        <input type="datetime-local" id="validDate">
        <button class="btn btn-write" id="btnWrite">Write Card</button>
    </div>

    <!-- Read -->
    <div id="panel-read" class="panel">
        <p style="color:var(--muted); margin-bottom:1.2rem; text-align:center;">Place card on encoder then click Read.</p>
        <button class="btn btn-read" id="btnRead">Read Card</button>
    </div>

    <div id="status" class="status"></div>
</div>

<script src="frontend.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab');
    const panels = document.querySelectorAll('.panel');
    const status = document.getElementById('status');

    tabs.forEach(t => t.addEventListener('click', () => {
        tabs.forEach(x => x.classList.remove('active'));
        panels.forEach(x => x.classList.remove('active'));
        t.classList.add('active');
        document.getElementById('panel-' + t.dataset.panel).classList.add('active');
        status.style.display = 'none';
    }));

    function showStatus(msg, type, data) {
        status.className = 'status ' + type;
        status.innerHTML = data
            ? `<strong>${msg}</strong><pre>${JSON.stringify(data, null, 2)}</pre>`
            : msg;
        status.style.display = 'block';
    }

    // Default valid date: tomorrow at 12:00
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(12, 0, 0, 0);
    tomorrow.setMinutes(tomorrow.getMinutes() - tomorrow.getTimezoneOffset());
    document.getElementById('validDate').value = tomorrow.toISOString().slice(0, 16);

    document.getElementById('btnWrite').addEventListener('click', async () => {
        const room = document.getElementById('room').value.trim();
        const validDateRaw = document.getElementById('validDate').value;
        if (!room) return showStatus('Enter a room number.', 'err');
        if (!validDateRaw) return showStatus('Enter a valid date.', 'err');

        // Convert datetime-local to YYYYMMDDHHmm
        const validDate = validDateRaw.replace(/[-T:]/g, '').slice(0, 12);

        const btn = document.getElementById('btnWrite');
        btn.disabled = true;
        btn.textContent = 'Writing...';
        status.style.display = 'none';

        try {
            const res = await writeCard(room, validDate);
            if (res.success) {
                showStatus('succeed Card written for room ' + room, 'ok');
                document.getElementById('room').value = '';
            } else {
                showStatus('failed ' + (res.error || 'Write failed'), 'err');
            }
        } catch (e) {
            showStatus('failed ' + e.message, 'err');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Write Card';
        }
    });

    document.getElementById('btnRead').addEventListener('click', async () => {
        const btn = document.getElementById('btnRead');
        btn.disabled = true;
        btn.textContent = 'Reading...';
        status.style.display = 'none';

        try {
            const res = await readCard();
            if (res.success) {
                showStatus('succeed Card read', 'ok', res.data);
            } else {
                showStatus('failed ' + (res.error || 'Read failed'), 'err');
            }
        } catch (e) {
            showStatus('failed ' + e.message, 'err');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Read Card';
        }
    });
});
</script>
</body>
</html>
