<?php

function showAdminPanel($chat_id, $panel) {
    bot("sendMessage", [
        "chat_id" => $chat_id,
        "text" => "🔧 Admin panelga hush kelibsiz:",
        "reply_markup" => $panel
    ]);
    return;
}

function generateUniqueMovieId($connect) {
    do {
        $random_id = rand(1, 9999);
        $check = $connect->query("SELECT 1 FROM movies_data WHERE movie_id = '$random_id' LIMIT 1");
    } while ($check && $check->num_rows > 0);

    return $random_id;
}



function uploadMovie($cid, $video, $connect, $glChannel, $baseChannel, $panel_movie, $codeType) {
    global $bot;

    if (!$video) {
        sendMessage($cid, "⚠️ <b>Video yuborilmadi!</b>\n\n<i>Iltimos, kino videosini yuboring yoki asosiy boshqaruvga qaytish uchun pasdagi tugmani bosing</i>");
        return;
    }

    $file_id = $video->file_id;
    $title = getTemp($cid, "movie_name");
    $about = getTemp($cid, "movie_about");

    if ($codeType === "random") {
        $kino_id = generateUniqueMovieId($connect);
    } else {
        $counter_file = "step/counter.txt";
        $kino_id = file_exists($counter_file) ? (int)file_get_contents($counter_file) + 1 : 1;
        file_put_contents($counter_file, $kino_id);
    }

    $caption = "<b>🎬 Nomi:</b> $title\n\n$about\n\n🆔 Kino kodi: <code>$kino_id</code>\n\n🤖 Filmlar olami: @$bot";
    $sent = sendVideo($baseChannel, $file_id, $caption);

    if (!isset($sent->result->message_id)) {
        sendMessage($cid, "⚠️ <b>Kanalga yuborishda xatolik!</b>");
        return;
    }

    $message_id = $sent->result->message_id;

    $sql = "INSERT INTO movies_data (movie_id, movie_name, movie_bid, movie_download)
        VALUES ('$kino_id', '" . base64_encode($title) . "', '$message_id', 0);";

    if ($connect->query($sql) === true) {
        sendMessage($cid, "✅ <b>Kino botga va kanalingizga joylandi!</b>\n\n<b>🆔 Film IDsi:</b> <code>$kino_id</code>", $panel_movie);

        if ($glChannel && $glChannel != '-100') {
            $glChannelTitle = $glChannel;
            $chatInfo = bot("getChat", ["chat_id" => $glChannel]);
            if ($chatInfo->ok && isset($chatInfo->result->title)) {
                $glChannelTitle = $chatInfo->result->title;
            }

            sendMessage($cid, "<b>🔗 Ushbu kinoni <b>$glChannelTitle</b>'ga ham yuborishni xohlaysizmi?</b>",
                json_encode([
                    "inline_keyboard" => [
                        [["text" => "✅ Yuborish", "callback_data" => "sms_{$kino_id}"]],
                    ],
                ])
            );
        }

    } else {
        sendMessage($cid, "⚠️ <b>SQL Xatolik!</b>\n\n<code>{$connect->error}</code>", $panel_movie);
        if ($codeType !== "random") {
            $kino_id--;
            file_put_contents($counter_file, $kino_id);
        }
    }

    step($cid, "none");
    clearTemp($cid);
}




function showBotStatusPanel($cid, $mid) {
    global $connect;
    $result = $connect->query("SELECT bot_status FROM settings LIMIT 1");
    $row = $result->fetch_assoc();
    $holat = $row["bot_status"];

    if ($holat == "on") {
        $xolat = "❌ O'chirish";
        $holat = "✅ Yoqilgan";
    } else {
        $xolat = "✅ Yoqish";
        $holat = "❌ O'chiq";
    }

    replyMessage($cid, $mid, "*️⃣ Hozirgi holati: $holat", json_encode([
        "inline_keyboard" => [
            [["text" => $xolat, "callback_data" => "change_status=bot_status"]],
        ],
    ]));
}

