# 🐳 Docker Setup Guide for Marina SES Tester

**Complete step-by-step guide for running the Marina SES Testing application using Docker**

---

## 📋 Prerequisites

### System Requirements
- **Operating System**: macOS, Windows 10/11, or Linux
- **RAM**: 4GB minimum (8GB recommended)
- **Disk Space**: 2GB free space
- **Internet**: Required for Docker images and AWS SES

---

## 🔧 Docker Installation

### For macOS

#### Option 1: Docker Desktop (Recommended)
```bash
# Download from https://docker.com/products/docker-desktop
# Or install via Homebrew:
brew install --cask docker
```

#### Option 2: Command Line Installation
```bash
# Install Homebrew if not installed
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Install Docker
brew install docker docker-compose

# Start Docker service
open /Applications/Docker.app
```

### For Windows

#### Docker Desktop Installation
1. **Download** Docker Desktop from https://docker.com/products/docker-desktop
2. **Run the installer** and follow the setup wizard
3. **Restart** your computer when prompted
4. **Launch** Docker Desktop from Start menu
5. **Verify** installation in Command Prompt:
   ```cmd
   docker --version
   docker-compose --version
   ```

### For Linux (Ubuntu/Debian)

```bash
# Update package index
sudo apt update

# Install required packages
sudo apt install apt-transport-https ca-certificates curl gnupg lsb-release

# Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Add Docker repository
echo "deb [arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine
sudo apt update
sudo apt install docker-ce docker-ce-cli containerd.io docker-compose

# Start and enable Docker
sudo systemctl start docker
sudo systemctl enable docker

# Add user to docker group (optional, to run without sudo)
sudo usermod -aG docker $USER
newgrp docker
```

---

## 🚀 Application Setup

### Step 1: Navigate to Project Directory

```bash
# Open Terminal/Command Prompt
# Navigate to the Marina project folder
cd /Users/jimbermudez/Documents/MARINA/marina-ses-tester

# Verify you're in the correct directory
ls -la
# You should see: docker-compose.yml, Dockerfile, src/, config/, database/
```

### Step 2: Understand the Docker Architecture

```
Marina SES Tester Docker Setup:
┌─────────────────────────────────────────┐
│  Docker Compose Network                 │
│                                         │
│  ┌─────────────┐  ┌─────────────┐      │
│  │   Web App   │  │   MySQL     │      │
│  │   PHP 8.2   │  │   Database  │      │
│  │   Apache    │  │             │      │
│  │   Port 8080 │  │   Port 3306 │      │
│  └─────────────┘  └─────────────┘      │
│                                         │
│  ┌─────────────┐                       │
│  │ phpMyAdmin  │                       │
│  │ Port 8081   │                       │
│  └─────────────┘                       │
└─────────────────────────────────────────┘
```

### Step 3: Build and Start Services

```bash
# Build and start all services in detached mode
docker-compose up -d --build

# This command will:
# 1. Build the PHP web application container
# 2. Download and start MySQL 8.0 container
# 3. Download and start phpMyAdmin container
# 4. Create a network for all services to communicate
# 5. Initialize the database with Marina's schema
```

**Expected Output:**
```
Creating network "marina-ses-tester_marina_network" with driver "bridge"
Creating volume "marina-ses-tester_mysql_data" with default driver
Building web
Step 1/10 : FROM php:8.2-apache
...
Successfully built abc123def456
Successfully tagged marina-ses-tester_web:latest
Creating marina_ses_db ... done
Creating marina_ses_web ... done
Creating marina_ses_phpmyadmin ... done
```

### Step 4: Verify Services are Running

```bash
# Check status of all containers
docker-compose ps

# Expected output:
#        Name                    Command               State           Ports
# ------------------------------------------------------------------------
# marina_ses_web          docker-php-entrypoint apac ...   Up      0.0.0.0:8080->80/tcp
# marina_ses_db           docker-entrypoint.sh mysqld      Up      0.0.0.0:3306->3306/tcp
# marina_ses_phpmyadmin   /docker-entrypoint.sh apac ...   Up      0.0.0.0:8081->80/tcp
```

### Step 5: Install PHP Dependencies

```bash
# Enter the web container
docker-compose exec web bash

# You're now inside the container
# Install Composer dependencies
cd /var/www/html
composer install

# Exit the container
exit
```

### Step 6: Access the Application

Open your web browser and navigate to:

- **🌐 Main Application**: http://localhost:8080
- **🗄️ Database Admin**: http://localhost:8081
- **📊 Database Direct**: localhost:3306

---

## 🔑 Default Access Credentials

### Application Login
```
URL: http://localhost:8080
Username: admin
Password: marina123
```

### Database Access (phpMyAdmin)
```
URL: http://localhost:8081
Server: db
Username: root
Password: root_pass123

OR

Username: marina_user
Password: marina_pass123
```

### Direct Database Connection
```
Host: localhost
Port: 3306
Database: marina_ses
Username: marina_user
Password: marina_pass123
```

---

## 🛠️ Docker Commands Reference

### Basic Operations

```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart all services
docker-compose restart

# View logs
docker-compose logs

# View logs for specific service
docker-compose logs web
docker-compose logs db
docker-compose logs phpmyadmin
```

### Development Commands

