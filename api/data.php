<?
/**************
hockey-LIVE API
***************
Generate CRC checksum for the DB at: key.php

THEN 

www.hockey-live.sk/api/team/{team}/{league}/{year}?key={API_key} - team statistics
team = 3-char country ISO, eg. SVK
league = WCH for World Championship; OG for Olympic Games; NHL for NHL season; KHL for KHL season; EL for Slovak extraliga season
year = tournament year or season, eg. 2018 (=2018/2019 for NHL season)

www.hockey-live.sk/api/player/{id}?key={API_key} - player statistics by id
www.hockey-live.sk/api/player/{player name}/{league}?key={API_key} - player statistics by name
www.hockey-live.sk/api/game/{id}?key={API_key} - game report by id
www.hockey-live.sk/api/game/{team1}/{team2}/{league}?key={API_key} - last game report by teams
www.hockey-live.sk/api/games/{league}/{year}?key={API_key} - game list
www.hockey-live.sk/api/table/{league}/{year}?key={API_key} - team standings
*/

include("../includes/db.php");
header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
header('Access-Control-Max-Age: 1000');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
$seconds_to_cache = 3600;
$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
header("Expires: $ts");
header("Pragma: cache");
header("Cache-Control: max-age=$seconds_to_cache");
header('Content-Type: application/json');

$key = $_GET["key"];
if(!$key) die("API key required");
else 
  {
  $crc = md5($key);
  $q = mysqli_query($link, "SELECT * FROM api_keys WHERE checksum='".$crc."' LIMIT 1");
  if(mysqli_num_rows($q)==0) die("Incorrect API key");
  else
    {
    $f = mysqli_fetch_array($q);
    $lang = $f["lang"];
    if($f["active"]==0) die("API is not active. Contact API provider.");
    $headers = json_encode(getallheaders(), JSON_HEX_QUOT);
    mysqli_query($link, "UPDATE api_keys SET last_use='".date("Y-m-d H:i:s")."', hits=hits+1 WHERE checksum='".$crc."'");
    mysqli_query($link, "UPDATE api_keys SET headers='".$headers."' WHERE checksum='".$crc."'");
    }
  }

function h2h_reorder1($uloha, $lid, $league_data)
  {
  Global $link;
  $m=$points=0;
  $tshort="";
  $reord = [];
  while($data = mysqli_fetch_array($uloha))
    {
    $posun=0;
    if($league_data["endbasic"]==1) $np = $data["p_basic"];
    else $np = $data["body"];
    if($points==$np)
      {
      $vzaj = mysqli_query($link, "SELECT IF(team1short='".$data["shortname"]."',IF(goals1>goals2,1,0),IF(goals1>goals2,0,1)) as posun FROM 2004matches WHERE (team1short='".$data["shortname"]."' && team2short='$tshort' || team1short='$tshort' && team2short='".$data["shortname"]."') && league='$lid' && kedy='konečný stav'");
      $vzajom = mysqli_fetch_array($vzaj);
      if($vzajom["posun"]==1)
        {
        $mm = $m-1;
        $reord[$m] = $reord[$mm];
        $m--;
        $posun=1;
        }
      }
    $games = $data["zapasov"];
    $wins = $data["wins"];
    $losts = $data["losts"];
    $ties = 0;
    $goals = $data["goals"].":".$data["ga"];
    $points = $data["body"];
    $tshort = $data["shortname"];
    $tid = $data["id"];
    if($league_data["endbasic"]==1) { $wins = $data["w_basic"]; $losts = $data["l_basic"]; $goals = $data["gf_basic"].":".$data["ga_basic"]; $points = $data["p_basic"]; $games=$data["w_basic"]+$data["l_basic"]; }
    $reord[$m] = array($data["shortname"], $data["longname"], $wins, $losts, $ties, $goals, $points, $data["league"], $games, $tid);
    $m++;
    if($posun==1) $m++;
    }
  return $reord;
  }
  
function TeamParser($team)
  {
  $slovak_teams = array("Bielorusko","Dánsko","Česko","Čína","Európa","Fínsko","Francúzsko","Japonsko","Južná Kórea","Kanada","Kazachstan","Lotyšsko","Maďarsko","Nemecko","Nórsko","Poľsko","Rakúsko","Rusko","Severná Amerika","Slovensko","Slovinsko","Taliansko","Ukrajina","USA","Veľká Británia","Švajčiarsko","Švédsko");
  $foreign_teams = array("Belarus","Denmark","Czechia","China","Europe","Finland","France","Japan","South Korea","Canada","Kazakhstan","Latvia","Hungary","Germany","Norway","Poland","Austria","Russia","North America","Slovakia","Slovenia","Italy","Ukraine","USA","Great Britain","Switzerland","Sweden");
  $newname = str_replace($slovak_teams,$foreign_teams,$team);
  return $newname;
  }
  
function StatusParser($status)
  {
  $slovak = array("konečný stav","na programe","pripravte sa","v 1.tretine","po 1.tretine","v 2.tretine","po 2.tretine","v 3.tretine","po 3.tretine","v predlzeni","po predlzeni");
  $english = array("final result","scheduled","get ready","in the 1st period","after 1st period","in the 2nd period","after 2nd period","in the 3rd period","after 3rd period","overtime","after overtime");
  $newname = str_replace($slovak,$english,$status);
  return $newname;
  }
  
function LeagueFont($name) {
    if(strstr($name, 'Tipsport') || strstr($name, 'Extraliga')) $font = "tipsport";
    elseif(strstr($name, 'KHL')) $font = "khl";
    elseif(strstr($name, 'NHL')) $font = "nhl";
    elseif(strstr($name, 'MS')) $font = "iihf";
    elseif(strstr($name, 'ZOH')) $font = "olympics";
    elseif(strstr($name, 'Kaufland') || strstr($name, 'Slovakia') || strstr($name, 'Loto')) $font = "kauflandcup";
    elseif(strstr($name, 'Challenge') || strstr($name, 'Škoda')) $font = "arosa";
    else $font = "dcup";
    return $font;
}
   
// START

