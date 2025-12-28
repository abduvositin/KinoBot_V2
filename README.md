Asosiy imkoniyatlar

• 🎥 Kino va video kontent bilan ishlash
• 🤖 Telegram Bot API asosida ishlaydi
• ⏱ Cron orqali avtomatik xabar yuborish
• 🔗 Webhook orqali tezkor javob
• 👮 Admin boshqaruvi mavjud

⚙️ O‘rnatish va sozlash
1️⃣ Admin sozlash

Fayl: main.php

$owners ichiga admin Telegram ID kiriting

2️⃣ Bot tokenini ulash

Fayl: core/bot.php

API_TOKEN o‘rniga Telegram bot tokenini yozing

3️⃣ Ma’lumotlar bazasi

Fayl: core/sql.php

Quyidagi ma’lumotlarni to‘ldiring:
• database username
• database password
• database nomi

⏱ Cron sozlamasi

Cron’ni har 1 daqiqada ishlaydigan qilib sozlang:

https://your-domain.uz/core/send?update=send

Bu botning avtomatik yuborish mexanizmi uchun zarur.

🔗 Webhook ulash

Webhook o‘rnatish uchun brauzerda oching:

https://api.telegram.org/bot
<API_TOKEN>/setWebhook?url=https://your-domain.uz/main.php

<API_TOKEN> o‘rniga bot tokeningizni qo‘ying.

🔐 Xavfsizlik va huquq

❌ Ushbu kod sotilmaydi
❌ Kodni o‘zgartirib, o‘zingizni muallif sifatida ko‘rsatish taqiqlanadi

Agar kod noqonuniy tarqatilsa yoki sotilsa, mualliflik huquqi bo‘yicha qonuniy choralar ko‘riladi.

✔️ Foydalanish mumkin, ammo halollik va hurmat asosida.

👨‍💻 Muallif

Abduvosit
Telegram bot va Android dasturchi
