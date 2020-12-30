<?
if($_GET[lid]) 
  {
  $params = explode("/", htmlspecialchars($_GET[lid]));
  $lid = explode("-", htmlspecialchars($params[0]));
  $lid=$lid[0];
  if($params[1]!="")
    {
    if($params[1]=="division" || $params[1]=="conference" || $params[1]=="league" || $params[1]=="simulation" || $params[1]=="groups" || $params[1]=="playoff" || $params[1]=="roundrobin") $table_type=htmlspecialchars($params[1]);
    }
  if($params[2]!="")
    {
    if($params[2]=="simulation") $sim=1;
    }
  }

$content = "";
// tabulka timov
if($lid)
  {
  $active_league = $lid;
  $content .= Get_Table($lid, $params, $table_type, $sim);
  }
// nebola vybrana ziadna liga
else
  {
  $content .= "Neexistujúca liga";
  }
?>