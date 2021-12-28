<?
if($_GET[pid]) 
  {
  $params = explode("/", htmlspecialchars($_GET[pid]));
  $pid = explode("-", htmlspecialchars($params[0]));
  $pid=$pid[0];
  }
if($_GET[gid]) 
  {
  $params = explode("/", htmlspecialchars($_GET[gid]));
  $gid = explode("-", htmlspecialchars($params[0]));
  $gid=$gid[0];
  }
if($_GET[slovaks]) 
  {
  $params = explode("/", htmlspecialchars($_GET[slovaks]));
  $sid = explode("-", htmlspecialchars($params[0]));
  $sid=$sid[0];
  }
if($_GET[injured]) 
  {
  $params = explode("/", htmlspecialchars($_GET[injured]));
  $iid = explode("-", htmlspecialchars($params[0]));
  $iid=$iid[0];
  }
if($_GET[transfers]) 
  {
  $params = explode("/", htmlspecialchars($_GET[transfers]));
  $tid = explode("-", htmlspecialchars($params[0]));
  $tid=$tid[0];
  }
  
$locale = explode(";",setlocale(LC_ALL, '0'));
$locale = explode("=",$locale[0]);
$locale = $locale[1];

$content = "";
// slovaci v KHL a NHL
if($sid)
  {
  $q = mysql_query("SELECT dt.topic_title, 2004leagues.color, 2004leagues.longname FROM 2004leagues JOIN (SELECT * FROM e_xoops_topics)dt ON 2004leagues.topic_id=dt.topic_id WHERE id='$sid'");
  $f = mysql_fetch_array($q);
  $lid = $sid;
  $leaguecolor = $f[color];
  $active_league = $lid;
  if($f[topic_title]=="KHL") include("includes/slovaki.php"); 
  else include("includes/slovaks.php");
  $title = LANG_PLAYERS_SLOVAKSTITLE." ".$f[topic_title];
   
  $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($f[longname])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERS_SLOVAKSTITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$f[longname]."</h2>
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
  array_walk($slovaks, create_function('&$i,$k','$i="\'$k\'";'));
  $slovaks = implode($slovaks,",");
  
  $r = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
	$r = mysql_query("SELECT dt.id,dt.name,dt.teamshort,dt.pos, sum(dt.gp) as gp, sum(dt.goals) as goals, sum(dt.asists) as asists, sum(dt.points) as points, sum(dt.gwg) as gwg, sum(dt.gtg) as gtg, sum(dt.shg) as shg, sum(dt.ppg) as ppg, sum(dt.penalty) as penalty, et.injury FROM (SELECT * FROM el_players WHERE name IN ($slovaks) && league='$lid' ORDER BY id DESC)dt LEFT JOIN(SELECT name, injury FROM el_injuries WHERE league='$lid')et ON dt.name=et.name GROUP BY dt.name ORDER BY points DESC, gp ASC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC");
		
		$p=1;
while ($t = mysql_fetch_array($r))
      {

	if($t[injury]!=NULL) $injury = ' <i class="fas fa-user-injured text-danger" data-toggle="tooltip" data-placement="top" data-html="true" title="'.LANG_PLAYERS_INJURED.': <b>'.$t[injury].'</b>"></i>';
	else $injury = '';
	
	$content .= '<tr>
                  <td class="text-center" style="width:2%;">'.$p.'.</td>
                  <td style="width:11%;" class="text-nowrap"><img class="flag-el '.$t[teamshort].'-small" src="/images/blank.png" alt="'.$t[teamshort].'"> '.$t[teamshort].'</td>
                  <td style="width:25%;" class="text-nowrap"><a href="/player/'.$t[id].'1-'.SEOTitle($t[name]).'">'.$t[name].'</a>'.$injury.'</td>
                  <td class="text-center" style="width:6%;">'.$t[pos].'</td>
                  <td class="text-center" style="width:7%;">'.$t[gp].'</td>
                  <td class="text-center" style="width:7%;">'.$t[goals].'</td>
                  <td class="text-center" style="width:7%;">'.$t[asists].'</td>
                  <td class="text-center font-weight-bold" style="width:7%;">'.$t[points].'</td>
                  <td class="text-center" style="width:7%;">'.$t[penalty].'</td>
                  <td class="text-center" style="width:7%;">'.$t[ppg].'</td>
                  <td class="text-center" style="width:7%;">'.$t[shg].'</td>
                  <td class="text-center" style="width:7%;">'.$t[gwg].'</td>
                </tr>';
      $p++;
      }
$content .= "</tbody></table>
            </div>
           </div>";

  array_walk($brankari, create_function('&$i,$k','$i="\'$k\'";'));
  $brankari = implode($brankari,",");

$r = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
$r = mysql_query("SELECT id, el_goalies.name, teamshort, sum(gp) as gp, sum(sog) as sog, sum(svs) as svs, sum(ga) as ga, sum(so) as so, sum(pim) as pim, (sum(svs)/sum(sog))*100 as svsp, sum(ga)/sum(gp) as gaa, et.injury FROM el_goalies LEFT JOIN(SELECT name, injury FROM el_injuries WHERE league='$lid')et ON el_goalies.name=et.name WHERE el_goalies.name IN ($brankari) && league='$lid' GROUP BY name ORDER BY svsp DESC, gaa ASC");

if(mysql_num_rows($r)>0)
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
while ($t = mysql_fetch_array($r))
      {
	
	$svp = round($t[svsp],1);
  $gaa = round($t[gaa],2);
  
	if($t[injury]!=NULL) $injury = ' <i class="fas fa-user-injured text-danger" data-toggle="tooltip" data-placement="top" data-html="true" title="'.LANG_PLAYERS_INJURED.': <b>'.$t[injury].'</b>"></i>';
	else $injury = '';
  
	$content .= '<tr>
                  <td class="text-center" style="width:2%;">'.$p.'.</td>
                  <td style="width:11%;" class="text-nowrap"><img class="flag-el '.$t[teamshort].'-small" src="/images/blank.png" alt="'.$t[teamshort].'"> '.$t[teamshort].'</td>
                  <td style="width:25%;" class="text-nowrap"><a href="/goalie/'.$t[id].'-'.SEOTitle($t[name]).'">'.$t[name].'</a>'.$injury.'</td>
                  <td class="text-center" style="width:6%;">G</td>
                  <td class="text-center" style="width:7%;">'.$t[gp].'</td>
                  <td class="text-center" style="width:7%;">'.$t[sog].'</td>
                  <td class="text-center" style="width:7%;">'.$t[svs].'</td>
                  <td class="text-center font-weight-bold" style="width:7%;">'.$svp.'</td>
                  <td class="text-center" style="width:7%;">'.$t[ga].'</td>
                  <td class="text-center" style="width:7%;">'.$gaa.'</td>
                  <td class="text-center" style="width:7%;">'.$t[so].'</td>
                  <td class="text-center" style="width:7%;">'.$t[pim].'</td>
                </tr>';
      $p++;
      }
$content .= '</tbody></table>
            </div>
           </div>
        </div> <!-- end col -->
        <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block mt-4">
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8860983069832222"
                    crossorigin="anonymous"></script>
                <!-- HL reklama na podstránkach XL zariadenie -->
                <ins class="adsbygoogle"
                    style="display:block"
                    data-ad-client="ca-pub-8860983069832222"
                    data-ad-slot="3044717777"
                    data-ad-format="auto"
                    data-full-width-responsive="true"></ins>
                <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
        </div> <!-- end col -->
        </div> <!-- end row -->';
    }
  }
