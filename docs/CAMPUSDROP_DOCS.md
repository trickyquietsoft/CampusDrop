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

### ⚙️ Infrastructure Optimizations
To ensure reliability in a containerized environment, the following configurations are implemented:
- **Upload Constraints**: A `.user.ini` file is utilized to override default PHP limits, increasing `upload_max_filesize` to 20MB and `post_max_size` to 100MB to support high-resolution book cover images.
- **Volume Permissions**: To resolve ownership conflicts inherent to Docker named volumes, the `Dockerfile` ensures the `/var/www/html/uploads` directory is explicitly owned by the `www-data` user, preventing "Permission Denied" errors during runtime.

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
**Key Logic**: Environment Aware. It uses `getenv()` to pull credentials from Docker's environment variables, falling back to local XAMPP defaults.

#### `auth_api.php`
**Purpose**: User access control.
**Key Logic**: Uses `password_hash()` and `password_verify()` for security. Manages user identity via `$_SESSION`.

---

### B. Marketplace Logic
#### `upload_manager.php`
**Purpose**: Handles the physical file system operations for product images.
**Key Logic**: Validates MIME types, manages absolute volume paths for storage, and maintains relative paths for database records.

#### `listing_api.php`
**Purpose**: CRUD operations for books.
**Key Logic**:
- **Execution Guard**: Implements a `basename()` check that allows the file to operate as both a REST API (returning JSON) and a PHP library (returning arrays) depending on how it is accessed.
- **Response Integrity**: Utilizes Output Buffering (`ob_start` / `ob_clean`) to ensure that PHP notices or warnings do not pollute the JSON response body.
- **Scoped Retrieval**: Includes a `get_mine` action that automatically filters listings by the currently authenticated `user_id`.
- **Atomicity**: Wraps listing creation and image uploads in a **Database Transaction** to prevent "ghost" listings.

#### `post.php` (Frontend Submission)
**Purpose**: Interface for creating new listings.
**Key Logic**:
- **Safe Submission**: Uses `FormData` for native multipart/form-data handling.
- **UI State Management**: Disables the submit button and provides "Posting..." feedback to prevent duplicate entries.
- **Robust Error Handling**: Captures raw response text before parsing JSON to diagnose server-side crashes effectively.

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

### Adding a New Feature (Example: Adding \"Book Genre\")
1. **DB**: Update `schema_v3.sql` or add a migration script to `/mysql_init`.
2. **API**: Update `listing_api.php`'s `handleCreate` function.
3. **UI**: Add an input field in `post.php`.
4. **Display**: Update the loop in `index.php`.

---

## 🎯 5. Future Roadmap & Enhancements

To elevate CampusDrop from an MVP to a production-grade application, the following enhancements are prioritized:

### Trust & Safety
- **Email Validation**: Implement regex-based validation to ensure only verified university emails are accepted.
- **Reporting API**: Develop a reporting endpoint allowing users to flag inappropriate or fraudulent listings.

### Trade Lifecycle
- **Negotiation Flow**: Transition from a binary status to a full state machine (Requested $\rightarrow$ Countered $\rightarrow$ Accepted $\rightarrow$ Completed).
- **Notification System**: Implement an unread-message tracking system with UI badges for real-time alerts.

### Performance & UX
- **API Pagination**: Implement `LIMIT` and `OFFSET` in the marketplace retrieval logic to ensure scalability with large inventories.
- **Advanced Filtering**: Expand search capabilities to include filtering by book condition, price range, and specific campus university.
- **Profile Management**: Add functionality for users to update their personal profiles and secure passwords.

---
