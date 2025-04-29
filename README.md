# NAS-Website

This is the repository for the **NAS-Website** project, a simple web application for managing user accounts with MySQL, PHP, HTML, CSS, and JavaScript. The website runs inside a Docker container for easy deployment.

## Features

- **User Management**: Users can register, log in, and reset their passwords.
- **Database**: The MySQL database contains a table called `accounts` for managing user information.
  - **Columns in the `accounts` table**:
    - `ID`: The account's ID.
    - `USERNAME`: The account's username.
    - `PASSWORD`: The user's password (stored encrypted).
    - `EMAIL`: The user's email address.
    - `reset_code`: A code used for password reset.
    - `rememberTOKEN`: A token used for the "Remember me" functionality.
    - `api_key`: For the My NAS app in the [My-NAS-Flutter-App](https://github.com/MarcusNebel/My-NAS-Flutter-App) repository.
- **Docker-based Deployment**: Easily deploy using Docker and Docker Compose.
- **Automatic Setup**: The entire setup process, including database creation, SSL certification, and configurations, is handled automatically when the container starts.
- **Modern UI**: Built with a clean, modern, and responsive design.

---

## Prerequisites

- **Docker** and **Docker Compose** installed on your system.
- **Linux Server** (or a local machine with Docker support).
- **Domain** (optional, for public websites).

---

## Installation

### 1. **Clone the Repository**

**Linux**

```bash
cd ~
git clone https://github.com/MarcusNebel/NAS-Website.git nas-website
cd nas-website
```

**MacOS**

- Download the release `.ZIP` file and extract it.
- Open a terminal window (ensure you're in the extracted folder).

**Windows**

- Download the release `.ZIP` file and extract it.
- Open a terminal window (ensure you're in the extracted folder).

### 2. **Configuration**

#### **Configure SMTP for Password Reset**

All SMTP settings are now configured exclusively in the `app/config.json` file. Open this file and edit the corresponding fields:

```json
{
    "flaskServerURL": "https://__SERVER_IP__:8080",
    "smtp": {
        "host": "smtp.gmail.com",
        "auth": true,
        "username": "your-email@gmail.com",
        "password": "your-app-password",
        "encryption": "tls",
        "port": 587
    },
    "email": {
        "support_address": "__SERVER_ADMIN_EMAIL__",
        "support_name": "My NAS Support",
        "from_address": "mynas-support@gmail.com",
        "from_name": "My NAS Support",
        "reset_password": "Create new password",
        "registered": "My NAS account created", 
        "signed_in": "New device logged in"
    }
}

```

- **`flaskServerURL`**: The URL of your My NAS server.
- **`host`**: SMTP server address (e.g., `smtp.gmail.com`).
- **`username`**: Your email address.
- **`password`**: Your app password.
- **`port`**: Port for the SMTP server (e.g., `587`).
- **`support_address`**: The email to reach the server admin.

> **Note:** For Gmail, use an app password instead of your regular account password.

The website is now accessible within the local network.

If you want to use it outside the network, you'll need to configure a domain.

### 3. **Start the Containers**

Start the NAS-Website using Docker Compose:

**Windows**

Run the `start-windows.cmd` file.

**MacOS and Linux**

```sh
chmod +x start-linux-macOS.sh
bash start-linux-macOS.sh
```

### 3.5 **Start update system on system-startup**

**Windows**

Add a shortcut to the run-update_flask_server.cmd and move it to the startup folder: 

> Open the start-up folder: 
>    - Press `Windows + R` type `shell:startup` and press `Enter`.

This will start the necessary containers and automatically set up everything, including:
- **Apache + PHP**
- **MySQL Database**
- **Database Initialization** (automatic creation of tables and configurations)

### 4. **Access the Website**

Once the containers are running, open your browser and visit:

```bash
https://your-server-ip:8443
```

You can set up the weather function on the `My Account` page.

That's it! No manual setup required. The website is ready to use.

---

## Setting up SSL with Let's Encrypt

### Step 1: Remove the OpenSSL Self-Signed Certificate

If you previously generated a self-signed certificate using OpenSSL, remove it to avoid conflicts:

```bash
rm -rf /etc/apache2/selfsigned.key
rm -rf /etc/apache2/ssl/selfsigned.crt
```

### Step 2: Install Certbot

Install Certbot with the following command:

```bash
sudo apt update
sudo apt install certbot -y
```

### Step 3: Request a Let's Encrypt SSL Certificate

Request an SSL certificate using Certbot. Replace `yourdomain.com` with your actual domain name:

```bash
sudo certbot certonly --standalone -d yourdomain.com
```

Certbot stores the certificates in the following directory:
- **Certificate**: `/etc/letsencrypt/live/yourdomain.com/fullchain.pem`
- **Private Key**: `/etc/letsencrypt/live/yourdomain.com/privkey.pem`

### Step 4: Configure Your Web Server

#### Nginx Configuration

```nginx
server {
    listen 443 ssl;
    server_name yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # Additional Nginx configuration...
}
```

#### Apache Configuration

```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/html

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    # Additional Apache configurations...
</VirtualHost>
```

### Step 5: Set Up Automatic Renewal

Add a cron job to automatically renew the certificate:

```bash
sudo crontab -e
```

Add the following line:

```bash
0 */12 * * * certbot renew --quiet
```

---

## Setting Up the Android App (Optional)

- Download the `APK` file and install it on your Android device.
- On the first launch, you must provide the server's domain or IP address (saved on your device).

To edit the server's domain or IP address, clear the `App Data` (not just the cache).

---

## Debugging

- If you encounter issues with the website or app after an update, reset all caches and clear your browser cache if you're on a computer.

---

## Known Issues

- The Android app's file upload and download functionality currently doesn't work.

---

## Contributing

If you'd like to contribute, feel free to fork the repository and submit a pull request!

---

## License

This project is licensed under the MIT License.