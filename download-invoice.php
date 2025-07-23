<?php
session_start();
require 'vendor/autoload.php'; // Make sure DomPDF is installed via Composer

use Dompdf\Dompdf;
use Dompdf\Options;

include 'db.php';

if (!isset($_POST['invoice_id'])) {
    die("Invoice ID is required.");
}

$invoice_id = intval($_POST['invoice_id']);

// Fetch invoice data
$invoice_sql = "SELECT invoices.*, customers.name, customers.address, customers.phone, customers.email 
                FROM invoices 
                JOIN customers ON invoices.customer_id = customers.id 
                WHERE invoices.id = $invoice_id";

$invoice_result = $conn->query($invoice_sql);
if ($invoice_result->num_rows === 0) {
    die("Invoice not found.");
}
$invoice = $invoice_result->fetch_assoc();

// Fetch invoice items
$item_sql = "SELECT invoice_items.*, products.name AS product_name, products.price, products.gst 
             FROM invoice_items 
             JOIN products ON invoice_items.product_id = products.id 
             WHERE invoice_id = $invoice_id";

$item_result = $conn->query($item_sql);
$items = [];
while ($row = $item_result->fetch_assoc()) {
    $items[] = $row;
}

// Start output buffering
ob_start();
include 'invoice-template.php';
$html = ob_get_clean();

// Setup DomPDF
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->setIsRemoteEnabled(true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// Paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the PDF as download
$filename = "invoice_" . $invoice_id . ".pdf";
$dompdf->stream($filename, ["Attachment" => true]);
exit;
