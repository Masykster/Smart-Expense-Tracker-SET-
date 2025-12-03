#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Smart Expense Tracker (SET) - Chart Generator
EXAMPLE CONFIGURATION FILE

Copy this file to generate_chart.py and update DB_CONFIG with your database credentials
"""

import mysql.connector
import matplotlib
matplotlib.use('Agg')  # Non-interactive backend
import matplotlib.pyplot as plt
import pandas as pd
from datetime import datetime
import os
import sys

# Database configuration - UPDATE THIS WITH YOUR CREDENTIALS
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',  # Your MySQL password
    'database': 'expense_tracker'
}

# ... rest of the file remains the same ...
# (Copy the rest from generate_chart.py)

