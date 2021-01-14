<?php
session_start();
include("db.php");
include("main_functions.php");
if(isset($_SESSION[lang])) {
  include("lang/lang_$_SESSION[lang].php");
}
else {
   $_SESSION[lang] = 'sk';
    include("lang/lang_sk.php");
}

header('Content-Type: text/html; charset=utf-8');

if(isset($_POST['username']) && isset($_POST['password']))
{
  $username=mysql_real_escape_string($_POST['username']);
  $password=md5(mysql_real_escape_string($_POST['password']));

  $result=mysql_query("SELECT uid FROM e_xoops_users WHERE email='$username' and pass='$password'");
  $count=mysql_num_rows($result);
  
  if($count==1)
    {
    $row=mysql_fetch_array($result);
    mysql_query("UPDATE e_xoops_users SET last_login='".mktime()."' WHERE uid='".$row['uid']."'");
    $_SESSION['logged']=$row['uid'];
    if($_POST['remember']=="on")
      {
      $cookiehash = md5(sha1($row['uname'] . $_SERVER['HTTP_X_FORWARDED_FOR'] . $_SERVER['HTTP_USER_AGENT']));
      setcookie("uname",$cookiehash,time()+3600*24*365,'/','.hockey-live.sk');
      $ls = mysql_query("SELECT login_session, JSON_SEARCH(login_session, 'one', '".$cookiehash."') as search FROM `e_xoops_users` WHERE uid='".$row['uid']."'");
      $lse = mysql_fetch_array($ls);
      if($lse[login_session]!=NULL)
        {
        if($lse[search]==NULL) mysql_query("UPDATE e_xoops_users SET login_session=JSON_MERGE_PRESERVE(login_session, '\"".$cookiehash."\"') WHERE uid='".$row['uid']."'");
        }
      else mysql_query("UPDATE e_xoops_users SET login_session='[\"".$cookiehash."\"]' WHERE uid='".$row['uid']."'");
      }
    header("Location:/");
    }
  else
    {
    $alert = LANG_LOGIN_ERROR;
    }
}

if($_GET[logout])
{
  $ls = mysql_query("SELECT JSON_SEARCH(login_session, 'one', '".$_COOKIE['uname']."') as search FROM `e_xoops_users` WHERE uid='".$_SESSION['logged']."'");
  $lse = mysql_fetch_array($ls);
  if($lse[search]!=NULL) mysql_query("UPDATE e_xoops_users SET login_session=JSON_REMOVE(login_session, ".$lse[search].") WHERE uid='".$_SESSION['logged']."'");
  session_unset($_SESSION['logged']);
  unset($_COOKIE['uname']);
  setcookie("uname", "", time() - 3600, "/", ".hockey-live.sk", 1);
  header("Location:/");
}

if($_GET[logoutall])
{
  mysql_query("UPDATE e_xoops_users SET login_session=NULL WHERE uid='".$_SESSION['logged']."'");
  header("Location:/profile");
}

if(isset($_POST['forgot']))
{
$email=mysql_real_escape_string($_POST['forgot']);
$result=mysql_query("SELECT email FROM e_xoops_users WHERE email='$email'");
if(mysql_num_rows($result)>0) 
  {
  $newpass = bin2hex(openssl_random_pseudo_bytes(3));
  $newpassmd5 = md5($newpass);
  mysql_query("UPDATE e_xoops_users SET pass='$newpassmd5' WHERE email='$email'");
  $headers = "From: hockey-LIVE.sk <".SITE_MAIL.">\r\n";
  $headers .= "Reply-To: ".SITE_MAIL."\r\n";
  $headers .= "MIME-Version: 1.0\r\n";
  $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
  mail($email, "Vaše nové heslo na prihlásenie", "Vaše nové heslo pre prihlásenie na stránkach www.hockey-live.sk je: <b>".$newpass."</b><br><br>Odporúčame Vám zmeniť si ho pri najbližšom prihlásení.", $headers);
  echo "exists";
  }
}

if($_POST[change]=="pass")
{
  $currentpass=md5(mysql_real_escape_string($_POST['currentpass']));
  $password=md5(mysql_real_escape_string($_POST['password']));
  $result=mysql_query("SELECT pass FROM e_xoops_users WHERE uid='".$_SESSION['logged']."'");
  $row=mysql_fetch_array($result);
  if($row[pass]==$currentpass)
    {
    mysql_query("UPDATE e_xoops_users SET pass='$password' WHERE uid='".$_SESSION['logged']."'");
    echo "ok";
    }
}

if($_POST[change]=="data")
{
  $email=mysql_real_escape_string($_POST['email']);
  $tshort=mysql_real_escape_string($_POST['tshort']);
  $goalhorn=mysql_real_escape_string($_POST['goalhorn']);
  $avatar=mysql_real_escape_string($_POST['avatar']);
  if(strlen($_POST['avatar'])>0)
    {
    $data = explode(',', $_POST['avatar']);
    $data1 = explode(';', $data[0]);
    $data2 = explode('/', $data1[0]);
    $img_type = $data2[1];
    $img = base64_decode($data[1]);
    if($img_type=="jpeg") $img_type="jpg";
    file_put_contents('../images/user_avatars/'.$_SESSION['logged'].'.'.$img_type, $img);
    mysql_query("UPDATE e_xoops_users SET user_avatar='$img_type' WHERE uid='".$_SESSION['logged']."'");
    $headers = 'From: '.SITE_MAIL. "\r\n" .
    'Reply-To: '.SITE_MAIL. "\r\n" .
    'X-Mailer: PHP/' . phpversion();
    mail(ADMIN_MAIL, "Zmenený avatar", "ID užívateľa: ".$_SESSION['logged'], $headers);
    }
  if(filter_var($email, FILTER_VALIDATE_EMAIL))
    {
    mysql_query("UPDATE e_xoops_users SET email='$email', user_favteam='$tshort', goalhorn='$goalhorn' WHERE uid='".$_SESSION['logged']."'");
    echo "ok";
    }
}
?>