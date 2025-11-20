# 🔄 LAMP/LEMP to Docker Migration Guide

**Complete Guide for Containerizing Traditional LAMP/LEMP Stack Applications**

---

## 📋 **Overview**

This guide helps you migrate traditional LAMP (Linux, Apache, MySQL, PHP) or LEMP (Linux, Nginx, MySQL, PHP) applications to Docker containers for better scalability, consistency, and deployment.

---

## 🎯 **Why Migrate to Docker?**

### **Current Challenges with LAMP/LEMP:**
- ❌ **Environment inconsistencies** across development/staging/production
- ❌ **Difficult scaling** - need to scale entire server
- ❌ **Complex deployments** - manual server configuration
- ❌ **Dependency conflicts** - different applications need different versions
- ❌ **Hard to replicate** - "works on my machine" problems

### **Docker Benefits:**
- ✅ **Consistent environments** - same containers everywhere
- ✅ **Easy scaling** - scale individual services
- ✅ **Simple deployments** - just run docker-compose
- ✅ **Isolated dependencies** - each container has its own environment
- ✅ **Reproducible** - identical setup every time

---

## 🏗️ **Architecture Comparison**

### **Traditional LAMP/LEMP Stack:**
```
Single Server (Physical/VM):
├── Linux OS
├── Apache/Nginx Web Server
├── MySQL/MariaDB Database
├── PHP Runtime
├── Your Application Code
└── All dependencies installed globally
```

### **Containerized Architecture:**
```
Docker Host:
├── Web Container (Apache/Nginx + PHP + Your Code)
├── Database Container (MySQL/MariaDB)
├── Cache Container (Redis) - Optional
├── Admin Container (phpMyAdmin) - Optional
└── Each container isolated and scalable
```

---

## 🎯 **Migration Strategies**

### **Strategy 1: Direct Translation (Recommended for Beginners)**
**Approach**: Replace each LAMP component with equivalent Docker container
**Timeline**: 3-5 days
**Complexity**: Low
**Best for**: Simple applications, learning Docker

### **Strategy 2: Microservices (Advanced)**
**Approach**: Split application into multiple specialized containers
**Timeline**: 1-2 weeks
**Complexity**: High
**Best for**: Complex applications, high-traffic sites

### **Strategy 3: Hybrid Approach**
**Approach**: Containerize gradually, starting with database
**Timeline**: 1-2 weeks
**Complexity**: Medium
**Best for**: Large applications, minimal downtime requirements

---

## 🚀 **Step-by-Step Migration Process**

### **Phase 1: Assessment and Planning (1 day)**

#### **1.1 Analyze Current Setup**
```bash
# Check PHP version
php -v

# Check web server
apache2 -v  # or nginx -v

# Check database
mysql --version

# List PHP extensions
php -m

# Check current configuration
cat /etc/apache2/apache2.conf  # or /etc/nginx/nginx.conf
cat /etc/mysql/my.cnf
```

#### **1.2 Document Requirements**
Create a checklist:
- [ ] PHP version and extensions needed
- [ ] Web server configuration (virtual hosts, SSL, etc.)
- [ ] Database version and configuration
- [ ] File upload limits and permissions
- [ ] Cron jobs and background processes
- [ ] External dependencies (Redis, Elasticsearch, etc.)

#### **1.3 Backup Everything**
```bash
# Backup database
mysqldump -u root -p --all-databases > full_backup.sql

# Backup application files
tar -czf app_backup.tar.gz /var/www/html/

# Backup configurations
cp -r /etc/apache2/ apache2_backup/  # or /etc/nginx/
cp -r /etc/mysql/ mysql_backup/
```

### **Phase 2: Create Docker Environment (2 days)**

#### **2.1 Create Project Structure**
```bash
mkdir my-app-docker
cd my-app-docker

# Create directory structure
mkdir -p src config database scripts docs
```

