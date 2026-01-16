<?
if(isset($_GET["pid"]))
  {
  $params = explode("/", htmlspecialchars($_GET["pid"]));
  $pid = explode("-", htmlspecialchars($params[0]));
  $pid=$pid[0];
  }
if(isset($_GET["gid"]))
  {
  $params = explode("/", htmlspecialchars($_GET["gid"]));
  $gid = explode("-", htmlspecialchars($params[0]));
  $gid=$gid[0];
  }
if(isset($_GET["slovaks"]))
  {
  $params = explode("/", htmlspecialchars($_GET["slovaks"]));
  $sid = explode("-", htmlspecialchars($params[0]));
  $sid=$sid[0];
  }
if(isset($_GET["injured"]))
  {
  $params = explode("/", htmlspecialchars($_GET["injured"]));
  $iid = explode("-", htmlspecialchars($params[0]));
  $iid=$iid[0];
  }
if(isset($_GET["transfers"]))
  {
  $params = explode("/", htmlspecialchars($_GET["transfers"]));
  $tid = explode("-", htmlspecialchars($params[0]));
  $tid=$tid[0];
  }
  
$locale = explode(";",setlocale(LC_ALL, '0'));
$locale = explode("=",$locale[0]);
$locale = $locale[1];

include("includes/advert_bigscreenside.php");

$content = "";
// slovaci v KHL a NHL
if(isset($sid))
  {
  $q = mysqli_query($link, "SELECT dt.topic_title, 2004leagues.color, 2004leagues.longname FROM 2004leagues JOIN (SELECT * FROM e_xoops_topics)dt ON 2004leagues.topic_id=dt.topic_id WHERE id='$sid'");
  $f = mysqli_fetch_array($q);
  $lid = $sid;
  $leaguecolor = $f["color"];
  $active_league = $lid;
  if($f["topic_title"]=="KHL") include("includes/slovaki.php"); 
  else include("includes/slovaks.php");
  $in = $slovaks;
  $title = LANG_PLAYERS_SLOVAKSTITLE." ".$f["topic_title"];

  if($f["topic_title"]=="NHL") $content .= '
            <!-- Video modal -->
            <div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="videoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                    <div class="modal-content bg-gradient-secondary">
                        <div class="modal-header border-0 pb-0 pt-2">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="clearVideoUrl()">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe class="embed-responsive-item border-0" id="videoFrame" src="" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
   
  $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($f["longname"])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERS_SLOVAKSTITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$f["longname"]."</h2>
               <div class='row'>
                <div class='col-12' style='max-width: 1000px;'>";
  
	$content .= '<div class="card my-4 shadow animated--grow-in">
              <div class="card-header">
                <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                  '.LANG_TEAMSTATS_PLAYERS.'
                  <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                </h6>
              </div>
              <div class="card-body">
                  <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="players">
                  <thead><tr>
                    <th class="text-center" style="width:2%;">#</th>
                    <th style="width:11%;">'.LANG_PLAYERSTATS_TEAM.'</th>
                    <th style="width:25%;">'.LANG_TEAMSTATS_NAME.'</th>
                    <th class="text-center" style="width:6%;" data-toggle="tooltip" data-placement="top" title="'.LANG_PLAYERSTATS_POS.'">POS</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAMES.'">GP</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GOALS.'">G</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_ASISTS.'">A</th>
                    <th class="text-center sorting_desc" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_POINTS.'">P</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PPG.'">PPG</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SHG.'">SHG</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GWG.'">GWG</th>
                </tr>
              </thead>
              <tbody>';

	$i=0;
  if(!is_array($slovaks)) $slovaks = "''";
  else {
    if(count($slovaks)==0) $slovaks = "''";
    else {
        array_walk($slovaks, function(&$i, $k) { $i = "'$k'"; });
        $slovaks = implode(",", $slovaks);
    }
  }
  
  $r = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
	$r = mysqli_query($link, "SELECT dt.id,dt.name,dt.teamshort,dt.pos, sum(dt.gp) as gp, sum(dt.goals) as goals, sum(dt.asists) as asists, sum(dt.points) as points, sum(dt.gwg) as gwg, sum(dt.gtg) as gtg, sum(dt.shg) as shg, sum(dt.ppg) as ppg, sum(dt.penalty) as penalty, et.injury FROM (SELECT * FROM el_players WHERE name IN (".$slovaks.") && league='$lid' ORDER BY id DESC)dt LEFT JOIN(SELECT name, injury FROM el_injuries WHERE league='$lid')et ON dt.name=et.name GROUP BY dt.name ORDER BY points DESC, gp ASC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC");
		
		$p=1;
while ($t = mysqli_fetch_array($r))
      {
	if($t["injury"]!=NULL) $injury = ' <i class="fas fa-user-injured text-danger align-self-center" data-toggle="tooltip" data-placement="top" data-html="true" title="'.LANG_PLAYERS_INJURED.': <b>'.$t["injury"].'</b>"></i>';
	else $injury = '';
	
	$content .= '<tr>
                  <td class="text-center" style="width:2%;">'.$p.'.</td>
                  <td style="width:11%;" class="text-nowrap"><img class="flag-el '.$t["teamshort"].'-small" src="/images/blank.png" alt="'.$t["teamshort"].'"> '.$t["teamshort"].'</td>
                  <td class="d-flex justify-content-between text-nowrap"><a href="/player/'.$t["id"].'1-'.SEOTitle($t["name"]).'">'.$t["name"].'</a>'.$injury.'</td>
                  <td class="text-center" style="width:6%;">'.$t["pos"].'</td>
                  <td class="text-center" style="width:7%;">'.$t["gp"].'</td>
                  <td class="text-center" style="width:7%;">'.$t["goals"].'</td>
                  <td class="text-center" style="width:7%;">'.$t["asists"].'</td>
                  <td class="text-center font-weight-bold" style="width:7%;">'.$t["points"].'</td>
                  <td class="text-center" style="width:7%;">'.$t["penalty"].'</td>
                  <td class="text-center" style="width:7%;">'.$t["ppg"].'</td>
                  <td class="text-center" style="width:7%;">'.$t["shg"].'</td>
                  <td class="text-center" style="width:7%;">'.$t["gwg"].'</td>
                </tr>';

      $y = mysqli_query($link, "SELECT * FROM player_lowerleagues WHERE name='".$t["name"]."' && league_upper='".$lid."'");
      if(mysqli_num_rows($y)>0) {
          while($u = mysqli_fetch_array($y)) {
            $points = $u["goals"]+$u["asists"];
            $content .= '<tr>
                    <td colspan="2"></td>
                    <td colspan="2" class="text-nowrap text-xs"><div><i class="fa-arrow-turn-up fa-rotate-90 fa-solid mr-1"></i>nižšia liga</div><div class="d-flex"><img src="'.$u["team_image"].'" style="
        height: 15px;
    " class="mr-1">'.$u["teamlong"].' ('.$u["league_name"].')</div></td>
                    
                    <td class="text-center" style="width:7%;">'.$u["gp"].'</td>
                    <td class="text-center" style="width:7%;">'.$u["goals"].'</td>
                    <td class="text-center" style="width:7%;">'.$u["asists"].'</td>
                    <td class="text-center font-weight-bold" style="width:7%;">'.$points.'</td>
                    <td colspan="4"</td>
                    </tr>';
          }
      }

      $p++;
      }
$content .= "</tbody></table>
            </div>
           </div>";

  if(isset($brankari) && !is_array($brankari)) $brankari = "''";
  else {
    if(count($brankari)==0) $brankari = "''";
    else {
        array_walk($brankari, function(&$i, $k) { $i = "'$k'"; });
        $brankari = implode(",", $brankari);
    }
  }

$r = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
$r = mysqli_query($link, "SELECT id, el_goalies.name, teamshort, sum(gp) as gp, sum(sog) as sog, sum(svs) as svs, sum(ga) as ga, sum(so) as so, sum(pim) as pim, (sum(svs)/sum(sog))*100 as svsp, sum(ga)/sum(gp) as gaa, et.injury FROM el_goalies LEFT JOIN(SELECT name, injury FROM el_injuries WHERE league='".$lid."')et ON el_goalies.name=et.name WHERE el_goalies.name IN (".$brankari.") && league='".$lid."' GROUP BY name ORDER BY svsp DESC, gaa ASC");

if(mysqli_num_rows($r)>0)
  {

$content .= '<div class="card my-4 shadow animated--grow-in">
              <div class="card-header">
                <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                  '.LANG_TEAMSTATS_GOALIES.'
                  <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                </h6>
              </div>
              <div class="card-body">
                  <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="players">
                  <thead><tr>
                    <th class="text-center" style="width:2%;">#</th>
                    <th style="width:11%;">'.LANG_PLAYERSTATS_TEAM.'</th>
                    <th style="width:25%;">'.LANG_TEAMSTATS_NAME.'</th>
                    <th class="text-center" style="width:6%;" data-toggle="tooltip" data-placement="top" title="'.LANG_PLAYERSTATS_POS.'">POS</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAMES.'">GP</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SOG.'">SOG</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SVS.'">SVS</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SVP.'">SV%</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GA.'">GA</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAA.'">GAA</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SO.'">SO</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                </tr>
              </thead>
              <tbody>';

		$p=1;
while ($t = mysqli_fetch_array($r))
      {
  if($t["svsp"]==NULL) $t["svsp"]=0;
  if($t["gaa"]==NULL) $t["gaa"]=0;
	$svp = round($t["svsp"],1);
  $gaa = round($t["gaa"],2);
  
	if($t["injury"]!=NULL) $injury = ' <i class="fas fa-user-injured text-danger align-self-center" data-toggle="tooltip" data-placement="top" data-html="true" title="'.LANG_PLAYERS_INJURED.': <b>'.$t["injury"].'</b>"></i>';
	else $injury = '';
  
	$content .= '<tr>
                  <td class="text-center" style="width:2%;">'.$p.'.</td>
                  <td style="width:11%;" class="text-nowrap"><img class="flag-el '.$t["teamshort"].'-small" src="/images/blank.png" alt="'.$t["teamshort"].'"> '.$t["teamshort"].'</td>
                  <td class="d-flex justify-content-between text-nowrap"><a href="/goalie/'.$t["id"].'1-'.SEOTitle($t["name"]).'">'.$t["name"].'</a>'.$injury.'</td>
                  <td class="text-center" style="width:6%;">G</td>
                  <td class="text-center" style="width:7%;">'.$t["gp"].'</td>
                  <td class="text-center" style="width:7%;">'.$t["sog"].'</td>
                  <td class="text-center" style="width:7%;">'.$t["svs"].'</td>
                  <td class="text-center font-weight-bold" style="width:7%;">'.$svp.'</td>
                  <td class="text-center" style="width:7%;">'.$t["ga"].'</td>
                  <td class="text-center" style="width:7%;">'.$gaa.'</td>
                  <td class="text-center" style="width:7%;">'.$t["so"].'</td>
                  <td class="text-center" style="width:7%;">'.$t["pim"].'</td>
                </tr>';
      $p++;
      }
$content .= '</tbody></table>
            </div>
           </div>';
    }

    // najnovsie videa
    if($f["topic_title"]=="NHL") {
        array_walk($in, function(&$i, $k) { $i = "'$k'"; });
        $in = implode(",", $in);
        $lv = mysqli_query($link, "SELECT pv.*, m.datetime, m.team1short, m.team1long, m.team2short, m.team2long, g.teamshort, g.goaler, g.asister1, g.asister2, g.status, g.kedy FROM `player_videos` pv LEFT JOIN el_matches m ON m.id=pv.match_id LEFT JOIN el_goals g ON g.id=pv.goal_id WHERE pv.name IN (".$in.") && pv.league='".$lid."' ORDER BY m.datetime DESC, goal_id DESC LIMIT 8");
        if(mysqli_num_rows($lv)>0) {
            $content .= '<div class="card my-4 shadow animated--grow-in">
                            <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                                Najnovšie videá
                            </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">';
            while($lvi = mysqli_fetch_array($lv)) {
                $status = explode(":",$lvi["status"]);
                if($lvi["teamshort"]==$lvi["team1short"]) {
                    $against = $lvi["team2long"];
                    $goals = "<b>&#91;".$status[0]."&#93;</b>:".$status[1];
                }
                else {
                    $against = $lvi["team1long"];
                    $goals = $status[0].":<b>&#91;".$status[1]."&#93;</b>";
                }
                if($lvi["name"]==$lvi["goaler"]) $hl = "dal gól";
                elseif($lvi["name"]==$lvi["asister1"] || $lvi["name"]==$lvi["asister2"]) $hl = "asistoval pri góle";
                $content .= "<div class='col-6 col-sm-4 col-lg-3 d-flex flex-column'><a class='flex-fill text-decoration-none' data-toggle='modal' data-target='#videoModal' data-url='".$lvi["link"]."' onclick='setVideoUrl(this)' href='#'>".(isset($lvi["image_url"]) ? "<img src='data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 1 0.563\"%3E%3C/svg%3E' data-src='".$lvi["image_url"]."' class='lazy img-thumbnail shadow-sm'>":"<div class='align-items-center d-flex h-100 img-thumbnail justify-content-center shadow-sm'><i class='fa-2x fa-image fas text-gray-300'></i></div>")."</a><p class='px-2 py-1 m-0 small text-muted'>".time_elapsed_string($lvi["datetime"])."</p><p class='px-2 pb-2 m-0 p-fluid'><b>".$lvi["name"]."</b> ".$hl." na ".$goals." proti tímu <b>".$against."</b></p></div>";
            }
                $content .= '
                                </div>
                            </div>
                        </div>';
        }
    }
$content .= '</div> <!-- end col -->
        <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block mt-4">
            '.$advert.'
        </div> <!-- end col -->
        </div> <!-- end row -->';
  }
