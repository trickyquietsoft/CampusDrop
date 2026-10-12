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
    <title>Book Details - CampusDrop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">CampusDrop</a>
        <div class="nav-top">
            <div style="margin-right: 1rem; font-size: 0.9rem; font-weight: 600;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="auth_api.php?action=logout" style="text-decoration:, none; color: var(--secondary);">Logout</a>
                <?php else: ?>
                    <a href="login.php" style="text-decoration: none; color: var(--secondary);">Login</a>
                <?php endif; ?>
            </div>
            <a href="post.php" class="btn-post">Sell a Book</a>
        </div>
    </header>
    <div class="container" style="max-width: 900px; margin-top: 2rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; background: white; padding: 2rem; border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow);">
            <div class="product-gallery">
                <img id="mainImage" src="https://via.placeholder.com/400" style="width: 100%; border-radius: 8px; border: 1px solid var(--border);">
                <div id="thumbGallery" style="display: flex; gap: 0.5rem; margin-top: 1rem;"></div>
            </div>
            <div class="product-info">
                <h1 id="bookTitle" style="margin: 0; font-size: 1.8rem;">Loading...</h1>
                <p id="bookAuthor" style="color: var(--secondary); font-size: 1.1rem; margin-bottom: 1rem;">...</p>
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <div style="background: #f8fafc; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--border);">
                        <span style="font-size: 0.8rem; color: var(--secondary);">Course</span><br>
                        <strong id="bookCourse">...</strong>
                    </div>
                    <div style="background: #f8fafc; padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--border);">
                        <span style="font-size: 0.8rem; color: var(--secondary);">Type</span><br>
                        <strong id="bookType">...</strong>
                    </div>
                </div>
                <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 1.5rem;">
                    <div style="font-size: 0.9rem; color: var(--secondary);">Asking Price</div>
                    <div id="bookPrice" style="font-size: 2rem; font-weight: 800; color: var(--primary);">$0.00</div>
                </div>
                <div style="margin-bottom: 1.5rem;">
                    <strong style="display: block; margin-bottom: 0.5rem;">Condition:</strong>
                    <span id="bookCondition" style="background: #e2e8f0; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.9rem;">...</span>
                </div>
                <div style="margin-bottom: 2rem;">
                    <strong style="display: block; margin-bottom: 0.5rem;">Description:</strong>
                    <p id="bookDescription" style="line-height: 1.6; color: var(--text);">Loading description...</p>
                </div>
                <div style="border-top: 1px solid var(--border); padding-top: 1.5rem;">
                    <strong style="display: block; margin-bottom: 0.5rem;">Seller:</strong>
                    <div id="sellerInfo" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">...</div>
                    <div id="sellerUni" style="font-size: 0.8rem; color: var(--secondary); margin-bottom: 1rem;">...</div>
                    <button id="contactBtn" class="btn-buy" style="width: 100%; border:none; cursor:pointer;">Contact Seller / Propose Trade</button>
                </div>
            </div>
        </div>
        <div style="margin-top: 2rem; text-align: center;">
            <a href="index.php" style="color: var(--secondary); text-decoration: none;">← Back to Marketplace</a>
        </div>
    </div>
    <script>
    async function loadProduct() {
        const params = new URLSearchParams(window.location.search);
        const id = params.get('id');
        if (!id) return;
        const res = await fetch(`listing_api.php?action=get&id=${id}`);
        const book = await res.json();
        if (!book || book.error) {
            alert('Book not found');
            return;
        }
        document.getElementById('bookTitle').innerText = book.title;
        document.getElementById('bookAuthor').innerText = 'By ' + book.author;
        document.getElementById('bookPrice').innerText = book.price ? '$' + book.price : 'Trade Only';
        document.getElementById('bookCourse').innerText = book.course_code || 'N/A';
        document.getElementById('bookType').innerText = book.listing_type;
        document.getElementById('bookCondition').innerText = book.book_condition;
        document.getElementById('bookDescription').innerText = book.description || 'No description provided.';
        
        // Create username from email (e.g., "john.doe@nccu.edu" -> "@john.doe")
        const username = book.email ? '@' + book.email.split('@')[0] : 'Unknown User';
        document.getElementById('sellerInfo').innerText = `${book.first_name} ${book.last_name} (${username})`;
        
        document.getElementById('sellerUni').innerText = book.university;
        document.getElementById('mainImage').src = book.image_path || 'https://via.placeholder.com/400';
    }
    document.getElementById('contactBtn').onclick = async () => {
        const params = new URLSearchParams(window.location.search);
        const id = params.get('id');
        const offer = prompt("Enter your offer or a message to the seller:");
        if (!offer) return;
        const formData = new FormData();
        formData.append('listing_id', id);
        formData.append('offer', offer);
        const res = await fetch('trade_api.php?action=initiate', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) {
            alert('Request sent! Redirecting to your inbox...');
            window.location.href = 'messages.php';
        } else { alert('Error: ' + result.error); }
    };
    window.onload = loadProduct;
    </script>
</body>
</html>
