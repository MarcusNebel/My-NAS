# My NAS

This is the repository for the **My NAS** project, a simple web application for managing user accounts with MySQL, PHP, HTML, CSS, and JavaScript. The website runs inside a Docker container for easy deployment.

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
git clone https://github.com/MarcusNebel/My-NAS.git nas-website
cd nas-website
```

**MacOS**

- Download the release `.ZIP` file and extract it.
- Open a terminal window (ensure you're in the extracted folder).

**Windows**

- Download the release `.ZIP` file and extract it.
- Open a terminal window (ensure you're in the extracted folder).

### 2. **Configuration**

#### **Configure SMTP for Password Reset and IP-adresses**

All SMTP and IP settings are now configured exclusively in the `app/config.json` file. Open this file and edit the corresponding fields:

```json
{
    "flaskServerURL": "https://__SERVER_IP__:8080",
    "update_server_ip": "http://__SERVER_IP__:5000",
    "proxy_update_server_ip": "https://__SERVER_IP__:8081/proxy-update",
    "smtp": {
        "host": "smtp.gmail.com",
        "auth": true,
        "username": "youremail@gmail.com",
        "password": "your_App_Password",
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

- **`__SERVER_IP__`**: Place the server IP (or Domain) here.
- **`host`**: SMTP server address (e.g., `smtp.gmail.com`).
- **`username`**: Your email address.
- **`password`**: Your app password.
- **`port`**: Port for the SMTP server (e.g., `587`).
- **`support_address`**: The email to reach the server admin.

> **Note:** For Gmail, use an app password instead of your regular account password.

The website is now accessible within the local network.

If you want to use it outside the network, you'll need to configure a domain.

### 3. **Start the Containers**

Start My NAS using Docker Compose:

**Windows**

Run the `start-windows.cmd` file.

> To be shure that the update server is running every time you have to put a shortcut of the `run-update-flask-server.cmd` into the Startup folder that you can acces over `Windows + R` and then `shell:startup`. 

**MacOS and Linux**

```sh
chmod +x start-linux-macOS.sh
bash start-linux-macOS.sh
```

This will start the necessary containers and automatically set up everything, including:
- **Apache + PHP**
- **MySQL Database**
- **Database Initialization** (automatic creation of tables and configurations)
- **Update Server to update My NAS automaticly on new Github release**

### 4. **Access the Website**

Once the containers are running, open your browser and visit:

```bash
https://your-server-ip:8443
```

You can set up the weather function on the `My Account` page.

That's it! No manual setup required. The website is ready to use.

---

## Debugging

- If you encounter issues with the website or app after an update, reset all caches and clear your browser cache.

---

## License

This project is licensed under the MIT License.