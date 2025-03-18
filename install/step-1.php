<?php
session_start();
$success = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = $_POST["servername"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $dbname = $_POST["dbname"];

    // Attempt database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        $error = "Connection failed: " . $conn->connect_error;
    } else {
        // Database connection successful, create config.php file
        $configContent = "<?php\n";
        $configContent .= "\$servername = \"$servername\";\n";
        $configContent .= "\$username = \"$username\";\n";
        $configContent .= "\$password = \"$password\";\n";
        $configContent .= "\$dbname = \"$dbname\";\n";
        $configContent .= "\$conn = new mysqli(\$servername, \$username, \$password, \$dbname);\n";
        $configContent .= "if (\$conn->connect_error) {\n";
        $configContent .= "    die(\"Connection failed: \" . \$conn->connect_error);\n";
        $configContent .= "}\n";
        $configContent .= "?>";

        if (file_put_contents("../config.php", $configContent)) {
            $success = true;
        } else {
            $error = "Failed to create config.php. Please check file permissions.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Step 1: Database Setup</title>
    <?php if ($success): ?>
        <meta http-equiv="refresh" content="5;url=step-2.php">
    <?php endif; ?>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
        <h2 class="text-2xl font-bold text-center mb-6">Step 1: Database Configuration</h2>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="text-center">
                <p class="text-green-600 font-semibold">Database connection successful! Redirecting...</p>
                <div class="w-full bg-gray-200 rounded-full h-2.5 mt-4">
                    <div id="progressBar" class="bg-green-600 h-2.5 rounded-full w-0"></div>
                </div>
                <script>
                    let progress = 0;
                    const interval = setInterval(() => {
                        progress += 20;
                        document.getElementById("progressBar").style.width = progress + "%";
                        if (progress >= 100) clearInterval(interval);
                    }, 1000);
                </script>
            </div>
        <?php else: ?>
            <form action="" method="post" class="space-y-4">
                <input type="text" name="servername" placeholder="Server Name" required class="w-full p-2 border rounded">
                <input type="text" name="username" placeholder="Username" required class="w-full p-2 border rounded">
                <input type="password" name="password" placeholder="Password" class="w-full p-2 border rounded">
                <input type="text" name="dbname" placeholder="Database Name" required class="w-full p-2 border rounded">
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Save & Proceed</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