if(isset($_GET["team"]))
  {
  $team = mysqli_real_escape_string($link, $_GET["team"]);
  if(strlen($team)!=3) die("Incorrect team shortname");
  $t = mysqli_real_escape_string($link, $_GET["tournament"]);
  $year = mysqli_real_escape_string($link, $_GET["year"]);
  if($t=="WCH") 
    {
    if($year<2004 || $year>date("Y")) die("Incorrect tournament year");
    $lname = "MS ".$year;
    $el = 0;
    }
  elseif($t=="WJC") 
    {
    if($year<2021 || $year>date("Y")+1) die("Incorrect tournament year");
    $lname = "MS U20 ".$year;
    $el = 0;
    }
  elseif($t=="OG") 
    {
    if($year<2006 || $year>date("Y")) die("Incorrect tournament year");
    $lname = "ZOH % ".$year;
    $el = 0;
    }
  elseif($t=="NHL") 
    {
    if($year<2009 || $year>date("Y")) die("Incorrect season");
    $sea = substr($year,2,2);
    $seas = $sea+1;
    $season = $sea."/".$seas;
    $lname = "NHL ".$season;
    $el = 1;
    }
  elseif($t=="KHL") 
    {
    if($year<2010 || $year>date("Y")) die("Incorrect season");
    $sea = substr($year,2,2);
    $seas = $sea+1;
    $season = $sea."/".$seas;
    $lname = "KHL ".$season;
    $el = 1;
    }
  elseif($t=="EL") 
    {
    if($year<2005 || $year>date("Y")) die("Incorrect season");
    $sea = substr($year,2,2);
    $seas = $sea+1;
    $season = $sea."/".$seas;
    $lname = "%liga ".$season;
    $el = 1;
    }
  else die("Incorrect tournament name");
  $q = mysqli_query($link, "SELECT * FROM 2004leagues WHERE longname LIKE '".$lname."'");
  $f = mysqli_fetch_array($q);
  if($el==1) {
      $p = mysqli_query($link, "SELECT el_players.*, dt.injury FROM el_players LEFT JOIN (SELECT name, injury FROM el_injuries WHERE league='".$f["id"]."')dt ON el_players.name=dt.name WHERE el_players.teamshort='".$team."' && el_players.league='".$f["id"]."' ORDER BY points DESC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC");
      $g = mysqli_query($link, "SELECT el_goalies.*, dt.injury FROM el_goalies LEFT JOIN (SELECT name, injury FROM el_injuries WHERE league='".$f["id"]."')dt ON el_goalies.name=dt.name WHERE el_goalies.teamshort='".$team."' && el_goalies.league='".$f["id"]."' ORDER BY name;");
  }
  else {
      $p = mysqli_query($link, "SELECT 2004players.*, dt.injury FROM 2004players LEFT JOIN (SELECT name, injury FROM el_injuries WHERE league='".$f["id"]."')dt ON 2004players.name=dt.name WHERE 2004players.teamshort='".$team."' && 2004players.league='".$f["id"]."' ORDER BY points DESC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC");
      $g = mysqli_query($link, "SELECT 2004goalies.*, dt.injury FROM 2004goalies LEFT JOIN (SELECT name, injury FROM el_injuries WHERE league='".$f["id"]."')dt ON 2004goalies.name=dt.name WHERE 2004goalies.teamshort='".$team."' && 2004goalies.league='".$f["id"]."' ORDER BY name");
  }
  $i=0;
  while($y = mysqli_fetch_array($p))
    {
    if($el==0)
      {
      if($y["pos"]=="LD" || $y["pos"]=="RD") $y["pos"]="D";
      if($y["pos"]=="CE" || $y["pos"]=="RW" || $y["pos"]=="LW") $y["pos"]="F";
      }
    $players["players"][$i]["id"] = $y["id"].$el;
    $players["players"][$i]["name"] = $y["name"];
    if($y["injury"]!=NULL) $players["players"][$i]["injury"] = $y["injury"];
    else $players["players"][$i]["injury"] = 0;
    $players["players"][$i]["pos"] = $y["pos"];
    if($el==1) 
      {
      $players["players"][$i]["bio"]["born"] = $y["born"];
      $players["players"][$i]["bio"]["hold"] = $y["hold"];
      $players["players"][$i]["bio"]["kg"] = $y["kg"];
      $players["players"][$i]["bio"]["cm"] = $y["cm"];
      }
    $players["players"][$i]["stats"]["gp"] = $y["gp"];
    $players["players"][$i]["stats"]["goals"] = $y["goals"];
    $players["players"][$i]["stats"]["asists"] = $y["asists"];
    $players["players"][$i]["stats"]["points"] = $y["points"];
    $players["players"][$i]["stats"]["penalty"] = $y["penalty"];
    $players["players"][$i]["stats"]["ppg"] = $y["ppg"];
    $players["players"][$i]["stats"]["shg"] = $y["shg"];
    $players["players"][$i]["stats"]["gwg"] = $y["gwg"];
    $i++;
    }
  $i=0;
  while($y = mysqli_fetch_array($g))
    {
    $players["goalies"][$i]["id"] = $y["id"].$el;
    $players["goalies"][$i]["name"] = $y["name"];
    if($y["injury"]!=NULL) $players["goalies"][$i]["injury"] = $y["injury"];
    else $players["goalies"][$i]["injury"] = 0;
    $players["goalies"][$i]["bio"]["born"] = $y["born"];
    $players["goalies"][$i]["bio"]["hold"] = $y["hold"];
    $players["goalies"][$i]["bio"]["kg"] = $y["kg"];
    $players["goalies"][$i]["bio"]["cm"] = $y["cm"];
    $players["goalies"][$i]["stats"]["gp"] = $y["gp"];
    $players["goalies"][$i]["stats"]["sog"] = $y["sog"];
    $players["goalies"][$i]["stats"]["svs"] = $y["svs"];
    $players["goalies"][$i]["stats"]["ga"] = $y["ga"];
    $players["goalies"][$i]["stats"]["so"] = $y["so"];
    $players["goalies"][$i]["stats"]["pim"] = $y["pim"];
    $i++;
    }
  if($el==1) $o = mysqli_query($link, "SELECT m.*, IF(kolo=0,p.potype,NULL) as po_type FROM el_matches m LEFT JOIN el_playoff p ON (p.team1=m.team1short && p.team2=m.team2short && p.league='".$f["id"]."') || (p.team2=m.team1short && p.team1=m.team2short && p.league='".$f["id"]."') WHERE (m.team1short='".$team."' || m.team2short='".$team."') && m.league='".$f["id"]."' ORDER BY m.datetime");
  else $o = mysqli_query($link, "SELECT * FROM 2004matches WHERE (team1short='$team' || team2short='$team') && league='".$f["id"]."' ORDER BY datetime");
  $i=0;
  while($y = mysqli_fetch_array($o))
    {
    if($lang=="en")
      {
      $y["team1long"] = TeamParser($y["team1long"]);
      $y["team2long"] = TeamParser($y["team2long"]);
      $y["kedy"] = StatusParser($y["kedy"]);
      }
    if($el==1) {
        $y["po_type"] = str_replace("stvrt", "QF", $y["po_type"]);
        $y["po_type"] = str_replace("semi", "SF", $y["po_type"]);
        $y["po_type"] = str_replace("final", "F", $y["po_type"]);
        $y["po_type"] = str_replace("baraz", "Q", $y["po_type"]);
        $y["po_type"] = str_replace("stanley", "SC", $y["po_type"]);
        if($y["po_type"]=="") $y["po_type"]=NULL;
    }
    $players["games"][$i]["id"] = $y["id"].$el;
    $players["games"][$i]["po_type"] = $y["po_type"];
    $players["games"][$i]["team1short"] = $y["team1short"];
    $players["games"][$i]["team1long"] = $y["team1long"];
    $players["games"][$i]["team2short"] = $y["team2short"];
    $players["games"][$i]["team2long"] = $y["team2long"];
    $players["games"][$i]["date"] = $y["datetime"];
    $players["games"][$i]["score"]["goals1"] = $y["goals1"];
    $players["games"][$i]["score"]["goals2"] = $y["goals2"];
    $players["games"][$i]["score"]["status"] = $y["kedy"];
    $i++;
    }
    
  $players = json_encode($players, JSON_UNESCAPED_UNICODE);
  echo $players;
  /*echo "<pre>";
  echo print_r($players);
  echo "</pre>";*/
  }
