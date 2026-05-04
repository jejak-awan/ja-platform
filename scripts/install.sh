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

# Error Handling Function
trap 'on_error $LINENO' ERR
on_error() {
    echo -e "\n${RED}==================================================${NC}"
    echo -e "${RED}  ERROR: Installation Failed at line $1          ${NC}"
    echo -e "${RED}==================================================${NC}"
    echo -e "${YELLOW}Common Solutions:${NC}"
    echo -e "1. Check your internet connection."
    echo -e "2. Run 'sudo apt update' or 'sudo dnf check-update' manually."
    echo -e "3. If a repo is blocked, check your firewall/DNS settings."
    echo -e "4. Ensure you have enough disk space."
    exit 1
}

echo -e "${BLUE}==================================================${NC}"
echo -e "${BLUE}       JA-Platform Auto-Installer v2.1            ${NC}"
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
    sudo apt-get update -y
    sudo apt-get autoremove -y
elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
    sudo dnf clean all
    sudo dnf update -y
fi

# 4. Dependency Management
install_pkg() {
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        sudo apt-get install -y "$@"
    elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        sudo dnf install -y "$@"
    fi
}

# PHP Check & Install
echo -e "${YELLOW}Checking PHP requirements...${NC}"
if ! command -v php &> /dev/null || [[ $(php -r "echo PHP_VERSION_ID;") -lt 80200 ]]; then
    echo -e "Installing/Upgrading PHP 8.3..."
    if [[ "$OS" == "ubuntu" || "$OS" == "debian" ]]; then
        sudo apt-get install -y software-properties-common
        sudo add-apt-repository -y ppa:ondrej/php
        sudo apt-get update
        install_pkg php8.3 php8.3-cli php8.3-common php8.3-pgsql php8.3-bcmath php8.3-curl php8.3-gd php8.3-intl php8.3-xml php8.3-zip php8.3-mbstring php8.3-redis
    elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-$(echo $VER | cut -d. -f1).rpm
        sudo dnf module reset php -y
        sudo dnf module enable php:remi-8.3 -y
        install_pkg php php-cli php-common php-pgsql php-bcmath php-curl php-gd php-intl php-xml php-zip php-mbstring php-redis
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
        curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
        install_pkg nodejs
    elif [[ "$OS" == "centos" || "$OS" == "almalinux" || "$OS" == "rhel" ]]; then
        curl -fsSL https://rpm.nodesource.com/setup_22.x | sudo bash -
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

echo -e "${GREEN}==================================================${NC}"
echo -e "${GREEN}   Environment is Ready!                          ${NC}"
echo -e "${GREEN}   Please visit your domain to finish setup.      ${NC}"
echo -e "${GREEN}==================================================${NC}"
