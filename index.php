<?php
/**
 * Smart Expense Tracker (SET) - Main Dashboard
 * Index page with chart visualization and transaction list
 */

require_once 'config/database.php';

// Generate chart on page load
$python_script = __DIR__ . '/python/generate_chart.py';
if (file_exists($python_script)) {
    // Try python3 first, then python (Windows compatibility)
    $python_cmd = 'python3 "' . $python_script . '" 2>&1';
    $output = shell_exec($python_cmd);
    if ($output === null) {
        $python_cmd = 'python "' . $python_script . '" 2>&1';
        shell_exec($python_cmd);
    }
}

// Get summary data
$conn = getDBConnection();
$summary_query = "SELECT * FROM v_ringkasan_bulanan LIMIT 1";
$summary_result = $conn->query($summary_query);
$summary = $summary_result && $summary_result->num_rows > 0 
    ? $summary_result->fetch_assoc() 
    : ['total_masuk' => 0, 'total_keluar' => 0, 'saldo' => 0];

// Get financial health status
$status_query = "SELECT fn_cek_kesehatan_finansial(?, ?) as status";
$status_stmt = $conn->prepare($status_query);
$status_stmt->bind_param("dd", $summary['total_masuk'], $summary['total_keluar']);
$status_stmt->execute();
$status_result = $status_stmt->get_result();
$health_status = $status_result->fetch_assoc()['status'] ?? 'AMAN/HEMAT';
$status_stmt->close();

// Get all transactions
$transactions_query = "SELECT * FROM tb_transaksi ORDER BY tanggal DESC, id DESC";
$transactions_result = $conn->query($transactions_query);
$transactions = [];
if ($transactions_result && $transactions_result->num_rows > 0) {
    while ($row = $transactions_result->fetch_assoc()) {
        $transactions[] = $row;
    }
}

closeDBConnection($conn);

