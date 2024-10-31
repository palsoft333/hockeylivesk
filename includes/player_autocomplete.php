<?
//[ { label: "Choice1", value: "value1" }, ... ]
include("db.php");
include("main_functions.php");

if($_GET["term"]) $term = mysqli_real_escape_string($link, $_GET["term"]);
else exit;

$q = mysqli_query($link, "SELECT dt.* FROM ((SELECT id, name, pos, born, 0 as type FROM `2004players` WHERE name LIKE '%$term%' GROUP BY name ORDER BY id DESC LIMIT 5)
UNION
(SELECT id, name, pos, born, 1 as type FROM `el_players` WHERE name LIKE '%$term%' GROUP BY name ORDER BY id DESC LIMIT 5))dt GROUP BY dt.name");

while($f = mysqli_fetch_array($q))
  {
  $info="";
    if($f["pos"]!="") $info .= "(".$f["pos"].")";
    if($f["born"]!="1970-01-01") $info .= " (".date("Y",strtotime($f["born"])).")";
    $image = "<img src='/includes/player_photo.php?name=".$f["name"]."' class='rounded-circle img-thumbnail mr-2' style='width: 40px; height: 40px;'>";
    $results[] = array("label" => $image." <b>".$f["name"]."</b> <span class='small text-muted'>".$info."</span>", "value" => $f["name"]);
  }
  
echo json_encode($results);

mysqli_close($link);
?>