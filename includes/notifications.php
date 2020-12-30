<?
session_start();
include("db.php");

if($_POST[markread])
  {
  if($_POST[markread]=="all") mysql_query("UPDATE user_notifications SET isread='1' WHERE uid='".$_SESSION["logged"]."'");
  else 
    {
    $q = mysql_query("SELECT uid FROM user_notifications WHERE id='".$_POST[markread]."' && uid='".$_SESSION["logged"]."'");
    if(mysql_num_rows($q)>0) mysql_query("UPDATE user_notifications SET isread='1' WHERE id='".$_POST[markread]."'");
    }
  echo "ok";
  }

mysql_close($link);
?>