elseif(isset($_GET["player"]))
  {
  $id = mysqli_real_escape_string($link, $_GET["id"]);
  if(is_numeric($id))
    {
    $el = substr($id, -1);
    $dl = strlen($id);
    $ide = substr($id, 0, $dl-1);
    if($el==1) $q = mysqli_query($link, "SELECT * FROM el_players WHERE id='$ide'");
    else $q = mysqli_query($link, "SELECT * FROM 2004players WHERE id='$ide'");
    if(mysqli_num_rows($q)>0)
      {
      $data = mysqli_fetch_array($q);
      }
    else die("Incorrect player ID");
    }
  else 
    {
    $t = $_GET["tournament"];
    if($t=="WCH" || $t=="OG") $el = 0;
    else $el = 1;
    $name = $_GET["id"];
    if($el==1) $q = mysqli_query($link, "SELECT * FROM el_players WHERE name='$name' ORDER BY id DESC LIMIT 1");
    else $q = mysqli_query($link, "SELECT * FROM 2004players WHERE name='$name' ORDER BY id DESC LIMIT 1");
    if(mysqli_num_rows($q)>0)
      {
      $data = mysqli_fetch_array($q);
      }
    else die("No such player in database");
    }
  if($data["name"]=="MIKUŠ Juraj" || $data["name"]=="MIKÚŠ Juraj") 
    {
    mysqli_query($link, "SET NAMES 'latin1'");
    $coll = " COLLATE latin1_bin";
    }
  else $coll="";
  $elinf = mysqli_query($link, "SELECT name, max(pos) as pos, max(born) as born, max(hold) as hold, max(kg) as kg, max(cm) as cm FROM el_players WHERE name='".$data["name"]."'$coll ORDER BY id DESC LIMIT 1");
  $elinfo = mysqli_fetch_array($elinf);
  if($elinfo["name"]==NULL)
    {
    $elinf = mysqli_query($link, "SELECT name, max(pos) as pos, max(born) as born, max(hold) as hold, max(kg) as kg, max(cm) as cm FROM 2004players WHERE name='".$data["name"]."'$coll ORDER BY id DESC LIMIT 1");
    $elinfo = mysqli_fetch_array($elinf);
    }
  if($lang=="en") $data["teamlong"] = TeamParser($data["teamlong"]);
  $player["id"] = $data["id"].$el;
  $player["name"] = $data["name"];
  $player["teamshort"] = $data["teamshort"];
  $player["teamlong"] = $data["teamlong"];
  $player["jersey"] = $data["jersey"];
  $player["pos"] = $elinfo["pos"];
  $player["bio"]["born"] = $elinfo["born"];
  $player["bio"]["hold"] = $elinfo["hold"];
  $player["bio"]["kg"] = $elinfo["kg"];
  $player["bio"]["cm"] = $elinfo["cm"];
  if($el==1) 
    {
    $w = mysqli_query($link, "SELECT el_players.*, l.longname, t.id as tid, m.datetime as firstgame FROM el_players JOIN 2004leagues l ON l.id=el_players.league JOIN el_teams t ON t.shortname=el_players.teamshort && t.league=el_players.league LEFT JOIN el_matches m ON m.league=el_players.league WHERE name='".$data["name"]."'$coll GROUP BY el_players.league ORDER BY firstgame ASC");
    $celk = mysqli_query($link, "SELECT sum(goals), sum(asists), sum(points), sum(penalty), sum(ppg), sum(shg), sum(gwg), sum(gp) FROM el_players WHERE name='".$data["name"]."'$coll");
    }
  else 
    {
    $w = mysqli_query($link, "SELECT 2004players.*, l.longname, t.id as tid, m.datetime as firstgame FROM 2004players JOIN 2004leagues l ON l.id=2004players.league JOIN 2004teams t ON t.shortname=2004players.teamshort && t.league=2004players.league LEFT JOIN 2004matches m ON m.league=2004players.league WHERE name='".$data["name"]."'$coll GROUP BY 2004players.league ORDER BY firstgame ASC");
    $celk = mysqli_query($link, "SELECT sum(goals), sum(asists), sum(points), sum(penalty), sum(ppg), sum(shg), sum(gwg), sum(gp) FROM 2004players WHERE name='".$data["name"]."'$coll");
    }
  $sumar = mysqli_fetch_array($celk);
  $player["overall"]["gp"] = $sumar[7];
  $player["overall"]["goals"] = $sumar[0];
  $player["overall"]["asists"] = $sumar[1];
  $player["overall"]["points"] = $sumar[2];
  $player["overall"]["penalty"] = $sumar[3];
  $player["overall"]["ppg"] = $sumar[4];
  $player["overall"]["shg"] = $sumar[5];
  $player["overall"]["gwg"] = $sumar[6];
  $i=0;
  while($f = mysqli_fetch_array($w))
    {
    if($lang=="en") $f["teamlong"] = TeamParser($f["teamlong"]);
    $player["league"][$i]["name"] = $f["longname"];
    $player["league"][$i]["team"] = $f["teamlong"];
    $player["league"][$i]["stats"]["gp"] = $f["gp"];
    $player["league"][$i]["stats"]["goals"] = $f["goals"];
    $player["league"][$i]["stats"]["asists"] = $f["asists"];
    $player["league"][$i]["stats"]["points"] = $f["points"];
    $player["league"][$i]["stats"]["penalty"] = $f["penalty"];
    $player["league"][$i]["stats"]["ppg"] = $f["ppg"];
    $player["league"][$i]["stats"]["shg"] = $f["shg"];
    $player["league"][$i]["stats"]["gwg"] = $f["gwg"];
    $i++;
    }
  $di = mysqli_query($link, "SELECT * FROM 2004playerdiary WHERE name='".$data["name"]."' ORDER BY msg_date DESC LIMIT 10");
  $i=0;
  while($dia = mysqli_fetch_array($di)) {
      $player["diary"][$i]["date"] = $dia["msg_date"];
      $player["diary"][$i]["type"] = $dia["msg_type"];
      $player["diary"][$i]["msg"] = $dia["msg"];
      $i++;
  }
  $player = json_encode($player, JSON_UNESCAPED_UNICODE);
  if($lang=="en")
    {
    $player = str_replace("Zranil sa", "Got injured", $player);
	$player = str_replace("brucho", "Abdomen", $player);
	$player = str_replace("achilovka", "Achilles", $player);
	$player = str_replace("členok", "Ankle", $player);
	$player = str_replace(">plece<", ">Arm<", $player);
	$player = str_replace("chrbát", "Back", $player);
	$player = str_replace("hruď", "Chest", $player);
	$player = str_replace("rozhodnutie trénera", "Coach`s Decision", $player);
	$player = str_replace("kľúčna kosť", "Collarbone", $player);
	$player = str_replace("zmluvný spor", "Contract Dispute", $player);
	$player = str_replace("otras mozgu", "Concussion", $player);
	$player = str_replace("kľúčna kosť", "Clavicle", $player);
	$player = str_replace("lakeť", "Elbow", $player);
	$player = str_replace(">oko<", ">Eye<", $player);
	$player = str_replace(">tvár<", ">Face<", $player);
	$player = str_replace(">prst<", ">Finger<", $player);
	$player = str_replace("chodidlo", "Foot", $player);
	$player = str_replace("chrípka", "Flu", $player);
	$player = str_replace("všeobecná bolesť", "General Soreness", $player);
	$player = str_replace("triesla", "Groin", $player);
	$player = str_replace(">ruka<", ">Hand<", $player);
	$player = str_replace("podkolenná šľacha", "Hamstring", $player);
	$player = str_replace(">hlava<", ">Head<", $player);
	$player = str_replace(">päta<", ">Heel<", $player);
	$player = str_replace("prietrž", "Hernia", $player);
	$player = str_replace(">bedro<", ">Hip<", $player);
	$player = str_replace("choroba", "Illness", $player);
	$player = str_replace("čeľusť", "Jaw", $player);
	$player = str_replace(">koleno<", ">Knee<", $player);
	$player = str_replace(">noha<", ">Leg<", $player);
	$player = str_replace("dolná časť tela", "Lower body", $player);
	$player = str_replace("dolná časť tela", "Lower Body", $player);
	$player = str_replace(">krk<", ">Neck<", $player);
	$player = str_replace(">nos<", ">Nose<", $player);
	$player = str_replace("nejde o zranenie", "Not Injury Related", $player);
	$player = str_replace(">panva<", ">Pelvis<", $player);
	$player = str_replace("hrudník", "Pectoral", $player);
	$player = str_replace("osobné dôvody", "Personal", $player);
	$player = str_replace("odpočinok", "Rest", $player);
	$player = str_replace("rebrá", "ribs", $player);
	$player = str_replace("rebrá", "Ribs", $player);
	$player = str_replace(">rameno<", ">Shoulder<", $player);
	$player = str_replace("suspenzácia", "Suspension", $player);
	$player = str_replace(">palec<", ">Thumb<", $player);
	$player = str_replace("horná časť tela", "Upper body", $player);
	$player = str_replace("horná časť tela", "Upper Body", $player);
	$player = str_replace("bližšie nešpecifikované", "Undisclosed", $player);
	$player = str_replace("zápästie", "Wrist", $player);
    $player = str_replace("Prestúpil z tímu", "Transferred from the team", $player);
    $player = str_replace("do tímu", "to the team", $player);
    $player = str_replace("Pridal sa k tímu", "He joined the team", $player);
    $player = str_replace("Bol nominovaný na turnaj", "He was nominated for the tournament", $player);
    $player = str_replace("Dosiahol hattrick v zápase proti", "He scored a hat trick in the game against", $player);
    $player = str_replace("Dosiahol jubilejný", "He scored the jubilee", $player);
    $player = str_replace(".gól v sezóne", "th goal of the season", $player);
    $player = str_replace("Strelil víťazný gól v zápase proti tímu", "He scored the winning goal in the game against", $player);
    $player = str_replace("Vychytal čisté konto v zápase proti", "He records a shutout in the game against", $player);
    $player = str_replace("S tímom ", "He won the title of champion of Slovakia with the team ", $player);
    $player = str_replace("získal titul majstra Slovenska", "", $player);
    $player = str_replace("Získal zlatú medailu s tímom", "He won a gold medal with the team", $player);
    $player = str_replace("Uzdravil sa", "He is no longer injured", $player);
    $player = str_replace("Stal sa neobmedzeným voľným hráčom", "He became an unrestricted free agent", $player);
    }
  echo $player;
  /*echo "<pre>";
  echo print_r($player);
  echo "</pre>";*/
  }
