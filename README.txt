C-Gas PHP + MSSQL Starter App

Files included:
- index.php
- login.php
- logout.php
- register.php
- edit_account.php
- dashboard.php
- header.php
- footer.php
- db.php
- config.php
- assets/style.css
- assets/app.js
- init_db.sql

Setup:
1. Install PHP with Microsoft SQL Server support (sqlsrv and pdo_sqlsrv).
2. Create the database and table by running init_db.sql in SQL Server Management Studio.
3. Update config.php with your MSSQL server, username, and password.
4. Put the project folder inside your web server root.
5. Open index.php in your browser.

Notes:
- Passwords are stored using PHP password_hash.
- This is a clean starter template for thesis or capstone demos.
- You can extend it with transactions, RFID tap logs, and rewards redemption.