// Format currency helper
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Expense Tracker (SET)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .status-boros {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .status-hemat {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .transaction-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .badge-masuk {
            background-color: #28a745;
        }
        .badge-keluar {
            background-color: #dc3545;
        }
        .search-box {
            position: relative;
        }
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .search-box input {
            padding-left: 40px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="bi bi-wallet2"></i> Smart Expense Tracker (SET)
            </span>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card summary-card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">Total Pemasukan</h6>
                        <h3 class="card-title"><?php echo formatRupiah($summary['total_masuk']); ?></h3>
                        <i class="bi bi-arrow-down-circle fs-4"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card summary-card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">Total Pengeluaran</h6>
                        <h3 class="card-title"><?php echo formatRupiah($summary['total_keluar']); ?></h3>
                        <i class="bi bi-arrow-up-circle fs-4"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card <?php echo $health_status === 'BAHAYA/BOROS' ? 'status-boros' : 'status-hemat'; ?>">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-white-50">Saldo & Status</h6>
                        <h3 class="card-title"><?php echo formatRupiah($summary['saldo']); ?></h3>
                        <span class="badge bg-light text-dark"><?php echo $health_status; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Visualisasi Grafik</h5>
                        <button class="btn btn-primary btn-sm" id="generateChartBtn" onclick="generateChart()">
                            <i class="bi bi-arrow-clockwise"></i> Generate Chart
                        </button>
                    </div>
                    <div id="chartContainer">
                        <?php
                        $chart_path = __DIR__ . '/assets/charts/chart_latest.png';
                        if (file_exists($chart_path)) {
                            echo '<img src="assets/charts/chart_latest.png?t=' . time() . '" class="img-fluid" alt="Chart" id="chartImage" style="max-height: 500px;">';
                        } else {
                            echo '<div class="alert alert-info" id="chartPlaceholder">Grafik akan muncul setelah ada data transaksi. Klik tombol "Generate Chart" untuk membuat grafik.</div>';
                        }
                        ?>
                    </div>
                    <div id="chartLoading" class="text-center mt-3" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Sedang generate chart...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Management -->
        <div class="row">
            <div class="col-12">
                <div class="card dashboard-card transaction-table">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-ul"></i> Daftar Transaksi</h5>
                        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
                            <i class="bi bi-plus-circle"></i> Tambah Transaksi
                        </button>
                    </div>
                    <div class="card-body">
                        <!-- Search Box -->
                        <div class="mb-3">
                            <div class="search-box">
                                <i class="bi bi-search"></i>
                                <input type="text" class="form-control" id="searchInput" placeholder="Cari transaksi (tanggal, kategori, keterangan)...">
                            </div>
                        </div>

                        <!-- Transactions Table -->
                        <div class="table-responsive">
                            <table class="table table-hover" id="transactionsTable">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th>Kategori</th>
                                        <th>Nominal</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="transactionsTableBody">
                                    <?php if (empty($transactions)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada transaksi</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transactions as $trans): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($trans['tanggal'])); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $trans['jenis'] === 'Masuk' ? 'badge-masuk' : 'badge-keluar'; ?>">
                                                        <?php echo $trans['jenis']; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($trans['kategori']); ?></td>
                                                <td><strong><?php echo formatRupiah($trans['nominal']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($trans['keterangan']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" onclick="editTransaction(<?php echo htmlspecialchars(json_encode($trans)); ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteTransaction(<?php echo $trans['id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Transaction Modal -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Transaksi Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addTransactionForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis</label>
                            <select class="form-select" name="jenis" required>
                                <option value="">Pilih Jenis</option>
                                <option value="Masuk">Pemasukan</option>
                                <option value="Keluar">Pengeluaran</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" class="form-control" name="kategori" placeholder="Contoh: Makan, Transport, Gaji" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominal</label>
                            <input type="number" class="form-control" name="nominal" min="1" step="0.01" placeholder="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="3" placeholder="Keterangan (opsional)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Transaction Modal -->
    <div class="modal fade" id="editTransactionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editTransactionForm">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" id="edit_tanggal" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jenis</label>
                            <select class="form-select" name="jenis" id="edit_jenis" required>
                                <option value="">Pilih Jenis</option>
                                <option value="Masuk">Pemasukan</option>
                                <option value="Keluar">Pengeluaran</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <input type="text" class="form-control" name="kategori" id="edit_kategori" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nominal</label>
                            <input type="number" class="form-control" name="nominal" id="edit_nominal" min="1" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" id="edit_keterangan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Generate Chart Function
        function generateChart() {
            const btn = document.getElementById('generateChartBtn');
            const loading = document.getElementById('chartLoading');
            const chartContainer = document.getElementById('chartContainer');
            const chartImage = document.getElementById('chartImage');
            const chartPlaceholder = document.getElementById('chartPlaceholder');
            
            // Disable button and show loading
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Generating...';
            loading.style.display = 'block';
            if (chartImage) chartImage.style.display = 'none';
            if (chartPlaceholder) chartPlaceholder.style.display = 'none';
            
            // Call API
            fetch('api/generate_chart.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload chart image
                        const timestamp = new Date().getTime();
                        if (chartImage) {
                            chartImage.src = 'assets/charts/chart_latest.png?t=' + timestamp;
                            chartImage.style.display = 'block';
                        } else {
                            // Create new image element
                            const newImg = document.createElement('img');
                            newImg.src = 'assets/charts/chart_latest.png?t=' + timestamp;
                            newImg.className = 'img-fluid';
                            newImg.id = 'chartImage';
                            newImg.style.maxHeight = '500px';
                            newImg.alt = 'Chart';
                            chartContainer.innerHTML = '';
                            chartContainer.appendChild(newImg);
                        }
                        if (chartPlaceholder) chartPlaceholder.style.display = 'none';
                        
                        // Show success message
                        showAlert('Chart berhasil di-generate!', 'success');
                    } else {
                        // Show detailed error message
                        let errorMsg = data.message;
                        if (data.output) {
                            errorMsg += '\n\nDetail Error:\n' + data.output;
                        }
                        if (data.error_details && data.error_details.length > 0) {
                            errorMsg += '\n\n' + data.error_details.join('\n');
                        }
                        
                        // Add installation instructions if ModuleNotFoundError
                        if (data.install_command) {
                            errorMsg += '\n\n📦 Cara Install Dependencies:\n';
                            errorMsg += '1. Buka Command Prompt (Windows) atau Terminal (Linux/Mac)\n';
                            errorMsg += '2. Jalankan command berikut:\n\n';
                            errorMsg += data.install_command + '\n\n';
                            errorMsg += 'Atau gunakan script helper: install_dependencies.bat (Windows) atau install_dependencies.sh (Linux/Mac)';
                        }
                        
                        showAlert(errorMsg, 'danger');
                        if (chartPlaceholder) {
                            let placeholderText = 'Gagal generate chart. ' + data.message;
                            if (data.output) {
                                placeholderText += '\n\nDetail: ' + data.output.substring(0, 200);
                            }
                            
                            // Add install instructions to placeholder
                            if (data.install_instructions) {
                                placeholderText += '\n\n📦 Cara Install:\n';
                                data.install_instructions.forEach(instruction => {
                                    placeholderText += instruction + '\n';
                                });
                            } else if (data.install_command) {
                                placeholderText += '\n\n📦 Install dependencies dengan command:\n';
                                placeholderText += data.install_command;
                            }
                            
                            chartPlaceholder.innerHTML = placeholderText.replace(/\n/g, '<br>');
                            chartPlaceholder.style.display = 'block';
                            chartPlaceholder.className = 'alert alert-danger';
                        }
                        
                        // Log debug info to console
                        if (data.debug_info) {
                            console.error('Debug Info:', data.debug_info);
                        }
                        if (data.install_command) {
                            console.info('Install Command:', data.install_command);
                        }
                        console.error('Full Error Response:', data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Terjadi kesalahan saat generate chart', 'danger');
                })
                .finally(() => {
                    // Re-enable button and hide loading
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Generate Chart';
                    loading.style.display = 'none';
                });
        }
        
        // Show Alert Function
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
        
        // Live Search Functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableRows = document.querySelectorAll('#transactionsTableBody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Add Transaction Form
        document.getElementById('addTransactionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
            
            fetch('api/add_transaction.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addTransactionModal'));
                    modal.hide();
                    
                    // Reset form
                    this.reset();
                    
                    // Show success message
                    showAlert(data.message, 'success');
                    
                    // Auto-generate chart after adding transaction
                    setTimeout(() => {
                        generateChart();
                    }, 500);
                    
                    // Reload page after a short delay to show new transaction
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('Error: ' + data.message, 'danger');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan saat menambahkan transaksi', 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        // Edit Transaction
        function editTransaction(trans) {
            document.getElementById('edit_id').value = trans.id;
            document.getElementById('edit_tanggal').value = trans.tanggal;
            document.getElementById('edit_jenis').value = trans.jenis;
            document.getElementById('edit_kategori').value = trans.kategori;
            document.getElementById('edit_nominal').value = trans.nominal;
            document.getElementById('edit_keterangan').value = trans.keterangan || '';
            
            const editModal = new bootstrap.Modal(document.getElementById('editTransactionModal'));
            editModal.show();
        }

        // Edit Transaction Form
        document.getElementById('editTransactionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Disable button
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengupdate...';
            
            fetch('api/update_transaction.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editTransactionModal'));
                    modal.hide();
                    
                    // Show success message
                    showAlert(data.message, 'success');
                    
                    // Auto-generate chart after updating transaction
                    setTimeout(() => {
                        generateChart();
                    }, 500);
                    
                    // Reload page after a short delay
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('Error: ' + data.message, 'danger');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Terjadi kesalahan saat mengupdate transaksi', 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });

        // Delete Transaction
        function deleteTransaction(id) {
            if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
                const formData = new FormData();
                formData.append('id', id);
                
                fetch('api/delete_transaction.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showAlert(data.message, 'success');
                        
                        // Auto-generate chart after deleting transaction
                        setTimeout(() => {
                            generateChart();
                        }, 500);
                        
                        // Reload page after a short delay
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showAlert('Error: ' + data.message, 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showAlert('Terjadi kesalahan saat menghapus transaksi', 'danger');
                });
            }
        }
    </script>
</body>
</html>

