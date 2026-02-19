# 🌿 GreenHaven — Plant Nursery Management System

A full-stack Plant Nursery Management System built with **HTML/CSS/JS** frontend and **PHP** backend, connected to **Neon PostgreSQL**.

---

## ✨ Features

| Module | Capabilities |
|--------|-------------|
| **Plants** | Add, edit, delete plants with name, category, price, quantity, images |
| **Inventory** | Track incoming/sold stock, auto-update quantities, low-stock & out-of-stock alerts |
| **Orders** | Place orders, update order status, sales history, payment mode (cash/online) |
| **Customers** | Customer details, contact info, purchase history |
| **Schedules** | Watering, fertilizer, sunlight requirements per plant |
| **Bills** | Generate and print professional invoices |
| **Admin** | Login/logout with session management |
| **Dashboard** | KPI cards, recent orders, low-stock alerts |

---

## 🛠 Tech Stack

- **Frontend**: HTML5, CSS3 (Glassmorphism dark theme), Vanilla JavaScript
- **Backend**: PHP 8+ (RESTful API)
- **Database**: PostgreSQL (Neon cloud)
- **Font**: [Inter](https://fonts.google.com/specimen/Inter) (Google Fonts)

---

## 📁 Project Structure

```
tend/
├── api/
│   ├── db.php              # Database connection (Neon PostgreSQL)
│   ├── plants.php           # Plants CRUD API
│   ├── inventory.php        # Inventory & stock tracking API
│   ├── orders.php           # Orders & sales API
│   ├── customers.php        # Customers CRUD API
│   ├── schedules.php        # Care schedules API
│   ├── admin.php            # Admin auth & dashboard stats API
│   └── bills.php            # Invoice generation API
├── css/
│   └── style.css            # Design system (dark glassmorphism)
├── js/
│   └── app.js               # Core JS module (API, toasts, modals, lazy load)
├── database/
│   └── schema.sql           # PostgreSQL schema
├── index.html               # Dashboard
├── login.html               # Admin login
├── plants.html              # Plants management
├── inventory.html           # Inventory tracking
├── orders.html              # Order management
├── customers.html           # Customer management
├── schedules.html           # Care schedules
├── bills.html               # Invoice viewer
├── README.md                # This file
└── INSTRUCTIONS.md          # Setup & run instructions
```

---

## 🚀 Quick Start

```bash
# 1. Ensure PHP 8+ is installed
php -v

# 2. Navigate to the project directory
cd tend

# 3. Run the database migration (one-time setup)
# Use psql or a DB tool to execute database/schema.sql against your Neon DB

# 4. Start the PHP development server
php -S localhost:8000

# 5. Open in browser
# http://localhost:8000/login.html
# Login: admin / admin123
```

See **[INSTRUCTIONS.md](INSTRUCTIONS.md)** for detailed setup steps.

---

## 🔑 Default Login

| Username | Password |
|----------|----------|
| `admin` | `admin123` |

---

## 📡 API Endpoints

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `/api/plants.php` | GET, POST, PUT, DELETE | Plants CRUD with search & pagination |
| `/api/inventory.php` | GET, POST | Stock logs, low-stock alerts, add stock |
| `/api/orders.php` | GET, POST, PUT | Orders with items, status updates |
| `/api/customers.php` | GET, POST, PUT, DELETE | Customer CRUD with purchase history |
| `/api/schedules.php` | GET | Plant care schedules |
| `/api/admin.php` | GET, POST | Login, logout, dashboard stats |
| `/api/bills.php` | GET | Invoice data for an order |

---

## 📄 License

This project is for educational purposes.
