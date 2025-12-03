<?php
/**
 * Test Script untuk Debug Python & Dependencies
 * Buka file ini di browser untuk melihat status Python dan dependencies
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Python & Dependencies</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        h1 { color: #333; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
        code { font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Python & Dependencies</h1>
        
        <?php
        // Test 1: Check shell_exec
        echo '<div class="test-section">';
        echo '<h2>1. Test shell_exec()</h2>';
        if (function_exists('shell_exec')) {
            echo '<div class="success">✅ shell_exec() tersedia</div>';
        } else {
            echo '<div class="error">❌ shell_exec() TIDAK tersedia. Edit php.ini dan hapus shell_exec dari disable_functions</div>';
        }
        echo '</div>';
        
        // Test 2: Find Python
        echo '<div class="test-section">';
        echo '<h2>2. Test Python Installation</h2>';
        $python_commands = ['py', 'python3', 'python'];
        $python_found = false;
        $python_exe = null;
        
        // Windows-specific: Try common Python installation paths
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            echo '<div class="info">🔍 Mencari Python di lokasi umum Windows...</div>';
            
            $common_paths = [
                'C:\\Python39\\python.exe',
                'C:\\Python310\\python.exe',
                'C:\\Python311\\python.exe',
                'C:\\Python312\\python.exe',
                'C:\\Python313\\python.exe',
                'C:\\Program Files\\Python39\\python.exe',
                'C:\\Program Files\\Python310\\python.exe',
                'C:\\Program Files\\Python311\\python.exe',
                'C:\\Program Files\\Python312\\python.exe',
                'C:\\Program Files\\Python313\\python.exe',
            ];
            
            $user_profile = getenv('USERPROFILE');
            if ($user_profile) {
                $user_paths = [
                    $user_profile . '\\AppData\\Local\\Programs\\Python\\Python39\\python.exe',
                    $user_profile . '\\AppData\\Local\\Programs\\Python\\Python310\\python.exe',
                    $user_profile . '\\AppData\\Local\\Programs\\Python\\Python311\\python.exe',
                    $user_profile . '\\AppData\\Local\\Programs\\Python\\Python312\\python.exe',
                    $user_profile . '\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
                ];
                $common_paths = array_merge($common_paths, $user_paths);
            }
            
            foreach ($common_paths as $path) {
                if (file_exists($path)) {
                    $test_cmd = escapeshellarg($path) . ' --version 2>&1';
                    $output = shell_exec($test_cmd);
                    
                    if ($output !== null && strpos($output, 'Python') !== false && strpos($output, 'Microsoft Store') === false) {
                        $python_found = true;
                        $python_exe = $path;
                        echo '<div class="success">✅ Python ditemukan di: <code>' . htmlspecialchars($path) . '</code></div>';
                        echo '<pre>' . htmlspecialchars($output) . '</pre>';
                        break;
                    }
                }
            }
        }
        
        // Try commands
        if (!$python_found) {
            foreach ($python_commands as $cmd) {
                $test_cmd = escapeshellcmd($cmd) . ' --version 2>&1';
                $output = shell_exec($test_cmd);
                
                // Check if it's Microsoft Store redirect
                if ($output !== null && strpos($output, 'Python') !== false) {
                    if (strpos($output, 'Microsoft Store') !== false || strpos($output, 'App execution aliases') !== false) {
                        echo '<div class="error">⚠️ <code>' . htmlspecialchars($cmd) . '</code> mengarah ke Microsoft Store (bukan Python asli)</div>';
                        echo '<pre>' . htmlspecialchars($output) . '</pre>';
                        continue;
                    }
                    
                    $python_found = true;
                    $python_exe = $cmd;
                    echo '<div class="success">✅ Python ditemukan: <code>' . htmlspecialchars($cmd) . '</code></div>';
                    echo '<pre>' . htmlspecialchars($output) . '</pre>';
                    break;
                }
            }
        }
        
        if (!$python_found) {
            echo '<div class="error">❌ Python TIDAK ditemukan.</div>';
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                echo '<div class="info">';
                echo '<h3>🔧 Solusi untuk Windows:</h3>';
                echo '<ol>';
                echo '<li><strong>Disable App Execution Aliases:</strong><br>';
                echo 'Buka <code>Settings > Apps > Advanced app settings > App execution aliases</code><br>';
                echo 'Disable "python.exe" dan "python3.exe"</li>';
                echo '<li><strong>Install Python dari python.org:</strong><br>';
                echo 'Download dari <a href="https://www.python.org/downloads/" target="_blank">https://www.python.org/downloads/</a><br>';
                echo 'Saat install, <strong>centang "Add Python to PATH"</strong></li>';
                echo '<li><strong>Gunakan Python Launcher:</strong><br>';
                echo 'Coba gunakan <code>py</code> command (Python Launcher untuk Windows)</li>';
                echo '<li><strong>Tambahkan ke PATH manual:</strong><br>';
                echo 'Cari lokasi Python (biasanya <code>C:\\PythonXX</code> atau <code>C:\\Users\\USERNAME\\AppData\\Local\\Programs\\Python\\PythonXX</code>)<br>';
                echo 'Tambahkan ke System Environment Variables > PATH</li>';
                echo '</ol>';
                echo '</div>';
            } else {
                echo '<div class="info">💡 Tips: Install Python dari https://www.python.org/ dan centang "Add Python to PATH" saat install</div>';
            }
        }
        echo '</div>';
        
        // Test 3: Check Python Script
        echo '<div class="test-section">';
        echo '<h2>3. Test Python Script</h2>';
        $python_script = __DIR__ . '/python/generate_chart.py';
        $python_script = realpath($python_script);
        
        if (file_exists($python_script)) {
            echo '<div class="success">✅ Python script ditemukan</div>';
            echo '<div class="info">Path: <code>' . htmlspecialchars($python_script) . '</code></div>';
        } else {
            echo '<div class="error">❌ Python script TIDAK ditemukan</div>';
            echo '<div class="info">Expected: <code>' . htmlspecialchars($python_script) . '</code></div>';
        }
        echo '</div>';
        
        // Test 4: Check Python Dependencies
        if ($python_found && $python_exe) {
            echo '<div class="test-section">';
            echo '<h2>4. Test Python Dependencies</h2>';
            
            $dependencies = [
                'mysql.connector' => 'mysql-connector-python',
                'matplotlib' => 'matplotlib',
                'pandas' => 'pandas'
            ];
            
            foreach ($dependencies as $module => $package) {
                $test_cmd = escapeshellcmd($python_exe) . ' -c "import ' . escapeshellarg($module) . '" 2>&1';
                $output = shell_exec($test_cmd);
                
                if (trim($output) === '') {
                    echo '<div class="success">✅ ' . htmlspecialchars($package) . ' terinstall</div>';
                } else {
                    echo '<div class="error">❌ ' . htmlspecialchars($package) . ' TIDAK terinstall</div>';
                    echo '<div class="info">Install dengan: <code>pip install ' . htmlspecialchars($package) . '</code></div>';
                    echo '<pre>' . htmlspecialchars($output) . '</pre>';
                }
            }
            echo '</div>';
        }
        
        // Test 5: Test Database Connection (from Python perspective)
        if ($python_found && $python_exe) {
            echo '<div class="test-section">';
            echo '<h2>5. Test Database Connection (Python)</h2>';
            
            $test_db_script = 'import mysql.connector; conn = mysql.connector.connect(host="localhost", user="root", password="", database="expense_tracker"); print("Database connection: OK"); conn.close()';
            $test_cmd = escapeshellcmd($python_exe) . ' -c ' . escapeshellarg($test_db_script) . ' 2>&1';
            $output = shell_exec($test_cmd);
            
            if (strpos($output, 'OK') !== false) {
                echo '<div class="success">✅ Koneksi database berhasil</div>';
            } else {
                echo '<div class="error">❌ Koneksi database GAGAL</div>';
                echo '<pre>' . htmlspecialchars($output) . '</pre>';
                echo '<div class="info">💡 Pastikan MySQL berjalan dan database expense_tracker sudah dibuat</div>';
            }
            echo '</div>';
        }
        
        // Test 6: Test Chart Directory
        echo '<div class="test-section">';
        echo '<h2>6. Test Chart Directory</h2>';
        $chart_dir = __DIR__ . '/assets/charts';
        
        if (!is_dir($chart_dir)) {
            @mkdir($chart_dir, 0755, true);
        }
        
        if (is_dir($chart_dir)) {
            echo '<div class="success">✅ Directory chart ada</div>';
            echo '<div class="info">Path: <code>' . htmlspecialchars(realpath($chart_dir)) . '</code></div>';
            
            if (is_writable($chart_dir)) {
                echo '<div class="success">✅ Directory chart writable</div>';
            } else {
                echo '<div class="error">❌ Directory chart TIDAK writable</div>';
                echo '<div class="info">💡 Ubah permission folder: <code>chmod 755 assets/charts</code> (Linux/Mac) atau Properties → Security (Windows)</div>';
            }
        } else {
            echo '<div class="error">❌ Directory chart TIDAK bisa dibuat</div>';
        }
        echo '</div>';
        
        // Test 7: Try to run Python script
        if ($python_found && $python_exe && file_exists($python_script)) {
            echo '<div class="test-section">';
            echo '<h2>7. Test Run Python Script</h2>';
            
            $test_cmd = escapeshellcmd($python_exe) . ' ' . escapeshellarg($python_script) . ' 2>&1';
            $output = shell_exec($test_cmd);
            
            echo '<div class="info">Command: <code>' . htmlspecialchars($test_cmd) . '</code></div>';
            echo '<pre>' . htmlspecialchars($output) . '</pre>';
            
            $chart_path = __DIR__ . '/assets/charts/chart_latest.png';
            if (file_exists($chart_path)) {
                echo '<div class="success">✅ Chart berhasil di-generate!</div>';
                echo '<div class="info">File: <code>' . htmlspecialchars(realpath($chart_path)) . '</code></div>';
                echo '<div class="info">Size: ' . filesize($chart_path) . ' bytes</div>';
            } else {
                echo '<div class="error">❌ Chart file TIDAK terbuat</div>';
                echo '<div class="info">Expected: <code>' . htmlspecialchars($chart_path) . '</code></div>';
            }
            echo '</div>';
        }
        ?>
        
        <div class="test-section">
            <h2>📝 Kesimpulan</h2>
            <p>Jika semua test berhasil, chart generation seharusnya bekerja. Jika ada error, ikuti saran di setiap section.</p>
            <p><a href="index.php">← Kembali ke Dashboard</a></p>
        </div>
    </div>
</body>
</html>

