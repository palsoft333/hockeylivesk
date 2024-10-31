<?
session_start();
include("db.php");
include("table_functions.php");
include("main_functions.php");
if(isset($_SESSION["lang"])) {
  include("lang/lang_".$_SESSION["lang"].".php");
}
else {
   $_SESSION["lang"] = 'sk';
    include("lang/lang_sk.php");
}

header('Content-Type: text/html; charset=utf-8');

if(isset($_GET["lid"]))
  {
  $params = explode("/", htmlspecialchars($_GET["lid"]));
  $lid = explode("-", htmlspecialchars($params[0]));
  $lid=$lid[0];
  if(!isset($params[1])) $params[1]="";
  if(!isset($params[2])) $params[2]="";
  if($params[1]!="")
    {
    if($params[1]=="division" || $params[1]=="conference" || $params[1]=="league" || $params[1]=="groups" || $params[1]=="playoff" || $params[1]=="roundrobin") $table_type=htmlspecialchars($params[1]);
    }
  if($params[2]!="")
    {
    if($params[2]=="simulation") $sim=1;
    }
  }

$content = "";
$table_type = $table_type ?? null;
$sim = $sim ?? null;

if($lid)
  {
  $active_league = $lid;
  echo Get_Table($lid, $params, $table_type, $sim);
  }
else
  {
  echo "Neexistujúca liga";
  }
?>