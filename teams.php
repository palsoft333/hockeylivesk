<?
if($_GET[id]) 
  {
  $params = explode("/", htmlspecialchars($_GET[id]));
  $id = explode("-", htmlspecialchars($params[0]));
  $id=$id[0];
  }

$content = "";  
// info o time
if($id)
  {
  $el = substr($id, -1);
  $dl = strlen($id);
  $ide = substr($id, 0, $dl-1);
  if($el==1)
    {
    $teams_table = "el_teams";
    $players_table = "el_players";
    $matches_table = "el_matches";
    $injury_table = "el_injuries";
    $goalies_table = "el_goalies";
    $title1 = LANG_TEAMSTATS_SEASON;
    $title2 = LANG_TEAMSTATS_SEASONSUMM;
    }
  elseif($el==0)
    {
    $teams_table = "2004teams";
    $players_table = "2004players";
    $matches_table = "2004matches";
    $injury_table = "el_injuries";
    $goalies_table = "2004goalies";
    $title1 = LANG_TEAMSTATS_LEAGUE;
    $title2 = LANG_TEAMSTATS_APPEAR;
    }
  else
    {
    $el="3";
    $teams_table = "al_teams";
    $players_table = "al_players";
    $matches_table = "al_matches";
    $injury_table = "al_injuries";
    $goalies_table = "al_goalies";
    $title1 = LANG_TEAMSTATS_SEASON;
    $title2 = LANG_TEAMSTATS_SEASONSUMM;
    }
  $q = mysql_query("SELECT * FROM $teams_table WHERE id='$ide'");
  if(mysql_num_rows($q)==0) { $leaguecolor = "hl"; $content .= "<div class='alert alert-warning' role='alert'><i class='fas fa-times'></i> ".LANG_TEAMSTATS_NOID."</div>"; }
  else
    {
    $f = mysql_fetch_array($q);
    $w = mysql_query("SELECT * FROM 2004leagues WHERE id='$f[league]'");
    $e = mysql_fetch_array($w);
    if(strstr($e[longname],"U20")) $lowerdiv="U20";
    $z = mysql_query("SELECT * FROM el_infos WHERE teamshort='$f[shortname]'");
    $u = mysql_fetch_array($z);
    $o = mysql_query("SELECT * FROM $matches_table WHERE (team1short='$f[shortname]' || team2short='$f[shortname]') && league='$f[league]' ORDER BY datetime");
    $tt = LeagueSpecifics($f[league], $e[longname], 0);
    $pos = $tt->check_position($f[shortname]);
    if($el==1) 
      {
      $hl = LANG_TEAMSTATS_SEASONTITLE;
      $seasson = Get_Seasson($e[longname]);
      $gl = mysql_num_rows($o)-$f[zapasov];
      $ce = $tt->check_canearn($f[shortname])-($f["p_basic"]!=0 ? $f["p_basic"]:$f["body"]);
      $g = mysql_query("SELECT $goalies_table.*, (svs/sog)*100 as svsp, ga/gp as gaa, CONCAT(YEAR(NOW()),DATE_FORMAT(born,'-%m-%d')) as datum, YEAR(NOW())-YEAR(born) as vek, dt.injury FROM $goalies_table LEFT JOIN (SELECT name, injury FROM $injury_table WHERE league='$f[league]')dt ON $goalies_table.name=dt.name WHERE teamshort='$f[shortname]' && league='$f[league]' ORDER BY svsp DESC, gaa ASC");
      $p = mysql_query("SELECT $players_table.*, CONCAT(YEAR(NOW()),DATE_FORMAT(born,'-%m-%d')) as datum, YEAR(NOW())-YEAR(born) as vek, dt.injury FROM $players_table LEFT JOIN (SELECT name, injury FROM $injury_table WHERE league='$f[league]')dt ON $players_table.name=dt.name WHERE teamshort='$f[shortname]' && league='$f[league]' ORDER BY points DESC, gp ASC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC");
      $h = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
      $h = mysql_query("SELECT pd.* FROM 2004playerdiary as pd JOIN(SELECT name FROM $players_table WHERE teamshort='$f[shortname]' && league='$f[league]' GROUP BY name UNION SELECT name FROM $goalies_table WHERE teamshort='$f[shortname]' && league='$f[league]' GROUP BY name)dt ON pd.name=dt.name ORDER BY pd.msg_date DESC, pd.id DESC LIMIT 10");
      $k = mysql_query("SELECT * FROM transfers WHERE from_team='".$f["shortname"]."' || to_team='".$f["shortname"]."' GROUP BY pname, from_team, to_team ORDER BY datetime DESC LIMIT 10");
      include("includes/slovaks.php");
      $slovaci = $slovaks;
      $brankars = $brankari;
      include("includes/slovaki.php");
      $slovaks = array_merge($slovaci, $slovaks, $brankars, $brankari);
      }
    else
      {
      $hl = LANG_TEAMSTATS_TURNEYTITLE;
      $seasson = $e[longname];
      $suffix = ' class="shadow-sm"';
      $g = mysql_query("SELECT $goalies_table.*, (svs/sog)*100 as svsp, ga/gp as gaa, CONCAT(YEAR(NOW()),DATE_FORMAT(born,'-%m-%d')) as datum, YEAR(NOW())-YEAR(born) as vek, dt.injury FROM $goalies_table LEFT JOIN (SELECT name, injury FROM $injury_table WHERE league='$f[league]')dt ON $goalies_table.name=dt.name WHERE teamshort='$f[shortname]' && league='$f[league]' ORDER BY svsp DESC, gaa ASC");
      $p = mysql_query("SELECT $players_table.*, dt.injury FROM $players_table LEFT JOIN (SELECT name, injury FROM $injury_table WHERE league='$f[league]')dt ON $players_table.name=dt.name WHERE $players_table.teamshort='$f[shortname]' && $players_table.league='$f[league]' ORDER BY points DESC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC");
      $h = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
      if($lowerdiv) $h = mysql_query("SELECT pd.* FROM 2004playerdiary as pd JOIN(SELECT p.*, l.longname FROM $players_table p LEFT JOIN 2004leagues l ON l.id=p.league WHERE p.teamshort='$f[shortname]' && l.longname LIKE '%".$lowerdiv."%' GROUP BY p.name)dt ON pd.name=dt.name ORDER BY pd.msg_date DESC, pd.id DESC LIMIT 10");
      else $h = mysql_query("SELECT pd.* FROM 2004playerdiary as pd JOIN(SELECT * FROM $players_table WHERE teamshort='$f[shortname]' GROUP BY name)dt ON pd.name=dt.name ORDER BY pd.msg_date DESC, pd.id DESC LIMIT 10");
      }
    $select = Get_Appearances($f[shortname], $el, $ide);
    $coach = explode(" (",$u[coach]);
    $winner = explode(" (",$u[winner]);

    if($_SESSION[lang]!="sk") $f[longname] = TeamParser($f[longname]);
    $leaguecolor = $e[color];
    $active_league = $f[league];
    $title = LANG_TEAMSTATS_TITLE.' '.$f[longname].' '.$hl.' '.$seasson;
    
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($e[longname])." text-gray-600 mr-1'></i>
                 <h1 class='h3 h3-fluid mb-1'>".$f[longname]."</h1>
                 <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$e[longname]."</h2>
                 <div class='row'>
                    <div class='col-12' style='max-width: 1000px;'>
                 <p class='d-flex justify-content-center justify-content-md-start p-fluid'>".($el==1 ? LANG_TEAMSTATS_SEASON : LANG_TEAMSTATS_LEAGUE).$select."</p>";

    $content .= '<div class="row">
                    <div class="col-auto mx-auto mx-md-0 mb-2 animated--fade-in">
                      <img src="/images/vlajky/'.$f[shortname].'_big.gif" class="'.($el==1 ? 'h-100 img-thumbnail p-2 ':'').'shadow-sm mb-2"'.($el==1 ? '':' style="width:126px;"').'>
                    </div>
                    <div class="col-auto justify-content-center mx-auto mx-md-0 mb-2 card pl-0 pr-2 animated--fade-in">
                      <ul class="list-unstyled px-3 py-2 mb-0 small">';
                      if($el==1)
                        {
                        $content .= '
                        <li><i class="fas fa-pen-fancy mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_ESTABLISHED.':</strong> '.$u[founded].'</li>
                        <li><i class="fas fa-ring mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_ARENA.':</strong> '.$u[arena].'</li>
                        <li><i class="fas fa-compress-arrows-alt mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_ARENACAP.':</strong> '.$u[capacity].'</li>
                        <li><i class="fas fa-user-tie mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_COACH.':</strong> '.$coach[0].'</li>
                        <li><i class="fas fa-medal mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_CHAMPION.':</strong> '.$winner[0].'</li>
                        <li><i class="fas fa-link mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_HOMEPAGE.':</strong> <a href="'.$u[url].'" target="_blank">'.$u[url].'</a></li>';
                        }
                      else
                        {
                        $content .= '
                        <li><i class="fas fa-pen-fancy mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_ENTRY.':</strong> '.$u[founded].'</li>
                        <li><i class="fas fa-users mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_NUMPLAYERS.':</strong> '.number_format($u[players], 0, ',', ' ').'</li>
                        <li><i class="fas fa-ring mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_RINKS.':</strong> '.number_format($u[rinks], 0, ',', ' ').'</li>
                        <li><i class="fas fa-house-user mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_POPULATION.':</strong> '.number_format($u[population], 0, ',', ' ').'</li>
                        <li><i class="fas fa-medal mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_MEDALS.':</strong> '.$u[winner].'</li>
                        <li><i class="fas fa-link mr-1 text-gray-800"></i> <strong>'.LANG_TEAMSTATS_URL.':</strong> <a href="'.$u[url].'" target="_blank">'.$u[url].'</a></li>';
                        }
                      $content .= '</ul>
                    </div>
                    <div class="col-auto justify-content-center mx-auto mx-md-2 mb-2 card p-3 p-sm-2 animated--fade-in">
                      <div class="row">
                        <div class="col-6 border-right"><span class="text-xs font-weight-bold text-'.$leaguecolor.' text-uppercase mb-1">'.($e[active]==1 ? LANG_TEAMSTATS_CURRENTPOS : LANG_TEAMSTATS_FINALPOS).':</span><br>'.$pos.'.'.LANG_TEAMSTATS_PLACE.'</div>
                        <div class="col-6"><span class="text-xs font-weight-bold text-'.$leaguecolor.' text-uppercase mb-1">'.LANG_TEAMSTATS_CURRENTPTS.':</span><br>'.$f[body].'</div>
                      </div>';
                    if($el==1 && $e[active]==1 && $e[endbasic]==0) $content .= '
                      <div class="row">
                        <div class="col-6 border-right"><span class="text-xs font-weight-bold text-'.$leaguecolor.' text-uppercase mb-1">'.LANG_TEAMSTATS_GAMESLEFT.':</span><br>'.$gl.'</div>
                        <div class="col-6"><span class="text-xs font-weight-bold text-'.$leaguecolor.' text-uppercase mb-1">'.LANG_TEAMSTATS_CANEARN.':</span><br>'.$ce.' '.LANG_TEAMSTATS_PTS.'</div>
                      </div>';
        $content .= '</div>
                  </div>
                  
            <div class="row">
              <div class="col-12 col-xl-7">';
                if(mysql_num_rows($g)>0) {
    $content .= '<div class="card my-4 shadow animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                      '.LANG_TEAMSTATS_GOALIES.'
                      <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                    </h6>
                  </div>
                  <div class="card-body">
                    <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid"'.(mysql_num_rows($g)==0 ? '' : ' id="goalies"').'>
                      <thead><tr>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_PLAYERSTATS_POS.'">POS</th>
                        <th>'.LANG_TEAMSTATS_NAME.'</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAMES.'">GP</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SOG.'">SOG</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SVS.'">SVS</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SVP.'">SV%</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GA.'">GA</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAA.'">GAA</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SO.'">SO</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                    </tr>
                  </thead>
                  <tbody>';
                    $i=0;
                    while($t = mysql_fetch_array($g))
                      {
                      $bday=$injury=$slovak="";
                      if(strtotime($t[datum])==mktime(0,0,0)) $bday = ' <i class="fas fa-birthday-cake" data-toggle="tooltip" data-placement="top" data-html="true" title="Dnes oslavuje <strong>'.$t[vek].'</strong> rokov<br>Blahoželáme!"></i>';
                      if($t[injury]!=NULL) $injury = ' <i class="fas fa-user-injured text-danger" data-toggle="tooltip" data-placement="top" data-html="true" title="Zranený: <strong>'.$t[injury].'</strong>"></i>';
                      if(array_key_exists($t[name], $slovaks)) $slovak = ' <img class="flag-iihf SVK-small" src="/img/blank.png" alt="Slovák">';
                      $content .= '<tr'.($el==1 && $t[gp]==0 ? ' class="text-gray-500"':'').'>
                      <td class="text-center">GK</td>
                      <td class="text-nowrap"><a href="/goalie/'.$t[id].$el.'-'.SEOtitle($t[name]).'">'.$t[name].'</a>'.$slovak.''.$injury.''.$bday.'</td>
                      <td class="text-center">'.$t[gp].'</td>
                      <td class="text-center">'.$t[sog].'</td>
                      <td class="text-center">'.$t[svs].'</td>
                      <td class="text-center font-weight-bold">'.round($t[svsp],1).'</td>
                      <td class="text-center">'.$t[ga].'</td>
                      <td class="text-center">'.round($t[gaa],2).'</td>
                      <td class="text-center">'.$t[so].'</td>
                      <td class="text-center">'.$t[pim].'</td>
                    </tr>';
                      $i++;
                      }
                  $content .='</tbody>
                    </table>
                  </div>
                </div>';
                }
                if($el==0) $prefix='nonel';
   $content .= '<div class="card my-4 shadow animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                      '.LANG_TEAMSTATS_PLAYERS.'
                      <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                    </h6>
                  </div>
                  <div class="card-body">
                    <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid"'.(mysql_num_rows($p)==0 ? '' : ' id="'.$prefix.'players"').'">
                      <thead><tr>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_PLAYERSTATS_POS.'">POS</th>
                        <th>'.LANG_TEAMSTATS_NAME.'</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAMES.'">GP</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GOALS.'">G</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_ASISTS.'">A</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_POINTS.'">P</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PPG.'">PPG</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SHG.'">SHG</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GWG.'">GWG</th>
                    </tr>
                  </thead>
                  <tbody>';
                    while($y = mysql_fetch_array($p))
                      {
                      $bday=$injury=$slovak="";
                      if($el==0)
                        {
                        if($y[pos]=="LD" || $y[pos]=="RD") $y[pos]="D";
                        if($y[pos]=="CE" || $y[pos]=="RW" || $y[pos]=="LW") $y[pos]="F";
                        }
                      if(strtotime($y[datum])==mktime(0,0,0)) $bday = ' <i class="fas fa-birthday-cake" data-toggle="tooltip" data-placement="top" data-html="true" title="Dnes oslavuje <strong>'.$y[vek].'</strong> rokov<br>Blahoželáme!"></i>';
                      if($y[injury]!=NULL) $injury = ' <i class="fas fa-user-injured text-danger" data-toggle="tooltip" data-placement="top" data-html="true" title="Zranený: <strong>'.$y[injury].'</strong>"></i>';
                      if(array_key_exists($y[name], $slovaks)) $slovak = ' <img class="flag-iihf SVK-small" src="/img/blank.png" alt="Slovák">';
                      $content .= '<tr'.($el==1 && $y[gp]==0 ? ' class="text-gray-500"':'').'>
                      <td class="text-center">'.$y[pos].'</td>
                      <td class="text-nowrap"><a href="/player/'.$y[id].$el.'-'.SEOtitle($y[name]).'">'.$y[name].'</a>'.$slovak.''.$injury.''.$bday.'</td>
                      <td class="text-center">'.$y[gp].'</td>
                      <td class="text-center">'.$y[goals].'</td>
                      <td class="text-center">'.$y[asists].'</td>
                      <td class="text-center font-weight-bold">'.$y[points].'</td>
                      <td class="text-center">'.$y[penalty].'</td>
                      <td class="text-center">'.$y[ppg].'</td>
                      <td class="text-center">'.$y[shg].'</td>
                      <td class="text-center">'.$y[gwg].'</td>
                    </tr>';
                      $i++;
                      }
                    if(mysql_num_rows($p)==0) $content .= '<tr><td colspan="10">'.LANG_TEAMSTATS_PLAYERSSOON.'</td></tr>';
                  $content .='</tbody>
                    </table>
                  </div>
                </div>
                <div class="card my-4 shadow animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                      '.LANG_TEAMSTATS_NEWS.'
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="row p-fluid font-weight-bold d-none d-sm-flex">
                        <div class="col-2">'.LANG_DATE.'</div>
                        <div class="col-4">'.LANG_PLAYERDB_PLAYER.'</div>
                        <div class="col-6">'.LANG_TEAMSTATS_EVENT.'</div>
                    </div>';
                    $i=0;
                    while($g = mysql_fetch_array($h))
                      {
                      $icon="";
                      $datum = date("j.n.Y", strtotime($g[msg_date]));
                      if(strtotime($g[msg_date])==mktime(0,0,0)) $datum='dnes';
                      if(strtotime($g[msg_date])==mktime(0,0,0,date("n"),date("j")-1)) $datum='včera';
                      if($g[msg_type]==1) $icon = '<i class="fas fa-exchange-alt text-danger"></i>'; //transfer
                      if($g[msg_type]==2) $icon = '<i class="fas fa-user-plus text-success"></i>'; //pridal sa
                      if($g[msg_type]==3) $icon = '<i class="fas fa-dice-three text-secondary"></i>'; //hattrick
                      if($g[msg_type]==4) $icon = '<i class="fas fa-hockey-puck text-warning"></i>'; //gwg
                      if($g[msg_type]==5) $icon = '<i class="fas fa-certificate text-primary"></i>'; //jubilejny gol
                      if($g[msg_type]==6) $icon = '<i class="fab fa-creative-commons-zero text-dark"></i>'; //shutout
                      if($g[msg_type]==7) $icon = '<i class="fas fa-user-injured text-danger"></i>'; //injury
                      if($g[msg_type]==8) $icon = '<i class="fas fa-trophy text-warning"></i>'; //titul
                      if($g[msg_type]==9) $icon = '<i class="fas fa-band-aid rotate-n-15 text-warning"></i>'; //uzdravil sa
                      if($g[msg_type]==10) $icon = '<i class="fas fa-user-slash text-primary"></i>'; //volny hrac
                      $content .= '<div class="row p-fluid"'.($i%2==1 ? '':' style="background-color: rgba(0,0,0,.05);"').'>
                      <div class="col-5 col-sm-2 order-2 order-sm-1 small pt-1 text-right text-sm-left">'.$datum.'</div>
                      <div class="col-7 col-sm-4 order-1 order-sm-2 pb-2 pb-sm-0">'.$g[name].'</div>
                      <div class="col-12 col-sm-6 order-3">'.$icon.' '.$g[msg].'</div>
                    </div>';
                      $i++;
                      }
                  $content .='
                  </div>
                </div>';
                if(mysql_num_rows($k)>0) {
                  $content .= '
                <div class="card my-4 shadow animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                      '.LANG_TEAMSTATS_LATESTTRANSFERS.'
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="row p-fluid font-weight-bold d-none d-sm-flex">
                        <div class="col-2">'.LANG_DATE.'</div>
                        <div class="col-3">'.LANG_PLAYERDB_PLAYER.'</div>
                        <div class="col-3">'.LANG_TEAMSTATS_FROMTEAM.'</div>
                        <div class="col-1"></div>
                        <div class="col-3">'.LANG_TEAMSTATS_TOTEAM.'</div>
                    </div>';
                  $i=0;
                  while($l = mysql_fetch_array($k)) {
                    $datum = date("j.n.Y", strtotime($l["datetime"]));
                    if(strtotime($l["datetime"])==mktime(0,0,0)) $datum='dnes';
                    if(strtotime($l["datetime"])==mktime(0,0,0,date("n"),date("j")-1)) $datum='včera';
                    if($l["status"]=="0" && $l["to_name"]=="") $l["to_name"]=LANG_TEAMSTATS_FREEAGENT;
                    if($l["pid"]!=NULL) {
                      if($l["goalie"]==0) $pl = mysql_query("SELECT name FROM el_players WHERE id='".$l["pid"]."'");
                      else $pl = mysql_query("SELECT name FROM el_goalies WHERE id='".$l["pid"]."'");
                      $player = mysql_fetch_array($pl);
                      if($l["goalie"]==0) $url = '/player/'.$l["pid"].'1-'.SEOtitle($player["name"]);
                      else $url = '/goalie/'.$l["pid"].'1-'.SEOtitle($player["name"]);
                    }
                    else $player["name"] = $l["pname"];
                    $content .= '
                    <div class="row p-fluid"'.($i%2==1 ? '':' style="background-color: rgba(0,0,0,.05);"').'>
                      <div class="col-5 col-sm-2 order-2 order-sm-1 small pt-1 text-right text-sm-left">'.$datum.'</div>
                      <div class="col-7 col-sm-3 order-1 order-sm-2 pb-2 pb-sm-0">'.($l["pid"]!=NULL ? '<a href="'.$url.'">'.$player["name"].'</a>':$player["name"]).'</div>
                      <div class="col-5 col-sm-3 order-3 small bg-white border rounded text-center p-1">'.($l["from_image"]!="" ? '<img src="'.$l["from_image"].'" style="height:24px;"><br>':'').''.$l["from_name"].'</div>
                      <div class="col-2 col-sm-1 order-4 align-self-center"><i class="fas fa-angle-double-right '.($l["from_team"]==$f["shortname"] ? 'text-danger':'text-success').'"></i></div>
                      <div class="col-5 col-sm-3 order-5 small bg-white border rounded text-center p-1">'.($l["to_image"]!="" ? '<img src="'.$l["to_image"].'" style="height:24px;"><br>':'').''.$l["to_name"].'</div>
                    </div>';
                    $i++;
                  }
                  $content .= '
                  </div>
                </div>';
                }
                $content .= '
              </div>
              <div class="col-12 col-xl-5'.($el==0 ? ' order-xl-last':'').'">
                <div class="card my-4 shadow animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                      '.LANG_TEAMSTATS_MATCHES.' '.$seasson.'
                    </h6>
                  </div>
                  <div class="card-body">
                    <div class="row p-fluid font-weight-bold d-none d-sm-flex">
                        <div class="col">'.LANG_DATE.'</div>
                        <div class="col text-center">'.LANG_MATCH1.'</div>
                        <div class="col text-right">'.LANG_MATCHES_RESULT.'</div>
                    </div>';
                    $i=0;
                    while($l = mysql_fetch_array($o))
                      {
                      $color="";
                      $ndatum = strftime("%a, %e.%b %Y %k:%M", strtotime($l[datetime]));
                      if($l[team1short]==$f[shortname])
                        {
                        if($l[goals1]>$l[goals2]) $color=" badge-success";
                        else $color=" badge-danger";
                        }
                      if($l[team2short]==$f[shortname])
                        {
                        if($l[goals1]>$l[goals2]) $color=" badge-danger";
                        else $color=" badge-success";
                        }
                      $score = '<a href="/report/'.$l[id].$el.'-'.SEOtitle($l[team1long].' vs '.$l[team2long]).'" class="badge badge-pill text-xs'.$color.'">'.$l[goals1].':'.$l[goals2].'</a>';
                      if($l[kedy]<>"konečný stav")
                        {
                        $color="";
                        if($l[kedy]=="na programe") $score='<a href="/game/'.$l[id].$el.'-'.SEOtitle($l[team1long]." vs ".$l[team2long]).'" class="btn btn-light btn-circle btn-sm"><i class="fas fa-search"></i></a>';
                        }
                      $content .= '<div class="row p-fluid"'.($i%2==1 ? '':' style="background-color: rgba(0,0,0,.05);"').'>
                      <div class="col-12 col-sm-5 align-self-center small text-nowrap text-center text-sm-left">'.$ndatum.'</div>
                      <div class="col-9 col-sm-5 align-self-center text-left text-sm-center text-nowrap"><img class="flag-'.($el==0 ? 'iihf':'el').' '.$l[team1short].'-small" src="/img/blank.png" alt="'.$l[team1long].'"> <strong>'.$l[team1short].'</strong> vs. <strong>'.$l[team2short].'</strong> <img class="flag-'.($el==0 ? 'iihf':'el').' '.$l[team2short].'-small" src="/img/blank.png" alt="'.$l[team2long].'"></div>
                      <div class="col-3 col-sm-2 text-center">'.$score.'</div>
                    </div>';
                      $struct_data[] = array($l[team1long], $l[team2long], 'https://www.hockey-live.sk/images/vlajky/'.$l[team1short].'_big.gif','https://www.hockey-live.sk/images/vlajky/'.$l[team2short].'_big.gif', 'https://www.hockey-live.sk/game/'.$l[id].$el.'-'.SEOtitle($l[team1long]." vs ".$l[team2long]), $l[datetime], 'https://www.hockey-live.sk/includes/gotd.php?id='.$l[id].$el);
                      $i++;
                      }
                  $content .= '
                  </div>
                </div>';

    // strukturovane data pre Google
    $content .= '<script type="application/ld+json">[';
    foreach($struct_data as $event) {
        $content .= '
  {
    "@context": "https://schema.org",
    "@type": "SportsEvent",
    "name": "'.$event[0].' vs. '.$event[1].'",
    "url": "'.$event[4].'",
    "image": "'.$event[6].'",
    "startDate": "'.date("c",strtotime($event[5])).'",
    "endDate": "'.date("c",strtotime($event[5])+9000).'",
    "eventAttendanceMode": "https://schema.org/MixedEventAttendanceMode",
    "eventStatus": "https://schema.org/EventScheduled",
    "homeTeam": {
      "@type": "SportsTeam",
      "name": "'.$event[0].'",
      "image": "'.$event[2].'"
    },
    "awayTeam": {
      "@type": "SportsTeam",
      "name": "'.$event[1].'",
      "image": "'.$event[3].'"
    },
    "location": "'.$e[longname].'"
  },';
    }
    $content = substr($content, 0, -1);
    $content .= ']
    </script>';

if($el==0) $content .= '<div class="card shadow mb-4">
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- V detaile non-EL tímu -->
<ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-8860983069832222"
     data-ad-slot="7955012184"
     data-ad-format="auto"
     data-full-width-responsive="true"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>';
$content .= '

            '.GoogleNews("t",$id).'

              </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-body">
                '.GenerateComments(1,$id).'
                </div>
            </div>';


          $content .='
    </div> <!-- end col -->
   <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block">';
            include("includes/advert_bigscreenside.php");
            $content .= $advert;
        $content .= '
   </div> <!-- end col -->
   </div> <!-- end row -->';
    }
// nebol vybrany ziaden tim
  }
else
  {
  $content .= "<div class='alert alert-warning' role='alert'><i class='fas fa-users-slash'></i> Neexistujúci tím</div>";
  }
?>