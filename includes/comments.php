<?
session_start();
include("db.php");
include("main_functions.php");
include("langs/".strtolower($_SESSION[lang]).".php");

header('Content-Type: text/html; charset=utf-8');

if($_POST[comment])
  {
  $ip = $_SERVER['REMOTE_ADDR'];
  $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode(GOOGLE_RECAPTCHA_SECRETKEY) . '&response=' . urlencode($_POST['token']) . '&remoteip=' . urlencode($ip);
  $response = file_get_contents($url);
  
  $responseKeys = json_decode($response, true); 

  if ($responseKeys["success"] && $responseKeys["action"] == 'submit') {
      if ($responseKeys["score"] >= 0.5) {
      
        $allowed_tags = '<p><b><i><u><a><ul><ol><li>';
        $name=mysql_real_escape_string($_POST[name]);
        $what=mysql_real_escape_string($_POST[what]);
        if($what==2) $uid = $_SESSION[logged];
        else $uid=mysql_real_escape_string($_POST[uid]);
        $whatid=mysql_real_escape_string($_POST[whatid]);
        $replyid=mysql_real_escape_string($_POST[replyid]);
        $comment=mysql_real_escape_string($_POST[comment]);
        $comment=strip_tags($comment, $allowed_tags);
        $lang = strtolower($_SESSION[lang]);
        if($uid==$_SESSION[logged])
          {
          if($replyid=="") $replyid=0;
          if($uid=="") mysql_query("INSERT INTO comments (what, whatid, uid, name, comment, replyto, datum) VALUES ('$what', '$whatid', '0', '$name', '".$comment."', '$replyid', '".date("Y-m-d H:i:s")."')");
          else mysql_query("INSERT INTO comments (what, whatid, uid, comment, replyto, datum) VALUES ('$what', '$whatid', '$uid', '".$comment."', '$replyid', '".date("Y-m-d H:i:s")."')");
          $last_id = mysql_insert_id();
          if($what==0) { $url = "news/".$whatid."#comments"; }
          if($what==1) { $url = "team/".$whatid."#comments"; }
          if($what==2) { $url = "game/".$whatid."#comments"; }
          if($what==3) 
            {
            if(substr($whatid, -1)=="p") $url = "\r\nHráč: ".substr($whatid, 0, -1);
            if(substr($whatid, -1)=="g") $url = "\r\nBrankár: ".substr($whatid, 0, -1);
            }
          if($replyid!=0 && $uid!=$_SESSION[logged]) 
            {
            $w = mysql_query("SELECT * FROM comments WHERE id='$replyid'");
            $e = mysql_fetch_array($w);
            Insert_Notification($e[uid], 3, $last_id);
            }
          mysql_query("UPDATE e_xoops_users SET posts=posts+1 WHERE uid='".$uid."'");
          SendMail(ADMIN_MAIL, "Nový komentár na HL", "https://www.hockey-live.sk/".$url);
          echo GetComments($what, $whatid);
          }
        
      } elseif ($responseKeys["score"] < 0.5) {
          echo "CAPTCHAERROR";
      }
  } elseif($responseKeys["error-codes"]) {
    echo "CAPTCHAERROR";
  } else {
      
    }
  }
  
if($_POST[del])
  {
  $cid=mysql_real_escape_string($_POST[del]);
  $q = mysql_query("SELECT * FROM comments WHERE id='$cid' && uid='".$_SESSION['logged']."'");
  if(mysql_num_rows($q)>0)
    {
    $f = mysql_fetch_array($q);
    mysql_query("DELETE FROM comments WHERE id='$cid'");
    mysql_query("DELETE FROM comments WHERE replyto='$cid'");
    mysql_query("UPDATE e_xoops_users SET posts=posts-1 WHERE uid='".$_SESSION['logged']."'");
    echo GetComments($f[what], $f[whatid]);
    }
  }
  
mysqli_close($link);
?>