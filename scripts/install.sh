#!/bin/bash

# JA-Platform Auto-Installer
# Supporting: Ubuntu/Debian & CentOS/AlmaLinux/RHEL

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Ensure script is run as root or with sudo
if [ "$EUID" -ne 0 ]; then
    echo -e "${YELLOW}This script requires sudo privileges. Refreshing sudo session...${NC}"
    sudo -v
    # Keep sudo alive during the process
    while true; do sudo -n true; sleep 60; kill -0 "$$" || exit; done 2>/dev/null &
fi

# Error Handling Function
trap 'on_error $LINENO' ERR
on_error() {
    echo -e "\n${RED}==================================================${NC}"
    echo -e "${RED}  CRITICAL ERROR: Installation Stalled            ${NC}"
    echo -e "${RED}  Failed at line $1                               ${NC}"
    echo -e "${RED}==================================================${NC}"
    echo -e "${YELLOW}Common Solutions:${NC}"
    echo -e "1. Check internet connection / firewall."
    echo -e "2. Mirrors might be down. Try again in 5 minutes."
    echo -e "3. Manual fix: sudo apt-get install -f"
    echo -e "\n${RED}NUCLEAR OPTION (If all fails):${NC}"
    echo -e "If this server has many legacy conflicts, it is highly recommended to:"
    echo -e "${GREEN}-> Reinstall a FRESH OS (Ubuntu 24.04 LTS or AlmaLinux 9 recommended)${NC}"
    echo -e "${GREEN}-> Use a clean, empty server to avoid dependency hell.${NC}"
    exit 1
}

# DNS Fallback Function
try_alternative_dns() {
    echo -e "${YELLOW}Resolving issues detected. Trying alternative DNS (8.8.8.8)...${NC}"
    echo "nameserver 8.8.8.8" | sudo tee /etc/resolv.conf > /dev/null
}

# Mirror Switcher for Ubuntu/Debian
switch_mirror() {
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        echo -e "${YELLOW}Mirror issue detected. Switching to main global mirror...${NC}"
        sudo sed -i 's/[a-z]\{2\}\.archive\.ubuntu\.com/archive.ubuntu.com/g' /etc/apt/sources.list
        sudo apt-get update -y
    fi
}

# Retry Command Function
retry_cmd() {
    local n=1
    local max=3
    local delay=3
    while true; do
        if "$@"; then
            break
        else
            if [[ $n -lt $max ]]; then
                ((n++))
                echo -e "${YELLOW}Command failed. Attempt $n/$max. Retrying in ${delay}s...${NC}"
                
                # Attempt alternative paths on second failure
                if [[ $n -eq 2 ]]; then
                    try_alternative_dns || true
                fi
                if [[ $n -eq 3 ]]; then
                    switch_mirror || true
                fi
                
                sleep $delay
            else
                echo -e "${RED}Command failed after $max attempts and alternative paths.${NC}"
                return 1
            fi
        fi
    done
}

echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}       JA-Platform Auto-Installer v2.5            ${NC}"
echo -e "${BLUE}==================================================${NC}"

# 0. Connectivity Check
echo -e "${YELLOW}Checking internet connectivity...${NC}"
if ! ping -c 1 8.8.8.8 &> /dev/null; then
    echo -e "${RED}Error: No internet connection detected.${NC}"
    echo -e "${YELLOW}Tip: Check your proxy or DNS settings (/etc/resolv.conf).${NC}"
    exit 1
fi
echo -e "${GREEN}Internet: OK${NC}"

# 1. OS Detection
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
    VER=$VERSION_ID
else
    echo -e "${RED}Error: Cannot detect OS. Please install manually.${NC}"
    exit 1
fi

echo -e "${GREEN}Detected OS: $OS $VER${NC}"

# 2. Resource Check
echo -e "${YELLOW}Auditing system resources...${NC}"
TOTAL_RAM=$(free -m | awk '/^Mem:/{print $2}')
if [ "$TOTAL_RAM" -lt 1000 ]; then
    echo -e "${RED}Warning: Minimal 1GB RAM recommended. Current: ${TOTAL_RAM}MB${NC}"
    # Continue anyway but with warning
fi
echo -e "RAM OK: ${TOTAL_RAM}MB"

# 3. Cleanup & Update
echo -e "${YELLOW}Cleaning up and updating package manager...${NC}"
if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
    retry_cmd sudo apt-get update -y
    sudo apt-get autoremove -y
elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
    sudo dnf clean all
    retry_cmd sudo dnf check-update || true
fi

# 4. Dependency Management
install_pkg() {
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        retry_cmd sudo apt-get install -y "$@"
    elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        retry_cmd sudo dnf install -y "$@"
    fi
}

