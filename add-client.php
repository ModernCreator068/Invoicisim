<?php
require 'config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['address'])) {
        echo json_encode(["status" => "error", "message" => "Invalid request."]);
        exit;
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (empty($name) || empty($email) || empty($phone) || empty($address)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM clients WHERE email = ?");
    if (!$check_stmt) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        exit;
    }
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $check_stmt->close();
        echo json_encode(["status" => "error", "message" => "Email already exists. Please use a different email."]);
        exit;
    }
    $check_stmt->close();

    // Insert new client
    $stmt = $conn->prepare("INSERT INTO clients (name, email, phone, address) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssss", $name, $email, $phone, $address);

    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(["status" => "success", "message" => "Client added successfully!", "redirect" => "clients.php"]);
        exit;
    } else {
        echo json_encode(["status" => "error", "message" => "Error adding client."]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Client</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("addClientForm");
            const responseMessage = document.getElementById("responseMessage");
            const progressBarContainer = document.getElementById("progressContainer");
            const progressBar = document.getElementById("progressBar");
            const countdownText = document.getElementById("countdownText");

            form.addEventListener("submit", function(event) {
                event.preventDefault();
                const formData = new FormData(form);

                fetch("add-client.php", {
                    method: "POST",
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    responseMessage.textContent = data.message;
                    responseMessage.className = data.status === "success"
                        ? "text-green-600 mt-4 text-center"
                        : "text-red-600 mt-4 text-center";

                    if (data.status === "success") {
                        progressBarContainer.classList.remove("hidden");
                        let secondsLeft = 5;
                        let width = 0;

                        const countdown = setInterval(() => {
                            width += 20; // Increase width every second
                            progressBar.style.width = width + "%";
                            countdownText.textContent = `Redirecting in ${secondsLeft} seconds...`;
                            secondsLeft--;

                            if (width >= 100) {
                                clearInterval(countdown);
                                window.location.href = "clients.php";
                            }
                        }, 1000);
                    }
                })
                .catch(error => console.error("Error:", error));
            });
        });
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold text-center text-gray-800 mb-6">Add New Client</h2>

        <form id="addClientForm" class="space-y-4">
            <div>
                <label for="name" class="block text-gray-700 font-medium">Client Name:</label>
                <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="email" class="block text-gray-700 font-medium">Email:</label>
                <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="phone" class="block text-gray-700 font-medium">Phone:</label>
                <input type="text" id="phone" name="phone" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label for="address" class="block text-gray-700 font-medium">Address:</label>
                <textarea id="address" name="address" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">Add Client</button>
        </form>

        <p id="responseMessage" class="text-center mt-4"></p>

        <!-- Progress Bar Container -->
        <div id="progressContainer" class="hidden mt-4">
            <p id="countdownText" class="text-gray-600 text-center">Redirecting in 5 seconds...</p>
            <div class="w-full bg-gray-300 h-2 rounded-full overflow-hidden mt-2">
                <div id="progressBar" class="h-full bg-blue-500 transition-all duration-1000 ease-linear" style="width: 0%;"></div>
            </div>
        </div>

    </div>

</body>
</html>
