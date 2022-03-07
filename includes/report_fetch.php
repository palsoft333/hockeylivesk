<?
session_start();
include("db.php");
header('Content-Type: text/html; charset=utf-8');

if(preg_match('/[0-9]+/', $_GET[id])==1)
  {
  $id = $_GET[id];
  $ide = urldecode($_COOKIE['hl-'.$id]);
  }
else
  {
  exit;
  }
  
$el = substr($_GET[id], -1);
$dl = strlen($_GET[id]);
$id = substr($_GET[id], 0, $dl-1);
if($el==1) $matches_table = "el_matches";
else $matches_table = "2004matches";

$q = mysql_query("SELECT * FROM $matches_table WHERE id='$id'");
$n = mysql_num_rows($q);
if($n==0) exit;
$f = mysql_fetch_array($q);
$f[id] .= $el;
// ostatne zapasy na pozadi
$w = mysql_query("SELECT * FROM $matches_table WHERE kedy!='na programe' && kedy!='konečný stav' && id!='$id'");
if(mysql_num_rows($w)>0)
  {
  while($g = mysql_fetch_array($w))
    {
    $f[other].="$g[team1short]|$g[team2short]|$g[goals1]|$g[goals2]|$g[kedy]|$g[id]$el-";
    }
  $f[other] = substr($f[other], 0, -1);
  }
  
echo json_encode($f, JSON_UNESCAPED_UNICODE);


mysql_close($link);
?>