// zraneni hraci
elseif($iid)
  {
  $q = mysql_query("SELECT dt.topic_title, 2004leagues.color, 2004leagues.longname FROM 2004leagues JOIN (SELECT * FROM e_xoops_topics)dt ON 2004leagues.topic_id=dt.topic_id WHERE id='$iid'");
  $f = mysql_fetch_array($q);
  $lid = $iid;
  $leaguecolor = $f[color];
  $active_league = $lid;
  $title = LANG_PLAYERS_INJUREDTITLE." ".$f[topic_title];
  
  $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($f[longname])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERS_INJUREDTITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$f[longname]."</h2>
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
                    <th style="width:50%;">'.LANG_PLAYERS_INJURY.'</th>
                </tr>
              </thead>
              <tbody>';

  $r = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
	$r = mysql_query("SELECT *, et.id as pid, et.pos as pos FROM el_injuries JOIN(SELECT name, id, pos FROM el_players WHERE league='$lid' GROUP BY name)et ON el_injuries.name=et.name WHERE league='$lid' ORDER BY teamshort ASC, et.name ASC");
		
		$p=1;
while ($t = mysql_fetch_array($r))
      {
	
	$content .= ' <tr>
                  <td class="text-center" style="width:2%;">'.$p.'.</td>
                  <td style="width:11%;" class="text-nowrap"><img class="flag-el '.$t[teamshort].'-small" src="/images/blank.png" alt="'.$t[teamshort].'"> '.$t[teamshort].'</td>
                  <td style="width:25%;" class="text-nowrap"><a href="/player/'.$t[id].'1-'.SEOTitle($t[name]).'">'.$t[name].'</a></td>
                  <td class="text-center" style="width:12%;">'.$t[pos].'</td>
                  <td style="width:50%;" class="text-nowrap">'.$t[injury].'</td>
                </tr>';
      $p++;
      }
$content .= "</tbody></table>
            </div>
           </div>
          </div>";
  }
