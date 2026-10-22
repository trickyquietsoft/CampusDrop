<?php
/**
 * Author: Ronnie Short
 * Reviewer: Gemma 4
 */

session_start();
if (isset($_SESSION['admin_id'])) { header('Location: admin_dashboard.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal | CampusDrop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; font-family: sans-serif; }
        .auth-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        .auth-header h2 { color: #d32f2f; margin: 0; }
        .tab-group { display: flex; gap: 10px; margin-bottom: 1.5rem; }
        .tab { flex: 1; padding: 10px; text-align: center; cursor: pointer; background: #eee; border-radius: 6px; font-weight: 600; }
        .tab.active { background: #d32f2f; color: white; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-size: 0.9rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { width: 100%; padding: 0.75rem; background: #d32f2f; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .hidden { display: none; }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="auth-header">
            <h2>🛡️ Admin Portal</h2>
            <p>Secure System Management</p>
        </div>

        <div class="tab-group">
            <div id="loginTab" class="tab active" onclick="switchTab('login')">Login</div>
            <div id="regTab" class="tab" onclick="switchTab('register')">Create Admin</div>
        </div>

        <!-- Login Form -->
        <form id="loginForm">
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="l_email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="l_pass" required>
            </div>
            <button type="submit" class="btn-submit">Enter Dashboard</button>
        </form>

        <!-- Register Form -->
        <form id="regForm" class="hidden">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" id="r_fn" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" id="r_ln" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="r_email" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="r_pass" required>
            </div>
            <div class="form-group">
                <label>Access Level</label>
                <select id="r_level">
                    <option value="moderator">Moderator</option>
                    <option value="superadmin">Super Admin</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Create Admin Account</button>
        </form>
    </div>

    <script>
        function switchTab(tab) {
            document.getElementById('loginForm').classList.toggle('hidden', tab === 'register');
            document.getElementById('regForm').classList.toggle('hidden', tab === 'login');
            document.getElementById('loginTab').classList.toggle('active', tab === 'login');
            document.getElementById('regTab').classList.toggle('active', tab === 'register');
        }

        document.getElementById('loginForm').onsubmit = async (e) => {
            e.preventDefault();
            const res = await fetch('includes/admin_auth_api.php?action=login', {
                method: 'POST',
                body: JSON.stringify({ email: document.getElementById('l_email').value, password: document.getElementById('l_pass').value })
            });
            const data = await res.json();
            if (data.success) window.location.href = 'admin_dashboard.php';
            else alert(data.error);
        };

        document.getElementById('regForm').onsubmit = async (e) => {
            e.preventDefault();
            const res = await fetch('includes/admin_auth_api.php?action=register', {
                method: 'POST',
                body: JSON.stringify({
                    first_name: document.getElementById('r_fn').value,
                    last_name: document.getElementById('r_ln').value,
                    email: document.getElementById('r_email').value,
                    password: document.getElementById('r_pass').value,
                    access_level: document.getElementById('r_level').value
                })
            });
            const data = await res.json();
            if (data.success) {
                alert('Admin created! Please login.');
                switchTab('login');
            } else alert(data.error);
        };
    </script>
</body>
</html>