function toggleBotStatus($cid, $mid2) {
    global $connect;

    $result = $connect->query("SELECT * FROM settings WHERE id = 1");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $bot_status = $row["bot_status"];

        if ($bot_status == "on") {
            $new_status = "off";
            $holat = "❌ O'chiq";
        } else {
            $new_status = "on";
            $holat = "✅ Yoniq";
        }

        if (mysqli_query($connect, "UPDATE settings SET bot_status = '$new_status' WHERE id=1")) {
            editMessage($cid, $mid2, "<b>Yangi holat o'rnatildi: $holat</b>", json_encode([
                "inline_keyboard" => [
                    [[
                        "text" => $new_status == "on" ? "❌ O'chiq" : "✅ Yoniq",
                        "callback_data" => "change_status=bot_status"
                    ]],
                ],
            ]));
        } else {
            replyMessage($cid, $mid2, "<b>❌ Xatolik yuz berdi! Bot holati o'zgartirilmadi.</b>");
        }
    } else {
        replyMessage($cid, $mid2, "<b>❌ Bot holatini o'zgartirishda xatolik yuz berdi.</b>");
    }
}



function processAddAdmin($cid, $text) {
    global $connect, $owners, $panel_admin, $back_panel;

    if (!is_numeric($text)) {
        sendMessage($cid, "<b>⚠️ Noto‘g‘ri ID!</b>\n\nIltimos, faqat raqam kiriting.", $back_panel);
        return;
    }

    $result = $connect->query("SELECT * FROM users WHERE user_id = '$text'");
    $row = $result->fetch_assoc();

    if (!$row) {
        sendMessage($cid, "<b>Ushbu foydalanuvchi botdan foydalanmaydi!</b>\n\nBoshqa ID raqamni kiriting:", $back_panel);
    } elseif (!in_array($text, $owners)) {
        $insert = $connect->query("INSERT INTO admins (user_id) VALUES ('$text')");
        if ($insert) {
            sendMessage($cid, "<code>$text</code> <b>adminlar ro'yxatiga qo'shildi!</b>", $panel_admin);
        } else {
            sendMessage($cid, "<b>❌ Xatolik yuz berdi:</b>\n\n<code>{$connect->error}</code>", $panel_admin);
        }
    } else {
        sendMessage($cid, "<b>Ushbu foydalanuvchi allaqachon adminlar ro'yxatida mavjud!</b>", $panel_admin);
    }

    step($cid, "none");
}

function promptRemoveAdmin($cid, $mid) {
    global $connect;

    $result = $connect->query("SELECT * FROM admins");
    if ($result->num_rows > 0) {
        $i = 1;
        $response = "";
        $uz = [];
        while ($row = $result->fetch_assoc()) {
            $name = bot("getchat", ["chat_id" => $row["user_id"]])->result->first_name;
            $response .= "<b>$i.</b> <a href='tg://user?id=" . $row["user_id"] . "'>$name</a>\n";
            $uz[] = ["text" => "$i", "callback_data" => "remove-admin=" . $row["user_id"]];
            $i++;
        }
        $keyboard2 = array_chunk($uz, 3);
        replyMessage($cid, $mid, "<b>👉 O'chirmoqchi bo'lgan administratorni tanlang:</b>\n\n$response", json_encode(["inline_keyboard" => $keyboard2]));
    } else {
        replyMessage($cid, $mid, "<b>Administratorlar mavjud emas</b>");
    }
}

function processRemoveAdmin($cid, $data, $qid) {
    global $connect, $panel_admin;

    $user_id = explode("=", $data)[1];
    if ($user_id <= 0) {
        answerCallback($qid, "❌ Noto‘g‘ri administrator ID raqami!", true);
        return;
    }

    $result = $connect->query("SELECT * FROM admins WHERE user_id = '$user_id'");
    if ($result && $result->num_rows > 0) {
        $delete = $connect->query("DELETE FROM admins WHERE user_id = '$user_id'");
        if ($delete) {
            deleteMessage();
            sendMessage($cid, "<code>$user_id</code> <b>adminlar ro'yxatidan olib tashlandi!</b>", $panel_admin);
        } else {
            sendMessage($cid, "<b>⚠️ Administratorni o‘chirishda xatolik yuz berdi:</b>\n\n<code>{$connect->error}</code>", $panel_admin);
        }
    } else {
        answerCallback($qid, "❌ Ushbu foydalanuvchi administratorlar ro'yxatida mavjud emas!", true);
    }
}

function listAllAdmins($cid, $mid) {
    global $connect;

    $res = $connect->query("SELECT * FROM admins");
    if ($res->num_rows > 0) {
        $key = [];
        while ($a = $res->fetch_assoc()) {
            $user = $a["user_id"];
            $name = bot("getchat", ["chat_id" => $user])->result->first_name;
            $key[] = ["text" => "$name", "url" => "tg://user?id=$user"];
        }
        $keyboard2 = array_chunk($key, 1);
        replyMessage($cid, $mid, "<b>👉 Barcha adminlar ro'yxati:</b>", json_encode(["inline_keyboard" => $keyboard2]));
    } else {
        replyMessage($cid, $mid, "<b>Administratorlar mavjud emas</b>");
    }
}