# PHP Check & Install
echo -e "${YELLOW}Checking PHP requirements...${NC}"
if ! command -v php &> /dev/null || [[ $(php -r "echo PHP_VERSION_ID;") -lt 80200 ]]; then
    echo -e "Installing/Upgrading PHP 8.3..."
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        retry_cmd sudo apt-get install -y software-properties-common
        retry_cmd sudo add-apt-repository -y ppa:ondrej/php
        retry_cmd sudo apt-get update
        install_pkg php8.3 php8.3-cli php8.3-common php8.3-pgsql php8.3-bcmath php8.3-curl php8.3-gd php8.3-intl php8.3-xml php8.3-zip php8.3-mbstring php8.3-redis nginx supervisor
    elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        retry_cmd sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-$(echo $VER | cut -d. -f1).rpm
        sudo dnf module reset php -y
        sudo dnf module enable php:remi-8.3 -y
        install_pkg php php-cli php-common php-pgsql php-bcmath php-curl php-gd php-intl php-xml php-zip php-mbstring php-redis nginx supervisor
    fi
else
    echo -e "${GREEN}PHP is up to date: $(php -v | head -n 1)${NC}"
fi

# PostgreSQL Check & Install
if ! command -v psql &> /dev/null; then
    echo -e "${YELLOW}Installing PostgreSQL...${NC}"
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        install_pkg postgresql postgresql-contrib
    elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        install_pkg postgresql-server postgresql-contrib
        sudo postgresql-setup --initdb || true
        sudo systemctl enable postgresql
        sudo systemctl start postgresql
    fi
else
    echo -e "${GREEN}PostgreSQL is already installed.${NC}"
fi

# Redis Check & Install
if ! command -v redis-server &> /dev/null; then
    echo -e "${YELLOW}Installing Redis...${NC}"
    install_pkg redis-server || install_pkg redis
    sudo systemctl enable redis-server || sudo systemctl enable redis
    sudo systemctl start redis-server || sudo systemctl start redis
fi

# Node.js Check & Install
if ! command -v node &> /dev/null || [[ $(node -v | cut -d. -f1 | sed 's/v//') -lt 22 ]]; then
    echo -e "${YELLOW}Installing Node.js 22...${NC}"
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        retry_cmd curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
        install_pkg nodejs
    elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        retry_cmd curl -fsSL https://rpm.nodesource.com/setup_22.x | sudo bash -
        install_pkg nodejs
    fi
fi

# Composer Check & Install
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}Installing Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
fi

# 5. Application Setup
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}       Setting up JA-Platform Application         ${NC}"
echo -e "${BLUE}==================================================${NC}"

# Backend Setup
cd backend
composer install --no-interaction --optimize-autoloader
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi
cd ..

# Frontend Setup
cd frontend
npm install
npm run build
cd ..

# Permissions
echo -e "${YELLOW}Setting up permissions...${NC}"
sudo chmod -R 775 backend/storage backend/bootstrap/cache
sudo chown -R $USER:www-data backend/storage backend/bootstrap/cache || true

# 6. Database & Redis Configuration
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}       Configuring Database & Redis               ${NC}"
echo -e "${BLUE}==================================================${NC}"

# Read DB values from .env (populated by earlier steps)
DB_CONN=$(grep DB_CONNECTION backend/.env | cut -d= -f2)
DB_NAME=$(grep DB_DATABASE backend/.env | cut -d= -f2)
DB_USER=$(grep DB_USERNAME backend/.env | cut -d= -f2)
DB_PASS=$(grep DB_PASSWORD backend/.env | cut -d= -f2)

# Automated DB Creation
if [ "$DB_CONN" == "pgsql" ]; then
    echo -e "${YELLOW}Provisioning PostgreSQL Database...${NC}"
    sudo -u postgres psql -c "CREATE DATABASE $DB_NAME;" || true
    sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';" || true
    sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;" || true
elif [ "$DB_CONN" == "mysql" ]; then
    echo -e "${YELLOW}Provisioning MySQL Database...${NC}"
    # This assumes root has no password or is configured via .my.cnf
    mysql -e "CREATE DATABASE IF NOT EXISTS $DB_NAME;" || true
    mysql -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';" || true
    mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';" || true
    mysql -e "FLUSH PRIVILEGES;" || true
fi

# Configure Redis
echo -e "${YELLOW}Deploying Redis configuration...${NC}"
REDIS_CONF_TARGET="/etc/redis.conf"
[ ! -f "$REDIS_CONF_TARGET" ] && REDIS_CONF_TARGET="/etc/redis/redis.conf"

