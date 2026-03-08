# Kontrol Duitmu

A personal finance management web app built with Laravel 12, Blade, and Tailwind CSS.

## Highlights

- Authentication with Laravel Breeze
- Dashboard for cashflow, bills, goals, debt, investment, and news overview
- Account balance tracking for `Tunai`, `Bank`, and `E-wallet`
- Transaction and internal transfer flows with automatic account mutation records
- Bills module with create, pay, and delete actions
- Saving goals module with create, deposit, withdraw, and delete actions
- Debt module with create, payment, and delete actions
- Investment module with create, buy/sell transactions, valuation updates, and delete actions
- Admin panel for integration settings, sync logs, and user CRUD
- Profile reset feature to wipe all user financial data without deleting the login account

## Tech Stack

- PHP 8.2+
- Laravel 12
- Blade
- Tailwind CSS
- MySQL
- Vite
- Pest

## Main Modules

### User Area

- Dashboard
- Accounts
- Transactions
- Transfers
- Bills
- Saving Goals
- Debts
- Investments
- News
- Analysis
- Profile

### Admin Area

- Admin dashboard
- User management
- API / integration settings
- Sync logs

## Financial Logic

The app uses service classes for balance-sensitive operations:

- `TransactionService`
- `TransferService`
- `BillPaymentService`
- `SavingGoalService`
- `DebtService`
- `InvestmentService`
- `PaymentAccountService`
- `AccountMutationService`

These flows automatically update balances and create mutation/history records where needed.

## Project Structure

- `app/Http/Controllers` - app and admin controllers
- `app/Http/Requests` - request validation
- `app/Models` - Eloquent models
- `app/Services` - finance and integration business logic
- `database/migrations` - schema
- `database/seeders` - default categories, integration config, starter users
- `resources/views` - Blade UI
- `routes/web.php` - web routes
- `routes/console.php` - scheduler registrations
- `tests/Feature` - feature coverage

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Configure your database in `.env`, then run:

```bash
php artisan migrate --seed
```

Start the app:

```bash
php artisan serve
npm run dev
```

## Default Accounts

After a new user registers, the app automatically creates:

- `Tunai`
- `Bank`
- `E-wallet`

## Seeder Notes

Current seeding keeps the project clean for real usage:

- default categories are seeded
- integration defaults are seeded
- starter admin and test users are seeded
- finance demo data is not seeded by default anymore

If you previously seeded demo data, reset your local database to remove it:

```bash
php artisan migrate:fresh --seed
```

## Default Login

- Admin: `admin@kontrolduitmu.test` / `password`
- User: `test@example.com` / `password`

## Integrations

Supported integration scaffolding includes:

- Alpha Vantage investment news sync
- Google Calendar sync placeholder for bill reminders

Available console commands:

```bash
php artisan finance:sync-investment-news
php artisan finance:sync-google-calendar-bills
```

## Testing

Run all feature tests:

```bash
php artisan test tests/Feature
```

Run all tests:

```bash
php artisan test
```

## Important Notes

- This project is frontend-first but already wired to real database-backed flows
- Some integrations are scaffolded and ready for deeper implementation later
- Admin user management supports create, update, and delete actions
- Profile page includes a financial data reset action for a single user

## License

This project is open-sourced under the MIT license.
