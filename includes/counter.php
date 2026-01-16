<?php
session_start();
include("db.php");
$_SESSION["visited_items"] = $_SESSION["visited_items"] ?? array();

if(isset($_POST["video_url"])) {
  if(!in_array("video-".$_POST["video_url"], $_SESSION["visited_items"])) {
    mysqli_query($link, "UPDATE player_videos SET views=views+1 WHERE link='".mysqli_real_escape_string($link, $_POST["video_url"])."'");
    $_SESSION["visited_items"][] = "video-".$_POST["video_url"];
  }
}