// zraneni hraci
elseif(isset($iid))
  {
  $q = mysqli_query($link, "SELECT dt.topic_title, 2004leagues.color, 2004leagues.longname FROM 2004leagues JOIN (SELECT * FROM e_xoops_topics)dt ON 2004leagues.topic_id=dt.topic_id WHERE id='$iid'");
  $f = mysqli_fetch_array($q);
  $lid = $iid;
  $leaguecolor = $f["color"];
  $active_league = $lid;
  $title = LANG_PLAYERS_INJUREDTITLE." ".$f["topic_title"];
  
  $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($f["longname"])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERS_INJUREDTITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$f["longname"]."</h2>
               <div style='max-width: 1000px;'>";
  
	$content .= '<div class="card my-4 shadow animated--grow-in">
              <div class="card-header">
                <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                  '.LANG_TEAMSTATS_PLAYERS.'
                  <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                </h6>
              </div>
              <div class="card-body">
                  <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="injured">
                  <thead><tr>
                    <th class="text-center" style="width:2%;">#</th>
                    <th style="width:11%;">'.LANG_PLAYERSTATS_TEAM.'</th>
                    <th style="width:25%;">'.LANG_TEAMSTATS_NAME.'</th>
                    <th class="text-center" style="width:12%;">'.LANG_PLAYERSTATS_POS.'</th>
                    <th style="width:30%;">'.LANG_PLAYERS_INJURY.'</th>
                    <th style="width:20%;">'.LANG_PLAYERS_INJUREDSINCE.'</th>
                </tr>
              </thead>
              <tbody>';

  $r = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
	$r = mysqli_query($link, "SELECT i.*, et.id as pid, et.pos as pos, max(d.msg_date) as date_from FROM el_injuries i JOIN(SELECT name, id, pos FROM el_players WHERE league='$lid' GROUP BY name)et ON i.name=et.name LEFT JOIN 2004playerdiary d ON d.name=i.name && d.msg_type='7' WHERE i.league='$lid' GROUP BY i.name ORDER BY i.teamshort ASC, et.name ASC");
		
		$p=1;
while ($t = mysqli_fetch_array($r))
      {
	
	$content .= ' <tr>
                  <td class="text-center" style="width:2%;">'.$p.'.</td>
                  <td style="width:11%;" class="text-nowrap"><img class="flag-el '.$t["teamshort"].'-small" src="/images/blank.png" alt="'.$t["teamshort"].'"> '.$t["teamshort"].'</td>
                  <td style="width:25%;" class="text-nowrap"><a href="/player/'.$t["pid"].'1-'.SEOTitle($t["name"]).'">'.$t["name"].'</a></td>
                  <td class="text-center" style="width:12%;">'.$t["pos"].'</td>
                  <td style="width:30%;" class="text-nowrap">'.$t["injury"].'</td>
                  <td style="width:20%;" class="text-nowrap">'.date("j.n.Y", strtotime($t["date_from"])).'</td>
                </tr>';
      $p++;
      }
$content .= "</tbody></table>
            </div>
           </div>
          </div>";
  }
// prestupy
elseif(isset($tid))
  {
  $q = mysqli_query($link, "SELECT dt.topic_title, 2004leagues.color, 2004leagues.longname FROM 2004leagues JOIN (SELECT * FROM e_xoops_topics)dt ON 2004leagues.topic_id=dt.topic_id WHERE id='$tid'");
  $f = mysqli_fetch_array($q);
  $lid = $tid;
  $leaguecolor = $f["color"];
  $active_league = $lid;
  $title = LANG_PLAYERS_TRANSFERSTITLE." ".$f["topic_title"];
  
  $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($f["longname"])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERS_TRANSFERSTITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$f["longname"]."</h2>
               <div class='row'>
                <div class='col-12' style='max-width: 1000px;'>";
  
	$content .= '<div class="card my-4 shadow animated--grow-in">
              <div class="card-header">
                <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                  '.LANG_TEAMSTATS_LATESTTRANSFERS.'
                  <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                </h6>
              </div>
              <div class="card-body">
                  <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="transfers">
                  <thead><tr>
                    <th style="width:15%;">'.LANG_DATE.'</th>
                    <th style="width:25%;">'.LANG_TEAMSTATS_NAME.'</th>
                    <th style="width:28%;">'.LANG_TEAMSTATS_FROMTEAM.'</th>
                    <th style="width:4%;"></th>
                    <th style="width:28%;">'.LANG_TEAMSTATS_TOTEAM.'</th>
                </tr>
              </thead>
              <tbody>';

	$r = mysqli_query($link, "SELECT tr.* FROM el_teams t LEFT JOIN transfers tr ON tr.from_team=t.shortname OR tr.to_team=t.shortname WHERE t.league='".$lid."' GROUP BY tr.pname, tr.from_team, tr.to_team ORDER BY datetime DESC LIMIT 50");
		
		$p=1;
while ($t = mysqli_fetch_array($r))
      {
      $datum = date("j.n.Y", strtotime($t["datetime"]));
      if(strtotime($t["datetime"])==mktime(0,0,0)) $datum='dnes';
      if(strtotime($t["datetime"])==mktime(0,0,0,date("n"),date("j")-1)) $datum='včera';
      if($t["status"]=="0" && $t["to_name"]=="") $t["to_name"]=LANG_TEAMSTATS_FREEAGENT;
      if($t["pid"]!=NULL) {
        if($t["goalie"]==0) $pl = mysqli_query($link, "SELECT name FROM ".($t["el"]==1 ? 'el_players':'2004players')." WHERE id='".$t["pid"]."'");
        else $pl = mysqli_query($link, "SELECT name FROM ".($t["el"]==1 ? 'el_goalies':'2004goalies')." WHERE id='".$t["pid"]."'");
        $player = mysqli_fetch_array($pl);
        if(!isset($player)) $player["name"]="";
        if($t["goalie"]==0) $url = '/player/'.$t["pid"].$t["el"].'-'.SEOtitle($player["name"]);
        else $url = '/goalie/'.$t["pid"].$t["el"].'-'.SEOtitle($player["name"]);
      }
      else $player["name"] = $t["pname"];
	$content .= ' <tr>
                  <td style="width:15%;">'.$datum.'</td>
                  <td style="width:25%;" class="text-nowrap">'.($t["pid"]!=NULL ? '<a href="'.$url.'">'.$player["name"].'</a>':$player["name"]).'</td>
                  <td style="width:28%;" class="text-nowrap">'.($t["from_image"]!="" ? '<img src="'.$t["from_image"].'" style="height:16px; vertical-align: -3px;" alt="'.$t["from_name"].'"> ':'').''.$t["from_name"].'</td>
                  <td class="text-center" style="width:4%;"><i class="fas fa-angle-double-right text-success"></i></td>
                  <td style="width:28%;" class="text-nowrap">'.($t["to_image"]!="" ? '<img src="'.$t["to_image"].'" style="height:16px; vertical-align: -3px;" alt="'.$t["to_name"].'"> ':'').''.$t["to_name"].'</td>
                </tr>';
      $p++;
      }
$content .= '</tbody></table>
            </div>
           </div>
        </div> <!-- end col -->
        <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block mt-4">
            '.$advert.'
        </div> <!-- end col -->
        </div> <!-- end row -->';
  }