elseif(isset($_GET["game"]))
  {
  $id = mysqli_real_escape_string($link, $_GET["game"]);
  if($id==1)
    {
    if(!$_GET["team1"] || !$_GET["team2"] || strlen($_GET["team1"])!=3 || strlen($_GET["team2"])!=3 || !$_GET["tournament"]) die("Missing required parameters");
    $t = mysqli_real_escape_string($link, $_GET["tournament"]);
    $_GET["team1"] = mysqli_real_escape_string($link, $_GET["team1"]);
    $_GET["team2"] = mysqli_real_escape_string($link, $_GET["team2"]);
    if($t=="WCH" || $t=="OG") $el = 0;
    else $el = 1;
    if($el==1) $q = mysqli_query($link, "SELECT * FROM `el_matches` WHERE (team1short='".$_GET["team1"]."' && team2short='".$_GET["team2"]."' && kedy!='na programe') || (team1short='".$_GET["team2"]."' && team2short='".$_GET["team1"]."' && kedy!='na programe') ORDER BY datetime DESC LIMIT 1");
    else $q = mysqli_query($link, "SELECT * FROM `2004matches` WHERE (team1short='".$_GET["team1"]."' && team2short='".$_GET["team2"]."' && kedy!='na programe') || (team1short='".$_GET["team2"]."' && team2short='".$_GET["team1"]."' && kedy!='na programe') ORDER BY datetime DESC LIMIT 1");
    }
  else
    {
    $el = substr($id, -1);
    $dl = strlen($id);
    $ide = substr($id, 0, $dl-1);
    if($el==1) $q = mysqli_query($link, "SELECT m.*, IF(kolo=0,p.potype,NULL) as po_type FROM el_matches m LEFT JOIN el_playoff p ON (p.team1=m.team1short && p.team2=m.team2short && p.league=m.league) || (p.team2=m.team1short && p.team1=m.team2short && p.league=m.league) WHERE m.id='".$ide."'");
    else $q = mysqli_query($link, "SELECT * FROM 2004matches WHERE id='$ide'");
    }
  if(mysqli_num_rows($q)>0)
    {
    $y = mysqli_fetch_array($q);
    }
  else die("Incorrect game ID");
  if($lang=="en") 
    {
    $y["team1long"] = TeamParser($y["team1long"]);
    $y["team2long"] = TeamParser($y["team2long"]);
    $y["kedy"] = StatusParser($y["kedy"]);
    }
  if($el==1) {
    $y["po_type"] = str_replace("stvrt", "QF", $y["po_type"]);
    $y["po_type"] = str_replace("semi", "SF", $y["po_type"]);
    $y["po_type"] = str_replace("final", "F", $y["po_type"]);
    $y["po_type"] = str_replace("baraz", "Q", $y["po_type"]);
    $y["po_type"] = str_replace("stanley", "SC", $y["po_type"]);
    if($y["po_type"]=="") $y["po_type"]=NULL;
  }
  $game["id"] = $y["id"].$el;
  $game["po_type"] = $y["po_type"];
  $game["team1short"] = $y["team1short"];
  $game["team1long"] = $y["team1long"];
  $game["team2short"] = $y["team2short"];
  $game["team2long"] = $y["team2long"];
  if(isset($_GET["tz"])) { 
      $game["date"] = new DateTime($y["datetime"], new DateTimeZone('Europe/Bratislava'));
      $game["date"]->setTimezone(new DateTimeZone($_GET["tz"]));
  }
  else $game["date"] = $y["datetime"];
  $game["score"]["goals1"] = $y["goals1"];
  $game["score"]["goals2"] = $y["goals2"];
  $game["score"]["status"] = $y["kedy"];
  $game["score"]["overtime"] = 0;
  if($el==1) $w = mysqli_query($link, "SELECT * FROM el_goals WHERE matchno='".$y["id"]."' ORDER BY time AsC, id ASC");
  else $w = mysqli_query($link, "SELECT * FROM 2004goals WHERE matchno='".$y["id"]."' ORDER BY time AsC, id ASC");
  $i=0;
  while($f = mysqli_fetch_array($w))
    {
    if($lang=="en") $f["teamlong"] = TeamParser($f["teamlong"]);
    $game["goals"][$i]["time"] = $f["time"];
    $game["goals"][$i]["teamshort"] = $f["teamshort"];
    $game["goals"][$i]["teamlong"] = $f["teamlong"];
    $game["goals"][$i]["goaler"] = $f["goaler"];
    $game["goals"][$i]["asister1"] = $f["asister1"];
    $game["goals"][$i]["asister2"] = $f["asister2"];
    $game["goals"][$i]["status"] = $f["status"];
    $game["goals"][$i]["when"] = $f["kedy"];
    if($f["time"]=="65.00" || $f["time"]=="70.00" || $f["time"]=="80.00") $game["score"]["overtime"]=2;
    elseif($f["time"]>"60.00") $game["score"]["overtime"]=1;
    $i++;
    }
  if($el==1) $e = mysqli_query($link, "SELECT * FROM el_penalty WHERE matchno='".$y["id"]."' ORDER BY time ASC");
  else $e = mysqli_query($link, "SELECT * FROM 2004penalty WHERE matchno='".$y["id"]."' ORDER BY time ASC");
  $i=0;
  while($r = mysqli_fetch_array($e))
    {
    if($lang=="en") $r["teamlong"] = TeamParser($r["teamlong"]);
    $game["penalties"][$i]["time"] = $r["time"];
    $game["penalties"][$i]["teamshort"] = $r["teamshort"];
    $game["penalties"][$i]["teamlong"] = $r["teamlong"];
    $game["penalties"][$i]["player"] = $r["player"];
    $game["penalties"][$i]["minutes"] = $r["minutes"];
    $game["penalties"][$i]["penalty"] = $r["kedy"];
    $i++;
    }
  if($el==1) $i = mysqli_query($link, "(SELECT *, 1 as roz FROM el_matches WHERE (team1short='".$y["team1short"]."' || team2short='".$y["team1short"]."') && kedy!='na programe' ORDER BY datetime DESC LIMIT 5) UNION (SELECT *, 2 as roz FROM el_matches WHERE (team1short='".$y["team2short"]."' || team2short='".$y["team2short"]."') && kedy!='na programe' ORDER BY datetime DESC LIMIT 5)");
  else $i = mysqli_query($link, "(SELECT *, 1 as roz FROM 2004matches WHERE (team1short='".$y["team1short"]."' || team2short='".$y["team1short"]."') && kedy!='na programe' ORDER BY datetime DESC LIMIT 5) UNION (SELECT *, 2 as roz FROM 2004matches WHERE (team1short='".$y["team2short"]."' || team2short='".$y["team2short"]."') && kedy!='na programe' ORDER BY datetime DESC LIMIT 5)");
  $z1=$z2=0;
  while($j = mysqli_fetch_array($i))
    {
    if($j["roz"]==1)
      {
      $lastt1[$z1] = array($j["team1short"], $j["team1long"], $j["team2short"], $j["team2long"], $j["goals1"], $j["goals2"], $j["datetime"], $j["id"]);
      $roz1++;
      $z1++;
      }
    else
      {
      $lastt2[$z2] = array($j["team1short"], $j["team1long"], $j["team2short"], $j["team2long"], $j["goals1"], $j["goals2"], $j["datetime"], $j["id"]);
      $roz2++;
      $z2++;
      }
    }
  $lastt1 = array_reverse($lastt1);
  $lastt2 = array_reverse($lastt2);
  $x=$w1=$w2=0;
  while($x < max($roz1, $roz2))
    {
    $color1=$color2="";
    if($lastt1[$x][0]==$y["team1short"])
      {
      if($lastt1[$x][4]>$lastt1[$x][5]) { $color1="win"; $w1++; }
      else $color1="loss";
      $vs1 = $lastt1[$x][3];
      $kde1 = "home";
      }
    else 
      {
      if($lastt1[$x][4]>$lastt1[$x][5]) $color1="loss";
      else { $color1="win"; $w1++; }
      $vs1 = $lastt1[$x][1];
      $kde1 = "away";
      }
    if($lastt2[$x][0]==$y["team2short"]) 
      {
      if($lastt2[$x][4]>$lastt2[$x][5]) { $color2="win"; $w2++; }
      else $color2="loss";
      $vs2 = $lastt2[$x][3];
      $kde2 = "home";
      }
    else
      {
      if($lastt2[$x][4]>$lastt2[$x][5]) $color2="loss";
      else { $color2="win"; $w2++; }
      $vs2 = $lastt2[$x][1];
      $kde2 = "away";
      }
    if($lang=="en") 
      {
      $vs1 = TeamParser($vs1);
      $vs2 = TeamParser($vs2);
      }
    if(isset($_GET["tz"])) { 
        $game["last5games"]["team1"][$x]["date"] = new DateTime($lastt1[$x][6], new DateTimeZone('Europe/Bratislava'));
        $game["last5games"]["team1"][$x]["date"]->setTimezone(new DateTimeZone($_GET["tz"]));
    }
    else $game["last5games"]["team1"][$x]["date"] = $lastt1[$x][6];
    $game["last5games"]["team1"][$x]["versus"] = $vs1;
    $game["last5games"]["team1"][$x]["where"] = $kde1;
    $game["last5games"]["team1"][$x]["score"] = $lastt1[$x][4].":".$lastt1[$x][5];
    $game["last5games"]["team1"][$x]["result"] = $color1;
    $game["last5games"]["team2"][$x]["versus"] = $vs2;
    $game["last5games"]["team2"][$x]["where"] = $kde2;
    $game["last5games"]["team2"][$x]["score"] = $lastt2[$x][4].":".$lastt2[$x][5];
    $game["last5games"]["team2"][$x]["result"] = $color2;
    $x++;
    }
  if($el==1) $i = mysqli_query($link, "SELECT * FROM el_matches WHERE (team1short='".$y["team1short"]."' && team2short='".$y["team2short"]."' && kedy!='na programe') || (team1short='".$y["team2short"]."' && team2short='".$y["team1short"]."' && kedy!='na programe') ORDER BY datetime DESC LIMIT 5");
  else $i = mysqli_query($link, "SELECT * FROM 2004matches WHERE (team1short='".$y["team1short"]."' && team2short='".$y["team2short"]."' && kedy!='na programe') || (team1short='".$y["team2short"]."' && team2short='".$y["team1short"]."' && kedy!='na programe') ORDER BY datetime DESC LIMIT 5");
  while($j = mysqli_fetch_array($i))
    {
    $h2h[] = array($j["team1short"], $j["team1long"], $j["team2short"], $j["team2long"], $j["goals1"], $j["goals2"], $j["datetime"], $j["id"]);
    }
  $h2h = array_reverse($h2h);
  $x=0;
  while($x < count($h2h))
    {
    $winner="";
    if($h2h[$x][0]==$y["team1short"])
      {
      if($h2h[$x][4]>$h2h[$x][5]) { $winner="1"; }
      else { $winner="2"; }
      $g1=$h2h[$x][4];
      $g2=$h2h[$x][5];
      }
    else 
      {
      if($h2h[$x][4]>$h2h[$x][5]) { $winner="2"; }
      else { $winner="1"; }
      $g1=$h2h[$x][5];
      $g2=$h2h[$x][4];
      }
    if(isset($_GET["tz"])) { 
        $game["last5head2head"][$x]["date"] = new DateTime($h2h[$x][6], new DateTimeZone('Europe/Bratislava'));
        $game["last5head2head"][$x]["date"]->setTimezone(new DateTimeZone($_GET["tz"]));
    }
    else $game["last5head2head"][$x]["date"] = $h2h[$x][6];
    $game["last5head2head"][$x]["score"] = $g1.":".$g2;
    $game["last5head2head"][$x]["winner"] = $winner;
    $x++;
    }
  if($el==1) $c = mysqli_query($link, "SELECT *, dt.arena FROM el_teams JOIN (SELECT * FROM el_infos)dt ON shortname=dt.teamshort WHERE shortname='".$y["team1short"]."' && league='".$y["league"]."'");
  else $c = mysqli_query($link, "SELECT *, dt.arena FROM 2004teams JOIN (SELECT * FROM el_infos)dt ON shortname=dt.teamshort WHERE shortname='".$y["team1short"]."' && league='".$y["league"]."'");
  $t1 = mysqli_fetch_array($c);
  if($el==1) $d = mysqli_query($link, "SELECT * FROM el_teams WHERE shortname='".$y["team2short"]."' && league='".$y["league"]."'");
  else $d = mysqli_query($link, "SELECT * FROM 2004teams WHERE shortname='".$y["team2short"]."' && league='".$y["league"]."'");
  $t2 = mysqli_fetch_array($d);
  if($el==1) $e = mysqli_query($link, "SELECT name, goals, asists FROM el_players WHERE teamshort='".$y["team1short"]."' && league='".$y["league"]."' ORDER BY points DESC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC LIMIT 1");
  else $e = mysqli_query($link, "SELECT name, goals, asists FROM 2004players WHERE teamshort='".$y["team1short"]."' && league='".$y["league"]."' ORDER BY points DESC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC LIMIT 1");
  $p1 = mysqli_fetch_array($e);
  if($el==1) $f = mysqli_query($link, "SELECT name, goals, asists FROM el_players WHERE teamshort='".$y["team2short"]."' && league='".$y["league"]."' ORDER BY points DESC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC LIMIT 1");
  else $f = mysqli_query($link, "SELECT name, goals, asists FROM 2004players WHERE teamshort='".$y["team2short"]."' && league='".$y["league"]."' ORDER BY points DESC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC LIMIT 1");
  $p2 = mysqli_fetch_array($f);
  $game["stats"]["team1"]["gp"] = $t1["zapasov"];
  $game["stats"]["team1"]["wins"] = $t1["wins"];
  $game["stats"]["team1"]["losts"] = $t1["losts"];
  $game["stats"]["team1"]["points"] = $t1["body"];
  $game["stats"]["team1"]["score"] = $t1["goals"].":".$t1["ga"];
  $game["stats"]["team1"]["scoreinpp"] = $t1["ppgf"].":".$t1["shga"];
  $game["stats"]["team1"]["scoreinsh"] = $t1["shgf"].":".$t1["ppga"];
  $game["stats"]["team1"]["shutouts"] = $t1["so"];
  $game["stats"]["team1"]["penaltyminutes"] = $t1["penalty"];
  $game["stats"]["team1"]["bestplayer"]["name"] = $p1["name"];
  $game["stats"]["team1"]["bestplayer"]["goals"] = $p1["goals"];
  $game["stats"]["team1"]["bestplayer"]["asists"] = $p1["asists"];
  $game["stats"]["team2"]["gp"] = $t2["zapasov"];
  $game["stats"]["team2"]["wins"] = $t2["wins"];
  $game["stats"]["team2"]["losts"] = $t2["losts"];
  $game["stats"]["team2"]["points"] = $t2["body"];
  $game["stats"]["team2"]["score"] = $t2["goals"].":".$t2["ga"];
  $game["stats"]["team2"]["scoreinpp"] = $t2["ppgf"].":".$t2["shga"];
  $game["stats"]["team2"]["scoreinsh"] = $t2["shgf"].":".$t2["ppga"];
  $game["stats"]["team2"]["shutouts"] = $t2["so"];
  $game["stats"]["team2"]["penaltyminutes"] = $t2["penalty"];
  $game["stats"]["team2"]["bestplayer"]["name"] = $p2["name"];
  $game["stats"]["team2"]["bestplayer"]["goals"] = $p2["goals"];
  $game["stats"]["team2"]["bestplayer"]["asists"] = $p2["asists"];
  $game = json_encode($game, JSON_UNESCAPED_UNICODE);
  
  if($lang=="en")
    {
    $game = str_replace("bez asistencie", "no assist", $game);
    $game = str_replace("pres\/", "PP", $game);
    $game = str_replace("oslab\/", "SH", $game);
    $game = str_replace("Neznámy", "Unknown", $game);
    $game = str_replace("Osobný trest", "Game misconduct", $game);
    $game = str_replace("Bodnutie", "Spearing", $game);
    $game = str_replace("Držanie protihráča", "Holding", $game);
    $game = str_replace("Držanie hokejky", "Holding the stick", $game);
    $game = str_replace("Faul lakťom", "Elbowing", $game);
    $game = str_replace("Faul kolenom", "Kneeing", $game);
    $game = str_replace("Hákovanie", "Hooking", $game);
    $game = str_replace("Hrubosť", "Roughing", $game);
    $game = str_replace("Hra vysokou hokejkou", "High sticking", $game);
    $game = str_replace("Krosček", "Crosschecking", $game);
    $game = str_replace("Napadnutie", "Charging", $game);
    $game = str_replace("Napadnutie zozadu", "Checking from behind", $game);
    $game = str_replace("Nedovolená výbava", "Illegal equipment", $game);
    $game = str_replace("Nedovolené bránenie", "Interference", $game);
    $game = str_replace("Nešportové správanie", "Unsportsmanlike conduct", $game);
    $game = str_replace("Pád pod nohy", "Diving", $game);
    $game = str_replace("Pichnutie", "Butt ending", $game);
    $game = str_replace("Podrazenie", "Tripping", $game);
    $game = str_replace("Príliš veľa hráčov na ľade", "Too many men on ice", $game);
    $game = str_replace("Sekanie", "Slashing", $game);
    $game = str_replace("Vrazenie na mantinel", "Boarding", $game);
    $game = str_replace("Úder do hlavy", "Checking to the neck and head area", $game);
    $game = str_replace("Zdržovanie hry", "Delaying", $game);
    $game = str_replace("Hodenie hokejky", "Throwing the stick", $game);
    $game = str_replace("Posunutie brány", "Moving the net", $game);
    }
  echo $game;
  /*echo "<pre>";
  echo print_r($game);
  echo "</pre>";*/
  }