```bash
# Rebuild containers (after code changes)
docker-compose up -d --build

# Enter web container for debugging
docker-compose exec web bash

# Enter database container
docker-compose exec db mysql -u root -p

# View real-time logs
docker-compose logs -f web
```

### Maintenance Commands

```bash
# Remove all containers and volumes (CAUTION: This deletes all data)
docker-compose down -v

# Remove unused Docker images
docker image prune

# Remove all stopped containers
docker container prune

# View Docker disk usage
docker system df
```

---

## 🔧 Troubleshooting

### Issue 1: Port Already in Use

**Error**: `Port 8080 is already allocated`

**Solution**:
```bash
# Check what's using the port
lsof -i :8080  # macOS/Linux
netstat -ano | findstr :8080  # Windows

# Kill the process or change port in docker-compose.yml
# Edit docker-compose.yml and change "8080:80" to "8090:80"
```

### Issue 2: Docker Not Starting

**Error**: `Cannot connect to the Docker daemon`

**Solution**:
```bash
# macOS: Start Docker Desktop
open /Applications/Docker.app

# Linux: Start Docker service
sudo systemctl start docker

# Windows: Start Docker Desktop from Start menu
```

### Issue 3: Database Connection Failed

**Error**: `Connection refused` or `Access denied`

**Solution**:
```bash
# Check if database container is running
docker-compose ps

# Restart database service
docker-compose restart db

# Check database logs
docker-compose logs db

# Reset database (CAUTION: Deletes all data)
docker-compose down -v
docker-compose up -d
```

### Issue 4: Web Application Not Loading

**Error**: `This site can't be reached`

**Solution**:
```bash
# Check web container status
docker-compose logs web

# Restart web service
docker-compose restart web

# Rebuild web container
docker-compose up -d --build web
```

### Issue 5: Permission Denied

**Error**: `Permission denied` when accessing files

**Solution**:
```bash
# Fix file permissions (Linux/macOS)
sudo chown -R $USER:$USER .
chmod -R 755 src/

# Or run Docker with sudo (not recommended)
sudo docker-compose up -d
```

### Issue 6: Login Failed

**Error**: "Invalid username or password" with admin/marina123

**Solution**:
```bash
# Method 1: Reset password hash
docker-compose exec web php -r "echo password_hash('marina123', PASSWORD_DEFAULT);"
# Copy the hash output, then:
docker-compose exec db mysql -u marina_user -pmarina_pass123 -e "UPDATE users SET password = 'PASTE_HASH_HERE' WHERE username = 'admin';" marina_ses

# Method 2: Clear browser cache
# Mac: Cmd+Shift+R
# Windows: Ctrl+Shift+R
# Then try login again

# Method 3: Test login via command line
docker-compose exec web php -r "
require_once 'classes/Auth.php';
\$auth = new Auth();
echo \$auth->login('admin', 'marina123') ? 'SUCCESS' : 'FAILED';
"
```

---

## 📊 Monitoring and Logs

### View Application Logs

```bash
# All services logs
docker-compose logs

# Specific service logs
docker-compose logs web
docker-compose logs db
docker-compose logs phpmyadmin

# Follow logs in real-time
docker-compose logs -f web

# Last 50 lines of logs
docker-compose logs --tail=50 web
```

### Monitor Resource Usage

```bash
# View container resource usage
docker stats

# View disk usage
docker system df

# View detailed container information
docker-compose exec web df -h
```

---

## 🔄 Updates and Maintenance

### Updating the Application

```bash
# Pull latest changes (if using Git)
git pull origin main

# Rebuild containers with latest changes
docker-compose down
docker-compose up -d --build

# Update PHP dependencies
docker-compose exec web composer update
```

### Database Backup

```bash
# Backup database
docker-compose exec db mysqldump -u root -proot_pass123 marina_ses > backup.sql

# Restore database
docker-compose exec -T db mysql -u root -proot_pass123 marina_ses < backup.sql
```

### Cleaning Up

```bash
# Remove stopped containers
docker container prune

# Remove unused images
docker image prune

# Remove unused volumes (CAUTION: This deletes data)
docker volume prune

# Complete cleanup (CAUTION: This removes everything)
docker system prune -a
```

---

## 🎯 Quick Start Checklist

**For your POC presentation, ensure:**

- [ ] **Docker is installed** and running
- [ ] **All containers are up**: `docker-compose ps` shows 3 running services
- [ ] **Web app loads**: http://localhost:8080 shows login page
- [ ] **Login works**: admin/marina123 credentials work (clear cache if needed)
- [ ] **Database accessible**: http://localhost:8081 shows phpMyAdmin
- [ ] **SES configured**: Settings page has your AWS credentials
- [ ] **Test email works**: Basic test sends successfully

---

## 📞 Support

### If You Need Help

1. **Check the logs**: `docker-compose logs`
2. **Restart services**: `docker-compose restart`
3. **Rebuild containers**: `docker-compose up -d --build`
4. **Check this guide** for common issues
5. **Contact Marina IT** for assistance

### Useful Resources

- **Docker Documentation**: https://docs.docker.com/
- **Docker Compose Reference**: https://docs.docker.com/compose/
- **PHP Official Images**: https://hub.docker.com/_/php
- **MySQL Official Images**: https://hub.docker.com/_/mysql

---

**🐳 Docker makes deployment simple and consistent across all environments!**

**Maritime Industry Authority**  
Republic of the Philippines
