# PageCraft Bookstore ERP — Mini ERP (Microservices)

> Capstone-aligned mini ERP for an **online bookstore**: 6 Laravel microservices, API gateway, RabbitMQ, React UI.  
> See **[docs/BRIEF-COMPLIANCE.md](docs/BRIEF-COMPLIANCE.md)** for mapping to the project brief.

---

## Architecture

```
React Frontend :3000
       │  (nginx proxy → /api)
       ▼
┌─────────────────────────┐
│    API Gateway :8080    │  ← JWT validated before forwarding
└─────────┬───────────────┘
          │
    ┌─────┼──────────────────────────────────────┐
    ▼     ▼           ▼           ▼              ▼
 Auth   Inventory  Order       Customer       Finance
        │          │ REST│ RabbitMQ           │
        │◄─────────┘      └──────────────────► │
        ▼          ▼           ▼              ▼
     MySQL      MySQL       MySQL          MySQL
   (per service — strict database isolation)
```

| Service | Database | Role |
|---------|----------|------|
| api-gateway | — | Single entry point, JWT gate |
| auth-service | auth_db | Users, JWT issuance |
| inventory-service | inventory_db | Books catalog & stock |
| order-service | orders_db | Orders; calls Inventory + publishes events |
| customer-service | customers_db | CRM profiles |
| finance-service | finance_db | Invoices via RabbitMQ consumer |

---

## Quick Start

### Prerequisites
- PHP 8.2+, Composer 2, Docker Desktop, Node.js 20+ (optional, for local frontend dev)

### 1. Scaffold all Laravel services

**Windows:**
```powershell
.\setup.ps1
```

**Linux / macOS / Git Bash:**
```bash
chmod +x setup.sh && ./setup.sh
```

### 2. Start the stack
```bash
docker compose up --build -d
```

> **Note:** Port **8080** is used for the API gateway (8000 may conflict with other projects).

### 3. Open the app

| Service | URL |
|---------|-----|
| **ERP Dashboard** | http://localhost:3000 |
| **API Gateway** | http://localhost:8080/api/v1/ |
| **RabbitMQ UI** | http://localhost:15672 (guest / guest) |

### Demo login
```
Email:    admin@pagecraft.ph
Password: secret123
```

Also seeded: `warehouse@pagecraft.ph`, `sales@pagecraft.ph` (same password).

---

## What Gets Seeded

- **3 users** (admin, warehouse manager, sales agent)
- **6 books** (including one low-stock item)
- **4 customers** (Maria, Juan, Ana, Pedro)

---

## Example: Place an Order

```bash
# Login
TOKEN=$(curl -s -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@pagecraft.ph","password":"secret123"}' | jq -r .token)

# Place order → deducts stock → publishes RabbitMQ → creates invoice
curl -X POST http://localhost:8080/api/v1/orders \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customer_id": 1,
    "customer_name": "Maria Santos",
    "shipping_address": "123 Colon St, Cebu City",
    "payment_method": "gcash",
    "items": [{ "book_id": 1, "quantity": 2 }]
  }'
```

**Flow:** Gateway → Order Service → Inventory (REST stock check/deduct) → RabbitMQ `order.created` → Finance Consumer creates invoice → Customer stats updated.

---

## Project Structure

```
Bookstore-ERP/
├── service-files/          # Source templates (copied by setup)
│   ├── shared/             # Middleware, health check, CORS
│   ├── auth-service/
│   ├── inventory-service/
│   ├── order-service/
│   ├── customer-service/
│   ├── finance-service/
│   └── api-gateway/
├── frontend/               # React + Vite ERP dashboard
├── scripts/                # Post-scaffold Laravel configuration
├── setup.ps1 / setup.sh    # One-time scaffold scripts
├── docker-compose.yml      # Full stack orchestration
└── Dockerfile              # Shared PHP 8.4 image
```

After setup, six Laravel project folders are created (`auth-service/`, etc.) — these are gitignored and built into Docker images.

---

## Roles

| Role | Inventory | Orders | Customers | Finance |
|------|-----------|--------|-----------|---------|
| admin | CRUD | all | CRUD | all |
| warehouse_manager | CRUD | view/update status | — | — |
| sales_agent | view | CRUD | CRUD | view |
| customer | view | own | — | — |

Enforced via `CheckRole` middleware on each service.

---

## Local Frontend Development

```bash
cd frontend
npm install
npm run dev    # http://localhost:3000, proxies /api → :8000
```

For local dev against Docker gateway on 8080, set in `frontend/.env`:
```
VITE_API_URL=http://localhost:8080/api/v1
```

---

## Capstone Documentation

| Deliverable | File |
|-------------|------|
| Business documentation | [docs/business-documentation.md](docs/business-documentation.md) → export PDF |
| Technical documentation | [docs/technical-documentation.md](docs/technical-documentation.md) → export PDF |
| OpenAPI spec | [docs/openapi.yaml](docs/openapi.yaml) |
| Deployment guide | [docs/deployment-guide.md](docs/deployment-guide.md) |
| Brief compliance matrix | [docs/BRIEF-COMPLIANCE.md](docs/BRIEF-COMPLIANCE.md) |

## Tech Stack

- **Backend:** Laravel 10, PHP 8.4, JWT (tymon/jwt-auth)
- **Messaging:** RabbitMQ (php-amqplib)
- **Database:** MySQL 8.0 (one per service)
- **Frontend:** React 18, Vite, React Router
- **Infra:** Docker Compose
