<?php
require 'config.php'; // Include database connection


if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid invoice ID.");
}

$invoice_id = intval($_GET['id']);

// Fetch invoice details
$query = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
$query->bind_param("i", $invoice_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    die("Invoice not found.");
}

$invoice = $result->fetch_assoc();
$grand_total = $invoice['total'] ?? 0.00;
$subtotal    = $invoice['subtotal'] ?? 0.00;
$tax_percentage = $invoice['tax'] ?? 0;
$tax_amount     = ($subtotal > 0) ? round($subtotal * ($tax_percentage / 100), 2) : 0;
$terms = $invoice['terms'] ?? 'Payment is due by the specified due date. Any payment received after the due date will incur a late fee of 5% on the outstanding balance.';
$payment_method = $invoice['payment_method'] ?? 'N/A';

// Fetch invoice items
$query = $conn->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
$query->bind_param("i", $invoice_id);
$query->execute();
$items_result = $query->get_result();
$items = $items_result->fetch_all(MYSQLI_ASSOC);

// Fetch company details
$query = $conn->prepare("SELECT * FROM company LIMIT 1");
$query->execute();
$company_result = $query->get_result();
$company = $company_result->fetch_assoc();

$company_name = $company['name'] ?? 'Company Name';
$company_phone = $company['phone'] ?? 'N/A';
$company_email = $company['email'] ?? 'N/A';
$company_address = $company['address'] ?? 'N/A';
$company_website = $company['website'] ?? 'N/A';
$company_logo = $company['logo'] ?? 'assets/img/default_logo.png'; // Default logo if none provided

// Fetch total amount paid for the invoice
$query = $conn->prepare("SELECT SUM(amount) as total_paid FROM payments WHERE invoice_id = ?");
$query->bind_param("i", $invoice_id);
$query->execute();
$payment_result = $query->get_result();
$payment = $payment_result->fetch_assoc();

$total_paid = $payment['total_paid'] ?? 0.00; // Default to 0 if no payments found
$amount_due = max(0, $grand_total - $total_paid); // Ensure amount due is not negative

