<?php

echo "Bot ID: " . $bot_id . "\n";



$panel = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "🎬 Kino"], ["text" => "📢 Kanallar"]],
        [["text" => "📊 Statistika"], ["text" => "👨🏻‍💻 Adminlar"]],
        [["text" => "✉️ Xabar jo‘natish"], ["text" => "⚙️ Sozlamalar"]],
        [["text" => "🤖 Bot holati"], ["text" => "◀️ Chiqish"]],
    ]
]);

$panel_movie = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "🎬 Kino yuklash"], ["text" => "🎬 Qism yuklash"]],
        [["text" => "🎬 Kinoni o'zgartirish"], ["text" => "🗑O'chirish"]],
        [["text" => "◀️ Orqaga"]],
    ]
]);

$backmovie_panel = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "❌ Bekor qilish"]],
    ],
]);


$repl_markup = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "◀️ Orqaga"]]
    ],
]);


$panel_channel = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "➕ Kanal qo'shish"], ["text" => "🗑 Kanal o'chirish"]],
        [["text" => "◀️ Orqaga"]],
    ],
]);

$panel_admin = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "➕ Qo'shish"],["text" => "🗑 O‘chirish"]],
        [["text" => "📋 Ro'yxat"],["text" => "◀️ Orqaga"]]
    ],
]);


if ($data == "cancelDeleteStep" && admin($cid) == 1) {
        sendMessage($cid, "❌ Bekor qilindi", $panel_movie);
        step($cid,"none");
}


if ($data == "cancelStep" && admin($cid) == 1) {
    deleteMessage($cid,$mid2);
    sendMessage($cid, "❌ Bekor qilindi", $panel);
    step($cid,"none");
    return;
}


if (($text == "◀️ Orqaga" && $type == "private") && admin($cid) == 1) {
    step($cid,"none");
    showAdminPanel($cid,$panel);
    return;
    
}

if (($text == "◀️ Chiqish" && $type == "private") && admin($cid) == 1) {
    replyMessage($cid, $mid, "<b>❗️ Admin paneldan chiqdingiz /start ni yuboring \n\n🚀 Qaytish uchun /panel ni yuboring</b>", json_encode(["remove_keyboard" => true]));
    step($cid, "none");
}

if (($text == "❌ Bekor qilish" && $type == "private") && admin($cid) == 1) {
    sendMessage($cid, "❌ Bekor qilindi", $panel_movie);
    step($cid,"none");
    clearTemp($cid);
    return;
}


if (($text == "📊 Statistika" && $type == "private") && admin($cid) == 1) {
    sendStatistics($cid, $mid);
    exit();
}

if ($data == "upstat" && admin($cid) == 1) {
    sendStatistics($cid, $mid2, true);
    exit();
}

if (($text == "🤖 Bot holati" && $type == "private") && admin($cid) == 1) {
    showBotStatusPanel($cid, $mid);
}

if (mb_stripos($data, "change_status=") !== false && in_array($cid, $owners)) {
    $change = explode("=", $data)[1];
    if ($change == "bot_status") {
        toggleBotStatus($cid, $mid2);
    }
}

if (($text == "👨🏻‍💻 Adminlar" && $type == "private") and admin($cid) == 1 ) {
    if (admin($cid) == 1) {
        replyMessage($cid,$mid,"<b>👨🏻‍💻 Adminlar  bo'limi:</b>",$panel_admin);
    }
}

if (($text == "➕ Qo'shish" && $type == "private" ) and admin($cid) == 1){
    if (in_array($cid, $owners)) {
        replyMessage($cid,$mid, "<b>Kerakli foydalanuvchi ID raqamini yuboring:</b>", $aort);
        step($cid, "add-admin");
    } else {
        replyMessage($cid,$mid,"<b>Ushbu bo'limdan foydalanish siz uchun taqiqlangan!</b>");
    }
}

if ($step == "add-admin" && in_array($cid, $owners)) {
    processAddAdmin($cid, $text);
}

if ($text == "🗑 O‘chirish" && $type == "private" && in_array($cid, $owners)) {
    promptRemoveAdmin($cid, $mid);
}

if (mb_stripos($data, "remove-admin=") !== false && in_array($cid, $owners)) {
    processRemoveAdmin($cid, $data, $qid);
}

if ($text == "📋 Ro'yxat" && $type == "private" && admin($cid) == 1) {
    listAllAdmins($cid, $mid);
}

if (($text == "🎬 Kino" && $type == "private") and admin($cid) == 1) {
    replyMessage($cid,$mid, "<b>🎬  Kino sozlamalari bo'limi!</b>",$panel_movie);
    step($cid, "none");
}

