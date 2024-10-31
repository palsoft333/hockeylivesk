<?
session_start();
//[ { label: "Choice1", value: "value1" }, ... ]
include("db.php");
include("main_functions.php");

if(isset($_GET["term"])) $term = mysqli_real_escape_string($link, $_GET["term"]);
else exit;

if($_GET["f"]>0 && $_GET["d"]>0) $ppos = "pos!='GK' && ";
elseif($_GET["f"]>0 && $_GET["d"]==0) $ppos = "(pos='F' || pos='C' || pos='CE' || pos='RW' || pos='LW') && pos!='GK' && ";
elseif($_GET["f"]==0 && $_GET["d"]>0) $ppos = "(pos='D' || pos='RD' || pos='LD') && pos!='GK' && ";
elseif($_GET["f"]==0 && $_GET["d"]==0) $ppos = "pos='false' && ";
if($_GET["g"]>0) $gpos="GK";
else $gpos="false";

if(isset($_SESSION["knownrosters"]) && $_SESSION["knownrosters"]==1) $q = mysqli_query($link, "(SELECT max(id) as id, name, pos, '1970-01-01' as born, 0 as type FROM `ft_choices` WHERE ".$ppos."name LIKE '%$term%' GROUP BY name ORDER BY id DESC LIMIT 5)
UNION
(SELECT max(id) as id, name, 'GK' as pos, '1970-01-01' as born, 2 as type FROM `ft_choices` WHERE pos='".$gpos."' && name LIKE '%$term%' GROUP BY name ORDER BY id DESC LIMIT 5)");
else {
  if($_GET["g"]>0) {
  $q = mysqli_query($link, "(SELECT max(id) as id, name, pos, born, 0 as type FROM `2004players` WHERE ".$ppos."name LIKE '%$term%' GROUP BY name ORDER BY id DESC LIMIT 5)
  UNION
  (SELECT max(id) as id, name, 'GK' as pos, born, 2 as type FROM `2004goalies` WHERE name LIKE '%$term%' GROUP BY name ORDER BY id DESC LIMIT 5)");
  }
  else $q = mysqli_query($link, "SELECT max(id) as id, name, pos, born, 0 as type FROM `2004players` WHERE ".$ppos."name LIKE '%$term%' GROUP BY name ORDER BY id DESC LIMIT 5");
}

$results = [];
while($f = mysqli_fetch_array($q))
  {
  $info="";
    if($f["pos"]!="") {
        if($f["pos"]=="C" || $f["pos"]=="CE" || $f["pos"]=="RW" || $f["pos"]=="LW") $f["pos"]="F";
        if($f["pos"]=="RD" || $f["pos"]=="LD") $f["pos"]="D";
        if($f["pos"]=="G") $f["pos"]="GK";
        $info .= "(".$f["pos"].")";
    }
    if($f["born"]!="1970-01-01") $info .= " (".date("Y",strtotime($f["born"])).")";
    $image = "<img src='/includes/player_photo.php?name=".$f["name"]."' class='rounded-circle img-thumbnail mr-2' style='width: 40px; height: 40px;'>";
    $results[] = array("label" => $image." <b>".$f["name"]."</b> <span class='small text-muted'>".$info."</span>", "value" => $f["pos"]."-".$f["type"]."-".$f["id"]);
  }
  
echo json_encode($results);

mysqli_close($link);
?>