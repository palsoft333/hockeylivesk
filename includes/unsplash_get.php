<?
include("db.php");

if(isset($_GET["collection"])) $coll = $_GET["collection"];
else exit;
   
$useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
$ch = curl_init('https://api.unsplash.com/photos/random?collections='.$coll.'&client_id='.UNSPLASH_ACCESS_KEY);

curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$r = curl_exec($ch);
curl_close($ch);

$json = json_decode($r, true);

header('Content-Type: application/json');
if(@is_array(getimagesize($json["urls"]["regular"]))) {
  $img = $json["urls"]["regular"];
  $arr = array("image"=>$img, "author"=>$json["user"]["name"], "link"=>"https://unsplash.com/@".$json["user"]["username"]."?utm_source=hockey-LIVE.sk&utm_medium=referral");
  echo json_encode($arr);
}