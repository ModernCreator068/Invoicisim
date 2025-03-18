<?php
require 'config.php'; // Database connection

// Pagination settings
$limit = 50;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Fetch total clients count
$result = $conn->query("SELECT COUNT(id) AS total FROM clients");
$total_clients = $result->fetch_assoc()['total'];
$total_pages = ceil($total_clients / $limit);

// Fetch clients for current page
$sql = "SELECT * FROM clients ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$clients = $conn->query($sql);



?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <?php include 'header.php'; ?>

    <div class="w-full max-w-5xl mx-auto mt-6">
    <?php include "check_install.php"; ?>

    <?php if ($installExists): ?>
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-6 rounded-lg shadow-md">
            <h2 class="font-bold text-lg">⚠️ Action Required</h2>
            <p class="mt-2">
                Modernlisim strongly advises deleting the <strong>install</strong> directory immediately.  
                Keeping it may lead to **security risks, database overrides, and unintended modifications to company information.**
            </p>
            
            <form method="post" class="mt-4">
                <button type="submit" name="delete" class="bg-red-600 text-white font-semibold px-5 py-2 rounded-lg hover:bg-red-700">
                    Delete Now
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>

    <div class="max-w-6xl mx-auto p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Clients List</h2>
            <a href="add-client.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add New Client</a>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th class="p-3">ID</th>
                        <th class="p-3">Name</th>
                        <th class="p-3">Email</th>
                        <th class="p-3">Phone</th>
                        <th class="p-3">Address</th>
                        <th class="p-3">Joined On</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if ($clients->num_rows > 0): ?>
                        <?php while ($row = $clients->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3"> <?= htmlspecialchars($row['id']); ?> </td>
                                <td class="p-3"> <?= htmlspecialchars($row['name']); ?> </td>
                                <td class="p-3"> <?= htmlspecialchars($row['email']); ?> </td>
                                <td class="p-3"> <?= htmlspecialchars($row['phone']); ?> </td>
                                <td class="p-3"> <?= htmlspecialchars($row['address']); ?> </td>
                                <td class="p-3"> <?= date("d M Y", strtotime($row['created_at'])); ?> </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center p-4 text-gray-500">No clients found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <div class="flex justify-center mt-6 space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1; ?>" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?= $i; ?>" class="px-4 py-2 <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?> rounded-lg hover:bg-blue-500">
                    <?= $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1; ?>" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Next</a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>