<?
if(!$_GET['y'] || !$_GET['m']) exit;
header('Content-Type: text/html; charset=utf-8');
include("db.php");

function SEOtitle($title) {
		$znak = array("ď","ľ","š","č","ť","ž","ý","á","í","ĺ","Ĺ","é","ň","ä","ú","ô","ó","ö","ü","Ľ","Š","Č","Ť","Ž","Ý","Á","Í","É","Ě","Ň","Ú","Ó","Ö","Ü", "Ů", "Ř", "Ď", "ř");
		$replacer = array("d","l","s","c","t","z","y","a","i","l","L","e","n","a","u","o","o","o","u","L","S","C","T","Z","Y","A","I","E","E","N","U","O","O","U","U", "R", "D", "r");
    $seotitle = str_replace($znak,$replacer,$title);
    $seotitle = preg_replace("/[^a-zA-Z0-9\s]/","",$seotitle);
    $seotitle = trim($seotitle);
    $seotitle = preg_replace("/\s+/"," ",$seotitle);
    $seotitle = str_replace(" ","-",$seotitle);
    $seotitle = strtolower($seotitle);
    return $seotitle;
}

$year = mysqli_real_escape_string($link, $_GET['y']);
$month = mysqli_real_escape_string($link, $_GET['m']);
$ndate = date("Y-m", strtotime("+1 month", mktime(0,0,0,$month,1,$year)));
$q = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
$q = mysqli_query($link, "SELECT dt.*, et.color, et.longname, EXTRACT(DAY FROM dt.datetime) as den FROM ((SELECT datetime, 0 as kolo, league, 0 as el FROM `2004matches` WHERE datetime >= '$year-$month-01' AND datetime < '$ndate-01')
UNION 
(SELECT IF(EXTRACT(HOUR FROM datetime)<7 && EXTRACT(DAY FROM datetime)>1,datetime - INTERVAL 1 DAY,datetime), kolo, league, 1 as el FROM `el_matches` WHERE datetime >= '$year-$month-01' AND datetime < '$ndate-01')
ORDER BY datetime)dt JOIN (SELECT id, color, longname, position FROM 2004leagues WHERE id!='1')et ON dt.league=et.id GROUP BY EXTRACT(DAY FROM dt.datetime), dt.league ORDER BY den, el, et.position");
if(mysqli_num_rows($q)>0)
  {
  echo "{";
  $prevday=0;
  $i=0;
  while($f = mysqli_fetch_array($q))
    {
    if(date("n", strtotime($f["datetime"]))!=$month && !strstr($f["longname"], 'NHL')) { }
    else
      {
      if($prevday==$f["den"]) echo ', ';
      elseif($i>0) echo '], "'.date("m-d-Y", strtotime($f["datetime"])).'" : [';
      else echo '"'.date("m-d-Y", strtotime($f["datetime"])).'" : [';
      if($f["el"]==1) $url = $f["league"].'-'.SEOtitle($f["longname"]).'/'.$f["kolo"];
      else $url = $f["league"].'-'.SEOtitle($f["longname"]).'/'.date("Y-m-d",strtotime($f["datetime"]));
      echo '{"content": "'.$f["longname"].'", "url": "/games/'.$url.'", "allDay": true, "color": "'.$f["league"].'|'.$f["color"].'"}';
      $prevday=$f["den"];
      $i++;
      }
    }
  echo ']
  }';
 }
else
  {
  echo "{}";
  }
mysqli_close($link);
?>