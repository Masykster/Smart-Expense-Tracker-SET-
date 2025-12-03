<?php
/**
 * API: Add New Transaction
 * Uses stored procedure sp_tambah_transaksi
 */

require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$conn = getDBConnection();

// Get POST data
$tanggal = $_POST['tanggal'] ?? date('Y-m-d');
$jenis = $_POST['jenis'] ?? '';
$kategori = $_POST['kategori'] ?? '';
$nominal = $_POST['nominal'] ?? 0;
$keterangan = $_POST['keterangan'] ?? '';

// Validate required fields
if (empty($jenis) || empty($kategori) || empty($nominal)) {
    echo json_encode(['success' => false, 'message' => 'Field wajib tidak boleh kosong']);
    closeDBConnection($conn);
    exit;
}

// Validate jenis
if (!in_array($jenis, ['Masuk', 'Keluar'])) {
    echo json_encode(['success' => false, 'message' => 'Jenis harus Masuk atau Keluar']);
    closeDBConnection($conn);
    exit;
}

// Validate nominal
$nominal = floatval($nominal);
if ($nominal <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nominal harus lebih dari 0']);
    closeDBConnection($conn);
    exit;
}

// Call stored procedure
$stmt = $conn->prepare("CALL sp_tambah_transaksi(?, ?, ?, ?, ?)");
$stmt->bind_param("sssds", $tanggal, $jenis, $kategori, $nominal, $keterangan);

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
    
    echo json_encode(['success' => true, 'message' => 'Transaksi berhasil ditambahkan']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menambahkan transaksi: ' . $conn->error]);
}

$stmt->close();
closeDBConnection($conn);

