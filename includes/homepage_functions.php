<?
require_once('slovaks.php');
$nhl_players = $slovaks;
$nhl_goalies = $brankari;
require_once('slovaki.php');
$khl_players = $slovaks;
$khl_goalies = $brankari;

/*
* Funkcia pre výpis najbližších zápasov všetkých líg dňa
* version: 2.0.0 (24.11.2015 - kompletne prekopané pre použitie s novou verziou stránok)
* version: 2.5.0 (6.2.2020 - prispôsobené pre Boostrap 4 template)
* @return $games string
*/

function Get_upcomming() {
  Global $link, $nhl_players, $nhl_goalies, $khl_players, $khl_goalies;
  $today_teams = $injured = array();
    if (!isset($nhl_players)) {
        require_once('slovaks.php');
        $nhl_players = $slovaks;
        $nhl_goalies = $brankari;
    }
    if (!isset($khl_players)) {
        require_once('slovaki.php');
        $khl_players = $slovaks;
        $khl_goalies = $brankari;
    }
  $dnes = date("Y-m-d");
  $zajtra = date('Y-m-d', strtotime("+1 day"));
  $q = mysqli_query($link, "SELECT dt.*, position, longname, el FROM 2004leagues JOIN ((SELECT m.id, m.team1short, m.team1long, m.team2short, m.team2long, m.goals1, m.goals2, m.kedy, m.datetime, null as kolo, m.league, t1.id as t1id, t2.id as t2id, t1.longname as t1long, t2.longname as t2long FROM 2004matches m LEFT JOIN 2004teams t1 ON t1.shortname=m.team1short && t1.league=m.league LEFT JOIN 2004teams t2 ON t2.shortname=m.team2short && t2.league=m.league WHERE m.datetime LIKE '".$dnes."%')
UNION
(SELECT m.id, m.team1short, m.team1long, m.team2short, m.team2long, m.goals1, m.goals2, m.kedy, m.datetime, m.kolo, m.league, t1.id as t1id, t2.id as t2id, t1.longname as t1long, t2.longname as t2long FROM el_matches m LEFT JOIN el_teams t1 ON t1.shortname=m.team1short && t1.league=m.league LEFT JOIN el_teams t2 ON t2.shortname=m.team2short && t2.league=m.league WHERE m.datetime > '".$dnes." 07:00:00' && m.datetime < '".$zajtra." 07:00:00'))dt ON dt.league=2004leagues.id ORDER BY position ASC, datetime ASC");

  $games = '<div class="card shadow mb-4">
              <div class="card-header">
                <div class="font-weight-bold text-primary text-uppercase">'.LANG_GAMECONT_TODAYS.'</div>
              </div>
              <div class="card-body">';

  if(mysqli_num_rows($q)==0)
    {
    $games .= "<p class='bg-gray-100 border p-2 rounded small m-0'>".LANG_GAMECONT_NOGAMES."</p>
              </div>
            </div>";
    }
  else
    {
    $fav="";
    $games .= '<div class="row no-gutters align-items-center">
                <div class="col mr-2">';
    
    $current_league = null;
    while($f = mysqli_fetch_array($q))
      {
      $lid = $f["league"];
      
      // Display league header when league changes
      if($current_league !== $f["longname"]) {
        if($current_league !== null) {
          $games .= '<div class="text-xs text-muted font-weight-bold mb-1 mt-3">'.$f["longname"].'</div>';
        } else {
          $games .= '<div class="text-xs text-muted font-weight-bold mb-1">'.$f["longname"].'</div>';
        }
        $current_league = $f["longname"];
        
        // Load injuries for this league
        $injured[$lid] = [];
        $zra = [];
        $z = mysqli_query($link, "SELECT * FROM el_injuries WHERE league='".$f["league"]."'");
        while($zr = mysqli_fetch_array($z))
          {
          $zra[] = $zr["name"];
          }
        $injured[$lid] = $zra;
      }
      $slov=$favor="";
      // slovaci v akcii
      if(strstr($f["longname"], 'NHL') || strstr($f["longname"], 'KHL'))
        {
        $tran1 = $tran2 = array();
        if(date("n")<8) {
          $rok = date("Y")-1;
          $season_start = $rok."-01-08";
          }
        else $season_start = date("Y")."-01-08";
        $tr = mysqli_query($link, "SELECT from_team, pname FROM transfers WHERE (from_team='".$f["team1short"]."' || from_team='".$f["team2short"]."') && datetime>'".$season_start."'");
        while($tra = mysqli_fetch_array($tr))
          {
          if($tra["from_team"]==$f["team1short"]) $tran1[] = $tra["pname"];
          if($tra["from_team"]==$f["team2short"]) $tran2[] = $tra["pname"];
          }
        if(strstr($f["longname"], 'KHL')) { $slovaks = $khl_players; $brankari = $khl_goalies; }
        if(strstr($f["longname"], 'NHL')) { $slovaks = $nhl_players; $brankari = $nhl_goalies; }
        $pia1 = array_keys($slovaks, $f["team1short"]);
        $gia1 = array_keys($brankari, $f["team1short"]);
        $inaction1 = array_merge($pia1, $gia1);
        if(count($zra)>0) $inaction1 = array_diff($inaction1, $zra);
        if(count($tran1)>0) $inaction1 = array_diff($inaction1, $tran1);
        $inaction1 = array_values($inaction1);
        $pia2 = array_keys($slovaks, $f["team2short"]);
        $gia2 = array_keys($brankari, $f["team2short"]);
        $inaction2 = array_merge($pia2, $gia2);
        if(count($zra)>0) $inaction2 = array_diff($inaction2, $zra);
        if(count($tran2)>0) $inaction2 = array_diff($inaction2, $tran2);
        $inaction2 = array_values($inaction2);
        if(count($inaction1)>0 || count($inaction2)>0)
          {
          $c = count($inaction1)+count($inaction2);
          $slov .= '<i class="fas fa-smile" data-toggle="tooltip" data-placement="top" data-html="true" title="';
          $y=0;
          while($y<count($inaction1))
            {
            $slov .= $inaction1[$y].'<br>';
            $y++;
            }
          $y=0;
          while($y<count($inaction2))
            {
            $slov .= $inaction2[$y].'<br>';
            $y++;
            }
          $slov .= '"></i>';
          }
        }
      // favorite team
      $fa = mysqli_query($link, "SELECT user_favteam FROM e_xoops_users WHERE uid='".$_SESSION["logged"]."'");
      $fav = mysqli_fetch_array($fa);
      $fav["user_favteam"] = $fav["user_favteam"] ?? "0";
      if($_SESSION["logged"])
        {
        if($fav["user_favteam"]!="0" && ($fav["user_favteam"]==$f["team1short"] || $fav["user_favteam"]==$f["team2short"])) $favor=' bg-gray-200 rounded';
        }
      else {
          $fav["user_favteam"]="0";
      }
      // preklad timov
      if($_SESSION["lang"]!='sk') {
          $f["team1long"] = TeamParser($f["team1long"]);
          $f["team2long"] = TeamParser($f["team2long"]);
      }
      // vypis
      $cas = date("G:i", strtotime($f["datetime"]));
      if(strtotime($f["datetime"]) > time()) $score = '<a href="/game/'.$f["id"].$f["el"].'-'.SEOtitle($f["team1long"]." vs ".$f["team2long"]).'" class="btn btn-light btn-circle btn-sm"><i class="fas fa-search"></i></a>';
      elseif($f["kedy"]!="na programe") $score = '<a href="/report/'.$f["id"].$f["el"].'-'.SEOtitle($f["team1long"].' vs '.$f["team2long"]).'" class="font-weight-bold'.($f["kedy"]!="konečný stav" ? ' live rounded-pill text-danger':'').'">'.$f["goals1"].':'.$f["goals2"].'</a>';
      else $score = $f["goals1"].":".$f["goals2"];
      $games .= '<div class="row no-gutters align-items-center small'.$favor.'">
                  <div class="col-2 text-nowrap">'.$cas.'</div>
                  <div class="col-3 font-weight-bold text-nowrap"><img class="flag-'.($f["el"]==1 ? 'el':'iihf').' '.$f["team1short"].'-small" src="/img/blank.png" alt="'.$f["team1long"].'"> <a href="/team/'.$f["t1id"].($f["el"]==1 ? '1':'0').'-'.SEOtitle($f["t1long"]).'" data-toggle="tooltip" data-placement="top" title="'.$f["team1long"].'">'.$f["team1short"].'</a></div>
                  <div class="col-2 text-center">'.$score.'</div>
                  <div class="col-3 text-right font-weight-bold text-nowrap"><a href="/team/'.$f["t2id"].($f["el"]==1 ? '1':'0').'-'.SEOtitle($f["t2long"]).'" data-toggle="tooltip" data-placement="top" title="'.$f["team2long"].'">'.$f["team2short"].'</a> <img class="flag-'.($f["el"]==1 ? 'el':'iihf').' '.$f["team2short"].'-small" src="/img/blank.png" alt="'.$f["team2long"].'"></div>
                  '.($f["el"]==1 ? '<div class="col-2 text-center">'.$slov.'</div>':'').'
                 </div>';
      if($f["kedy"]!="konečný stav") {
        $today_teams[$lid]["teams"][] = $f["team1short"];
        $today_teams[$lid]["teams"][] = $f["team2short"];
        $today_teams[$lid]["longname"] = $f["longname"];
        $today_teams[$lid]["el"] = $f["el"];
        }
      }
    $games .= "</div></div>";
    if(count($today_teams) > 0) {
      $games .= '</div></div>';
      $games .= Get_PlayersToWatch($today_teams, $injured, $fav["user_favteam"]);
    }
    else {
      $games .= "</div></div>";
    }
    }
return $games;
}

