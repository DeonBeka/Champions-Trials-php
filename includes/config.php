<?php
// DB credentials - change to your values
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'volunteer_connect');
define('DB_USER', 'root');
define('DB_PASS', '');

// Upload directory (relative to project root)
define('UPLOAD_DIR', __DIR__ . '/../uploads/'); // ensure writable
// Public base url - keep empty or adjust if using a subfolder
define('BASE_PATH', '/volunteer-connect/public/'); // set to '' if not needed