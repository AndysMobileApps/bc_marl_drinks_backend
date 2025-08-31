#!/bin/bash
set -e

# Install composer dependencies if vendor directory doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing composer dependencies..."
    COMPOSER_MEMORY_LIMIT=-1 composer install --no-dev --optimize-autoloader
fi

# Create .env file from environment variables if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env file from environment variables..."
    cat > .env << EOF
# Database Configuration
DB_HOST=${DB_HOST:-mysql}
DB_NAME=${DB_NAME:-bcmarl_drinks}
DB_USER=${DB_USER:-bcmarl_user}
DB_PASS=${DB_PASS:-bcmarl_pass_2025}

# JWT Configuration
JWT_SECRET=${JWT_SECRET:-bc_marl_jwt_secret_key_2025_very_secure}

# Application Environment
API_ENV=${API_ENV:-development}

# Logging
LOG_LEVEL=debug
EOF
fi

# Start Apache
exec apache2-foreground
