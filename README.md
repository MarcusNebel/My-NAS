# NAS-Website

This is the repository for the **NAS-Website** project, a simple web application for managing user accounts with MySQL, PHP, HTML, CSS, and JavaScript. The website runs inside a Docker container for easy deployment.

## Features

- **User Management**: Users can register, log in, and reset their passwords.
- **Database**: The MySQL database contains a table called `accounts` for managing user information.
  - **Columns in the `accounts` table**:
    - `ID`: The account's id.
    - `USERNAME`: The account's username.
    - `PASSWORD`: The user's password (stored encrypted).
    - `EMAIL`: The user's email address.
    - `reset_code`: A code used for password reset.
    - `rememberTOKEN`: A token used for the "Remember me" functionality.
    - `api_key`: For the My NAS app in the [My-NAS-Flutter-App](https://github.com/MarcusNebel/My-NAS-Flutter-App) Repository.
- **Docker-based Deployment**: Easily deploy using Docker and Docker Compose.
- **Automatic Setup**: The entire setup process, including database creation, SSL certification and configurations, is handled automatically when the container starts.
- **Modern UI**: Built with a clean, modern, and responsive design.

---

## Prerequisites

- **Docker** and **Docker Compose** installed on your system.
- **Linux Server** (or local machine with Docker support).
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

- Download the release .ZIP File and extract it
- Open a Terminal Window (make sure you're in the folder you've extracted before)

**Windows** 

- Download the release .ZIP File and extract it
- Open a Terminal Window (make sure you're in the folder you've extracted before)

### 2. **Configuration**

### **Configure SMTP for Password Reset**

Edit `Login/forgot_password.php` and update your SMTP settings:

Open the `forgot_password.php` file with an text-editor to edit the following lines:

```php
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
```

Make sure you use an app password for Gmail (without space).

If you want to use another SMTP Server you have to edit the settings by yourself.

### 3. **Start the Containers**

Simply run the following command to start the NAS-Website using Docker Compose:

**Windows**
```powershell
docker-compose up -d
```

**MacOS and Linux**
```sh
docker compose up -d
```

This will start the necessary containers and automatically set up everything, including:
- **Apache + PHP**
- **MySQL Database**
- **Database Initialization** (automatic creation of tables and configurations)

### 4. **Access the Website**

Once the containers are running, open your browser and visit:

```bash
https://your-server-ip:8443
```

That's it! No manual setup required. The website is ready to use.

---

## Setting up SSL with Let's Encrypt

In this guide, we will go through the process of replacing an existing self-signed OpenSSL certificate with a Let's Encrypt SSL certificate.

### Step 1: Remove the OpenSSL Self-Signed Certificate

If you previously generated a self-signed certificate using OpenSSL, you should remove it before proceeding with Let's Encrypt. This is important to avoid conflicts.

To remove the OpenSSL certificates, delete the following files (or whichever path you saved them to):

```bash
rm -rf /etc/apache2/selfsigned.key
rm -rf /etc/apache2/ssl/selfsigned.crt
```

### Step 2: Install Certbot

Certbot is a tool used to request and manage Let's Encrypt certificates. Install it on your server with the following command:

```bash
sudo apt update
sudo apt install certbot -y
```

### Step 3: Request an SSL Certificate with Let's Encrypt

Next, request the SSL certificate using Certbot. In this example, we will use the `--standalone` method, which runs a temporary web server to complete the domain validation process.

Replace `yourdomain.com` with your actual domain name:

```bash
sudo certbot certonly --standalone -d yourdomain.com
```

Certbot will ask for your email address (for renewal notices) and will validate your domain by temporarily serving a challenge file. After successful validation, Certbot will download the certificate files.

### Step 4: Locate the Let's Encrypt SSL Certificates

After the certificate is issued, Certbot will store the certificates in the following directory:

- **Certificate**: `/etc/letsencrypt/live/yourdomain.com/fullchain.pem`
- **Private Key**: `/etc/letsencrypt/live/yourdomain.com/privkey.pem`

### Step 5: Configure Your Web Server (Nginx or Apache)

#### Option 1: Nginx Configuration

If you're using Nginx, update your Nginx configuration to use the Let's Encrypt certificates. Here’s an example Nginx server block:

```nginx
server {
    listen 443 ssl;
    server_name yourdomain.com;

    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;

    # Other Nginx configuration goes here...
}
```

Make sure to replace `yourdomain.com` with your actual domain name.

#### Option 2: Apache Configuration

If you're using Apache, you can update your configuration to use the Let's Encrypt certificates like so:

```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/html

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem

    # Other Apache configurations...
</VirtualHost>
```

Again, replace `yourdomain.com` with your actual domain.

### Step 6: Restart Your Web Server

After making changes to your web server’s configuration, restart the server to apply the new SSL settings.

For Nginx:

```bash
sudo systemctl restart nginx
```

For Apache:

```bash
sudo systemctl restart apache2
```

### Step 7: Set Up Automatic SSL Certificate Renewal

Let's Encrypt certificates are only valid for 90 days. To avoid interruptions, set up automatic renewal by adding a cron job to run the renewal command periodically.

To open the crontab configuration for the root user, use the following command:

```bash
sudo crontab -e
```

Then, add the following line to automatically renew the certificate every 12 hours:

```bash
0 */12 * * * certbot renew --quiet
```

This will attempt to renew the certificate every 12 hours.

### Step 8: Verify the SSL Configuration

After everything is set up, you can verify that SSL is working by visiting your website with `https://yourdomain.com`. You can also use online tools like [SSL Labs](https://www.ssllabs.com/ssltest/) to check the status of your SSL certificate.


By following these steps, you will successfully replace the self-signed SSL certificate with a free and trusted SSL certificate from Let's Encrypt, and you will have configured automatic renewal.

---

In this `README.md`, the steps are outlined clearly in English. It includes:

- **Removing the OpenSSL self-signed certificate**
- **Installing Certbot**
- **Requesting and issuing an SSL certificate with Let's Encrypt**
- **Configuring Nginx or Apache to use the new SSL certificate**
- **Setting up automatic SSL certificate renewal**

This should help you transition to using Let's Encrypt SSL certificates in your Docker environment! Let me know if you need further adjustments.

---

## Install and setup Android App (optional)

- Download the `APK` file and run it on any android device you want 
- If you open the App first time you have to paste the server's Domain or IP-Adress (it will be saved on your device)

If you want to edit the server's Domain or IP-Adress you have to clear the `App Data` (not only the cache)

---

## Debugging

- If you have some issus with the website or the App after an website update than reset all caches and clear your browser cache if you're on a computer. 

---

## Knowen issues

- Android App file up- and downloadfunction isn't working

---

## Contributing

If you would like to contribute, feel free to fork the repository and submit a pull request!

---

## License

This project is licensed under the MIT License.
