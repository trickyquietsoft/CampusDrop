# CampusDrop - Textbook Marketplace

CampusDrop is a decoupled monolith textbook marketplace designed for university students to buy, sell, and trade textbooks within their own campus.

## 🛠 Tech Stack
- **Backend**: PHP 8.2 (Apache)
- **Database**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (Fetch API)
- **Infrastructure**: Docker, Docker Compose
- **Architecture**: Decoupled Monolith (PHP APIs serving JSON to Frontend)

---

## 📦 Deployment with Docker (Recommended)

The project is containerized for consistent deployment across any Linux or Windows environment.

### 1. Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) or Docker Engine installed.
- Docker Compose installed.

### 2. Configuration
Create a `.env` file in the project root with the following variables:
```env
MYSQL_ROOT_PASSWORD=your_root_password
MYSQL_DATABASE=textbook_project
MYSQL_USER=admin
MYSQL_PASSWORD=your_admin_password
```

### 3. Installation & Launch
1. Open a terminal in the project root.
2. Run the following command to build and start the containers:
   ```bash
   docker compose up -d --build
   ```
3. The application will be available at: `http://localhost:8080`

### 4. Database Initialization
If you need to seed the database, place your `.sql` scripts in the `mysql_init/` directory before running `docker compose up`.

---

## 🚀 Local Development with XAMPP

### 1. Prerequisites
- [XAMPP](https://www.apachefriends.org/index.html) installed.

### 2. Installation
1. **Copy Files**: Move the `www` folder contents to your XAMPP `htdocs` directory.
2. **Start Services**: Open XAMPP Control Panel and start **Apache** and **MySQL**.

### 3. Database Setup
1. Navigate to `http://localhost/phpmyadmin/`.
2. Create a database named `campusdrop_schema`.
3. Import the `schema_v3.sql` file.

### 4. Configuration
Ensure `includes/db_connect.php` is configured correctly. The system now uses environment variables by default but falls back to XAMPP defaults if not detected.

---

## 📁 Project Structure
```text
CampusDrop/
├── compose.yaml        # Docker orchestration
├── .env                # Environment secrets
├── php/                # PHP build configuration
│   └── Dockerfile      # Custom PHP-Apache image
├── www/                # Web application source
│   ├── assets/         # CSS and Images
│   ├── includes/       # PHP API and DB logic
│   └── index.php       # Entry point
└── mysql_init/         # DB initialization scripts
```

---
**Author:** Ronnie Short  
**Review:** Gemma 4