function sendStatistics($cid, $mid, $edit = false) {
    global $connect;

    $today = date("Y-m-d");
    $month_start = date("Y-m-01");
    $now = date("Y-m-d H:i:s");

    $sql = "
        SELECT 
            (SELECT COUNT(*) FROM users) AS total_users,
            (SELECT COUNT(*) FROM movies_data) AS movies_count,
            (SELECT COUNT(*) FROM users WHERE DATE(time) = '$today') AS joined_today,
            (SELECT COUNT(*) FROM users WHERE time BETWEEN '$month_start 00:00:00' AND '$now') AS joined_this_month
    ";

    $res = $connect->query($sql);

    if ($res && $row = $res->fetch_assoc()) {
        $load = sys_getloadavg()[0];
        $current_time = date("H:i:s");
        $current_date = date("d.m.Y");

        $msg = "💡 <b>O'rtacha yuklanish:</b> <code>$load</code>

• <b>Barcha odamlar:</b> {$row['total_users']} ta 
• <b>Bugun qo'shilganlar:</b> {$row['joined_today']} ta
• <b>Shu oy qo'shilganlar:</b> {$row['joined_this_month']} ta

• <b>Jami yuklangan kitoblar:</b> {$row['movies_count']} ta
<b>⏰ Soat:</b> $current_time | <b>📆 Sana:</b> $current_date";

        $keyboard = json_encode([
            "inline_keyboard" => [
                [["text" => "🔄 Yangilash", "callback_data" => "upstat"]],
            ]
        ]);

        if ($edit) {
            editMessage($cid, $mid, $msg, $keyboard);
        } else {
            replyMessage($cid, $mid, $msg, $keyboard);
        }
    }
}



function handleDeleteMovieStep($cid, $text, $mid) {
    global $connect, $panel_movie;

    if (!is_numeric($text)) {
        sendMessage($cid, "<b>❌ Noto‘g‘ri kino ID!</b>\n\nIltimos, raqam kiriting.", $panel_movie);
        return;
    }

    $checkSql = "SELECT * FROM movies_data WHERE movie_id = '$text'";
    $checkResult = $connect->query($checkSql);

    if ($checkResult && $checkResult->num_rows > 0) {
        if ($checkResult->num_rows > 1) {
            $texts = "<b>🔍 Bir nechta kino topildi, o‘chirish uchun birini tanlang:</b>\n\n";
            $buttons = [];
            $counter = 1;

            while ($row = $checkResult->fetch_assoc()) {
                $title = base64_decode($row["movie_name"]);
                $texts .= "<b>$counter.</b> $title\n";
                $buttons[] = [
                    "text" => (string)$counter,
                    "callback_data" => "deleteserie_" . $row["id"]
                ];
                $counter++;
            }

            $keyboard = array_chunk($buttons, 5);
            $keyboard[] = [["text" => "🗑 Hammasini o'chirish", "callback_data" => "deleteAll_" . $text]];
            $keyboard[] = [["text" => "🔙 Orqaga", "callback_data" => "cancelDeleteStep"]];

            replyMessage($cid, $mid, $texts, json_encode(["inline_keyboard" => $keyboard]));
        } else {
            $row = $checkResult->fetch_assoc();
            $sql = "DELETE FROM movies_data WHERE id = '{$row['id']}'";
            if ($connect->query($sql) === true) {
                replyMessage($cid, $mid, "<b>✅ Kino muvaffaqiyatli o‘chirildi!</b>", $panel_movie);
            } else {
                replyMessage($cid, $mid, "<b>❌ Kino o‘chirishda xatolik yuz berdi!</b>\n\n<code>{$connect->error}</code>", $panel_movie);
            }
        }
    } else {
        sendMessage($cid, "<b>❌ Ushbu ID bo‘yicha kino topilmadi!</b>\n\nIltimos, to‘g‘ri kino ID kiriting.", $panel_movie);
    }

    step($cid, "none");
}

