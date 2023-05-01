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


### The postman collections are available 
- Authentication.postman_collection.json
- Loans.postman_collection.json
- Payments.postman_collection.json


### Application's Features are plitted into 3 parts: User, LoanAmount, and Repayments
1. User:
    - Register
    - Login
    - Admin role: Approve Loan Amount

2. LoanAmount
    - Create LoanAmount:
        - When a LoanAmount is created the payments will be created automatically base on required_amount and loan_term. which the repayment amount and date are calculated to match the application's requirement.
    - List LoanAmounts
    - Show a specific LoanAmount
    - Update a specific LoanAmount
        - When a LoanAmount is updated the number of payments will be adjust automatically base on the loan_term and the repayment amount for each term will be recalculated to match the application's requirement.

3. Repayment
    - List Repayments
    - Show a specific Repayment
    - Repay a specific Repayment

### Feature and Unit Tests
    - I just apply some feature tests for the User part.
    
    - Run test
    ```
    ./vendor/bin/sail test
    ```

