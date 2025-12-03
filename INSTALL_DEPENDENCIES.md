# 📦 Install Python Dependencies

## Error yang Terjadi
```
ModuleNotFoundError: No module named 'mysql'
```

Ini berarti dependencies Python belum terinstall.

## 🚀 Cara Install (Pilih salah satu)

### ✅ Metode 1: Menggunakan Script Helper (Paling Mudah)

**Windows:**
1. Double-click file `install_dependencies.bat`
2. Script akan otomatis mencari Python dan menginstall dependencies
3. Tunggu sampai selesai

**Linux/Mac:**
```bash
chmod +x install_dependencies.sh
./install_dependencies.sh
```

### ✅ Metode 2: Install Manual via Command Prompt/Terminal

**Windows:**
1. Buka **Command Prompt** (atau PowerShell)
2. Jalankan command berikut:
   ```bash
   python -m pip install mysql-connector-python matplotlib pandas
   ```
   
   Atau jika menggunakan `py` launcher:
   ```bash
   py -m pip install mysql-connector-python matplotlib pandas
   ```

**Linux/Mac:**
```bash
python3 -m pip install mysql-connector-python matplotlib pandas
```

### ✅ Metode 3: Install Satu per Satu

Jika ada masalah dengan install sekaligus:

```bash
python -m pip install mysql-connector-python
python -m pip install matplotlib
python -m pip install pandas
```

## 🔍 Verifikasi Install

Setelah install, verifikasi dengan:

```bash
python -m pip list | findstr /i "mysql matplotlib pandas"
```

Atau:

```bash
python -c "import mysql.connector; import matplotlib; import pandas; print('All dependencies installed!')"
```

## ⚠️ Troubleshooting

### Error: "pip is not recognized"

**Solusi:**
1. Pastikan Python terinstall dengan benar
2. Install ulang Python dan centang **"Add Python to PATH"**
3. Atau gunakan: `python -m ensurepip --upgrade`

### Error: "Permission denied"

**Windows:**
- Jalankan Command Prompt sebagai **Administrator**

**Linux/Mac:**
- Gunakan `sudo`: `sudo python3 -m pip install ...`
- Atau install untuk user saja: `python3 -m pip install --user ...`

### Error: "No module named 'pip'"

**Solusi:**
```bash
python -m ensurepip --upgrade
```

Atau download get-pip.py:
```bash
python get-pip.py
```

### Dependencies sudah terinstall tapi masih error

1. Pastikan menggunakan Python yang sama:
   ```bash
   python --version
   python -m pip list
   ```

2. Cek apakah dependencies terinstall:
   ```bash
   python -c "import mysql.connector; print('mysql.connector: OK')"
   python -c "import matplotlib; print('matplotlib: OK')"
   python -c "import pandas; print('pandas: OK')"
   ```

3. Jika masih error, coba install ulang:
   ```bash
   python -m pip install --upgrade mysql-connector-python matplotlib pandas
   ```

## 📝 Dependencies yang Diperlukan

1. **mysql-connector-python** - Koneksi ke MySQL database
2. **matplotlib** - Generate chart/graph
3. **pandas** - Data processing (opsional, tapi recommended)

## ✅ Setelah Install

1. Refresh halaman aplikasi
2. Klik tombol **"Generate Chart"**
3. Chart seharusnya sudah bisa di-generate

## 🧪 Test

Buka `test_python.php` di browser untuk test apakah dependencies sudah terinstall dengan benar.

