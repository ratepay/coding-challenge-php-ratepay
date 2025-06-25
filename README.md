# Ratepay Live Coding Challenge

## Description
This is the code for Ratepay Live Coding Challenge. Please read this README file and ensure beforehand that you are able to import the project to an IDE of your choice, build and run it. You don't have to implement something in advance, just get a feeling of the project, so you are mentally prepared for what we might ask you to do in our interview :)

This challenge involves implementing a Task Management API using Laravel to demonstrate your PHP and Laravel development skills.

## Dependencies
This project already includes:

- **Laravel Framework** (PHP web framework)
- **Laravel Sanctum** (API authentication)
- **MySQL Database** (via Docker)
- **Docker & Docker Compose** (containerization)
- **PHPUnit** (testing framework)
- **Nginx** (web server)

You don't have to include any other dependencies for this challenge (unless something is really needed by you). And don't worry if something is unknown to you, you won't be asked to necessarily utilize all of these dependencies.

## Database and testing data
Please note that some dummy data is populated to the MySQL database on application startup for you to play around. This behavior is implemented in the `TaskSeeder.php` class.

The application uses MySQL database running in Docker container.

Database credentials (configured in docker-compose.yml):
- **Database**: `laravel_db`
- **User**: `laravel_user` 
- **Password**: `laravel_password`
- **Host**: `localhost`
- **Port**: `3306`

You can connect to the database using any MySQL client with these credentials.

## How to build, test, run
You should be able to build and start the application as you get the code from us. The project includes Docker setup which handles all dependencies and services.

### Prerequisites
- Docker and Docker Compose installed on your system

### Setup and Run
To build and start the application, execute:
```bash
./docker-setup.sh
```

This script will:
1. Build the Docker containers
2. Install PHP dependencies via Composer
3. Set up the database with migrations and seeders
4. Start all services (PHP, Nginx, MySQL)

### Running Tests
To run the test suite:
```bash
# Enter the PHP container
docker-compose exec app bash

# Run PHPUnit tests
php artisan test
```

### API Base URL
Once running, the API will be available at:
```
http://localhost:8000/api
```

## Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php      # Authentication endpoints
│   │   │   └── TaskController.php      # Task management endpoints
│   │   └── Requests/
│   │       ├── StoreTaskRequest.php    # Task creation validation
│   │       └── UpdateTaskRequest.php   # Task update validation
│   └── Models/
│       ├── Task.php                    # Task model
│       └── User.php                    # User model
├── database/
│   ├── migrations/
│   │   └── create_tasks_table.php      # Database schema
│   └── seeders/                        # Test data
└── routes/
    └── api.php                         # API routes definition
```

## What We're Looking For

- **Laravel Best Practices**: Proper use of Eloquent, middleware, validation
- **Security**: Input validation, authorization, secure authentication  
- **Code Quality**: Clean, readable, well-organized code
- **API Design**: Consistent responses, proper HTTP status codes
- **Problem Solving**: Efficient queries, proper error handling

