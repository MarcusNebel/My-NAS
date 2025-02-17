# NAS-Website

This is the repository for the **NAS-Website** project, a simple web application for managing user accounts with MySQL, PHP, HTML, CSS, and JavaScript. The website runs on an Apache2 web server.

## Features

- **User Management**: Users can register, log in, and reset their passwords.
- **Database**: The MySQL database contains a table called `accounts` for managing user information.
  - **Columns in the `accounts` table**:
    - `USERNAME`: The account's username.
    - `PASSWORD`: The user's password (stored encrypted).
    - `EMAIL`: The user's email address.
    - `reset_code`: A code used for password reset.
    - `rememberTOKEN`: A token used for the "Remember me" functionality.

---

## Prerequisites

- **Ubuntu Server** (or desktop with server components)
- **SSH access** to the server (if remote)
- **Domain** (optional, for public websites)
- **Apache2**, **MySQL**, **PHP**, and **phpMyAdmin** must be installed on the server.

---

## Installation

### 1. **Install Apache2**

Install Apache2:
```bash
sudo apt update
sudo apt install apache2 -y
```

Start the Apache2 service:
```bash
sudo systemctl enable apache2
sudo systemctl start apache2
```

### 2. **Install MySQL**

Install MySQL:
```bash
sudo apt install mysql-server -y
```

Run the security setup:
```bash
sudo mysql_secure_installation
```

### 3. **Install PHP**

Install PHP and required modules:
```bash
sudo apt install php libapache2-mod-php php-mysql php-cli php-curl php-zip php-mbstring php-xml -y
```

Restart Apache to ensure PHP works correctly:
```bash
sudo systemctl restart apache2
```

### 4. **Set up the Database**

Log in to MySQL:
```bash
sudo mysql -u root -p
```

Create the database, table and the 'root@localhost' password:
```sql
CREATE DATABASE `nas-website`;
USE `nas-website`;

CREATE TABLE `accounts` (
  `USERNAME` varchar(255) DEFAULT NULL,
  `PASSWORD` varchar(255) DEFAULT NULL,
  `EMAIL` varchar(255) DEFAULT NULL,
  `reset_code` varchar(6) DEFAULT NULL,
  `rememberTOKEN` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

ALTER TABLE `accounts`
  ADD UNIQUE KEY `USERNAME` (`USERNAME`);
COMMIT;

ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '59LWrt!mDo6GC4';
FLUSH PRIVILEGES;

EXIT;
```

### 5. **Upload Website Files**

Copy your HTML, CSS, JS, and PHP files into the web server folder `/var/www/html/nas-website`.
If you need to upload files from a local machine, use SCP or FTP:
```bash
cd ~
mkdir nas-website
ls
```

Upload files to server via SCP(make sure that you copy the individual Folder like ".vscode, Login, Main_Website,PHPMailer, LICANSE, Logo.png and README.md):
```bash
scp -r "/local/path/to/github/download/*" user@server-ip:~/nas-website
```

Move the nas-website folder to `var/www/html/`:
```bash
sudo mv ~/nas-website /var/www/html/
```

Configure forgot_password.php:
Open the forgot_password.php with the following command: 
```bash
sudo nano /var/www/html/nas-website/Login/forgot_password.php
```

Paste your email wich you want to use for your SMTP Server (make sure that your email is a gmail email or you have to configure the SMTP server url and port for an other SMTP server). Next you must create a App Password in your google account settings and paste it to App_Password in the forgot_password.php. 

### 6. **Configure Apache for the Website**

Create a new Apache configuration file:
```bash
sudo nano /etc/apache2/sites-available/nas-website.conf
```

Add the following content:
```bash
ServerName nas-website.local
DocumentRoot /var/www/html/nas-website
ErrorLog ${APACHE_LOG_DIR}/error.log
CustomLog ${APACHE_LOG_DIR}/access.log combined
```

Enable the site and restart Apache:
```bash
sudo a2ensite nas-website.conf
sudo systemctl restart apache2
```

### 7. **Set Permissions**

Ensure that Apache has access to the website files:
```bash
sudo chown -R www-data:www-data /var/www/html/nas-website
sudo chmod -R 755 /var/www/html/nas-website
```

### 8. **Adjust Upload Limits**

If you need to upload large files, you must adjust the upload limits in the Apache configuration or via `.htaccess`.

#### Option 1: **Adjust `.htaccess`**

If you want to change the upload size for a specific directory in your website, you can create or edit a `.htaccess` file in the web folder (e.g., `/var/www/html/nas-website`):

Create or open the `.htaccess` file:
```bash
sudo nano /var/www/html/nas-website/.htaccess
```

Add the following:
```bash
<IfModule mod_php8.c>
    php_value upload_max_filesize 10000G
    php_value post_max_size 10000G
    php_value max_execution_time 86400
    php_value memory_limit 10000G
</IfModule>
```

#### Option 2: **Adjust Apache Configuration**

If you have access to the Apache configuration file, open the file:

For Ubuntu/Debian:
```bash
sudo nano /etc/apache2/apache2.conf
```

Add the following at the end of the file:
```nginx
LimitRequestBody 0
```

`LimitRequestBody 0` means there is no limit for uploads. Save the file and restart Apache:
```bash
sudo systemctl restart apache2
```

### 9. **Test the Website**

Open the website in your browser:
```bash
http://YOUR_SERVER_IP/nas-website
```

If you have configured a domain:
```bash
http://yourdomain.com/nas-website
```

Test if registration, login, and password reset functionalities are working.

---

### **Optional: SSL with Let's Encrypt**

For secure connections, you can set up a free SSL certificate with Let's Encrypt.

Install Certbot:
```bash
sudo apt install certbot python3-certbot-apache -y
```

Request the SSL certificate:
```bash
sudo certbot --apache
```

Follow the instructions to enable SSL for your domain.
