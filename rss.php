<?php #rss.php

header("Content-Type: text/xml; charset=UTF-8");

include("includes/config.php");
include("includes/main_functions.php");

$rss_datum = gmdate('r');

$title = "hockey-LIVE.sk";

$url = "http://www.hockey-live.sk/";

$description = "Stránka venovaná hokeju, štatistikám a tipovaniu";

$lang = "sk";

$email = "redakcia@hockey-live.sk";

$logo = "http://www.hockey-live.sk/logos/hockey_logo_small.gif";



echo '<?xml version="1.0" encoding="UTF-8" ?>'. "\n";

echo '<rss version="2.0">'. "\n";

echo '<channel>'. "\n";

echo '<title>'.$title.'</title>'. "\n";

echo '<link>'.$url.'</link>'. "\n";

echo '<description>'.$description.'</description>'. "\n";

echo '<language>'.$lang.'</language>'. "\n";

echo '<pubDate>'.$rss_datum.'</pubDate>'. "\n";

echo '<lastBuildDate>'.$rss_datum.'</lastBuildDate>'. "\n";

echo '<webMaster>'.$email.'</webMaster>'. "\n";

echo '<image>'. "\n";

echo '<title>'.$title.'</title>'. "\n";

echo '<url>'.$logo.'</url>'. "\n";

echo '<link>'.$url.'</link>'. "\n";

echo '</image>'. "\n". "\n";



$sql = mysql_query("SELECT * FROM e_xoops_stories WHERE topicdisplay='1' ORDER BY published DESC LIMIT 15");

while($kanal = mysql_fetch_array($sql, MYSQL_BOTH)){

$id = $kanal["storyid"]; // id článku

$nadpis = $kanal["title"];

$nadpisurl = SEOTitle($nadpis);
$nadpis = iconv('cp1250','utf-8', $nadpis); 
$nadpisurl = iconv('cp1250','utf-8', $nadpisurl); 
$nadpis = nl2br($nadpis);
$nadpis = trim(strip_tags($nadpis));

$uvod = $kanal["hometext"];

$uvod = iconv('cp1250','utf-8', $uvod); 

//$rss = date("D, d M Y H:i:s \G\M\T", $kanal["published"]);
$rss = date("r", $kanal["published"]);

echo '<item>'. "\n";

echo '<title>'.$nadpis.'</title>'. "\n";

echo '<link>'.$url.'news/'.$id.'-'.$nadpisurl.'</link>'. "\n";

echo '<comments>'.$url.'news/'.$id.'-'.$nadpisurl.'</comments>'. "\n";

echo '<description><![CDATA['.$uvod.']]></description>'. "\n";

echo '<pubDate>'.$rss.'</pubDate>'. "\n";

echo '</item>'. "\n";

}

echo '</channel>'. "\n";

echo '</rss>';



mysql_close($bd); // ukončenie práce s DB

?>
