<?php
session_start();
require 'config.php'; // Database connection file

// Fetch company details
$companyQuery = $conn->query("SELECT * FROM company WHERE id = 1");
$company = $companyQuery->fetch_assoc();

// Fetch clients
$clientsQuery = $conn->query("SELECT * FROM clients");
$clients = $clientsQuery->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_invoice'])) {
    $client_id = intval($_POST['client_id']);
    $invoice_date = $_POST['invoice_date'];
    $due_date = $_POST['due_date'];
    $items = $_POST['items']; // Array of items
    $subtotal = floatval($_POST['subtotal']);
    $tax = floatval($_POST['tax']);
    $total = floatval($_POST['total']);

    // Fetch client details
    $clientQuery = $conn->prepare("SELECT * FROM clients WHERE id = ?");
    $clientQuery->bind_param("i", $client_id);
    $clientQuery->execute();
    $client = $clientQuery->get_result()->fetch_assoc();

    // Generate invoice number
    $invoice_number = 'MSLSM-' . time();

    // Save invoice to database
    $stmt = $conn->prepare("INSERT INTO invoices (client_id, invoice_number, invoice_date, due_date, subtotal, tax, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssddd", $client_id, $invoice_number, $invoice_date, $due_date, $subtotal, $tax, $total);
    $stmt->execute();
    $invoice_id = $stmt->insert_id; // Get generated invoice ID

    // Insert invoice items
    $stmt = $conn->prepare("INSERT INTO invoice_items (invoice_id, item_name, description, quantity, price, total) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($items as $item) {
    $item_name = $item['item_name']; // Retrieve the item name
    $description = $item['description'];
    $quantity = intval($item['quantity']);
    $unit_price = floatval($item['unit_price']);
    $total_price = floatval($item['total_price']);

    $stmt->bind_param("issidd", $invoice_id, $item_name, $description, $quantity, $unit_price, $total_price);
    $stmt->execute();
}

    $_SESSION['success'] = "Invoice generated successfully!";
    // Instead of redirecting via header, return a JSON response with the redirect URL
    echo json_encode(["status" => "success", "redirect" => "view_invoice.php?id=" . $invoice_id]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

</head>
<body class="bg-gray-100 white:bg-gray-900 text-white">
<?php include 'header.php'; ?>

<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg mt-8 text-black">


    <h2 class="text-2xl font-semibold mb-4 text-gray-700">Create Invoice</h2>
    
    <form id="invoiceForm" method="post" class="space-y-4">
        <div>
            <label class="block text-gray-600">Client:</label>
            <select name="client_id" required class="w-full p-2 border border-gray-300 rounded text-black">
            <?php foreach ($clients as $client): ?>
                    <option value="<?= htmlspecialchars($client['id']) ?>">
                        <?= htmlspecialchars($client['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-gray-600">Invoice Date:</label>
                <input type="text" name="invoice_date" required class="w-full p-2 border border-gray-300 rounded datepicker" placeholder="Select Invoice Date">
                </div>
            <div>
                <label class="block text-gray-600">Due Date:</label>
                <input type="text" name="due_date" required class="w-full p-2 border border-gray-300 rounded datepicker" placeholder="Select Due Date">
                </div>
        </div>

        <table class="w-full border-collapse border border-gray-300 text-gray-700" id="invoice-items">
    <thead>
        <tr class="bg-gray-200">
            <th class="p-2 border">Name</th>
            <th class="p-2 border">Description</th>
            <th class="p-2 border">Quantity</th>
            <th class="p-2 border">Unit Price</th>
            <th class="p-2 border">Total</th>
            <th class="p-2 border">Action</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

        <button type="button" class="bg-blue-600 text-white px-4 py-2 rounded" onclick="addItem()">+ Add Item</button>

        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-gray-600">Subtotal:</label>
                <input type="number" step="0.01" name="subtotal" id="subtotal" readonly class="w-full p-2 border border-gray-300 rounded">
            </div>
            <div>
                <label class="block text-gray-600">Tax (%):</label>
                <input type="number" step="0.01" name="tax" id="tax" oninput="calculateTotal()" class="w-full p-2 border border-gray-300 rounded">
            </div>
        </div>

        <div>
            <label class="block text-gray-600">Total:</label>
            <input type="number" step="0.01" name="total" id="total" readonly class="w-full p-2 border border-gray-300 rounded font-bold text-lg">
        </div>

        <button type="submit" name="generate_invoice" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700">Generate Invoice</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  // Initialize Flatpickr on all elements with class 'datepicker'
  flatpickr(".datepicker", {
      dateFormat: "Y-m-d"
    });
    function addItem() {
      let table = document.querySelector("#invoice-items tbody");
      let rowCount = table.rows.length;
      let row = document.createElement("tr");
      row.innerHTML = `
        <td class="p-2 border">
          <input type="text" name="items[${rowCount}][item_name]" required class="w-full p-2 border rounded" placeholder="Item Name">
        </td>
        <td class="p-2 border">
          <input type="text" name="items[${rowCount}][description]" required class="w-full p-2 border rounded" placeholder="Description">
        </td>
        <td class="p-2 border">
          <input type="number" name="items[${rowCount}][quantity]" class="quantity w-full p-2 border rounded" oninput="calculateRow(this)" required>
        </td>
        <td class="p-2 border">
          <input type="number" step="0.01" name="items[${rowCount}][unit_price]" class="unit_price w-full p-2 border rounded" oninput="calculateRow(this)" required>
        </td>
        <td class="p-2 border">
          <input type="number" step="0.01" name="items[${rowCount}][total_price]" class="total_price w-full p-2 border rounded" readonly>
        </td>
        <td class="p-2 border">
          <button type="button" class="bg-red-600 text-white px-3 py-1 rounded" onclick="removeRow(this)">Remove</button>
        </td>
      `;
      table.appendChild(row);
    }

    function removeRow(button) {
      button.closest("tr").remove();
      calculateTotal();
    }

    function calculateRow(input) {
      let row = input.closest("tr");
      let qty = row.querySelector('.quantity').value;
      let price = row.querySelector('.unit_price').value;
      row.querySelector('.total_price').value = (qty * price).toFixed(2);
      calculateTotal();
    }

    function calculateTotal() {
      let total = 0;
      document.querySelectorAll('.total_price').forEach(input => total += parseFloat(input.value) || 0);
      document.getElementById('subtotal').value = total.toFixed(2);
      let tax = parseFloat(document.getElementById('tax').value) || 0;
      document.getElementById('total').value = (total + (total * tax / 100)).toFixed(2);
    }

    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
  e.preventDefault();
  let form = this;
  let formData = new FormData(form);
  // Manually add the missing submit parameter
  formData.append('generate_invoice', 'true');

  fetch('create-invoice.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      if (typeof loadPage === 'function') {
        loadPage(data.redirect);
      } else {
        window.location.href = data.redirect;
      }
    } else {
      alert(data.message);
    }
  })
  .catch(error => console.error('Error:', error));
});
  </script>

</body>
</html>
