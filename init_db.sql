IF DB_ID('Ggas') IS NULL
BEGIN
    CREATE DATABASE Ggas;
END;
GO

USE Ggas;
GO

IF OBJECT_ID('dbo.users', 'U') IS NOT NULL
BEGIN
    DROP TABLE dbo.users;
END;
GO

CREATE TABLE dbo.users (
    user_id INT IDENTITY(1,1) PRIMARY KEY,
    first_name NVARCHAR(100) NOT NULL,
    last_name NVARCHAR(100) NOT NULL,
    email NVARCHAR(150) NOT NULL UNIQUE,
    phone NVARCHAR(30) NULL,
    rfid_uid NVARCHAR(100) NOT NULL UNIQUE,
    password_hash NVARCHAR(255) NOT NULL,
    reward_points INT NOT NULL DEFAULT 0,
    account_status NVARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at DATETIME NOT NULL DEFAULT GETDATE(),
    updated_at DATETIME NULL
);
GO

-- Optional sample account
INSERT INTO dbo.users (
    first_name,
    last_name,
    email,
    phone,
    rfid_uid,
    password_hash,
    reward_points,
    account_status
)
VALUES (
    'Admin',
    'User',
    'admin@cgas.com',
    '09123456789',
    'RFID-0001',
    '$2y$10$WjleOnWZJCrjQ6PfRK1nA.a7j3YeI1G6yGZ6nIK4WMbeDzzc0TDfS',
    120,
    'Active'
);
GO

/*
Sample login for the inserted account:
Email: admin@cgas.com
Password: password123
*/
