
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CampusDrop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">CampusDrop</a>
    </header>

    <div class="container" style="max-width: 400px; margin-top: 4rem;">
        <section style="text-align: center; margin-bottom: 2rem;">
            <h2>Welcome Back</h2>
            <p>Login to manage your listings and contact sellers.</p>
        </section>

        <form id="loginForm" style="background: white; padding: 2rem; border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow);">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Email Address</label>
                <input type="email" id="email" name="email" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;" placeholder="student@college.edu">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Password</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;" placeholder="••••••••">
            </div>
            <button type="submit" class="btn-search" style="width: 100%; font-size: 1rem; padding: 1rem;">Login</button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center;">
            <p>Don't have an account? <a href="register.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Register here</a></p>
            <a href="index.php" style="display: block; margin-top: 1rem; color: var(--secondary); text-decoration: none; font-size: 0.9rem;">← Back to Marketplace</a>
        </div>
    </div>

    <script>
    document.getElementById('loginForm').onsubmit = async (e) => {
        e.preventDefault();
        const data = {
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        };

        const res = await fetch('includes/auth_api.php?action=login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await res.json();
        if (result.success) {
            window.location.href = 'index.php';
        } else {
            alert(result.error);
        }
    };
    </script>
</body>
</html>
