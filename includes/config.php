<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'collegehub');
define('DB_USER', 'root'); // Change to your MySQL username
define('DB_PASS', ''); // Change to your MySQL password

// Project Constants
define('BASE_URL', 'http://localhost/CollegeHub/'); // Update to your project URL
define('UPLOAD_DIR', __DIR__ . '/../assets/images/uploads/'); // Path for image uploads
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB max for uploads

// Other Settings
define('SITENAME', 'CollegeHub');
define('ADMIN_EMAIL', 'admin@collegehub.com'); // For notifications or contact
?>