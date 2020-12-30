<?
session_start();
include("db.php");

$uid=$_SESSION['logged'];

// vlozit alebo zmenit tip
if(isset($_POST['place']) && isset($_POST['tip1']) && isset($_POST['tip2']))
{
  $gid=mysql_real_escape_string($_POST['place']);
  $tip1=mysql_real_escape_string($_POST['tip1']);
  $tip2=mysql_real_escape_string($_POST['tip2']);
  
	$el = substr($gid, -1);
	$dl = strlen($gid);
	$ide = substr($gid, 0, $dl-1);
	
	if($el==0) { $tips_table="2004tips"; $matches_table="2004matches"; }
	else { $tips_table="el_tips"; $matches_table="el_matches"; }
	
	$w = mysql_query("SELECT league FROM $matches_table WHERE id='$ide'");
	$f = mysql_fetch_array($w);

  $q = mysql_query("SELECT id FROM $tips_table WHERE matchid='$ide' && userid='$uid'");
  if(mysql_num_rows($q)==0)
    {
    //vlozit novy tip
    mysql_query("INSERT INTO $tips_table (userid, matchid, tip1, tip2, status, league) VALUES ('$uid', '$ide', '$tip1', '$tip2', '1', '$f[league]')") or die(mysql_error());
    echo "INSERT INTO $tips_table (userid, matchid, tip1, tip2, status, league) VALUES ('$uid', '$ide', '$tip1', '$tip2', '1', '$f[league]')";
    }
  else
    {
    //zmenit existujuci tip
    mysql_query("UPDATE $tips_table SET tip1='$tip1', tip2='$tip2' WHERE userid='$uid' && matchid='$ide'");
    }
}

// zmazat tip
if(isset($_POST['delete']))
{
  $gid=mysql_real_escape_string($_POST['delete']);

	$el = substr($gid, -1);
	$dl = strlen($gid);
	$ide = substr($gid, 0, $dl-1);
	
	if($el==0) $tips_table="2004tips";
	else $tips_table="el_tips";

  mysql_query("DELETE FROM $tips_table WHERE userid='$uid' && matchid='$ide'");
}

mysql_close($link);
?>