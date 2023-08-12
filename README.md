# About Petshop

Petshop is a technical interview task.

## Project Setup

- Clone project `git clone git@github.com:alhaji-aki/petshop.git`
- Run `composer install`
- Copy `.env.example` to `.env` and fill your values
- Run `php artisan key:generate` to generate app key
- Fill database and mail credentials in `.env` file
- Run `php artisan migrate --seed`, this will create your database tables and seed data.
- Run `php artisan serve` to serve your project
