<?php

global $cid, $type, $uid, $user_name, $last_name, $name, $text, $mid, $photo, $nameuz;
global $data, $qid, $mid2, $callfrid, $callname;
global $join_chat_id, $join_user_id;

$update = json_decode(file_get_contents("php://input"));

$message = $update->message ?? null;
$callbackQuery = $update->callback_query ?? null;
$chatJoinRequest = $update->chat_join_request ?? null;

if ($message) {
    $cid = $message->chat->id;
    $type = $message->chat->type;
    $uid = $message->from->id;
    $user_name = $message->from->first_name;
    $last_name = $message->from->last_name ?? '';
    $safe_name = htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8');
    $name = "$safe_name";
    $text = $message->text ?? '';
    $mid = $message->message_id;
    $photo = $message->photo ?? null;
    $nameuz = "<a href='tg://user?id=$uid'>$name</a>";
}

if ($callbackQuery) {
    $data = $callbackQuery->data;
    $qid = $callbackQuery->id;
    $cid = $callbackQuery->message->chat->id;
    $mid2 = $callbackQuery->message->message_id;
    $callfrid = $callbackQuery->from->id;
    $callname = $callbackQuery->from->first_name;
    $nameuz = "<a href='tg://user?id=$callfrid'>$callname</a>";
}

if ($chatJoinRequest) {
    $join_chat_id = $chatJoinRequest->chat->id;
    $join_user_id = $chatJoinRequest->from->id;
    saveRequest($join_user_id, $join_chat_id);
}
