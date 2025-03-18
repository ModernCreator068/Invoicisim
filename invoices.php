<?php
require 'config.php'; // Include database configuration

// Handle payment status update
if (isset($_POST['mark_paid'])) {
    $invoice_id = intval($_POST['invoice_id']);

    // 1. Fetch invoice details to get the total amount and payment method
    $invoice_query = "SELECT total, payment_method FROM invoices WHERE id = $invoice_id";
    $invoice_result = mysqli_query($conn, $invoice_query);
    if (!$invoice_result || mysqli_num_rows($invoice_result) == 0) {
        die("Invoice not found.");
    }
    $invoice = mysqli_fetch_assoc($invoice_result);
    $total = $invoice['total'];
    // Use a default value if no payment method is set
    $payment_method = !empty($invoice['payment_method']) ? $invoice['payment_method'] : 'N/A';

    // 2. Update the invoice status to 'Paid'
    $update_sql = "UPDATE invoices SET payment_status = 'Paid' WHERE id = $invoice_id";
    mysqli_query($conn, $update_sql);

    // 3. Insert a new record into the payments table with the invoice total
    $payment_date = date('Y-m-d');
    // Generate a simple unique transaction ID (in a real-world scenario, this might come from a payment gateway)
    $transaction_id = uniqid('txn_');
    $insert_payment_sql = "
        INSERT INTO payments (invoice_id, amount, payment_date, payment_method, transaction_id)
        VALUES ($invoice_id, $total, '$payment_date', '$payment_method', '$transaction_id')
    ";
    mysqli_query($conn, $insert_payment_sql);

    header("Location: invoices.php"); // Refresh the page
    exit;
}

// Fetch invoices
$sql = "
    SELECT invoices.id, invoices.invoice_number, invoices.invoice_date, invoices.due_date, 
           invoices.total, invoices.payment_status, clients.name AS client_name
    FROM invoices 
    JOIN clients ON invoices.client_id = clients.id
    ORDER BY invoices.created_at DESC
";
$result = mysqli_query($conn, $sql);
$invoices = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 white:bg-gray-900 text-white">
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


    <div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Invoices</h2>
            <a href="create-invoice.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Add New Invoice</a>
        </div>

        <div class="bg-gray-800 shadow-md rounded-lg overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-700 text-white">
                    <tr>
                        <th class="p-4">#</th>
                        <th class="p-4">Client</th>
                        <th class="p-4">Invoice #</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">Due Date</th>
                        <th class="p-4">Total</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice) : ?>
                    <tr class="border-b border-gray-700 hover:bg-gray-600 transition">
                        <td class="p-4"><?php echo $invoice['id']; ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($invoice['client_name']); ?></td>
                        <td class="p-4"><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                        <td class="p-4"><?php echo $invoice['invoice_date']; ?></td>
                        <td class="p-4"><?php echo $invoice['due_date']; ?></td>
                        <td class="p-4 font-semibold">$<?php echo number_format($invoice['total'], 2); ?></td>
                        <td class="p-4">
                            <?php if ($invoice['payment_status'] === 'Paid') : ?>
                                <span class="px-3 py-1 text-xs font-semibold text-green-300 bg-green-700 rounded-full">Paid</span>
                            <?php elseif ($invoice['payment_status'] === 'Pending') : ?>
                                <span class="px-3 py-1 text-xs font-semibold text-yellow-300 bg-yellow-700 rounded-full">Pending</span>
                            <?php else : ?>
                                <span class="px-3 py-1 text-xs font-semibold text-red-300 bg-red-700 rounded-full">Overdue</span>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 text-right">
                            <a href="view_invoice.php?id=<?php echo $invoice['id']; ?>" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                                View Invoice
                            </a>
                            <?php if ($invoice['payment_status'] !== 'Paid') : ?>
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="invoice_id" value="<?php echo $invoice['id']; ?>">
                                    <button type="submit" name="mark_paid" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                                        Mark as Paid
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
