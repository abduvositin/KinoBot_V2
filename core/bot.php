<?php
define('API_KEY', 'API_TOKEN'); 

function bot($method, $datas = []){
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        error_log("Curl error: " . curl_error($ch));
        return false;
    } else {
        return json_decode($res);
    }
}

function sendMessage($chat_id, $text, $keyboard = null){
    return bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'html',
        'disable_web_page_preview' => true,
        'reply_markup' => $keyboard
    ]);
}

function replyMessage($chat_id, $message_id, $text, $keyboard = null){
    return bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'html',
        'reply_to_message_id' => $message_id,
        'disable_web_page_preview' => true,
        'reply_markup' => $keyboard
    ]);
}

function copyMessage($chat_id, $from_chat_id, $message_id, $keyboard = null) {
    return bot("copyMessage", [
        'chat_id' => $chat_id,
        'from_chat_id' => $from_chat_id,
        'message_id' => $message_id,
        'parse_mode' => 'html',
        'reply_markup' => $keyboard
    ]);
}

function editMessage($chat_id, $message_id, $text, $keyboard = null){
    return bot("editMessageText", [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => 'html',
        'reply_markup' => $keyboard
    ]);
}

function sendVideo($chat_id, $file_id, $caption = "", $reply_markup = null) {
    return bot("sendVideo", [
        'chat_id' => $chat_id,
        'video' => $file_id,
        'caption' => $caption,
        'disable_web_page_preview' => true,
        'parse_mode' => 'HTML',
        'reply_markup' => $reply_markup,
    ]);
}

function deleteMessage($chat_id, $message_id){
    return bot("deleteMessage", [
        'chat_id' => $chat_id,
        'message_id' => $message_id
    ]);
}

function answerCallback($callback_query_id, $text, $alert = false){
    return bot("answerCallbackQuery", [
        'callback_query_id' => $callback_query_id,
        'text' => $text,
        'show_alert' => $alert
    ]);
}


