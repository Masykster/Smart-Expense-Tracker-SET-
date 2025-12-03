<?php
/**
 * API: Generate Chart Manually
 * Regenerate chart by calling Python script
 */

header('Content-Type: application/json');

// Generate chart
$python_script = __DIR__ . '/../python/generate_chart.py';
$python_script = realpath($python_script);

if (!file_exists($python_script)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Python script tidak ditemukan: ' . $python_script
    ]);
    exit;
}

// Check if shell_exec is enabled
if (!function_exists('shell_exec')) {
    echo json_encode([
        'success' => false, 
        'message' => 'shell_exec() tidak tersedia. Pastikan fungsi ini tidak di-disable di php.ini'
    ]);
    exit;
}

// Find Python executable
$python_exe = null;
$python_commands = ['py', 'python3', 'python'];

// Windows-specific: Try common Python installation paths
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
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
        'C:\\Program Files (x86)\\Python39\\python.exe',
        'C:\\Program Files (x86)\\Python310\\python.exe',
        'C:\\Program Files (x86)\\Python311\\python.exe',
        'C:\\Program Files (x86)\\Python312\\python.exe',
        'C:\\Program Files (x86)\\Python313\\python.exe',
    ];
    
    // Also check user's AppData
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
    
    // Try full paths first
    foreach ($common_paths as $path) {
        if (file_exists($path)) {
            $test_cmd = escapeshellarg($path) . ' --version 2>&1';
            $test_output = shell_exec($test_cmd);
            
            if ($test_output !== null && strpos($test_output, 'Python') !== false && strpos($test_output, 'Microsoft Store') === false) {
                $python_exe = $path;
                break;
            }
        }
    }
}

// If not found via full path, try commands
if ($python_exe === null) {
    foreach ($python_commands as $cmd) {
        // Try to find Python
        $test_cmd = escapeshellcmd($cmd) . ' --version 2>&1';
        $test_output = shell_exec($test_cmd);
        
        // Check if it's a valid Python (not Microsoft Store redirect)
        if ($test_output !== null && 
            strpos($test_output, 'Python') !== false && 
            strpos($test_output, 'Microsoft Store') === false &&
            strpos($test_output, 'App execution aliases') === false) {
            $python_exe = $cmd;
            break;
        }
    }
}

if ($python_exe === null) {
    $error_msg = 'Python tidak ditemukan. ';
    $solutions = [];
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $error_msg .= 'Kemungkinan masalah: Windows App Execution Aliases mengarahkan perintah python ke Microsoft Store.';
        $solutions[] = 'Solusi 1: Disable App Execution Aliases - Buka Settings > Apps > Advanced app settings > App execution aliases, lalu disable "python.exe" dan "python3.exe"';
        $solutions[] = 'Solusi 2: Install Python dari https://www.python.org/ (bukan dari Microsoft Store) dan centang "Add Python to PATH" saat install';
        $solutions[] = 'Solusi 3: Gunakan Python Launcher "py" - biasanya sudah tersedia setelah install Python';
        $solutions[] = 'Solusi 4: Tambahkan Python ke PATH manual - Cari lokasi Python (biasanya C:\\PythonXX atau C:\\Users\\USERNAME\\AppData\\Local\\Programs\\Python\\PythonXX) dan tambahkan ke System Environment Variables';
    } else {
        $solutions[] = 'Install Python: sudo apt install python3 (Ubuntu/Debian) atau brew install python3 (Mac)';
        $solutions[] = 'Pastikan Python ada di PATH: which python3';
    }
    
    echo json_encode([
        'success' => false, 
        'message' => $error_msg,
        'solutions' => $solutions,
        'debug' => [
            'tested_commands' => $python_commands,
            'shell_exec_available' => function_exists('shell_exec'),
            'os' => PHP_OS
        ]
    ]);
    exit;
}

// Execute Python script
$python_cmd = escapeshellcmd($python_exe) . ' ' . escapeshellarg($python_script) . ' 2>&1';
$output = shell_exec($python_cmd);

// Check if chart file was created
$chart_path = __DIR__ . '/../assets/charts/chart_latest.png';
$chart_path = realpath(dirname($chart_path)) . '/chart_latest.png';

// Check if directory exists and is writable
$chart_dir = dirname($chart_path);
if (!is_dir($chart_dir)) {
    @mkdir($chart_dir, 0755, true);
}

$chart_exists = file_exists($chart_path);
$chart_writable = is_writable($chart_dir);

if ($chart_exists) {
    echo json_encode([
        'success' => true, 
        'message' => 'Chart berhasil di-generate',
        'output' => trim($output),
        'python_exe' => $python_exe,
        'chart_path' => $chart_path
    ]);
} else {
    // Parse output for errors
    $error_message = 'Gagal generate chart.';
    $error_details = [];
    
    if (strpos($output, 'ModuleNotFoundError') !== false) {
        $error_message = 'Dependencies Python tidak lengkap.';
        $error_details[] = 'ModuleNotFoundError detected';
        
        // Provide installation command based on detected Python
        $install_cmd = $python_exe . ' -m pip install mysql-connector-python matplotlib pandas';
        if (strpos($python_exe, '.exe') !== false) {
            // Full path to Python
            $install_cmd = '"' . $python_exe . '" -m pip install mysql-connector-python matplotlib pandas';
        } else {
            // Command name
            $install_cmd = $python_exe . ' -m pip install mysql-connector-python matplotlib pandas';
        }
    } elseif (strpos($output, 'mysql.connector') !== false || strpos($output, 'database') !== false) {
        $error_message = 'Error koneksi database. Cek konfigurasi database di python/generate_chart.py';
        $error_details[] = 'Database connection error';
    } elseif (strpos($output, 'Permission denied') !== false) {
        $error_message = 'Permission denied. Pastikan folder assets/charts/ memiliki permission write.';
        $error_details[] = 'Permission error';
    } elseif (trim($output) === '') {
        $error_message = 'Python script tidak menghasilkan output. Cek error log atau jalankan manual untuk debug.';
        $error_details[] = 'No output from script';
    }
    
    // Build response
    $response = [
        'success' => false, 
        'message' => $error_message,
        'output' => trim($output),
        'python_exe' => $python_exe,
        'python_cmd' => $python_cmd,
        'chart_path' => $chart_path,
        'chart_dir_exists' => is_dir($chart_dir),
        'chart_dir_writable' => $chart_writable,
        'error_details' => $error_details,
        'debug_info' => [
            'script_path' => $python_script,
            'script_exists' => file_exists($python_script)
        ]
    ];
    
    // Add installation instructions for ModuleNotFoundError
    if (strpos($output, 'ModuleNotFoundError') !== false) {
        $install_cmd = $python_exe;
        if (strpos($python_exe, '.exe') !== false || strpos($python_exe, '\\') !== false) {
            // Full path to Python
            $install_cmd = '"' . $python_exe . '" -m pip install mysql-connector-python matplotlib pandas';
        } else {
            // Command name
            $install_cmd = $python_exe . ' -m pip install mysql-connector-python matplotlib pandas';
        }
        
        $response['install_command'] = $install_cmd;
        $response['install_instructions'] = [
            'Windows: Buka Command Prompt sebagai Administrator, lalu jalankan:',
            $install_cmd,
            '',
            'Atau gunakan script helper:',
            'Double-click file install_dependencies.bat di folder project',
            '',
            'Linux/Mac:',
            $install_cmd
        ];
    }
    
    echo json_encode($response);
}

