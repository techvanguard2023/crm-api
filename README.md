# CRM API - Laravel REST API

A comprehensive Customer Relationship Management API built with Laravel 12, featuring customer management, service billing, expense tracking, and payment processing.

## Features

- **Customer Management**: Create, update, and manage customer profiles
- **Service Billing**: Attach services with customizable pricing and recurrence
- **Payment Processing**: Multiple payment methods (PIX, Boleto)
- **Expense Tracking**: Monitor and categorize business expenses
- **Domain Management**: Associate and manage customer domains
- **Automated Testing**: 43 comprehensive tests covering all endpoints

## Technology Stack

- **Framework**: Laravel 12
- **Database**: MySQL 8.0
- **Testing**: PHPUnit with SQLite in-memory database
- **Authentication**: Laravel Sanctum
- **API Format**: RESTful JSON with Resource classes

## Running Tests

### Quick Reference

All Tests:
```bash
docker-compose exec laravel.test php artisan test
```

Specific Test File:
```bash
docker-compose exec laravel.test php artisan test tests/Feature/CustomerTest.php
```

Specific Test Method:
```bash
docker-compose exec laravel.test php artisan test --filter test_index_returns_customers
```

### Test Suite Overview

**43 comprehensive tests** with **305 assertions** across 6 test files:

| Test File | Tests | Purpose |
|-----------|-------|---------|
| CustomerTest.php | 7 | Customer CRUD, service attachment, soft delete |
| ServiceTest.php | 6 | Service CRUD, validation |
| ExpenseTest.php | 7 | Expense management, metrics |
| DomainTest.php | 7 | Domain CRUD, unique constraints |
| CustomerServiceTest.php | 5 | Service renewal, billing metrics |
| PaymentTest.php | 4 | Payment requests, callbacks |

**Test Execution**: ~1.5 seconds  
**Database**: SQLite in-memory (isolated per test)

### Test Database Configuration

Tests use SQLite in-memory database (phpunit.xml):
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Benefits:
- Fresh database per test
- No cleanup required
- Automatic rollback after test
- Sub-2 second execution

## Key Design Patterns

### 1. Resource Classes

All API responses standardized with Resource classes:

- CustomerResource (14 fields)
- ServiceResource (5 fields)
- ExpenseResource (10 fields)
- DomainResource (7 fields)
- PaymentResource (with related data)

### 2. FormRequest Validation

Centralized validation with comprehensive documentation:

- StoreCustomerRequest
- UpdateCustomerRequest
- StoreExpenseRequest / UpdateExpenseRequest
- AttachServiceToCustomerRequest
- RenewCustomerServiceRequest
- StorePaymentRequest / UpdatePaymentCallbackRequest

### 3. Recurrence Service

Billing cycle calculations for: once, daily, weekly, bi-weekly, monthly, quarterly, semi-annual, annual

## API Endpoints

### Customers
- GET /api/v1/customers
- POST /api/v1/customers
- GET /api/v1/customers/{id}
- PUT /api/v1/customers/{id}
- DELETE /api/v1/customers/{id}
- POST /api/v1/customers/{id}/services

### Services
- GET /api/v1/services
- POST /api/v1/services
- GET /api/v1/services/{id}
- PUT /api/v1/services/{id}
- DELETE /api/v1/services/{id}

### Expenses
- GET /api/v1/expenses
- GET /api/v1/expenses/metrics
- POST /api/v1/expenses
- GET /api/v1/expenses/{id}
- PUT /api/v1/expenses/{id}
- DELETE /api/v1/expenses/{id}

### Domains
- GET /api/v1/domains
- POST /api/v1/domains
- GET /api/v1/domains/{id}
- PUT /api/v1/domains/{id}
- DELETE /api/v1/domains/{id}

### Payments
- GET /api/v1/payments
- POST /api/v1/customer-services/{id}/payment-request
- PUT /api/v1/payments/callback
- GET /api/v1/payments/request/{request_id}
- GET /api/v1/payments/request/{request_id}/customer

## Project Structure

```
app/
├── Models/                 # Eloquent models
├── Http/
│   ├── Controllers/Api/   # API controllers
│   ├── Requests/          # Form request validation
│   └── Resources/         # JSON API resources
├── Services/              # Business logic
└── Traits/               # Reusable traits

database/
├── migrations/            # Schema migrations
├── factories/             # Model factories
└── seeders/              # Database seeders

tests/
├── Feature/              # Integration tests
└── TestCase.php          # Base test case
```

## API Response Format

List Response:
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  ]
}
```

Single Resource:
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com"
}
```

## Troubleshooting

Regenerate autoloader:
```bash
docker-compose exec laravel.test composer dump-autoload
```

Reset database:
```bash
docker-compose exec laravel.test php artisan migrate:fresh
```

Fix permissions:
```bash
docker-compose exec laravel.test chmod -R 755 storage bootstrap/cache
```

## License

MIT License
