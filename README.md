# About Petshop

Petshop is a technical interview task.

## Project Setup

- Clone project `git clone git@github.com:alhaji-aki/petshop.git`
- Run `composer install`
- Copy `.env.example` to `.env` and fill your values
- Run `php artisan key:generate` to generate app key
- Run `openssl rand -base64 32` to generate a jwt secret and update the `JWT_SECRET` key in your `.env` file
- Run `openssl genpkey -algorithm RSA -out ./storage/app/private.key` to generate a private key for your project
- Run `openssl rsa -pubout -in ./storage/app/private.key -out ./storage/app/public.key` to generate a public key for your project using the private key
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
