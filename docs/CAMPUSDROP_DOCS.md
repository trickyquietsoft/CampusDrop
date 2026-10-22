# 📚 CampusDrop Developer Notebook

This document serves as the complete technical reference for the CampusDrop textbook marketplace.

---

## 🛠️ 1. Architecture Overview
CampusDrop is built as a **Containerized Decoupled Monolith**. 

- **Frontend**: HTML/CSS/PHP (Templating) located in the `/www` directory.
- **Backend API**: PHP scripts (`auth_api.php`, `listing_api.php`) that handle logic and return JSON.
- **Database**: MySQL 8.0 (InnoDB) for structured data.
- **Infrastructure**: Dockerized environment using Docker Compose for orchestration.
- **Storage**: Persistent Docker volumes for database data and product images.

---

## 🗄️ 2. Database Schema (`schema_v3.sql`)
The database is centered around four key entities:
1. **Users**: Stores student profiles and credentials.
2. **Listings**: The core textbook data (Price, Condition, ISBN).
3. **Listing Images**: Links specific images to a listing.
4. **Trades/Messages**: Manages the negotiation process between buyer and seller.

**Automation**: Database initialization is handled automatically via the `/mysql_init` directory, which runs SQL scripts upon the first container launch.

---

## 📂 3. The Codebase

### A. Core Infrastructure
#### `db_connect.php`
**Purpose**: The single point of contact for the database.
**Key Logic**: Updated to be **Environment Aware**. It uses `getenv()` to pull credentials from Docker's environment variables, falling back to local XAMPP defaults if not found. This allows a single codebase to run in both local and production environments.

```php
// Snippet: Universal Connection Setup
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'campusdrop_schema');
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
2. Creates a dedicated folder for each post.
3. Saves file paths to `listing_images` table.

#### `listing_api.php`
**Purpose**: CRUD operations for books.
**Key Logic**:
- **Create**: Wraps the listing insertion and image upload in a **Database Transaction**. If the image upload fails, the listing is rolled back to prevent "ghost" listings.
- **Search**: Implemented using `LIKE` operators for flexible keyword search.

---

### C. Project Structure
The project follows a standardized container layout:
- `/www`: All web-facing source code, assets, and includes.
- `/php`: contains the `Dockerfile` used to build the PHP-Apache environment.
- `/mysql_init`: SQL scripts for automatic database setup.
- `compose.yaml`: Orchestrates the `webserver` and `mysql_db` services.
- `.env`: Stores sensitive database credentials.

---

## 🚀 4. Deployment & Maintenance

### Docker Deployment
1. Configure `.env` with database credentials.
2. Run `docker compose up -d --build`.
3. Access via `http://localhost:8080`.

### Adding a New Feature (Example: Adding "Book Genre")
1. **DB**: Update `schema_v3.sql` or add a migration script to `/mysql_init` to add a `genre` column to the `listings` table.
2. **API**: Update `listing_api.php`'s `handleCreate` function to accept and insert the `genre` field.
3. **UI**: Add an `<input>` or `<select>` for Genre in `post.php`.
4. **Display**: Update the loop in `index.php` to show the genre on the product card.

---
