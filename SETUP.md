# Panduan Setup Smart Expense Tracker (SET)

## Langkah-langkah Instalasi

### 1. Setup Database

**Opsi A: Menggunakan Command Line**
```bash
mysql -u root -p < database/schema.sql
```

**Opsi B: Menggunakan phpMyAdmin**
1. Buka phpMyAdmin
2. Klik "Import"
3. Pilih file `database/schema.sql`
4. Klik "Go"

**Opsi C: Manual SQL**
1. Buka MySQL client
2. Copy isi file `database/schema.sql`
3. Paste dan execute

### 2. Konfigurasi Database

Edit file `config/database.php` dan sesuaikan dengan konfigurasi MySQL Anda:

```php
define('DB_HOST', 'localhost');  // Host database
define('DB_USER', 'root');        // Username database
define('DB_PASS', '');            // Password database (kosongkan jika tidak ada)
define('DB_NAME', 'expense_tracker'); // Nama database
```

### 3. Install Python Dependencies

**Windows:**
```bash
pip install -r requirements.txt
```

**Linux/Mac:**
```bash
pip3 install -r requirements.txt
```

**Atau install manual:**
```bash
pip install mysql-connector-python matplotlib pandas
```

### 4. Verifikasi Python

Pastikan Python terinstall dan dapat diakses:

**Windows:**
```bash
python --version
```

**Linux/Mac:**
```bash
python3 --version
```

Jika perintah di atas tidak bekerja, Anda perlu:
- Install Python dari https://www.python.org/
- Atau tambahkan Python ke PATH environment variable

### 5. Setup Folder Assets

Pastikan folder `assets/charts/` sudah ada dan memiliki permission write.

**Windows (PowerShell):**
```powershell
New-Item -ItemType Directory -Path "assets\charts" -Force
```

**Linux/Mac:**
```bash
mkdir -p assets/charts
chmod 755 assets/charts
```

### 6. Jalankan Aplikasi

**Menggunakan PHP Built-in Server:**

Buka terminal/command prompt di folder project, lalu jalankan:

```bash
php -S localhost:8000
```

Kemudian buka browser: `http://localhost:8000`

**Menggunakan Laragon (Windows):**

1. Pastikan project sudah di folder `C:\laragon\www\`
2. Start Laragon
3. Buka browser: `http://expense-tracker.test` (atau sesuai konfigurasi virtual host)

**Menggunakan XAMPP/WAMP:**

1. Copy project ke folder `htdocs` atau `www`
2. Start Apache dan MySQL
3. Buka browser: `http://localhost/expense-tracker`

## Troubleshooting

### Error: "Database connection failed"

**Solusi:**
1. Pastikan MySQL/MariaDB sedang berjalan
2. Cek konfigurasi di `config/database.php`
3. Pastikan database `expense_tracker` sudah dibuat
4. Pastikan user database memiliki permission yang cukup

### Error: Chart tidak muncul

**Solusi:**
1. Cek apakah Python terinstall: `python --version`
2. Cek apakah dependencies terinstall: `pip list | grep matplotlib`
3. Cek permission folder `assets/charts/`
4. Cek error log PHP untuk detail error
5. Coba jalankan script Python manual:
   ```bash
   python python/generate_chart.py
   ```

### Error: "python: command not found"

**Solusi Windows:**
1. Install Python dari https://www.python.org/
2. Saat install, centang "Add Python to PATH"
3. Restart terminal/command prompt

**Solusi Linux/Mac:**
- Gunakan `python3` instead of `python`
- Atau install Python: `sudo apt install python3` (Ubuntu/Debian)

### Error: "Permission denied" saat generate chart

**Solusi:**
1. Pastikan folder `assets/charts/` memiliki permission write
2. Windows: Klik kanan folder → Properties → Security → Edit permissions
3. Linux/Mac: `chmod 755 assets/charts`

### Error: "Call to undefined function shell_exec()"

**Solusi:**
1. Edit `php.ini`
2. Cari `disable_functions` dan hapus `shell_exec` dari list
3. Restart web server

### Chart kosong atau tidak update

**Solusi:**
1. Pastikan ada data transaksi di database
2. Cek apakah script Python berjalan dengan benar
3. Cek file `assets/charts/chart_latest.png` apakah terupdate
4. Clear browser cache

## Testing

Setelah setup selesai, lakukan testing:

1. **Test Database:**
   - Buka phpMyAdmin
   - Cek apakah database `expense_tracker` ada
   - Cek apakah tabel `tb_transaksi` dan `tb_log_hapus` ada

2. **Test Python:**
   ```bash
   python python/generate_chart.py
   ```
   - Pastikan tidak ada error
   - Cek apakah file `assets/charts/chart_latest.png` terbuat

3. **Test Aplikasi:**
   - Buka `http://localhost:8000`
   - Coba tambah transaksi
   - Cek apakah chart muncul
   - Coba edit dan hapus transaksi
   - Coba fitur search

## Catatan Penting

1. **Single User:** Aplikasi ini dirancang untuk single user (tidak ada sistem login)
2. **Data Sample:** File `database/schema.sql` sudah include sample data untuk testing
3. **Backup:** Selalu backup database sebelum melakukan perubahan besar
4. **Security:** Untuk production, tambahkan validasi dan sanitasi input yang lebih ketat

## Support

Jika masih ada masalah, cek:
- Error log PHP
- Error log MySQL
- Console browser (F12)
- Output Python script

