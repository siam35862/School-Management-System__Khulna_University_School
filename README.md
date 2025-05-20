# School Management System

A comprehensive web-based  management system built with PHP and MySQL, designed to manage admission system, result making and publication system, attendance system, notice system, and information storage system automatically and real time update.

## Features

- Automated Result Making System 
- Attendance System 
- Admission System
- Real Notice Management
- Data Management System

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web Server (Apache/WAMP/XAMPP)

## Installation

1. **Set up your web server**
   - If you're using WAMP:
     - Make sure WAMP is installed and running
     - Place the project files in the `www` directory (e.g., `C:/wamp64/www/School-Management-System`)

2. **Database Setup**

   You can set up the database using phpMyAdmin (GUI Method) or MySQL command line.

   ### Using phpMyAdmin (Recommended)
   1. Open phpMyAdmin in your browser:
      - If using WAMP: Click on the WAMP icon in the system tray → phpMyAdmin
      - Or visit: `http://localhost/phpmyadmin`
   
   2. Login to phpMyAdmin (default username is 'root' with no password for WAMP)
   
   3. Create a new database:
      - Click "New" in the left sidebar
      - Enter "school_management_system" as the database name
      - Click "Create"
   
   4. Import the database schema:
      - Select the "school_management_system" database from the left sidebar
      - Click the "Import" tab at the top
      - Click "Choose File" and select `Database/ddl.sql`
      - Scroll down to "other options"
      - Uncheck the box for "Enable foreign key checks"
      - Click "Import"
   
   5. (Optional) Import sample data:
      - Repeat the import process
      - Select `Database/data_insert.sql`
      - Make sure "Enable foreign key checks" is unchecked
      - Click "Import"

   ### Using MySQL Command Line (Alternative)
   ```bash
   # Log in to MySQL and create a new database
   mysql -u root -p
   CREATE DATABASE school_management_system;
   USE school_management_system;
   
   # Import the database schema
   mysql -u root -p school_management_system < Database/ddl.sql
   
   # (Optional) Import sample data
   mysql -u root -p school_management_system < Database/data_insert.sql
   ```

3. **Configure Database Connection**
   - Open `assets/database.php` and update the database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');  // default 'root' for WAMP
     define('DB_PASS', 'your_password');  // default '' (empty) for WAMP
     define('DB_NAME', 'school_management_system');
     ```

## Running the Application

1. Start your WAMP/XAMPP server
2. Open your web browser and navigate to:
   ```
   http://localhost/School-Management-System/index.php
   ```


## Authors

This project is built by students from the Computer Science and Engineering discipline, Khulna University:

- **Md. Siam Ahmed**
  - Student ID: 230202

- **Md. Masrafi Murtoja**
  - Student ID: 230207

- **Sobuj Chandra Paul**
  - Student ID: 230231


