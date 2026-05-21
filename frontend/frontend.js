const API = '../index.php';

async function readCard() {
    const res = await fetch(`${API}?action=read_card`);
    return await res.json();
}

async function writeCard(room, validDate) {
    const res = await fetch(`${API}?action=write_card&room=${encodeURIComponent(room)}&validDate=${encodeURIComponent(validDate)}`);
    return await res.json();
}
