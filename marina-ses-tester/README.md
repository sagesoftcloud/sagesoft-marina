# Marina SES SMTP Testing Web Application

**🏛️ Maritime Industry Authority - Republic of the Philippines**

A comprehensive PHP web application for testing AWS SES (Simple Email Service) SMTP functionality with Marina's official email templates and government-grade security features.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Features](#features)
3. [Prerequisites](#prerequisites)
4. [Installation Guide](#installation-guide)
5. [Configuration](#configuration)
6. [Usage Guide](#usage-guide)
7. [API Documentation](#api-documentation)
8. [Troubleshooting](#troubleshooting)
9. [Security](#security)
10. [Support](#support)

---

## 🎯 Overview

This web application provides a complete testing environment for Marina's AWS SES email system, featuring:

- **Multi-page PHP application** with modern Tailwind CSS interface
- **Docker containerization** for easy deployment
- **MySQL database** for logging and template management
- **Government-compliant email templates** (OTP, Transaction, Official)
- **Comprehensive logging and monitoring**
- **Security features** with authentication and input validation

**Perfect for POC presentations and client demonstrations.**

---

## ✨ Features

### 🔐 Authentication & Security
- Admin login system with session management
- Input sanitization and validation
- Activity logging for audit trails
- Government-grade security practices

### 📧 Email Testing Capabilities
- **Basic Email Test**: Send simple test emails
- **Template Testing**: Use Marina's official email templates
- **Bulk Email Testing**: Send to multiple recipients
- **Delivery Status Tracking**: Monitor email success/failure rates

### 🎨 Marina Email Templates
- **OTP Verification**: Professional OTP emails with security warnings
- **Transaction Confirmation**: Official transaction receipts
- **Government Communications**: Formal notices and announcements

### 📊 Monitoring & Logging
- Real-time email activity dashboard
- Comprehensive email logs with filtering
- Success/failure statistics
- User activity tracking

### 🛠️ Configuration Management
- Easy SES SMTP credential setup
- Regional endpoint configuration
- Template variable management
- Connection testing tools

---

## 📋 Prerequisites

### System Requirements
- **Docker & Docker Compose** (latest version)
- **4GB RAM minimum** (8GB recommended)
- **2GB free disk space**
- **Internet connection** for AWS SES

### AWS Requirements
- **AWS Account** with SES access
- **SES Domain Verification** (marina.gov.ph)
- **SMTP Credentials** generated in SES
- **Production Access** (for sending to any email address)

---

## 🚀 Installation Guide

### Step 1: Download and Setup

```bash
# Navigate to your project directory
cd /Users/jimbermudez/Documents/MARINA

# The project structure should look like this:
marina-ses-tester/
├── docker-compose.yml
├── Dockerfile
├── README.md
├── config/
├── database/
└── src/
```

### Step 2: Install Docker (if not installed)

**For macOS:**
```bash
# Install Docker Desktop from https://docker.com/products/docker-desktop
# Or using Homebrew:
brew install --cask docker
```

**For Windows:**
```bash
# Download Docker Desktop from https://docker.com/products/docker-desktop
```

**For Linux (Ubuntu):**
```bash
sudo apt update
sudo apt install docker.io docker-compose
sudo systemctl start docker
sudo systemctl enable docker
```

### Step 3: Build and Start the Application

```bash
# Navigate to the project directory
cd marina-ses-tester

# Build and start all services
docker-compose up -d --build

# Check if services are running
docker-compose ps
```

**Expected Output:**
```
Name                    Command               State           Ports
------------------------------------------------------------------------
marina_ses_web          docker-php-entrypoint apac ...   Up      0.0.0.0:8080->80/tcp
marina_ses_db           docker-entrypoint.sh mysqld      Up      0.0.0.0:3306->3306/tcp
marina_ses_phpmyadmin   /docker-entrypoint.sh apac ...   Up      0.0.0.0:8081->80/tcp
```

### Step 4: Install PHP Dependencies

```bash
# Enter the web container
docker-compose exec web bash

# Install Composer dependencies
cd /var/www/html
composer install

# Exit the container
exit
```

### Step 6: Verify Installation

Open your web browser and navigate to:

- **Main Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Database**: localhost:3306

**If login fails with admin/marina123:**
```bash
# Clear browser cache (Cmd+Shift+R / Ctrl+Shift+R) and try again
# Or reset password:
docker-compose exec web php -r "echo password_hash('marina123', PASSWORD_DEFAULT);"
```

---

## ⚙️ Configuration

### Default Login Credentials

```
Username: admin
Password: marina123
```

### Database Connection Details

```
Host: localhost (or db from within containers)
Port: 3306
Database: marina_ses
Username: marina_user
Password: marina_pass123
Root Password: root_pass123
```

### AWS SES Configuration

1. **Login to the application** at http://localhost:8080
2. **Navigate to Settings** page
3. **Enter your SES credentials**:

```
SMTP Host: email-smtp.ap-southeast-1.amazonaws.com
SMTP Port: 587
SMTP Username: [Your SES SMTP Username]
SMTP Password: [Your SES SMTP Password]
From Email: noreply@marina.gov.ph
From Name: Marina Portal
```

### Getting AWS SES Credentials

1. **Login to AWS Console**
2. **Navigate to SES** (Simple Email Service)
3. **Go to SMTP Settings**
4. **Click "Create SMTP Credentials"**
5. **Download the credentials**
6. **Use in the Settings page**

---

## 📖 Usage Guide

### 1. Basic Email Testing

1. **Login** to the application
2. **Click "Basic Test"** from dashboard
3. **Fill in the form**:
   - To Email: recipient@example.com
   - Subject: Your test subject
   - Body: Your email content
   - Check "HTML" if needed
4. **Click "Send Test Email"**
5. **Check results** in the message area

### 2. Template Testing

1. **Navigate to "Template Test"**
2. **Select a Marina template**:
   - OTP Verification
   - Transaction Confirmation
   - Official Communication
3. **Fill in template variables**:
   - OTP_CODE: 123456
   - CUSTOMER_NAME: Juan Dela Cruz
   - TRANSACTION_ID: TXN-123456
   - etc.
4. **Enter recipient email**
5. **Click "Send Template Email"**

### 3. Bulk Email Testing

1. **Go to "Bulk Test"**
2. **Enter multiple email addresses**:
   ```
   user1@example.com
   user2@example.com
   user3@example.com
   ```
3. **Fill in subject and body**
4. **Click "Send Bulk Emails"**
5. **Monitor results** in real-time

### 4. Monitoring and Logs

1. **Visit "Logs" page**
2. **Filter by**:
   - Status (Sent/Failed)
   - Type (Basic/Template/Bulk)
   - Date
3. **View detailed results**
4. **Export logs** (if needed)

---

## 🔧 API Documentation

### Database Schema

#### Users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Email Templates Table
```sql
CREATE TABLE email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('otp', 'transaction', 'official') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body_html TEXT NOT NULL,
    body_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### Email Logs Table
```sql
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    test_type ENUM('basic', 'template', 'bulk') NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    template_id INT NULL,
    status ENUM('sent', 'failed', 'pending') NOT NULL,
    message_id VARCHAR(255) NULL,
    error_message TEXT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### PHP Classes

#### SESMailer Class
```php
// Send single email
$mailer = new SESMailer();
$result = $mailer->sendEmail($to, $subject, $body, $isHTML, $templateId);

// Send bulk emails
$results = $mailer->sendBulkEmails($recipients, $subject, $body, $isHTML);

// Update configuration
$mailer->updateConfig($configArray);
```

#### TemplateManager Class
```php
// Get all templates
$templates = $templateManager->getAllTemplates();

// Process template with variables
$processed = $templateManager->processTemplate($templateId, $variables);

// Get template variables
$variables = $templateManager->getTemplateVariables($templateId);
```

---

## 🔍 Troubleshooting

### Common Issues

#### 1. Docker Services Won't Start
```bash
# Check Docker status
docker --version
docker-compose --version

# Restart Docker service
sudo systemctl restart docker  # Linux
# Or restart Docker Desktop on Mac/Windows

# Rebuild containers
docker-compose down
docker-compose up -d --build
```

#### 2. Database Connection Failed
```bash
# Check database container
docker-compose logs db

# Reset database
docker-compose down -v
docker-compose up -d
```

#### 3. SES Authentication Failed
- **Verify SMTP credentials** in AWS SES console
- **Check region** - use correct regional endpoint
- **Verify domain** - ensure marina.gov.ph is verified
- **Check sandbox mode** - request production access if needed

#### 4. Emails Not Sending
- **Check SES quotas** in AWS console
- **Verify sender email** is verified in SES
- **Check recipient email** format
- **Review error logs** in the application

#### 5. Permission Denied Errors
```bash
# Fix file permissions
sudo chown -R www-data:www-data src/
sudo chmod -R 755 src/
```

#### 6. Login Issues ("Invalid username or password")
**Error**: Cannot login with admin/marina123

**Solution**:
```bash
# Reset admin password
docker-compose exec web php -r "echo password_hash('marina123', PASSWORD_DEFAULT);"
# Copy the output hash and update database
docker-compose exec db mysql -u marina_user -pmarina_pass123 -e "UPDATE users SET password = 'PASTE_HASH_HERE' WHERE username = 'admin';" marina_ses

# Or clear browser cache and try again
# Mac: Cmd+Shift+R, Windows: Ctrl+Shift+R
```

### Debug Mode

Enable debug mode by adding to `config/php.ini`:
```ini
display_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /var/log/php_errors.log
```

### Log Files

- **Application Logs**: Available in the Logs page
- **PHP Errors**: `/var/log/php_errors.log`
- **Apache Logs**: `/var/log/apache2/error.log`
- **MySQL Logs**: Check with `docker-compose logs db`

---

## 🔒 Security

### Security Features Implemented

1. **Authentication System**
   - Password hashing with PHP's `password_hash()`
   - Session management
   - Login attempt monitoring

2. **Input Validation**
   - Email address validation
   - SQL injection prevention with PDO
   - XSS protection with `htmlspecialchars()`

3. **Data Protection**
   - Encrypted database passwords
   - Secure session handling
   - HTTPS ready (configure reverse proxy)

### Security Best Practices

1. **Change Default Passwords**
   ```sql
   -- Update admin password
   UPDATE users SET password = PASSWORD_HASH('new_secure_password', PASSWORD_DEFAULT) WHERE username = 'admin';
   ```

2. **Use HTTPS in Production**
   - Configure SSL certificates
   - Use reverse proxy (nginx/Apache)
   - Force HTTPS redirects

3. **Database Security**
   - Change default database passwords
   - Restrict database access
   - Regular backups

4. **Network Security**
   - Use VPN for remote access
   - Firewall configuration
   - Regular security updates

---

## 📞 Support

### For Technical Issues

1. **Check this documentation** first
2. **Review troubleshooting section**
3. **Check application logs**
4. **Contact Marina IT Department**

### For AWS SES Issues

1. **AWS SES Documentation**: https://docs.aws.amazon.com/ses/
2. **AWS Support Console**: https://console.aws.amazon.com/support/
3. **SES Sending Limits**: Check your AWS console

### Emergency Contacts

- **Marina IT Department**: [Contact Information]
- **AWS Support**: Available through AWS Console
- **System Administrator**: [Contact Information]

---

## 📄 License

This application is developed for the Maritime Industry Authority, Republic of the Philippines. All rights reserved.

---

## 🎯 Success Criteria for POC

**Your POC is ready when:**

✅ **Application loads** at http://localhost:8080  
✅ **Login works** with admin/marina123  
✅ **SES configuration** is set up in Settings  
✅ **Basic email test** sends successfully  
✅ **Marina templates** display correctly  
✅ **Bulk email** processes multiple recipients  
✅ **Logs page** shows email activity  
✅ **Professional appearance** with Marina branding  

---

**🇵🇭 Serving the Filipino Maritime Community with Excellence**

**Maritime Industry Authority**  
Republic of the Philippines

---

**📅 Created**: November 2024  
**👥 For**: Marina IT Department  
**🎯 Purpose**: SES SMTP Testing and POC Demonstration
