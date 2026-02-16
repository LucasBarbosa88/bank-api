# Laravel Bank API

A simple banking API developed in Laravel to simulate basic financial operations such as deposit, withdrawal, transfer, and balance inquiry.  
This project follows the MVC pattern and uses Eloquent ORM with MySQL.

## Project Setup (Docker)

This project is fully dockerized for ease of setup.

1. **Clone and Access:**
   ```bash
   git clone https://github.com/LucasBarbosa88/bank-api.git
   cd bank-api
   ```

2. **Environment File:**
   ```bash
   cp .env.example .env
   ```

3. **Start the Application:**
   ```bash
   docker-compose up -d --build
   ```
   *The entrypoint script will automatically handle dependencies and migrations.*

4. **Access:**
   The API will be available at `http://localhost:8080`.
---

## Database

- **accounts:** Stores account data and the current balance.
- **transactions:** Records deposits and withdrawals.
- **transfers:** Records money transfers between accounts.

> [!IMPORTANT]
> **Data Integrity:** The `balance` in the `accounts` table is updated atomically within database transactions using **Pessimistic Locking** (`FOR UPDATE`) to prevent race conditions and ensure 100% accuracy in concurrent operations.

---

## System Architecture

The project follows a **Layered Architecture** to ensure separation of concerns, maintainability, and testability:

- **Controllers:** Handle HTTP communication, request validation, and response formatting.
- **DTOs (Data Transfer Objects):** Carry data between layers in a structured and immutable way, ensuring type safety.
- **Services:** Contain the core business logic and manage database transactions.
- **Repositories:** Abstract the data access layer, keeping the business logic decoupled from the ORM.

### Flow Example:
`Request` → `Controller` → `DTO` → `Service` → `Repository` → `Database`
---

## Example Test with cURL

```bash
curl -X POST http://localhost:8080/api/event -H "Content-Type: application/json" -d '{"type":"deposit", "destination":"100", "amount":10}'
```

---

## License

This project is licensed under the [MIT License](https://choosealicense.com/licenses/mit/).
