# Project Brief Compliance Matrix

Mapping to **Endterm Mini ERP System — Microservices Architecture and Deployment**.

| Brief requirement | Section | Status | Evidence |
|-------------------|---------|--------|----------|
| Fictional business (online bookstore) | 3 | ✅ | PageCraft Books — `docs/business-documentation.md` §1 |
| Company profile | 3.2 | ✅ | Business doc §1 |
| Organizational structure | 3.2 | ✅ | Business doc §2 |
| ≥5 core business processes + diagrams | 3.2 | ✅ | Business doc §3 (6 processes, Mermaid) |
| Pain points | 3.2 | ✅ | Business doc §4 |
| User roles & permissions matrix | 3.2 | ✅ | Business doc §5 |
| ≥4 functional modules + auth | 4.1 | ✅ | Auth, Inventory, Orders, CRM, Finance |
| Independent microservices | 4.2 | ✅ | 6 deployable Laravel apps |
| Dedicated DB per service (no shared DB) | 4.2 | ✅ | 5 MySQL instances in `docker-compose.yml` |
| API Gateway | 4.2 | ✅ | `api-gateway` service |
| Inter-service REST | 4.2 | ✅ | Order → Inventory, Order → Customer |
| Inter-service message broker | 4.2 | ✅ | RabbitMQ `order.created` → Finance |
| JWT authentication | 4.2 | ✅ | tymon/jwt-auth |
| Authorization on protected routes | 4.2 | ✅ | `CheckRole`, `AuthenticateGateway` |
| Docker containers per service | 4.2 | ✅ | `Dockerfile` in each service |
| Docker Compose orchestration | 4.2 | ✅ | `docker-compose.yml` (14 containers) |
| Functional frontend | 4.2 | ✅ | React ERP at `:3000` |
| API versioning `/api/v1` | Rec. §8 | ✅ | All routes |
| OpenAPI / API spec | 5 | ✅ | `docs/openapi.yaml` |
| Business documentation PDF | 5 | ⚠️ | Markdown ready — export to PDF |
| Technical documentation PDF | 5 | ⚠️ | Markdown ready — export to PDF |
| Deployment guide | 5 | ✅ | `docs/deployment-guide.md` |
| Public deployment URL | 4.2, 5 | ❌ | **You must deploy to VPS/cloud** |
| Demo video | 5 | ❌ | Script in deployment guide §5 |
| Git repository | 5 | ⚠️ | Push to GitHub; grant instructor access |
| Original work (not Odoo/ERPNext fork) | 7 | ✅ | Custom Laravel microservices |

## Microservices confirmation

This project **is** a microservices architecture:

- **Separate codebases** — one Laravel app per bounded context  
- **Separate databases** — strict isolation, no shared tables  
- **Independent deployment** — each service has its own Docker image  
- **API Gateway** — single client entry point  
- **Hybrid communication** — sync REST where consistency matters; async MQ for invoicing  

## Remaining actions for full submission

1. Export `docs/business-documentation.md` and `docs/technical-documentation.md` to PDF  
2. Deploy to a public URL (see `docs/deployment-guide.md` §3)  
3. Record 5–10 minute demo video  
4. Push repo and submit via Google Classroom  
