# Project Information for AI (Adam)

This file contains key information about the "Czytelnia" project, the development environment, and our goals. It serves as a reference point for the AI assistant (Adam) to ensure consistent and effective help.

## 1. General Information

- **Project Name:** Czytelnia
- **User:** Mariusz
- **AI Assistant:** Adam
- **Project Goal:** Build an application to manage and summarize articles.
- **Communication Language:** The AI assistant (Adam) will communicate with the user (Mariusz) in **Polish**. All other generated content (code, comments, documentation, commit messages, etc.) will be in **English**.

## 2. Development Environment

- **Operating System:** Windows 11 + WSL2 (Ubuntu 24.04 LTS)
- **Working Directory:** `/home/mariusz/projects/czytelnia`
- **Infrastructure:** Docker, Nginx, PHP-FPM
- **Application Access:** The application is accessible at `http://localhost:9999` from the host's (Windows) browser.

## 3. Technology Stack

### Backend

- **Language:** PHP >= 8.3
- **Framework:** Symfony 7.3.*

### Frontend

- **Current:** Twig (server-side rendering).
- **Target:** React/JavaScript (as per the user's learning request).

### Database

- **ORM:** Doctrine
- **System:** Currently **undefined** in `docker-compose.yml`. A database container (e.g., MariaDB or PostgreSQL) needs to be added.

## 4. Testing & Code Quality

- **Testing Framework:** PHPUnit
- **Tools:**
  - `vendor/bin/phpunit` - to run tests.
  - `php-cs-fixer` - to maintain consistent code style.

## 5. Goals & Conventions

- **User's Main Goal:** To learn and understand modern technologies, especially React and JavaScript, within the context of a practical project.
- **My Role (Adam):** To explain code step-by-step, assist with refactoring, implementing new features, and writing tests.
- **Verification:** After every major code modification in PHP, we will run the tests to ensure no regressions have been introduced.

## 6. Project Structure

This is a `tree` view approximating the project's source code, with common untracked directories and files excluded for clarity.

```
.
├── Dockerfile
├── README.md
├── ai.md
├── app
│   ├── GEMINI.md
│   ├── bin
│   │   ├── console
│   │   └── phpunit
│   ├── compose.override.yaml
│   ├── compose.yaml
│   ├── composer.json
│   ├── composer.lock
│   ├── config
│   │   ├── bundles.php
│   │   ├── packages
│   │   │   ├── cache.yaml
│   │   │   ├── csrf.yaml
│   │   │   ├── debug.yaml
│   │   │   ├── doctrine.yaml
│   │   │   ├── doctrine_migrations.yaml
│   │   │   ├── framework.yaml
│   │   │   ├── monolog.yaml
│   │   │   ├── property_info.yaml
│   │   │   ├── routing.yaml
│   │   │   ├── twig.yaml
│   │   │   ├── validator.yaml
│   │   │   └── web_profiler.yaml
│   │   ├── preload.php
│   │   ├── routes
│   │   │   ├── framework.yaml
│   │   │   └── web_profiler.yaml
│   │   ├── routes.yaml
│   │   ├── secrets
│   │   │   └── dev
│   │   │       ├── dev.decrypt.private.php
│   │   │       └── dev.encrypt.public.php
│   │   └── services.yaml
│   ├── http
│   │   └── test.http
│   ├── migrations
│   │   ├── Version20250713153009.php
│   │   └── Version20250911210718.php
│   ├── phpunit.dist.xml
│   ├── phpunit.xml
│   ├── public
│   │   ├── index.php
│   │   ├── phpstorm_debug.php
│   │   ├── phpstorm_debug_validator.phar
│   │   └── phpstorm_index.php
│   ├── secrets
│   ├── src
│   │   ├── Command
│   │   │   ├── AddArticleCommand.php
│   │   │   └── WebsocketTestCommand.php
│   │   ├── Controller
│   │   │   ├── HomeController.php
│   │   │   └── SummaryController.php
│   │   ├── Entity
│   │   │   └── ArticleSummary.php
│   │   ├── EventListener
│   │   │   └── EnvironmentBannerListener.php
│   │   ├── EventListner
│   │   ├── Form
│   │   │   └── SummaryFormType.php
│   │   ├── Kernel.php
│   │   ├── Repository
│   │   │   └── ArticleSummaryRepository.php
│   │   └── Service
│   │       ├── ArticleManager.php
│   │       ├── DatabaseConnectionManager.php
│   │       ├── SummarizationService.php
│   │       └── SummaryFormHandler.php
│   ├── symfony.lock
│   ├── templates
│   │   ├── base.html.twig
│   │   ├── partials
│   │   │   └── _pagination.html.twig
│   │   └── summary
│   │       ├── index.html.twig
│   │       ├── new.html.twig
│   │       └── show.html.twig
│   └── tests
│       ├── Command
│       │   └── AddArticleCommandTest.php
│       ├── Controller
│       │   └── SummaryControllerTest.php
│       ├── Service
│       │   ├── ArticleManagerTest.php
│       │   ├── SummarizationServiceTest.php
│       │   └── SummaryFormHandlerTest.php
│       └── bootstrap.php
├── docker
│   ├── cron
│   │   └── crontab
│   ├── nginx
│   │   ├── app.conf
│   │   └── nginx.conf
│   ├── php
│   │   ├── php-dev.ini
│   │   ├── php-fpm-www.conf
│   │   ├── php-fpm.conf
│   │   ├── php-prod.ini
│   │   ├── php.ini
│   │   └── xdebug.ini
│   ├── scripts
│   │   └── health-check.sh
│   └── supervisor
│       └── supervisord.conf
└── docker-compose.yml
```