#### **2.2 Create docker-compose.yml**
```yaml
services:
  # Web Server + PHP
  web:
    build: .
    container_name: myapp_web
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./src:/var/www/html
      - ./config/apache:/etc/apache2/sites-available
      - ./config/php/php.ini:/usr/local/etc/php/php.ini
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=myapp_db
      - DB_USER=myapp_user
      - DB_PASS=myapp_pass
    networks:
      - myapp_network

  # Database
  db:
    image: mysql:8.0
    container_name: myapp_db
    restart: always
    environment:
      MYSQL_DATABASE: myapp_db
      MYSQL_USER: myapp_user
      MYSQL_PASSWORD: myapp_pass
      MYSQL_ROOT_PASSWORD: root_pass
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/init.sql:/docker-entrypoint-initdb.d/init.sql
      - ./config/mysql/my.cnf:/etc/mysql/conf.d/custom.cnf
    networks:
      - myapp_network

  # Database Administration (Optional)
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: myapp_phpmyadmin
    restart: always
    ports:
      - "8080:80"
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: root_pass
    depends_on:
      - db
    networks:
      - myapp_network

  # Redis Cache (Optional)
  redis:
    image: redis:alpine
    container_name: myapp_redis
    restart: always
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
    networks:
      - myapp_network

volumes:
  mysql_data:
  redis_data:

networks:
  myapp_network:
    driver: bridge
```

#### **2.3 Create Dockerfile**
```dockerfile
# Choose base image based on your needs
FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nano \
    cron

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (customize based on your needs)
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache modules
RUN a2enmod rewrite ssl headers

# Set working directory
WORKDIR /var/www/html

# Copy application code
COPY src/ /var/www/html/

# Copy Apache configuration
COPY config/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port
EXPOSE 80 443

# Start Apache
CMD ["apache2-foreground"]
```

### **Phase 3: Configuration Migration (1 day)**

#### **3.1 Apache Configuration**
```bash
# Create config/apache/000-default.conf
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/error.log
    CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

#### **3.2 PHP Configuration**
```ini
# Create config/php/php.ini
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
memory_limit = 512M
display_errors = On
error_reporting = E_ALL
date.timezone = Asia/Manila

# Add your custom PHP settings here
```

#### **3.3 MySQL Configuration**
```ini
# Create config/mysql/my.cnf
[mysqld]
innodb_buffer_pool_size = 256M
max_connections = 200
query_cache_size = 64M
tmp_table_size = 64M
max_heap_table_size = 64M

# Add your custom MySQL settings here
```

### **Phase 4: Data Migration (1 day)**

#### **4.1 Migrate Database**
```bash
# Copy your database backup
cp /path/to/full_backup.sql ./database/init.sql

# Or create a more specific init script
cat > ./database/init.sql << 'EOF'
-- Create database and user
CREATE DATABASE IF NOT EXISTS myapp_db;
CREATE USER IF NOT EXISTS 'myapp_user'@'%' IDENTIFIED BY 'myapp_pass';
GRANT ALL PRIVILEGES ON myapp_db.* TO 'myapp_user'@'%';
FLUSH PRIVILEGES;

-- Use the database
USE myapp_db;

