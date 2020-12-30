<?
//[ { label: "Choice1", value: "value1" }, ... ]
include("db.php");
include("main_functions.php");

if($_GET[term]) $term = mysql_real_escape_string($_GET[term]);
else exit;

$q = mysql_query("SELECT dt.* FROM ((SELECT id, name, pos, born, 0 as type FROM `2004players` WHERE name LIKE '%$term%' GROUP BY name ORDER BY id DESC LIMIT 5)
UNION
(SELECT id, name, pos, born, 1 as type FROM `el_players` WHERE name LIKE '%$term%' GROUP BY name ORDER BY id DESC LIMIT 5))dt GROUP BY dt.name
UNION
SELECT et.* FROM ((SELECT id, shortname as name, longname as pos, 0 as born, 2 as type FROM `2004teams` WHERE longname LIKE '%$term%' ORDER BY id DESC LIMIT 5)
UNION
(SELECT id, shortname as name, longname as pos, 0 as born, 3 as type FROM `el_teams` WHERE longname LIKE '%$term%' ORDER BY id DESC LIMIT 5))et GROUP BY et.name, et.pos
UNION
(SELECT id, name, 'GK' as pos, born, 4 as type FROM `el_goalies` WHERE name LIKE '%$term%' GROUP BY name ORDER BY id DESC LIMIT 5)
UNION
(SELECT id, country as name, longname as pos, groups as born, 5 as type FROM `2004leagues` WHERE longname LIKE '%$term%' GROUP BY longname ORDER BY id DESC LIMIT 5)");

while($f = mysql_fetch_array($q))
  {
  $info="";
  if($f[type]==0 || $f[type]==1) 
    {
    if($f[pos]!="") $info .= "(".$f[pos].")";
    if($f[born]!="1970-01-01") $info .= " (".date("Y",strtotime($f[born])).")";
    $image = "<img src='/includes/player_photo.php?name=".$f[name]."' class='rounded-circle img-thumbnail mr-2' style='width: 40px; height: 40px;'>";
    $results[] = array("label" => $image." <b>".$f[name]."</b> <span class='small text-muted'>".$info."</span>", "value" => $f[type]."-".$f[id].($f[type]==0 ? '0':'1'));
    }
  if($f[type]==2 || $f[type]==3) 
    {
    $image = "<img class='flag-".($f[type]==2 ? 'iihf':'el')." ".$f[name]."-small' src='/img/blank.png' alt='".$f[pos]."'>";
    $results[] = array("label" => $image." <b>".$f[pos]."</b>", "value" => $f[type]."-".$f[id].($f[type]==2 ? '0':'1'));
    }
  if($f[type]==4) 
    {
    $info .= "(GK)";
    if($f[born]!="1970-01-01") $info .= " (".date("Y",strtotime($f[born])).")";
    $image = "<img src='/includes/player_photo.php?name=".$f[name]."' class='rounded-circle img-thumbnail mr-2' style='width: 40px; height: 40px;'>";
    $results[] = array("label" => $image." <b>".$f[name]."</b> <span class='small text-muted'>".$info."</span>", "value" => $f[type]."-".$f[id]);
    }
  if($f[type]==5) 
    {
    $image = "<i class='ll-".LeagueFont($f[pos])." text-".LeagueColor($f[pos])."'></i>";
    $results[] = array("label" => $image." <b>".$f[pos]."</b>", "value" => $f[type]."-".$f[id].($f[born]!="" ? "/groups" : ""));
    }
  }
  
echo json_encode($results);

mysql_close($link);
?>