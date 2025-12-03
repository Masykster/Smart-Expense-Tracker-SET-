@echo off
echo ========================================
echo Install Python Dependencies
echo Smart Expense Tracker (SET)
echo ========================================
echo.

REM Try to find Python
echo [1/3] Mencari Python...
python --version >nul 2>&1
if %errorlevel% == 0 (
    echo Python ditemukan!
    set PYTHON_CMD=python
    goto :install
)

py --version >nul 2>&1
if %errorlevel% == 0 (
    echo Python ditemukan (menggunakan py launcher)!
    set PYTHON_CMD=py
    goto :install
)

python3 --version >nul 2>&1
if %errorlevel% == 0 (
    echo Python ditemukan (python3)!
    set PYTHON_CMD=python3
    goto :install
)

echo.
echo ERROR: Python tidak ditemukan!
echo.
echo Solusi:
echo 1. Install Python dari https://www.python.org/downloads/
echo 2. Pastikan centang "Add Python to PATH" saat install
echo 3. Restart Command Prompt setelah install
echo.
pause
exit /b 1

:install
echo.
echo [2/3] Menginstall dependencies...
echo Command: %PYTHON_CMD% -m pip install mysql-connector-python matplotlib pandas
echo.

%PYTHON_CMD% -m pip install mysql-connector-python matplotlib pandas

if %errorlevel% == 0 (
    echo.
    echo [3/3] SUCCESS! Dependencies berhasil diinstall.
    echo.
    echo Dependencies yang terinstall:
    %PYTHON_CMD% -m pip list | findstr /i "mysql matplotlib pandas"
    echo.
    echo Silakan refresh halaman aplikasi dan coba generate chart lagi.
) else (
    echo.
    echo ERROR: Gagal menginstall dependencies.
    echo.
    echo Coba install manual:
    echo %PYTHON_CMD% -m pip install mysql-connector-python matplotlib pandas
    echo.
)

pause

