<?php
/**
 * Volunteer Connect - Logout Page
 * PHP/MySQL Implementation
 */

require_once 'config.php';
require_once 'classes/Auth.php';

// Initialize auth class
$auth = new Auth();

// Logout user
$auth->logout();

// Redirect to home with message
Utils::redirect('index.php?logged_out=1');
?>