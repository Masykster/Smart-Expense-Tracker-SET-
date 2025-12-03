<?php
/**
 * API: Get All Transactions
 * Returns JSON data for transactions
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$conn = getDBConnection();

// Get all transactions
$query = "SELECT * FROM tb_transaksi ORDER BY tanggal DESC, id DESC";
$result = $conn->query($query);

$transactions = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => $transactions
]);

