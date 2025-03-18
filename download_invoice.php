<?php
require 'config.php'; // Include database connection
require 'vendor/autoload.php'; // Load Dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid invoice ID.");
}

$invoice_id = intval($_GET['id']);

// Fetch invoice details (you may include additional queries as needed)
$query = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
$query->bind_param("i", $invoice_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    die("Invoice not found.");
}

$invoice = $result->fetch_assoc();

// Use the invoice number to generate a unique PDF file name
$invoice_number = $invoice['invoice_number'] ?? $invoice_id;
$pdfFile = "pdf/Invoice_{$invoice_number}.pdf";

// Ensure the pdf directory exists
if (!is_dir('pdf')) {
    mkdir('pdf', 0777, true);
}

if (file_exists($pdfFile)) {
    // If PDF already exists, send it for download
    header("Content-Type: application/pdf");
    header("Content-Disposition: attachment; filename=\"Invoice_{$invoice_number}.pdf\"");
    header("Content-Length: " . filesize($pdfFile));
    readfile($pdfFile);
    exit();
}

// Define a constant so that invoice_template.php knows we're generating a PDF (e.g. to hide buttons)
define('PDF_VERSION', true);

// Load CSS and capture the HTML from the invoice template
$css = file_get_contents('assets/css/style.css');
ob_start();
include 'invoice_template.php'; // This will render the invoice HTML with dynamic data
$html = ob_get_clean();

// Configure Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml('<style>' . $css . '</style>' . $html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Get the generated PDF content
$output = $dompdf->output();

// Save the PDF file in the pdf folder
file_put_contents($pdfFile, $output);

// Send the PDF for download
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"Invoice_{$invoice_number}.pdf\"");
header("Content-Length: " . filesize($pdfFile));
readfile($pdfFile);
exit();
