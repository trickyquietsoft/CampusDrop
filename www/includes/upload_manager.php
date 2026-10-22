<?php
/**
 * Author: Ronnie Short
 * Reviewer: Gemma 4
 */

/**
 * CAMPUSDROP UPLOAD MANAGER
 * This file handles the physical file system operations for product images.
 * It is a helper library used by listing_api.php.
 */

/**
 * FUNCTION: uploadListingImages
 * Handles the upload of multiple images for a single listing.
 * 
 * @param PDO $pdo The database connection
 * @param int $listingId The ID of the book being listed
 * @param array $files The $_FILES array containing image data
 * @return array List of paths to successfully uploaded images
 */
function uploadListingImages($pdo, $listingId, $files) {
    // If no images were uploaded, exit early
    if (empty($files)) return [];

    /**
     * Directory Management
     * We create a folder specific to each listing to avoid file name collisions.
     * Path: /uploads/books/{listing_id}/
     */
    $uploadDir = 'uploads/books/' . $listingId . '/';
    if (!is_dir($uploadDir)) {
        // 0777 permissions allow the server to write the folder
        mkdir($uploadDir, 0777, true);
    }

    $uploadedPaths = [];
    // Only allow standard image formats for security and performance
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // Limit each image to 5 Megabytes

    // Iterate through all uploaded files
    foreach ($files['name'] as $index => $name) {
        $tmpName = $files['tmp_name'][$index];
        $type = $files['type'][$index];
        $size = $files['size'][$index];

        // VALIDATION: Reject wrong file types or oversized files
        if (!in_array($type, $allowedTypes)) continue;
        if ($size > $maxSize) continue;

        /**
         * File Naming
         * We generate a unique filename using time() and index to prevent 
         * users from overwriting each other's files.
         */
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $fileName = 'img_' . time() . '_' . $index . '.' . $extension;
        $destPath = $uploadDir . $fileName;

        // Move the file from the temporary system folder to our uploads folder
        if (move_uploaded_file($tmpName, $destPath)) {
            /**
             * Database Sync
             * Save the file path into the listing_images table.
             * The first image in the array is automatically marked as the 'Primary' photo.
             */
            $stmt = $pdo->prepare("INSERT INTO listing_images (listing_id, image_path, is_primary) VALUES (?, ?, ?)");
            $isPrimary = ($index === 0) ? 1 : 0;
            $stmt->execute([$listingId, $destPath, $isPrimary]);
            
            $uploadedPaths[] = $destPath;
        }
    }

    return $uploadedPaths;
}

/**
 * FUNCTION: cleanupListingImages
 * Deletes the physical image folder when a listing is deleted from the database.
 * This prevents the server from filling up with "orphaned" images.
 */
function cleanupListingImages($listingId) {
    $dir = 'uploads/books/' . $listingId . '/';
    if (is_dir($dir)) {
        // Find all files in the folder including hidden ones
        $files = glob($dir . '{*,.*}', GLOB_BRACE);
        foreach ($files as $file) {
            if (is_file($file)) unlink($file); // Delete the file
        }
        rmdir($dir); // Remove the empty directory
    }
}
?>
