# PageCraft Books — Technical Documentation

**Mini ERP | Microservices Architecture**

---

## 1. Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                     Client (React ERP UI :3000)                  │
└────────────────────────────┬────────────────────────────────────┘
                             │ HTTPS /api/v1/*
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│  api-gateway (:8080)  — JWT validation, request routing          │
└───┬─────────┬─────────┬─────────┬─────────┬─────────────────────┘
    │         │         │         │         │
    ▼         ▼         ▼         ▼         ▼
 auth     inventory   order    customer   finance
 :8000     :8000      :8000     :8000      :8000
    │         │         │         │         │
    ▼         ▼         ▼         ▼         ▼
 mysql-    mysql-     mysql-    mysql-     mysql-
 auth      inventory  orders    customers  finance

Inter-service:
  order ──REST──► inventory   (stock check / deduct / restore)
  order ──REST──► customer    (record order stats)
  order ──RabbitMQ──► finance (order.created → invoice)
  *     ──REST──► auth        (/auth/me for JWT validation)
```

### Brief compliance (Section 4)

| Requirement | Implementation |
|-------------|----------------|
| ≥4 functional modules + auth | Auth + Inventory + Orders + CRM + Finance (5 modules) |
| Independent services & DBs | 6 Laravel apps, 5 MySQL databases, no shared DB |
| API Gateway | Laravel proxy (`GatewayController`) |
| Inter-service comms (≥2) | REST (Order↔Inventory, Order↔Customer) + RabbitMQ (Order→Finance) |
| JWT + access control | tymon/jwt-auth; `AuthenticateGateway`, `CheckRole` |
| Docker containers | `Dockerfile` per service (PHP 8.4 Alpine) |
| Orchestration | Docker Compose (14 containers) |
| Frontend | React 18 + Vite ERP dashboard |
| API versioning | `/api/v1/` prefix on all routes |

---

## 2. Microservices

| Service | Repository path | Database | Responsibility |
|---------|-----------------|----------|----------------|
| api-gateway | `api-gateway/` | None | Route, authenticate, forward |
| auth-service | `auth-service/` | `auth_db` | Register, login, JWT, users |
| inventory-service | `inventory-service/` | `inventory_db` | Books CRUD, stock ops, alerts |
| order-service | `order-service/` | `orders_db` | Orders, saga (stock + events) |
| customer-service | `customer-service/` | `customers_db` | CRM, customer stats |
| finance-service | `finance-service/` | `finance_db` | Invoices, revenue reports |

Source templates live in `service-files/`; `setup.ps1` / `setup.sh` scaffolds Laravel 10 projects.

---

## 3. Inter-Service Communication Justification

### REST (synchronous) — Order → Inventory

**Why REST:** Stock availability must be verified and decremented *before* an order is confirmed. A message queue would introduce eventual consistency and risk overselling.

**Endpoints used:**
- `GET /api/v1/books/{id}/stock`
- `PATCH /api/v1/books/{id}/stock/deduct`
- `PATCH /api/v1/books/{id}/stock/restore`

**Auth:** Internal service token (`INTERNAL_SERVICE_TOKEN`) via `VerifyServiceAuth` middleware.

### RabbitMQ (asynchronous) — Order → Finance

**Why RabbitMQ:** Invoice creation is not on the critical path for checkout. Async processing decouples Finance from Order latency and allows retries if Finance is temporarily unavailable.

**Queue:** `order.created` (durable)  
**Consumer:** `finance-consumer` container runs `php artisan consume:orders`

### REST — Order → Customer

**Why REST:** Customer `total_orders` and `lifetime_value` should update promptly after a successful order; simple PATCH to Customer Service.

---

## 4. Authentication & Authorization

1. Client sends `Authorization: Bearer <JWT>` to API Gateway.
2. Gateway calls `GET auth-service/api/v1/auth/me` to validate token.
3. Downstream services repeat validation via `VerifyJwtToken` (or accept internal token for service routes).
4. `CheckRole` middleware enforces permissions per route.

**JWT claims:** `sub`, `role`, `email`, `name`

---

## 5. Data Models (per service)

### auth_db — `users`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | |
| email | string unique | |
| password | string hashed | |
| role | enum | admin, warehouse_manager, sales_agent, customer |

### inventory_db — `books`

| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| isbn | string unique | |
| title, author, genre | string | |
| price, cost_price | decimal | |
| stock_qty, reorder_level | int | Soft deletes enabled |

### orders_db — `orders`, `order_items`

Orders store denormalized `customer_name`; items store `book_id`, `quantity`, `unit_price`, `subtotal` at time of sale.

### customers_db — `customers`

Includes `total_orders`, `lifetime_value`, `status` (new, active, vip, inactive).

### finance_db — `invoices`

`order_id` unique (idempotent invoice creation); `status`: pending, paid, voided.

**No cross-database foreign keys** — services reference IDs only.

---

## 6. Technology Choices

| Layer | Choice | Justification |
|-------|--------|---------------|
| Language | PHP 8.4 | Team familiarity; strong Laravel ecosystem |
| Framework | Laravel 10 | Mature REST, migrations, middleware, Docker-friendly |
| Auth | tymon/jwt-auth | Stateless JWT fits microservices; no shared session store |
| Message broker | RabbitMQ | Brief-approved; simple queue for order.created events |
| Database | MySQL 8.0 | Reliable relational store per service |
| HTTP client | Guzzle (Laravel Http) | Service-to-service REST |
| Frontend | React + Vite | SPA for ERP; nginx proxies `/api` to gateway |
| Containers | Docker Compose | Meets minimum orchestration requirement |

Polyglot note: All services use PHP/Laravel for consistency and faster capstone delivery; boundaries remain independent and could be rewritten per service in future.

---

## 7. API Specification

Full OpenAPI 3.0 spec: [`docs/openapi.yaml`](openapi.yaml)

Import into Postman or Swagger UI for interactive testing.

**Base URL (local):** `http://localhost:8080/api/v1`

---

## 8. Logging & Health

| Endpoint | Service | Purpose |
|----------|---------|---------|
| `GET /api/health` | All services | Liveness + DB connectivity |
| Request logging | All services | `LogRequest` middleware → Laravel log |

Gateway adds headers: `X-Gateway`, `X-Service` on proxied responses.

---

## 9. Local Development

See [`deployment-guide.md`](deployment-guide.md).

```powershell
.\setup.ps1
docker compose up --build -d
```

- ERP UI: http://localhost:3000  
- API: http://localhost:8080/api/v1/  
- RabbitMQ UI: http://localhost:15672  

---

## 10. Repository Structure

```
Bookstore-ERP/
├── service-files/     # Microservice source templates
├── api-gateway/       # Generated by setup (gitignored)
├── auth-service/
├── inventory-service/
├── order-service/
├── customer-service/
├── finance-service/
├── frontend/          # React ERP UI
├── docs/              # Submission documentation
├── docker-compose.yml
├── Dockerfile
├── setup.ps1 / setup.sh
└── scripts/configure-service.php
```

---

*Export this document to PDF for submission (Section 5 of the project brief).*
