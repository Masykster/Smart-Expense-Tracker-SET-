# Smart Expense Tracker (SET)

Aplikasi web sederhana untuk mencatat arus kas masuk/keluar dan menghasilkan visualisasi data otomatis menggunakan Python.

## Teknologi yang Digunakan

- **Backend:** PHP 8.x (Native)
- **Data Processing & Visualization:** Python 3.x (Matplotlib, Pandas)
- **Frontend:** HTML/CSS (Bootstrap 5) + JavaScript (Vanilla)
- **Database:** MySQL/MariaDB

## Fitur

### 1. Modul Transaksi (CRUD)
- ✅ Create: Tambah transaksi baru
- ✅ Read: Tampilkan daftar transaksi
- ✅ Update: Edit transaksi
- ✅ Delete: Hapus transaksi (dilindungi trigger)

### 2. Modul Pencarian
- ✅ Live Search: Pencarian real-time tanpa reload halaman

### 3. Modul Visualisasi
- ✅ Generate Chart: Pie chart pemasukan vs pengeluaran
- ✅ Auto-generate: Chart otomatis dibuat saat halaman dimuat

### 4. Fitur Database Advanced
- ✅ **View:** `v_ringkasan_bulanan` - Ringkasan bulanan
- ✅ **Stored Procedure:** `sp_tambah_transaksi` - Menambah transaksi dengan validasi
- ✅ **Function:** `fn_cek_kesehatan_finansial` - Cek status finansial
- ✅ **Trigger:** `tr_audit_hapus` - Audit log saat hapus transaksi

## 🚀 Quick Start

### Clone Repository
```bash
git clone https://github.com/USERNAME/REPO_NAME.git
cd REPO_NAME
```

### Setup Konfigurasi
```bash
# Copy example config
cp config/database.example.php config/database.php

# Edit config/database.php dengan kredensial database Anda
```

### Setup Database
```bash
mysql -u root -p < database/schema.sql
```

### Install Dependencies
```bash
# Python dependencies
python -m pip install -r requirements.txt
# Atau gunakan: install_dependencies.bat (Windows)
```

### Jalankan Aplikasi
```bash
php -S localhost:8000
```

Buka browser: `http://localhost:8000`

---

## Instalasi

### 1. Persyaratan
- PHP 8.x atau lebih tinggi
- Python 3.x
- MySQL/MariaDB
- Web server (Apache/Nginx) atau PHP built-in server
- Extension PHP: `mysqli`, `shell_exec` enabled

### 2. Setup Database

1. Import schema database:
```bash
mysql -u root -p < database/schema.sql
```

Atau buka phpMyAdmin dan import file `database/schema.sql`

2. Konfigurasi database di `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'expense_tracker');
```

### 3. Setup Python

1. Install dependencies Python:
```bash
pip install -r requirements.txt
```

Atau install manual:
```bash
pip install mysql-connector-python matplotlib pandas
```

2. Pastikan Python dapat diakses dari command line:
```bash
python --version
```

### 4. Setup Direktori

Pastikan direktori untuk chart sudah ada:
```bash
mkdir -p assets/charts
```

Atau buat manual folder `assets/charts/` di root project.

### 5. Jalankan Aplikasi

**Menggunakan PHP Built-in Server:**
```bash
php -S localhost:8000
```

Kemudian buka browser: `http://localhost:8000`

**Menggunakan Web Server (Apache/Nginx):**
- Copy project ke `htdocs` atau `www` directory
- Akses melalui browser sesuai konfigurasi web server

## Struktur Project

```
.
├── api/                    # API endpoints
│   ├── add_transaction.php
│   ├── update_transaction.php
│   ├── delete_transaction.php
│   ├── get_transactions.php
│   └── get_summary.php
├── assets/
│   └── charts/            # Folder untuk menyimpan chart PNG
├── config/
│   └── database.php       # Konfigurasi database
├── database/
│   └── schema.sql         # SQL schema dan setup
├── python/
│   └── generate_chart.py  # Script Python untuk generate chart
├── index.php              # Halaman utama/dashboard
└── README.md
```

## Penggunaan

### Menambah Transaksi
1. Klik tombol "Tambah Transaksi"
2. Isi form: Tanggal, Jenis (Masuk/Keluar), Kategori, Nominal, Keterangan
3. Klik "Simpan"

### Mencari Transaksi
- Ketikkan kata kunci di kolom pencarian
- Tabel akan terfilter secara otomatis

### Edit Transaksi
- Klik tombol edit (ikon pensil) pada transaksi yang ingin diubah
- Ubah data yang diperlukan
- Klik "Update"

### Hapus Transaksi
- Klik tombol hapus (ikon trash) pada transaksi yang ingin dihapus
- Konfirmasi penghapusan
- Data akan tersimpan di `tb_log_hapus` (audit log)

## Catatan Penting

1. **Python Path:** Jika perintah `python` tidak bekerja, ubah di file PHP yang memanggil script Python:
   - Ganti `python` dengan `python3` atau path lengkap ke Python executable

2. **Permission:** Pastikan folder `assets/charts/` memiliki permission write untuk menyimpan file chart

3. **Database:** Pastikan user database memiliki permission untuk:
   - CREATE, SELECT, INSERT, UPDATE, DELETE
   - CREATE VIEW, CREATE PROCEDURE, CREATE FUNCTION, CREATE TRIGGER

## Troubleshooting

### Chart tidak muncul
- Pastikan Python script dapat dijalankan
- Cek permission folder `assets/charts/`
- Cek error log PHP untuk detail error

### Database connection error
- Pastikan MySQL/MariaDB berjalan
- Cek konfigurasi di `config/database.php`
- Pastikan database `expense_tracker` sudah dibuat

### Python script error
- Pastikan semua dependencies terinstall
- Cek koneksi database dari Python
- Pastikan MySQL connector Python terinstall

## License

MIT License

