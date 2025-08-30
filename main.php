<?php
ob_start();
date_default_timezone_set("Asia/Tashkent");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/core/sql.php";
require_once __DIR__ . "/core/functions.php";
require_once __DIR__ . "/core/bot.php";
require_once __DIR__ . "/core/update.php";  
require_once __DIR__ . "/core/send.php";  
require_once __DIR__ . "/admin/adminFunctions.php";  







$owners = [ADMINS_ID];
$botInfo = bot("getMe");
$bot = $botInfo->result->username;
$bot_id = $botInfo->result->id;


if (!is_dir("step")) {
    mkdir("step");
}
if (file_exists("step/counter.txt") == false) {
    file_put_contents("step/counter.txt", 0);
}

if (file_exists("step/sendCount.txt") == false) {
    file_put_contents("step/sendCount.txt", 500);
}


$settings = getSettings($connect);
$baseChannel = $settings["baseChannel"];
$bot_status = $settings["bot_status"];
$step = getUserStep($connect, $cid);

if ($type == "private") {
    registerUserIfNotExists($connect, $cid);
}

if ($text && $type=="private") {
    if ($bot_status == "off" and !admin($cid) == 1) {
        sendMessage($cid,"⛔️ <b>Bot vaqtinchalik o'chirilgan!</b>

<i>Botda ta'mirlash ishlari olib borilayotgan bo'lishi mumkin!</i>",
            json_encode(["remove_keyboard" => true])
        );
        exit();
    }
}

if ($data) {
    if ($bot_status == "off" and !admin($cid) == 1) {
        answerCallback( $qid,"⛔️ Bot vaqtinchalik o'chirilgan!

Botda ta'mirlash ishlari olib borilayotgan bo'lishi mumkin!", 1);
        exit();
    }
}

if (($text == "/start" && $type == "private") && joinchat($cid) == 1) {
    step($cid, "none");
    $msg = "<b>🎬 Assalomu alaykum $name Sevimli kinolaringiz shu yerda!</b> \n
<i>🔎 Kino kodini yuboring</i>:";
    sendMessage($cid, $msg);
}

if (($text && is_numeric($text) && $step == "none") && joinchat($cid) == 1){
    searchMovie($cid, $text, $mid, $baseChannel,$bot);
}

if (mb_stripos($text, "/start ") !== false and joinchat($cid) == 1) {
    $text = str_replace("/start ", "", $text); 
    searchMovie($cid, $text, $mid,$baseChannel,$bot);
}

if (strpos($data, "serie_") === 0 and joinchat($cid) == 1){
    sendSerieVideo($cid, $data, $mid2, $bot,$baseChannel);
}


//admin oanel
$panel_main = json_encode([
    "resize_keyboard" => true,
    "keyboard" => [
        [["text" => "🎬 Kino"], ["text" => "📢 Kanallar"]],
        [["text" => "📊 Statistika"], ["text" => "👨🏻‍💻 Adminlar"]],
        [["text" => "✉️ Xabar jo‘natish"], ["text" => "⚙️ Sozlamalar"]],
        [["text" => "🤖 Bot holati"], ["text" => "◀️ Chiqish"]],
    ]
]);

if (($text == "/panel" && $type == "private") && admin($cid) == 1) {
    showAdminPanel($cid,$panel_main);
    step($cid,"none");
}

if($data=="orqaga"){
    deleteMessage($cid,$mid2);
    step($cid,"none");
}

if ($data == "checkSub") {
    deleteMessage($cid,$mid2);
    if (joinchat($cid) == true) {
    $msg = "<b>🎬 Assalomu alaykum $name Sevimli kinolaringiz shu yerda!</b> \n
<i>🔎 Kino kodini yuboring</i>:";
    sendMessage($cid, $msg);
        exit();
    } else {
        exit();
    }
}







require_once __DIR__ . "/admin/admin.php";





