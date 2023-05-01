# Aspire's Loan Management Application

## Setup the repository

```
git clone https://github.com/Quang-Nguyen-Van/aspire-loan-management.git
cd aspire-loan-management
composer install
cp .env.example .env
php artisan key:generate
php artisan cache:clear && php artisan config:clear
```

## Setup database
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aspire-loan-management
DB_USERNAME={USERNAME}
DB_PASSWORD={PASSWORD}
```

- The application has been configured to run with docker

### Start docker
```
./vendor/bin/sail up -d
```

### DB Migration and Seeding
```
php artisan migrate
php artisan db:seed
```
### Users for testing
- Normal user: user@example.com / T3stabcde#
- Admin user: admin@example.com / T3stabcde#


