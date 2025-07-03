#!/bin/bash

# Docker Setup Script for Ratepay Coding Challenge

echo "🐳 Setting up Ratepay Coding Challenge with Docker..."

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed. Please install Docker first."
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker compose &> /dev/null; then
    echo "❌ Docker Compose is not installed. Please install Docker Compose first."
    exit 1
fi

# Copy Docker environment file
echo "📄 Setting up environment file..."
cp backend/.env.docker backend/.env

# Build and start containers
echo "🚀 Building and starting Docker containers..."
docker compose up -d --build

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 15

# Install dependencies
echo "📦 Installing Composer dependencies..."
docker compose exec app composer install

# Generate application key
echo "🔑 Generating application key..."
docker compose exec app php artisan key:generate

# Install Laravel Sanctum if not already installed
echo "🔒 Setting up Laravel Sanctum..."
docker compose exec app composer require laravel/sanctum
docker compose exec app php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Run migrations and seeders
echo "🗄️ Running database migrations and seeders..."
docker compose exec app php artisan migrate --seed

# Set proper permissions
echo "🔧 Setting proper permissions..."
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache

echo ""
echo "✅ Setup complete!"
echo ""
echo "🌐 Application is running at: http://localhost:8000"
echo "🗄️ PHPMyAdmin is available at: http://localhost:8080"
echo ""
echo "📧 Sample user credentials:"
echo "   Email: test@example.com"
echo "   Password: password"
echo ""
echo "🛠️ Useful Docker commands:"
echo "   Stop containers: docker compose down"
echo "   View logs: docker compose logs -f"
echo "   Access container: docker compose exec app bash"
echo "   Restart containers: docker compose restart"