// Fetch client details
$query = $conn->prepare("
    SELECT clients.name, clients.email, clients.phone, clients.address
    FROM clients 
    JOIN invoices ON clients.id = invoices.client_id 
    WHERE invoices.id = ?
");
$query->bind_param("i", $invoice_id);
$query->execute();
$client_result = $query->get_result();

if ($client_result->num_rows == 0) {
    die("Client not found.");
}

$client = $client_result->fetch_assoc();

$client_name = $client['name'] ?? 'N/A';
$client_address = $client['address'] ?? 'N/A';
$client_phone = $client['phone'] ?? 'N/A';
$client_email = $client['email'] ?? 'N/A';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #<?php echo $invoice['invoice_number'] ?? 'N/A'; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="tm_container">
        <div class="tm_invoice_wrap">
            <div class="tm_invoice tm_style1 tm_radius_0">
                <div class="tm_invoice_in">
                    <div class="tm_flex tm_flex_column_sm tm_justify_between tm_align_center tm_align_start_sm tm_f14 tm_primary_color tm_medium tm_mb5">
                        <p class="tm_m0 tm_f18 tm_bold">Invoice</p>
                        <p class="tm_m0">Invoice Date: <?php echo date("d M Y", strtotime($invoice['invoice_date'] ?? '')); ?></p>
                        <p class="tm_m0">Invoice No: <?php echo $invoice['invoice_number'] ?? 'N/A'; ?></p>
                    </div>

                    <div class="tm_grid_row tm_col_4 tm_padd_20 tm_accent_bg tm_mb25 tm_white_color tm_align_center">
                        <div>
                            <div class="tm_logo"><img src="<?php echo htmlspecialchars($company_logo); ?>" alt="Company Logo"></div>
                        </div>
                        <div><?php echo $company_phone; ?> <br> <?php echo $company_email; ?></div>
                        <div><?php echo $company_address; ?></div>
                        <div>
                            Visit Our Site: <br>
                            <a href="<?php echo $company_website; ?>" target="_blank">
                                <?php echo $company_website; ?>
                            </a>
                        </div>
                    </div>

                    <div class="tm_invoice_head tm_mb10">
                        <div class="tm_invoice_left">
                            <p class="tm_mb2"><b class="tm_primary_color">Bill To:</b></p>
                            <p>
                                <?php echo $client_name; ?><br>
                                <?php echo $client_address; ?><br>
                                <?php echo $client_phone; ?><br>
                                <?php echo $client_email; ?>
                            </p>
                        </div>
                        <div class="tm_invoice_right tm_text_center">
                            <p class="tm_mb3"><b class="tm_primary_color">Amount Due</b></p>
                            <div class="tm_f30 tm_bold tm_accent_color tm_padd_15 tm_accent_bg_10 tm_border_1 tm_accent_border_20 tm_mb5">
                                $<?php echo number_format($amount_due, 2); ?>
                            </div>
                            <p class="tm_mb0"><i>Total Paid: $<?php echo number_format($total_paid, 2); ?></i></p>
                            <p class="tm_mb0"><i>Payment method: <?php echo $payment_method; ?></i></p>

                        </div>
                    </div>

                    <div class="tm_table tm_style1 tm_mb40">
                        <div class="tm_round_border tm_radius_0">
                            <div class="tm_table_responsive">
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="tm_width_3 tm_semi_bold tm_primary_color">Item</th>
                                            <th class="tm_width_4 tm_semi_bold tm_primary_color tm_border_left">Description</th>
                                            <th class="tm_width_2 tm_semi_bold tm_primary_color tm_border_left">Price</th>
                                            <th class="tm_width_1 tm_semi_bold tm_primary_color tm_border_left">Qty</th>
                                            <th class="tm_width_2 tm_semi_bold tm_primary_color tm_border_left tm_text_right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td class="tm_width_3"><?php echo $item['item_name'] ?? 'N/A'; ?></td>
                                                <td class="tm_width_4 tm_border_left"><?php echo $item['description'] ?? 'N/A'; ?></td>
                                                <td class="tm_width_2 tm_border_left">$<?php echo number_format($item['price'] ?? 0.00, 2); ?></td>
                                                <td class="tm_width_1 tm_border_left"><?php echo $item['quantity'] ?? 0; ?></td>
                                                <td class="tm_width_2 tm_border_left tm_text_right">$<?php echo number_format($item['total'] ?? 0.00, 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tm_invoice_footer">
                            <div class="tm_right_footer">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="tm_width_3 tm_primary_color tm_border_none tm_bold">Subtotal</td>
                                            <td class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_bold">$<?php echo number_format($invoice['subtotal'] ?? 0.00, 2); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="tm_width_3 tm_primary_color">Tax (<?php echo $tax_percentage; ?>%)</td>
                                            <td class="tm_width_3 tm_primary_color tm_text_right">+$<?php echo number_format($tax_amount, 2); ?></td>
                                        </tr>
                                        <tr class="tm_border_bottom tm_accent_bg_10">
                                            <td class="tm_width_3 tm_bold tm_f16 tm_accent_color">Grand Total</td>
                                            <td class="tm_width_3 tm_bold tm_f16 tm_accent_color tm_text_right">$<?php echo number_format($grand_total, 2); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <p class="tm_bold tm_primary_color tm_m0">Terms and conditions</p>
                    <p class="tm_m0"><?php echo $terms; ?></p>
                </div>
            </div>

            <div class="tm_invoice_btns tm_hide_print">
                <a href="javascript:window.print()" class="tm_invoice_btn tm_color1">
                <span class="tm_btn_icon">
            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path d="M384 368h24a40.12 40.12 0 0040-40V168a40.12 40.12 0 00-40-40H104a40.12 40.12 0 00-40 40v160a40.12 40.12 0 0040 40h24" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"/><rect x="128" y="240" width="256" height="208" rx="24.32" ry="24.32" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"/><path d="M384 128v-24a40.12 40.12 0 00-40-40H168a40.12 40.12 0 00-40 40v24" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="32"/><circle cx="392" cy="184" r="24" fill='currentColor'/></svg>
          </span>
                    <span class="tm_btn_text">Print</span>
                </a>
                <a href="download_invoice.php?id=<?php echo $invoice_id; ?>" class="tm_invoice_btn tm_color2" id="tm_download_btn">
                <span class="tm_btn_icon">
            <svg xmlns="http://www.w3.org/2000/svg" class="ionicon" viewBox="0 0 512 512"><path d="M320 336h76c55 0 100-21.21 100-75.6s-53-73.47-96-75.6C391.11 99.74 329 48 256 48c-69 0-113.44 45.79-128 91.2-60 5.7-112 35.88-112 98.4S70 336 136 336h56M192 400.1l64 63.9 64-63.9M256 224v224.03" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="32"/></svg>
          </span>
                    <span class="tm_btn_text">Download</span>
                </a>
            </div>
        </div>
    </div>