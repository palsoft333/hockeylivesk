<?
session_start();
include("db.php");
include("games_functions.php");
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
  if($params[1]!="")
    {
    if(is_numeric($params[1]) || strstr($params[1], "-")) $_GET["sel"]=htmlspecialchars($params[1]);
    else  { $_GET["sel"]="0"; $potype=htmlspecialchars($params[1]); }
    }
  }

// vypisat zoznam zapasov danej ligy
if(isset($lid))
  {
  echo Get_matches($lid, $params, $_GET["sel"], $potype);
  }
// nebol vybrany ziaden zapas alebo liga
else
  {
  echo "Neexistujúca liga alebo zápas";
  }
?>