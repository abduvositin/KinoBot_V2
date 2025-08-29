<?php


$host = "localhost";
$username = "DB_USERNAME";
$password = "DP_PASS";
$database = "DP_NAME";

$connect = mysqli_connect($host, $username, $password, $database);

if (!$connect) {
    die("Bazaga ulanishda xatolik: " . mysqli_connect_error());
}

$tables = [
    "admins" => "
        CREATE TABLE IF NOT EXISTS admins (
            id INT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT NOT NULL,
            PRIMARY KEY (id)
        )
    ",

    "users" => "
        CREATE TABLE IF NOT EXISTS users (
            id INT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT NOT NULL,
            time VARCHAR(100) NOT NULL,
            step VARCHAR(100) NOT NULL,
            PRIMARY KEY (id),
            INDEX idx_user_id (user_id)
        )
    ",

    "movies_data" => "
        CREATE TABLE IF NOT EXISTS movies_data (
            id INT(20) NOT NULL AUTO_INCREMENT,
            movie_id VARCHAR(256) NOT NULL, 
            movie_name VARCHAR(256) NOT NULL, 
            movie_bid VARCHAR(256) NOT NULL, 
            movie_download VARCHAR(256) NOT NULL, 
            PRIMARY KEY (id)
        )
    ",
    
    "send" => "
        CREATE TABLE IF NOT EXISTS send (
            send_id INT(11) NOT NULL AUTO_INCREMENT,
            time1 TEXT NOT NULL,
            time2 TEXT NOT NULL,
            start_id TEXT NOT NULL,
            stop_id TEXT NOT NULL,
            admin_id TEXT NOT NULL,
            message_id TEXT NOT NULL,
            reply_markup TEXT NOT NULL,
            edit_mess_id TEXT DEFAULT NULL,
            sends_count VARCHAR(255) DEFAULT '0',
            receive_count VARCHAR(255) DEFAULT '0',
            statistics TEXT NOT NULL,
            status TEXT DEFAULT NULL,
            step TEXT NOT NULL,
            PRIMARY KEY (send_id)
        )
    ",

    "channels" => "
        CREATE TABLE IF NOT EXISTS channels (
            id INT(20) NOT NULL AUTO_INCREMENT,
            channel_id BIGINT NOT NULL,
            title TEXT DEFAULT NULL,
            link VARCHAR(255) NOT NULL,
            type VARCHAR(255) NOT NULL,
            PRIMARY KEY (id)
        )
    ",

    "requests" => "
        CREATE TABLE IF NOT EXISTS requests (
            id INT(20) NOT NULL AUTO_INCREMENT,
            user_id BIGINT NOT NULL,
            chat_id BIGINT NOT NULL,
            PRIMARY KEY (id)
        )
    ",

    "settings" => "
        CREATE TABLE IF NOT EXISTS settings (
            id INT(11) NOT NULL PRIMARY KEY,
            bot_status TEXT NOT NULL,
            baseChannel TEXT NOT NULL,
            mainChannel TEXT NOT NULL
        )
    "
];

foreach ($tables as $name => $query) {
    if (!mysqli_query($connect, $query)) {
        echo "Jadval yaratishda xatolik ($name): " . mysqli_error($connect) . "<br>";
    }
}

$setSettings = mysqli_query($connect, "SELECT * FROM settings WHERE id = 1");

if (!$setSettings) {
    die("Sozlamalarni tekshirishda xatolik: " . mysqli_error($connect));
}

if (mysqli_num_rows($setSettings) == 0) {
    $sql = "INSERT INTO settings (id, bot_status, baseChannel, mainChannel) VALUES (1, 'on', '-100','-100')";
    if (!mysqli_query($connect, $sql)) {
        echo "Sozlamalarni kiritishda xatolik: " . mysqli_error($connect);
    }
}

echo "Bazaga ulanish va jadvallar tayyorlandi.<br>";

?>
