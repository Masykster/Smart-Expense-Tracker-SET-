#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Smart Expense Tracker (SET) - Chart Generator
Generate pie chart for income vs expense visualization
"""

import mysql.connector
import matplotlib
matplotlib.use('Agg')  # Non-interactive backend
import matplotlib.pyplot as plt
import pandas as pd
from datetime import datetime
import os
import sys

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'expense_tracker'
}

def get_data_from_db():
    """Fetch income and expense data from database"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # Get current month data
        query = """
        SELECT 
            SUM(CASE WHEN jenis = 'Masuk' THEN nominal ELSE 0 END) as total_masuk,
            SUM(CASE WHEN jenis = 'Keluar' THEN nominal ELSE 0 END) as total_keluar
        FROM tb_transaksi
        WHERE MONTH(tanggal) = MONTH(CURRENT_DATE())
          AND YEAR(tanggal) = YEAR(CURRENT_DATE())
        """
        
        cursor.execute(query)
        result = cursor.fetchone()
        
        total_masuk = float(result[0]) if result[0] else 0.0
        total_keluar = float(result[1]) if result[1] else 0.0
        
        cursor.close()
        conn.close()
        
        return total_masuk, total_keluar
        
    except mysql.connector.Error as err:
        error_msg = f"Database error: {err.msg} (Error Code: {err.errno})"
        print(error_msg, file=sys.stderr)
        sys.exit(1)
    except Exception as e:
        error_msg = f"Unexpected error: {str(e)}"
        print(error_msg, file=sys.stderr)
        import traceback
        print(traceback.format_exc(), file=sys.stderr)
        sys.exit(1)

def format_rupiah(amount):
    """Format number to Rupiah currency"""
    return f"Rp {amount:,.0f}".replace(',', '.')

def generate_chart():
    """Generate pie chart and save as PNG"""
    total_masuk, total_keluar = get_data_from_db()
    
    # If no data, create empty chart
    if total_masuk == 0 and total_keluar == 0:
        # Create a simple message chart
        fig, ax = plt.subplots(figsize=(10, 8))
        ax.text(0.5, 0.5, 'Belum ada data transaksi', 
                ha='center', va='center', fontsize=16, color='gray')
        ax.set_xlim(0, 1)
        ax.set_ylim(0, 1)
        ax.axis('off')
        plt.tight_layout()
        
        # Save chart
        script_dir = os.path.dirname(os.path.abspath(__file__))
        chart_path = os.path.join(script_dir, '..', 'assets', 'charts', 'chart_latest.png')
        chart_path = os.path.normpath(chart_path)  # Normalize path for Windows
        
        # Create directory if not exists
        chart_dir = os.path.dirname(chart_path)
        try:
            os.makedirs(chart_dir, exist_ok=True)
        except Exception as e:
            print(f"Error creating directory {chart_dir}: {e}", file=sys.stderr)
            sys.exit(1)
        
        # Check if directory is writable
        if not os.access(chart_dir, os.W_OK):
            print(f"Error: Directory {chart_dir} is not writable", file=sys.stderr)
            sys.exit(1)
        
        try:
            plt.savefig(chart_path, dpi=100, bbox_inches='tight')
            plt.close()
            print(f"Empty chart generated: {chart_path}")
        except Exception as e:
            print(f"Error saving chart: {e}", file=sys.stderr)
            import traceback
            print(traceback.format_exc(), file=sys.stderr)
            sys.exit(1)
        return
    
    # Prepare data for pie chart
    labels = ['Pemasukan', 'Pengeluaran']
    sizes = [total_masuk, total_keluar]
    colors = ['#28a745', '#dc3545']
    explode = (0.05, 0.05)  # Explode slices
    
    # Create pie chart
    fig, ax = plt.subplots(figsize=(10, 8))
    
    wedges, texts, autotexts = ax.pie(
        sizes, 
        explode=explode, 
        labels=labels, 
        colors=colors,
        autopct='%1.1f%%',
        shadow=True, 
        startangle=90,
        textprops={'fontsize': 12, 'fontweight': 'bold'}
    )
    
    # Format autopct to show Rupiah
    for i, (size, autotext) in enumerate(zip(sizes, autotexts)):
        autotext.set_text(f'{format_rupiah(size)}\n({size/sum(sizes)*100:.1f}%)')
    
    # Add title
    ax.set_title('Rasio Pemasukan vs Pengeluaran (Bulan Ini)', 
                 fontsize=16, fontweight='bold', pad=20)
    
    # Add legend with values
    legend_labels = [
        f'Pemasukan: {format_rupiah(total_masuk)}',
        f'Pengeluaran: {format_rupiah(total_keluar)}',
        f'Saldo: {format_rupiah(total_masuk - total_keluar)}'
    ]
    ax.legend(wedges, legend_labels, loc="center left", bbox_to_anchor=(1, 0, 0.5, 1))
    
    plt.tight_layout()
    
    # Save chart
    script_dir = os.path.dirname(os.path.abspath(__file__))
    chart_path = os.path.join(script_dir, '..', 'assets', 'charts', 'chart_latest.png')
    chart_path = os.path.normpath(chart_path)  # Normalize path for Windows
    
    # Create directory if not exists
    chart_dir = os.path.dirname(chart_path)
    try:
        os.makedirs(chart_dir, exist_ok=True)
    except Exception as e:
        print(f"Error creating directory {chart_dir}: {e}", file=sys.stderr)
        sys.exit(1)
    
    # Check if directory is writable
    if not os.access(chart_dir, os.W_OK):
        print(f"Error: Directory {chart_dir} is not writable", file=sys.stderr)
        sys.exit(1)
    
    try:
        plt.savefig(chart_path, dpi=100, bbox_inches='tight')
        plt.close()
        print(f"Chart generated successfully: {chart_path}")
    except Exception as e:
        print(f"Error saving chart: {e}", file=sys.stderr)
        import traceback
        print(traceback.format_exc(), file=sys.stderr)
        sys.exit(1)

if __name__ == '__main__':
    try:
        generate_chart()
    except Exception as e:
        import traceback
        error_msg = f"Error: {str(e)}\n{traceback.format_exc()}"
        print(error_msg, file=sys.stderr)
        sys.exit(1)

