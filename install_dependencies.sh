#!/bin/bash

echo "========================================"
echo "Install Python Dependencies"
echo "Smart Expense Tracker (SET)"
echo "========================================"
echo ""

# Try to find Python
echo "[1/3] Mencari Python..."

if command -v python3 &> /dev/null; then
    PYTHON_CMD=python3
    echo "Python ditemukan (python3)!"
elif command -v python &> /dev/null; then
    PYTHON_CMD=python
    echo "Python ditemukan (python)!"
else
    echo ""
    echo "ERROR: Python tidak ditemukan!"
    echo ""
    echo "Solusi:"
    echo "1. Install Python: sudo apt install python3 (Ubuntu/Debian)"
    echo "2. Atau: brew install python3 (Mac)"
    echo "3. Pastikan Python ada di PATH"
    exit 1
fi

echo ""
echo "[2/3] Menginstall dependencies..."
echo "Command: $PYTHON_CMD -m pip install mysql-connector-python matplotlib pandas"
echo ""

$PYTHON_CMD -m pip install mysql-connector-python matplotlib pandas

if [ $? -eq 0 ]; then
    echo ""
    echo "[3/3] SUCCESS! Dependencies berhasil diinstall."
    echo ""
    echo "Dependencies yang terinstall:"
    $PYTHON_CMD -m pip list | grep -i "mysql\|matplotlib\|pandas"
    echo ""
    echo "Silakan refresh halaman aplikasi dan coba generate chart lagi."
else
    echo ""
    echo "ERROR: Gagal menginstall dependencies."
    echo ""
    echo "Coba install manual:"
    echo "$PYTHON_CMD -m pip install mysql-connector-python matplotlib pandas"
    echo ""
fi

