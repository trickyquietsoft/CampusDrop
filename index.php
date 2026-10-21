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
    <title>CampusDrop | Textbook Marketplace</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background-color: var(--background);">
    <header>
        <a href="index.php" class="logo">CampusDrop</a>
        <div class="nav-top">
            <a href="index.php" style="text-decoration:none; color:var(--text); font-weight:600;">Marketplace</a>
            <a href="about.php" style="text-decoration:none; color:var(--text); font-weight:600;">About</a>
            <a href="contact.php" style="text-decoration:none; color:var(--text); font-weight:600;">Contact</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="my_account.php" style="text-decoration:none; color:var(--primary); font-weight:600;">My Account</a>
                <a href="includes/auth_api.php?action=logout" style="text-decoration:none; color:var(--text); font-weight:600; margin-left: 1rem;">Logout</a>
            <?php else: ?>
                <a href="login.php" style="text-decoration:none; color:var(--text); font-weight:600;">Login</a>
                <a href="register.php" class="btn-post" style="padding: 0.3rem 0.6rem; font-size: 0.8rem;">Register</a>
            <?php endif; ?>
            <a href="post.php" class="btn-post">Sell a Book</a>
        </div>
    </header>

    <section class="search-section">
        <h1>Trade your next textbook</h1>
        <div class="search-bar-container">
            <form action="index.php" method="GET" style="display: flex; width: 100%; gap: 0.5rem;">
                <input type="text" name="search" class="search-bar" placeholder="Search by title, author, or ISBN...">
                <button type="submit" class="btn-search">Search</button>
            </form>
        </div>
    </section>

    <div class="container">
        <h2 style="margin-bottom: 1.5rem;">Trending Textbooks</h2>
        <div class="marketplace-grid">
            <?php
            require_once 'includes/listing_api.php';
            
            $search = $_GET['search'] ?? '';
            
            // Call the function directly (now returns an array)
            $books = handleGet($pdo);
            
            if (empty($books)) {
                echo '<p style="grid-column: 1/-1; text-align: center; color: var(--secondary);">No textbooks found. Try a different search!</p>';
            } else {
                foreach ($books as $book): ?>
                    <a href="product.php?id=<?php echo $book['listing_id']; ?>" class="product-card">
                        <img src="<?php echo $book['image_path'] ?? 'https://via.placeholder.com/300?text=Book'; ?>" alt="<?php echo $book['title']; ?>" class="product-image">
                        <div class="product-details">
                            <h3 class="product-title"><?php echo $book['title']; ?></h3>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div class="product-price">
                                    <?php echo ($book['price'] && $book['price'] > 0) ? '$' . $book['price'] : 'Trade'; ?>
                                </div>
                                <div style="font-size: 0.7rem; background: var(--primary); color: white; padding: 0.1rem 0.4rem; border-radius: 4px; font-weight: 600;"><?php echo $book['university']; ?></div>
                            </div>
                            <div class="product-meta"><?php echo $book['author']; ?></div>
                            <div class="btn-buy">View Listing</div>
                        </div>
                    </a>
                <?php endforeach; 
            }
            ?>
        </div>
    </div>
</body>
</html>
