# 🔧 Cara Memperbaiki Error "Python was not found" di Windows

## Masalah
Error: `Python was not found; run without arguments to install from the Microsoft Store`

Ini terjadi karena Windows App Execution Aliases mengarahkan perintah `python` ke Microsoft Store, bukan ke Python yang sebenarnya terinstall.

## Solusi (Pilih salah satu)

### ✅ Solusi 1: Disable App Execution Aliases (Paling Mudah)

1. Buka **Settings** (Windows + I)
2. Pergi ke **Apps** > **Advanced app settings** > **App execution aliases**
3. Cari **python.exe** dan **python3.exe**
4. **Disable** kedua shortcut tersebut
5. Restart terminal/command prompt
6. Coba lagi: `python --version`

### ✅ Solusi 2: Install Python dari python.org (Recommended)

1. Download Python dari: https://www.python.org/downloads/
2. **JANGAN** install dari Microsoft Store
3. Saat install, **PENTING**: Centang **"Add Python to PATH"**
4. Pilih **"Install Now"** atau **"Customize installation"**
5. Setelah install, restart terminal
6. Test: `python --version`

### ✅ Solusi 3: Gunakan Python Launcher (py)

Jika Python sudah terinstall tapi tidak di PATH:

1. Coba gunakan command `py`:
   ```bash
   py --version
   ```

2. Jika berhasil, aplikasi akan otomatis menggunakan `py` launcher

### ✅ Solusi 4: Tambahkan Python ke PATH Manual

1. Cari lokasi Python terinstall:
   - Biasanya di: `C:\Python39\`, `C:\Python310\`, dll
   - Atau: `C:\Users\USERNAME\AppData\Local\Programs\Python\Python39\`

2. Copy path ke folder Python (contoh: `C:\Python311`)

3. Tambahkan ke PATH:
   - Buka **System Properties** > **Environment Variables**
   - Di **System Variables**, pilih **Path** > **Edit**
   - Klik **New** > Paste path Python
   - Klik **OK** di semua window

4. Restart terminal/command prompt

5. Test: `python --version`

## Verifikasi

Setelah melakukan salah satu solusi di atas:

1. Buka Command Prompt atau PowerShell
2. Jalankan: `python --version`
3. Harus muncul versi Python (contoh: `Python 3.11.5`)
4. **JANGAN** muncul pesan tentang Microsoft Store

## Test di Aplikasi

1. Buka browser: `http://localhost:8000/test_python.php`
2. Lihat apakah Python terdeteksi
3. Jika masih error, cek detail error di test page

## Catatan Penting

- ⚠️ **JANGAN** install Python dari Microsoft Store jika ingin menggunakan di aplikasi
- ✅ Install dari **python.org** dan centang **"Add Python to PATH"**
- ✅ Setelah install, **restart terminal/command prompt**
- ✅ Test dengan `python --version` sebelum menggunakan aplikasi

## Troubleshooting Lanjutan

Jika masih tidak bekerja:

1. Cek apakah Python benar-benar terinstall:
   ```bash
   where python
   ```

2. Cek versi Python:
   ```bash
   python --version
   ```

3. Cek PATH environment variable:
   ```bash
   echo %PATH%
   ```

4. Install dependencies Python:
   ```bash
   python -m pip install mysql-connector-python matplotlib pandas
   ```

5. Test Python script manual:
   ```bash
   python python/generate_chart.py
   ```

