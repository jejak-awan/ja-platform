#!/bin/bash

# JA-Platform Quick Setup Script
# This script initializes the project and calls the Artisan Installer

echo "🚀 Welcome to JA-Platform Setup"
echo "--------------------------------"

# Check if PHP is installed
if ! command -v php &> /dev/null
then
    echo "❌ PHP is not installed. Please install PHP 8.2+ first."
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null
then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi

echo "📦 Installing backend dependencies..."
cd backend && composer install

echo "🛠️ Starting Interactive Installer..."
php artisan ja:install

echo ""
echo "✨ Setup complete! If everything went well, your JA-Platform is ready."
echo "🌐 Run 'php artisan serve' in backend and 'npm run dev' in frontend for development."