// prestupy
elseif($tid)
  {
  $q = mysql_query("SELECT dt.topic_title, 2004leagues.color, 2004leagues.longname FROM 2004leagues JOIN (SELECT * FROM e_xoops_topics)dt ON 2004leagues.topic_id=dt.topic_id WHERE id='$tid'");
  $f = mysql_fetch_array($q);
  $lid = $tid;
  $leaguecolor = $f[color];
  $active_league = $lid;
  $title = LANG_PLAYERS_TRANSFERSTITLE." ".$f[topic_title];
  
  $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($f[longname])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERS_TRANSFERSTITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$f[longname]."</h2>
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

	$r = mysql_query("SELECT tr.* FROM el_teams t LEFT JOIN transfers tr ON tr.from_team=t.shortname OR tr.to_team=t.shortname WHERE t.league='".$lid."' GROUP BY tr.pname, tr.from_team, tr.to_team ORDER BY datetime DESC LIMIT 50");
		
		$p=1;
while ($t = mysql_fetch_array($r))
      {
      $datum = date("j.n.Y", strtotime($t["datetime"]));
      if(strtotime($t["datetime"])==mktime(0,0,0)) $datum='dnes';
      if(strtotime($t["datetime"])==mktime(0,0,0,date("n"),date("j")-1)) $datum='včera';
      if($t["status"]=="0" && $t["to_name"]=="") $t["to_name"]=LANG_TEAMSTATS_FREEAGENT;
      if($t["pid"]!=NULL) {
        if($t["goalie"]==0) $pl = mysql_query("SELECT name FROM el_players WHERE id='".$t["pid"]."'");
        else $pl = mysql_query("SELECT name FROM el_goalies WHERE id='".$t["pid"]."'");
        $player = mysql_fetch_array($pl);
        if($t["goalie"]==0) $url = '/player/'.$t["pid"].'1-'.SEOtitle($player["name"]);
        else $url = '/goalie/'.$t["pid"].'-'.SEOtitle($player["name"]);
      }
      else $player["name"] = $t["pname"];
	$content .= ' <tr>
                  <td style="width:15%;">'.$datum.'</td>
                  <td style="width:25%;" class="text-nowrap">'.($t["pid"]!=NULL ? '<a href="'.$url.'">'.$player["name"].'</a>':$player["name"]).'</td>
                  <td style="width:28%;" class="text-nowrap">'.($t["from_image"]!="" ? '<img src="'.$t["from_image"].'" style="height:16px; vertical-align: -3px;"> ':'').''.$t["from_name"].'</td>
                  <td class="text-center" style="width:4%;"><i class="fas fa-angle-double-right text-success"></i></td>
                  <td style="width:28%;" class="text-nowrap">'.($t["to_image"]!="" ? '<img src="'.$t["to_image"].'" style="height:16px; vertical-align: -3px;"> ':'').''.$t["to_name"].'</td>
                </tr>';
      $p++;
      }
$content .= '</tbody></table>
            </div>
           </div>
        </div> <!-- end col -->
        <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block mt-4">
                <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8860983069832222"
                    crossorigin="anonymous"></script>
                <!-- HL reklama na podstránkach XL zariadenie -->
                <ins class="adsbygoogle"
                    style="display:block"
                    data-ad-client="ca-pub-8860983069832222"
                    data-ad-slot="3044717777"
                    data-ad-format="auto"
                    data-full-width-responsive="true"></ins>
                <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
        </div> <!-- end col -->
        </div> <!-- end row -->';
  }
