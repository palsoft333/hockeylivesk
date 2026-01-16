<?php
session_start();
include("db.php");
include("main_functions.php");
if(isset($_SESSION["lang"])) {
  include("lang/lang_".$_SESSION["lang"].".php");
}
else {
   $_SESSION["lang"] = 'sk';
    include("lang/lang_sk.php");
}

function GetSubscriber($email) {
    $url = 'https://api.sender.net/v2/subscribers/' . urlencode($email);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SENDERS_TOKEN,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    if (curl_errno($ch)) {
      return false;
    }
    else {
      $json = json_decode($response, true);
      if(isset($json["success"]) && $json["success"]==false) return false;
      else return $json;
    }
}

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

function UpdateSubscriber($email, $status) {
    $url = 'https://api.sender.net/v2/subscribers/' . urlencode($email);

    $json = [
        "subscriber_status" => ($status ? "ACTIVE" : "UNSUBSCRIBED"),
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . SENDERS_TOKEN,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
      return false;
    }
    else {
      return true;
    }

    curl_close($ch);
}

function GetUserName($uid) {
  Global $link;
  $q = mysqli_query($link, "SELECT uname FROM e_xoops_users WHERE uid=".mysqli_real_escape_string($link, $uid));
  $f = mysqli_fetch_array($q);
  return $f["uname"];
}

header('Content-Type: text/html; charset=utf-8');

if(isset($_POST['username']) && isset($_POST['password']))
{
  $username=mysqli_real_escape_string($link, $_POST['username']);
  $password=md5(mysqli_real_escape_string($link, $_POST['password']));

  $result=mysqli_query($link, "SELECT uid FROM e_xoops_users WHERE email='".$username."' and pass='".$password."'");
  $count=mysqli_num_rows($result);
  
  if($count==1)
    {
    $row=mysqli_fetch_array($result);
    mysqli_query($link, "UPDATE e_xoops_users SET last_login='".time()."' WHERE uid='".$row['uid']."'");
    $_SESSION['logged']=$row['uid'];
    if($_POST['remember']=="on")
      {
      $cookiehash = md5(sha1($row['uname'] . $_SERVER['HTTP_X_FORWARDED_FOR'] . $_SERVER['HTTP_USER_AGENT']));
      setcookie("uname",$cookiehash,time()+3600*24*365,'/','.hockey-live.sk');
      $ls = mysqli_query($link, "SELECT login_session, JSON_SEARCH(login_session, 'one', '".$cookiehash."') as search FROM `e_xoops_users` WHERE uid='".$row['uid']."'");
      $lse = mysqli_fetch_array($ls);
      if($lse["login_session"]!=NULL)
        {
        if($lse["search"]==NULL) mysqli_query($link, "UPDATE e_xoops_users SET login_session=JSON_MERGE_PRESERVE(login_session, '\"".$cookiehash."\"') WHERE uid='".$row['uid']."'");
        }
      else mysqli_query($link, "UPDATE e_xoops_users SET login_session='[\"".$cookiehash."\"]' WHERE uid='".$row['uid']."'");
      }
    header("Location:/");
    }
  else
    {
    $alert = LANG_LOGIN_ERROR;
    }
}

if($_GET["logout"])
{
  $ls = mysqli_query($link, "SELECT JSON_SEARCH(login_session, 'one', '".$_COOKIE['uname']."') as search FROM `e_xoops_users` WHERE uid='".$_SESSION['logged']."'");
  $lse = mysqli_fetch_array($ls);
  if($lse["search"]!=NULL) mysqli_query($link, "UPDATE e_xoops_users SET login_session=JSON_REMOVE(login_session, ".$lse["search"].") WHERE uid='".$_SESSION['logged']."'");
  unset($_SESSION['logged']);
  unset($_COOKIE['uname']);
  setcookie("uname", "", time() - 3600, "/", ".hockey-live.sk", 1);
  header("Location:/");
}

if($_GET["logoutall"])
{
  mysqli_query($link, "UPDATE e_xoops_users SET login_session=NULL WHERE uid='".$_SESSION['logged']."'");
  header("Location:/profile");
}

if(isset($_POST['forgot']))
{
$email=mysqli_real_escape_string($link, $_POST['forgot']);
$result=mysqli_query($link, "SELECT email FROM e_xoops_users WHERE email='".$email."'");
if(mysqli_num_rows($result)>0) 
  {
  $newpass = bin2hex(openssl_random_pseudo_bytes(3));
  $newpassmd5 = md5($newpass);
  mysqli_query($link, "UPDATE e_xoops_users SET pass='".$newpassmd5."' WHERE email='".$email."'");
  $headers = "From: hockey-LIVE.sk <".SITE_MAIL.">\r\n";
  $headers .= "Reply-To: ".SITE_MAIL."\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  mail($email, "Vaše nové heslo na prihlásenie", "Vaše nové heslo pre prihlásenie na stránkach www.hockey-live.sk je: <b>".$newpass."</b><br><br>Odporúčame Vám zmeniť si ho pri najbližšom prihlásení.", $headers);
  echo "exists";
  }
}

if($_POST["change"]=="pass")
{
  $currentpass=md5(mysqli_real_escape_string($link, $_POST['currentpass']));
  $password=md5(mysqli_real_escape_string($link, $_POST['password']));
  $result=mysqli_query($link, "SELECT pass FROM e_xoops_users WHERE uid='".$_SESSION['logged']."'");
  $row=mysqli_fetch_array($result);
  if($row["pass"]==$currentpass)
    {
    mysqli_query($link, "UPDATE e_xoops_users SET pass='".$password."' WHERE uid='".$_SESSION['logged']."'");
    echo "ok";
    }
}

if($_POST["change"]=="data")
{
  $email=mysqli_real_escape_string($link, $_POST['email']);
  $tshort=mysqli_real_escape_string($link, $_POST['tshort']);
  $lang=mysqli_real_escape_string($link, $_POST['lang']);
  $mailnotify=mysqli_real_escape_string($link, $_POST['mailnotify']);
  $goalhorn=mysqli_real_escape_string($link, $_POST['goalhorn']);
  $avatar=mysqli_real_escape_string($link, $_POST['avatar']);
  if(strlen($_POST['avatar'])>0)
    {
    $data = explode(',', $_POST['avatar']);
    $data1 = explode(';', $data[0]);
    $data2 = explode('/', $data1[0]);
    $img_type = $data2[1];
    $img = base64_decode($data[1]);
    if($img_type=="jpeg") $img_type="jpg";
    file_put_contents('../images/user_avatars/'.$_SESSION['logged'].'.'.$img_type, $img);
    mysqli_query($link, "UPDATE e_xoops_users SET user_avatar='".$img_type."' WHERE uid='".$_SESSION['logged']."'");
    SendMail(ADMIN_MAIL, "Zmenený avatar", "ID užívateľa: ".$_SESSION['logged']);
    }
  if(filter_var($email, FILTER_VALIDATE_EMAIL))
    {
    mysqli_query($link, "UPDATE e_xoops_users SET email='".$email."', lang='".$lang."', user_favteam='".$tshort."', goalhorn='".$goalhorn."', mail_notify='".$mailnotify."' WHERE uid='".$_SESSION['logged']."'");
    $subscriber = GetSubscriber($email);
    if($subscriber) {
      if($mailnotify==1) {
        $update = UpdateSubscriber($email, true);
      }
      else {
        $update = UpdateSubscriber($email, false);
      }
    }
    else {
      if($mailnotify==1) {
        $username = GetUserName($_SESSION['logged']);
        AddSubscriber($username, $email);
      }
    }
    echo "ok";
    }
}
?>