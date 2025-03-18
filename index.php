<?php
// Check if config.php exists
if (file_exists("config.php")) {
    // Redirect to clients.php if the website is already installed
    header("Location: clients.php");
} else {
    // Redirect to the installation page if config.php is missing
    header("Location: install/step-1.php");
}
exit;
?>
