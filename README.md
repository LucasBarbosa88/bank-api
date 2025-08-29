# Laravel Bank API

A simple banking API developed in Laravel to simulate basic financial operations such as deposit, withdrawal, transfer, and balance inquiry.  
This project follows the MVC pattern and uses Eloquent ORM with MySQL.

## Project Setup

Clone this repository:
```bash
git clone https://github.com/LucasBarbosa88/bank-api.git
```

Access the project directory:
```bash
cd bank-api
```

Install dependencies:
```bash
composer install
```

Copy the example env file:
```bash
cp .env.example .env
```

Create an empty database and update your `.env` file with the DB credentials.

Generate app key:
```bash
php artisan key:generate
```

Run the migrations:
```bash
php artisan migrate
```

Start the local development server:
```bash
php artisan serve
```

You can now access the server at (http://localhost:8000)

---

## Database

- The **accounts** table stores account information and current balance.
- The **transactions** table stores deposits and withdrawals (differentiated by the `type` field).
- The **transfers** table stores money transfers between two accounts.

> The `balance` column in the **accounts** table is a denormalized field for performance reasons.  
> It is automatically kept up-to-date using Laravel **model observers**, which react to insert/update/delete events on `transactions` and `transfers`.

---

## System Architecture

The system follows the **MVC (Model-View-Controller)** pattern:

- **Controllers** handle HTTP requests and validate input.
- **Models** contain the business logic and relationships.
- **Observers** ensure account balances are synchronized with transactions and transfers.

---

## Example Test with cURL

```bash
curl -X POST http://localhost:8000/api/event -H "Content-Type: application/json" -d '{"type":"deposit", "destination":"100", "amount":10}'
```

---

## License

This project is licensed under the [MIT License](https://choosealicense.com/licenses/mit/).