/*
* Funkcia pre výpis dnešných hráčov na sledovanie
* @param $today_teams array shortname tímov, ktoré dnes hrajú
* @param $injured array všetci zranení hráči v danej lige
* @param $favteam string obľúbený tím prihláseného užívateľa
* @return $p string
*/

function Get_PlayersToWatch($today_teams, $injured, $favteam) {
    Global $link;
    $ptw = array();
    $p = "";
    foreach($today_teams as $league=>$teams) {
        $ptw[$league]["longname"] = $teams["longname"];
        $el = $teams["el"];
        $ptw[$league]["el"] = $el;
        $teams = implode("','", $teams["teams"]);
        if($el==1) $q = mysqli_query($link, "SELECT * FROM `el_players` WHERE league='".$league."' && (RIGHT(goals,1)=9 || RIGHT(points,1)=9) && teamshort IN ('".$teams."')");
        else $q = mysqli_query($link, "SELECT * FROM `2004players` WHERE league='".$league."' && (RIGHT(goals,1)=9 || RIGHT(points,1)=9) && teamshort IN ('".$teams."')");
        if(mysqli_num_rows($q)>0) {
            while($f = mysqli_fetch_array($q)) {
                if(!in_array($f["name"],$injured[$league])) $ptw[$league]["players"][] = array($f["teamshort"], $f["name"], $f["goals"], $f["asists"], $f["points"]);
            }
            if(isset($ptw[$league]["players"])) {
                usort($ptw[$league]["players"], function($a,$b){ $c = $b[2] - $a[2]; $c .= $b[4] - $a[4]; return $c; });
                $ptw[$league]["players"] = array_slice($ptw[$league]["players"], 0, 5, true);
            }
        }
    }

    if(count($ptw)>0) {
        foreach($ptw as $league=>$players) {
            if(isset($players["players"]) && count($players["players"])>0) {
                if(!isset($bol)) $p .= '<div class="card shadow mb-4">
                        <div class="card-header">
                            <div class="font-weight-bold text-primary text-uppercase">'.LANG_GAMECONT_PTW.'</div>
                        </div>
                        <div class="card-body">';
                $p .= '<div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs text-muted font-weight-bold mb-1'.(isset($bol) ? ' mt-3':'').'">'.$players["longname"].'</div>
                        </div>
                    </div>
                        <div class="row no-gutters align-items-center text-xs border-bottom mb-1">
                            <div class="col-9">'.LANG_PLAYERDB_PLAYER.'</div>
                            <div class="col">'.LANG_P.'</div>
                            <div class="col">'.LANG_G.'</div>
                            <div class="col">'.LANG_A.'</div>
                        </div>';
                foreach($players["players"] as $player) {
                    $p .= '<div class="row no-gutters align-items-center small'.($favteam==$player[0] ? ' bg-gray-200 rounded':'').'">
                                <div class="col-9"><img class="flag-'.($players["el"]==1 ? 'el':'iihf').' '.$player[0].'-small" src="/img/blank.png" alt="'.$player[0].'"> '.$player[1].'</div>
                                <div class="col">'.(substr($player[4],-1)==9 ? '<abbr title="'.(sprintf(LANG_GAMECONT_WILLHEPOINTS, $player[4]+1)).'" class="initialism font-weight-bold'.($player[4]>30 ? ' text-danger':'').'" data-toggle="tooltip">'.$player[4].'</abbr>':$player[4]).'</div>
                                <div class="col">'.(substr($player[2],-1)==9 ? '<abbr title="'.(sprintf(LANG_GAMECONT_WILLHESCORE, $player[2]+1)).'" class="initialism font-weight-bold'.($player[2]>10 ? ' text-danger':'').'" data-toggle="tooltip">'.$player[2].'</abbr>':$player[2]).'</div>
                                <div class="col">'.$player[3].'</div>
                            </div>';
                }
            $bol=1;
            }
        }
        if(isset($bol)) $p .= '</div>
            </div>';
    }
    return $p;
}

/*
* Funkcia pre výpis posledných výkonov slovenských hráčov
* version: 1.5.0 (24.11.2015 - funguje na základe starej verzie - prenesené do novej)
* version: 1.6.0 (22.3.2016 - pridanie štatistík aj pre hráčov reprezentácie)
* version: 2.0.0 (6.2.2020 - prispôsobenie pre Boostrap 4 template)
* @return $stat string
*/