function deleteSingleSerie($cid, $data) {
    global $connect, $panel_movie;

    $deleteId = explode("_", $data)[1];

    $sql = "DELETE FROM movies_data WHERE id = '$deleteId'";
    if ($connect->query($sql) === true) {
        sendMessage($cid, "<b>✅ Serial muvaffaqiyatli o‘chirildi!</b>", $panel_movie);
    } else {
        sendMessage($cid, "<b>❌ Serial o‘chirishda xatolik yuz berdi!</b>\n\n<code>{$connect->error}</code>", $panel_movie);
    }

    step($cid, "none");
}

function deleteAllSeries($cid, $data, $mid2) {
    global $connect, $panel_movie, $back_panel;

    $movie_id = explode("_", $data)[1];
    deleteMessage($cid, $mid2); 

    $sql = "DELETE FROM movies_data WHERE movie_id = '$movie_id'";
    if ($connect->query($sql) === true) {
        sendMessage($cid, "<b>✅ Barcha kinolar muvaffaqiyatli o‘chirildi!</b>", $panel_movie);
    } else {
        sendMessage($cid, "<b>❌ Barcha kinolarni o‘chirishda xatolik yuz berdi!</b>\n\n<code>{$connect->error}</code>", $back_panel);
    }

    step($cid, "none");
}


function handleSeriesIdStep($cid, $text, $connect, $back_panel, $panel_movie) {
    if (!is_numeric($text)) return;

    $checkSql = "SELECT * FROM movies_data WHERE movie_id = '$text'";
    $checkResult = $connect->query($checkSql);

    if ($checkResult && $checkResult->num_rows > 0) {
        setTemp($cid, "series_id", $text);
        sendMessage($cid, "<b>🍿 Serial nomi va qismni kiriting:</b>\n\n✏️ Masalan: \n<code>Flesh 123-qism</code>", $back_panel);
        step($cid, "name_series");
    } else {
        sendMessage($cid, "<b>❌ Bunday serial mavjud emas!</b>\n\nIltimos, mavjud serial ID raqamini kiriting yoki qayta urinib ko'ring.", $panel_movie);
        step($cid, "none");
    }
}

function handleSeriesNameStep($cid, $text) {
    setTemp($cid, "series_name", $text);
    sendMessage($cid, "<b>✅ $text qabul qilindi!</b>\n\n<b>🍿 Serial haqida ma’lumotlarni kiriting:</b>\n\nMasalan:\n<code>🌍 Davlati: Davlati\n🌐 Tili: O'zbek tilida\n🎭 Janr: #Jangari, #Sarguzasht\n💿 Sifati: 720p\n📆 Yili: 2024</code>", json_encode([
        "resize_keyboard" => true,
        "keyboard" => [
            [["text" => "◀️ Orqaga"]],
        ]
    ]));
    step($cid, "about_series");
}

function handleSeriesAboutStep($cid, $text, $back_panel) {
    setTemp($cid, "series_about", $text);
    sendMessage($cid, "<i>✅ Serial ma'lumotlari qabul qilindi!</i>\n\n<i>Endi qismni yuboring:</i>", $back_panel);
    step($cid, "add_series");
}

function handleSeriesVideoStep($cid, $message, $connect, $baseChannel, $bot, $panel_movie, $back_panel) {
    if (!isset($message->video)) {
        sendMessage($cid, "⚠️ <b>Video yuborilmadi!</b>\n\n<i>Iltimos, kino videosini yuboring yoki /start buyrug‘i orqali qayting.</i>", $back_panel);
        return;
    }

    $file_id = $message->video->file_id;

    $kino_id = getTemp($cid, "series_id");
    $title = getTemp($cid, "series_name");
    $about = getTemp($cid, "series_about");

    if (empty($kino_id) || empty($title) || empty($about)) {
        sendMessage($cid, "⚠️ <b>Ma'lumotlar to‘liq emas!</b>\n\nIltimos, boshidan urinib ko‘ring.", $panel_movie);
        step($cid, "none");
        clearTemp($cid);
        return;
    }

    $caption = "<b>🎬 Nomi: $title</b>\n\n$about\n\n🆔 Kino kodi: <code>$kino_id</code>\n\n🤖 Filmlar olami: @$bot";
    $sent = sendVideo($baseChannel, $file_id, $caption);

    if (!isset($sent->result->message_id)) {
        sendMessage($cid, "⚠️ <b>Kanalga yuborishda xatolik!</b>\n\nIltimos, kanalda bot adminmi yoki noto‘g‘ri fayl emasligini tekshirib ko‘ring.", $panel_movie);
        step($cid, "none");
        return;
    }

    $message_id = $sent->result->message_id;
    $encoded_title = base64_encode($title);

    $sql = "INSERT INTO movies_data (movie_id, movie_name, movie_bid, movie_download) 
            VALUES ('$kino_id', '$encoded_title','$message_id', 0);";

    if ($connect->query($sql) === true) {
        sendMessage($cid, "✅ <b>Serial qismi muvaffaqiyatli yuklandi!</b>\n\n🆔 Serial ID: <code>$kino_id</code>", $panel_movie);
    } else {
        sendMessage($cid, "⚠️ <b>Ma'lumotlar bazasiga yozishda xatolik:</b>\n<code>{$connect->error}</code>", $panel_movie);
    }

    step($cid, "none");
    clearTemp($cid);
}