elseif(isset($_GET["games"]))
  {
  $t = mysqli_real_escape_string($link, $_GET["tournament"]);
  $year = mysqli_real_escape_string($link, $_GET["year"]);
  if($t=="WCH") 
    {
    if($year<2004 || $year>date("Y")) die("Incorrect tournament year");
    $lname = "MS ".$year;
    $el = 0;
    }
  elseif($t=="WJC") 
    {
    if($year<2021 || $year>date("Y")+1) die("Incorrect tournament year");
    $lname = "MS U20 ".$year;
    $el = 0;
    }
  elseif($t=="OG") 
    {
    if($year<2006 || $year>date("Y")) die("Incorrect tournament year");
    $lname = "ZOH % ".$year;
    $el = 0;
    }
  elseif($t=="NHL") 
    {
    if($year<2009 || $year>date("Y")) die("Incorrect season");
    $sea = substr($year,2,2);
    $seas = $sea+1;
    $season = $sea."/".$seas;
    $lname = "NHL ".$season;
    $el = 1;
    }
  elseif($t=="KHL") 
    {
    if($year<2010 || $year>date("Y")) die("Incorrect season");
    $sea = substr($year,2,2);
    $seas = $sea+1;
    $season = $sea."/".$seas;
    $lname = "KHL ".$season;
    $el = 1;
    }
  elseif($t=="EL") 
    {
    if($year<2005 || $year>date("Y")) die("Incorrect season");
    $sea = substr($year,2,2);
    $seas = $sea+1;
    $season = $sea."/".$seas;
    $lname = "%liga ".$season;
    $el = 1;
    }
  else die("Incorrect tournament name");
  $q = mysqli_query($link, "SELECT * FROM 2004leagues WHERE longname LIKE '".$lname."'");
  $f = mysqli_fetch_array($q);
  if($el==1) $w = mysqli_query($link, "SELECT m.*, IF(kolo=0,p.potype,NULL) as po_type, (SELECT MAX(CAST(g.time as DECIMAL(5,2))) FROM el_goals g WHERE g.matchno = m.id) AS maxtime FROM el_matches m LEFT JOIN el_playoff p ON (p.team1=m.team1short && p.team2=m.team2short && p.league=m.league) || (p.team2=m.team1short && p.team1=m.team2short && p.league=m.league) WHERE m.league='".$f["id"]."' ORDER BY m.datetime ASC");
  else $w = mysqli_query($link, "SELECT m.*, t.skupina, (SELECT MAX(CAST(g.time as DECIMAL(5,2))) FROM 2004goals g WHERE g.matchno = m.id) AS maxtime FROM 2004matches m LEFT JOIN 2004teams t ON t.shortname=m.team1short && t.league='".$f["id"]."' WHERE m.league='".$f["id"]."' ORDER BY m.datetime ASC");
  $i=0;
  while($e = mysqli_fetch_array($w))
    {
    if($lang=="en") 
      {
      $e["team1long"] = TeamParser($e["team1long"]);
      $e["team2long"] = TeamParser($e["team2long"]);
      $e["kedy"] = StatusParser($e["kedy"]);
      }
    $games["games"][$i]["id"] = $e["id"].$el;
    if($el==0) $games["games"][$i]["group"] = $e["skupina"];
    else {
        $e["po_type"] = str_replace("stvrt", "QF", $e["po_type"]);
        $e["po_type"] = str_replace("semi", "SF", $e["po_type"]);
        $e["po_type"] = str_replace("final", "F", $e["po_type"]);
        $e["po_type"] = str_replace("baraz", "Q", $e["po_type"]);
        $e["po_type"] = str_replace("stanley", "SC", $e["po_type"]);
        if($e["po_type"]=="") $e["po_type"]=NULL;
    }
    $games["games"][$i]["po_type"] = $e["po_type"];
    $games["games"][$i]["team1short"] = $e["team1short"];
    $games["games"][$i]["team1long"] = $e["team1long"];
    $games["games"][$i]["team2short"] = $e["team2short"];
    $games["games"][$i]["team2long"] = $e["team2long"];
    if(isset($_GET["tz"])) { 
        $games["games"][$i]["date"] = new DateTime($e["datetime"], new DateTimeZone('Europe/Bratislava'));
        $games["games"][$i]["date"]->setTimezone(new DateTimeZone($_GET["tz"]));
    }
    else $games["games"][$i]["date"] = $e["datetime"];
    $games["games"][$i]["score"]["goals1"] = $e["goals1"];
    $games["games"][$i]["score"]["goals2"] = $e["goals2"];
    $games["games"][$i]["score"]["status"] = $e["kedy"];
    if($e["maxtime"]=="65.00" || $e["maxtime"]=="70.00" || $e["maxtime"]=="80.00") $games["games"][$i]["score"]["overtime"]=2;
    elseif($e["maxtime"]>"60.00") $games["games"][$i]["score"]["overtime"]=1;
    else $games["games"][$i]["score"]["overtime"]=0;
    $i++;
    }
  $games = json_encode($games, JSON_UNESCAPED_UNICODE);
  echo $games;
  /*echo "<pre>";
  echo print_r($games);
  echo "</pre>";*/
  }