function Get_Latest_Stats() {
  Global $link, $nhl_players, $nhl_goalies, $khl_players, $khl_goalies;
  $stat = '<div class="card shadow mb-4">
            <div class="card-header">
                <div class="font-weight-bold text-primary text-uppercase">'.LANG_GAMECONT_STATS.'</div>
            </div>
            <div class="card-body">
              <div class="row no-gutters align-items-center">
                <div class="col mr-2">';
    if (!isset($nhl_players)) {
        require_once('slovaks.php');
        $nhl_players = $slovaks;
        $nhl_goalies = $brankari;
    }
    if (!isset($khl_players)) {
        require_once('slovaki.php');
        $khl_players = $slovaks;
        $khl_goalies = $brankari;
    }
  $slovaks = array_merge($nhl_players, $khl_players);
  $brankari = array_merge($nhl_goalies, $khl_goalies);
  $vcera = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));
  $dnes = date("Y-m-d");
  //array_walk($slovaks, function (&$i, $k) { $i = "'$k'"; });
  //array_walk($brankari, function (&$i, $k) { $i = "'$k'"; });
  //array_walk($slovaks, create_function('&$i,$k','$i="\'$k\'";'));
  array_walk($slovaks, function(&$i, $k) { $i = "'$k'"; });
  //array_walk($brankari, create_function('&$i,$k','$i="\'$k\'";'));
  array_walk($brankari, function(&$i, $k) { $i = "'$k'"; });
  $in = implode(",", $slovaks);
  $in_goalies = implode(",", $brankari);
  $w = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
  $w = mysqli_query($link, "SELECT * FROM el_matches WHERE datetime > '$vcera 07:00' && datetime < '$dnes 07:00' GROUP BY kedy");
  if(mysqli_num_rows($w)==1)
    {
    $e = mysqli_fetch_array($w);
    if($e["kedy"]=="na programe") { $vcera = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-2, date('Y'))); $dnes = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-1, date('Y'))); }
    }
  $q = mysqli_query($link, "(SELECT ft.*, 2004leagues.longname FROM 2004leagues JOIN (SELECT et.* FROM (SELECT el_goals.goaler, el_goals.asister1, el_goals.asister2, el_goals.teamshort, dt.league, pv.link as video FROM el_goals JOIN (SELECT id, league FROM el_matches WHERE kedy = 'konečný stav' && datetime > '$vcera 07:00' && datetime < '$dnes 07:00' ORDER BY datetime)dt ON dt.id=el_goals.matchno LEFT JOIN player_videos pv ON pv.goal_id=el_goals.id)et WHERE goaler IN ($in) OR asister1 IN ($in) OR asister2 IN ($in))ft ON 2004leagues.id=ft.league)
  UNION ALL
  (SELECT gt.*, 2004leagues.longname FROM 2004leagues JOIN (SELECT et.* FROM (SELECT 2004goals.goaler, 2004goals.asister1, 2004goals.asister2, 2004goals.teamshort, dt.league, NULL as video FROM 2004goals JOIN (SELECT id, league FROM 2004matches WHERE kedy = 'konečný stav' && datetime > '$vcera 07:00' && datetime < '$dnes 07:00' ORDER BY datetime)dt ON dt.id=2004goals.matchno)et WHERE teamshort='SVK')gt ON 2004leagues.id=gt.league)");
 
  if(!empty($in_goalies)) {
    $g = mysqli_query($link, "(SELECT m.id, m.league, m.team1short, m.team2short, l.longname, ms.goalie1, ms.goalie2, 
    IF(JSON_UNQUOTE(JSON_EXTRACT(goalie1, '$[0]')) IN ($in_goalies),1,
      IF(JSON_UNQUOTE(JSON_EXTRACT(goalie1, '$[1]')) IN ($in_goalies),2,
        IF(JSON_UNQUOTE(JSON_EXTRACT(goalie2, '$[0]')) IN ($in_goalies),3,4)
        )
      ) as kde, ms.g1_goals, ms.g1_shots, ms.g2_goals, ms.g2_shots
    FROM el_matches m LEFT JOIN el_matchstats ms ON ms.matchid=m.id LEFT JOIN 2004leagues l ON l.id=m.league WHERE m.kedy = 'konečný stav' && m.datetime > '$vcera 07:00' && m.datetime < '$dnes 07:00' && (JSON_UNQUOTE(JSON_EXTRACT(goalie1, '$[0]')) IN ($in_goalies) || JSON_UNQUOTE(JSON_EXTRACT(goalie1, '$[1]')) IN ($in_goalies) || JSON_UNQUOTE(JSON_EXTRACT(goalie2, '$[0]')) IN ($in_goalies) || JSON_UNQUOTE(JSON_EXTRACT(goalie2, '$[1]')) IN ($in_goalies)))");
  } else {
    $g = mysqli_query($link, "SELECT NULL LIMIT 0");
  }
      
  if(mysqli_num_rows($q)==0 && mysqli_num_rows($g)==0)
    {
    $stat .= "<p class='bg-gray-100 border p-2 rounded small m-0'>".LANG_GAMECONT_NOSTATS."</p>
              </div>
            </div>
          </div>
        </div>";
    }
  else
    {
    $gstats = array();
    $p=0;
    while($go = mysqli_fetch_array($g))
      {
      if(!strstr($go["longname"], "Tipos"))
        {
        $g1json = json_decode($go["goalie1"], true);
        $g1_goalsjson = json_decode($go["g1_goals"], true);
        $g1_shotsjson = json_decode($go["g1_shots"], true);
        $g2json = json_decode($go["goalie2"], true);
        $g2_goalsjson = json_decode($go["g2_goals"], true);
        $g2_shotsjson = json_decode($go["g2_shots"], true);
        if($go["kde"]==1) { $gname = $g1json[0]; $goals = $g1_goalsjson[0]; $shots = $g1_shotsjson[0]; $tshort = $go["team1short"]; }
        elseif($go["kde"]==2) { $gname = $g1json[1]; $goals = $g1_goalsjson[1]; $shots = $g1_shotsjson[1]; $tshort = $go["team1short"]; }
        elseif($go["kde"]==3) { $gname = $g2json[0]; $goals = $g2_goalsjson[0]; $shots = $g2_shotsjson[0]; $tshort = $go["team2short"]; }
        else { $gname = $g2json[1]; $goals = $g2_goalsjson[1]; $shots = $g2_shotsjson[1]; $tshort = $go["team2short"]; }
        $gstats[$gname][0]=$shots; $gstats[$gname][1]=$goals; $gstats[$gname][2]=($shots-$goals)/$shots; $gstats[$gname][3]=$go["league"]; $gstats[$gname][4]=$go["longname"]; $gstats[$gname][5]=$gname; $gstats[$gname][6]=$tshort;
        $p++;
        }
      }
      usort($gstats, function($a,$b){ $c = $a[3] - $b[3]; $c .= $b[2] - $a[2]; $c .= $b[0] - $a[0]; return $c; });
      /*
      [0] => Array
          (
              [0] => shots
              [1] => goals
              [2] => svs%
              [3] => lid
              [4] => league longname
              [5] => player
              [6] => teamshort
          )    
      */
      
      $i=$gpos=$ppos=0;
      while($i < count($gstats))
        {
        $lid = $gstats[$i][3];
        if($gstats[$i][0]=="") $gstats[$i][0]=0;
        if($gstats[$i][1]=="") $gstats[$i][1]=0;
        $svsp = round($gstats[$i][2]*100,2);
        $svs = $gstats[$i][0]-$gstats[$i][1];
        
        if($gpos==0) $stat .= '<div class="text-xs text-muted font-weight-bold mb-1">'.$gstats[$i][4].'</div>
                            </div>
                           </div>
                           <div class="row no-gutters align-items-center text-xs border-bottom mb-1">
                            <div class="col-8">'.LANG_FANTASY_GOALIE.'</div>
                            <div class="col">S</div>
                            <div class="col">SVS</div>
                            <div class="col">SVS%</div>
                           </div>';
        $gpos=$lid;
        if($gpos!=$ppos && $ppos!=0) $stat .= ' 
                              <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                  <div class="text-xs text-muted font-weight-bold mb-1 mt-3">'.$gstats[$i][4].'</div>
                                </div>
                              </div>
                              <div class="row no-gutters align-items-center text-xs border-bottom mb-1">
                                <div class="col-8">'.LANG_FANTASY_GOALIE.'</div>
                                <div class="col">S</div>
                                <div class="col">SVS</div>
                                <div class="col">SVS%</div>
                              </div>';
        
        $stat .= '            <div class="row no-gutters align-items-center small">
                                <div class="col-8"><img class="flag-iihf '.$gstats[$i][6].'-small" src="/img/blank.png" alt="'.$gstats[$i][6].'"> '.$gstats[$i][5].'</div>
                                <div class="col">'.$gstats[$i][0].'</div>
                                <div class="col">'.$svs.'</div>
                                <div class="col font-weight-bold">'.$svsp.'</div>
                              </div>';
        $ppos = $gpos;
        $i++;
        }
        
    $stats = array();
    $p=$ppos=0;
    while($f = mysqli_fetch_array($q))
      {
      if(!strstr($f["longname"], "Tipos"))
        {
        $gname = $f["goaler"];
        $a1name = $f["asister1"];
        $a2name = $f["asister2"];
        if(array_key_exists($gname,$slovaks) || $f["teamshort"]=="SVK") {
            if(!isset($stats[$gname])) {
                $stats[$gname] = array_fill(0, 7, 0);
                $stats[$gname][7] = [];
            }
            $stats[$gname][0]++; 
            $stats[$gname][1]++; 
            $stats[$gname][3]=$f["league"]; 
            $stats[$gname][4]=$f["longname"]; 
            $stats[$gname][5]=$gname; 
            $stats[$gname][6]=$f["teamshort"];
            if(isset($f["video"])) $stats[$gname][7][] = $f["video"];
        }
        if(array_key_exists($a1name,$slovaks) || ($f["teamshort"]=="SVK" && $f["asister1"]!="bez asistencie")) {
            if(!isset($stats[$a1name])) {
                $stats[$a1name] = array_fill(0, 7, 0);
                $stats[$a1name][7] = [];
            }
            $stats[$a1name][0]++;
            $stats[$a1name][2]++; 
            $stats[$a1name][3]=$f["league"]; 
            $stats[$a1name][4]=$f["longname"];
            $stats[$a1name][5]=$a1name; 
            $stats[$a1name][6]=$f["teamshort"];
            if(isset($f["video"])) $stats[$a1name][7][] = $f["video"];
        }
        if(array_key_exists($a2name,$slovaks) || ($f["teamshort"]=="SVK" && $f["asister2"]!="bez asistencie")) { 
            if(!isset($stats[$a2name])) {
                $stats[$a2name] = array_fill(0, 7, 0);
                $stats[$a2name][7] = [];
            }
            $stats[$a2name][0]++; 
            $stats[$a2name][2]++; 
            $stats[$a2name][3]=$f["league"]; 
            $stats[$a2name][4]=$f["longname"]; 
            $stats[$a2name][5]=$a2name; 
            $stats[$a2name][6]=$f["teamshort"];
            if(isset($f["video"])) $stats[$a2name][7][] = $f["video"]; 
        }
        $p++;
        }
      }
      if($p==0 && count($gstats)==0) $stat .= "<p class='bg-gray-100 border p-2 rounded small'>".LANG_GAMECONT_NOSTATS."</p>";
      usort($stats, function($a,$b){ $c = $a[3] - $b[3]; $c .= $b[0] - $a[0]; $c .= $b[1] - $a[1]; return $c; });
      /*
      [0] => Array
          (
              [0] => points
              [1] => goals
              [2] => asists
              [3] => lid
              [4] => league longname
              [5] => player
              [6] => teamshort
              [7] => video links
          )    
      */
    
      $i=$pos=0;
      while($i < count($stats))
        {
        $lid = $stats[$i][3];
        if(!isset($stats[$i][1])) $stats[$i][1]=0;
        if(!isset($stats[$i][2])) $stats[$i][2]=0;
        $videos = "";
        if(count($stats[$i][7])>0) {
            $videos .= '<div class="float-right mr-2 text-primary">';
            foreach($stats[$i][7] as $video) {
                $videos .= '<a class="fa-film fas ml-1 text-decoration-none" data-toggle="modal" data-target="#videoModal" data-url="'.$video.'" onclick="setVideoUrl(this)" href="#"></a>';
            }
            $videos .= '</div>';
        }
        
        if($gpos==0 && !$ppos) $stat .= '<div class="text-xs text-muted font-weight-bold mb-1">'.$stats[$i][4].'</div>
                            </div>
                           </div>
                           <div class="row no-gutters align-items-center text-xs border-bottom mb-1">
                            <div class="col-9">'.LANG_PLAYERDB_PLAYER.'</div>
                            <div class="col">'.LANG_P.'</div>
                            <div class="col">'.LANG_G.'</div>
                            <div class="col">'.LANG_A.'</div>
                           </div>';
        $pos=$lid;
        if(($pos!=$ppos && $ppos) || !$ppos && $gpos>0) $stat .= ' 
                              <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                  <div class="text-xs text-muted font-weight-bold mb-1 mt-3">'.$stats[$i][4].'</div>
                                </div>
                              </div>
                              <div class="row no-gutters align-items-center text-xs border-bottom mb-1">
                                <div class="col-9">'.LANG_PLAYERDB_PLAYER.'</div>
                                <div class="col">'.LANG_P.'</div>
                                <div class="col">'.LANG_G.'</div>
                                <div class="col">'.LANG_A.'</div>
                              </div>';
        
        $stat .= '            <div class="row no-gutters align-items-center small">
                                <div class="col-9"><img class="flag-iihf '.$stats[$i][6].'-small" src="/img/blank.png" alt="'.$stats[$i][6].'"> '.$stats[$i][5].$videos.'</div>
                                <div class="col font-weight-bold">'.$stats[$i][0].'</div>
                                <div class="col">'.$stats[$i][1].'</div>
                                <div class="col">'.$stats[$i][2].'</div>
                              </div>';
        $ppos = $pos;
        $i++;
        }
    $stat .= "</div>
    </div>";
    }
return $stat;
}

/*
* Funkcia pre výpis posledných hráčskych prestupov
* version: 1.0.0 (23.11.2021 - vytvorenie funkcie)
* @return $trans string
*/

