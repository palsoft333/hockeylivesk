<?php
session_start();
include("db.php");
include("main_functions.php");
header('Content-Type: text/html; charset=utf-8');

function AddSubscriber($username, $email) {
    $url = 'https://api.sender.net/v2/subscribers';

    $json = [
        "email" => $email,
        "firstname" => $username,
        "groups" => ["azx8oO"]
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SENDERS_TOKEN,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        SendMail(ADMIN_MAIL, "Chyba pri importe do Senders.net", 'cURL error: '.curl_error($ch));
    }

    curl_close($ch);
}

if(isset($_POST['check']))
{
$username=mysqli_real_escape_string($link, $_POST['check']);
$result=mysqli_query($link, "SELECT uname FROM e_xoops_users WHERE uname='".$username."'");
if(mysqli_num_rows($result)>0) echo "taken";
}

if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email']))
{
  $ip = $_SERVER['REMOTE_ADDR'];
  $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode(GOOGLE_RECAPTCHA_SECRETKEY) . '&response=' . urlencode($_POST['token']) . '&remoteip=' . urlencode($ip);
  $response = file_get_contents($url);
  
  $responseKeys = json_decode($response, true);

  if ($responseKeys["success"] && $responseKeys["action"] == 'submit') {
      if ($responseKeys["score"] >= 0.5) {
      
        $username=mysqli_real_escape_string($link, $_POST['username']);
        $password=md5(mysqli_real_escape_string($link, $_POST['password']));
        $email=mysqli_real_escape_string($link, $_POST['email']);
        if($_POST["optin"]=="1") $notify=1;
        else $notify=0;
        $q=mysqli_query($link, "SELECT email FROM e_xoops_users WHERE email='".$email."'");
        if(mysqli_num_rows($q)>0) echo "EMAILEXISTS";
        else {
            $result=mysqli_query($link, "INSERT INTO e_xoops_users (uname, email, lang, user_regdate, pass, mail_notify) VALUES ('".$username."', '".$email."', '".$_SESSION["lang"]."', '".time()."', '".$password."', '".$notify."')");
            if($notify==1) AddSubscriber($username, $email);
        }
        
      } elseif ($responseKeys["score"] < 0.5) {
          echo "CAPTCHAERROR";
      }
  } elseif($responseKeys["error-codes"]) {
    echo "CAPTCHAERROR";
  } else {
      
  }
  
  if($result)
    {
    $id=mysqli_insert_id($link);
    mysqli_query($link, "UPDATE e_xoops_users SET last_login='".time()."' WHERE uid='".$id."'");
    $_SESSION['logged']=$id;
    SendMail(ADMIN_MAIL, "Nový užívateľ na HL", "https://www.hockey-live.sk/user/".$id);
    echo "OK";
    }
}
?>