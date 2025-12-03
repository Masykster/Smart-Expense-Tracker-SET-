<?php
/**
 * API: Update Transaction
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
$tanggal = $_POST['tanggal'] ?? '';
$jenis = $_POST['jenis'] ?? '';
$kategori = $_POST['kategori'] ?? '';
$nominal = $_POST['nominal'] ?? 0;
$keterangan = $_POST['keterangan'] ?? '';

// Validate
if (empty($id) || empty($tanggal) || empty($jenis) || empty($kategori) || empty($nominal)) {
    echo json_encode(['success' => false, 'message' => 'Field wajib tidak boleh kosong']);
    closeDBConnection($conn);
    exit;
}

if (!in_array($jenis, ['Masuk', 'Keluar'])) {
    echo json_encode(['success' => false, 'message' => 'Jenis harus Masuk atau Keluar']);
    closeDBConnection($conn);
    exit;
}

$nominal = floatval($nominal);
if ($nominal <= 0) {
    echo json_encode(['success' => false, 'message' => 'Nominal harus lebih dari 0']);
    closeDBConnection($conn);
    exit;
}

// Update transaction
$stmt = $conn->prepare("UPDATE tb_transaksi SET tanggal = ?, jenis = ?, kategori = ?, nominal = ?, keterangan = ? WHERE id = ?");
$stmt->bind_param("sssdsi", $tanggal, $jenis, $kategori, $nominal, $keterangan, $id);

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
    
    echo json_encode(['success' => true, 'message' => 'Transaksi berhasil diupdate']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate transaksi: ' . $conn->error]);
}

$stmt->close();
closeDBConnection($conn);

