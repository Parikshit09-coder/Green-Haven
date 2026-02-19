# 📋 Setup & Run Instructions

Step-by-step guide to run the **GreenHaven Plant Nursery Management System** on your machine.

---

## Prerequisites

1. **PHP 8.0+** with the `pdo_pgsql` extension enabled
2. **Internet connection** (for the Neon PostgreSQL cloud database)

---

## Step 1: Install PHP (if not installed)

### Windows

1. Download PHP from [https://windows.php.net/download](https://windows.php.net/download)
2. Extract to `C:\php`
3. Add `C:\php` to your system PATH
4. Copy `php.ini-development` to `php.ini`
5. Open `php.ini` and **uncomment** these lines (remove the `;`):
   ```
   extension=pdo_pgsql
   extension=pgsql
   extension=openssl
   ```
6. Verify: `php -v`

### Linux / Mac (via package manager)

```bash
# Ubuntu/Debian
sudo apt install php php-pgsql

# Mac (Homebrew)
brew install php
```

---

## Step 2: Run Database Migration

The database schema needs to be created once on your Neon PostgreSQL.

### Option A: Using psql (command-line)

```bash
psql 'postgresql://neondb_owner:npg_xgyYkH6b4CIK@ep-polished-waterfall-ai9taylr-pooler.c-4.us-east-1.aws.neon.tech/neondb?sslmode=require' -f database/schema.sql
```

### Option B: Using Neon Dashboard

1. Go to [https://console.neon.tech](https://console.neon.tech)
2. Open the **SQL Editor**
3. Copy-paste the contents of `database/schema.sql`
4. Click **Run**

---

## Step 3: Start the Server

From the project root directory:

```bash
php -S localhost:8000
```

You should see:

```
PHP Development Server (http://localhost:8000) started
```

---

## Step 4: Open the Application

1. Open your browser and go to: **http://localhost:8000/login.html**
2. Login with: **admin** / **admin123**
3. You'll be redirected to the Dashboard

---

## Step 5: Use the Application

### Navigation (Sidebar)

| Page | What to do |
|------|-----------|
| **Dashboard** | Overview of KPIs, recent orders, stock alerts |
| **Plants** | Add/edit/delete plants, search by name, filter by category |
| **Inventory** | View stock levels, add incoming stock, view inventory log |
| **Orders** | Place new orders, update status, view sales history |
| **Customers** | Add/edit customers, view purchase history |
| **Schedules** | View care schedules (watering, fertilizer, sunlight) |
| **Bills** | Load invoice by order ID, print invoices |

### Recommended Flow

1. **Add some plants** → Go to Plants page → Click "Add Plant"
2. **Add customers** → Go to Customers page → Click "Add Customer"
3. **Place an order** → Go to Orders page → Click "New Order" → Select customer, add items
4. **Check inventory** → Inventory page shows updated stock levels
5. **Print invoice** → Orders page → Click 🧾 icon on an order → Print

---

## Troubleshooting

| Issue | Solution |
|-------|---------|
| `pdo_pgsql` not found | Enable the extension in `php.ini` (see Step 1) |
| CORS errors | These should not occur since frontend and API run on the same server |
| Database connection error | Check internet connection and verify the Neon connection string in `api/db.php` |
| Blank page | Open browser DevTools (F12) → Console tab → Check for errors |
| Port 8000 in use | Use a different port: `php -S localhost:8080` |

---

## File Summary

| File | Purpose |
|------|---------|
| `api/db.php` | Database connection & helpers |
| `api/plants.php` | Plants CRUD API |
| `api/inventory.php` | Inventory tracking API |
| `api/orders.php` | Orders & sales API |
| `api/customers.php` | Customer management API |
| `api/schedules.php` | Care schedules API |
| `api/admin.php` | Admin authentication & dashboard API |
| `api/bills.php` | Invoice generation API |
| `database/schema.sql` | PostgreSQL table definitions |
| `css/style.css` | Complete design system |
| `js/app.js` | Core JavaScript module |