if [ -f "$REDIS_CONF_TARGET" ]; then
    sed -e "s|{{REDIS_PORT}}|6379|g" \
        scripts/templates/redis.conf.template | sudo tee $REDIS_CONF_TARGET > /dev/null
    sudo systemctl restart redis-server || sudo systemctl restart redis
fi

# 7. Server Configuration (Nginx, Supervisor, Cron)
echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}       Configuring Server Services                ${NC}"
echo -e "${BLUE}==================================================${NC}"

# Ask for Domain
read -p "Enter your Domain/IP (e.g., example.com): " DOMAIN_NAME
DOMAIN_NAME=${DOMAIN_NAME:-$SERVER_IP}

# Variables for templates
CURRENT_PATH=$(pwd)

# Detect PHP-FPM Socket
if [ -S /run/php-fpm/www.sock ]; then
    PHP_FPM_SOCK="unix:/run/php-fpm/www.sock"
elif [ -S /var/run/php/php8.3-fpm.sock ]; then
    PHP_FPM_SOCK="unix:/var/run/php/php8.3-fpm.sock"
elif [ -S /var/run/php/php8.2-fpm.sock ]; then
    PHP_FPM_SOCK="unix:/var/run/php/php8.2-fpm.sock"
else
    # Fallback/Guess
    PHP_FPM_SOCK="unix:/var/run/php-fpm.sock"
fi

echo -e "${GREEN}Detected PHP-FPM Sock: $PHP_FPM_SOCK${NC}"

# Configure Nginx
if command -v nginx &> /dev/null; then
    echo -e "${YELLOW}Deploying Nginx configuration...${NC}"
    NGINX_CONF_PATH="/etc/nginx/sites-available/ja-platform.conf"
    if [ ! -d /etc/nginx/sites-available ]; then
        NGINX_CONF_PATH="/etc/nginx/conf.d/ja-platform.conf"
    fi

    sed -e "s|{{DOMAIN}}|$DOMAIN_NAME|g" \
        -e "s|{{APP_PATH}}|$CURRENT_PATH|g" \
        -e "s|{{PHP_FPM_SOCK}}|$PHP_FPM_SOCK|g" \
        scripts/templates/nginx.conf.template | sudo tee $NGINX_CONF_PATH > /dev/null
    
    if [ -d /etc/nginx/sites-enabled ]; then
        sudo ln -sf /etc/nginx/sites-available/ja-platform.conf /etc/nginx/sites-enabled/
    fi
    sudo nginx -t && sudo systemctl restart nginx
fi

# Configure Supervisor (Workers)
if command -v supervisord &> /dev/null || [ -d /etc/supervisor/conf.d ]; then
    echo -e "${YELLOW}Deploying Supervisor worker configuration...${NC}"
    sed -e "s|{{APP_PATH}}|$CURRENT_PATH|g" \
        -e "s|{{USER}}|$USER|g" \
        scripts/templates/supervisor.conf.template | sudo tee /etc/supervisor/conf.d/ja-worker.conf > /dev/null
    
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start ja-worker:* || true
fi

# Configure Cron
echo -e "${YELLOW}Deploying Cron job...${NC}"
sed -e "s|{{APP_PATH}}|$CURRENT_PATH|g" scripts/templates/cron.template > scripts/current_cron
# Append to crontab if not already exists
(crontab -l 2>/dev/null | grep -v "php artisan schedule:run" ; cat scripts/current_cron) | crontab -
rm scripts/current_cron

# Final Summary
SERVER_IP=$(curl -s https://ifconfig.me || hostname -I | awk '{print $1}')
APP_URL="http://$DOMAIN_NAME"

# Update .env with finalized domain
sed -i "s|APP_URL=.*|APP_URL=$APP_URL|g" backend/.env
sed -i "s|VITE_API_URL=.*|VITE_API_URL=$APP_URL|g" backend/.env

echo -e "${GREEN}==================================================${NC}"
echo -e "${GREEN}   JA-PLATFORM INSTALLATION COMPLETE!             ${NC}"
echo -e "${GREEN}==================================================${NC}"
echo -e "${YELLOW}Access URL:${NC} ${BLUE}${APP_URL}${NC}"
echo -e ""
echo -e "${YELLOW}Default Credentials:${NC}"
echo -e "Username : ${GREEN}super${NC}"
echo -e "Password : ${GREEN}Senja@jejakawan${NC}"
echo -e "${GREEN}==================================================${NC}"
echo -e "${YELLOW}Next Step:${NC} All services (Nginx, Worker, Cron) are ready!"
echo -e "${GREEN}==================================================${NC}"
