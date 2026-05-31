# Deployment Guide — PageCraft Books Mini ERP

Required deliverable per project brief Section 5.

---

## 1. Prerequisites

| Tool | Version |
|------|---------|
| Docker Desktop | Latest |
| Docker Compose | v2+ |
| Git | Any recent |
| PHP + Composer | 8.2+ (for initial `setup.ps1` only) |

---

## 2. Local Deployment

### Step 1 — Clone and scaffold

```powershell
git clone <your-repo-url> Bookstore-ERP
cd Bookstore-ERP
.\setup.ps1
```

Linux/macOS: `chmod +x setup.sh && ./setup.sh`

### Step 2 — Start stack

```powershell
docker compose up --build -d
```

Wait ~60 seconds for MySQL health checks and migrations.

### Step 3 — Verify

| Check | URL / Command |
|-------|---------------|
| ERP UI | http://localhost:3000 |
| API Gateway | http://localhost:8080/api/v1/auth/login |
| Health | http://localhost:8080/api/health |
| RabbitMQ | http://localhost:15672 (guest/guest) |

```powershell
# Login test
Invoke-RestMethod -Uri "http://localhost:8080/api/v1/auth/login" `
  -Method POST -ContentType "application/json" `
  -Body '{"email":"admin@pagecraft.ph","password":"secret123"}'
```

### Step 4 — Stop

```powershell
docker compose down
```

Data persists in Docker volumes. To reset: `docker compose down -v`

---

## 3. Public Deployment (Required for Submission)

The brief requires a **live URL** accessible through presentation day. Options:

### Option A — VPS (DigitalOcean, Linode, AWS EC2)

1. Provision Ubuntu 22.04+ server (minimum 4 GB RAM recommended for 14 containers).
2. Install Docker and Docker Compose.
3. Clone repo, run `setup.sh`, then `docker compose up --build -d`.
4. Open firewall ports **80**, **443** (and **8080** if not using reverse proxy).
5. Point domain A record to server IP.
6. Use **nginx** or **Caddy** reverse proxy:
   - `yourdomain.com` → frontend:80
   - `yourdomain.com/api` → api-gateway:8000

### Option B — Railway / Render (simpler, may need service splitting)

For full Compose stack, a VPS is more reliable. Alternatively deploy gateway + one service as proof, document full stack runs locally.

### Option C — Docker Compose on cloud VM (recommended for capstone)

Same as Option A; document URL in README and Google Classroom submission.

### Production checklist

- [ ] Replace `JWT_SECRET` and `INTERNAL_SERVICE_TOKEN` in `docker-compose.yml`
- [ ] Set strong MySQL passwords
- [ ] Enable HTTPS (Let's Encrypt via Caddy/Certbot)
- [ ] Restrict RabbitMQ management port (15672) to admin IP only
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false` per service

---

## 4. Environment Variables

Key variables (set in `docker-compose.yml` or `.env` per service):

| Variable | Used by |
|----------|---------|
| `JWT_SECRET` | auth-service |
| `INTERNAL_SERVICE_TOKEN` | order, inventory, customer (service-to-service) |
| `AUTH_SERVICE_URL` | gateway, all services |
| `RABBITMQ_*` | order-service, finance-consumer |
| `DB_*` | each service with a database |
| `SEED_DATABASE=true` | auth, inventory, customer (demo data) |

---

## 5. Demo Script (5–10 min video)

Record walking through these flows in the ERP UI (http://localhost:3000):

1. **Login** as admin@pagecraft.ph
2. **Inventory** — show books, low-stock alert on dashboard
3. **Customers** — view CRM list
4. **Orders** — place order for a customer + book
5. **Finance** — show auto-created invoice (wait ~5s for RabbitMQ)
6. **Architecture** — briefly show `docker compose ps` and RabbitMQ UI

---

## 6. Troubleshooting

| Issue | Fix |
|-------|-----|
| Port 8080 in use | Change `8080:8000` in docker-compose or stop conflicting service |
| Auth 500 / DB connection | Rebuild: `docker compose up -d --build auth-service` |
| No invoices | Check `docker logs bookstore-erp-finance-consumer-1` |
| Frontend API errors | Ensure api-gateway is up; frontend nginx proxies `/api` |

---

## 7. Submission Checklist (Brief Section 9)

- [ ] Git repository link (instructor access)
- [ ] Live deployment URL
- [ ] `docs/business-documentation.md` → export PDF
- [ ] `docs/technical-documentation.md` → export PDF
- [ ] `docs/openapi.yaml` (Postman import)
- [ ] Demo video link
- [ ] Team roster + contribution breakdown
