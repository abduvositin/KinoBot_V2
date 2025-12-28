# 🎬 KinoBot V2 — Professional Kino Bot Skripti

Telegram bot orqali kino va video kontentlarni boshqarish uchun mo‘ljallangan mukammal tizim.

## 🚀 Asosiy Imkoniyatlar
* 🎥 **Kontent boshqaruvi:** Kino va videolarni tizimli joylash.
* 🤖 **Telegram API:** Bot API-ning eng so‘nggi imkoniyatlari.
* ⏱ **Cron tizimi:** Xabarlarni avtomatik yuborish mexanizmi.
* ⚡️ **Webhook:** Tezkor va barqaror javob qaytarish.
* 👮 **Admin Panel:** To‘liq boshqaruv imkoniyati.

---

## ⚙️ O‘rnatish va Sozlash

Botni to‘g‘ri sozlash uchun quyidagi fayllarga o‘zgartirish kiriting:

### 1. Adminlarni tayinlash
**Fayl:** `main.php`  
`$owners` massiviga bot adminlarining Telegram ID raqamlarini kiriting.

### 2. Bot Tokenini ulash
**Fayl:** `core/bot.php`  
`API_TOKEN` o‘rniga [@BotFather](https://t.me/BotFather) dan olingan tokenni yozing.

### 3. Ma’lumotlar bazasi
**Fayl:** `core/sql.php`  
Quyidagi ma’lumotlarni o‘z bazangizga moslang:
* `username` — Baza foydalanuvchi nomi.
* `password` — Baza paroli.
* `database` — Baza nomi.

---

### 🔐 Foydalanish shartlari va Mualliflik huquqi
Ushbu loyihadan foydalanishda quyidagi qoidalarga amal qilish majburiydir:

❌ Sotish taqiqlanadi: Kodni pullik asosda tarqatish qat'iyan man etiladi.
❌ Mualliflikni o‘zgartirish: Skriptdagi matnlarni o‘zgartirish sizni dasturchi qilmaydi. Mualliflik huquqini saqlab qoling.

⚖️ Kod noqonuniy sotilgani aniqlansa choralar ko‘riladi.

✔️ Halollik va mehnatni hurmat qilgan holda foydalaning.


## 🛰 Texnik Bog‘lanma

### ⏱ Cron Job Sozlamasi
Botning avtomatik yuborish funksiyasi ishlashi uchun **Cron** ni har 1 daqiqada quyidagi manzilga so‘rov yuboradigan qilib sozlang:
```text
[https://your-domain.uz/core/send?update=send](https://your-domain.uz/core/send?update=send)
```

