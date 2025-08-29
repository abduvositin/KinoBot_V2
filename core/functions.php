<?php

function step($user_id, $value){
    global $connect;
    mysqli_query($connect, "UPDATE users SET step = '$value' WHERE user_id = $user_id");
}

function getStep($user_id){
    global $connect;
    $res = mysqli_query($connect, "SELECT step FROM users WHERE user_id = $user_id");
    $row = mysqli_fetch_assoc($res);
    return $row['step'] ?? 'none';
}

function admin($user_id){
    global $connect, $owners;
    if (in_array($user_id, $owners)) return true;
    $res = mysqli_query($connect, "SELECT * FROM admins WHERE user_id = $user_id");
    return mysqli_num_rows($res) > 0;
}

function saveRequest($user_id, $channel_id){
    global $connect;
    mysqli_query($connect, "INSERT INTO requests(user_id, chat_id) VALUES($user_id, $channel_id)");
}

function getSettings($connect) {
    $result = $connect->query("SELECT * FROM settings WHERE id = '1'"); 
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

function getUserStep($connect, $user_id) {
    $userResult = mysqli_query($connect, "SELECT * FROM users WHERE user_id = '$user_id'");
    if ($userResult && mysqli_num_rows($userResult) > 0) {
        $ures = mysqli_fetch_assoc($userResult);
        return $ures["step"];
    }
    return null;
}

function registerUserIfNotExists($connect, $user_id) {
    $result = mysqli_query($connect,"SELECT * FROM users WHERE user_id = '$user_id'");
    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        $registered_date = date("d.m.Y H:i");
        mysqli_query($connect,"INSERT INTO users(`user_id`,`time`,`step`) VALUES ('$user_id','$registered_date','none')");
    }
}


function joinchat($id){
    global $connect;
    $result = $connect->query("SELECT * FROM `channels`");
    if ($result->num_rows > 0 and admin($id) !== true) {
        $no_subs = 0;
        $button = [];
        while ($row = $result->fetch_assoc()) {
            $type = $row["type"];
            $link = $row["link"];
            $channel_id = $row["channel_id"];
            $title = $row["title"];
            $gettitle = bot("getchat", ["chat_id" => $channel_id])->result->title;
            if ($type == "lock" or $type == "request" or $type == "other") {
                if ($type == "request") {
                    $check = $connect->query("SELECT * FROM `requests` WHERE user_id = '$id' AND chat_id = '$channel_id'");
                    if ($check->num_rows > 0) {
                        $button[] = ["text" => "✅ $gettitle", "url" => $link];
                    } else {
                        $button[] = ["text" => "❌ $gettitle", "url" => $link];
                        $no_subs++;
                    }
                } elseif ($type == "lock") {
                    $check = bot("getChatMember", [
                        "chat_id" => $channel_id,
                        "user_id" => $id,
                    ])->result->status;
                    if ($check == "left") {
                        $button[] = ["text" => "❌ $gettitle", "url" => $link];
                        $no_subs++;
                    } else {
                        $button[] = ["text" => "✅ $gettitle", "url" => $link];
                    }
                } elseif ($type == "other") {
                    $button[] = ["text" => "❌ $title", "url" => $link];
                }
            }
        }
        if ($no_subs > 0) {
            $button[] = [
                "text" => "✅ Tekshirish",
                "callback_data" => "checkSub",
            ];
            $keyboard2 = array_chunk($button, 1);
            $keyboard = json_encode([
                "inline_keyboard" => $keyboard2,
            ]);
            bot("sendMessage", [
                "chat_id" => $id,
                "text" =>"<b>❌ Kechirasiz botimizdan foydalanishdan oldin ushbu kanallarga a'zo bo'lishingiz kerak.</b>",
                "parse_mode" => "html",
                "reply_markup" => $keyboard,
            ]);
            exit();
        } else {
            return true;
        }
    } else {
        return true;
    }
}

function searchMovie($cid, $text, $mid, $baseChannel,$bot) {
    global $connect;

    if (!is_numeric($text)) {
        replyMessage($cid, $mid, "<b>❌ Iltimos, film ID sini faqat raqam sifatida kiriting!</b>");
        return;
    }

    $movie_id = intval($text);
    $result = $connect->query("SELECT * FROM movies_data WHERE movie_id = $movie_id");

    if ($result && $result->num_rows > 0) {
        if ($result->num_rows > 1) {
            $texts = "🔍 Bir nechta natija topildi ({$result->num_rows}):\n\n";
            $counter = 1;
            $buttons = [];

            while ($row = $result->fetch_assoc()) {
                $title = base64_decode($row["movie_name"]);

                $texts .= "<b>$counter.</b> {$title}\n";
                $buttons[] = [
                    "text" => (string)$counter,
                    "callback_data" => "serie_" . $row["id"],
                ];
                $counter++;
            }

            $keyboard = array_chunk($buttons, 5);
            $keyboard[] = [["text" => "🔙 Orqaga", "callback_data" => "orqaga"]];

            bot("sendMessage", [
                "chat_id" => $cid,
                "text" => $texts,
                "parse_mode" => "html",
                "reply_markup" => json_encode(["inline_keyboard" => $keyboard]),
            ]);
        } else {
            $row = $result->fetch_assoc();
            $movie_bid = $row["movie_bid"];
            copyMessage($cid, $baseChannel, $movie_bid,json_encode([
                    "inline_keyboard" => [
                        [["text" => "♻️ Do'stlarga ulashish", "url" => "https://t.me/share/url?url=https://t.me/$bot?start=$text"]],
                        [["text" => "❌", "callback_data" => "orqaga"]],
                    ],
                ]));
        }
    } else {
        replyMessage($cid, $mid, "<b>❌ Film mavjud emas yoki o'chirib tashlangan!</b>");
    }
}

function sendSerieVideo($cid, $data, $mid2,$bot,$baseChannel) {
    global $connect,$baseChannel;

    $movie_id = str_replace("serie_", "", $data); 
    $result = $connect->query("SELECT * FROM movies_data WHERE id = '$movie_id'");

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $movie_bid = $row["movie_bid"];
        $movie_ids = $row["movie_id"];
        copyMessage($cid, $baseChannel, $movie_bid,json_encode([
                    "inline_keyboard" => [
                        [["text" => "♻️ Do'stlarga ulashish", "url" => "https://t.me/share/url?url=https://t.me/$bot?start=$movie_ids"]],
                        [["text" => "❌", "callback_data" => "orqaga"]],
                    ],
                ]));
    } else {
        bot("sendMessage", [
            "chat_id" => $cid,
            "text" => "❌ Kino topilmadi.\n\n♻️ Qayta urinib ko'ring",
            "reply_to_message_id" => $mid2
        ]);
    }
}


