# KinoBot_V2 ishlatish

### Sozlash
- `main.php` → `$owners` → Admin ID
- `core/bot.php` → `API_TOKEN` → Bot tokeni
- `core/sql.php` → Ma’lumotlar bazasi: username, parol, database nomi

### Cron
1 daqiqaga sozlash:  
https://your-domain.uz/core/send?update=send

### Webhook
https://api.telegram.org/bot<API_TOKEN>/setWebhook?url=https://your-domain.uz/main.php
