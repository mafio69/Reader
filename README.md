# Czytelnia

Aplikacja webowa do zapisywania i streszczania artykułów z podanego adresu URL.

## Opis

Projekt "Czytelnia" to aplikacja oparta na frameworku Symfony, która umożliwia użytkownikom dodawanie artykułów poprzez podanie ich adresu URL. Aplikacja następnie pobiera treść artykułu, generuje jego podsumowanie i zapisuje w bazie danych. Użytkownik może przeglądać listę dodanych streszczeń.

Aplikacja jest w pełni skonteneryzowana przy użyciu Docker i Docker Compose.

## Technologie

*   **Backend:** PHP 8.2+ / Symfony 6.4+
*   **Baza danych:** SQLite (dla środowiska deweloperskiego), Doctrine ORM
*   **Konteneryzacja:** Docker, Docker Compose
*   **Serwer WWW:** Nginx
*   **Testy:** PHPUnit

## Wymagania

*   Docker
*   Docker Compose

## Instalacja i uruchomienie

1.  **Sklonuj repozytorium:**
    ```bash
    git clone <adres-repozytorium>
    cd czytelnia
    ```

2.  **Konfiguracja środowiska:**
    Projekt wykorzystuje zmienne środowiskowe. Skopiuj plik `.env` do `.env.local` i dostosuj go w razie potrzeby.
    ```bash
    cp .env .env.local
    ```
    Domyślna konfiguracja jest przystosowana do uruchomienia lokalnego z użyciem Docker.

3.  **Zbuduj i uruchom kontenery Docker:**
    ```bash
    docker-compose up -d --build
    ```

4.  **Zainstaluj zależności Composer:**
    Polecenie należy wykonać wewnątrz kontenera `app`.
    ```bash
    docker-compose exec app composer install
    ```

5.  **Uruchom migracje bazy danych:**
    Aby utworzyć schemat bazy danych, wykonaj następującą komendę:
    ```bash
    docker-compose exec app php bin/console doctrine:migrations:migrate
    ```

6.  **Aplikacja jest gotowa!**
    Aplikacja powinna być dostępna pod adresem [http://localhost:8888](http://localhost:8888) (zgodnie z konfiguracją w `docker-compose.yml`).

## Użycie

### Interfejs webowy

Po wejściu na stronę główną [http://localhost:8888](http://localhost:8888) zobaczysz listę streszczonych artykułów. Możesz dodać nowy artykuł, klikając odpowiedni przycisk i podając adres URL.

### Linia komend (CLI)

Możesz również dodać nowy artykuł za pomocą komendy Symfony:
```bash
docker-compose exec app php bin/console app:add-article <adres-url-artykulu>
```
Na przykład:
```bash
docker-compose exec app php bin/console app:add-article "https://example.com/news/some-interesting-article"
```

## Testy

Aby uruchomić testy jednostkowe i integracyjne, użyj PHPUnit wewnątrz kontenera aplikacji:
```bash
docker-compose exec app php bin/phpunit
```
