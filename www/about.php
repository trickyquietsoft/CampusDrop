
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | CampusDrop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">CampusDrop</a>
    <div class="nav-top">
        <a href="index.php" style="text-decoration:none; color:var(--text); font-weight:600;">Marketplace</a>
        <a href="about.php" style="text-decoration:none; color:var(--text); font-weight:600;">About</a>
        <a href="contact.php" style="text-decoration:none; color:var(--text); font-weight:600;">Contact</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="my_account.php" style="text-decoration:none; color:var(--primary); font-weight:600;">My Account</a>
        <?php else: ?>
            <a href="login.php" style="text-decoration:none; color:var(--text); font-weight:600;">Login</a>
            <a href="register.php" class="btn-post" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Register</a>
        <?php endif; ?>
        <a href="post.php" class="btn-post">Sell a Book</a>
    </div>
</header>

<section class="hero">
    <div class="container">
        <h1>Empowering the Eagle Community</h1>
        <p>Making textbooks affordable, accessible, and sustainable for every NCCU student.</p>
    </div>
</section>

<div class="container">
    <div style="text-align: center; max-width: 800px; margin: 0 auto 60px auto;">
        <h2>Our Mission</h2>
        <p>CampusDrop was born out of a simple realization: textbooks are too expensive. We believe that academic success shouldn't be gated by the cost of a book. Our platform allows students to buy and sell textbooks directly with their peers, keeping money in students' pockets and books in the classroom.</p>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <h3>Student-to-Student</h3>
            <p>No middlemen. No corporate markups. Just Eagles helping Eagles find the resources they need.</p>
        </div>
        <div class="feature-card">
            <h3>Safe & Local</h3>
            <p>Exchanges happen right here on campus. No shipping fees, no waiting for the mail, and no scams.</p>
        </div>
        <div class="feature-card">
            <h3>Sustainable</h3>
            <p>By recycling textbooks within the university, we reduce waste and promote a circular campus economy.</p>
        </div>
    </div>
</div>

<footer>
    &copy; 2026 CampusDrop | North Carolina Central University Capstone Project
</footer>

</body>
</html>
