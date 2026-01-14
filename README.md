# Optics POS System

A modern Point of Sale system for optical stores, built with Laravel 11 following the Dashboard Template CRUD pattern.

## Features

- **Dashboard**: Overview of sales, purchases, and key metrics
- **Clients Management**: Manage customer information with multiple phone numbers
- **Products & Categories**: Inventory management with stock tracking
- **Vendors Management**: Manage suppliers and their information
- **Invoices (Sales)**: Create and manage sales invoices with payment tracking
- **Bills (Purchases)**: Track purchase orders from vendors
- **Transactions**: Record income and expense transactions
- **Expenses**: Track operational expenses

## Technology Stack

- **Backend**: Laravel 11
- **Frontend**: Bootstrap 5, jQuery
- **DataTables**: Yajra Laravel DataTables (Server-side processing)
- **Icons**: Font Awesome 6

## Installation

### 1. Clone and Install Dependencies

```bash
cd new-optics-pos
composer install
```

### 2. Environment Setup

Copy the environment file and configure your database:

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration

**IMPORTANT**: This project connects to an EXISTING database. No migrations are run.

Update your `.env` file with the existing database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_existing_pos_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Create Storage Link

```bash
php artisan storage:link
```

### 5. Serve the Application

```bash
php artisan serve
```

Visit `http://localhost:8000` to access the application.

## Project Structure

```
app/
├── DataTables/           # DataTable classes for server-side processing
│   ├── BaseDataTable.php
│   ├── ClientDataTable.php
│   ├── ProductDataTable.php
│   └── ...
├── Helpers/              # Helper classes
│   ├── Column.php        # DataTable column builder
│   └── Filter.php        # DataTable filter builder
├── Http/
│   ├── Controllers/      # CRUD Controllers
│   └── Requests/         # Form Request validation
├── Models/               # Eloquent models (mapping only)
└── ...

resources/views/
├── components/           # Blade components
│   ├── app-layout.blade.php
│   ├── dynamic-table.blade.php
│   ├── form.blade.php
│   └── group-input-*.blade.php
├── pages/                # Page views organized by module
│   ├── client/
│   ├── product/
│   ├── invoice/
│   └── ...
└── auth/                 # Authentication views
```

## CRUD Pattern

All modules follow the same CRUD pattern from the Dashboard Template:

### Controller Structure
```php
public function index(DataTable $dataTable, Request $request)
{
    if ($request->ajax()) {
        return $dataTable->handle();
    }
    return view('pages.module.index', [
        'columns' => $dataTable->columns(),
        'filters' => $dataTable->filters(),
    ]);
}
```

### DataTable Structure
```php
class ModuleDataTable extends BaseDataTable
{
    public function columns(): array
    {
        return [
            Column::create('id')->setOrderable(true),
            Column::create('name')->setTitle('Name'),
            // ...
        ];
    }

    public function filters(): array
    {
        return [
            'status' => Filter::select('Status', [...]),
            'created_at' => Filter::dateRange('Date'),
        ];
    }

    public function handle() { ... }
}
```

### View Structure
- `index.blade.php` - Uses `<x-dynamic-table>` component
- `form.blade.php` - Uses `<x-form>` and `<x-group-input-*>` components
- `columns/_actions.blade.php` - Action buttons (view, edit, delete)
- `show.blade.php` - Detail view page

## Database Tables Used

The system expects these tables in your existing database:

- `users` - User authentication
- `clients` - Customer information
- `categories` - Product categories
- `products` - Product inventory
- `vendors` - Suppliers
- `invoices` - Sales invoices
- `invoice_items` - Invoice line items
- `bills` - Purchase bills
- `bill_items` - Bill line items
- `transactions` - Payment transactions
- `accounts` - Bank/Cash accounts
- `expenses` - Expense records
- `papers` - Prescription papers (optics-specific)

## API Response Format

All AJAX operations return JSON in this format:

```json
{
    "status": true,
    "msg": "Operation successful",
    "data": { ... }
}
```

## Notes

- **No Migrations**: This project connects to an existing database
- **Model Mapping**: Models only define fillable fields and relationships
- **Same CRUD Style**: All modules follow the Dashboard Template pattern
- **Production Ready**: Clean, modular, and well-organized code

## License

This project is proprietary software.
