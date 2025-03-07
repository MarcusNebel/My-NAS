# NAS-Website

This is the repository for the **NAS-Website** project, a simple web application for managing user accounts with MySQL, PHP, HTML, CSS, and JavaScript. The website runs inside a Docker container for easy deployment.

## Features

- **User Management**: Users can register, log in, and reset their passwords.
- **Database**: The MySQL database contains a table called `accounts` for managing user information.
  - **Columns in the `accounts` table**:
    - `USERNAME`: The account's username.
    - `PASSWORD`: The user's password (stored encrypted).
    - `EMAIL`: The user's email address.
    - `reset_code`: A code used for password reset.
    - `rememberTOKEN`: A token used for the "Remember me" functionality.
- **Docker-based Deployment**: Easily deploy using Docker and Docker Compose.
- **Automatic Setup**: The entire setup process, including database creation and configurations, is handled automatically when the container starts.
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

Make sure you use an app password for Gmail.

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
http://your-server-ip:8443
```

That's it! No manual setup required. The website is ready to use.

---

## SSL with Let's Encrypt (Optional)

For secure connections, you can set up SSL with Let's Encrypt using a reverse proxy like Traefik or Nginx Proxy Manager.

Install Certbot:
```bash
sudo apt install certbot -y
```

Request an SSL certificate:
```bash
sudo certbot certonly --standalone -d yourdomain.com
```

---

## Contributing

If you would like to contribute, feel free to fork the repository and submit a pull request!

---

## License

This project is licensed under the MIT License.
