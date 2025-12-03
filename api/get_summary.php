<?php
/**
 * API: Get Monthly Summary
 * Uses view v_ringkasan_bulanan and function fn_cek_kesehatan_finansial
 */

require_once '../config/database.php';

header('Content-Type: application/json');

$conn = getDBConnection();

// Get summary from view
$query = "SELECT * FROM v_ringkasan_bulanan LIMIT 1";
$result = $conn->query($query);

$summary = [
    'total_masuk' => 0,
    'total_keluar' => 0,
    'saldo' => 0,
    'status' => 'AMAN/HEMAT'
];

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $summary['total_masuk'] = floatval($row['total_masuk'] ?? 0);
    $summary['total_keluar'] = floatval($row['total_keluar'] ?? 0);
    $summary['saldo'] = floatval($row['saldo'] ?? 0);
    
    // Use function to check financial health
    $func_query = "SELECT fn_cek_kesehatan_finansial(?, ?) as status";
    $stmt = $conn->prepare($func_query);
    $stmt->bind_param("dd", $summary['total_masuk'], $summary['total_keluar']);
    $stmt->execute();
    $func_result = $stmt->get_result();
    if ($func_result && $func_row = $func_result->fetch_assoc()) {
        $summary['status'] = $func_row['status'];
    }
    $stmt->close();
}

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => $summary
]);

