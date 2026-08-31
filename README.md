# CineReserve 🎬 - Real-time Cinema Ticket Booking & Concurrency Engine

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Redis](https://img.shields.io/badge/Redis-Lock%20%26%20Queue-DC382D?style=for-the-badge&logo=redis&logoColor=white)](https://redis.io)
[![Docker](https://img.shields.io/badge/Docker-Containerized-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)

**CineReserve** is a production-grade, full-stack real-time movie ticket booking platform built with **Laravel 11**, **Laravel Reverb (WebSockets)**, **Redis Distributed Locks**, and **Vue.js 3 (Pinia + Tailwind CSS)**.

---

## 🌟 Key Technical Highlights

1. **Real-time Seat State Broadcasting (WebSockets):**
   * Instant sub-second UI updates across all active users using **Laravel Reverb** and **Laravel Echo**.
   * 4 dynamic seat states: `Available` (Slate), `Selected` (Emerald glow), `Holding` (Amber lock pulse), and `Booked` (Muted red).

2. **Concurrency Control & Double-booking Prevention:**
   * Eliminates race conditions when multiple users attempt to reserve the same VIP seat concurrently.
   * Leverages **Atomic Redis Locks** (`SET key value NX EX 600`) for temporary 10-minute seat reservations.
   * Auto-releases expired holds via delayed background queue workers.

3. **Secure Checkout & Idempotent Payments:**
   * Database transactions (`DB::transaction`) with row-level pessimistic locking (`SELECT ... FOR UPDATE`).
   * Simulated payment gateway handling with idempotency protection against duplicate webhook callbacks.

4. **Digital E-Ticket & Background Processing:**
   * Automated scannable QR Code generation.
   * Asynchronous queue jobs for ticket issuance and confirmation email dispatching.

---

## 🏗️ System Architecture

```
[ Vue.js 3 SPA (Vite + Tailwind) ]
       │                         ▲
       │ (REST API)              │ (WebSocket Event Stream)
       ▼                         │
[ Nginx Reverse Proxy ] ───► [ Laravel Reverb (Port 8080) ]
       │                                 ▲
       ▼                                 │
[ Laravel 11 Backend ] ──────── (Broadcasting)
  ├── MySQL 8.0 (ACID Transactions & Bookings)
  ├── Redis 7.0 (10-min Distributed Seat Locks & Queues)
  └── Mailpit (Local SMTP Server)
```

---

## 🚀 Quick Start Guide

### Option 1: Run with Docker Compose (Recommended)

1. Clone the repository:
   ```bash
   git clone https://github.com/hiimthien/cinereserve.git
   cd cinereserve
   ```

2. Start all services in the background:
   ```bash
   docker compose up -d
   ```

3. Initialize Backend:
   ```bash
   docker compose exec php composer install
   docker compose exec php php artisan migrate --seed
   ```

4. Open in browser:
   * **Frontend Application:** `http://localhost:5173`
   * **Backend REST API:** `http://localhost:8000/api/movies`
   * **Reverb WebSocket:** `ws://localhost:8080`
   * **Mailpit Web UI:** `http://localhost:8025`

---

### Option 2: Run Locally (Standalone)

#### Backend (Laravel 11)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan reverb:start --port=8080 &
php artisan serve --port=8000
```

#### Frontend (Vue.js 3)
```bash
cd frontend
npm install
npm run dev
```

---

## 📡 Core API Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/movies` | Fetch all now-showing movies with showtimes |
| `GET` | `/api/showtimes/{id}/seats` | Real-time seat matrix with active Redis hold status |
| `POST` | `/api/showtimes/{id}/seats/{seatId}/hold` | Acquire temporary 10-minute atomic lock on a seat |
| `POST` | `/api/showtimes/{id}/seats/{seatId}/release` | Release held seat back to the pool |
| `POST` | `/api/bookings/checkout` | Finalize booking, record payment, and issue ticket |
| `GET` | `/api/bookings/{code}` | Retrieve digital ticket details with QR code |

---

## 👨‍💻 Author

* **Cao Luong Thien**
* Email: [thiencao.work@gmail.com](mailto:thiencao.work@gmail.com)
* GitHub: [@hiimthien](https://github.com/hiimthien)