// statistika hraca
elseif($pid)
  {
  if(strstr($params[1], "newdraft")) { session_unset($_SESSION['olddraft']); }
	$el = substr($pid, -1);
	$dl = strlen($pid);
	$ide = substr($pid, 0, $dl-1);
  if($el==1) $players_table = "el_players";
  else $players_table = "2004players";
  $q = mysql_query("SELECT p.*, l.color, l.longname FROM $players_table p LEFT JOIN 2004leagues l ON l.id=p.league WHERE p.id='$ide'");
  if(mysql_num_rows($q)>0)
    {
    $comm_id = $pid;
    $data = mysql_fetch_array($q);
    if($data[name]=="MIKUŠ Juraj" || $data[name]=="MIKÚŠ Juraj") 
      {
      $coll = " COLLATE utf8_bin";
      }
    else $coll="";
    $elinf = mysql_query("SELECT name, max(pos) as pos, max(born) as born, max(hold) as hold, max(kg) as kg, max(cm) as cm FROM el_players WHERE name='$data[name]'$coll ORDER BY id DESC LIMIT 1");
    $elinfo = mysql_fetch_array($elinf);
    if($elinfo[name]==NULL)
      {
      $elinf = mysql_query("SELECT name, max(pos) as pos, max(born) as born, max(hold) as hold, max(kg) as kg, max(cm) as cm FROM 2004players WHERE name='$data[name]'$coll ORDER BY id DESC LIMIT 1");
      $elinfo = mysql_fetch_array($elinf);
      }
    $title = "Štatistika hráča ".$data[name];
    $leaguecolor = $data[color];
    $active_league = $data[league];
    if($elinfo[pos]=="F") $hl=LANG_PLAYERSTATS_F;
    elseif($elinfo[pos]=="LW") $hl=LANG_PLAYERSTATS_LW;
    elseif($elinfo[pos]=="RW") $hl=LANG_PLAYERSTATS_RW;
    elseif($elinfo[pos]=="C" || $elinfo[pos]=="CE") $hl=LANG_PLAYERSTATS_C;
    elseif($elinfo[pos]=="D") $hl=LANG_PLAYERSTATS_D;
    elseif($elinfo[pos]=="LD") $hl=LANG_PLAYERSTATS_LD;
    elseif($elinfo[pos]=="RD") $hl=LANG_PLAYERSTATS_RD;
    elseif($elinfo[pos]=="GK" || $elinfo[pos]=="G") $hl=LANG_PLAYERSTATS_GK;
    if($elinfo[hold]=="L") $hl1=LANG_PLAYERSTATS_LHOLD;
    else $hl1=LANG_PLAYERSTATS_RHOLD;
    $pinfo = array();
    if($elinfo[pos] && $elinfo[pos]!="") $pinfo[] = $hl;
    if($elinfo[born] && $elinfo[born]!="1970-01-01") $pinfo[] = date_diff(date_create($elinfo[born]), date_create('today'))->y.' rokov';
    if($elinfo[cm] && $elinfo[cm]!=0) $pinfo[] = $elinfo[cm].' cm';
    if($elinfo[kg] && $elinfo[kg]!=0) $pinfo[] = $elinfo[kg].' kg';
    if($elinfo[hold] && $elinfo[hold]!="") $pinfo[] = $hl1;
     
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($data[longname])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERSTATS_TITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".($data[jersey]>0 ? '#'.$data[jersey].' ' : '').$data[name]."</h2>
                 <div class='row'>
                    <div class='col-12' style='max-width: 1000px;'>";
    
    //$draft = Show_Draft_Button($data[name],$pid);
    
    $content .= '
    <div class="player-info">
                  <div class="row">
                    <div class="col-auto mx-auto mx-md-0 mb-2 order-1 animated--fade-in">
                      <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$data[name].'" class="lazy rounded-circle img-thumbnail shadow-sm mb-2 p-1" style="width:100px; height:100px; object-fit: cover; object-position: top;">
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
        $pot = mysql_query("SELECT * FROM (SELECT p.id, p.teamshort, p.teamlong, p.name, potw.datetime, potw.g, potw.a, potw.el FROM el_players p LEFT JOIN potw ON potw.pid=p.id && potw.el=1 WHERE p.name='".$data[name]."' && potw.datetime IS NOT NULL 
UNION
SELECT p.id, p.teamshort, p.teamlong, p.name, potw.datetime, potw.g, potw.a, potw.el FROM 2004players p LEFT JOIN potw ON potw.pid=p.id && potw.el=0 WHERE p.name='".$data[name]."' && potw.datetime IS NOT NULL
ORDER BY datetime DESC LIMIT 1)dt WHERE dt.id IS NOT NULL");
        if(mysql_num_rows($pot)>0)
            {
            $potw = mysql_fetch_array($pot);
            if($potw[g]=="") $potw[g]=0;
            if($potw[a]=="") $potw[a]=0;
            $p = $potw[g]+$potw[a];
            if($p==1) $hl = LANG_GAMECONT_POINT;
            else if($p>1 && $p<5) $hl = LANG_GAMECONT_POINTS;
            else $hl = LANG_TEAMSTATS_PTS;
            $week = (int)date('W',strtotime($potw[datetime]));
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
                        <p class="mb-1 ml-2 small text-center">'.sprintf(LANG_PLAYERS_LASTPOTWTEXT, '<b>'.$week.date('. \týž\d\eň Y',strtotime($potw[datetime])).'</b>', '<br><img class="'.$potw[teamshort].'-small flag-'.($potw[el]==0 ? 'iihf':'el').'" src="/img/blank.png" alt="'.$potw[teamlong].'"><b>'.$potw[teamlong].'</b>').'</p>
                        <p class="h5 text-center"><span class="badge badge-pill badge-'.$leaguecolor.'">'.$p.' '.$hl.' ('.$potw[g].'G + '.$potw[a].'A)</span></p>
                    </div>';
            }
        if($draft!="") $content .='<div class="col-auto mx-auto mx-md-0 mb-2 order-2 order-md-3">'.$draft.'</div>';
      $content .= '</div>';

    $w = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
    $w = mysql_query("SELECT 2004players.*, l.longname, t.id as tid, m.datetime as firstgame FROM 2004players JOIN 2004leagues l ON l.id=2004players.league JOIN 2004teams t ON t.shortname=2004players.teamshort && t.league=2004players.league LEFT JOIN 2004matches m ON m.league=2004players.league WHERE name='$data[name]'$coll GROUP BY 2004players.league ORDER BY firstgame ASC");
    if(mysql_num_rows($w)>0)
        {
        $name = mysql_query("SELECT sum(goals), sum(asists), sum(points), sum(penalty), sum(ppg), sum(shg), sum(gwg), sum(gtg) FROM 2004players WHERE name='$data[name]'$coll");
        $sumar = mysql_fetch_array($name);
        $content .= '<div class="card my-4 shadow animated--grow-in">
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
                    <th class="text-center" style="width:8%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GOALS.'">G</th>
                    <th class="text-center" style="width:8%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_ASISTS.'">A</th>
                    <th class="text-center" style="width:8%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_POINTS.'">P</th>
                    <th class="text-center" style="width:8%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                    <th class="text-center" style="width:8%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PPG.'">PPG</th>
                    <th class="text-center" style="width:8%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SHG.'">SHG</th>
                    <th class="text-center" style="width:8%;" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GWG.'">GWG</th>
                </tr>
              </thead>
              <tbody>';
        while($f = mysql_fetch_array($w))
          {
          $content .= '<tr>
              <td style="width:22%;"><a href="/games/'.$f[league].'-'.SEOTitle($f[longname]).'">'.$f[longname].'</a></td>
              <td style="width:22%;"><a href="/team/'.$f[tid].'0-'.SEOTitle($f[teamlong]).'">'.$f[teamlong].'</a></td>
              <td class="text-center" style="width:8%;">'.$f[goals].'</td>
              <td class="text-center" style="width:8%;">'.$f[asists].'</td>
              <td class="text-center font-weight-bold" style="width:8%;">'.$f[points].'</td>
              <td class="text-center" style="width:8%;">'.$f[penalty].'</td>
              <td class="text-center" style="width:8%;">'.$f[ppg].'</td>
              <td class="text-center" style="width:8%;">'.$f[shg].'</td>
              <td class="text-center" style="width:8%;">'.$f[gwg].'</td>
            </tr>';
          }
        $content .= '</tbody>
              <tfoot class="font-weight-bold">
                <tr>
                  <td colspan="2">'.LANG_BETS_OVERALL.'</td>
                  <td class="text-center" style="width:8%;">'.$sumar[0].'</td>
                  <td class="text-center" style="width:8%;">'.$sumar[1].'</td>
                  <td class="text-center" style="width:8%;">'.$sumar[2].'</td>
                  <td class="text-center" style="width:8%;">'.$sumar[3].'</td>
                  <td class="text-center" style="width:8%;">'.$sumar[4].'</td>
                  <td class="text-center" style="width:8%;">'.$sumar[5].'</td>
                  <td class="text-center" style="width:8%;">'.$sumar[6].'</td>
                </tr>
              </tfoot>
          </table>
         </div>
        </div>';
        }
                
    $w = mysql_query("SELECT el_players.*, l.longname, t.id as tid FROM el_players JOIN 2004leagues l ON l.id=el_players.league JOIN el_teams t ON t.shortname=el_players.teamshort && t.league=el_players.league WHERE name='$data[name]'$coll ORDER BY league ASC, el_players.id ASC");
    if(mysql_num_rows($w)>0)
        {
        $name1 = mysql_query("SELECT sum(gp), sum(goals), sum(asists), sum(points), sum(penalty), sum(ppg), sum(shg), sum(gwg) FROM el_players WHERE name='$data[name]'$coll");
        $sumar1 = mysql_fetch_array($name1);
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
        while($f = mysql_fetch_array($w))
          {
          $content .= '<tr>
              <td style="width:22%;"><a href="/games/'.$f[league].'-'.SEOTitle($f[longname]).'">'.$f[longname].'</a></td>
              <td style="width:22%;"><a href="/team/'.$f[tid].'1-'.SEOTitle($f[teamlong]).'">'.$f[teamlong].'</a></td>
              <td class="text-center" style="width:7%;">'.$f[gp].'</td>
              <td class="text-center" style="width:7%;">'.$f[goals].'</td>
              <td class="text-center" style="width:7%;">'.$f[asists].'</td>
              <td class="text-center font-weight-bold" style="width:7%;">'.$f[points].'</td>
              <td class="text-center" style="width:7%;">'.$f[penalty].'</td>
              <td class="text-center" style="width:7%;">'.$f[ppg].'</td>
              <td class="text-center" style="width:7%;">'.$f[shg].'</td>
              <td class="text-center" style="width:7%;">'.$f[gwg].'</td>
            </tr>';
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
               
    $h = mysql_query("SELECT * FROM 2004playerdiary WHERE name='$data[name]'$coll ORDER BY msg_date DESC");
    if(mysql_num_rows($h)>0)
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
                        $k = mysql_query("SELECT max(msg_date) as max, min(msg_date) as min, datediff(max(msg_date), min(msg_date)) as rozdiel FROM 2004playerdiary WHERE name='$data[name]'$coll");
                        $l = mysql_fetch_array($k);
                        $maxrok = date("Y", strtotime($l[max]));
                        $minrok = date("Y", strtotime($l[min]));
                        $rok = $minrok+1;
                        // timeline roky
                        $i=1;
                        while($i < ($maxrok-$minrok)+1)
                          {
                          $date1 = new DateTime($l[min]);
                          $date2 = new DateTime('01-01-'.$rok);
                          $diff = $date2->diff($date1)->format("%a");
                          $pot = round(($diff/$l[rozdiel])*100,2);
                          $content .= '<div class="timeline-year text-nowrap" style="left: '.$pot.'%;">&nbsp;'.$rok.'</div>';
                          $rok++;
                          $i++;
                          }
                        // timeline eventy
                        $i=$injured=0;
                        while($j = mysql_fetch_array($h))
                          {
                          $wid = '10px';
                          $col = 'primary';
                          $date1 = new DateTime($l[min]);
                          $date2 = new DateTime($j[msg_date]);

                          $diff = $date2->diff($date1)->format("%a");
                          $pot = round(($diff/$l[rozdiel])*100,2);
                          if($j[msg_type]==9) 
                            {
                            $injured=1;
                            $date_healed=$j[msg_date];
                            }
                          if($j[msg_type]==7 && $injured==1)
                            {
                            $date_injured=$j[msg_date];
                            $date1 = new DateTime($date_injured);
                            $date2 = new DateTime($date_healed);
                            $diff = $date2->diff($date1)->format("%a");
                            $wid = round(($diff/$l[rozdiel])*100,2).'%';
                            $injured=0;
                            }
                          if($j[msg_type]==2 || $j[msg_type]==1 || $j[msg_type]==10) $col = 'success';
                          if($j[msg_type]==3) $col = 'secondary';
                          if($j[msg_type]==4 || $j[msg_type]==8) $col = 'warning';
                          if($j[msg_type]==7) $col = 'danger';
                          if($j[msg_type]!=9) $content .= '<span class="timeline-event bg-'.$col.'" style="width: '.$wid.'; left: '.$pot.'%;" data-toggle="tooltip" data-placement="top" data-html="true" title="'.date("j.n.Y", strtotime($j[msg_date])).' - '.$j[msg].'"></span>';
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
                   </div>
                    <div class="card shadow my-4">
                        <div class="card-body">
                        '.GenerateComments(3,$data[name]."p").'
                        </div>
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
      "sAjaxSource": "/includes/diary.php?name='.$data[name].'"
    } );
  } );
          </script>';
      }
    $content .= '   </div> <!-- end col -->
   <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8860983069832222"
            crossorigin="anonymous"></script>
        <!-- HL reklama na podstránkach XL zariadenie -->
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-8860983069832222"
            data-ad-slot="3044717777"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
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
elseif($gid)
  {
  $q = mysql_query("SELECT g.*, l.color, l.longname FROM el_goalies g LEFT JOIN 2004leagues l ON l.id=g.league WHERE g.id='$gid'");
  if(mysql_num_rows($q)>0)
    {
    $comm_id = $gid;
    $data = mysql_fetch_array($q);
    $elinf = mysql_query("SELECT max(born) as born, max(hold) as hold, max(kg) as kg, max(cm) as cm FROM el_goalies WHERE name='$data[name]' ORDER BY id DESC LIMIT 1");
    $elinfo = mysql_fetch_array($elinf);
    $title = "Štatistika brankára ".$data[name];
    $leaguecolor = $data[color];
    $active_league = $data[league];
    if($elinfo[hold]=="L") $hl1=LANG_PLAYERSTATS_LHOLD;
    else $hl1=LANG_PLAYERSTATS_RHOLD;
    $pinfo = array();
    $pinfo[] = LANG_PLAYERSTATS_GK;
    if($elinfo[born] && $elinfo[born]!="1970-01-01") $pinfo[] = date_diff(date_create($elinfo[born]), date_create('today'))->y.' rokov';
    if($elinfo[cm] && $elinfo[cm]!=0) $pinfo[] = $elinfo[cm].' cm';
    if($elinfo[kg] && $elinfo[kg]!=0) $pinfo[] = $elinfo[kg].' kg';
    if($elinfo[hold] && $elinfo[hold]!="") $pinfo[] = $hl1;
    
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($data[longname])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_PLAYERSTATS_TITLEGOALIE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".($data[jersey]>0 ? '#'.$data[jersey].' ' : '').$data[name]."</h2>
                 <div class='row'>
                    <div class='col-12' style='max-width: 1000px;'>";
    
    $content .= '<div class="player-info">
                  <div class="row">
                    <div class="col-auto mx-auto mx-md-0 mb-2 order-1 animated--fade-in">
                      <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$data[name].'" class="lazy rounded-circle img-thumbnail shadow-sm mb-2 p-1" style="width:100px; height:100px; object-fit: cover; object-position: top;">
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
    $w = mysql_query("SELECT el_goalies.*, l.longname, t.id as tid FROM el_goalies JOIN 2004leagues l ON l.id=el_goalies.league JOIN el_teams t ON t.shortname=el_goalies.teamshort && t.league=el_goalies.league WHERE name='$data[name]' ORDER BY league ASC, el_goalies.id ASC");
    $name = mysql_query("SELECT sum(gp), sum(sog), sum(svs), sum(ga), sum(so), sum(pim) FROM el_goalies WHERE name='$data[name]'");
    $sumar = mysql_fetch_array($name);
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
    $i=0;
    $svpp=0;
    $gaap=0;
    while($f = mysql_fetch_array($w))
      {
      $svp = round(($f[svs]/$f[sog])*100,1);
      $gaa = round(($f[ga]/$f[gp]),2);
      $svpp = $svp+$svpp;
      $gaap = $gaa+$gaap;
      $content .= '<tr>
          <td class="text-nowrap" style="width:22%;"><a href="/games/'.$f[league].'-'.SEOTitle($f[longname]).'">'.$f[longname].'</a></td>
          <td class="text-nowrap" style="width:22%;"><a href="/team/'.$f[tid].'1-'.SEOTitle($f[teamlong]).'">'.$f[teamlong].'</a></td>
          <td class="text-center" style="width:7%;">'.$f[gp].'</td>
          <td class="text-center" style="width:7%;">'.$f[sog].'</td>
          <td class="text-center" style="width:7%;">'.$f[svs].'</td>
          <td class="text-center font-weight-bold" style="width:7%;">'.$svp.'%</td>
          <td class="text-center" style="width:7%;">'.$f[ga].'</td>
          <td class="text-center" style="width:7%;">'.$gaa.'</td>
          <td class="text-center" style="width:7%;">'.$f[so].'</td>
          <td class="text-center" style="width:7%;">'.$f[pim].'</td>
        </tr>';
      $i++;
      }
    $content .= '</tbody>
        <tfoot class="font-weight-bold">
        <tr>
          <td colspan="2">'.LANG_BETS_OVERALL.'</td>
          <td class="text-center" style="width:7%;">'.$sumar[0].'</td>
          <td class="text-center" style="width:7%;">'.$sumar[1].'</td>
          <td class="text-center" style="width:7%;">'.$sumar[2].'</td>
          <td class="text-center" style="width:7%;">'.round($svpp/$i,1).'%</td>
          <td class="text-center" style="width:7%;">'.$sumar[3].'</td>
          <td class="text-center" style="width:7%;">'.round($gaap/$i,1).'</td>
          <td class="text-center" style="width:7%;">'.$sumar[4].'</td>
          <td class="text-center" style="width:7%;">'.$sumar[5].'</td>
        </tr>
      </tfoot>
      </table>
     </div>
   </div>
 </div>';
    $h = mysql_query("SELECT * FROM 2004playerdiary WHERE name='$data[name]' ORDER BY msg_date DESC");
    if(mysql_num_rows($h)>0)
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
                    </div>
                    <div class="card shadow my-4">
                        <div class="card-body">
                        '.GenerateComments(3,$data[name]."g").'
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
      "sAjaxSource": "/includes/diary.php?name='.$data[name].'"
    } );
  } );
          </script>';
      }
    $content .= '   
    </div> <!-- end col -->
   <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block">
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-8860983069832222"
            crossorigin="anonymous"></script>
        <!-- HL reklama na podstránkach XL zariadenie -->
        <ins class="adsbygoogle"
            style="display:block"
            data-ad-client="ca-pub-8860983069832222"
            data-ad-slot="3044717777"
            data-ad-format="auto"
            data-full-width-responsive="true"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>
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
elseif($_GET[database])
  {
  $title = LANG_NAV_PLAYERDB;
  $leaguecolor = "hl";
  // manazer si chce zmenit draftovy vyber
  if(strstr($_GET[database], 'newdraft'))
    {
    $id = explode("/", $_GET[database]);
    $_SESSION['olddraft']=$id[1];
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
    if($_GET[database]==$mena[$i]) $content .= '<li class="page-item disabled">
                                                  <a class="page-link" href="#" tabindex="-1" aria-disabled="true">'.$mena[$i].'</a>
                                                </li>';
    else $content .= '<li class="page-item"><a class="page-link" href="/database/'.$mena[$i].'">'.$mena[$i].'</a></li>';
    $i++;
    }
  if($_GET[database]!=1) 
    {
    if(strlen($_GET[database])==1) $add='?vyb='.$_GET[database];
    else $add='?tshort='.$_GET[database];
    }
  $content .= '</ul>
              </nav>
    '.LANG_PLAYERDB_FILTER.': <select id="team" size="1" class="custom-select custom-select-sm w-auto"><optgroup label="Medzinárodné">';
        
    $i=0;
    $uloha = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
    $uloha = MySQL_Query("SELECT * FROM 2004teams WHERE longname NOT LIKE '%U20' GROUP BY longname ORDER BY longname ASC");
    while ($data = mysql_fetch_array($uloha))
          {
          $ts = explode("|", $p_team);
          $teamshort = $ts[0];
          $content .= "<option value='$data[shortname]'";
          if($_GET[database]==$data[shortname]) $content .= "selected"; 
          $content .= ">$data[longname]</option>";
          $i++;
          }
    $content .= "</optgroup><optgroup label='Ligové'>";

    $i=0;
    $uloha = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
    $uloha = MySQL_Query("SELECT * FROM el_teams GROUP BY shortname ORDER BY longname ASC");
    while ($data = mysql_fetch_array($uloha))
          {
          $ts = explode("|", $p_team);
          $teamshort = $ts[0];
          $content .= "<option value='$data[shortname]'";
          if($_GET[database]==$data[shortname]) $content .= "selected"; 
          $content .= ">$data[longname]</option>";
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
// nebol vybrany ziaden hrac
else
  {
  $leaguecolor = "hl";
  $content .= "<div class='alert alert-warning' role='alert'><i class='fas fa-skating'></i> Neexistujúci hráč</div>";
  }
?>