function Transfers() {
    Global $link;
    $datum="";
    $q = mysqli_query($link, "SELECT * FROM `transfers` GROUP BY pname, from_team, to_team ORDER BY `transfers`.`datetime` DESC LIMIT 5");
    $trans = '
        <div class="card shadow mb-4">
            <div class="card-header">
                <div class="font-weight-bold text-primary text-uppercase">'.LANG_TEAMSTATS_LATESTTRANSFERS.'</div>
            </div>
            <div class="card-body">';
            $i=0;
            while($l = mysqli_fetch_array($q)) {

            $datum = (strtotime($l['datetime']) == mktime(0, 0, 0)) ? 'dnes' : (($datum == mktime(0, 0, 0, date('n'), date('j') - 1)) ? 'včera' : date('j.n.Y', strtotime($l['datetime'])));

            if($l["status"]=="0" && $l["to_name"]=="") $l["to_name"]=LANG_TEAMSTATS_FREEAGENT;
            if($l["pid"]!=NULL) {
                if($l["goalie"]==0) $pl = mysqli_query($link, "SELECT name FROM ".($l["el"]==1 ? 'el_players':'2004players')." WHERE id='".$l["pid"]."'");
                else $pl = mysqli_query($link, "SELECT name FROM ".($l["el"]==1 ? 'el_goalies':'2004goalies')." WHERE id='".$l["pid"]."'");
                $player = mysqli_fetch_array($pl);
                if($l["goalie"]==0) $url = sprintf('/player/%s%s-%s', $l['pid'], $l["el"], SEOtitle($player['name']));
                else $url = sprintf('/goalie/%s%s-%s', $l['pid'], $l["el"], SEOtitle($player['name']));
            }
            else $player["name"] = $l["pname"];
            $trans .= '
            <div class="row p-fluid"'.($i%2==1 ? '':' style="background-color: rgba(0,0,0,.05);"').'>
                <div class="col-7">'.($l["pid"]!=NULL ? '<a href="'.$url.'">'.$player["name"].'</a>':$player["name"]).'</div>
                <div class="col-5 small pt-1 text-right">'.$datum.'</div>
                <div class="col-5 small bg-white border rounded text-center p-2">'.($l["from_image"]!="" ? '<img src="'.$l["from_image"].'" style="height:24px;" alt="'.$l["from_name"].'"><br>':'').''.$l["from_name"].'</div>
                <div class="col-2 align-self-center text-center"><i class="fas fa-angle-double-right text-success"></i></div>
                <div class="col-5 small bg-white border rounded text-center p-2">'.($l["to_image"]!="" ? '<img src="'.$l["to_image"].'" style="height:24px;" alt="'.$l["to_name"].'"><br>':'').''.$l["to_name"].'</div>
            </div>';
            $i++;
            }
    $trans .= '
            </div>
        </div>';
    return $trans;
}

/*
* Funkcia pre výpis formy obľúbeného tímu
* version: 1.0.0 (18.10.2016 - vytvorenie funkcie)
* version: 2.0.0 (6.2.2020 - prispôsobenie pre Boostrap 4 template)
* @return $favteam string
*/

