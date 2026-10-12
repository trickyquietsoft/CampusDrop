# CampusDrop - Textbook Marketplace

CampusDrop is a decoupled monolith textbook marketplace designed for university students to buy, sell, and trade textbooks within their own campus.

## 🚀 Getting Started with XAMPP

### 1. Prerequisites
- [XAMPP](https://www.apachefriends.org/index.html) installed on your machine.
- A web browser (Chrome, Firefox, Edge).

### 2. Installation
1.  **Copy Files**: Move the entire project folder to your XAMPP `htdocs` directory.
    - Path: `C:\xampp\htdocs\campd\` (or keep the files directly in `htdocs` if preferred).
2.  **Start Services**:
    - Open the **XAMPP Control Panel**.
    - Start **Apache** (Web Server).
    - Start **MySQL** (Database).

### 3. Database Setup
1.  Open your browser and navigate to `http://localhost/phpmyadmin/`.
2.  **Create Database**: Create a new database named `campusdrop_schema`.
3.  **Import Schema**:
    - Click the **Import** tab.
    - Select the `schema_v3.sql` file from the project root.
    - Click **Go/Import**.

### 4. Configuration
1.  Open `db_connect.php`.
2.  Ensure the database credentials match your XAMPP setup:
    - `DB_HOST`: `localhost`
    - `DB_NAME`: `campusdrop_schema`
    - `DB_USER`: `root`
    - `DB_PASS`: ` ` (empty by default in XAMPP).

### 5. Run the App
Navigate to: `http://localhost/campd/index.php`

## 🛠 Tech Stack
- **Backend**: PHP 8.x
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript (Fetch API)
- **Architecture**: Decoupled Monolith (PHP APIs serving JSON to Frontend)

---
**Author:** Ronnie Short  
**Review:** Gemma 4:31B
