<?php
/**
 * API: Delete Transaction
 * Trigger will automatically log to tb_log_hapus
 */

require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$conn = getDBConnection();

// Get POST data
$id = $_POST['id'] ?? 0;

if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ID transaksi tidak valid']);
    closeDBConnection($conn);
    exit;
}

// Delete transaction (trigger will handle logging)
$stmt = $conn->prepare("DELETE FROM tb_transaksi WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Regenerate chart
    $python_script = __DIR__ . '/../python/generate_chart.py';
    if (file_exists($python_script)) {
        // Try python3 first, then python (Windows compatibility)
        $python_cmd = 'python3 "' . $python_script . '" 2>&1';
        $output = shell_exec($python_cmd);
        if ($output === null) {
            $python_cmd = 'python "' . $python_script . '" 2>&1';
            shell_exec($python_cmd);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Transaksi berhasil dihapus']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus transaksi: ' . $conn->error]);
}

$stmt->close();
closeDBConnection($conn);

