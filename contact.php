
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | CampusDrop</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <a href="index.php" class="logo">CampusDrop</a>
    <div class="nav-top">
        <a href="about.php" style="text-decoration:none; color:var(--text); font-weight:600;">About</a>
        <a href="contact.php" style="text-decoration:none; color:var(--text); font-weight:600;">Contact</a>
        <a href="post.php" class="btn-post">Sell a Book</a>
    </div>
</header>

<section class="hero">
    <div class="container">
        <h1>Get in Touch</h1>
        <p>Have a question or a suggestion? We're here to help.</p>
    </div>
</section>

<div class="container">
    <div class="contact-wrapper">
        <div>
            <h2>Reach Out to Us</h2>
            <p>Whether you're having trouble listing a book or just want to say hello, feel free to drop us a message.</p>
            
            <div style="margin-top: 2rem; line-height: 2;">
                <p><strong>📧 Email:</strong> support@campusdrop.com</p>
                <p><strong>📍 Location:</strong> Henderson, NC </p>
                <p><strong>⏰ Hours:</strong> Mon-Fri, 9am - 5pm</p>
            </div>
        </div>

        <div class="contact-form">
            <form action="process_contact.php" method="POST">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your name" required>
                </div>
                <div class="form-group">
                    <label for="email">NCCU Email</label>
                    <input type="email" id="email" name="email" placeholder="yourname@nccu.edu" required>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" placeholder="How can we help?">
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" placeholder="Write your message here..." required></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>
    </div>
</div>

<footer>
    &copy; 2026 CampusDrop | North Carolina Central University Capstone Project
</footer>

</body>
</html>
