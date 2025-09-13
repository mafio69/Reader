# Reader

A web application to save and summarize articles from a given URL.

## Overview

reader is a Symfony-based web application that allows users to add articles by providing their URL. The application downloads the article content, generates a summary using AI services, and saves it in the database. Users can view the list of added summaries through a web interface or interact with the application via command-line tools.

The application is fully containerized using Docker and includes WebSocket functionality for real-time communication.

## Technology Stack

- **Backend:** PHP 8.3+ / Symfony 7.3
- **Database:** SQLite (default), Doctrine ORM
- **Containerization:** Docker, Docker Compose
- **Web Server:** Nginx
- **Process Manager:** Supervisor
- **Package Manager:** Composer
- **Testing:** PHPUnit 12.3+
- **WebSocket:** amphp/websocket-client
- **Additional:** Cron support, Xdebug (development)

## Requirements

- Docker
- Docker Compose

## Installation and Setup

1. **Clone repository:**
   ```bash
   git clone <repository-url>
   cd reader
   ```

2. **Environment Configuration:**
   The application uses environment variables. Copy the `.env` file to `.env.local` and customize it as needed:
   ```bash
   cp app/.env app/.env.local
   ```
   The default configuration is designed to run locally with Docker.

3. **Build and run Docker containers:**
   ```bash
   docker-compose up -d --build
   ```

4. **Install Composer Dependencies:**
   Dependencies are automatically installed during the Docker build process. To manually install or update:
   ```bash
   docker-compose exec web composer install
   ```

5. **Run Database Migrations:**
   To create the database schema:
   ```bash
   docker-compose exec web php bin/console doctrine:migrations:migrate
   ```

6. **Access the Application:**
   The application is available at [http://localhost:8080](http://localhost:8080)

## Available Scripts and Commands

### Web Interface
Access the main application at [http://localhost:8080](http://localhost:8080) to:
- View summarized articles
- Add new articles by URL
- Manage article summaries

### Command Line Interface (CLI)

The application provides several console commands:

#### Add Article Command
```bash
docker-compose exec web php bin/console app:add-article <article-url>
```
Example:
```bash
docker-compose exec web php bin/console app:add-article "https://example.com/news/some-article"
```

#### WebSocket Test Command
```bash
docker-compose exec web php bin/console app:websocket-test
```

#### Standard Symfony Commands
```bash
# Clear cache
docker-compose exec web php bin/console cache:clear

# List all available commands
docker-compose exec web php bin/console list

# Database commands
docker-compose exec web php bin/console doctrine:migrations:status
docker-compose exec web php bin/console doctrine:schema:validate
```

## Environment Variables

The application uses the following environment variables:

| Variable | Description | Default |
|----------|-------------|---------|
| `APP_ENV` | Application environment (dev/prod/test) | `dev` |
| `APP_SECRET` | Secret key for Symfony | Generated value |
| `DATABASE_URL` | Database connection string | `sqlite:///%kernel.project_dir%/var/data.db` |
| `PHP_IDE_CONFIG` | PHP debugging configuration | `serverName=Docker` |
| `TZ` | Timezone | `Europe/Warsaw` |

Additional environment-specific files:
- `.env` - Default values
- `.env.local` - Local overrides (not committed)
- `.env.dev` - Development-specific defaults
- `.env.test` - Testing environment defaults

## Testing

The application includes a comprehensive test suite with unit and integration tests:

```bash
# Run all tests
docker-compose exec web php bin/phpunit

# Run specific test categories
docker-compose exec web php bin/phpunit tests/Unit
docker-compose exec web php bin/phpunit tests/Integration
```

### Test Structure
- `tests/Command/` - Command tests
- `tests/Controller/` - Controller integration tests  
- `tests/Service/` - Service unit tests

Test coverage includes:
- Article management functionality
- Summarization service
- Form handling
- Console commands
- Controller endpoints

## Project Structure

```
reader/
├── app/                          # Symfony application
│   ├── bin/console              # Console entry point
│   ├── config/                  # Configuration files
│   ├── public/                  # Web root
│   ├── src/                     # Application source code
│   │   ├── Command/            # Console commands
│   │   ├── Controller/         # Web controllers
│   │   ├── Entity/             # Doctrine entities
│   │   ├── Service/            # Business logic services
│   │   └── Repository/         # Data repositories
│   ├── templates/              # Twig templates
│   ├── tests/                  # Test files
│   ├── var/                    # Cache and logs
│   ├── composer.json           # PHP dependencies
│   └── .env                    # Environment variables
├── docker/                      # Docker configuration
│   ├── Dockerfile              # Main container definition
│   ├── nginx/                  # Nginx configuration
│   ├── php/                    # PHP configuration
│   ├── supervisor/             # Process management
│   └── cron/                   # Cron job definitions
├── docker-compose.yml          # Container orchestration
├── logs/                       # Application logs
└── README.md                   # This file
```

## Development

### Docker Environment
The application supports both development and production Docker environments:

- **Development**: Includes Xdebug, development PHP settings
- **Production**: Optimized settings, no development tools

Build for specific environment:
```bash
# Development
docker build --build-arg ENVIRONMENT=dev --target dev -t reader-dev .

# Production
docker build --build-arg ENVIRONMENT=prod --target prod -t reader-prod .
```

### Alternative Production Build and Run

For standalone production deployment without Docker Compose:

```bash
# Build production image with multiple tags
docker build --target prod -t reader-prod -t reader-mf .

# Run production container
docker run --name reader-mf reader-prod
```

**Note:** These commands are for true production deployment without port mapping or volume mounting, as the application and its dependencies are built into the container image.

### Logging
Application logs are mounted to the `./logs` directory:
- `nginx-error.log` - Nginx errors
- `php-fpm.log` - PHP-FPM process logs
- `php_errors.log` - PHP application errors
- `supervisord.log` - Supervisor process management
- `xdebug.log` - Xdebug output (development only)

## TODOs

- [ ] Add configuration for external AI summarization services
- [ ] Document WebSocket endpoint usage and protocols  
- [ ] Add API documentation for REST endpoints
- [ ] Configure production database connection (MySQL/PostgreSQL)
- [ ] Add monitoring and health check endpoints
- [ ] Document backup and recovery procedures

## License

This project is proprietary software. All rights reserved.

---

**Last Updated:** 2025-09-13