function Favourite_Team() {
  Global $link;
  $favteam=$kde="";
  $_SESSION["logged"] = $_SESSION["logged"] ?? null;
  $q = mysqli_query($link, "SELECT user_favteam FROM e_xoops_users WHERE uid='".$_SESSION["logged"]."'");
  $f = mysqli_fetch_array($q);
  if(!isset($f)) $f["user_favteam"]=0;
  if($f["user_favteam"]!='0')
    {
    $t = mysqli_query($link, "SELECT el_teams.longname, cws, cls, el_teams.league, t1.el, t1.longname as liga FROM el_teams JOIN 2004leagues t1 ON t1.id=el_teams.league WHERE shortname = '".$f["user_favteam"]."' UNION SELECT 2004teams.longname, cws, cls, 2004teams.league, t1.el, t1.longname as liga FROM 2004teams JOIN 2004leagues t1 ON t1.id=2004teams.league WHERE shortname = '".$f["user_favteam"]."' ORDER BY league DESC LIMIT 1");
    $team = mysqli_fetch_array($t);
    $w = mysqli_query($link, "SELECT team1short, team1long, team2short, team2long, goals1, goals2, kedy, datetime FROM `el_matches` WHERE (team1short='".$f["user_favteam"]."' || team2short='".$f["user_favteam"]."') && datetime > NOW()
UNION
SELECT team1short, team1long, team2short, team2long, goals1, goals2, kedy, datetime FROM `2004matches` WHERE (team1short='".$f["user_favteam"]."' || team2short='".$f["user_favteam"]."') && datetime > NOW()
ORDER BY datetime LIMIT 1");
    $e = mysqli_query($link, "SELECT team1short, team1long, team2short, team2long, goals1, goals2, kedy, datetime FROM `el_matches` WHERE (team1short='".$f["user_favteam"]."' || team2short='".$f["user_favteam"]."') && datetime < NOW() && kedy='konečný stav'
UNION
SELECT team1short, team1long, team2short, team2long, goals1, goals2, kedy, datetime FROM `2004matches` WHERE (team1short='".$f["user_favteam"]."' || team2short='".$f["user_favteam"]."') && datetime < NOW() && kedy='konečný stav'
ORDER BY datetime DESC LIMIT 1");
    $favteam = '<div class="card shadow py-2 mb-4">
                  <div class="card-body">
                    <div class="row no-gutters align-items-center">
                      <div class="col mr-2">
                        <div class="h6 font-weight-bold text-primary text-uppercase mb-1">'.LANG_USERPROFILE_FAVTEAM.'</div>
                        <div class="text-xs text-muted font-weight-bold mb-1">'.$team["longname"].'</div>
                        <ul class="list-group list-group-flush">';
        if(mysqli_num_rows($e)>0)
          {
          $prev = mysqli_fetch_array($e);
          if(date("Y-m-d", strtotime($prev["datetime"]))==date("Y-m-d", mktime(0,0,0))) $datum='dnes';
          elseif(date("Y-m-d", strtotime($prev["datetime"]))==date("Y-m-d", mktime(0,0,0,date("n"),date("j")-1))) $datum='včera';
          elseif(date("Y-m-d", strtotime($prev["datetime"]))==date("Y-m-d", mktime(0,0,0,date("n"),date("j")-2))) $datum='pred dvoma dňami';
          else $datum = date("j.n.Y", strtotime($prev["datetime"]));
          if($prev["team1short"]==$f["user_favteam"])
            {
            if($team["el"]==1) 
              {
              if(strstr($team["liga"], "NHL")) $kde = "vonku";
              else $kde = "doma";
              }
            $skym = $prev["team2long"];
            if($prev["goals1"]>$prev["goals2"])
              {
              $hl = "vyhral $datum $kde nad";
              $score = $prev["goals1"].":".$prev["goals2"];
              }
            else
              {
              $hl = "prehral $datum $kde s";
              $score = $prev["goals1"].":".$prev["goals2"];
              }
            }
          else
            {
            if($team["el"]==1) 
              {
              if(strstr($team["liga"], "NHL")) $kde = "doma";
              else $kde = "vonku";
              }
            $skym = $prev["team1long"];
            if($prev["goals1"]>$prev["goals2"])
              {
              $hl = "prehral $datum $kde s";
              $score = $prev["goals2"].":".$prev["goals1"];
              }
            else
              {
              $hl = "vyhral $datum $kde nad";
              $score = $prev["goals2"].":".$prev["goals1"];
              }
            }
          $favteam .= '<li class="list-group-item px-0 p-fluid"><i class="fa-undo-alt fas mr-3 text-gray-400"></i>Naposledy '.$hl.' tímom <strong>'.$skym.'</strong> ('.$score.')</li>';
          }
        if(mysqli_num_rows($w)>0)
          {
          $next = mysqli_fetch_array($w);
          if($next["team1short"]==$f["user_favteam"]) 
            {
            if($team["el"]==1)
              {
              if(strstr($team["liga"], "NHL")) $kde = "vonku";
              else $kde = "doma";
              }
            $skym = $next["team2long"];
            }
          else
            {
            if($team["el"]==1)
              {
              if(strstr($team["liga"], "NHL")) $kde = "doma";
              else $kde = "vonku";
              }
            $skym = $next["team1long"];
            }
          if(date("Y-m-d", strtotime($next["datetime"]))==date("Y-m-d", mktime(0,0,0))) $datum='dnes '.$kde.' o '.date("H:i", strtotime($next["datetime"]));
          elseif(date("Y-m-d", strtotime($next["datetime"]))==date("Y-m-d", mktime(0,0,0,date("n"),date("j")+1))) $datum='zajtra '.$kde.' o '.date("H:i", strtotime($next["datetime"]));
          elseif(date("Y-m-d", strtotime($next["datetime"]))==date("Y-m-d", mktime(0,0,0,date("n"),date("j")+2))) $datum='pozajtra '.$kde.' o '.date("H:i", strtotime($next["datetime"]));
          else $datum = date("j.n.Y \o H:i", strtotime($next["datetime"]))." ".$kde;
          $favteam .= '<li class="list-group-item px-0 p-fluid"><i class="fa-calendar fas mr-3 text-gray-400"></i>Najbližšie hrá '.$datum.' s tímom <strong>'.$skym.'</strong></li>';
          }

        if($team["cws"]==1 || $team["cls"]==1) $hl = "začal";
        else $hl = "ťahá";
        if($team["cws"]>0) { $co=$team["cws"]; if($team["cws"]==1) $wl = "výhrou"; else $wl = "výhrami"; $ico = "5"; }
        else { $co=$team["cls"]; if($team["cls"]==1) $wl = "prehrou"; else $wl = "prehrami"; $ico = "3"; }
        $favteam .= '<li class="list-group-item px-0 p-fluid"><i class="fa-thumbs-up fas mr-3 text-gray-400"></i>Momentálne '.$hl.' sériu s <strong>'.$co.'</strong> '.$wl.'</li>';
      $favteam .= "     </ul>
                      </div>
                    </div>
                  </div>
                </div>";
    }
  return $favteam;
}

/*
* Funkcia pre výpis práve prihlásených užívateľov
* @return $online string
*/

function Users_Online() {
  Global $link;
  $online="";
  $q = mysqli_query($link, "SELECT * FROM `e_xoops_users` WHERE last_login>UNIX_TIMESTAMP()-300");
  if(mysqli_num_rows($q)>0)
    {
    $on = array();
    while($f = mysqli_fetch_array($q))
      {
      $on[] = '<a href="/user/'.$f["uid"].'">'.$f["uname"].'</a>';
      }
    $online = '
      <div class="card shadow mb-4">
        <div class="card-header">
          <div class="font-weight-bold text-primary text-uppercase">'.LANG_USERSONLINE.'</div>
        </div>
        <div class="card-body">
          <p class="p-fluid">'.LANG_CURRENTLYONLINE.': '.implode(", ", $on).'</p>
        </div>
      </div>';
    }
  return $online;
}

/*
* Funkcia pre výpis vecnej ceny, ktorá sa práve odosiela víťazovi
* version: 1.0.0 (7.1.2021 - vytvorenie funkcie)
* @return $sending string
*/

function Sending_Prize() {
  Global $link;
  $q = mysqli_query($link, "SELECT u.*, l.longname FROM `e_xoops_users` u LEFT JOIN 2004leagues l ON l.id=SUBSTRING_INDEX(sending_prize,'-',1) WHERE u.sending_prize IS NOT NULL");
  if(mysqli_num_rows($q)>0)
    {
    $sending = "";
    while($f = mysqli_fetch_array($q))
      {
      $prize = explode("-", $f["sending_prize"]);
      if($prize[1]==0) { $hl = "prechodná bunda"; $image = "bunda.jpg"; }
      elseif($prize[1]==1) { $hl = "zimná čiapka"; $image = "ciapka.jpg"; }
      elseif($prize[1]==2) { $hl = "USB LED lampa"; $image = "usbled.jpg"; }
      elseif($prize[1]==3) { $hl = "odznaky"; $image = "odznaky.jpg"; }
      elseif($prize[1]==4) { $hl = "perá"; $image = "pero.jpg"; }
      elseif($prize[1]==5) { $hl = "nálepky"; $image = "nalepky.jpg"; }
      elseif($prize[1]==6) { $hl = "držiak na puk"; $image = "drziak.jpg"; }
      elseif($prize[1]==7) { $hl = "závesná placka"; $image = "placka.jpg"; }
      elseif($prize[1]==8) { $hl = "mikina s kapucňou"; $image = "mikina.jpg"; }
      else { $hl = "zástavka na auto"; $image = "zastavka.jpg"; }
      $sending .= '
      <div class="card shadow mb-4">
        <div class="card-header">
          <div class="font-weight-bold text-primary text-uppercase">Vecná cena na ceste</div>
        </div>
        <div class="card-body">
          <div class="row no-gutters align-items-center">
            <div class="col mr-2">
              <div class="text-xs text-muted font-weight-bold mb-1">Za víťazstvo v '.$f["longname"].'</div>
            </div>
          </div>
          <div class="row">
            <div class="col-12 text-center"><i class="fa-2x fa-trophy fas img-thumbnail p-2 rounded-circle shadow-sm text-warning"><span class="badge badge-info d-block text-xs">1.miesto</span></i></div>
            <div class="col-12 text-center"><i class="fa-angle-double-down fas text-gray-500"></i></div>
            <div class="col-12 text-center"><img class="img-thumbnail rounded-circle shadow-sm" src="/images/ceny/'.$image.'" style="width: 100px;"><p class="mt-1 mb-0 small">'.$hl.'</p></div>
            <div class="col-12 text-center"><i class="fa-angle-double-down fas text-gray-500"></i></div><div class="col-12 text-center"><img class="img-thumbnail rounded-circle shadow-sm" src="'.($f["user_avatar"]!="" ? '/images/user_avatars/'.$f["uid"].'.'.$f["user_avatar"]:'/img/players/no_photo.jpg').'" style="width: 65px;"><p class="mt-1 mb-0 small">'.$f["uname"].'</p><p></p></div>
          </div>
        </div>
        <div class="card-footer text-center p-fluid">
          <a href="/bets" class="text-gray-700">Tiež chcete súťažiť?</a>
        </div>
      </div>';
      }
    return $sending;
    }
}

/*
* Funkcia pre výpis hráča týždňa
* version: 1.0.0 (26.9.2017 - vytvorenie funkcie)
* version: 2.0.0 (6.2.2020 - prispôsobenie pre Boostrap 4 template)
* @return $potw string
*/

function potw() {
  Global $link;
  $potwdata = ComputePOTW();
    
  if($potwdata[0]!=0)
    {
    if($potwdata[1]==1) $q = mysqli_query($link, "SELECT p.*, l.longname FROM el_players p LEFT JOIN 2004leagues l ON l.id=p.league WHERE p.id='".$potwdata[0]."'");
    else $q = mysqli_query($link, "SELECT p.*, l.longname FROM 2004players p LEFT JOIN 2004leagues l ON l.id=p.league WHERE p.id='".$potwdata[0]."'");
    $f = mysqli_fetch_array($q);
    if($potwdata[2]=="") $potwdata[2]=0;
    if($potwdata[3]=="") $potwdata[3]=0;
    $p = $potwdata[2]+$potwdata[3];
    
    if($p==1) $hl = LANG_GAMECONT_POINT;
    else if($p>1 && $p<5) $hl = LANG_GAMECONT_POINTS;
    else $hl = LANG_TEAMSTATS_PTS;
    
    if($f["pos"]=="F" || $f["pos"]=="LW" || $f["pos"]=="RW" || $f["pos"]=="CE") $hl1=LANG_PLAYERSTATS_F;
    elseif($f["pos"]=="D" || $f["pos"]=="LD" || $f["pos"]=="RD") $hl1=LANG_PLAYERSTATS_D;
    else $hl1="";
    
    $potw = ' <div class="card border-left-warning shadow h-100">
                <div class="card-body p-3">
                  <div class="row no-gutters align-items-center pb-2">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">'.LANG_GAMECONT_POTW.'</div>
                      <div class="text-xs text-muted font-weight-bold mb-1">'.$f["longname"].'</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                    </div>
                  </div>
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2 text-center">
                      <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$f["name"].'" class="lazy rounded-circle img-thumbnail shadow-sm mb-2 p-1" style="width:100px; height:100px; object-fit: cover; object-position: center top;" alt="'.$f["name"].'">
                      <p class="m-0 font-weight-bold"><img class="flag-'.($potwdata[1]==1 ? 'el':'iihf').' '.$f["teamshort"].'-small align-baseline" src="/img/blank.png" alt="'.$f["teamlong"].'"> <a href="/player/'.$f["id"].$potwdata[1].'-'.SEOtitle($f["name"]).'" class="stretched-link text-gray-600">'.$f["name"].'</a></p>
                      <p class="m-0 text-xs">'.$hl1.'</p>
                      <p class="h5"><span class="badge badge-pill badge-warning">'.$p.' '.$hl.' ('.$potwdata[2].'G + '.$potwdata[3].'A)</span></p>
                    </div>
                  </div>
                </div>
              </div>';
    }
else
    {
    $potw = ' <div class="card border-left-warning shadow h-100">
                <div class="card-body p-3">
                  <div class="row no-gutters align-items-center pb-2">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">'.LANG_GAMECONT_POTW.'</div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                    </div>
                  </div>
                  <div class="row no-gutters align-items-center">
                    <div class="col mr-2 text-center">
                      <p class="mt-4">'.LANG_GAMECONT_NOPOTW.'</p>
                    </div>
                  </div>
                </div>
              </div>';
    }
return $potw;
}

/*
* Funkcia pre výpočet hráča týždňa
* version: 1.0.0 (26.9.2017 - vytvorenie funkcie)
* @return $potwdata array
*/

function ComputePOTW() {
  Global $link;
  $z = mysqli_query($link, "SELECT * FROM potw WHERE datetime='".date("Y-m-d")."'");
  if(mysqli_num_rows($z)>0)
    {
    $f = mysqli_fetch_array($z);
    $potwdata = array($f["pid"], $f["el"], $f["g"], $f["a"]);
    }
  else
    {
    $pondelok = date('Y-m-d', strtotime('next Monday -1 week', strtotime('this sunday')));
    $nedela = date('Y-m-d', strtotime('this sunday'));
    $potw = "";
    $w = mysqli_query($link, "(SELECT g.goaler, g.asister1, g.asister2, g.teamshort, m.league, 1 as el FROM `el_matches` m JOIN el_goals g ON g.matchno=m.id WHERE datetime > '$pondelok 00:00:00' && datetime < '$nedela 23:59:59')
    UNION
    (SELECT g1.goaler, g1.asister1, g1.asister2, g1.teamshort, m1.league, 0 as el FROM `2004matches` m1 JOIN 2004goals g1 ON g1.matchno=m1.id WHERE datetime > '$pondelok 00:00:00' && datetime < '$nedela 23:59:59')");
    if(mysqli_num_rows($w)==0)
      {
      // predosly tyzden
      $pondelok = date('Y-m-d', strtotime('next Monday -1 week', strtotime('last sunday')));
      $nedela = date('Y-m-d', strtotime('last sunday'));
      $w = mysqli_query($link, "(SELECT g.goaler, g.asister1, g.asister2, g.teamshort, m.league, 1 as el FROM `el_matches` m JOIN el_goals g ON g.matchno=m.id WHERE datetime > '$pondelok 00:00:00' && datetime < '$nedela 23:59:59')
      UNION
      (SELECT g1.goaler, g1.asister1, g1.asister2, g1.teamshort, m1.league, 0 as el FROM `2004matches` m1 JOIN 2004goals g1 ON g1.matchno=m1.id WHERE datetime > '$pondelok 00:00:00' && datetime < '$nedela 23:59:59')");
      }
    $stats = array();
    /*
    [0] => Array
        (
            [0] => points
            [1] => goals
            [2] => asists
            [3] => lid
            [5] => player
            [6] => teamshort
            [7] => el
        )    
    */
    $p=0;
    while($f = mysqli_fetch_array($w))
      {
      $gname = $f["goaler"];
      $a1name = $f["asister1"];
      $a2name = $f["asister2"];
      if(!isset($stats[$gname])) {
        $stats[$gname][0]=$stats[$gname][1]=$stats[$gname][2]=$stats[$gname][3]=$stats[$gname][7]=0;
        $stats[$gname][5]=$stats[$gname][6]="";
      }
      if(!isset($stats[$a1name])) {
        $stats[$a1name][0]=$stats[$a1name][1]=$stats[$a1name][2]=$stats[$a1name][3]=$stats[$a1name][7]=0;
        $stats[$a1name][5]=$stats[$a1name][6]="";
      }
      if(!isset($stats[$a2name])) {
        $stats[$a2name][0]=$stats[$a2name][1]=$stats[$a2name][2]=$stats[$a2name][3]=$stats[$a2name][7]=0;
        $stats[$a2name][5]=$stats[$a2name][6]="";
      }
      $stats[$gname][0]++; $stats[$gname][1]++; $stats[$gname][3]=$f["league"]; $stats[$gname][5]=$gname; $stats[$gname][6]=$f["teamshort"]; $stats[$gname][7]=$f["el"]; 
      if($f["asister1"]!="bez asistencie") { $stats[$a1name][0]++; $stats[$a1name][2]++; $stats[$a1name][3]=$f["league"]; $stats[$a1name][5]=$a1name; $stats[$a1name][6]=$f["teamshort"]; $stats[$a1name][7]=$f["el"]; }
      if($f["asister2"]!="bez asistencie") { $stats[$a2name][0]++; $stats[$a2name][2]++; $stats[$a2name][3]=$f["league"]; $stats[$a2name][5]=$a2name; $stats[$a2name][6]=$f["teamshort"]; $stats[$a2name][7]=$f["el"]; }
      $p++;
      }
    usort($stats, function($a,$b){ $c = $b[0] - $a[0]; $c .= $b[1] - $a[1]; return $c; });
    $teraz = date("Y-m-d");
    if(isset($stats[0])) {
      if($stats[0][7]==1) $e = mysqli_query($link, "SELECT * FROM el_players WHERE league='".$stats[0][3]."' && name='".$stats[0][5]."' ORDER BY id DESC LIMIT 1");
      else $e = mysqli_query($link, "SELECT * FROM 2004players WHERE league='".$stats[0][3]."' && name='".$stats[0][5]."' ORDER BY id DESC LIMIT 1");
      $r = mysqli_fetch_array($e);
      if($stats[0][1]=="") $stats[0][1]=0;
      if($stats[0][2]=="") $stats[0][2]=0;
      if($r && !empty($r["id"])) {
        mysqli_query($link, "INSERT INTO potw (datetime, pid, el, g, a) VALUES ('$teraz', '".$r["id"]."', '".$stats[0][7]."', '".$stats[0][1]."', '".$stats[0][2]."')");
        $potwdata = array($r["id"], $stats[0][7], $stats[0][1], $stats[0][2]);
      } else {
        $potwdata = array(0, 0, 0, 0);
      }
    }
    else $potwdata = array(0, 0, 0, 0);
    }
    return $potwdata;
}

/*
* Funkcia pre výpočet zápasu dňa
* version: 2.0.0 (13.1.2022 - prekopaná stará verzia funkcie)
* @return $gotdid array (matchid, el)
*/

function ComputeGOTD()
  {
  Global $lid, $link, $nhl_players, $nhl_goalies, $khl_players, $khl_goalies;
  $z = mysqli_query($link, "SELECT * FROM gotd WHERE datetime='".date("Y-m-d")."'");
  if(mysqli_num_rows($z)>0)
    {
    $f = mysqli_fetch_array($z);
    $gotdid = array($f["matchid"], $f["el"]);
    }
  else {
    // vybrat dnesnu najdolezitejsiu ligu v poradi TURNAJ -> EXTRALIGA -> NHL -> KHL
    $dnes = date("Y-m-d");
    $zajtra = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+1, date('Y')));
    $a = mysqli_query($link, "SELECT dt.*, et.zor FROM ((SELECT team1long, team2long, datetime, league FROM 2004matches WHERE datetime > '$dnes 07:00:00' && datetime < '$zajtra 07:00:00') UNION (SELECT team1long, team2long, datetime, league FROM el_matches WHERE datetime > '$dnes 07:00:00' && datetime < '$zajtra 07:00:00') ORDER BY datetime ASC)dt JOIN (SELECT id, IF(el=0,1,IF(topic_id=60,2,IF(topic_id=68,3,IF(topic_id=71,4,0)))) as zor FROM 2004leagues)et ON et.id=dt.league ORDER BY et.zor LIMIT 1;");
    if(mysqli_num_rows($a)==0) {
      // nehra sa, najdi najblizsi hraci den
      $ne = mysqli_query($link, "SELECT datetime FROM 2004matches WHERE datetime > '$dnes 07:00:00' UNION SELECT datetime FROM el_matches WHERE datetime > '$dnes 07:00:00' LIMIT 1");
      $nea = mysqli_fetch_array($ne);
      if(mysqli_num_rows($ne)==0) {
        $dnes = date("Y-m-d");
      }
      else {
        $dnes = date("Y-m-d", strtotime($nea["datetime"]));
        $zajtra = date('Y-m-d', strtotime($nea["datetime"])+86400);
        if(date("G", strtotime($nea["datetime"]))<=7) {
            $dnes = date("Y-m-d", strtotime($nea["datetime"])-86400);
            $zajtra = date('Y-m-d', strtotime($nea["datetime"]));
        }
      }
      $a = mysqli_query($link, "SELECT dt.*, et.zor FROM ((SELECT team1long, team2long, datetime, league FROM 2004matches WHERE datetime > '$dnes 07:00:00' && datetime < '$zajtra 07:00:00') UNION (SELECT team1long, team2long, datetime, league FROM el_matches WHERE datetime > '$dnes 07:00:00' && datetime < '$zajtra 07:00:00') ORDER BY datetime ASC)dt JOIN (SELECT id, IF(el=0,1,IF(topic_id=60,2,IF(topic_id=68,3,IF(topic_id=71,4,0)))) as zor FROM 2004leagues)et ON et.id=dt.league ORDER BY et.zor LIMIT 1;");
    }
    $f = mysqli_fetch_array($a);
    $lid = $f["league"];

    // zistenie ci sa jedna o EL
    $q = mysqli_query($link, "SELECT * FROM 2004leagues WHERE id='$lid'");
    $f = mysqli_fetch_array($q);
    $teraz = date("Y-m-d H:i:s");
    // JEDNA SA O EL
    if($f["el"]==1)
      {
      if(date("n")<8) {
        $rok = date("Y")-1;
        $season_start = $rok."-01-08";
        }
      else $season_start = date("Y")."-01-08";
        if (!isset($nhl_players)) {
            require_once('slovaks.php');
            $nhl_players = $slovaks;
            $nhl_goalies = $brankari;
        }
        if (!isset($khl_players)) {
            require_once('slovaki.php');
            $khl_players = $slovaks;
            $khl_goalies = $brankari;
        }
      $a = mysqli_query($link, "SELECT * FROM el_matches WHERE datetime > '$dnes 07:00:00' && datetime < '$zajtra 07:00:00' && league='$lid'");
      if($f["topic_id"]==68 || $f["topic_id"]==71) {
        // NHL a KHL
        if($f["topic_id"]==68) { $slovaks = $nhl_players; $brankari = $nhl_goalies; }
        if($f["topic_id"]==71) { $slovaks = $khl_players; $brankari = $khl_goalies; }
        $games_with_slovaks = array();
        while($b = mysqli_fetch_array($a)) {
          $zra = $tran1 = $tran2 = array();
          $z = mysqli_query($link, "SELECT * FROM el_injuries WHERE league='".$lid."'");
          while($zr = mysqli_fetch_array($z))
            {
            $zra[] = $zr["name"];
            }
          $tr = mysqli_query($link, "SELECT from_team, pname FROM transfers WHERE (from_team='".$b["team1short"]."' || from_team='".$b["team2short"]."') && datetime>'".$season_start."'");
          while($tra = mysqli_fetch_array($tr))
            {
            if($tra["from_team"]==$b["team1short"]) $tran1[] = $tra["pname"];
            if($tra["from_team"]==$b["team2short"]) $tran2[] = $tra["pname"];
            }
          $pia1 = array_keys($slovaks, $b["team1short"]);
          $gia1 = array_keys($brankari, $b["team1short"]);
          $inaction1 = array_merge($pia1, $gia1);
          if(count($zra)>0) $inaction1 = array_diff($inaction1, $zra);
          if(count($tran1)>0) $inaction1 = array_diff($inaction1, $tran1);
          $inaction1 = array_values($inaction1);
          $pia2 = array_keys($slovaks, $b["team2short"]);
          $gia2 = array_keys($brankari, $b["team2short"]);
          $inaction2 = array_merge($pia2, $gia2);
          if(count($zra)>0) $inaction2 = array_diff($inaction2, $zra);
          if(count($tran2)>0) $inaction2 = array_diff($inaction2, $tran2);
          $inaction2 = array_values($inaction2);
          $slovaks_sum = count($inaction1)+count($inaction2);
          if($slovaks_sum>0) $games_with_slovaks[] = $b["id"];
        }
        if(count($games_with_slovaks)>0) {
          // vyber lubovolny zapas so slovakmi
          $rand = array_rand($games_with_slovaks, 1);
          $gotdid = array($games_with_slovaks[$rand], 1);
        }
        else {
          // vyber lubovolny zapas dna
          $a = mysqli_query($link, "SELECT * FROM el_matches WHERE datetime > '$dnes 07:00:00' && datetime < '$zajtra 07:00:00' && league='$lid' ORDER BY rand() LIMIT 1");
          $b = mysqli_fetch_array($a);
          $gotdid = array($b["id"], 1);
        }
      }
      else {
        // EXTRALIGA
        $k=0;
        while($b = mysqli_fetch_array($a)) 
          {
          $i=0;
          $tim = mysqli_query($link, "SELECT *, goals-ga as diff FROM el_teams WHERE league='$lid' ORDER BY body desc, diff desc, goals desc, wins desc, losts asc, ties desc");
          while($i < mysqli_num_rows($tim))
            {
            $ti = mysqli_fetch_array($tim);
            if($b["team1short"]==$ti["shortname"])
              {
              $t1p=$i+1;
              break;
              }
            $i++;
            }
          $j=0;
          $tim = mysqli_query($link, "SELECT *, goals-ga as diff FROM el_teams WHERE league='$lid' ORDER BY body desc, diff desc, goals desc, wins desc, losts asc, ties desc");
          while($j < mysqli_num_rows($tim))
            {
            $ti = mysqli_fetch_array($tim);
            if($b["team2short"]==$ti["shortname"])
              {
              $t2p=$j+1;
              break;
              }
            $j++;
            }
          $rozdiel = abs($t1p-$t2p);
          $pozicia = round(($t1p+$t2p)/2, 1);
          $vysl = round(($rozdiel+$pozicia)/2, 1);
          $cis[$k] = array($vysl, $b["id"], 1);
          $k++;
          }
      sort($cis);
      $gotdid = array($cis[0][1], 1);
      }
    }
    // NEJEDNA SA O EL
    else {
      $a = mysqli_query($link, "SELECT * FROM 2004matches WHERE datetime > '$dnes 07:00:00' && datetime < '$zajtra 07:00:00' && league='$lid' && (team1short='SVK' || team2short='SVK')");
      if(mysqli_num_rows($a)>0) {
        // hra Slovensko
        $b = mysqli_fetch_array($a);
        $gotdid = array($b["id"], 0);
      }
      else {
        // nehra Slovensko
        $a = mysqli_query($link, "SELECT * FROM 2004matches WHERE datetime > '$dnes 07:00:00' && datetime < '$zajtra 07:00:00' && league='$lid'");
        $k=0;
        while($b = mysqli_fetch_array($a)) 
          {
          $i=0;
          $tim = mysqli_query($link, "SELECT *, goals-ga as diff FROM 2004teams WHERE league='$lid' ORDER BY body desc, diff desc, goals desc, wins desc, losts asc, ties desc");
          while($i < mysqli_num_rows($tim))
            {
            $ti = mysqli_fetch_array($tim);
            if($b["team1short"]==$ti["shortname"])
              {
              $t1p=$i+1;
              break;
              }
            $i++;
            }
          $j=0;
          $tim = mysqli_query($link, "SELECT *, goals-ga as diff FROM 2004teams WHERE league='$lid' ORDER BY body desc, diff desc, goals desc, wins desc, losts asc, ties desc");
          while($j < mysqli_num_rows($tim))
            {
            $ti = mysqli_fetch_array($tim);
            if($b["team2short"]==$ti["shortname"])
              {
              $t2p=$j+1;
              break;
              }
            $j++;
            }
          $rozdiel = abs($t1p-$t2p);
          $pozicia = round(($t1p+$t2p)/2, 1);
          $vysl = round(($rozdiel+$pozicia)/2, 1);
          $cis[$k] = array($vysl, $b["id"], 1);
          $k++;
          }
        sort($cis);
        $gotdid = array($cis[0][1], 0);
      }
    }
  $dnes = date("Y-m-d");
  mysqli_query($link, "REPLACE INTO gotd (datetime, matchid, el) VALUES ('$dnes', '".$gotdid[0]."', '".$gotdid[1]."')");
  }
  return $gotdid;
}

/*
* Funkcia pre zobrazenie zápasu dňa
* version: 1.5.0 (23.11.2015 - funguje na základe starej verzie - prenesené do novej)
* version: 2.0.0 (3.2.2020 - prispôsobenie pre Boostrap 4 template)
* @return $gotd string
*/

function gotd()
  {
  Global $link, $nhl_players, $nhl_goalies, $khl_players, $khl_goalies;
  $pohl=$slov=$tv="";
  $gotdid = ComputeGOTD();
  
  $k = mysqli_query($link, "select * from comments WHERE what='2' && whatid='".$gotdid[0].$gotdid[1]."'");
  $comm_count = mysqli_num_rows($k);
  
  $gotd = '<div class="card border-left-primary shadow h-100">
      '.($comm_count>0 ? '
            <div class="position-absolute" style="right: -5px;top: -10px;">
              <span class="badge badge-pill badge-secondary"><a href="/game/'.$gotdid[0].$gotdid[1].'#comments" class="text-white"><i class="fa fa-comment mr-1"></i>'.$comm_count.'</a></span>
            </div>
            ':'').'
            <div class="card-body d-flex flex-column p-3">
              <div class="row no-gutters align-items-center pb-2">
                <div class="col mr-2">
                  <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">'.LANG_CARDS_GOTD.'</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-hockey-puck fa-2x text-gray-300"></i>
                </div>
              </div>';
  
  if($gotdid[0]!=0)
    {
    if($gotdid[1]==1) { $mtable = "el_matches"; $ttable = "el_teams"; $st=""; }
    else { $mtable = "2004matches"; $ttable = "2004teams"; $st = " shadow-sm"; }
    $q = mysqli_query($link, "SELECT m.*, DATE_FORMAT(m.datetime, '%e.%c.%Y o %k:%i') as datum, t1.id as t1id, t2.id as t2id, t1.longname as t1long, t2.longname as t2long, l.longname as league_name FROM $mtable m LEFT JOIN $ttable t1 ON t1.shortname=m.team1short && t1.league=m.league LEFT JOIN $ttable t2 ON t2.shortname=m.team2short && t2.league=m.league LEFT JOIN 2004leagues l ON l.id=m.league WHERE m.id='".$gotdid[0]."'");

    if(mysqli_num_rows($q)>0) {
        $gotf = mysqli_fetch_array($q);
        if($gotdid[1]==1) {
        $g = mysqli_query($link, "SELECT count(id) as poc, ROUND(sum(tip1)/count(id),2) as vys1, ROUND(sum(tip2)/count(id),2) as vys2 FROM el_tips WHERE matchid='".$gotdid[0]."'");
        // stav serie
        $gotf["kolo"] = $gotf["kolo"] ?? null;
        if($gotf["kolo"]==0) {
            $p = mysqli_query($link, "SELECT * FROM `el_playoff` WHERE league='".$gotf["league"]."' && ((team1='".$gotf["team1short"]."' && team2='".$gotf["team2short"]."') || (team2='".$gotf["team1short"]."' && team1='".$gotf["team2short"]."'))");
            $po = mysqli_fetch_array($p);
            if($gotf["team1short"]!=$po["team1"]) $sstatus = $po["status2"].':'.$po["status1"];
            else $sstatus = $po["status1"].':'.$po["status2"];
            $pohl = '<p class="m-0"><span class="font-weight-bold">'.LANG_MATCHES_SERIES.':</span> '.$sstatus.'</p>';
        }
        // slovaci v akcii
            if (!isset($nhl_players)) {
                require_once('slovaks.php');
                $nhl_players = $slovaks;
                $nhl_goalies = $brankari;
            }
            if (!isset($khl_players)) {
                require_once('slovaki.php');
                $khl_players = $slovaks;
                $khl_goalies = $brankari;
            }
        $slov=$tv="";
        if(strstr($gotf["league_name"], 'NHL') || strstr($gotf["league_name"], 'KHL'))
            {
            $tran1 = $tran2 = array();
            if(date("n")<8) {
            $rok = date("Y")-1;
            $season_start = $rok."-01-08";
            }
            else $season_start = date("Y")."-01-08";
            $z = mysqli_query($link, "SELECT * FROM el_injuries WHERE league='".$gotf["league"]."'");
            while($zr = mysqli_fetch_array($z))
            {
            $zra[] = $zr["name"];
            }
            $tr = mysqli_query($link, "SELECT from_team, pname FROM transfers WHERE (from_team='".$gotf["team1short"]."' || from_team='".$gotf["team2short"]."') && datetime>'".$season_start."'");
            while($tra = mysqli_fetch_array($tr))
            {
            if($tra["from_team"]==$gotf["team1short"]) $tran1[] = $tra["pname"];
            if($tra["from_team"]==$gotf["team2short"]) $tran2[] = $tra["pname"];
            }
            if(strstr($gotf["league_name"], 'NHL')) { $slovaks = $nhl_players; $brankari = $nhl_goalies; }
            if(strstr($gotf["league_name"], 'KHL')) { $slovaks = $khl_players; $brankari = $khl_goalies; }
            $pia1 = array_keys($slovaks, $gotf["team1short"]);
            $gia1 = array_keys($brankari, $gotf["team1short"]);
            $inaction1 = array_merge($pia1, $gia1);
            if(count($zra)>0) $inaction1 = array_diff($inaction1, $zra);
            if(count($tran1)>0) $inaction1 = array_diff($inaction1, $tran1);
            $inaction1 = array_values($inaction1);
            $pia2 = array_keys($slovaks, $gotf["team2short"]);
            $gia2 = array_keys($brankari, $gotf["team2short"]);
            $inaction2 = array_merge($pia2, $gia2);
            if(count($zra)>0) $inaction2 = array_diff($inaction2, $zra);
            if(count($tran2)>0) $inaction2 = array_diff($inaction2, $tran2);
            $inaction2 = array_values($inaction2);
            if(count($inaction1)>0 || count($inaction2)>0)
            {
            $c = count($inaction1)+count($inaction2);
            $slov = '<p class="m-0"><span class="font-weight-bold">'.LANG_MATCHES_SLOVAKS.':</span> '.(count($inaction1)>0 ? implode(", ",$inaction1):'').(count($inaction2)>0 ? (count($inaction1)>0 ? ', '.implode(", ",$inaction2):implode(", ",$inaction2)):'').'</p>';
            }
            }
        }
        else $g = mysqli_query($link, "SELECT count(id) as poc, ROUND(sum(tip1)/count(id),2) as vys1, ROUND(sum(tip2)/count(id),2) as vys2 FROM 2004tips WHERE matchid='".$gotdid[0]."'");
        if($gotf["fs_tv"]!=NULL && $gotf["fs_tv"]!='[]')
        {
        $tvarr = json_decode($gotf["fs_tv"], true);
        $tvarr = implode(", ",$tvarr);
        $tv .= '<p class="m-0"><span class="font-weight-bold">LIVE:</span> '.$tvarr.'</p>';
        }
        $h = mysqli_fetch_array($g);
        
        $gotd .= '    <div class="row mb-2 no-gutters">
                        <div class="col-5 text-center">
                        <img src="/images/vlajky/'.$gotf["team1short"].'.gif" alt="'.$gotf["team1long"].'" class="img-fluid'.$st.'">
                        <div class="gotd-team h6 mb-0 mt-1 font-weight-bold"><a href="/team/'.$gotf["t1id"].($gotdid[1]==1 ? '1':'0').'-'.SEOtitle($gotf["t1long"]).'" class="stretched-link text-gray-800">'.$gotf["team1long"].'</a></div>
                        </div>
                        <div class="col-2 text-center align-self-center">
                        vs.
                        </div>
                        <div class="col-5 text-center">
                        <img src="/images/vlajky/'.$gotf["team2short"].'.gif" alt="'.$gotf["team2long"].'" class="img-fluid'.$st.'">
                        <div class="gotd-team h6 mb-0 mt-1 font-weight-bold"><a href="/team/'.$gotf["t2id"].($gotdid[1]==1 ? '1':'0').'-'.SEOtitle($gotf["t2long"]).'" class="stretched-link text-gray-800">'.$gotf["team2long"].'</a></div>
                        </div>
                    </div>
                    <div class="text-xs text-center mb-2">
                        '.$pohl.'
                        <p class="m-0"><span class="font-weight-bold">'.LIVE_GAME_START.':</span> '.$gotf["datum"].'</p>
                        <p class="m-0"><span class="font-weight-bold">'.LANG_MATCHES_AVGBET.':</span> '.$h["vys1"].' : '.$h["vys2"].'</p>
                        <p class="m-0"><span class="font-weight-bold">'.LANG_MATCHES_BETS.':</span> '.$h["poc"].'</p>
                        '.$slov.'
                        '.$tv.'
                    </div>
                    <div class="align-items-end d-flex flex-fill justify-content-center">
                        <a href="/'.($gotf["active"]==1 ? 'report':'game').'/'.$gotf["id"].$gotdid[1].'-'.SEOtitle($gotf["team1long"].' vs '.$gotf["team2long"]).'" class="btn btn-light btn-icon-split">
                        <span class="icon text-gray-600">
                            <i class="fas fa-search"></i>
                        </span>
                        <span class="text text-gray-800">'.($gotf["active"]==1 ? LANG_NAV_LIVE:LANG_MATCHES_DETAIL).'</span>
                        </a>
                    </div>';
    }
    else {
        if(date("n")>5 && date("n")<9) $gotd .= '<h5 class="mt-2 text-center"><i class="fas fa-umbrella-beach text-gray-300"></i> Letná prestávka</h5><p class="mt-4 text-center">Netrpezlivo čakáme na začiatok novej sezóny ...</p>';
        else $gotd .= '<h5 class="mt-2 text-center"><i class="fas fa-ban text-gray-300"></i> Bez zápasu</h5><p class="mt-4 text-center">Najbližšie nás nečaká žiaden napínavý zápas</p>';
    }
    }
  else
    {
    if(date("n")>5 && date("n")<9) $gotd .= '<h5 class="mt-2 text-center"><i class="fas fa-umbrella-beach text-gray-300"></i> Letná prestávka</h5><p class="mt-4 text-center">Netrpezlivo čakáme na začiatok novej sezóny ...</p>';
    else $gotd .= '<h5 class="mt-2 text-center"><i class="fas fa-ban text-gray-300"></i> Bez zápasu</h5><p class="mt-4 text-center">Najbližšie nás nečaká žiaden napínavý zápas</p>';
    }
  $gotd .= '  </div>
            </div>';
  return $gotd;
  }
  
/*
* Funkcia pre zobrazenie noviniek
* version: 1.5.0 (29.11.2015 - prerobená stará verzia funkcie do novej podoby stránky)
* version: 2.0.0 (6.2.2025 - prispôsobené pre Boostrap 4 template)
* @param $limit integer - počet noviniek na stránku
* @param $page integer - aktuálna stránka
* @param $topicID integer - zvolené ID kategórie
* @return $newsList string
*/
  
function Get_news($limit, $page,$topicID = false) {
Global $link;

$newsList=$specifiedTopic="";

if(!isset($page)) $page=1;
else $page = intval($page);
$limit_start = ($page*$limit)-$limit;
if($limit_start<0) $limit_start=0;
if($topicID != false && $topicID!="all")
  {
  $topic = explode("-", $topicID);
  $topicName = substr($topicID, strpos($topicID, "-")+1,strlen($topicID));
  $topicID = $topic[0];
  $specifiedTopic = "&& s.topicid = '".$topicID."'";
  }
$q = mysqli_query($link, "SELECT s.*, t.topic_id, t.topic_title, u.uname, u.name, count(c.id) as comment_count FROM e_xoops_stories s LEFT JOIN e_xoops_topics t ON t.topic_id=s.topicid LEFT JOIN e_xoops_users u ON u.uid=s.uid LEFT JOIN comments c ON c.what='0' && c.whatid=s.storyid WHERE s.langID = 'sk' ".$specifiedTopic." && s.topicdisplay='1' && s.ihome='0' && s.lid IS NULL GROUP BY s.storyid ORDER BY s.published DESC LIMIT $limit_start, $limit");
$i=0;
while ($f = mysqli_fetch_array($q)) {
$s="";
$e="";
if($i==2) $newsList .= '<div class="card shadow mb-4">
  <div class="col">
    <script async defer src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <ins class="adsbygoogle"
         style="display:block"
         data-overlays="bottom"
         data-ad-format="fluid"
         data-ad-layout-key="-ei-13-51-9i+155"
         data-ad-client="ca-pub-8860983069832222"
         data-ad-slot="7800945421"></ins>
    <script>
         (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
  </div>
</div>';
/*$newsList .= '
<div class="card shadow mb-4">
    <div class="col">
        <div id="101390-1">
            <script src="//ads.themoneytizer.com/s/gen.js?type=1" defer></script>
            <script src="//ads.themoneytizer.com/s/requestform.js?siteId=101390&formatId=1" defer></script>
        </div>
    </div>
</div>';*/
if($f["bodytext"] != '') { $s = '<a href="/news/'.$f["storyid"].'-'.SEOtitle($f["title"]).'">'; $e = '</a>'; }
    preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $f["hometext"], $image);
    preg_match('/<img.+class=[\'"](?P<class>.+?)[\'"].*>/i', $f["hometext"], $imageclass);
    $tags_to_strip = Array("img");
    foreach ($tags_to_strip as $tag)
      {
      $f["hometext"] = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/","",$f["hometext"]);
      }
    $newsList .= '<div class="card shadow border-left-'.LeagueColor($f["topic_title"]).' mb-4">
                    <div class="row no-gutters">
                  
                      <div class="col-md-4 col-lg-2 border-right lazy" data-src="'.(isset($image['src']) ? $image['src']:"").'" style="
                          min-height: 200px;
                          background-position: center center;
                          background-repeat: no-repeat;
                          background-size: '.(isset($imageclass['class']) && strstr($imageclass['class'], "news-cover") ? 'cover':'contain').';
                      ">
                      </div>
                      
                      <div class="col-md-8 col-lg-10">
                  
                        <div class="card-header py-3 clearfix">
                          <h6 class="float-left m-0 font-weight-bold text-gray-900"><a href="/news/'.$f["storyid"].'-'.SEOtitle($f["title"]).'" class="stretched-link text-gray-900">'.$f["title"].'</a></h6>
                          <h6 class="float-right m-1 text-xs font-weight-bold text-'.LeagueColor($f["topic_title"]).' text-uppercase">'.$f["topic_title"].'</h6>
                        </div>
                        <div class="card-body news-body text-justify">
                          '.$f["hometext"].'
                          '.($f["bodytext"]!="" ? '<p class="float-right small"><a href="#" class="text-'.LeagueColor($f["topic_title"]).'">'.LANG_READMORE.' <i class="fas fa-angle-double-right"></i></a></p>':'').'
                          <p class="text-left text-xs text-muted m-0"><span class="font-weight-bold">'.$f["name"].'</span> · '.date("j.n.Y H:i",$f["published"]).' · <span class="text-hl"><i class="far fa-comment"></i> '.$f["comment_count"].'</span></p>
                        </div>
                      
                      </div>
                      
                    </div>
                  </div>';
      $i++;
      }
      
  $pagenext = $page+1;
  $pageprev = $page-1;
  if($topicID != false && $topicID!="all")
    {
    $specifiedTopic = " WHERE e_xoops_stories.topicid = '".$topicID."'";
    $tema = $topicID."-".$topicName;
    }
  else {
      $tema = "all";
      $specifiedTopic = "";
  }
  $p = mysqli_query($link, "SELECT * FROM e_xoops_stories".$specifiedTopic);
  $num = mysqli_num_rows($p);
  $newsList .= '<nav aria-label="Page navigation">
        <ul class="pagination justify-content-between">
          <li class="page-item">'.($pageprev>=1 ? '<a class="page-link" href="/category/'.$tema.'/'.$pageprev.'"><i class="fas fa-angle-double-left" aria-hidden="true"></i> '.LANG_BACK.'</a>':'').'</li>
          <li class="page-item">'.(($page*$limit)<=$num ? '<a class="page-link" href="/category/'.$tema.'/'.$pagenext.'">'.LANG_NEXT.' <i class="fas fa-angle-double-right" aria-hidden="true"></i></a>':'').'</li>
        </ul>
      </nav>';
      
  return $newsList;
  }
?>