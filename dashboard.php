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
    <title>My Dashboard - CampusDrop</title>
    <link rel="stylesheet" href="style.css">
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
        <h2 style="margin-bottom: 1.5rem;">Seller Dashboard</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <div style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border); text-align: center;">
                <h3 style="margin: 0; color: var(--secondary);">Active Listings</h3>
                <div id="activeCount" style="font-size: 2rem; font-weight: 800; color: var(--primary);">0</div>
            </div>
            <div style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border); text-align: center;">
                <h3 style="margin: 0; color: var(--secondary);">Pending Trades</h3>
                <div id="pendingCount" style="font-size: 2rem; font-weight: 800; color: var(--accent);">0</div>
            </div>
        </div>
        <h3 style="margin-bottom: 1rem;">My Books</h3>
        <div class="marketplace-grid" id="myListings"></div>
    </div>
    <script>
    async function loadDashboard() {
        const res = await fetch('listing_api.php?action=get');
        const books = await res.json();
        const list = document.getElementById('myListings');
        list.innerHTML = '';
        books.forEach(book => {
            const div = document.createElement('div');
            div.className = 'product-card';
            div.innerHTML = `<div class="product-details">
                <h3 class="product-title">${book.title}</h3>
                <div class="product-price">$${book.price}</div>
                <div class="product-meta">${book.status}</div>
                <button onclick="deleteListing(${book.listing_id})" class="btn" style="background: #ef4444; color: white; border: none; cursor: pointer; width: 100%;">Delete Listing</button>
            </div>`;
            list.appendChild(div);
        });
    }
    async function deleteListing(id) {
        if (!confirm('Are you sure?')) return;
        const formData = new FormData();
        formData.append('id', id);
        const res = await fetch('listing_api.php?action=delete', { method: 'POST', body: formData });
        const result = await res.json();
        if (result.success) { alert('Removed.'); loadDashboard(); } else { alert(result.error); }
    }
    window.onload = loadDashboard;
    </script>
</body>
</html>
