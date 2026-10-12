<?php
session_start();
require_once 'db_connect.php';

// Security: Only logged-in users can access the account page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Fetch User Profile Data
$stmt = $pdo->prepare("SELECT first_name, last_name, university, email, account_status FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account | CampusDrop</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .account-container { max-width: 900px; margin: 4rem auto; padding: 0 1rem; }
        .profile-card { 
            background: white; padding: 2rem; border-radius: 12px; 
            border: 1px solid var(--border); box-shadow: var(--shadow);
            margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;
        }
        .tabs { display: flex; gap: 1rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--border); }
        .tab-btn { 
            padding: 1rem 2rem; cursor: pointer; background: none; border: none; 
            font-weight: 600; color: var(--secondary); transition: 0.3s;
        }
        .tab-btn.active { color: var(--primary); border-bottom: 3px solid var(--primary); }
        .tab-content { display: none; background: white; padding: 2rem; border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow); }
        .tab-content.active { display: block; }
        
        /* Grid for Posts */
        .item-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem; }
        .item-card { border: 1px solid var(--border); padding: 1rem; border-radius: 8px; text-align: center; }
        .item-card img { width: 100%; height: 150px; object-fit: cover; border-radius: 4px; }
        
        /* Trade & Chat Layout */
        .trade-layout { display: grid; grid-template-columns: 300px 1fr; gap: 2rem; height: 500px; }
        .trade-list { overflow-y: auto; border-right: 1px solid var(--border); }
        .trade-item { 
            padding: 1rem; border-bottom: 1px solid var(--border); cursor: pointer; 
            transition: 0.2s; display: flex; justify-content: space-between; align-items: center;
        }
        .trade-item:hover, .trade-item.active { background: var(--accent); }
        .chat-window { display: flex; flex-direction: column; height: 100%; }
        .messages-area { flex: 1; overflow-y: auto; padding: 1rem; background: #f9f9f9; border-radius: 8px; margin-bottom: 1rem; }
        .msg { margin-bottom: 0.8rem; padding: 0.5rem 1rem; border-radius: 8px; max-width: 80%; }
        .msg.sent { background: var(--primary); color: white; align-self: flex-end; margin-left: auto; }
        .msg.received { background: #eee; color: #333; }
        .chat-input-group { display: flex; gap: 0.5rem; }
        .chat-input-group input { flex: 1; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; }
    </style>
</head>
<body>
    <header>
        <a href="index.php" class="logo">CampusDrop</a>
        <div class="nav-top">
            <a href="index.php" style="text-decoration:none; color:var(--text); font-weight:600;">Marketplace</a>
            <a href="my_account.php" style="text-decoration:none; color:var(--primary); font-weight:600;">My Account</a>
        </div>
    </header>

    <div class="account-container">
        <!-- Profile Summary -->
        <div class="profile-card">
            <div>
                <h1 style="margin: 0;"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
                <?php 
                    $username = !empty($user['email']) ? '@' . explode('@', $user['email'])[0] : '@user';
                ?>
                <p style="color: var(--primary); font-weight: 600; margin: 0;"><?php echo htmlspecialchars($username); ?></p>
                <p style="color: var(--secondary); margin: 0.5rem 0;"><?php echo htmlspecialchars($user['university']); ?></p>
                <span class="status-badge" style="font-size: 0.8rem; padding: 2px 8px; background: #eee; border-radius: 12px;">
                    Status: <?php echo htmlspecialchars($user['account_status']); ?>
                </span>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs">
            <button class="tab-btn active" onclick="openTab(event, 'posts')">My Posts</button>
            <button class="tab-btn" onclick="openTab(event, 'trades')">Trades & Messages</button>
        </div>

        <!-- Tab A: My Posts -->
        <div id="posts" class="tab-content active">
            <div class="item-grid" id="my-posts-grid">
                <!-- Loaded via JS -->
                <p>Loading your listings...</p>
            </div>
        </div>

        <!-- Tab B: Trades & Messages -->
        <div id="trades" class="tab-content">
            <div class="trade-layout">
                <div class="trade-list" id="trade-list">
                    <p style="padding: 1rem;">Loading trades...</p>
                </div>
                <div class="chat-window" id="chat-window">
                    <div class="messages-area" id="messages-area">
                        <p style="text-align: center; color: var(--secondary);">Select a trade to start chatting</p>
                    </div>
                    <div class="chat-input-group" id="chat-input-group" style="display: none;">
                        <input type="text" id="msg-text" placeholder="Type a message...">
                        <button class="btn-post" onclick="sendMessage()">Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let activeTradeId = null;

        function openTab(evt, tabName) {
            const contents = document.getElementsByClassName("tab-content");
            for (let i = 0; i < contents.length; i++) contents[i].classList.remove("active");
            
            const buttons = document.getElementsByClassName("tab-btn");
            for (let i = 0; i < buttons.length; i++) buttons[i].classList.remove("active");
            
            document.getElementById(tabName).classList.add("active");
            evt.currentTarget.classList.add("active");

            if (tabName === 'posts') loadMyPosts();
            if (tabName === 'trades') loadTrades();
        }

        // --- Posts Logic ---
        async function loadMyPosts() {
            const res = await fetch('listing_api.php?action=get_mine');
            const data = await res.json();
            // Filter for current user (simplified: API should ideally handle ?seller_id=)
            // For now we assume the API returns all and we filter locally or API is updated
            const grid = document.getElementById('my-posts-grid');
            grid.innerHTML = data.length ? '' : '<p>No listings found.</p>';
            
            data.forEach(item => {
                grid.innerHTML += `
                    <div class="item-card">
                        <img src="${item.image_path || 'placeholder.jpg'}" alt="${item.title}">
                        <h3>${item.title}</h3>
                        <p>$${item.price || 'Trade'}</p>
                        <button class="btn-post" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;" 
                                onclick="deleteListing(${item.listing_id})">Delete</button>
                    </div>
                `;
            });
        }

        async function deleteListing(id) {
            if (!confirm('Delete this listing?')) return;
            // Changed method to POST for better XAMPP/Apache compatibility
            const res = await fetch(`listing_api.php?action=delete&id=${id}`, { method: 'POST' });
            const result = await res.json();
            if (result.success) loadMyPosts(); else alert(result.error);
        }

        // --- Trades & Messages Logic ---
        async function loadTrades() {
            const res = await fetch('trade_api.php?action=get_trades');
            const data = await res.json();
            const list = document.getElementById('trade-list');
            list.innerHTML = data.length ? '' : '<p style="padding:1rem;">No active trades.</p>';

            data.forEach(trade => {
                list.innerHTML += `
                    <div class="trade-item" onclick="selectTrade(${trade.trade_id}, this)">
                        <div>
                            <strong>${trade.book_title}</strong><br>
                            <small>${trade.partner_name}</small>
                        </div>
                        <span class="status-badge" style="font-size: 0.7rem;">${trade.trade_status}</span>
                    </div>
                `;
            });
        }

        async function selectTrade(tradeId, element) {
            activeTradeId = tradeId;
            document.querySelectorAll('.trade-item').forEach(el => el.classList.remove('active'));
            element.classList.add('active');
            
            document.getElementById('chat-input-group').style.display = 'flex';
            loadMessages(tradeId);
        }

        async function loadMessages(tradeId) {
            const res = await fetch(`trade_api.php?action=get_messages&trade_id=${tradeId}`);
            const data = await res.json();
            const area = document.getElementById('messages-area');
            area.innerHTML = '';
            
            data.forEach(msg => {
                const isSent = msg.sender_id == <?php echo $userId; ?>;
                area.innerHTML += `<div class="msg ${isSent ? 'sent' : 'received'}">${msg.message_text}</div>`;
            });
            area.scrollTop = area.scrollHeight;
        }

        async function sendMessage() {
            const input = document.getElementById('msg-text');
            const text = input.value;
            if (!text || !activeTradeId) return;

            const formData = new FormData();
            formData.append('trade_id', activeTradeId);
            formData.append('message', text);

            const res = await fetch('trade_api.php?action=send_message', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            if (result.success) {
                input.value = '';
                loadMessages(activeTradeId);
            } else alert(result.error);
        }

        // Initial load
        window.onload = () => {
            loadMyPosts();
        };
    </script>
</body>
</html>
