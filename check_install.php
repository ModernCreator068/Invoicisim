<?php
$installDir = __DIR__ . "/install";

// Function to delete the install directory
function deleteInstallDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $files = array_diff(scandir($dir), array('.', '..'));

    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? deleteInstallDirectory($path) : unlink($path);
    }
    
    rmdir($dir);
}

// Check if delete action is triggered
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    deleteInstallDirectory($installDir);
    header("Location: " . $_SERVER["PHP_SELF"]); // Refresh the page
    exit;
}

// Check if install directory exists
$installExists = is_dir($installDir);
?>
