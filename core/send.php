<?php

require_once __DIR__ . "/sql.php";
require_once __DIR__ . "/bot.php";

$sendLimitFile = __DIR__ . "/../step/sendCount.txt";
if (file_exists($sendLimitFile)) {
    $limit = (int)trim(file_get_contents($sendLimitFile));
    if ($limit <= 0) {
        $limit = 500; 
    }
} else {
    $limit = 500; 
}


if (isset($_GET["update"]) && $_GET["update"] === "send") {
    $result = mysqli_query($connect, "SELECT * FROM `send` LIMIT 1");
    $row = mysqli_fetch_assoc($result);

    if (!$row) {
        echo json_encode(["status" => false, "message" => "Hech qanday yuborish jarayoni topilmadi."]);
        exit;
    }

    $time = date("H:i");
    if ($row["status"] !== "resume") {
        echo json_encode(["status" => false, "message" => "Yuborish holati 'resume' emas."]);
        exit;
    }

    $time1 = $row["time1"];
    $time2 = $row["time2"];
    $start_offset = (int)$row["start_id"];  
    $stop_id = (int)$row["stop_id"];
    $admin_id = $row["admin_id"];
    $message_id = $row["message_id"];
    $edit_mess_id = $row["edit_mess_id"];
    $sends_count = (int)$row["sends_count"];
    $receive_count = (int)$row["receive_count"];
    $repl_markup = base64_decode($row["reply_markup"]);
    $statistics = (int)$row["statistics"];

    if ($time !== $time1 && $time !== $time2) {
        echo json_encode(["status" => true, "message" => "Yuborish vaqti emas."]);
        exit;
    }

    $sql = "SELECT * FROM `users` ORDER BY `id` ASC LIMIT $limit OFFSET $start_offset";
    $res = mysqli_query($connect, $sql);

    if (!$res) {
        echo json_encode(["status" => false, "message" => "Users so‘rovi bajarilmadi."]);
        exit;
    }

    $users_processed = 0;
    while ($user = mysqli_fetch_assoc($res)) {
        $user_id = $user["user_id"];
        
        if($repl_markup=="null"){
            $copyResult = bot("CopyMessage", [
            "chat_id" => $user_id,
            "from_chat_id" => $admin_id,
            "message_id" => $message_id,
        ]);
        }else{
        $copyResult = bot("CopyMessage", [
            "chat_id" => $user_id,
            "from_chat_id" => $admin_id,
            "message_id" => $message_id,
            "reply_markup" => $repl_markup,

        ]);
        }

        $sends_count++;
        if (isset($copyResult->ok) && $copyResult->ok === true) {
            $receive_count++;
        }

        $users_processed++;

        if ($user_id == $stop_id) {
            bot('deleteMessage', [
                'chat_id' => $admin_id,
                'message_id' => $edit_mess_id,
            ]);

            bot("sendMessage", [
                "chat_id" => $admin_id,
                "text" => "<b>✅ Xabar yuborish yakunlandi</b>\n\n<b>✅ Yuborildi:</b> <code>$sends_count/$statistics</code>",
                "parse_mode" => "html",
                "reply_markup" => $panel,
            ]);

            mysqli_query($connect, "DELETE FROM `send`");
            echo json_encode(["status" => true, "message" => "Yuborish yakunlandi."]);
            exit;
        }
    }

    $new_time1 = date("H:i", strtotime("+1 minutes"));
    $new_time2 = date("H:i", strtotime("+2 minutes"));
    $new_start_offset = $start_offset + $users_processed;

    $update_sql = "UPDATE `send` SET 
        `time1` = '$new_time1',
        `time2` = '$new_time2',
        `start_id` = $new_start_offset,
        `sends_count` = $sends_count,
        `receive_count` = $receive_count
    ";
    mysqli_query($connect, $update_sql);

    $edit = bot("editMessageText", [
        "chat_id" => $admin_id,
        "message_id" => $edit_mess_id,
        "text" => "<b>✅ Yuborildi:</b> <code>$sends_count/$statistics</code>\n<b>📥 Qabul qilindi:</b> <code>$receive_count</code>\n<b>🔰 Status</b>: <code>resume</code>",
        "parse_mode" => "html",
        "reply_markup" => json_encode([
            "inline_keyboard" => [
                [["text" => "To'xtatish ⏸️", "callback_data" => "sendstatus=stopped"]],
                [["text" => "🗑 O'chirish", "callback_data" => "bekorqilish_send"]],
            ],
        ]),
    ]);

    if (isset($edit->ok) && $edit->ok === true) {
        $edit_mess_id_new = $edit->result->message_id;
        mysqli_query($connect, "UPDATE `send` SET `edit_mess_id` = '$edit_mess_id_new'");
    }

    echo json_encode(["status" => true, "message" => "Xabar yuborilmoqda"]);
}
