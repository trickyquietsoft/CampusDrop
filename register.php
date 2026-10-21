
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join CampusDrop - Register</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">CampusDrop</a>
    </header>

    <div class="container" style="max-width: 400px; margin-top: 4rem;">
        <section style="text-align: center; margin-bottom: 2rem;">
            <h2>Create Account</h2>
            <p>Join your campus community and start exchanging books.</p>
        </section>

        <form id="regForm" style="background: white; padding: 2rem; border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow);">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">First Name</label>
                <input type="text" id="first_name" name="first_name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Last Name</label>
                <input type="text" id="last_name" name="last_name" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Campus Email</label>
                <input type="email" id="email" name="email" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">University</label>
                <input type="text" id="university" name="university" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;" placeholder="e.g. NCCU">
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Password</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
            </div>
            <button type="submit" class="btn-search" style="width: 100%; font-size: 1rem; padding: 1rem;">Create Account</button>
        </form>

        <div style="margin-top: 1.5rem; text-align: center;">
            <p>Already have an account? <a href="login.php" style="color: var(--primary); font-weight: 600; text-decoration: none;">Login here</a></p>
            <a href="index.php" style="display: block; margin-top: 1rem; color: var(--secondary); text-decoration: none; font-size: 0.9rem;">← Back to Marketplace</a>
        </div>
    </div>

    <script>
    document.getElementById('regForm').onsubmit = async (e) => {
        e.preventDefault();
        const data = {
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            email: document.getElementById('email').value,
            university: document.getElementById('university').value,
            password: document.getElementById('password').value
        };

        const res = await fetch('includes/auth_api.php?action=register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await res.json();
        if (result.success) {
            alert('Account created! Please login.');
            window.location.href = 'login.php';
        } else {
            alert(result.error);
        }
    };
    </script>
</body>
</html>