elseif(isset($_GET["table"]))
  {
  $t = mysqli_real_escape_string($link, $_GET["tournament"]);
  $year = mysqli_real_escape_string($link, $_GET["year"]);
  if($t=="WCH") 
    {
    if($year<2004 || $year>date("Y")) die("Incorrect tournament year");
    $lname = "MS ".$year;
    $el = 0;
    $teams_table = "2004teams";
    }
  elseif($t=="WJC") 
    {
    if($year<2021 || $year>date("Y")+1) die("Incorrect tournament year");
    $lname = "MS U20 ".$year;
    $el = 0;
    $teams_table = "2004teams";
    }
  elseif($t=="OG") 
    {
    if($year<2006 || $year>date("Y")) die("Incorrect tournament year");
    $lname = "ZOH % ".$year;
    $el = 0;
    $teams_table = "2004teams";
    }
  elseif($t=="NHL") 
    {
    if($year<2009 || $year>date("Y")) die("Incorrect season");
    $sea = substr($year,2,2);
    $seas = $sea+1;
    $season = $sea."/".$seas;
    $lname = "NHL ".$season;
    $el = 1;
    $teams_table = "el_teams";
    }
  elseif($t=="KHL") 
    {
    if($year<2010 || $year>date("Y")) die("Incorrect season");
    $sea = substr($year,2,2);
    $seas = $sea+1;
    $season = $sea."/".$seas;
    $lname = "KHL ".$season;
    $el = 1;
    }
  elseif($t=="EL") 
    {
    if($year<2005 || $year>date("Y")) die("Incorrect season");
    $sea = substr($year,2,2);
    $seas = $sea+1;
    $season = $sea."/".$seas;
    $lname = "%liga ".$season;
    $el = 1;
    }
  else die("Incorrect tournament name");
  $q = mysqli_query($link, "SELECT * FROM 2004leagues WHERE longname LIKE '".$lname."'");
  $f = mysqli_fetch_array($q);
  
  if(strstr($f["longname"], 'MS'))
    {
    $playoff_line=4;
    $games_total=7;
    }
  elseif(strstr($f["longname"], 'ZOH'))
    {
    $playoff_line=1;
    $games_total=3;
    }
  elseif(strstr($f["longname"], 'NHL'))
    {
    $playoff_line=8;
    $games_total=82;
    }
  elseif(strstr($f["longname"], 'KHL'))
    {
    $playoff_line=8;
    $games_total=56;
    }
  else
    {
    $playoff_line=6;
    $games_total=50;
    }
    
  if($playoff_line>0) $pol = $playoff_line-1;
  $show_clinch=1;
  
  if(strstr($f["longname"], 'MS') || strstr($f["longname"], 'ZOH'))
    {
    $crop = explode("|", $f["groups"]);
    $j=0;
    while ($j < count($crop))
      {
      $skup = $crop[$j];
      if($f["endbasic"]==1)
        {
        $dev = mysqli_query($link, "SELECT ($games_total-(dt.w_basic+dt.l_basic))*".$f["points"]."+p_basic as ce, dt.w_basic+dt.l_basic as gp_basic FROM (SELECT *, gf_basic-ga_basic as diff FROM $teams_table WHERE league='".$f["id"]."' && skupina='$skup' ORDER BY p_basic desc, diff desc, gf_basic desc, w_basic desc, l_basic asc LIMIT $playoff_line,8)dt ORDER BY ce DESC LIMIT 1");
        $deviaty = mysqli_fetch_array($dev);
        $osm = mysqli_query($link, "SELECT *, gf_basic-ga_basic as diff, w_basic+l_basic as gp_basic FROM $teams_table WHERE league='".$f["id"]."' && skupina='$skup' ORDER BY p_basic desc, diff desc, gf_basic desc, w_basic desc, l_basic asc LIMIT ".$pol.",1");
        $osmy = mysqli_fetch_array($osm);
        }
      else
        {
        $dev = mysqli_query($link, "SELECT ($games_total-dt.zapasov)*".$f["points"]."+body as ce FROM (SELECT *, goals-ga as diff FROM $teams_table WHERE league='".$f["id"]."' && skupina='$skup' ORDER BY body desc, diff desc, zapasov asc, goals desc, wins desc, losts asc LIMIT $playoff_line,8)dt ORDER BY ce DESC LIMIT 1");
        $deviaty = mysqli_fetch_array($dev);
        $osm = mysqli_query($link, "SELECT *, goals-ga as diff FROM $teams_table WHERE league='".$f["id"]."' && skupina='$skup' ORDER BY body desc, diff desc, zapasov asc, goals desc, wins desc, losts asc LIMIT ".$pol.",1");
        $osmy = mysqli_fetch_array($osm);
        }
      if($f["endbasic"]==1) $uloha = mysqli_query($link, "SELECT *, gf_basic-ga_basic as diff FROM $teams_table WHERE league='".$f["id"]."' && skupina='$skup' ORDER BY p_basic desc, diff desc, gf_basic desc, w_basic desc, l_basic asc");
      else $uloha = mysqli_query($link, "SELECT *, goals-ga as diff FROM $teams_table WHERE league='".$f["id"]."' && skupina='$skup' ORDER BY body desc, zapasov asc, diff desc, goals desc, wins desc, losts asc");
    
      $reord = h2h_reorder1($uloha, $f["id"], $f);
      $prem = count($reord);
      $rtp=1;
      
      $i=0;
      $p=1;
      while ($i < $prem)
        {
        if($rtp==0) $data = mysqli_fetch_array($uloha);
        else { $exp=explode(":",$reord[$i][5]); $data["wins"]=$reord[$i][2]; $data["w_basic"]=$reord[$i][2]; $data["losts"]=$reord[$i][3]; $data["l_basic"]=$reord[$i][3]; $data["goals"]=$exp[0]; $data["gf_basic"]=$exp[0]; $data["ga"]=$exp[1]; $data["ga_basic"]=$exp[1]; $data["body"]=$reord[$i][6]; $data["p_basic"]=$reord[$i][6]; $data["shortname"]=$reord[$i][0]; $data["longname"]=$reord[$i][1]; $data["zapasov"]=$reord[$i][8]; $data["id"]=$reord[$i][9]; }
        $clinch=$bs=$be=$fav=$leader=$line="";
        
        $wins = $data["wins"];
        $losts = $data["losts"];
        $goals = $data["goals"].":".$data["ga"];
        $points = $data["body"];
        if($f["endbasic"]==1) { $wins = $data["w_basic"]; $losts = $data["l_basic"]; $goals = $data["gf_basic"].":".$data["ga_basic"]; $points = $data["p_basic"]; }
        if($f["el"]==0 && $f["endbasic"]==1) { $data["zapasov"] = $data["w_basic"]+$data["l_basic"]; $osmy["body"] = $osmy["p_basic"]; }
        // clinched playoff
        if($show_clinch==1 && $points > $deviaty["ce"] && $p<=$playoff_line) { $clinch = "tím už má zaistenú účasť vo štvrťfinále"; $clinchwas=1; }
        // cannot make playoff's
        if($show_clinch==1 && (($games_total-$data["zapasov"])*$f["points"])+$points < $osmy["body"] || $show_clinch==1 && $games_total-$data["zapasov"]==0 && $p>$playoff_line) { $clinch = "tím sa už nedostane do štvrťfinále"; $cannotwas=1; }
        // relegated to DIV.I
        if(strstr($f["longname"], "MS") && $show_clinch==1 && (($games_total-$data["zapasov"])*$f["points"])+$data["body"] < $reord[6][6] || strstr($f["longname"], "MS") && $show_clinch==1 && ($data["zapasov"]==7 && $i==7)) { $clinch = "tím zostupuje do I.DIV"; $relegwas=1; }
        $group["group"][$skup][$p]["shortname"] = $data["shortname"];
        $group["group"][$skup][$p]["longname"] = $data["longname"];
        $group["group"][$skup][$p]["gp"] = $data["zapasov"];
        $group["group"][$skup][$p]["wins"] = $wins;
        $group["group"][$skup][$p]["losts"] = $losts;
        $group["group"][$skup][$p]["score"] = $goals;
        $group["group"][$skup][$p]["points"] = $points;
        if($clinchwas==1 || $cannotwas==1 || $relegwas==1) $group["group"][$skup][$p]["clinch"] = $clinch;
        else $group["group"][$skup][$p]["clinch"] = 0;
        if($npos==$data["shortname"] && $league_data["el"]==1) { $a = "$p|$games_total|$wpoints"; return $a; break; }
        if($npos==$data["shortname"] && $league_data["el"]==0) { return $p; break; }
        $i++;
        $p++;
        }

      $j++;
      }
    $group = json_encode($group, JSON_UNESCAPED_UNICODE);
    echo $group;
    }
  elseif(strstr($f["longname"], 'NHL') || strstr($f["longname"], 'KHL'))
    {
    if($lang=="en") include("../includes/lang/lang_en.php");
    else include("../includes/lang/lang_sk.php");
    include("../includes/teamtable.class.php");
    include("../includes/league_specifics.php");
    $tt = LeagueSpecifics($f["id"], $f["longname"]);
    $conference = $tt->render_table("conference", false, true);
    $conference = str_replace('}}}}{"conference":{"', '}},"', $conference);
    echo $conference;
    }
  else
    {
    if($lang=="en") include("../includes/lang/lang_en.php");
    else include("../includes/lang/lang_sk.php");
    include("../includes/teamtable.class.php");
    include("../includes/league_specifics.php");
    $tt = LeagueSpecifics($f["id"], $f["longname"]);
    $conference = $tt->render_table("league", false, true);
    if($lang=="en") $conference = str_replace("LANG_NAV_TABLE", "Table", $conference);
    else $conference = str_replace("LANG_NAV_TABLE", "Tabuľka", $conference);
    echo $conference;
    }
  /*echo "<pre>";
  echo print_r($group);
  echo "</pre>";*/
  }
else die("Missing required parameters");

mysqli_close($link);
?>