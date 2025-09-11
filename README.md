# Reader

A web application to save and summarize articles from a given URL.

## Description

The "Reader" project is an application based on the Symfony framework that allows users to add articles by providing their URL. The application then downloads the content of the article, generates its summary and saves it in the database. The user can view the list of added summaries.

The application is fully containerized using Docker and Docker Compose.

## Technology

* **Backend:** PHP 8.2+ / Symfony 6.4+
* **Database:** SQLite (for the development environment), Doctrine ORM
* **Containerization:** Docker, Docker Compose
* **Web Server:** Nginx
* **Tests:** PHPUnit

## Requirements

*Docker
* Docker Compose

## Installation and commissioning

1. **Clone repository:**
    '''bash
    git clone <adres-repozytorium>
    CD Reading Room
    ```

2. **Environment Configuration:**
    The design uses environmental variables. Copy the '.env' file to '.env.local' and customize it as needed.
    '''bash
    cp .env .env.local
    ```
    The default configuration is designed to run locally with Docker.

3. Build and run Docker containers:**
    '''bash
    docker-compose up -d --build
    ```

4. **Install Composer Dependencies:**
    The command must be executed inside the 'app' container.
    '''bash
    docker-compose exec app composer install
    ```

5. **Run Database Migrations:**
    To create a database schema, run the following command:
    '''bash
    Docker-Compose Exec App PHP Bin/Console Doctrine:Migrations:Migrate
    ```

6. **The app is ready!**
    The application should be available at [http://localhost:8888](http://localhost:8888) (as configured in 'docker-compose.yml').

## Usage

### Web interface

When you enter the [http://localhost:8888](http://localhost:8888) homepage, you will see a list of summarized articles. You can add a new article by clicking on the corresponding button and entering the URL.

### Command Line (CLI)

You can also add a new article using the Symfony command:
'''bash
docker-compose exec app php bin/console app:add-article <adres-url-artykulu>
```
For example:
'''bash
docker-compose exec app php bin/console app:add-article "https://example.com/news/some-interesting-article"
```

## Tests

To run unit and integration tests, use PHPUnit inside the application container:
'''bash
docker-compose exec app php bin/phpunit
```