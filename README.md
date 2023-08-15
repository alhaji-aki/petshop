# About Petshop

Petshop is a technical interview task.

## Features Implemented

- [x] User Login
- [x] User type middleware to restrict access depending on user type
- [x] Authenticated user can get their profile
- [x] User can get their payments
- [x] User can get their orders
- [x] Payment CRUD this does not include updating payments
- [x] Order CRUD: Implemented only creating and getting an order

## Project Setup

- Clone project `git clone git@github.com:alhaji-aki/petshop.git`
- Run `composer install`
- Copy `.env.example` to `.env` and fill your values
- Run `php artisan key:generate` to generate app key
- Run `php artisan jwt:install` to generate a jwt secret and create private key files
- Fill database and mail credentials in `.env` file
- Run `php artisan migrate --seed`, this will create your database tables and seed data.
- Run `php artisan serve` to serve your project

## Test Accounts

When you run the `php artisan db:seed` command an admin and a user are seeded. You can find credentials below:

- Admin: <admin@petshop.com>
- User: <someone@somewhere.com>

They both are seeded with password as `password`

## Testing

Test are written with [Pest](https://pestphp.com). Run the command below to run test.

```bash
php artisan test
```
