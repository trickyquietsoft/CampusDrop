<?php
/**
 * Author: Ronnie Short
 * Reviewer: Gemma 4
 */
 session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - CampusDrop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .chat-container { display: grid; grid-template-columns: 300px 1fr; height: 80vh; background: white; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; }
        .trade-list { border-right: 1px solid var(--border); overflow-y: auto; background: #f8fafc; }
        .trade-item { padding: 1rem; border-bottom: 1px solid var(--border); cursor: pointer; transition: background 0.2s; }
        .trade-item:hover { background: #f1f5f9; }
        .trade-item.active { background: var(--primary); color: white; }
        .trade-item.active .status-pill { background: white; color: var(--primary); }
        .chat-window { display: flex; flex-direction: column; }
        .chat-header { padding: 1rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .chat-messages { flex: 1; overflow-y: auto; padding: 1rem; display: flex; flex-direction: column; gap: 1rem; background: #fff; }
        .msg { max-width: 70%; padding: 0.75rem; border-radius: 8px; font-size: 0.9rem; }
        .msg.sent { align-self: flex-end; background: var(--primary); color: white; }
        .msg.received { align-self: flex-start; background: #e2e8f0; color: var(--text); }
        .chat-input { padding: 1rem; border-top: 1px solid var(--border); display: flex; gap: 0.5rem; }
        .status-pill { font-size: 0.7rem; padding: 0.2rem 0.5rem; border-radius: 10px; background: #cbd5e1; color: #475569; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo">CampusDrop</a>
        <div class="nav-top">
            <a href="index.php" style="text-decoration: none; color: var(--secondary); font-weight: 600; font-size: 0.9rem; margin-right: 1rem;">Home</a>
            <a href="post.php" class="btn-post">Sell a Book</a>
        </div>
    </header>

    <div class="container">
        <h2 style="margin-bottom: 1.5rem;">Your Conversations</h2>
        <div class="chat-container">
            <div class="trade-list" id="tradeList"></div>
            <div class="chat-window" id="chatWindow" style="display: none;">
                <div class="chat-header">
                    <h3 id="chatPartner" style="margin: 0;">Select a trade</h3>
                    <div id="tradeControls">
                        <button id="completeBtn" class="btn" style="background: var(--accent); color: white; border: none; cursor: pointer;">Mark as Completed</button>
                    </div>
                </div>
                <div class="chat-messages" id="messageArea"></div>
                <form class="chat-input" id="msgForm">
                    <input type="text" id="msgText" style="flex: 1; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px;" placeholder="Type a message...">
                    <button type="submit" class="btn-search">Send</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    const currentUserId = <?php echo $_SESSION['user_id'] ?? 'null'; ?>;
    let activeTradeId = null;
    async function loadTrades() {
        const res = await fetch('trade_api.php?action=get_trades');
        const trades = await res.json();
        const list = document.getElementById('tradeList');
        list.innerHTML = '';
        trades.forEach(t => {
            const div = document.createElement('div');
            div.className = 'trade-item';
            div.innerHTML = `<strong>${t.book_title}</strong><br><small>${t.partner_name}</small> <span class="status-pill">${t.trade_status}</span>`;
            div.onclick = () => openTrade(t.trade_id, t.partner_name);
            list.appendChild(div);
        });
    }
    async function openTrade(id, partner) {
        activeTradeId = id;
        document.getElementById('chatWindow').style.display = 'flex';
        document.getElementById('chatPartner').innerText = partner;
        loadMessages();
    }
    async function loadMessages() {
        const res = await fetch(`trade_api.php?action=get_messages&trade_id=${activeTradeId}`);
        const msgs = await res.json();
        const area = document.getElementById('messageArea');
        area.innerHTML = '';
        msgs.forEach(m => {
            const div = document.createElement('div');
            // Correctly distinguish between sent and received messages
            div.className = m.sender_id == currentUserId ? 'msg sent' : 'msg received';
            div.innerText = m.message_text;
            area.appendChild(div);
        });
        area.scrollTop = area.scrollHeight;
    }
    document.getElementById('msgForm').onsubmit = async (e) => {
        e.preventDefault();
        const text = document.getElementById('msgText').value;
        if (!text) return;
        const formData = new FormData();
        formData.append('trade_id', activeTradeId);
        formData.append('message', text);
        await fetch('trade_api.php?action=send_message', { method: 'POST', body: formData });
        document.getElementById('msgText').value = '';
        loadMessages();
    };
    document.getElementById('completeBtn').onclick = async () => {
        if (!confirm('Mark as completed?')) return;
        const formData = new FormData();
        formData.append('trade_id', activeTradeId);
        formData.append('status', 'completed');
        await fetch('trade_api.php?action=update_status', { method: 'POST', body: formData });
        alert('Trade completed!');
        window.location.reload();
    };
    window.onload = loadTrades;
    </script>
</body>
</html>
