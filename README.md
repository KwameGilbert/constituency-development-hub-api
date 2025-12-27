# Constituency Development Hub API

This is the backend API for the Constituency Development Hub platform. It is built using the Slim Framework and manages data persistence using Eloquent ORM.

## Prerequisites

Before setting up the project, ensure you have the following software installed on your machine:

1.  **XAMPP** (for PHP and MySQL):
    *   Download: [https://www.apachefriends.org/index.html](https://www.apachefriends.org/index.html)
    *   *Note during installation*: Ensure "PHP" and "MySQL" are selected.
2.  **Composer** (PHP Dependency Manager):
    *   Download: [https://getcomposer.org/download/](https://getcomposer.org/download/)
    *   *Note during installation*: When asked for the PHP executable, browse to your XAMPP installation folder (usually `C:\xampp\php\php.exe`).

---

## Step-by-Step Setup Guide

### 1. Start XAMPP Services
1.  Open the **XAMPP Control Panel**.
2.  Click **Start** next to **Apache**.
3.  Click **Start** next to **MySQL**.

### 2. Clone or Download the Project
If you haven't already, navigate to the project directory in your terminal (Command Prompt or PowerShell).
```bash
cd c:\Users\G.E.Kukah\code\constituency-development-hub-api
```

### 3. Install PHP Dependencies
Run the following command in the project root to install all required packages: :
```bash
composer install
```

### 4. Configure Environment Variables
1.  Create a copy of the example environment file:
    ```bash
    copy .env.example .env
    ```
2.  Open the `.env` file in your code editor.
3.  Find the `LOCAL_DB_` section and ensure it matches your local database credentials. Default XAMPP settings are usually:
    ```env
    LOCAL_DB_HOST=127.0.0.1
    LOCAL_DB_PORT=3306
    LOCAL_DB_DATABASE=constituency_hub
    LOCAL_DB_USERNAME=root
    LOCAL_DB_PASSWORD=
    ```
    *Note: Change `LOCAL_DB_DATABASE` to your desired database name if `constituency_hub` is not preferred.*

### 5. Create the Database
1.  Open your browser and search for [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
2.  Click **New** in the sidebar.
3.  Enter the database name exactly as defined in your `.env` file (e.g., `constituency_hub`).
4.  Select `utf8mb4_unicode_ci` as the collation.
5.  Click **Create**.

### 6. Run Database Migrations
Create the necessary tables in your database by running:
```bash
composer phinx-migrate
```
*This command executes the migration scripts located in `database/migrations`.*

### 7. Seed the Database (Optional)
Populate the database with initial dummy data for testing:
```bash
composer phinx-seed
```

---

## Running the API

You can serve the API using the built-in PHP server command defined in `composer.json`.

**Start the server:**
```bash
composer start
```

The API will be accessible at: `http://localhost:8080`

---

## API Documentation

### Postman Collection
A Postman collection is available to help you test the API endpoints.
*   **Location**: `requests/constituency-hub.postman_collection.json`
*   **Import**: Open Postman -> File -> Import -> Select the file above.

### Common Commands

| Command | Description |
| :--- | :--- |
| `composer start` | Starts the local development server on port 8080. |
| `composer phinx-migrate` | Runs pending database migrations. |
| `composer phinx-rollback` | Reverts the last migration. |
| `composer phinx-seed` | Runs database seeders. |
| `composer test` | Runs PHPUnit tests. |

## Troubleshooting

*   **"Composer is not recognized..."**: Ensure Composer is added to your system PATH during installation. You may need to restart your terminal.
*   **Database Connection Errors**: Double-check your `.env` file credentials and ensure MySQL is running in XAMPP.
*   **Missing extensions**: If `composer install` complains about missing PHP extensions, enable them in `C:\xampp\php\php.ini` by removing the `;` prefix (e.g., `;extension=fileinfo` -> `extension=fileinfo`).
