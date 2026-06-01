import os

files = [
    "api-gateway/docker-entrypoint.sh",
    "auth-service/docker-entrypoint.sh",
    "customer-service/docker-entrypoint.sh",
    "finance-service/docker-entrypoint.sh",
    "inventory-service/docker-entrypoint.sh",
    "order-service/docker-entrypoint.sh",
    "reporting-service/docker-entrypoint.sh",
    "supplier-service/docker-entrypoint.sh"
]

for file in files:
    if os.path.exists(file):
        with open(file, "r", encoding="utf-8") as f:
            content = f.read()
            content = content.replace("sleep 5\n\n    echo \"→ Running migrations...\"", "sleep $(( (RANDOM % 15) + 5 ))\n\n    echo \"→ Running migrations...\"")
        with open(file, "w", encoding="utf-8", newline="\n") as f:
            f.write(content)
        print(f"Updated {file}")
