<?php

$bot_token = "توکن";

$sudo_port = "HHH";

$dev_id = "AMIR_PHOSHING";

$dev_name = "AMIR_PHOSHING";

$sudo_user = ["8092819577"];

$id_sender = "8092819577";

date_default_timezone_set("Asia/Tehran");

$start_date = "2026/06/25";
$end_date   = "2026/07/25";

$today = strtotime(date("Y/m/d"));
$expire = strtotime($end_date);
$days_left = ceil(($expire - $today) / 86400);
if ($days_left < 0) $days_left = 0;

$dateBuy = $start_date;
$expir = $end_date . " (" . $days_left . " Day)";

$file = "matinlurd-firebase-adminsdk-hjvu1-4f11c1dd46.json";
$project_id = "matinlurd";

?>