-- Your existing database structure and data here
-- (paste content from your mysqldump)
EOF
```

#### **4.2 Migrate Application Files**
```bash
# Copy your application files
cp -r /var/www/html/* ./src/

# Update database connection in your application
# Example for PHP applications:
sed -i 's/localhost/db/g' ./src/config/database.php
sed -i 's/127.0.0.1/db/g' ./src/wp-config.php  # For WordPress
```

### **Phase 5: Testing and Validation (1 day)**

#### **5.1 Start the Environment**
```bash
# Build and start containers
docker-compose up -d --build

# Check container status
docker-compose ps

# View logs
docker-compose logs web
docker-compose logs db
```

#### **5.2 Test Functionality**
```bash
# Test web access
curl http://localhost

# Test database connection
docker-compose exec db mysql -u myapp_user -p myapp_db

# Test PHP functionality
docker-compose exec web php -v
docker-compose exec web php -m  # Check extensions
```

#### **5.3 Performance Testing**
```bash
# Test application performance
ab -n 100 -c 10 http://localhost/

# Monitor resource usage
docker stats
```

---

## 📋 **Common Application Patterns**

### **WordPress Migration**
```yaml
services:
  wordpress:
    image: wordpress:latest
    ports:
      - "80:80"
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wp_user
      WORDPRESS_DB_PASSWORD: wp_pass
      WORDPRESS_DB_NAME: wordpress
    volumes:
      - ./wp-content:/var/www/html/wp-content
      - ./uploads.ini:/usr/local/etc/php/conf.d/uploads.ini
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wp_user
      MYSQL_PASSWORD: wp_pass
      MYSQL_ROOT_PASSWORD: root_pass
    volumes:
      - mysql_data:/var/lib/mysql
      - ./wordpress.sql:/docker-entrypoint-initdb.d/wordpress.sql
```

### **Laravel Application**
```yaml
services:
  app:
    build: .
    ports:
      - "80:80"
    volumes:
      - ./laravel-app:/var/www/html
    environment:
      - APP_ENV=production
      - DB_CONNECTION=mysql
      - DB_HOST=db
      - DB_DATABASE=laravel
      - DB_USERNAME=laravel_user
      - DB_PASSWORD=laravel_pass
      - REDIS_HOST=redis
    depends_on:
      - db
      - redis

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: laravel
      MYSQL_USER: laravel_user
      MYSQL_PASSWORD: laravel_pass
      MYSQL_ROOT_PASSWORD: root_pass
    volumes:
      - mysql_data:/var/lib/mysql

  redis:
    image: redis:alpine
    volumes:
      - redis_data:/data
```

### **Custom PHP Application**
```yaml
services:
  web:
    build: .
    ports:
      - "80:80"
    volumes:
      - ./app:/var/www/html
      - ./config/apache/vhost.conf:/etc/apache2/sites-available/000-default.conf
    environment:
      - DB_HOST=db
      - DB_NAME=myapp
      - DB_USER=myapp_user
      - DB_PASS=myapp_pass
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: myapp
      MYSQL_USER: myapp_user
      MYSQL_PASSWORD: myapp_pass
      MYSQL_ROOT_PASSWORD: root_pass
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/schema.sql:/docker-entrypoint-initdb.d/init.sql
```

---

## 🔧 **Advanced Configurations**

### **Nginx + PHP-FPM Setup**
```yaml
services:
  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./app:/var/www/html
      - ./config/nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./config/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php-fpm

  php-fpm:
    build:
      context: .
      dockerfile: Dockerfile.php-fpm
    volumes:
      - ./app:/var/www/html
      - ./config/php/php.ini:/usr/local/etc/php/php.ini
    environment:
      - DB_HOST=db
    depends_on:
      - db

  db:
    image: mysql:8.0
    # ... database configuration
```

### **SSL/HTTPS Configuration**
```yaml
services:
  web:
    build: .
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./ssl:/etc/ssl/certs
      - ./config/apache/ssl.conf:/etc/apache2/sites-available/ssl.conf
    environment:
      - APACHE_SERVER_NAME=yourdomain.com
```

### **Multi-Environment Setup**
```bash
# Development
docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d

# Staging
docker-compose -f docker-compose.yml -f docker-compose.staging.yml up -d

# Production
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

---

## 🚨 **Troubleshooting Common Issues**

### **Database Connection Issues**
```bash
# Check if database container is running
docker-compose ps db

# Check database logs
docker-compose logs db

# Test connection from web container
docker-compose exec web ping db
docker-compose exec web telnet db 3306

# Common fixes:
# 1. Update application config to use 'db' as hostname
# 2. Check environment variables
# 3. Verify database credentials
```

### **File Permission Issues**
```bash
# Fix file permissions
docker-compose exec web chown -R www-data:www-data /var/www/html
docker-compose exec web chmod -R 755 /var/www/html

# For development, you might need:
docker-compose exec web chown -R 1000:1000 /var/www/html
```

### **PHP Extension Missing**
```dockerfile
# Add to Dockerfile
RUN docker-php-ext-install extension_name

# For extensions requiring additional packages:
RUN apt-get update && apt-get install -y libpng-dev \
    && docker-php-ext-install gd
```

### **Performance Issues**
```yaml
# Increase PHP memory and execution time
services:
  web:
    environment:
      - PHP_MEMORY_LIMIT=512M
      - PHP_MAX_EXECUTION_TIME=300
```

---

## 📊 **Performance Optimization**

### **Database Optimization**
```ini
# config/mysql/my.cnf
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_size = 128M
max_connections = 500
```

### **PHP Optimization**
```ini
# config/php/php.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

### **Web Server Optimization**
```apache
# Apache configuration
LoadModule deflate_module modules/mod_deflate.so
LoadModule expires_module modules/mod_expires.so

<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

## 🔒 **Security Considerations**

### **Container Security**
```yaml
services:
  web:
    # Run as non-root user
    user: "1000:1000"
    
    # Read-only root filesystem
    read_only: true
    
    # Limit resources
    deploy:
      resources:
        limits:
          memory: 512M
          cpus: '0.5'
```

### **Network Security**
```yaml
# Expose only necessary ports
services:
  db:
    # Don't expose database port externally
    # ports:
    #   - "3306:3306"  # Remove this line
    expose:
      - "3306"  # Only accessible from other containers
```

### **Environment Variables**
```bash
# Use .env file for sensitive data
echo "DB_PASSWORD=secure_password" > .env
echo ".env" >> .gitignore
```

---

## 📋 **Migration Checklist**

### **Pre-Migration**
- [ ] **Backup** all data and configurations
- [ ] **Document** current setup and requirements
- [ ] **Test** backup restoration process
- [ ] **Plan** rollback strategy
- [ ] **Schedule** maintenance window

### **During Migration**
- [ ] **Create** Docker environment
- [ ] **Migrate** database schema and data
- [ ] **Copy** application files
- [ ] **Update** configuration files
- [ ] **Test** all functionality

### **Post-Migration**
- [ ] **Performance** testing
- [ ] **Security** review
- [ ] **Monitoring** setup
- [ ] **Backup** strategy implementation
- [ ] **Documentation** update
- [ ] **Team** training

---

## 🎯 **Best Practices**

### **Development Workflow**
```bash
# Use separate environments
docker-compose -f docker-compose.dev.yml up -d    # Development
docker-compose -f docker-compose.staging.yml up -d # Staging
docker-compose -f docker-compose.prod.yml up -d   # Production
```

### **Version Control**
```gitignore
# .gitignore
.env
data/
logs/
*.log
.DS_Store
```

### **Monitoring and Logging**
```yaml
services:
  web:
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"
```

### **Backup Strategy**
```bash
# Automated backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)

# Backup database
docker-compose exec -T db mysqldump -u root -p$MYSQL_ROOT_PASSWORD --all-databases > backup_${DATE}.sql

# Backup volumes
docker run --rm -v myapp_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql_data_${DATE}.tar.gz -C /data .
```

---

## 🚀 **Deployment Strategies**

### **Blue-Green Deployment**
```bash
# Deploy new version alongside old
docker-compose -f docker-compose.blue.yml up -d
# Test new version
# Switch traffic to new version
# Remove old version
docker-compose -f docker-compose.green.yml down
```

### **Rolling Updates**
```bash
# Update one service at a time
docker-compose up -d --no-deps web
docker-compose up -d --no-deps db
```

### **CI/CD Integration**
```yaml
# .github/workflows/deploy.yml
name: Deploy
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Deploy to server
        run: |
          docker-compose pull
          docker-compose up -d --build
```

---

## 📞 **Support and Resources**

### **Useful Commands**
```bash
# Container management
docker-compose ps                    # List containers
docker-compose logs service_name     # View logs
docker-compose exec service_name bash # Enter container
docker-compose restart service_name  # Restart service

# Debugging
docker-compose config               # Validate compose file
docker system df                    # Check disk usage
docker system prune                 # Clean up unused resources
```

### **Learning Resources**
- **Docker Documentation**: https://docs.docker.com/
- **Docker Compose Reference**: https://docs.docker.com/compose/
- **Best Practices**: https://docs.docker.com/develop/best-practices/

### **Community Support**
- **Docker Community**: https://forums.docker.com/
- **Stack Overflow**: Tag your questions with `docker` and `docker-compose`
- **GitHub**: Many example repositories available

---

## 🎉 **Conclusion**

Migrating from LAMP/LEMP to Docker provides significant benefits in terms of consistency, scalability, and deployment ease. While the initial setup requires some learning, the long-term benefits make it worthwhile for most applications.

**Key Success Factors:**
- ✅ **Start simple** - begin with direct translation approach
- ✅ **Test thoroughly** - validate all functionality works
- ✅ **Document everything** - maintain clear documentation
- ✅ **Plan for rollback** - always have a backup strategy
- ✅ **Monitor performance** - ensure Docker setup meets requirements

**Remember**: Docker is a tool to solve problems, not create them. If your current LAMP/LEMP setup works well and you don't face the challenges Docker solves, migration might not be necessary.

---

**🐳 Happy Containerizing!**

---

**📅 Created**: November 2024  
**👥 For**: Developers migrating from traditional LAMP/LEMP stacks  
**🎯 Purpose**: Complete migration guide and reference
