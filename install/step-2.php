<?php
// Include database configuration
require_once '../config.php';

$database_created = false;
$redirect = false;

// Check if the database has tables
$result = mysqli_query($conn, "SHOW TABLES");
if (mysqli_num_rows($result) == 0) {
    // If no tables exist, import database.sql
    $sql = file_get_contents('database.sql');

    if (mysqli_multi_query($conn, $sql)) {
        $database_created = true;
    } else {
        die("Error creating tables: " . mysqli_error($conn));
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $website = mysqli_real_escape_string($conn, $_POST['website']);
    $logo = mysqli_real_escape_string($conn, $_POST['logo']);

    // Insert company details into the database
    $sql = "INSERT INTO company (name, email, phone, address, website, logo) 
            VALUES ('$name', '$email', '$phone', '$address', '$website', '$logo')";

    if (mysqli_query($conn, $sql)) {
        $message = "Company details saved successfully!";
        $redirect = true; // Enable redirect
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Installer</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
        <h2 class="text-2xl font-semibold text-gray-700 mb-4">Enter Company Details</h2>

        <?php if ($database_created): ?>
            <p class="mb-4 text-sm text-green-500">Database created successfully! Redirecting in <span id="countdown">5</span> seconds...</p>
            <div class="relative pt-1">
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                    <div id="progress-bar" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-500 ease-linear" style="width: 0;"></div>
                </div>
            </div>
            <script>
                let countdown = 5;
                let progressBar = document.getElementById('progress-bar');
                let countdownEl = document.getElementById('countdown');

                function updateProgress() {
                    let progress = ((5 - countdown) / 5) * 100;
                    progressBar.style.width = progress + "%";
                    if (countdown > 0) {
                        countdown--;
                        countdownEl.textContent = countdown;
                        setTimeout(updateProgress, 1000);
                    } else {
                        location.reload();
                    }
                }
                updateProgress();
            </script>
        <?php endif; ?>

        <?php if (isset($message)): ?>
            <p class="mb-4 text-sm text-green-500"><?php echo $message; ?></p>
            <?php if ($redirect): ?>
                <p class="mb-4 text-sm text-blue-500">Redirecting to clients page in <span id="redirectCountdown">5</span> seconds...</p>
                <div class="relative pt-1">
                    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
                        <div id="redirect-progress-bar" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500 transition-all duration-500 ease-linear" style="width: 0;"></div>
                    </div>
                </div>
                <script>
                    let redirectCountdown = 5;
                    let redirectProgressBar = document.getElementById('redirect-progress-bar');
                    let redirectCountdownEl = document.getElementById('redirectCountdown');

                    function updateRedirectProgress() {
                        let progress = ((5 - redirectCountdown) / 5) * 100;
                        redirectProgressBar.style.width = progress + "%";
                        if (redirectCountdown > 0) {
                            redirectCountdown--;
                            redirectCountdownEl.textContent = redirectCountdown;
                            setTimeout(updateRedirectProgress, 1000);
                        } else {
                            window.location.href = '../clients.php';
                        }
                    }
                    updateRedirectProgress();
                </script>
            <?php endif; ?>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-gray-600">Company Name</label>
                <input aria-required="true" type="text" name="name" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-600">Email</label>
                <input aria-required="true" type="email" name="email" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-600">Phone</label>
                <input aria-required="true" type="text" name="phone" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-600">Address</label>
                <textarea aria-required="true" name="address" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div>
                <label class="block text-gray-600">Website</label>
                <input type="text" name="website" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-gray-600">Logo URL</label>
                <input aria-required="true" type="text" name="logo" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600">Save</button>
        </form>
    </div>
</body>
</html>
