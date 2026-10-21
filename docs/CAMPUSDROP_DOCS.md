# 📚 CampusDrop Developer Notebook

This document serves as the complete technical reference for the CampusDrop textbook marketplace.

---

## 🛠️ 1. Architecture Overview
CampusDrop is built as a **Decoupled Monolith**. 
- **Frontend**: HTML/CSS/PHP (Templating).
- **Backend API**: PHP scripts (`auth_api.php`, `listing_api.php`) that handle logic and return JSON.
- **Database**: MySQL (InnoDB) for structured data.
- **Storage**: Local filesystem for product images.

---

## 🗄️ 2. Database Schema (`schema_v2.sql`)
The database is centered around four key entities:
1. **Users**: Stores student profiles and credentials.
2. **Listings**: The core textbook data (Price, Condition, ISBN).
3. **Listing Images**: Links specific images to a listing.
4. **Trades/Messages**: Manages the negotiation process between buyer and seller.

---

## 📂 3. The Codebase

### A. Core Infrastructure
#### `db_connect.php`
**Purpose**: The single point of contact for the database.
**Key Logic**: Uses PDO with `ERRMODE_EXCEPTION` to ensure database errors are caught and handled.

```php
// Snippet: Connection Setup
$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
```

#### `auth_api.php`
**Purpose**: User access control.
**Key Logic**: Uses `password_hash()` and `password_verify()` for security. Manages user identity via `$_SESSION`.

---

### B. Marketplace Logic
#### `upload_manager.php`
**Purpose**: Handles the "Production Pictures" requirement.
**Key Logic**: 
1. Validates MIME types (JPEG/PNG/WebP).
2. Creates a dedicated folder for each post: `/uploads/books/{id}/`.
3. Saves file paths to `listing_images` table.

#### `listing_api.php`
**Purpose**: CRUD operations for books.
**Key Logic**:
- **Create**: Wraps the listing insertion and image upload in a **Database Transaction**. If the image upload fails, the listing is rolled back to prevent "ghost" listings.
- **Search**: Implemented using `LIKE` operators for flexible keyword search.

---

### C. Frontend
#### `index.php` & `post.php`
**Purpose**: User Interface.
**Key Logic**: Use CSS variables for theme consistency and HTML5 forms with `enctype="multipart/form-data"` for image support.

---

## 🚀 4. How to Make Changes

### Adding a New Feature (Example: Adding "Book Genre")
1. **DB**: Update `schema_v2.sql` to add a `genre` column to the `listings` table.
2. **API**: Update `listing_api.php`'s `handleCreate` function to accept and insert the `genre` field.
3. **UI**: Add an `<input>` or `<select>` for Genre in `post.php`.
4. **Display**: Update the loop in `index.php` to show the genre on the product card.

---

## 📝 5. Full Code Appendix
*(The entire code for every file is available in the /campusdrop directory)*

## 📝 Change Log
- Added `messages.php`: Implemented trade chat and state management.
- Added `dashboard.php`: Implemented seller inventory and listing deletion.
- Integrated `trade_api.php` with Frontend UI for the complete transaction loop.
- 2026-10-06 16:15 | Feature: Implemented Campus-Based Scoping (JOIN logic in `listing_api.php` to filter books by user's university).
- 2026-10-06 16:30 | Feature: Added University Badging to `index.php` and `product.php` for at-a-glance campus identification.
- 2026-10-06 16:45 | Feature: Scalability Pivot; shifted architecture to support any .edu institution.
- 2026-10-06 16:50 | Strategy: Developed "A-Grade" presentation script, risk mitigation strategies, and demo storyboard.
- 2026-10-06 17:05 | Bug Fix: Resolved message alignment issue in `messages.php` by implementing dynamic styling based on `sender_id`.