function handleRequestChannel($cid, $type, $back_panel) {
    file_put_contents("step/$cid.type", $type);
    step($cid, "addChannel");
    sendMessage($cid, "<b>🔗 Iltimos, kanalingizga botni admin qilib qo'ying va kanaldan \"Forward\" xabar yuboring:</b>", $back_panel);
}

function addChannelStep($message, $cid, $connect, $panel) {
    if (!isset($message->forward_origin)) return;

    $kanal_id = $message->forward_origin->chat->id;
    $type = file_get_contents("step/$cid.type");

    if ($type == "true") {
        $link = bot("createChatInviteLink", [
            "chat_id" => $kanal_id,
            "creates_join_request" => true,
        ])->result->invite_link;
        $sql = "INSERT INTO `channels` (`channel_id`, `link`, `type`) VALUES ('$kanal_id', '$link', 'request')";
    } else {
        $link = "https://t.me/" . $message->forward_origin->chat->username;
        $sql = "INSERT INTO `channels` (`channel_id`, `link`, `type`) VALUES ('$kanal_id', '$link', 'lock')";
    }

    if ($connect->query($sql)) {
        sendMessage($cid, "<b>✅ Kanal muvaffaqiyatli qo‘shildi</b>", $panel);
    } else {
        sendMessage($cid, "<b>⚠️ Kanalni qo‘shishda xatolik yuz berdi!</b>\n\n<code>{$connect->error}</code>", $panel);
    }

    unlink("step/$cid.type");
    step($cid, "none");
}

function addOtherChannelStep($cid, $text, $connect, $panel, $back_panel) {
    if (strpos($text, "http") === false) {
        sendMessage($cid, "<b>⚠️ Noto'g'ri havola\n\n♻️ Qayta urunib ko'ring.</b>", $panel);
        unlink("step/$cid.type");
        return;
    }

    $check_sql = "SELECT COUNT(*) FROM `channels` WHERE `link` = '$text' AND `type` = 'other'";
    $result = $connect->query($check_sql);
    $row = $result->fetch_row();

    if ($row[0] > 0) {
        sendMessage($cid, "<b>⚠️ Ushbu link allaqachon qo‘shilgan!</b>", $panel);
    } else {
        file_put_contents("step/$cid.link", $text);
        sendMessage($cid, "<b>📌 Iltimos, ushbu link uchun nom kiriting:</b>", $back_panel);
        step($cid, "addOtherChannelName");
    }

    unlink("step/$cid.type");
}

function saveOtherChannelName($cid, $text, $connect, $panel) {
    $link = file_get_contents("step/$cid.link");
    $randomNumber = rand(1, 99999);
    $sql = "INSERT INTO channels (channel_id, title, link, type) VALUES ('$randomNumber', '$text', '$link', 'other')";

    if ($connect->query($sql) === true) {
        sendMessage($cid, "<b>✅ Kanal muvaffaqiyatli qo'shildi!</b>", $panel);
    } else {
        sendMessage($cid, "<b>❌ Xatolik yuz berdi!</b>\n\n<code>{$connect->error}</code>", $panel);
    }

    unlink("step/$cid.link");
    step($cid, "none");
}




function setTemp($cid, $key, $value) {
    $file = __DIR__ . "/../step/temp_$cid.json";
    $data = file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    $data[$key] = $value;
    file_put_contents($file, json_encode($data));
}

function getTemp($cid, $key) {
    $file = __DIR__ . "/../step/temp_$cid.json";
    if (!file_exists($file)) return null;
    $data = json_decode(file_get_contents($file), true);
    return $data[$key] ?? null;
}

function clearTemp($cid) {
    $file = __DIR__ . "/../step/temp_$cid.json";
    if (file_exists($file)) unlink($file);
}



