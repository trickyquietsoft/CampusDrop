-- Disable foreign key checks during creation to prevent ordering issues
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- D1: Users Store (Students)
-- -----------------------------------------------------
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    university VARCHAR(100) NOT NULL,
    account_status ENUM('active', 'suspended', 'pending_verification') NOT NULL DEFAULT 'pending_verification',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_email (email),
    INDEX idx_account_status (account_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- D1b: Admins Store
-- Separate identity for system administrators
-- -----------------------------------------------------
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
    admin_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    access_level ENUM('superadmin', 'moderator') NOT NULL DEFAULT 'moderator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- D2: Listings Store
-- -----------------------------------------------------
DROP TABLE IF EXISTS listings;
CREATE TABLE listings (
    listing_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    seller_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) NOT NULL,
    course_code VARCHAR(20) DEFAULT NULL,
    book_condition ENUM('new', 'like_new', 'good', 'fair', 'poor') NOT NULL,
    listing_type ENUM('sell', 'trade', 'both') NOT NULL DEFAULT 'both',
    price DECIMAL(6, 2) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('active', 'pending', 'completed', 'removed') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_listings_seller FOREIGN KEY (seller_id)
        REFERENCES users (user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_listing_isbn (isbn),
    INDEX idx_listing_course (course_code),
    INDEX idx_listing_status (status),
    INDEX idx_seller_status (seller_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- D2b: Listing Images Store
-- -----------------------------------------------------
DROP TABLE IF EXISTS listing_images;
CREATE TABLE listing_images (
    image_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_images_listing FOREIGN KEY (listing_id) 
        REFERENCES listings (listing_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- D3: Trades & Messages Store
-- -----------------------------------------------------
DROP TABLE IF EXISTS trades;
CREATE TABLE trades (
    trade_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNSIGNED NOT NULL,
    buyer_id INT UNSIGNED NOT NULL,
    offered_item_details TEXT DEFAULT NULL,
    agreed_price DECIMAL(6, 2) DEFAULT NULL,
    trade_status ENUM('requested', 'accepted', 'declined', 'completed', 'cancelled') NOT NULL DEFAULT 'requested',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_trades_listing FOREIGN KEY (listing_id)
        REFERENCES listings (listing_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_trades_buyer FOREIGN KEY (buyer_id)
        REFERENCES users (user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_buyer_trade (buyer_id),
    INDEX idx_trade_status (trade_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS messages;
CREATE TABLE messages (
    message_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    trade_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    message_text TEXT NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_trade FOREIGN KEY (trade_id)
        REFERENCES trades (trade_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id)
        REFERENCES users (user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id)
        REFERENCES users (user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_trade_messages (trade_id, sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- D4: Logs & Reports Store
-- -----------------------------------------------------
DROP TABLE IF EXISTS flagged_reports;
CREATE TABLE flagged_reports (
    report_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT UNSIGNED NOT NULL,
    listing_id INT UNSIGNED DEFAULT NULL,
    reported_user_id INT UNSIGNED DEFAULT NULL,
    reason ENUM('inappropriate_content', 'counterfeit_or_scam', 'wrong_details', 'harassment', 'other') NOT NULL,
    details TEXT DEFAULT NULL,
    resolution_status ENUM('open', 'under_review', 'resolved', 'dismissed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_reports_reporter FOREIGN KEY (reporter_id)
        REFERENCES users (user_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_reports_listing FOREIGN KEY (listing_id)
        REFERENCES listings (listing_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT fk_reports_reported_user FOREIGN KEY (reported_user_id)
        REFERENCES users (user_id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX idx_resolution_status (resolution_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS audit_logs;
CREATE TABLE audit_logs (
    log_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    target_entity VARCHAR(30) NOT NULL,
    target_id INT UNSIGNED NOT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_admin FOREIGN KEY (admin_id)
        REFERENCES admins (admin_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_admin_action (admin_id, action_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
