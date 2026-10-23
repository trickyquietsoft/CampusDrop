<?php 
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List a Textbook - CampusDrop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <a href="index.php" class="logo">CampusDrop</a>
        <div class="nav-top">
            <div style="margin-right: 1rem; font-size: 0.9rem; font-weight: 600;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="includes/auth_api.php?action=logout" style="text-decoration: none; color: var(--secondary);">Logout</a>
                <?php else: ?>
                    <a href="login.php" style="text-decoration: none; color: var(--secondary);">Login</a>
                <?php endif; ?>
            </div>
            <a href="post.php" class="btn-post">Sell a Book</a>
        </div>
    </header>

    <div class="container" style="max-width: 600px; margin-top: 2rem;">
        <section style="text-align: center; margin-bottom: 2rem;">
            <h2>Sell Your Textbook</h2>
            <p>Reach hundreds of students on campus. List your book in seconds.</p>
        </section>

        <form id="postForm" style="background: white; padding: 2rem; border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow);">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Product Images</label>
                <input type="file" id="images" name="images[]" multiple accept="image/*" style="width: 100%; padding: 0.5rem; border: 1px dashed var(--border); border-radius: 8px;">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Book Title</label>
                <input type="text" id="title" name="title" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Author</label>
                <input type="text" id="author" name="author" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">ISBN</label>
                    <input type="text" id="isbn" name="isbn" required style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Course Code</label>
                    <input type="text" id="course_code" name="course_code" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;" placeholder="e.g. COMP 4200">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Asking Price ($)</label>
                    <input type="number" step="0.01" id="price" name="price" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;" placeholder="0.00">
                </div>
                <div class="form-group">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Listing Type</label>
                    <select id="listing_type" name="listing_type" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
                        <option value="both">Sell or Trade</option>
                        <option value="sell">Sell Only</option>
                        <option value="trade">Trade Only</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Condition</label>
                <select id="condition" name="condition" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box;">
                    <option value="new">New</option>
                    <option value="like_new">Like New</option>
                    <option value="good" selected>Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Description</label>
                <textarea id="description" name="description" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border); border-radius: 8px; box-sizing: border-box; height: 100px;"></textarea>
            </div>
            <button type="submit" class="btn-search" style="width: 100%; font-size: 1rem; padding: 1rem;">List Item Now</button>
        </form>
        <div style="margin-top: 2rem; text-align: center;">
            <a href="index.php" style="color: var(--secondary); text-decoration: none;">← Back to Marketplace</a>
        </div>
    </div>
    <script>
    document.getElementById('postForm').onsubmit = async (e) => {
        e.preventDefault();

        const form = e.currentTarget;
        const submitButton = form.querySelector(
            'button[type="submit"]'
        );

        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Posting...';

        try {
            const formData = new FormData(form);

            const res = await fetch(
                'includes/listing_api.php?action=create',
                {
                    method: 'POST',
                    body: formData,
                    headers: {
                        Accept: 'application/json'
                    }
                }
            );

            const responseText = await res.text();

            let result;
            try {
                result = JSON.parse(responseText);
            } catch {
                console.error('Invalid API response:', {
                    status: res.status,
                    body: responseText
                });

                throw new Error(
                    `Server returned an invalid response (HTTP ${res.status}). ` +
                    'Check the browser console and PHP logs.'
                );
            }

            if (!res.ok || !result.success) {
                console.error('Listing API error:', {
                    status: res.status,
                    result
                });

                throw new Error(
                    result.error ||
                    `Request failed with HTTP ${res.status}.`
                );
            }

            alert('Book listed successfully!');
            window.location.assign(result.redirect || 'index.php');

        } catch (error) {
            console.error('Book submission failed:', error);
            alert(error.message || 'Unable to post your book.');

        } finally {
            submitButton.disabled = false;
            submitButton.textContent = originalText;
        }
    };
    </script>
</body>
</html>