// statistika hraca
elseif(isset($pid))
  {
  $params[1] = $params[1] ?? "";
  if(strstr($params[1], "newdraft")) { session_unset($_SESSION['olddraft']); }
	$el = substr($pid, -1);
	$dl = strlen($pid);
	$ide = substr($pid, 0, $dl-1);
  if($el==1) $players_table = "el_players";
  else $players_table = "2004players";
  $q = mysqli_query($link, "SELECT p.*, l.color, l.longname FROM $players_table p LEFT JOIN 2004leagues l ON l.id=p.league WHERE p.id='$ide'");
  if(mysqli_num_rows($q)>0)
    {
    $comm_id = $pid;
    $data = mysqli_fetch_array($q);
    if($data["name"]=="MIKUŠ Juraj" || $data["name"]=="MIKÚŠ Juraj") 
      {
      //$coll = " COLLATE utf8_bin";
      if($data["name"]=="MIKUŠ Juraj") $coll = " && born='1988-11-30'";
      if($data["name"]=="MIKÚŠ Juraj") $coll = " && born='1987-02-22'";
      }
    else $coll="";

    $title = "Štatistika hráča ".$data["name"];
    $leaguecolor = $data["color"];
    $active_league = $data["league"];   
    $pinfo = GetBio($data["name"], 0);

    $content .= '
            <!-- Video modal -->
            <div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="videoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                    <div class="modal-content bg-gradient-secondary">
                        <div class="modal-header border-0 pb-0 pt-2">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="clearVideoUrl()">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe class="embed-responsive-item border-0" id="videoFrame" src="" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
     
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($data["longname"])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERSTATS_TITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".($data["jersey"]>0 ? '#'.$data["jersey"].' ' : '').$data["name"]."</h2>
                 <div class='row'>
                    <div class='col-12' style='max-width: 1000px;'>";
    
    //$draft = Show_Draft_Button($data["name"],$pid);
    
    $content .= '
                    <div class="player-info">
                      <div class="row">
                        <div class="col-auto mx-auto mx-md-0 mb-2 order-1 animated--fade-in">
                          <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$data["name"].'" class="lazy rounded-circle img-thumbnail shadow-sm mb-2 p-1" style="width:100px; height:100px; object-fit: cover; object-position: top;">
                        </div>
                        <div class="col-auto justify-content-center mx-auto mx-md-0 mb-2 card pl-0 pr-2 order-3 order-md-2 animated--fade-in border-left-'.$leaguecolor.'">
                          <ul class="m-1 small">
                      ';
                      $i=0;
                      while($i < count($pinfo))
                        {
                        $content .= '<li>'.$pinfo[$i].'</li>';
                        $i++;
                        }
        $content .= '</ul>
                        </div>';
        $pot = mysqli_query($link, "SELECT * FROM (SELECT p.id, p.teamshort, p.teamlong, p.name, potw.datetime, potw.g, potw.a, potw.el FROM el_players p LEFT JOIN potw ON potw.pid=p.id && potw.el=1 WHERE p.name='".$data["name"]."' && potw.datetime IS NOT NULL 
UNION
SELECT p.id, p.teamshort, p.teamlong, p.name, potw.datetime, potw.g, potw.a, potw.el FROM 2004players p LEFT JOIN potw ON potw.pid=p.id && potw.el=0 WHERE p.name='".$data["name"]."' && potw.datetime IS NOT NULL
ORDER BY datetime DESC LIMIT 1)dt WHERE dt.id IS NOT NULL");
        if(mysqli_num_rows($pot)>0)
            {
            $potw = mysqli_fetch_array($pot);
            if($potw["g"]=="") $potw["g"]=0;
            if($potw["a"]=="") $potw["a"]=0;
            $p = $potw["g"]+$potw["a"];
            if($p==1) $hl = LANG_GAMECONT_POINT;
            else if($p>1 && $p<5) $hl = LANG_GAMECONT_POINTS;
            else $hl = LANG_TEAMSTATS_PTS;
            $week = (int)date('W',strtotime($potw["datetime"]));
            $content .= '
                        <div class="col-auto justify-content-center mx-auto mx-md-3 mb-2 card pl-0 pr-2 order-3 order-md-2 animated--fade-in border-left-'.$leaguecolor.'">
                          <div class="row no-gutters align-items-center">
                            <div class="col m-2">
                                <div class="font-weight-bold text-'.$leaguecolor.' text-uppercase text-xs">'.LANG_PLAYERS_LASTPOTW.'</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-shield text-gray-300"></i>
                            </div>
                          </div>
                          <p class="mb-1 ml-2 small text-center">'.sprintf(LANG_PLAYERS_LASTPOTWTEXT, '<b>'.$week.date('. \týž\d\eň Y',strtotime($potw["datetime"])).'</b>', '<br><img class="'.$potw["teamshort"].'-small flag-'.($potw["el"]==0 ? 'iihf':'el').'" src="/img/blank.png" alt="'.$potw["teamlong"].'"><b>'.$potw["teamlong"].'</b>').'</p>
                          <p class="h5 text-center"><span class="badge badge-pill badge-'.$leaguecolor.'">'.$p.' '.$hl.' ('.$potw["g"].'G + '.$potw["a"].'A)</span></p>
                        </div>';
            }
      $content .= '   </div>';

    $w = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
    $w = mysqli_query($link, "SELECT 2004players.*, l.longname, t.id as tid, m.datetime as firstgame FROM 2004players JOIN 2004leagues l ON l.id=2004players.league JOIN 2004teams t ON t.shortname=2004players.teamshort && t.league=2004players.league LEFT JOIN 2004matches m ON m.league=2004players.league WHERE name='".$data["name"]."'".$coll." GROUP BY 2004players.league ORDER BY firstgame ASC");
    if(mysqli_num_rows($w)>0)
        {
        $name = mysqli_query($link, "SELECT sum(gp), sum(goals), sum(asists), sum(points), sum(penalty), sum(ppg), sum(shg), sum(gwg), sum(gtg) FROM 2004players WHERE name='".$data["name"]."'".$coll."");
        $sumar = mysqli_fetch_array($name);
        $content .= '
                      <div class="card my-4 shadow animated--grow-in">
                        <div class="card-header">
                          <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                            '.LANG_PLAYERSTATS_NATIONAL.'
                            <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                          </h6>
                        </div>
                        <div class="card-body">
                            <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="repre">
                            <thead><tr>
                              <th style="width:22%;">'.LANG_TEAMSTATS_LEAGUE.'</th>
                              <th style="width:22%;">'.LANG_PLAYERSTATS_TEAM.'</th>
                              <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAMES.'">GP</th>
                              <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GOALS.'">G</th>
                              <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_ASISTS.'">A</th>
                              <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_POINTS.'">P</th>
                              <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                              <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PPG.'">PPG</th>
                              <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SHG.'">SHG</th>
                              <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GWG.'">GWG</th>
                          </tr>
                        </thead>
                        <tbody>';
        while($f = mysqli_fetch_array($w))
          {
          $content .= '<tr>
              <td style="width:22%;"><a href="/games/'.$f["league"].'-'.SEOTitle($f["longname"]).'">'.$f["longname"].'</a></td>
              <td style="width:22%;"><a href="/team/'.$f["tid"].'0-'.SEOTitle($f["teamlong"]).'">'.$f["teamlong"].'</a></td>
              <td class="text-center" style="width:7%;">'.$f["gp"].'</td>
              <td class="text-center" style="width:7%;">'.$f["goals"].'</td>
              <td class="text-center" style="width:7%;">'.$f["asists"].'</td>
              <td class="text-center font-weight-bold" style="width:7%;">'.$f["points"].'</td>
              <td class="text-center" style="width:7%;">'.$f["penalty"].'</td>
              <td class="text-center" style="width:7%;">'.$f["ppg"].'</td>
              <td class="text-center" style="width:7%;">'.$f["shg"].'</td>
              <td class="text-center" style="width:7%;">'.$f["gwg"].'</td>
            </tr>';
          }
        $content .= '</tbody>
              <tfoot class="font-weight-bold">
                <tr>
                  <td colspan="2">'.LANG_BETS_OVERALL.'</td>
                  <td class="text-center" style="width:7%;">'.$sumar[0].'</td>
                  <td class="text-center" style="width:7%;">'.$sumar[1].'</td>
                  <td class="text-center" style="width:7%;">'.$sumar[2].'</td>
                  <td class="text-center" style="width:7%;">'.$sumar[3].'</td>
                  <td class="text-center" style="width:7%;">'.$sumar[4].'</td>
                  <td class="text-center" style="width:7%;">'.$sumar[5].'</td>
                  <td class="text-center" style="width:7%;">'.$sumar[6].'</td>
                  <td class="text-center" style="width:7%;">'.$sumar[7].'</td>
                </tr>
              </tfoot>
          </table>
                        </div>
                      </div>';
        }
                
    $w = mysqli_query($link, "SELECT el_players.*, l.longname, l.active, t.id as tid FROM el_players JOIN 2004leagues l ON l.id=el_players.league JOIN el_teams t ON t.shortname=el_players.teamshort && t.league=el_players.league WHERE name='".$data["name"]."'".$coll."
UNION
SELECT id_upper as id, NULL as teamshort, teamlong, name, 0 as jersey, NULL as pos, NULL as born, 'L' as hold, 0 as kg, 0 as cm, gp, goals, asists, goals+asists as points, 0 as penalty, 0 as gwg, 0 as gtg, 0 as ppg, 0 as shg, league_upper as league, CONCAT(league_name, ' ', season) as longname, 0 as active, 0 as tid FROM player_lowerleagues WHERE name='".$data["name"]."'
ORDER BY league ASC, id ASC");
    //$w = mysqli_query($link, "SELECT el_players.*, l.longname, l.active, t.id as tid FROM el_players JOIN 2004leagues l ON l.id=el_players.league JOIN el_teams t ON t.shortname=el_players.teamshort && t.league=el_players.league WHERE name='".$data["name"]."'".$coll." ORDER BY league ASC, el_players.id ASC");
    if(mysqli_num_rows($w)>0)
        {
        $name1 = mysqli_query($link, "SELECT sum(gp), sum(goals), sum(asists), sum(points), sum(penalty), sum(ppg), sum(shg), sum(gwg) FROM el_players WHERE name='".$data["name"]."'".$coll."");
        $sumar1 = mysqli_fetch_array($name1);
        $content .= '<div class="card my-4 shadow animated--grow-in">
              <div class="card-header">
                <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                  '.LANG_PLAYERSTATS_CLUB.'
                  <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                </h6>
              </div>
              <div class="card-body">
                  <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="club">
                  <thead><tr>
                    <th style="width:22%;">'.LANG_TEAMSTATS_SEASON.'</th>
                    <th style="width:22%;">'.LANG_PLAYERSTATS_TEAM.'</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAMES.'">GP</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GOALS.'">G</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_ASISTS.'">A</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_POINTS.'">P</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PPG.'">PPG</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SHG.'">SHG</th>
                    <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GWG.'">GWG</th>
                </tr>
              </thead>
              <tbody>';
        $cs=0;
        $teams_played = [];
        while($f = mysqli_fetch_array($w))
          {
          $content .= '<tr>
              <td style="width:22%;">'.($f["tid"]!=0 ? '<a href="/games/'.$f["league"].'-'.SEOTitle($f["longname"]).'">'.$f["longname"].'</a>':$f["longname"]).'</td>
              <td style="width:22%;">'.($f["tid"]!=0 ? '<a href="/team/'.$f["tid"].'1-'.SEOTitle($f["teamlong"]).'">'.$f["teamlong"].'</a>':$f["teamlong"]).'</td>
              <td class="text-center" style="width:7%;">'.$f["gp"].'</td>
              <td class="text-center" style="width:7%;">'.$f["goals"].'</td>
              <td class="text-center" style="width:7%;">'.$f["asists"].'</td>
              <td class="text-center font-weight-bold" style="width:7%;">'.$f["points"].'</td>
              <td class="text-center" style="width:7%;">'.($f["tid"]!=0 ? $f["penalty"]:'').'</td>
              <td class="text-center" style="width:7%;">'.($f["tid"]!=0 ? $f["ppg"]:'').'</td>
              <td class="text-center" style="width:7%;">'.($f["tid"]!=0 ? $f["shg"]:'').'</td>
              <td class="text-center" style="width:7%;">'.($f["tid"]!=0 ? $f["gwg"]:'').'</td>
            </tr>';
            if($f["active"]==1) {
                // hrá túto sezónu
                $current_league = $f["league"];
                $current_team = $f["teamshort"];
                $teams_played[] = $f["teamshort"];
                $cs++;
            }
          }
        if($cs>2) {
            // prestúpil 2x, nerobiť gamelog
            unset($current_league);
        }
        $content .= '</tbody>
          <tfoot class="font-weight-bold">
            <tr>
              <td colspan="2">'.LANG_BETS_OVERALL.'</td>
              <td class="text-center" style="width:7%;">'.$sumar1[0].'</td>
              <td class="text-center" style="width:7%;">'.$sumar1[1].'</td>
              <td class="text-center" style="width:7%;">'.$sumar1[2].'</td>
              <td class="text-center" style="width:7%;">'.$sumar1[3].'</td>
              <td class="text-center" style="width:7%;">'.$sumar1[4].'</td>
              <td class="text-center" style="width:7%;">'.$sumar1[5].'</td>
              <td class="text-center" style="width:7%;">'.$sumar1[6].'</td>
              <td class="text-center" style="width:7%;">'.$sumar1[7].'</td>
            </tr>
          </tfoot>
        </table>
       </div>
      </div>';
        }

    if(isset($current_league)) {
        if(count($teams_played)==2) {
            $transfer_name = explode(" ", $data["name"]);
            $transfer_name = $transfer_name[0]." ".$transfer_name[1];
            $tr = mysqli_query($link, "SELECT * FROM `transfers` WHERE pname='".$transfer_name."' && from_team='".$teams_played[0]."' && to_team='".$teams_played[1]."' GROUP BY from_team, to_team ORDER BY datetime DESC");
            if(mysqli_num_rows($tr)>0) {
                $transfer = mysqli_fetch_array($tr);
                $transfer_date = $transfer["datetime"];
            }
        }
        $injured_dates = [];
        $inj = mysqli_query($link, "SELECT d1.id AS injury_id, d1.name, d1.msg_date AS injury_date, d2.msg_date AS recovery_date FROM 2004playerdiary d1 JOIN 2004playerdiary d2 ON d1.name = d2.name AND d1.msg_type = 7 AND d2.msg_type = 9 AND d2.msg_date > d1.msg_date AND NOT EXISTS (SELECT 1 FROM 2004playerdiary d3 WHERE d3.name = d1.name AND d3.msg_type = 7 AND d3.msg_date > d1.msg_date AND d3.msg_date < d2.msg_date) WHERE d1.name = '".$data["name"]."' ORDER BY d1.msg_date;");
        if(mysqli_num_rows($inj)>0) {
            while($injur = mysqli_fetch_array($inj)) {
                $injured_dates[] = array($injur["injury_date"], $injur["recovery_date"]);
            }
        }
        $content .= '<div class="card my-4 shadow animated--grow-in gamelog">
              <div class="card-header">
                <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                  '.LANG_PLAYERSTATS_GAMELOG.'
                  <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                </h6>
              </div>
              <div class="card-body row">';
              $gamelog = [];
              if(!isset($transfer_date) && count($teams_played)==2) $teams_played = array($teams_played[1]);
              foreach($teams_played as $key => $current_team) {
                $gl = mysqli_query($link, "SELECT m.id, g.goaler, g.asister1, g.asister2 FROM `el_matches` m JOIN el_goals g ON g.matchno=m.id && g.teamshort='".$current_team."' && (g.goaler='".$data["name"]."' || g.asister1='".$data["name"]."' || g.asister2='".$data["name"]."') WHERE m.league='".$current_league."' && (m.team1short='".$current_team."' || m.team2short='".$current_team."')");
                while($glog = mysqli_fetch_array($gl)) {
                    //$g=$a=0;
                    $gid = $glog["id"];
                    if(!isset($gamelog[$gid]["g"])) $gamelog[$gid]["g"]=0;
                    if(!isset($gamelog[$gid]["a"])) $gamelog[$gid]["a"]=0;
                    if($glog["goaler"]==$data["name"]) $gamelog[$gid]["g"]=$gamelog[$gid]["g"]+1;
                    if($glog["asister1"]==$data["name"] || $glog["asister2"]==$data["name"]) $gamelog[$gid]["a"]=$gamelog[$gid]["a"]+1;
                }
                if(isset($transfer_date) && $key==0) $ts = mysqli_query($link, "SELECT id, kedy, IF(team1short='".$current_team."',team2short,team1short) as vsteam, datetime FROM `el_matches` WHERE league='".$current_league."' && (team1short='".$current_team."' || team2short='".$current_team."') && datetime<='".$transfer_date."' ORDER BY datetime");
                elseif(isset($transfer_date) && $key==1) $ts = mysqli_query($link, "SELECT id, kedy, IF(team1short='".$current_team."',team2short,team1short) as vsteam, datetime FROM `el_matches` WHERE league='".$current_league."' && (team1short='".$current_team."' || team2short='".$current_team."') && datetime>'".$transfer_date."' ORDER BY datetime");
                else $ts = mysqli_query($link, "SELECT id, kedy, IF(team1short='".$current_team."',team2short,team1short) as vsteam, datetime FROM `el_matches` WHERE league='".$current_league."' && (team1short='".$current_team."' || team2short='".$current_team."') ORDER BY datetime");
                    while ($f = mysqli_fetch_assoc($ts)) {
                        $gid = $f["id"];
                        $goals = str_repeat('<i class="fa-fw fa-hockey-puck fas text-gray-900"></i>', $gamelog[$gid]["g"] ?? 0);
                        $asists = str_repeat('<i class="fa-fw fa-a fas text-danger"></i>', $gamelog[$gid]["a"] ?? 0);
                        
                        $content .= '<div class="d-block position-relative game-holder' . ($f["kedy"] == "na programe" || (isDateInInjuredPeriod(date("Y-m-d", strtotime($f["datetime"])), $injured_dates) && $goals=="" && $asists=="") ? ' text-gray-400' : '') . '">
                            <div class="text-nowrap border border-left-0 border-right-0 vs-team">vs. ' . $f["vsteam"] . '</div>
                            <div class="text-center border border-top-0 pt-1 icons">' . $goals . $asists . '</div>
                        </div>';
                    }
                    if(isset($transfer_date) && $key==0) {
                        $content .= '
                        <div class="d-block position-relative game-holder">
                            <div class="pl-1 text-nowrap transfer-line">Prestup do '.$teams_played[1].'</div>
                        </div>';
                    }
              }
              $content .= '
              </div>
            </div>';
    }

    // najnovsie videa
    $lv = mysqli_query($link, "SELECT pv.*, m.datetime, m.team1short, m.team1long, m.team2short, m.team2long, g.teamshort, g.goaler, g.asister1, g.asister2, g.status, g.kedy FROM `player_videos` pv LEFT JOIN el_matches m ON m.id=pv.match_id LEFT JOIN el_goals g ON g.id=pv.goal_id WHERE pv.name='".$data["name"]."' ORDER BY m.datetime DESC LIMIT 8");
    if(mysqli_num_rows($lv)>0) {
        $content .= '<div class="card my-4 shadow animated--grow-in">
                        <div class="card-header">
                        <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                            Najnovšie videá
                        </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">';
        while($lvi = mysqli_fetch_array($lv)) {
            $status = explode(":",$lvi["status"]);
            if($lvi["teamshort"]==$lvi["team1short"]) {
                $against = $lvi["team2long"];
                $goals = "<b>&#91;".$status[0]."&#93;</b>:".$status[1];
            }
            else {
                $against = $lvi["team1long"];
                $goals = $status[0].":<b>&#91;".$status[1]."&#93;</b>";
            }
            if($lvi["name"]==$lvi["goaler"]) $hl = "dal gól";
            elseif($lvi["name"]==$lvi["asister1"] || $lvi["name"]==$lvi["asister2"]) $hl = "asistoval pri góle";
            $content .= "<div class='col-6 col-sm-4 col-lg-3'><a data-toggle='modal' data-target='#videoModal' data-url='".$lvi["link"]."' onclick='setVideoUrl(this)' href='#'>".(isset($lvi["image_url"]) ? "<img src='data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 1 0.563\"%3E%3C/svg%3E' data-src='".$lvi["image_url"]."' class='lazy img-thumbnail shadow-sm'>":"<div class='img-thumbnail shadow-sm'></div>")."</a><p class='p-2 m-0 p-fluid'>".time_elapsed_string($lvi["datetime"])." ".$hl." na ".$goals." proti tímu <b>".$against."</b></p></div>";
        }
            $content .= '
                            </div>
                        </div>
                    </div>';
    }
    
    if($data["name"]=="MIKUŠ Juraj" || $data["name"]=="MIKÚŠ Juraj") {
      $coll = " COLLATE utf8_bin";
    }
    else $coll = "";
    $h = mysqli_query($link, "SELECT * FROM 2004playerdiary WHERE name='".$data["name"]."'".$coll." ORDER BY msg_date DESC");
    if(mysqli_num_rows($h)>0)
      {
      $content .= '<div class="card my-4 shadow animated--grow-in">
                    <div class="card-header">
                      <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                        '.LANG_PLAYERSTATS_DIARY.'
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="timeline d-none d-md-block">
                        ';
                        $k = mysqli_query($link, "SELECT max(msg_date) as max, min(msg_date) as min, datediff(max(msg_date), min(msg_date)) as rozdiel FROM 2004playerdiary WHERE name='".$data["name"]."'".$coll."");
                        $l = mysqli_fetch_array($k);
                        $maxrok = date("Y", strtotime($l["max"]));
                        $minrok = date("Y", strtotime($l["min"]));
                        $rok = $minrok+1;
                        // timeline roky
                        $i=1;
                        while($i < ($maxrok-$minrok)+1)
                          {
                          $date1 = new DateTime($l["min"]);
                          $date2 = new DateTime('01-01-'.$rok);
                          $diff = $date2->diff($date1)->format("%a");
                          $pot = ($l["rozdiel"]!=0 ? round(($diff/$l["rozdiel"])*100,2):0);
                          $content .= '<div class="timeline-year text-nowrap" style="left: '.$pot.'%;">&nbsp;'.$rok.'</div>';
                          $rok++;
                          $i++;
                          }
                        // timeline eventy
                        $i=$injured=0;
                        while($j = mysqli_fetch_array($h))
                          {
                          $wid = '10px';
                          $col = 'primary';
                          $date1 = new DateTime($l["min"]);
                          $date2 = new DateTime($j["msg_date"]);

                          $diff = $date2->diff($date1)->format("%a");
                          $pot = ($l["rozdiel"]!=0 ? round(($diff/$l["rozdiel"])*100,2):0);
                          if($j["msg_type"]==9) 
                            {
                            $injured=1;
                            $date_healed=$j["msg_date"];
                            }
                          if($j["msg_type"]==7 && $injured==1)
                            {
                            $date_injured=$j["msg_date"];
                            $date1 = new DateTime($date_injured);
                            $date2 = new DateTime($date_healed);
                            $diff = $date2->diff($date1)->format("%a");
                            $wid = ($l["rozdiel"]!=0 ? round(($diff/$l["rozdiel"])*100,2).'%':'0%');
                            $injured=0;
                            }
                          if($j["msg_type"]==2 || $j["msg_type"]==1 || $j["msg_type"]==10) $col = 'success';
                          if($j["msg_type"]==3) $col = 'secondary';
                          if($j["msg_type"]==4 || $j["msg_type"]==8) $col = 'warning';
                          if($j["msg_type"]==7) $col = 'danger';
                          if($j["msg_type"]!=9) $content .= '<span class="timeline-event bg-'.$col.'" style="width: '.$wid.'; left: '.$pot.'%;" data-toggle="tooltip" data-placement="top" data-html="true" title="'.date("j.n.Y", strtotime($j["msg_date"])).' - '.$j["msg"].'"></span>';
                          $i++;
                          }
                      $content .= '</div>
                      <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="diary">
                      <thead>
                        <tr>
                          <th>'.LANG_DATE.'</th>
                          <th>'.LANG_TEAMSTATS_EVENT.'</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td colspan="2" class="dataTables_empty">'.LANG_STATS_LOADING.'</td>
                        </tr>
                      </tbody></table>
                    </div>
                   </div>';
                   
  $script_end = '<script type="text/javascript">
    
    $(document).ready(function() {
    $("#diary").dataTable( {
      "bProcessing": true,
      "bServerSide": true,
      "searching": false,
      "ordering": false,
      "oLanguage": { "sUrl": "/includes/lang/datatables_'.$locale.'.txt" },
      "bAutoWidth": false,
      "aoColumns": [{ "sWidth": "15%" }, { "sWidth": "85%" }],
      "sPaginationType": "numbers",
      "bJQueryUI": false,
      "sAjaxSource": "/includes/diary.php?name='.$data["name"].'"
    } );
  } );
          </script>';
      }
                   

                    $content .= GoogleNews("p",$pid);

                    $content .= '
                  <div class="card shadow my-4">
                      <div class="card-body">
                      '.GenerateComments(3,$data["name"]."p").'
                      </div>
                  </div>
                </div>';
      
    $content .= '   </div> <!-- end col -->
   <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block">
    '.$advert.'
   </div> <!-- end col -->
   </div> <!-- end row -->';
    }
  else
    {
    $leaguecolor = "hl";
    $content .= "<div class='alert alert-warning' role='alert'><i class='fas fa-skating'></i> Neexistujúci hráč</div>";
    }
  }
// statistika brankara
elseif(isset($gid))
  {
	$el = substr($gid, -1);
	$dl = strlen($gid);
	$ide = substr($gid, 0, $dl-1);
  if($el==1) $goalies_table = "el_goalies";
  else $goalies_table = "2004goalies";
  $q = mysqli_query($link, "SELECT g.*, l.color, l.longname FROM $goalies_table g LEFT JOIN 2004leagues l ON l.id=g.league WHERE g.id='$ide'");
  if(mysqli_num_rows($q)>0)
    {
    $comm_id = $gid;
    $data = mysqli_fetch_array($q);
    $data["jersey"] = $data["jersey"] ?? null;

    $title = "Štatistika brankára ".$data["name"];
    $leaguecolor = $data["color"];
    $active_league = $data["league"];
    $pinfo = GetBio($data["name"], 1);
    
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($data["longname"])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERSTATS_TITLEGOALIE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".($data["jersey"]>0 ? '#'.$data["jersey"].' ' : '').$data["name"]."</h2>
                 <div class='row'>
                    <div class='col-12' style='max-width: 1000px;'>";
    
    $content .= '<div class="player-info">
                  <div class="row">
                    <div class="col-auto mx-auto mx-md-0 mb-2 order-1 animated--fade-in">
                      <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$data["name"].'" class="lazy rounded-circle img-thumbnail shadow-sm mb-2 p-1" style="width:100px; height:100px; object-fit: cover; object-position: top;">
                    </div>
                    <div class="col-auto justify-content-center mx-auto mx-md-0 mb-2 card pl-0 pr-2 order-3 order-md-2 animated--fade-in border-left-'.$leaguecolor.'">
                      <ul class="m-1 small">
                      ';
                      $i=0;
                      while($i < count($pinfo))
                        {
                        $content .= '<li>'.$pinfo[$i].'</li>';
                        $i++;
                        }
        $content .= '</ul>
                    </div>
                </div>';
                
    $w = mysqli_query($link, "SELECT 2004goalies.*, l.longname, t.id as tid FROM 2004goalies JOIN 2004leagues l ON l.id=2004goalies.league JOIN 2004teams t ON t.shortname=2004goalies.teamshort && t.league=2004goalies.league WHERE name='".$data["name"]."' ORDER BY league ASC, 2004goalies.id ASC");
    if(mysqli_num_rows($w)>0)
        {
        $name = mysqli_query($link, "SELECT sum(gp), sum(sog), sum(svs), sum(ga), sum(so), sum(pim) FROM 2004goalies WHERE name='".$data["name"]."'");
        $sumar = mysqli_fetch_array($name);
        $content .= '<div class="card my-4 shadow animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                      '.LANG_PLAYERSTATS_NATIONAL.'
                      <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                    </h6>
                  </div>
                  <div class="card-body">
                      <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="club">
                      <thead><tr>
                        <th class="text-nowrap" style="width:22%;">'.LANG_TEAMSTATS_SEASON.'</th>
                        <th class="text-nowrap" style="width:22%;">'.LANG_PLAYERSTATS_TEAM.'</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAMES.'">GP</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SOG.'">SOG</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SVS.'">SVS</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SVP.'">SV%</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GA.'">GA</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAA.'">GAA</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SO.'">SO</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                    </tr>
                  </thead>
                  <tbody>';
        $i=$k=0;
        $svpp=0;
        $gaap=0;
        while($f = mysqli_fetch_array($w))
          {
          $svp = ($f["sog"]!=0 ? round(($f["svs"]/$f["sog"])*100,1):0);
          $gaa = ($f["gp"]!=0 ? round(($f["ga"]/$f["gp"]),2):0);
          $svpp = $svp+$svpp;
          $gaap = $gaa+$gaap;
          $content .= '<tr>
              <td class="text-nowrap" style="width:22%;"><a href="/games/'.$f["league"].'-'.SEOTitle($f["longname"]).'">'.$f["longname"].'</a></td>
              <td class="text-nowrap" style="width:22%;"><a href="/team/'.$f["tid"].'0-'.SEOTitle($f["teamlong"]).'">'.$f["teamlong"].'</a></td>
              <td class="text-center" style="width:7%;">'.$f["gp"].'</td>
              <td class="text-center" style="width:7%;">'.$f["sog"].'</td>
              <td class="text-center" style="width:7%;">'.$f["svs"].'</td>
              <td class="text-center font-weight-bold" style="width:7%;">'.$svp.'%</td>
              <td class="text-center" style="width:7%;">'.$f["ga"].'</td>
              <td class="text-center" style="width:7%;">'.$gaa.'</td>
              <td class="text-center" style="width:7%;">'.$f["so"].'</td>
              <td class="text-center" style="width:7%;">'.$f["pim"].'</td>
            </tr>';
          $i++;
          if($f["gp"]>0) $k++;
          }
        $content .= '</tbody>
            <tfoot class="font-weight-bold">
            <tr>
              <td colspan="2">'.LANG_BETS_OVERALL.'</td>
              <td class="text-center" style="width:7%;">'.$sumar[0].'</td>
              <td class="text-center" style="width:7%;">'.$sumar[1].'</td>
              <td class="text-center" style="width:7%;">'.$sumar[2].'</td>
              <td class="text-center" style="width:7%;">'.($k!=0 ? round($svpp/$k,1):0).'%</td>
              <td class="text-center" style="width:7%;">'.$sumar[3].'</td>
              <td class="text-center" style="width:7%;">'.($k!=0 ? round($gaap/$k,1):0).'</td>
              <td class="text-center" style="width:7%;">'.$sumar[4].'</td>
              <td class="text-center" style="width:7%;">'.$sumar[5].'</td>
            </tr>
          </tfoot>
          </table>
         </div>
       </div>';
        }
                
    $w = mysqli_query($link, "SELECT el_goalies.*, l.longname, t.id as tid FROM el_goalies JOIN 2004leagues l ON l.id=el_goalies.league JOIN el_teams t ON t.shortname=el_goalies.teamshort && t.league=el_goalies.league WHERE name='".$data["name"]."' ORDER BY league ASC, el_goalies.id ASC");
    if(mysqli_num_rows($w)>0)
        {
        $name = mysqli_query($link, "SELECT sum(gp), sum(sog), sum(svs), sum(ga), sum(so), sum(pim) FROM el_goalies WHERE name='".$data["name"]."'");
        $sumar = mysqli_fetch_array($name);
        $content .= '<div class="card my-4 shadow animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                      '.LANG_PLAYERSTATS_CLUB.'
                      <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                    </h6>
                  </div>
                  <div class="card-body">
                      <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="club">
                      <thead><tr>
                        <th class="text-nowrap" style="width:22%;">'.LANG_TEAMSTATS_SEASON.'</th>
                        <th class="text-nowrap" style="width:22%;">'.LANG_PLAYERSTATS_TEAM.'</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAMES.'">GP</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SOG.'">SOG</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SVS.'">SVS</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SVP.'">SV%</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GA.'">GA</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAA.'">GAA</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SO.'">SO</th>
                        <th class="text-center" style="width:7%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                    </tr>
                  </thead>
                  <tbody>';
        $i=$k=0;
        $svpp=0;
        $gaap=0;
        while($f = mysqli_fetch_array($w))
          {
          $svp = ($f["sog"]!=0 ? round(($f["svs"]/$f["sog"])*100,1):0);
          $gaa = ($f["gp"]!=0 ? round(($f["ga"]/$f["gp"]),2):0);
          $svpp = $svp+$svpp;
          $gaap = $gaa+$gaap;
          $content .= '<tr>
              <td class="text-nowrap" style="width:22%;"><a href="/games/'.$f["league"].'-'.SEOTitle($f["longname"]).'">'.$f["longname"].'</a></td>
              <td class="text-nowrap" style="width:22%;"><a href="/team/'.$f["tid"].'1-'.SEOTitle($f["teamlong"]).'">'.$f["teamlong"].'</a></td>
              <td class="text-center" style="width:7%;">'.$f["gp"].'</td>
              <td class="text-center" style="width:7%;">'.$f["sog"].'</td>
              <td class="text-center" style="width:7%;">'.$f["svs"].'</td>
              <td class="text-center font-weight-bold" style="width:7%;">'.$svp.'%</td>
              <td class="text-center" style="width:7%;">'.$f["ga"].'</td>
              <td class="text-center" style="width:7%;">'.$gaa.'</td>
              <td class="text-center" style="width:7%;">'.$f["so"].'</td>
              <td class="text-center" style="width:7%;">'.$f["pim"].'</td>
            </tr>';
          $i++;
          if($f["gp"]>0) $k++;
          }
        $svpp = ($k!=0 ? round($svpp/$k,1):0);
        $gaap = ($k!=0 ? round($gaap/$k,1):0);
        $content .= '</tbody>
            <tfoot class="font-weight-bold">
            <tr>
              <td colspan="2">'.LANG_BETS_OVERALL.'</td>
              <td class="text-center" style="width:7%;">'.$sumar[0].'</td>
              <td class="text-center" style="width:7%;">'.$sumar[1].'</td>
              <td class="text-center" style="width:7%;">'.$sumar[2].'</td>
              <td class="text-center" style="width:7%;">'.$svpp.'%</td>
              <td class="text-center" style="width:7%;">'.$sumar[3].'</td>
              <td class="text-center" style="width:7%;">'.$gaap.'</td>
              <td class="text-center" style="width:7%;">'.$sumar[4].'</td>
              <td class="text-center" style="width:7%;">'.$sumar[5].'</td>
            </tr>
          </tfoot>
          </table>
         </div>
       </div>';
         }
   
   $content .= '
  </div>';
    $h = mysqli_query($link, "SELECT * FROM 2004playerdiary WHERE name='".$data["name"]."' ORDER BY msg_date DESC");
    if(mysqli_num_rows($h)>0)
      {
      $content .= '<div class="card my-4 shadow animated--grow-in">
                    <div class="card-header">
                      <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                        '.LANG_PLAYERSTATS_DIARY.'
                        <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                      </h6>
                    </div>
                    <div class="card-body">
                        <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="diary">
                        <thead>
                          <tr>
                            <th>'.LANG_DATE.'</th>
                            <th>'.LANG_TEAMSTATS_EVENT.'</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td colspan="2" class="dataTables_empty">'.LANG_STATS_LOADING.'</td>
                          </tr>
                        </tbody></table>
                      </div>
                    </div>';
          
$script_end = '<script type="text/javascript">
          
    $(document).ready(function() {
    $("#diary").dataTable( {
      "bProcessing": true,
      "bServerSide": true,
      "searching": false,
      "ordering": false,
      "oLanguage": { "sUrl": "/includes/lang/datatables_'.$locale.'.txt" },
      "bAutoWidth": false,
      "aoColumns": [{ "sWidth": "15%" }, { "sWidth": "85%" }],
      "sPaginationType": "numbers",
      "bJQueryUI": false,
      "sAjaxSource": "/includes/diary.php?name='.$data["name"].'"
    } );
  } );
          </script>';
      }
    $content .= 
                    GoogleNews("g",$gid).'

                    <div class="card shadow my-4">
                        <div class="card-body">
                        '.GenerateComments(3,$data["name"]."g").'
                        </div>
                    </div>
    </div> <!-- end col -->
   <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block">
    '.$advert.'
   </div> <!-- end col -->
   </div> <!-- end row -->';
    }
  else
    {
    $leaguecolor = "hl";
    $content .= "<div class='alert alert-warning' role='alert'><i class='fas fa-skating'></i> Neexistujúci brankár</div>";
    }
  }
// databaza hracov
elseif(isset($_GET["database"]))
  {
  $title = LANG_NAV_PLAYERDB;
  $add="";
  $leaguecolor = "hl";
  // manazer si chce zmenit draftovy vyber
  if(strstr($_GET["database"], 'newdraft'))
    {
    $id = explode("/", $_GET["database"]);
    $_SESSION["olddraft"]=$id[1];
    }
  $mena = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
  $content .= "<h1 class='h3 h3-fluid mb-1'>".LANG_NAV_PLAYERDB."</h1>
               <div style='max-width: 1000px;'>";
  $content .= '<nav aria-label="Database navigation">
                <ul class="pagination pagination-sm">';
  $i=0;
  while($i < count($mena))
    {
    $dis="";
    if($_GET["database"]==$mena[$i]) $content .= '<li class="page-item disabled">
                                                  <a class="page-link" href="#" tabindex="-1" aria-disabled="true">'.$mena[$i].'</a>
                                                </li>';
    else $content .= '<li class="page-item"><a class="page-link" href="/database/'.$mena[$i].'">'.$mena[$i].'</a></li>';
    $i++;
    }
  if($_GET["database"]!=1) 
    {
    if(strlen($_GET["database"])==1) $add='?vyb='.$_GET["database"];
    else $add='?tshort='.$_GET["database"];
    }
  $content .= '</ul>
              </nav>
    '.LANG_PLAYERDB_FILTER.': <select id="team" size="1" class="custom-select custom-select-sm w-auto"><optgroup label="Medzinárodné">';
        
    $i=0;
    $uloha = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
    $uloha = mysqli_query($link, "SELECT * FROM 2004teams WHERE longname NOT LIKE '%U20' GROUP BY longname ORDER BY longname ASC");
    while ($data = mysqli_fetch_array($uloha))
          {
          $content .= "<option value='".$data["shortname"]."'";
          if($_GET["database"]==$data["shortname"]) $content .= "selected"; 
          $content .= ">".$data["longname"]."</option>";
          $i++;
          }
    $content .= "</optgroup><optgroup label='Ligové'>";

    $i=0;
    $uloha = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
    $uloha = mysqli_query($link, "SELECT * FROM el_teams GROUP BY shortname ORDER BY longname ASC");
    while ($data = mysqli_fetch_array($uloha))
          {
          $content .= "<option value='".$data["shortname"]."'";
          if($_GET["database"]==$data["shortname"]) $content .= "selected"; 
          $content .= ">".$data["longname"]."</option>";
          $i++;
          }
    $content .= '</optgroup></select>
    
    <div class="card my-4 shadow animated--grow-in">
      <div class="card-body">
        <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="database">
          <thead>
            <tr>
              <th class="text-center" style="width:10%;">#</th>
              <th style="width:50%;">'.LANG_PLAYERDB_PLAYER.'</th>
              <th style="width:40%;">'.LANG_PLAYERDB_TEAMS.'</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td colspan="10" class="dataTables_empty">'.LANG_STATS_LOADING.'...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>';
  
$script_end = '<script type="text/javascript">
	$(document).ready(function() {
	$("#database").dataTable( {
		"bProcessing": true,
		"bServerSide": true,
		"pageLength": 25,
		"aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 ] }],
    "oLanguage": { "sUrl": "/includes/lang/datatables_'.$locale.'.txt" },
    "aaSorting": [[1, "asc"]],
    "bAutoWidth": false,
    "aoColumns": [{ "sWidth": "10%", className: "text-center" }, { "sWidth": "50%" }, { "sWidth": "40%" }],
		"sPaginationType": "numbers",
    "bJQueryUI": false,
		"sAjaxSource": "/includes/database.php'.$add.'",
		 "drawCallback": function( settings ) {
        $(\'[data-toggle="tooltip"]\').tooltip()
        }
	} );
	
  $("#team").on(\'change\', function() {
    var tshort = this.value;
    window.location.href = "/database/"+tshort;
  });
	
} );
</script>';
  }
// najlepsi strelci
elseif(isset($_GET["shooters"]))
  {
  $title = LANG_PLAYERS_SHOOTERSTITLE;
  $leaguecolor = "hl";

    if(!isset($_POST["league"])) {
        $_POST["ok"]=1;
        $m = mysqli_query($link, "SELECT * FROM 2004leagues WHERE el='1' && active='1' ORDER BY id ASC LIMIT 1");
        $n = mysqli_fetch_array($m);
        $_POST["league"]=$n["id"];
    }

    if(isset($_POST["ok"])) {
        $league = $_POST["league"];
        $p = mysqli_query($link, "SELECT * FROM 2004leagues WHERE id='$league'");
        $po = mysqli_fetch_array($p);

        $content .= "
               <i class='float-left h1 h1-fluid ll-".LeagueFont($po["longname"])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERS_SHOOTERSTITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$po["longname"]."</h2>
                <div class='row'>
                    <div class='col-12' style='max-width: 1000px;'>";

        // playoff
        if($po["endbasic"]==1) {
            $po_teams = "";
            $poteams = mysqli_query($link, "SELECT * FROM el_playoff WHERE played='0' && league='$league'");
            while($pot = mysqli_fetch_array($poteams)) {
                $po_teams .= "teamshort='".$pot["team1"]."' || teamshort='".$pot["team2"]."' || ";
            }
            $po_teams = substr($po_teams, 0, -4);
            if($po_teams=="") $po_teams="''";
            // v riadnom hracom case
            //$q = mysqli_query($link, "SELECT et.*, COUNT(et.goaler) as poc, DATE_FORMAT(et.datetime, '%e.%c.%Y') as datum, ft.injury FROM (SELECT el_goals.*, dt.datetime, IF(dt.team1short=el_goals.teamshort,dt.team1long,dt.team2long) as teamlong FROM el_goals JOIN (SELECT * FROM el_matches WHERE league='$league' && kedy='konečný stav' ORDER BY id ASC)dt ON dt.id=el_goals.matchno WHERE goaler!='' && time<'60.00' && ($po_teams) GROUP BY el_goals.goaler, el_goals.matchno ORDER BY datetime DESC)et LEFT JOIN(SELECT name, injury FROM el_injuries WHERE league='$league')ft ON et.goaler=ft.name WHERE ft.injury IS NULL GROUP BY et.goaler ORDER BY poc DESC LIMIT 20");
            $q = mysqli_query($link, "SELECT et.*, COUNT(et.goaler) as poc, DATE_FORMAT(et.datetime, '%e.%c.%Y') as datum, ft.injury FROM (SELECT el_goals.*, dt.datetime, IF(dt.team1short=el_goals.teamshort,dt.team1long,dt.team2long) as teamlong FROM el_goals JOIN (SELECT * FROM el_matches WHERE league='$league' && kedy='konečný stav' ORDER BY id ASC)dt ON dt.id=el_goals.matchno WHERE goaler!='' && (".$po_teams.") GROUP BY el_goals.goaler, el_goals.matchno ORDER BY datetime DESC)et LEFT JOIN(SELECT name, injury FROM el_injuries WHERE league='$league')ft ON et.goaler=ft.name WHERE ft.injury IS NULL GROUP BY et.goaler ORDER BY poc DESC LIMIT 20");
            $q1 = mysqli_query($link, "SELECT *, el_players.name as name, ft.injury FROM el_players LEFT JOIN(SELECT name, injury FROM el_injuries WHERE league='$league')ft ON el_players.name=ft.name WHERE ft.injury IS NULL && league='$league' && (".$po_teams.") GROUP BY el_players.name ORDER BY points DESC LIMIT 20");
        }
        // non-playoff
        else {
            // v riadnom hracom case
            //$q = mysqli_query($link, "SELECT et.*, COUNT(et.goaler) as poc, DATE_FORMAT(et.datetime, '%e.%c.%Y') as datum, ft.injury FROM (SELECT el_goals.*, dt.datetime, IF(dt.team1short=el_goals.teamshort,dt.team1long,dt.team2long) as teamlong FROM el_goals JOIN (SELECT * FROM el_matches WHERE league='$league' && kedy='konečný stav' ORDER BY id ASC)dt ON dt.id=el_goals.matchno WHERE goaler!='' && time<'60.00' GROUP BY el_goals.goaler, el_goals.matchno ORDER BY datetime DESC)et LEFT JOIN(SELECT name, injury FROM el_injuries WHERE league='$league')ft ON et.goaler=ft.name WHERE ft.injury IS NULL GROUP BY et.goaler ORDER BY poc DESC LIMIT 20");
            $q = mysqli_query($link, "SELECT et.*, COUNT(et.goaler) as poc, DATE_FORMAT(et.datetime, '%e.%c.%Y') as datum, ft.injury FROM (SELECT el_goals.*, dt.datetime, IF(dt.team1short=el_goals.teamshort,dt.team1long,dt.team2long) as teamlong FROM el_goals JOIN (SELECT * FROM el_matches WHERE league='$league' && kedy='konečný stav' ORDER BY id ASC)dt ON dt.id=el_goals.matchno WHERE goaler!='' GROUP BY el_goals.goaler, el_goals.matchno ORDER BY datetime DESC)et LEFT JOIN(SELECT name, injury FROM el_injuries WHERE league='$league')ft ON et.goaler=ft.name WHERE ft.injury IS NULL GROUP BY et.goaler ORDER BY poc DESC LIMIT 20");
            $q1 = mysqli_query($link, "SELECT *, el_players.name as name, ft.injury FROM el_players LEFT JOIN(SELECT name, injury FROM el_injuries WHERE league='$league')ft ON el_players.name=ft.name WHERE ft.injury IS NULL && league='$league' GROUP BY el_players.name ORDER BY points DESC LIMIT 20");
        }
        
        $e = mysqli_query($link, "SELECT DATE_FORMAT(tstamp, '%e.%c.%Y o %k:%i') as datum FROM el_matches WHERE league='$league' ORDER BY tstamp DESC LIMIT 1");
        $r = mysqli_fetch_array($e);
        
        $dat = explode(" o ",$r["datum"]);
        if($dat[0]==date("j.n.Y")) $r["datum"]="dnes o $dat[1]";
        if($dat[0]==date("j.n.Y", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")))) $r["datum"]="včera o $dat[1]";

        $content .= "
    <div class='alert alert-info'>
        <p class='p-fluid'><i class='fas fa-question-circle mr-2'></i>".LANG_PLAYERS_SHOOTERSTEXT1."</p>
        <!--p class='p-fluid'>".LANG_PLAYERS_SHOOTERSTEXT2."</p-->
        <p class='text-xs m-0'>".LANG_PLAYERS_NOTES.":<br>
            <hr class='mt-0'>
            <ul class='text-xs'>
                <li>".sprintf(LANG_PLAYERS_NOTE1, "<b>".$r["datum"]."</b>")."</li>
                <!--li>".LANG_PLAYERS_NOTE2."</li-->
            </ul>
        </p>
    </div>

    <form name='form' method='post' action='/shooters' enctype='multipart/form-data' class='text-center mb-4'>".LANG_BETS_SHOWFOR.": 
        <select name='league' size='1' class='custom-select custom-select-sm w-auto ml-2'>";
        $uloha1 = mysqli_query($link, "SELECT * FROM 2004leagues WHERE el='1' && active='1' ORDER BY id ASC");
        while($data1 = mysqli_fetch_array($uloha1)) {
            if($data1["id"]==$_POST["league"]) $sel=" selected";
            else $sel="";
            $content .= "<option value='".$data1["id"]."'".$sel.">".$data1["longname"]."</option>";
        }
        $content .= "
        </select>
        <input type='submit' name='ok' value='OK' class='btn btn-primary btn-sm'>
    </form>

    <div class='card my-4 shadow animated--grow-in'>
        <div class='card-header'>
            <h6 class='m-0 font-weight-bold text-".$leaguecolor."'>
                ".LANG_PLAYERS_BESTSHOOTERS."
                <span class='swipe d-none float-right text-gray-800'><i class='fas fa-hand-point-up'></i> <i class='fas fa-exchange-alt align-text-top text-xs'></i></span>
            </h6>
        </div>
        <div class='card-body p-fluid'>

        <ul class='nav nav-tabs' id='myTab' role='tablist'>
            <li class='nav-item'>
                <a class='nav-link active' id='goals-tab' data-toggle='tab' href='#goals-panel' role='tab' aria-controls='goals' aria-selected='true'>".LANG_TEAMSTATS_GOALS."</a>
            </li>
            <li class='nav-item'>
                <a class='nav-link' id='points-tab' data-toggle='tab' href='#points-panel' role='tab' aria-controls='points' aria-selected='false'>".LANG_PLAYERS_CANPOINTS."</a>
            </li>
        </ul>

        <div class='tab-content' id='myTabContent'>
            <div class='tab-pane fade show active' id='goals-panel' role='tabpanel' aria-labelledby='goals-tab'>
                <table class='table table-striped table-hover table-sm table-responsive-sm w-100 border'>
                    <thead>
                        <tr>
                            <th class='mdl-data-table__cell--non-numeric'>".LANG_PLAYERSTATS_TEAM."</th>
                            <th class='mdl-data-table__cell--non-numeric'>".LANG_PLAYERDB_PLAYER."</th>
                            <th class='mdl-data-table__cell--non-numeric'>".LANG_PLAYERS_SCORED."</th>
                            <th class='mdl-data-table__cell--non-numeric'>".LANG_PLAYERS_LASTTIME."</th>
                            <th class='mdl-data-table__cell--non-numeric'>".LANG_MATCHES_STREAK."</th>
                            <!--th class='mdl-data-table__cell--numeric'>".LANG_PLAYERS_RATE."</th-->
                        </tr>
                    </thead>
                    <tbody>";

  $kurzy = json_decode(file_get_contents('strelci_kurzy.json'),true);
  
  $i=0;
  while($f = mysqli_fetch_array($q))
    {
    $j=0;
    $dal=$nedal=0;
    $times = mysqli_query($link, "SELECT * FROM el_matches WHERE league='$league' && kedy='konečný stav' && (team1short='".$f["teamshort"]."' || team2short='".$f["teamshort"]."') ORDER BY datetime DESC");
    $kol = mysqli_num_rows($times);
    while($j < mysqli_num_rows($times))
      {
      $w = mysqli_fetch_array($times);
      //$jetam = mysqli_query($link, "SELECT * FROM el_goals WHERE matchno='".$w["id"]."' && goaler='".$f["goaler"]."' && time<'60.00' LIMIT 1");
      $jetam = mysqli_query($link, "SELECT * FROM el_goals WHERE matchno='".$w["id"]."' && goaler='".$f["goaler"]."' LIMIT 1");
      if(mysqli_num_rows($jetam)>0) 
         {
         if($j==0) $dal++;
         else 
            {
            if($dal>0) $dal++;
            else break;
            }
         }
      else
         {
         if($j==0) $nedal++;
         else 
            {
            if($nedal>0) $nedal++;
            else break;
            }
         }
      $j++;
      }
    if($dal > $nedal) $hl = sprintf(LANG_PLAYERS_SCOREDTIMES, $dal);
    if($nedal > $dal) 
      {
      if($nedal > 3) $hl = "<font color='red'><b>".sprintf(LANG_PLAYERS_NOTSCOREDTIMES, $nedal)."</b></font>";
      else $hl = sprintf(LANG_PLAYERS_NOTSCOREDTIMES, $nedal);
      }
    if($f["datum"]==date("j.n.Y")) $f["datum"]="<b>".LANG_TIME_TODAY."</b>";
    if($f["datum"]==date("j.n.Y", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")))) $f["datum"]="<b>".LANG_TIME_YESTERDAY."</b>";
    if($kol==0) $pom=0;
    else $pom = round(($f["poc"]/$kol)*100,1);
    
    // prehladanie kurzov zo suboru
    $odd="";
    $meno = explode(" ",$f["goaler"]);
    $pname = "g ".mb_convert_case($meno[0]." ".substr($meno[1],0,1), MB_CASE_LOWER, "UTF-8");
    $pname1 = "g ".mb_convert_case($f["goaler"], MB_CASE_LOWER, "UTF-8");
    if(array_key_exists($pname,$kurzy))
      {
      $odd = number_format($kurzy[$pname], 2, '.', '');
      }
    if(array_key_exists($pname1,$kurzy))
      {
      $odd = number_format($kurzy[$pname1], 2, '.', '');
      }

    $content .= "<tr>
                    <td class='mdl-data-table__cell--non-numeric'><img class='flag-el ".$f["teamshort"]."-small' src='/images/blank.png' alt='".$f["teamlong"]."'> ".$f["teamlong"]."</td>
                    <td class='mdl-data-table__cell--non-numeric'>".$f["goaler"]."</td>
                    <td class='mdl-data-table__cell--non-numeric'>".$f["poc"]."/".$kol." (".$pom."%)</td>
                    <td class='mdl-data-table__cell--non-numeric'>".$f["datum"]."</td>
                    <td class='mdl-data-table__cell--non-numeric'>".$hl."</td>
                    <!--td class='mdl-data-table__cell--numeric'>".$odd."</td-->
                </tr>";
    $i++;
    } 

    $content .= "</tbody>
    </table>
  </div>

  <div class='tab-pane fade' id='points-panel' role='tabpanel' aria-labelledby='points-tab'>
     <table class='table table-striped table-hover table-sm table-responsive-sm w-100 border'>
        <thead>
            <tr>
                <th class='mdl-data-table__cell--non-numeric'>".LANG_PLAYERSTATS_TEAM."</th>
                <th class='mdl-data-table__cell--non-numeric'>".LANG_PLAYERDB_PLAYER."</th>
                <th class='mdl-data-table__cell--non-numeric'>".LANG_PLAYERS_POINTS."</th>
                <th class='mdl-data-table__cell--non-numeric'>".LANG_PLAYERS_LASTTIME."</th>
                <th class='mdl-data-table__cell--non-numeric'>".LANG_MATCHES_STREAK."</th>
                <th class='mdl-data-table__cell--numeric'>".LANG_PLAYERS_RATE."</th>
            </tr>
        </thead>
        <tbody>";
 
  $i=0;
  while($p = mysqli_fetch_array($q1))
    {
    $j=0;
    $dal=$nedal=0;
    $hl="";
    $times = mysqli_query($link, "SELECT * FROM el_matches WHERE league='$league' && kedy='konečný stav' && (team1short='".$p["teamshort"]."' || team2short='".$p["teamshort"]."') ORDER BY datetime DESC");
    $kol = mysqli_num_rows($times);
    while($j < mysqli_num_rows($times))
      {
      $w = mysqli_fetch_array($times);
      //$jetam = mysqli_query($link, "SELECT * FROM el_goals WHERE matchno='$w[id]' && (goaler='$p[name]' || asister1='$p[name]' || asister2='$p[name]') && time<'60.00' LIMIT 1");
      $jetam = mysqli_query($link, "SELECT * FROM el_goals WHERE matchno='".$w["id"]."' && (goaler='".$p["name"]."' || asister1='".$p["name"]."' || asister2='".$p["name"]."') LIMIT 1");
      if(mysqli_num_rows($jetam)>0) 
         {
         if($j==0) $dal++;
         else 
            {
            if($dal>0) $dal++;
            else break;
            }
         }
      else
         {
         if($j==0) $nedal++;
         else 
            {
            if($nedal>0) $nedal++;
            else break;
            }
         }
      $j++;
      }
    if($dal > $nedal) $hl = sprintf(LANG_PLAYERS_POINTSTIMES, $dal);
    if($nedal > $dal) 
      {
      if($nedal > 3) $hl = "<font color='red'><b>".sprintf(LANG_PLAYERS_NOTPOINTSTIMES, $nedal)."</b></font>";
      else $hl = sprintf(LANG_PLAYERS_NOTPOINTSTIMES, $nedal);
      }
    $p["datum"]=$p["datum"] ?? NULL;
    if($p["datum"]==date("j.n.Y")) $p["datum"]="<b>".LANG_TIME_TODAY."</b>";
    if($p["datum"]==date("j.n.Y", mktime(0, 0, 0, date("m"), date("d")-1, date("Y")))) $p["datum"]="<b>".LANG_TIME_YESTERDAY."</b>";
    if($kol==0) $pom=0;
    else $pom = round(($p["points"]/$kol)*100,1);
    
    // prehladanie kurzov zo suboru
    $odd="";
    $meno = explode(" ",$p["name"]);
    $pname = "p ".mb_convert_case($meno[0]." ".substr($meno[1],0,1), MB_CASE_LOWER, "UTF-8");
    $pname1 = "p ".mb_convert_case($p["name"], MB_CASE_LOWER, "UTF-8");
    if(array_key_exists($pname,$kurzy))
      {
      $odd = number_format($kurzy[$pname], 2, '.', '');
      }
    if(array_key_exists($pname1,$kurzy))
      {
      $odd = number_format($kurzy[$pname1], 2, '.', '');
      }

    $content .= "<tr>
                    <td class='mdl-data-table__cell--non-numeric'><img class='flag-el ".$p["teamshort"]."-small' src='/images/blank.png' alt='".$p["teamlong"]."'> ".$p["teamlong"]."</td>
                    <td class='mdl-data-table__cell--non-numeric'>".$p["name"]."</td>
                    <td class='mdl-data-table__cell--non-numeric'>".$p["points"]."/".$kol." (".$pom."%)</td>
                    <td class='mdl-data-table__cell--non-numeric'>".$p["datum"]."</td>
                    <td class='mdl-data-table__cell--non-numeric'>".$hl."</td>
                    <td class='mdl-data-table__cell--numeric'>".$odd."</td>
                </tr>";
    $i++;
    } 

    $content .=  "</tbody>
    </table>
  </div>
</div>
	
    </div> <!-- end card-body -->
    </div> <!-- end card -->";
    }

    $content .= '  
    </div> <!-- end col -->
    <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block">
        '.$advert.'
    </div> <!-- end col -->
    </div> <!-- end row -->';
  }
// sledovaní hráči
elseif(isset($_GET["watched"])) {
  $title = LANG_PLAYERS_WATCHEDTITLE;
  $leaguecolor = "hl";
  if(isset($_SESSION['logged'])) {
    $q = mysqli_query($link, "SELECT * FROM e_xoops_users WHERE uid='".$_SESSION['logged']."'");
    $f = mysqli_fetch_array($q);
    if($f["user_avatar"]!="") $avatar = '/images/user_avatars/'.$_SESSION['logged'].'.'.$f["user_avatar"]."?".filemtime('images/user_avatars/'.$_SESSION['logged'].'.'.$f["user_avatar"]);
    else $avatar = '/img/players/no_photo.jpg';

    $content .= '<!-- Delete watched player modal -->
      <div class="modal fade" id="deletePlayer" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">'.LANG_PLAYERS_DELETEWATCHED.'</h5>
              <button class="close" type="button" data-dismiss="modal" aria-label="'.LANG_CLOSE.'">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="modal-body">
                '.LANG_PLAYERS_WATCHEDREALLY.'
                <input type="hidden" id="delpid">
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" type="button" data-dismiss="modal">'.LANG_CANCEL.'</button>
              <button class="btn btn-hl deletePlayerOK">'.LANG_REMOVE.'</button>
            </div>
          </div>
        </div>
      </div>';

    $content .= "<div id='toasts' class='fixed-top' style='top: 80px; right: 23px; left: initial; z-index:3;'></div>
                 <img class='float-left img-profile img-thumbnail mr-2 rounded-circle' src='".$avatar."' style='width: 55px;'>
                 <h1 class='h3 h3-fluid mb-1'>".$title."</h1>
                 <h2 class='h6 h6-fluid text-hl text-uppercase font-weight-bold mb-3'>".$f["uname"]."</h2>
                 <div class='row mb-4'>
                    <div class='col-12' style='max-width: 1000px;'>

                        <form class='form-inline'>
                        <div class='form-group'>
                            <label for='addPlayer'>".LANG_PLAYERS_ADDWATCHED."</label>
                            <div class='input-group'>
                                <input type='text' id='addPlayer' class='form-control ml-2' placeholder='".LANG_FANTASY_PICKPLACEHOLDER."' aria-label='Search' autocomplete='off'>
                                <input type='hidden' id='addPlayerId'>
                                <div class='input-group-append'>
                                <button class='btn btn-success' id='addPlayerOK' type='button' aria-label='".LANG_PLAYERS_SEARCHPLAYER."...'>
                                    <i class='fas fa-plus fa-sm'></i>
                                </button>
                                </div>
                            </div>
                        </div>
                        </form>";

                    if($f["user_favplayers"]!=NULL) {
                        $json = json_decode($f["user_favplayers"], true);
                        $content .= "
                      <div class='card my-4 shadow animated--grow-in'>
                        <div class='card-header'>
                          <h6 class='m-0 font-weight-bold text-".$leaguecolor."'>
                            ".LANG_PLAYERS_MYWATCHED."
                            <span class='badge badge-pill badge-secondary ml-2' style='vertical-align: 2px;'>".count($json)."</span>
                          </h6>
                        </div>
                        <div class='card-body'>
                          <div class='row'>
                            <div class='col-12 d-block d-lg-none p-fluid'>
                              <p>Zoradiť podľa: 
                                <span data-sort='points' class='sort-option badge badge-pill badge-secondary' style='vertical-align: 2px;'>bodov</span>
                                <span data-sort='lastgame' class='sort-option badge badge-pill badge-secondary' style='vertical-align: 2px;'>posledného zápasu</span>
                                <span data-sort='nextgame' class='sort-option badge badge-pill badge-secondary' style='vertical-align: 2px;'>najbližšieho zápasu</span>
                                <span data-sort='diaryts' class='sort-option badge badge-pill badge-secondary' style='vertical-align: 2px;'>záznamu v denníku</span>
                              </p>
                            </div>
                            <div class='col-4 d-none d-lg-block'></div>
                            <div class='col-8 d-none d-lg-block text-right text-gray-400'>
                              <div class='row'>
                                <div class='col-6 col-lg-3'>
                                  <i class='fa-solid fa-arrow-down-wide-short' data-sort='points'></i>
                                </div>
                                <div class='col-6 col-lg-3'>
                                  <i class='fa-solid fa-arrow-down-wide-short' data-sort='lastgame'></i>
                                </div>
                                <div class='col-6 col-lg-3'>
                                  <i class='fa-solid fa-arrow-down-wide-short' data-sort='nextgame'></i>
                                </div>
                                <div class='col-6 col-lg-3'>
                                  <i class='fa-solid fa-arrow-down-wide-short' data-sort='diaryts'></i>
                                </div>
                              </div>
                            </div>
                          </div>
                          <div id='playersContainer' class='p-fluid'>";
                        
                        $i=0;
                        foreach($json as $player) {
                          $pname = $player;
                          $curr = mysqli_query($link, "SELECT dt.* FROM (SELECT p.id, 0 as el, p.teamshort, p.league, m.datetime FROM 2004players p LEFT JOIN 2004matches m ON m.id=(SELECT id FROM 2004matches mm WHERE (mm.team1short=p.teamshort || mm.team2short=p.teamshort) && mm.datetime>NOW() && mm.league=p.league LIMIT 1) WHERE p.name='".$player."'
UNION
SELECT p1.id, 1 as el, p1.teamshort, p1.league, m1.datetime FROM el_players p1 LEFT JOIN el_matches m1 ON m1.id=(SELECT id FROM el_matches mm1 WHERE (mm1.team1short=p1.teamshort || mm1.team2short=p1.teamshort) && mm1.datetime>NOW() && mm1.league=p1.league LIMIT 1) WHERE p1.name='".$player."')dt
WHERE dt.datetime IS NOT NULL
ORDER BY datetime ASC, id DESC LIMIT 1");
                        if(mysqli_num_rows($curr)==0) {
                            $curr = mysqli_query($link, "SELECT p.id, 0 as el, p.teamshort, p.league FROM 2004players p WHERE p.name='".$player."'
UNION
SELECT p1.id, 1 as el, p1.teamshort, p1.league FROM el_players p1 WHERE p1.name='".$player."'
ORDER BY id DESC LIMIT 1");
                        }
                        $player = mysqli_fetch_array($curr);
                          $id = $player["id"];
                          $el = $player["el"];
                          if($el==0) {
                            $players_table = "2004players";
                            $matches_table = "2004matches";
                          }
                          else {
                            $players_table = "el_players";
                            $matches_table = "el_matches";
                          }
                          $w = mysqli_query($link, "SELECT p.*, i.injury, l.longname, m.datetime, m.team1short, m.team1long, m.team2short, m.team2long, pd.msg, pd.msg_date FROM ".$players_table." p LEFT JOIN el_injuries i ON i.name=p.name LEFT JOIN 2004leagues l ON l.id=p.league LEFT JOIN ".$matches_table." m ON m.id=(SELECT id FROM ".$matches_table." mm WHERE (mm.team1short=p.teamshort || mm.team2short=p.teamshort) && mm.datetime>NOW() && mm.league=p.league LIMIT 1) LEFT JOIN 2004playerdiary pd ON pd.id=(SELECT id FROM 2004playerdiary pdi WHERE pdi.name=p.name ORDER BY pdi.msg_date DESC LIMIT 1) WHERE p.id='".$id."'");
                          $e = mysqli_fetch_array($w);
                          if($e["injury"]!=NULL) $injury = ' <i class="fas fa-user-injured text-danger" data-toggle="tooltip" data-placement="top" data-html="true" title="'.LANG_PLAYERS_INJURED.': <b>'.$e["injury"].'</b>"></i>';
                          else $injury = '';

                          if($e["datetime"]!=NULL && $injury=='') {
                            $timestamp = strtotime($e["datetime"]);
                            $now = strtotime('today');
                            $tomorrow = strtotime('tomorrow');
                            $dayAfterTomorrow = strtotime('+2 days', $now);
                            $kedy = ($timestamp >= $now && $timestamp < $tomorrow) ? "<b>".LANG_TIME_TODAY."</b> ".LANG_AT." ".date('G:i', $timestamp) : (($timestamp >= $tomorrow && $timestamp < $dayAfterTomorrow) ? "<b>".LANG_TIME_TOMORROW."</b> ".LANG_AT." ".date('G:i', $timestamp) : (($timestamp >= $dayAfterTomorrow && $timestamp < strtotime('+3 days', $now)) ? "<b>".LANG_TIME_DAYAFTERTOMORROW."</b> ".LANG_AT." ".date('G:i', $timestamp) : date('d.m. \o G:i', $timestamp)));
                            $kedy .= " ".LANG_PLAYERS_AGAINST." ".($e["team1short"]==$e["teamshort"] ? $e["team2long"]:$e["team1long"]);
                          }
                          elseif($injury!='') {
                            $timestamp = 0;
                            $kedy="?";
                          }
                          else {
                            $timestamp = 0;
                            $kedy="-";
                          }

                          if($e["msg"]!=NULL) {
                            $diaryts = strtotime($e["msg_date"]);
                            $kedydiary = "<span class='badge badge-pill badge-secondary'>".time_elapsed_string($e["msg_date"])."</span>";
                            $diary = $e["msg"]."<br>".$kedydiary;
                          }
                          else {
                            $diaryts = 0;
                            $diary = "-";
                          }

                          $ppg = ($e["gp"]!=0 ? round($e["points"]/$e["gp"], 2):0);
                          if($ppg<=0.5 && $ppg!=0) $streak='<i class="fa-snowflake fas mr-1" style="color: #a8e0f9 !important;"></i>';
                          elseif($ppg>=1) $streak='<i class="fa-fire fas mr-1" style="color: #ffbb0e !important;"></i>';
                          else $streak='';

                          $lg = mysqli_query($link, "SELECT ph.*, m.team1short, m.team1long, m.team2short, m.team2long, m.datetime FROM `player_history` ph LEFT JOIN el_matches m ON m.id=ph.mid WHERE ph.name='".$pname."' ORDER BY id DESC LIMIT 1");
                          if(mysqli_num_rows($lg)>0) {
                              $last = mysqli_fetch_array($lg);
                              if($last["toi"]!=0) {
                                $toim = floor($last["toi"]/60);
                                $tois = $last["toi"]%60;
                                if($tois<10) $tois = "0".$tois;
                                $toi = ' · <span class="badge badge-pill badge-secondary" data-toggle="tooltip" data-placement="top" title="'.LANG_PLAYERS_TOI.'">TOI '.$toim.':'.$tois.'</span>';
                              }
                              else $toi = '';
                              $lastg = '<p class="m-0 small mt-2">'.time_elapsed_string($last["datetime"]).' '.LANG_PLAYERS_AGAINST.' <span class="font-italic">'.($last["team1short"]==$e["teamshort"] ? '<abbr title="'.$last["team2long"].'" class="initialism">'.$last["team2short"].'</abbr>':'<abbr title="'.$last["team1long"].'" class="initialism">'.$last["team1short"].'</abbr>').'</span></p>';
                              $lastg .= '<p class="align-content-center flex-fill font-weight-bold m-0" style="font-size: 1.75rem;">'.$last["g"].'+'.$last["a"].'</p>';
                              $lastg .= '<p class="m-0 small"><span class="badge badge-pill badge-secondary" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">'.$last["pim"].' PIM</span> · <span class="badge badge-pill badge-secondary" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SOG.'">'.$last["sog"].' SOG</span>'.$toi.'</p>';
                              $lastp = $last["g"]+$last["a"];
                          }
                          else {
                            $lastg = "-";
                            $lastp = 0;
                          }
                          $content .= '
                              <div class="row border mb-2 rounded playerContainer'.($i%2==0 ? '':' bg-light').'" data-name="'.$pname.'" data-points="'.$e["points"].'" data-lastgame="'.$lastp.'" data-nextgame="'.$timestamp.'" data-diaryts="'.$diaryts.'">
                                <div class="col-12 col-md-6 col-lg-4 align-items-center d-flex flex-column flex-sm-row py-2">
                                        <button class="btn btn-light btn-sm position-absolute" style="color: #aeaeae; top: 0; right: 0; padding: .1rem .4rem;" data-player="'.$pname.'" data-toggle="modal" data-target="#deletePlayer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <div>
                                            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$e["name"].'" class="lazy rounded-circle img-thumbnail shadow-sm mr-sm-2 mb-2 mb-md-0" style="width:75px; height:75px; object-fit: cover; object-position: top; min-width:75px;">
                                        </div>
                                        <div class="text-center text-sm-left">
                                            <p class="m-0 font-weight-bold"><a href="/player/'.$id.$el.'-'.SEOTitle($e["name"]).'">'.$e["name"].'</a>'.$injury.'</p>
                                            <p class="m-0 font-weight-bold small">'.$e["longname"].' · '.$e["teamlong"].'</p>
                                        </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-8 bg-gray-200">
                                    <div class="row">
                                        <div class="col-6 col-lg-3 d-flex flex-column text-center py-1 border-light border-right">
                                            <p class="m-0 font-weight-bold text-xs">'.LANG_PLAYERS_SUMMARYSTATS.':</p>
                                            <p class="align-content-center flex-fill font-weight-bold m-0" style="font-size: 1.75rem;">'.$e["points"].'<span class="font-italic font-weight-light ml-1" style="font-size: 1rem;">'.($e["points"]>4 || $e["points"]==0 ? LANG_TEAMSTATS_PTS:($e["points"]>1 ? LANG_GAMECONT_POINTS:LANG_GAMECONT_POINT)).'</span></p>
                                            <p class="m-0 small"><span class="badge badge-pill badge-dark">'.$e["goals"].'G + '.$e["asists"].'A</span> · <span class="badge badge-pill badge-secondary">'.$e["gp"].' '.($e["gp"]>4 || $e["gp"]==0 ? LANG_MATCH4:($e["gp"]>1 ? LANG_MATCH3:LANG_MATCH2)).'</span> · <span class="badge badge-pill badge-secondary" data-toggle="tooltip" data-placement="top" title="'.LANG_PLAYERS_PPG.'">'.$streak.$ppg.' PPG</span></p>
                                        </div>
                                        <div class="col-6 col-lg-3 d-flex flex-column text-center py-1">
                                            <p class="m-0 font-weight-bold text-xs">'.LANG_PLAYERS_LASTGAME.':</p>
                                            '.$lastg.'
                                        </div>
                                        <div class="col-6 col-lg-3 bg-gray-300 text-center pb-1 pt-2 pt-md-1 border-light border-right">
                                            <p class="m-0 font-weight-bold text-xs">'.LANG_PLAYERS_NEXTGAME.':</p>
                                            <p class="m-0 small mt-2">'.$kedy.'</p>
                                        </div>
                                        <div class="col-6 col-lg-3 bg-gray-300 text-center pb-1 pt-2 pt-md-1">
                                            <p class="m-0 font-weight-bold text-xs">'.LANG_PLAYERS_LASTDIARY.':</p>
                                            <p class="m-0 small mt-2">'.$diary.'</p>
                                        </div>
                                    </div>

                                </div>
                              </div>
                          ';
                          $i++;
                        }
    $content .= "
                        </div>
                      </div>
                    </div>";

                    }
    $content .= "                             
                    </div>
                 </div>";
  }
  else {
    $content .= "<h1 class='h3 h3-fluid mb-4'>".$title."</h1>
                 <div class='row mb-4'>
                    <div class='col-12' style='max-width: 1000px;'>
                        <img src='/images/watched.png' alt='".$title."' class='col-12 col-sm-5 col-xl-4 float-left img-thumbnail mr-3 mb-2 p-1'>
                        <p class='lead'>".LANG_PLAYERS_WATCHEDTEASER."</p>
                        <p>".LANG_PLAYERS_WATCHEDTEXT."</p>
                    </div>
                </div>";
  }
}
// nebol vybrany ziaden hrac
else
  {
  $leaguecolor = "hl";
  $content .= "<div class='alert alert-warning' role='alert'><i class='fas fa-skating'></i> Neexistujúci hráč</div>";
  }
?>