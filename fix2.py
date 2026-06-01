import os

files = [
    "api-gateway/docker-entrypoint.sh",
    "auth-service/docker-entrypoint.sh",
    "customer-service/docker-entrypoint.sh",
    "finance-service/docker-entrypoint.sh",
    "inventory-service/docker-entrypoint.sh",
    "order-service/docker-entrypoint.sh",
    "supplier-service/docker-entrypoint.sh",
    "reporting-service/docker-entrypoint.sh"
]

search1 = 'echo "? Forcing valid APP_KEY generation..."\nphp artisan key:generate --force'
search2 = 'echo "? Forcing valid APP_KEY generation..."\r\nphp artisan key:generate --force'

replace = '''if ! grep -Eq '^APP_KEY=base64:[A-Za-z0-9+/=]{44}$' .env; then
    echo "? Generating valid APP_KEY (provided key is missing or invalid length)..."
    php artisan key:generate --force
fi'''

for f in files:
    with open(f, "r", encoding="utf-8") as file:
        content = file.read()
    
    if search1 in content:
        content = content.replace(search1, replace)
        print("Replaced LF in " + f)
    elif search2 in content:
        content = content.replace(search2, replace)
        print("Replaced CRLF in " + f)
    else:
        print("COULD NOT FIND SEARCH STRING IN " + f)
        
    with open(f, "w", encoding="utf-8", newline="\n") as file:
        file.write(content)
