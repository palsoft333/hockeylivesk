<?
session_start();
include("db.php");

$uid=$_SESSION['logged'];

// vlozit alebo zmenit tip
if(isset($_POST['place']) && isset($_POST['tip1']) && isset($_POST['tip2']))
{
  $gid=mysqli_real_escape_string($link, $_POST['place']);
  $tip1=mysqli_real_escape_string($link, $_POST['tip1']);
  $tip2=mysqli_real_escape_string($link, $_POST['tip2']);
  
	$el = substr($gid, -1);
	$dl = strlen($gid);
	$ide = substr($gid, 0, $dl-1);
	
	if($el==0) { $tips_table="2004tips"; $matches_table="2004matches"; }
	else { $tips_table="el_tips"; $matches_table="el_matches"; }
	
	$w = mysqli_query($link, "SELECT league FROM $matches_table WHERE id='$ide'");
	$f = mysqli_fetch_array($w);

  $q = mysqli_query($link, "SELECT id FROM $tips_table WHERE matchid='$ide' && userid='$uid'");
  if(mysqli_num_rows($q)==0)
    {
    //vlozit novy tip
    mysqli_query($link, "INSERT INTO $tips_table (userid, matchid, tip1, tip2, status, league) VALUES ('$uid', '$ide', '$tip1', '$tip2', '1', '".$f["league"]."')") or die(mysqli_error($link));
    echo "ok";
    }
  else
    {
    //zmenit existujuci tip
    mysqli_query($link, "UPDATE $tips_table SET tip1='$tip1', tip2='$tip2' WHERE userid='$uid' && matchid='$ide'");
    }
}

// zmazat tip
if(isset($_POST['delete']))
{
  $gid=mysqli_real_escape_string($link, $_POST['delete']);

	$el = substr($gid, -1);
	$dl = strlen($gid);
	$ide = substr($gid, 0, $dl-1);
	
	if($el==0) $tips_table="2004tips";
	else $tips_table="el_tips";

  mysqli_query($link, "DELETE FROM $tips_table WHERE userid='$uid' && matchid='$ide'");
}

mysqli_close($link);
?>