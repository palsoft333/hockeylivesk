<?
session_start();
include("db.php");

if(isset($_POST["markread"]))
  {
  if($_POST["markread"]=="all") mysqli_query($link, "UPDATE user_notifications SET isread='1' WHERE uid='".$_SESSION["logged"]."'");
  else 
    {
    $q = mysqli_query($link, "SELECT uid FROM user_notifications WHERE id='".$_POST["markread"]."' && uid='".$_SESSION["logged"]."'");
    if(mysqli_num_rows($q)>0) mysqli_query($link, "UPDATE user_notifications SET isread='1' WHERE id='".$_POST["markread"]."'");
    }
  echo "ok";
  }

mysqli_close($link);
?>