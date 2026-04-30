# Nordic Inventory — Order & Inventory System

A multi-user order and inventory management system built with **Laravel** (API) and **Vue.js** (SPA frontend).

---

## Tech Stack

- **Backend:** Laravel 12, Laravel Sanctum (API authentication)
- **Frontend:** Vue 3, Vue Router, Axios, Tailwind CSS
- **Database:** MySQL
- **Build Tool:** Vite

---

## Features

- User login and logout with token-based authentication (Sanctum)
- View available products with stock levels
- Add products to cart with custom quantities
- Place orders with automatic total price calculation
- Stock validation — prevents ordering more than available stock
- Clean error messages and loading states

---

## Database Structure

| Table         | Columns                                   |
| ------------- | ----------------------------------------- |
| `users`       | id, name, email, password                 |
| `products`    | id, name, price, stock                    |
| `orders`      | id, user_id, total_price                  |
| `order_items` | id, order_id, product_id, quantity, price |

---

## API Endpoints

| Method | Endpoint        | Description             | Auth |
| ------ | --------------- | ----------------------- | ---- |
| POST   | `/api/login`    | Login and get token     | No   |
| POST   | `/api/logout`   | Logout and revoke token | Yes  |
| GET    | `/api/products` | List all products       | Yes  |
| POST   | `/api/orders`   | Place a new order       | Yes  |

---

## Project Structure

```
app/
├── Http/Controllers/
│   ├── AuthController.php        # Login & Logout
│   ├── ProductController.php     # Product listing
│   └── OrderController.php       # Order creation & business logic
├── Models/
│   ├── User.php
│   ├── Product.php
│   ├── Order.php
│   └── OrderItem.php

resources/js/
├── App.vue                       # Root Vue component
├── axios.js                      # Axios instance with token handling
├── router/index.js               # Vue Router with auth guard
├── components/
│   ├── Login.vue                 # Login page
│   └── Dashboard.vue             # Products list + cart + place order

database/
├── migrations/                   # Table migrations
├── seeders/
│   ├── DatabaseSeeder.php
│   └── ProductSeeder.php         # Sample products
```

---

## Setup Instructions

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL

### 1. Clone the repository

```bash
git clone https://github.com/your-username/nordic-inventory.git
cd nordic-inventory
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```
DB_DATABASE=nordic_inventory
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Run migrations and seed data

```bash
php artisan migrate --seed
```

This creates the tables and seeds 5 sample products.

### 5. Run the application

Build frontend assets once

```
npm run build
```

Set production environment

```
APP_ENV=production
APP_DEBUG=false
```

Start server - no need for npm run dev!

```
php artisan serve
```

### 6. Open in browser

Visit **http://localhost:8000**

### Test credentials

| Email            | Password |
| ---------------- | -------- |
| test@example.com | password |

---

## How to Use

1. Login with the test credentials
2. Browse the product list on the dashboard
3. Set quantity and click **Add** to add items to your cart
4. Review your order and click **Place Order**
5. Stock updates automatically after each order

```

```