if (($text == "🎬 Kino yuklash"  && $type == "private") and admin($cid) == 1) {
    sendMessage($cid, "<b>🍿 Kino nomini kiriting:</b>\n\n✏️ Masalan: \n<code>Qasoskorlar</code> yoki <code>Loki 1-qism</code>", $backmovie_panel);
    step($cid, "movie_name");
}

if ($step == "movie_name") {
    sendMessage($cid, "<b>✅ $text qabul qilindi!</b>\n\n<b>🍿 Kino haqida ma’lumotlarni kiriting:</b>\n\nMasalan:\n<code>🌍 Davlati: Davlati
🌐 Tili: O'zbek tilida
🎭 Janr: #Jangari, #Sarguzasht
💿 Sifati: 720p
📆 Yili: 2024</code>", $backmovie_panel);

    setTemp($cid, "movie_name", $text); 
    step($cid, "movie_about");
}

if ($step == "movie_about") {
    sendMessage($cid, "<b>✅ $text qabul qilindi!</b>\n\n<i>Kino kodi qanday bo‘lishini tanlang:</i>", json_encode([
        "inline_keyboard" => [
            [["text" => "🔢 Avtomatik (Counter)", "callback_data" => "code=counter"]],
            [["text" => "🎲 Random", "callback_data" => "code=random"]]
        ]
    ]));

    setTemp($cid, "movie_about", $text);
    step($cid, "choose_code_type");
}

if (mb_stripos($data, "code=") !== false and admin($cid) == 1) {
    $code = explode("=", $data)[1];
    setTemp($cid, "movie_code_type", $code);
    step($cid, "movie");
    editMessage($cid, $mid2, "<b>✅ Kino kodi turi:</b> <i>$code</i>\n\n<i>Endi kino videosini yuboring.</i>");

}


if ($step == "movie") {
    if (isset($message->video)) {
        $res = mysqli_query($connect, "SELECT mainChannel FROM settings WHERE id = 1");
        $mainChannel = null;

        if ($res && $row = mysqli_fetch_assoc($res)) {
            $mainChannel = $row['mainChannel'];
        }
        if (!$mainChannel || $mainChannel == '-100') {
            sendMessage($cid, "⚠️ <b>Kanal sozlanmagan!</b>\n\n<i>Iltimos, kinoni yuklab bolgandan keyin Sozlamalardan kanalni to'g'rilang.</i>");
        }

        $codeType = getTemp($cid, "movie_code_type");
        uploadMovie($cid, $message->video, $connect, $mainChannel, $baseChannel, $panel_movie, $codeType);

    } else {
        sendMessage(
            $cid,
            "⚠️ <b>Video yuborilmadi!</b>\n\n<i>Iltimos, kino videosini yuboring yoki asosiy boshqaruvga qaytish uchun pasdagi tugmani bosing</i>",
            $back_panel
        );
    }
}

if (($text == "🗑O'chirish" && $type == "private") and admin($cid) == 1) {
    replyMessage($cid, $mid, "<b>🆔 Iltimos, o‘chirish uchun kino yoki serial IDisini yuboring:</b>", $back_panel);
    step($cid, "deleteMov");
}
    
if ($step == "deleteMov" && is_numeric($text)) {
    handleDeleteMovieStep($cid, $text, $mid);
}

if (mb_stripos($data, "deleteserie_") !== false) {
    deleteSingleSerie($cid, $data);
}

if (mb_stripos($data, "deleteAll_") !== false) {
    deleteAllSeries($cid, $data, $mid2);
}

if (($text == "🎬 Qism yuklash" && $type == "private") and admin($cid) == 1) {
    replyMessage($cid, $mid, "<b>🔍 Qism qo‘shish uchun serial ID raqamini kiriting:</b>", $back_panel);
    step($cid, "series_id");
}

if ($step == "series_id"){
    handleSeriesIdStep($cid, $text, $connect, $back_panel, $panel_movie);
}
if ($step == "name_series"){
    handleSeriesNameStep($cid, $text);
}
if ($step == "about_series"){
    handleSeriesAboutStep($cid, $text, $back_panel);
}
if ($step == "add_series"){
    handleSeriesVideoStep($cid, $message, $connect, $baseChannel, $bot, $panel_movie, $back_panel);
}



if ($text == "✉️ Xabar jo‘natish" and admin($cid) == 1) {
    $result = mysqli_query($connect, "SELECT * FROM `send`");
    $row = mysqli_fetch_assoc($result);
    $status = $row["status"];
    $sends_count = $row["sends_count"];
    $statistics = $row["statistics"];
    $receive_count = $row["receive_count"];
    if (!$row) {
       sendMessage($cid,"<b>📬 Foydalanuvchilarga yuboriladigan xabarni kiriting:</b>",json_encode([
            "inline_keyboard" => [
                [["text" => "🔙 Orqaga", "callback_data" => "cancelStep"]]
            ]
        ]))->result->message_id;
        step($cid,"send");
    } else {
        if ($status == "resume") {
            $kb = json_encode([
                "inline_keyboard" => [
                    [["text" => "To'xtatish ⏸","callback_data" => "sendstatus=stopped",]],
                    [["text" => "🗑 O'chirish","callback_data" => "bekorqilish_send", ]],
                ],
            ]);
        } else if ($status == "stopped") {
            $kb = json_encode([
                "inline_keyboard" => [
                    [[ "text" => "Davom ettirish ▶️","callback_data" => "sendstatus=resume"]],
                    [["text" => "🗑 O'chirish","callback_data" => "bekorqilish_send" ]],
                ],
            ]);
        }
        sendMessage($cid,"<b>✅ Yuborildi:</b> <code>$sends_count/$statistics</code>
<b>📥 Qabul qilindi:</b> <code>$receive_count</code>
<b>🔰 Status</b>: <code>$status</code>", $kb);
    }
}

if ($step == "send" and admin($cid) == 1) {
    $res = mysqli_query($connect, "SELECT * FROM `users` ORDER BY `id` DESC LIMIT 1;");
    $row = mysqli_fetch_assoc($res);
    
    if (!$row) {
        sendMessage($cid,"❌ Xato: `users` jadvalidan ma'lumot olinmadi!",$panel);
        step($cid,"none");
        exit();
    }

    $stop_id = $row["user_id"]; 
    $time1 = date("H:i", strtotime("+1 minutes"));
    $time2 = date("H:i", strtotime("+2 minutes"));
    $tugma = json_encode($update->message->reply_markup);
    $reply_markup = base64_encode($tugma);
    $stat = $connect->query("SELECT * FROM users")->num_rows;

    $edit_mess_id = sendMessage(
        $cid,
        "<b>✅ Yuborildi:</b> <code>0/$stat</code>
<b>📥 Qabul qilindi:</b> <code>0</code>
<b>🔰 Status</b>: <code>resume</code>",
        json_encode([
            "inline_keyboard" => [
                [["text" => "To'xtatish ⏸", "callback_data" => "sendstatus=stopped"]],
                [["text" => "🗑 O'chirish", "callback_data" => "bekorqilish_send"]],
            ],
        ])
    )->result->message_id;

    mysqli_query(
        $connect,
        "INSERT INTO `send` (`time1`,`time2`,`start_id`,`stop_id`,`admin_id`,`message_id`,`reply_markup`,`step`,`edit_mess_id`,`status`,`statistics`,`sends_count`,`receive_count`)
        VALUES ('$time1','$time2','0','$stop_id','$cid','$mid','$reply_markup','send','$edit_mess_id','resume','$stat',0,0)"
    );
    sendMessage($cid, "<b>🔄️ Qabul qilindi, bir necha daqiqadan keyin yuborish boshlanadi!</b> / $smsmid", $panel);
    step($cid, "none");
}

if ($data == "bekorqilish_send" and admin($cid) == 1) {
    mysqli_query($connect, "DELETE FROM `send`");
    deleteMessage($cid,$mid2);
    sendMessage($cid, "<b>Admin paneliga xush kelibsiz!</b>", $panel);
    step($cid, "none");
    exit();
}

if (mb_stripos($data, "sendstatus=") !== false and admin($cid) == 1) {
    $up_stat = explode("=", $data)[1];
    $result = mysqli_query($connect, "SELECT * FROM `send`");
    $row = mysqli_fetch_assoc($result);
    if ($row["status"] == $up_stat) {
        answerCallback($qid, "Xabar yuborish xolati $up_stat ga o'zgartirolmaysiz.", 1);
    } else {
        if ($up_stat == "resume") {
            $time1 = date("H:i", time() + 60);
            $time2 = date("H:i", time() + 120);
            mysqli_query(
                $connect,
                "UPDATE `send` SET time1 = '$time1', `time2` = '$time2'"
            );
        }
        if ($up_stat == "resume") {
            $kb = json_encode([
                "inline_keyboard" => [
                    [["text" => "To'xtatish ⏸","callback_data" => "sendstatus=stopped"]],
                    [["text" => "🗑 O'chirish","callback_data" => "bekorqilish_send"]],
                ],
            ]);
        } elseif ($up_stat == "stopped") {
            $kb = json_encode([
                "inline_keyboard" => [
                    [["text" => "Davom ettirish ▶️", "callback_data" => "sendstatus=resume"]],
                    [["text" => "🗑 O'chirish","callback_data" => "bekorqilish_send"]],
                ],
            ]);
        }
        $edit_mess_id = editMessage($cid, $mid2, "<b>✅ Yuborildi:</b> <code>" .
                $row["sends_count"] .
                "/" .
                $row["statistics"] .
                "</code>
<b>📥 Qabul qilindi:</b> <code>" .
                $row["receive_count"] .
                "</code>
<b>🔰 Status</b>: <code>$up_stat</code>",
            $kb
        )->result->message_id;
        mysqli_query($connect, "UPDATE `send` SET edit_mess_id = '$edit_mess_id', `status` = '$up_stat'");
    }
}

if (($text == "📢 Kanallar" && $type == "private" ) and admin($cid) == 1){
    replyMessage($cid, $mid, "🛠 Kanal sozlamalari!", $panel_channel);
}

if (($text == "➕ Kanal qo'shish" && $type == "private" ) and admin($cid) == 1) {
    sendMessage($cid,"<b>👉 Qo‘shmoqchi bo‘lgan kanal turini tanlang:</b>",json_encode([
            "inline_keyboard" => [
                [["text" => "🌐 Ommaviy", "callback_data" => "request-false"]],
                [["text" => "🔐 So‘rov qabul qiluvchi", "callback_data" => "request-true"]],
                [["text" => "⁉️Boshqa", "callback_data" => "request-other"]],
            ],
        ])
    );
}

if (mb_stripos($data, "request-") !== false) {
    $type = explode("-", $data)[1];
    if ($type == "true" || $type == "false") {
        handleRequestChannel($cid, $type, $back_panel);
    } elseif ($type == "other") {
        file_put_contents("step/$cid.type", $type);
        step($cid, "addOtherChannel");
        sendMessage($cid, "<b>🔗 Iltimos, Telegram botning referal yoki Instagram, TikTok kabi ijtimoiy tarmoqlardan biror havolani kiriting:</b>", $back_panel);
    }
}

if ($step == "addChannel") {
    addChannelStep($message, $cid, $connect, $panel);
}

if ($step == "addOtherChannel") {
    addOtherChannelStep($cid, $text, $connect, $panel, $back_panel);
}

if ($step == "addOtherChannelName") {
    saveOtherChannelName($cid, $text, $connect, $panel);
}

if ($text == "🗑 Kanal o'chirish" && $type == "private" && admin($cid) == 1) {
    $result = $connect->query("SELECT * FROM `channels`");
    
    if ($result->num_rows > 0) {
        $buttons = [];

        while ($row = $result->fetch_assoc()) {
            $channel_id = $row["channel_id"];
            $type = $row["type"];
            $title = $row["title"];

            if ($type == "lock" || $type == "request") {
                $title = bot("getchat", ["chat_id" => $channel_id])->result->title;
            }

            $buttons[] = [
                "text" => "🗑️ $title",
                "callback_data" => "delChan=$channel_id",
            ];
        }

        $keyboard = [
            "inline_keyboard" => array_merge(
                array_chunk($buttons, 1),
                [[["text" => "◀️ Orqaga", "callback_data" => "cancelStep"]]]
            )
        ];

        sendMessage($cid, "<b>Kerakli kanalni tanlang va u o‘chiriladi:</b>", json_encode($keyboard));
    } else {
        sendMessage($cid, "<b>Hech qanday kanal ulanmagan!</b>");
    }
}

if (stripos($data, "delChan=") !== false) {
    $chnId = explode("=", $data)[1];
    $result = $connect->query(
        "SELECT * FROM `channels` WHERE channel_id = '$chnId'"
    );
    $row = $result->fetch_assoc();
    if ($row["requestchannel"] == "true") {
        $connect->query("DELETE FROM requests WHERE chat_id = '$chnId'");
    }
    $connect->query("DELETE FROM channels WHERE channel_id = '$chnId'");
    editMessage($cid,$mid2,"<b>✅ Kanal o'chirildi!</b>");
}


if ($text == "⚙️ Sozlamalar" and admin($cid) == 1) { 
    replyMessage($cid, $mid, "⤵️ Kerakli menyuni tanlang:", json_encode([
        "inline_keyboard" => [
            [["text" => "🎬 Baza Kanal", "callback_data" => "changeChannel"]],
            [["text" => "🎬 Asosiy Kanal", "callback_data" => "changeMainChannel"]]
            ],
    ]));
}

if ($data == "changeChannel" and admin($cid) == 1) {
    deleteMessage($cid, $mid2);
    sendMessage($cid, "⏳ Iltimos, botni kanalingizga admin qiling va o‘sha kanaldan *forward xabar* yuboring:", $repl_markup);
    step($cid, "new_movie_base_chan");
}

if ($step == "new_movie_base_chan" && isset($message->forward_origin->chat->id)) {
    $base_kanal_id = $message->forward_origin->chat->id;

    $botInfos = bot('getChatMember', [
        'chat_id' => $base_kanal_id,
        'user_id' => $bot_id
    ]);

    if (!$botInfos || $botInfos->ok == false) {
        sendMessage($cid, "❌ API xatolik: " . json_encode($botInfos));
        step($cid, "new_movie_base_chan");
        return;
    }

    $status = $botInfos->result->status ?? 'none';

    if (in_array($status, ['administrator', 'creator'])) {
        $safe_id = mysqli_real_escape_string($connect, $base_kanal_id);
        mysqli_query($connect, "UPDATE `settings` SET `baseChannel` = '$safe_id' WHERE id = 1");

        sendMessage($cid, "✅ Baza kanal muvaffaqiyatli yangilandi!", $panel);
        step($cid, "none");
    } else {
        sendMessage($cid, "❌ Bot ushbu kanalda admin emas. Iltimos, botni admin qilib ulang va qaytadan urinib ko‘ring.");
        step($cid, "new_movie_base_chan");
    }
}


if ($data == "changeMainChannel" and admin($cid) == 1) {
    deleteMessage($cid, $mid2);
    sendMessage($cid, "⏳ Iltimos, botni kanalingizga admin qiling va o‘sha kanaldan *forward xabar* yuboring:", $repl_markup);
    step($cid, "new_movie_main_chan");
}

if ($step == "new_movie_main_chan" && isset($message->forward_origin->chat->id)) {
    $base_kanal_id = $message->forward_origin->chat->id;

    $newBotInfo = bot('getChatMember', [
        'chat_id' => $base_kanal_id,
        'user_id' => $bot_id 
    ]);

    if (!$newBotInfo || $newBotInfo->ok == false) {
        sendMessage($cid, "❌ API xatolik: " . json_encode($newBotInfo));
        step($cid, "new_movie_main_chan");
        return;
    }

    $newStatus = $newBotInfo->result->status ?? 'left';

    if (in_array($newStatus, ['administrator', 'creator'])) {
        $safe_id = mysqli_real_escape_string($connect, $base_kanal_id);
        mysqli_query($connect, "UPDATE `settings` SET `mainChannel` = '$safe_id' WHERE id = 1");

        sendMessage($cid, "✅ Asosiy kanal muvaffaqiyatli yangilandi!", $panel);
        step($cid, "none");
    } else {
        sendMessage($cid, "❌ Bot ushbu kanalda admin emas. Iltimos, botni kanalingizga admin qilib ulang va qayta yuboring.");
        step($cid, "new_movie_main_chan");
    }
}

if (strpos($data, "sms_") === 0) {
    $kino_id = str_replace("sms_", "", $data);
    step($cid, "wait_sms_video_$kino_id");
    sendMessage($cid, "📤 Iltimos, video va captionni yuboring.");
}



if (strpos($step, "wait_sms_video_") === 0 && isset($message->video)) {
    $kino_id = str_replace("wait_sms_video_", "", $step);

    $video_file_id = $message->video->file_id;
    $caption = $message->caption ?? "";

    $inline_keyboard = json_encode([
        "inline_keyboard" => [
            [["text" => "▶️ Ko‘rish", "url" => "https://t.me/$bot?start=$kino_id"]]
        ]
    ]);

    $res = mysqli_query($connect, "SELECT mainChannel FROM settings WHERE id = 1");
    $mainChannel = null;
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $mainChannel = $row['mainChannel'];
    }

    if ($mainChannel && $mainChannel != '-100') {
        sendVideo($mainChannel, $video_file_id, $caption, $inline_keyboard);
        sendMessage($cid, "✅ Video kanalga yuborildi!");
    } else {
        sendMessage($cid, "⚠️ Main kanal sozlanmagan. Sozlamalardan to‘g‘rilang.");
    }

    step($cid, "none");
}














