<?  
/*
* Funkcia pre zistenie aká tabuľka sa bude generovať
* version: 1.0.0 (29.1.2016 - vytvorenie novej funkcie)
* @param $lid integer - ID ligy
* @param $params array - parametre z URL adresy
* @param $sim boolean - jedná sa o simuláciu všetkých zápasov? (0/1)
* @param $pos string - ak obsahuje shortname tímu, tak zistí jeho pozíciu v tabuľke
* @return $content string
*/
function Get_Table($lid, $params, $table_type, $sim, $pos=false) {
  Global $title, $leaguecolor, $leaderwas, $clinchwas, $cannotwas, $relegwas, $npos, $json;
  if($pos) $npos=$pos;
  $sel = mysql_query("SELECT * FROM 2004leagues WHERE id='$lid'");
  $vyb = mysql_fetch_array($sel);
  
  $title = LANG_NAV_TABLE.' '.$vyb[longname];
  $leaguecolor = $vyb[color];
  $content .= "<div id='toasts' class='fixed-top' style='top: 80px; right: 23px; left: initial; z-index:3;'></div>
			   <div id='table-spinner' class='position-absolute' style='top: 50%; left: 50%; z-index: 2; display: none;'>
                <div class='spinner-border text-".$leaguecolor."' role='status'>
                  <span class='sr-only'>Loading...</span>
                </div>
               </div>
               <i class='float-left h1 h1-fluid ll-".LeagueFont($vyb[longname])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".($table_type=="playoff" ? LANG_TEAMTABLE_PLAYOFF:LANG_NAV_TABLE)."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$vyb[longname]."</h2>
               <div".($table_type=="playoff" ? "":" style='max-width: 1000px;'").">";
  
  // prepínače
  if($vyb[el]==1) 
    {
    if($table_type=="playoff")
      {
      $po_type=explode("/",$params);
      $po_type=$params[2];
      $j = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
      $j = mysql_query("SELECT potype FROM el_playoff WHERE league='$lid' && played='0' GROUP BY potype ORDER BY id ASC");
      if(mysql_num_rows($j)>0) 
        {
        $k = mysql_fetch_array($j);
        if($po_type=="") $po_type=$k[potype];
        }
      elseif($po_type=="") $po_type="stvrt";
      if($po_type=="quarter") $po_type="stvrt";
      if($po_type=="gagarin") $po_type="stanley";
      if($po_type=="qualify") $po_type="baraz";
      $g = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
      $g = mysql_query("SELECT potype FROM el_playoff WHERE league='$lid' GROUP BY potype ORDER BY id ASC");
      if(mysql_num_rows($g)>1)
        {
        $content .= '<nav aria-label="Table navigation">
                      '.LANG_TEAMTABLE_SHOW.':
                      <ul class="pagination pagination-sm">';
        while($h = mysql_fetch_array($g))
          {
          $lin = $h[potype];
          if($h[potype]=="baraz") { $nam = LANG_QUALIFYROUND; $lin="qualify"; }
          elseif($h[potype]=="stvrt") { $nam = LANG_QUARTERFINAL; $lin="quarter"; }
          elseif($h[potype]=="semi") $nam = LANG_SEMIFINAL;
          elseif($h[potype]=="final") $nam = LANG_FINAL;
          elseif($h[potype]=="stanley" && strstr($vyb[longname], 'KHL')) { $nam = LANG_GAGARIN; $lin="gagarin"; }
          elseif($h[potype]=="stanley" && strstr($vyb[longname], 'NHL')) $nam = LANG_STANLEY;
          else $nam = LANG_RELEGATION;
          if($po_type==$h[potype]) $content .= '<li class="page-item disabled">
                                                    <a class="page-link" tabindex="-1" href="#" aria-disabled="true">'.$nam.'</a>
                                                </li>';
          else $content .= '<li class="page-item">
                                <a class="page-link text-'.$leaguecolor.'" href="#" aria-label="'.$nam.'" onclick="GetTable(\''.$params[0].'/playoff/'.$lin.'\');">
                                  <span aria-hidden="true">'.$nam.'</span>
                                </a>
                             </li>';
          }
        $content .= '</ul></nav>';
        }
      }
    else
      {
      if(strstr($vyb[longname], 'KHL') || strstr($vyb[longname], 'NHL'))
        {
        if(!$table_type && $lid==132) $table_type="division";
        if(!$table_type) $table_type="conference";
        $content .= '<nav aria-label="Table navigation">
                     '.LANG_TEAMTABLE_SHOW.':
                     <ul class="pagination pagination-sm">';
        $types = array(
                  array("division",LANG_TEAMTABLE_DIVISIONS),
                  array("conference",LANG_TEAMTABLE_CONFERENCES),
                  array("league",LANG_TEAMTABLE_LEAGUE)
                  );
        //if(strstr($vyb[longname], 'NHL')) $types[] = array("roundrobin",LANG_TEAMTABLE_ROUNDROBIN);
        // nasleduje riadok pre COVID sezonu bez konferencii
        //if(strstr($vyb[longname], 'NHL')) array_splice($types, 1, 1);
        $i=0;
        while($i < count($types))
          {
          if($table_type==$types[$i][0]) $content .= '<li class="page-item disabled">
                                                    <a class="page-link" tabindex="-1" href="#" aria-disabled="true">'.$types[$i][1].'</a>
                                                   </li>';
          else $content .= '<li class="page-item">
                                <a class="page-link text-'.$leaguecolor.'" href="#" aria-label="'.$types[$i][1].'" onclick="GetTable(\''.$params[0].'/'.$types[$i][0].'\');">
                                  <span aria-hidden="true">'.$types[$i][1].'</span>
                                </a>
                             </li>';         
          $i++;
          }
        $content .= '</ul></nav>';
        }
      else
        {
        if(!$table_type || $table_type!="league") $table_type="league";
        }
      if(!strstr($vyb[longname], 'NHL')) { $content .= '
      <div class="d-flex justify-content-end">
        <div class="custom-control custom-switch d-inline-block" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMTABLE_SIMULATIONTOOLTIP.'" >
          <input type="checkbox" class="custom-control-input" id="switch" value="'.$params[0].'/'.$table_type.'"'.($sim==1 ? ' checked' : '').'>
          <label class="custom-control-label" for="switch">'.LANG_TEAMTABLE_SIMULATION.'</label>
        </div>
      </div>'; }
      }
    }
  else
    {
    if($table_type=="playoff")
      {
      $po_type=explode("/",$params);
      $po_type=$params[2];
      $j = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
      $j = mysql_query("SELECT po_type FROM 2004matches WHERE league='$lid' && kedy!='konečný stav' GROUP BY po_type ORDER BY id ASC");
      if(mysql_num_rows($j)>0) 
        {
        $k = mysql_fetch_array($j);
        if($po_type=="") 
          {
          if($k[po_type]=="Q") $po_type="qualify";
          elseif($k[po_type]=="QF") $po_type="quarter";
          elseif($k[po_type]=="SF") $po_type="semi";
          elseif($k[po_type]=="B") $po_type="bronze";
          else $po_type="final";
          }
        }
      elseif($po_type=="") $po_type="final";
      $g = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
      $g = mysql_query("SELECT po_type FROM 2004matches WHERE league='$lid' && po_type!='' GROUP BY po_type ORDER BY id ASC");
      if(mysql_num_rows($g)>0)
        {
        $content .= '<nav aria-label="Table navigation">
                     '.LANG_TEAMTABLE_SHOW.':
                     <ul class="pagination pagination-sm">';
        while($h = mysql_fetch_array($g))
          {
          if($h[po_type]=="Q") { $nam = LANG_QUALIFYROUND; $lin="qualify"; }
          elseif($h[po_type]=="QF") { $nam = LANG_QUARTERFINAL; $lin="quarter"; }
          elseif($h[po_type]=="SF") { $nam = LANG_SEMIFINAL; $lin="semi"; }
          elseif($h[po_type]=="B") { $nam = LANG_BRONZE; $lin="bronze"; }
          else { $nam = LANG_FINAL; $lin="final"; }
          if($po_type==$lin) $content .= '<li class="page-item disabled">
                                              <a class="page-link" tabindex="-1" href="#" aria-disabled="true">'.$nam.'</a>
                                          </li>';
          else $content .= '<li class="page-item">
                                <a class="page-link text-'.$leaguecolor.'" href="#" aria-label="'.$nam.'" onclick="GetTable(\''.$params[0].'/playoff/'.$lin.'\');">
                                  <span aria-hidden="true">'.$nam.'</span>
                                </a>
                             </li>';
          }
        $content .= '</ul></nav>';
        }
      }
    }
  
  // KHL
  if(strstr($vyb[longname], 'KHL'))
    {
    $games_total = 56;
    $playoff_line = 8;
    $wpoints = $vyb[points];
    $playoff_wins = 4;
    $div1_teams = array("SPA", "VIT", "SOC", "JOK", "PET", "TNN");
    $div2_teams = array("DYN", "DMN", "LOK", "DIR", "CSK", "SEV");
    $div3_teams = array("AVT", "AKB", "MMG", "NKH", "TRA", "KUN");
    $div4_teams = array("AVA", "SIB", "AMU", "BAR", "SAL", "VLA");
    $orderby = "body desc, wins desc, diff desc, zapasov asc, id asc";
    if($table_type=="conference")
      {
      $conf1_teams = array_merge($div1_teams,$div2_teams);
      $conf2_teams = array_merge($div3_teams,$div4_teams);
      $rtable .= Render_Table(LANG_TEAMTABLE_WESTCONF1, $vyb, $table_type, $conf1_teams, $wpoints, $games_total, $playoff_line, $orderby, $sim);
      $rtable .= Render_Table(LANG_TEAMTABLE_EASTCONF1, $vyb, $table_type, $conf2_teams, $wpoints, $games_total, $playoff_line, $orderby, $sim);
      }
    if($table_type=="division")
      {
      $rtable .= Render_Table(LANG_TEAMTABLE_BOBROV, $vyb, $table_type, $div1_teams, $wpoints, $games_total, 0, $orderby, $sim);
      $rtable .= Render_Table(LANG_TEAMTABLE_TARASOV, $vyb, $table_type, $div2_teams, $wpoints, $games_total, 0, $orderby, $sim);
      $rtable .= Render_Table(LANG_TEAMTABLE_KHARLAMOV, $vyb, $table_type, $div3_teams, $wpoints, $games_total, 0, $orderby, $sim);
      $rtable .= Render_Table(LANG_TEAMTABLE_CHERNYSHEV, $vyb, $table_type, $div4_teams, $wpoints, $games_total, 0, $orderby, $sim);
      }
    if($table_type=="league")
      {
      $league_teams = array_merge($div1_teams,$div2_teams,$div3_teams,$div4_teams);
      $rtable .= Render_Table(LANG_TEAMTABLE_WHOLELEAGUE, $vyb, $table_type, $league_teams, $wpoints, $games_total, 0, $orderby, $sim);
      }
    if($table_type=="playoff")
      {
      $rtable .= Render_Playoff($vyb, $po_type, $playoff_wins);
      }
    }
  // NHL
  elseif(strstr($vyb[longname], 'NHL'))
    {
    $games_total = 82;
    $playoff_line = 8;
    $wpoints = $vyb[points];
    $playoff_wins = 4;
    $div1_teams = array("STL", "COL", "CHI", "MIN", "DAL", "WPG", "NSH", "ARI");
    $div2_teams = array("ANA", "SJS", "LAK", "SEA", "VAN", "CGY", "EDM", "VGK");
    $div3_teams = array("BOS", "MTL", "TBL", "DET", "TOR", "OTT", "FLA", "BUF");
    $div4_teams = array("PIT", "NYR", "PHI", "CBJ", "WSH", "NJD", "CAR", "NYI");
    /*$div1_teams = array("CAR", "CHI", "CBJ", "DAL", "DET", "FLA", "NSH", "TBL");
    $div2_teams = array("ANA", "ARI", "COL", "LAK", "MIN", "SJS", "STL", "VGK");
    $div3_teams = array("BOS", "BUF", "NJD", "NYI", "NYR", "PHI", "PIT", "WSH");
    $div4_teams = array("MTL", "CGY", "EDM", "OTT", "TOR", "VAN", "WPG", "XXX");*/
    $orderby = "body desc, zapasov asc, wins desc, diff desc";
    if($table_type=="conference")
      {
      $conf1_teams = array_merge($div1_teams,$div2_teams);
      $conf2_teams = array_merge($div3_teams,$div4_teams);
      $rtable .= Render_Table(LANG_TEAMTABLE_WESTCONF1, $vyb, $table_type, $conf1_teams, $wpoints, $games_total, $playoff_line, $orderby, $sim);
      $rtable .= Render_Table(LANG_TEAMTABLE_EASTCONF1, $vyb, $table_type, $conf2_teams, $wpoints, $games_total, $playoff_line, $orderby, $sim);
      }
    if($table_type=="division")
      {
      $rtable .= Render_Table(LANG_TEAMTABLE_CENTRAL, $vyb, $table_type, $div1_teams, $wpoints, $games_total, 0, $orderby, $sim);
      $rtable .= Render_Table(LANG_TEAMTABLE_PACIFIC, $vyb, $table_type, $div2_teams, $wpoints, $games_total, 0, $orderby, $sim);
      $rtable .= Render_Table(LANG_TEAMTABLE_ATLANTIC, $vyb, $table_type, $div3_teams, $wpoints, $games_total, 0, $orderby, $sim);
      $rtable .= Render_Table(LANG_TEAMTABLE_METROPOLITAN, $vyb, $table_type, $div4_teams, $wpoints, $games_total, 0, $orderby, $sim);
      }
    if($table_type=="league")
      {
      $league_teams = array_merge($div1_teams,$div2_teams,$div3_teams,$div4_teams);
      $rtable .= Render_Table(LANG_TEAMTABLE_WHOLELEAGUE, $vyb, $table_type, $league_teams, $wpoints, $games_total, 0, $orderby, $sim);
      }
    if($table_type=="roundrobin")
      {
      $conf1_teams = array("STL", "COL", "VGK", "DAL");
      $conf2_teams = array("BOS", "TBL", "WSH", "PHI");
      $rtable .= Render_Table(LANG_TEAMTABLE_WESTCONF1, $vyb, "league", $conf1_teams, $wpoints, 3, 0, $orderby, $sim);
      $rtable .= Render_Table(LANG_TEAMTABLE_EASTCONF1, $vyb, "league", $conf2_teams, $wpoints, 3, 0, $orderby, $sim);
      }
    if($table_type=="playoff")
      {
      if($po_type=="baraz") $playoff_wins = 3;
      $rtable .= Render_Playoff($vyb, $po_type, $playoff_wins);
      }
    }
  // Tipsport Liga
  elseif($vyb[el]==1)
    {
    $games_total = 50;
    $playoff_line = 6;
    $wpoints = $vyb[points];
    $playoff_wins = 4;
    $orderby = "body desc, diff desc, zapasov asc, goals desc, wins desc, losts asc";
    if($table_type=="league")
      {
      $rtable .= Render_Table(LANG_NAV_TABLE." $vyb[longname]", $vyb, $table_type, 0, $wpoints, $games_total, $playoff_line, $orderby, $sim);
      /*$skupa = array("BBS", "SBA", "KOS", "ZVO", "POP", "TRE");
      $skupb = array("MII", "NIT", "MIJ", "NZO", "DTV", "MAC");
      $rtable .= Render_Table("II. Nadstavbová časť sk. A (1.-6.)", $vyb, $table_type, $skupa, $wpoints, $games_total, 6, $orderby, $sim);
      $rtable .= Render_Table("II. Nadstavbová časť sk. B (7.-12.)", $vyb, $table_type, $skupb, $wpoints, $games_total, 2, $orderby, $sim);*/
      }
    if($table_type=="playoff")
      {
      if($po_type=="baraz") $playoff_wins = 3;
      $content .= Render_Playoff($vyb, $po_type, $playoff_wins);
      }
    }
  // Medzinarodný turnaj
  else
    {
    if(!$table_type && $vyb[endbasic]==0 && $vyb[groups]!="") $table_type="groups";
    if(!$table_type && $vyb[endbasic]==0 && $vyb[groups]=="") $table_type="league";
    if(!$table_type && $vyb[endbasic]==1) $table_type="playoff";
    $wpoints = $vyb[points];
    if($table_type=="groups")
      {
      if(strstr($vyb[longname], 'MS'))
        {
        $playoff_line=4;
        if(strstr($vyb[longname], 'MS U20')) $games_total=4;
        else $games_total=7;
        }
      elseif(strstr($vyb[longname], 'Svetový pohár')) 
        {
        $playoff_line=2;
        $games_total=3;
        }
      elseif(strstr($vyb[longname], 'ZOH')) 
        {
        $playoff_line=1;
        $games_total=3;
        }
      else
        {
        $playoff_line=0;
        $games_total=0;
        }
      $rtable .= Render_Table(0, $vyb, $table_type, 0, $wpoints, $games_total, $playoff_line, 0, $sim);
      }
    if($table_type=="league")
      {
      $orderby = "body desc, diff desc, goals desc, wins desc, losts asc";
      $rtable .= Render_Table("Tabuľka $vyb[longname]", $vyb, $table_type, 0, $wpoints, 0, 0, $orderby, $sim);
      }
    if($table_type=="playoff")
      {
      $rtable .= Render_Playoff($vyb, $po_type, 1);
      }
    }
  $content .= $rtable;
  if($leaderwas==1 || $clinchwas==1 || $cannotwas==1 || $relegwas==1) $content .= "<div class='bg-white border my-3 p-2 rounded small'>";
  if($leaderwas==1) $content .= "<span class='font-weight-bold'>*</span> - ".LANG_TEAMTABLE_DIVLEADERS."<br>";
	if($clinchwas==1) $content .= "<span class='font-weight-bold text-success'>x</span> - ".($table_type=="groups" ? LANG_TEAMTABLE_CLINCHEDQF : LANG_TEAMTABLE_CLINCHED)."<br>";
	if($cannotwas==1) $content .= "<span class='font-weight-bold text-danger'>y</span> - ".($table_type=="groups" ? LANG_TEAMTABLE_CANNOTMAKEQF : LANG_TEAMTABLE_CANNOTMAKEPO)."<br>";
	if($relegwas==1) $content .= "<span class='font-weight-bold text-info'>z</span> - ".LANG_TEAMTABLE_RELEGATED;
	if($leaderwas==1 || $clinchwas==1 || $cannotwas==1 || $relegwas==1) $content .= "</div>";
	$content .= '</div>';
	if(!$npos  && !$json) return $content;
	else return $rtable;
}
/*
* Funkcia pre vytvorenie hlavnej dátovej sady tímovej tabuľky
* version: 1.0.0 (24.1.2016 - vytvorenie novej funkcie)
* @param $table_name string - Nadpis tabuľky
* @param $league_data resource - riadok z databázy o danej lige
* @param $table_type string - typ tabuľky (conference, division, league, groups, playoff)
* @param $teams array - skratky všetkých tímov, ktoré sa vypíšu
* @param $wpoints integer - počet bodov za víťazstvo v lige
* @param $games_total integer - počet zápasov jedného tímu v danej skupine
* @param $playoff_line integer - počet postupujúcich tímov do playoff (ak je 0, nezobrazí čiaru)
* @param $orderby string - podľa čoho sa radia tímy v tabuľke (mysql formát)
* @param $sim boolean - jedná sa o simuláciu všetkých zápasov? (0/1)
* @return $out string
*/

function Render_Table($table_name, $league_data, $table_type, $teams, $wpoints, $games_total, $playoff_line, $orderby, $sim)
  {
  Global $npos, $json;
  if($league_data[el]==1)	$teams_table = "el_teams";
  elseif($league_data[el]==0) $teams_table = "2004teams";
  else $teams_table = "al_teams";
  if($sim==1) $teams_table = "el_simulation";
  $lid = $league_data[id];
  if($playoff_line>0) $pol = $playoff_line-1;
  
  // KHL a NHL
  if(strstr($league_data[longname], 'KHL') || strstr($league_data[longname], 'NHL'))
    {
    // konferencie
    if($table_type=="conference")
      {
      $show_clinch=1;
      $div1 = array_slice($teams, 0, count($teams)/2);
      $div2 = array_slice($teams, count($teams)/2);
      if(($key = array_search("XXX", $div1)) !== false) { unset($div1[$key]); }
      if(($key = array_search("XXX", $div2)) !== false) { unset($div2[$key]); }
      $div1_count = count($div1)-1;
      $div2_count = count($div2)-1;
      $dev = mysql_query("SELECT ($games_total-dt.zapasov)*$wpoints+body as ce FROM (SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$teams)."\") ORDER BY $orderby LIMIT $playoff_line,8)dt ORDER BY ce DESC LIMIT 1");
      $deviaty = mysql_fetch_array($dev);
      $osm = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$teams)."\") ORDER BY $orderby LIMIT ".$pol.",1");
      $osmy = mysql_fetch_array($osm);
      
      if(strstr($league_data[longname], 'NHL')) $lim=3;
      else $lim=0;
      
      if(strstr($league_data[longname], 'NHL')) $uloha = mysql_query("(SELECT dt.* FROM ((SELECT *, goals-ga as diff, 1 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div1)."\") ORDER BY $orderby LIMIT $lim)
UNION
(SELECT *, goals-ga as diff, 1 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div2)."\") ORDER BY $orderby LIMIT $lim))dt)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div1)."\") ORDER BY $orderby LIMIT $lim,$div1_count)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div2)."\") ORDER BY $orderby LIMIT $lim,$div2_count)
ORDER BY leader asc, $orderby");
      else $uloha = mysql_query("(SELECT *, goals-ga as diff, 2 as leader, ($games_total-zapasov)*$wpoints+body as points_doable FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div1)."\") ORDER BY $orderby)
UNION
(SELECT *, goals-ga as diff, 2 as leader, ($games_total-zapasov)*$wpoints+body as points_doable FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div2)."\") ORDER BY $orderby)
ORDER BY leader asc, $orderby");
      $out = Insert_to_table($table_name, $uloha, $league_data, $show_clinch, $playoff_line, $sim, $deviaty, $osmy, $games_total, $wpoints);
      }
    // divízie
    if($table_type=="division")
      {
      $show_clinch=0;
      $uloha = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$teams)."\") ORDER BY $orderby");
      if(!$npos) $out = Insert_to_table($table_name, $uloha, $league_data, $show_clinch, $playoff_line, $sim, $deviaty, $osmy, $games_total, $wpoints);
      else $a = Insert_to_table($table_name, $uloha, $league_data, $show_clinch, $playoff_line, $sim, $deviaty, $osmy, $games_total, $wpoints);
      }
    // liga
    if($table_type=="league")
      {
      $show_clinch=0;
      $div1 = array_slice($teams, 0, count($teams)/4);
      $div2 = array_slice($teams, count($teams)/4, count($teams)/4);
      $div3 = array_slice($teams, (count($teams)/4)*2, count($teams)/4);
      $div4 = array_slice($teams, (count($teams)/4)*3);
      if(($key = array_search("XXX", $div1)) !== false) { unset($div1[$key]); }
      if(($key = array_search("XXX", $div2)) !== false) { unset($div2[$key]); }
      if(($key = array_search("XXX", $div3)) !== false) { unset($div3[$key]); }
      if(($key = array_search("XXX", $div4)) !== false) { unset($div4[$key]); }
      $div1_count = count($div1)-1;
      $div2_count = count($div2)-1;
      $div3_count = count($div3)-1;
      $div4_count = count($div4)-1;
      $uloha = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$teams)."\") ORDER BY $orderby");
      if($games_total==3)
        {
        if(strstr(implode('","',$teams), "STL")) $uloha = mysql_query("SELECT * FROM (SELECT id, shortname, mediumname, longname, skupina, 3 as zapasov, 0 as w_basic, 3 as l_basic, 6 as gf_basic, 10 as ga_basic, 1 as p_basic, 0.662 as rsp FROM $teams_table WHERE shortname='STL' && league='$lid'
UNION
SELECT id, shortname, mediumname, longname, skupina, 3 as zapasov, 2 as w_basic, 1 as l_basic, 9 as gf_basic, 5 as ga_basic, 5 as p_basic, 0.657 as rsp FROM `el_teams` WHERE shortname='COL' && league='$lid'
UNION
SELECT id, shortname, mediumname, longname, skupina, 3 as zapasov, 3 as w_basic, 0 as l_basic, 15 as gf_basic, 10 as ga_basic, 6 as p_basic, 0.606 as rsp FROM `el_teams` WHERE shortname='VGK' && league='$lid'
UNION
SELECT id, shortname, mediumname, longname, skupina, 3 as zapasov, 1 as w_basic, 2 as l_basic, 5 as gf_basic, 10 as ga_basic, 2 as p_basic, 0.594 as rsp FROM `el_teams` WHERE shortname='DAL' && league='$lid')dt ORDER BY dt.p_basic DESC, dt.rsp DESC");
        else $uloha = mysql_query("SELECT * FROM (SELECT id, shortname, mediumname, longname, skupina, 3 as zapasov, 0 as w_basic, 3 as l_basic, 4 as gf_basic, 9 as ga_basic, 0 as p_basic, 0.714 as rsp FROM $teams_table WHERE shortname='BOS' && league='$lid'
UNION
SELECT id, shortname, mediumname, longname, skupina, 3 as zapasov, 2 as w_basic, 1 as l_basic, 7 as gf_basic, 8 as ga_basic, 4 as p_basic, 0.657 as rsp FROM `el_teams` WHERE shortname='TBL' && league='$lid'
UNION
SELECT id, shortname, mediumname, longname, skupina, 3 as zapasov, 1 as w_basic, 2 as l_basic, 5 as gf_basic, 7 as ga_basic, 3 as p_basic, 0.652 as rsp FROM `el_teams` WHERE shortname='WSH' && league='$lid'
UNION
SELECT id, shortname, mediumname, longname, skupina, 3 as zapasov, 3 as w_basic, 0 as l_basic, 11 as gf_basic, 3 as ga_basic, 6 as p_basic, 0.645 as rsp FROM `el_teams` WHERE shortname='PHI' && league='$lid')dt ORDER BY dt.p_basic DESC, dt.rsp DESC");
        }
      /* STARE RADENIE PODLA VEDUCICH DIVIZII 
      $uloha = mysql_query("(SELECT dt.* FROM ((SELECT *, goals-ga as diff, 1 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div1)."\") ORDER BY $orderby LIMIT 1)
UNION
(SELECT *, goals-ga as diff, 1 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div2)."\") ORDER BY $orderby LIMIT 1)
UNION
(SELECT *, goals-ga as diff, 1 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div3)."\") ORDER BY $orderby LIMIT 1)
UNION
(SELECT *, goals-ga as diff, 1 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div4)."\") ORDER BY $orderby LIMIT 1))dt)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div1)."\") ORDER BY $orderby LIMIT 1,$div1_count)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div2)."\") ORDER BY $orderby LIMIT 1,$div2_count)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div3)."\") ORDER BY $orderby LIMIT 1,$div3_count)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$div4)."\") ORDER BY $orderby LIMIT 1,$div4_count)
ORDER BY leader asc, $orderby");*/
      $out = Insert_to_table($table_name, $uloha, $league_data, $show_clinch, $playoff_line, $sim, $deviaty, $osmy, $games_total, $wpoints);
      }
    }
  // Tipsport Liga
  elseif($league_data[el]==1)
    {
    $show_clinch=1;
    $dev = mysql_query("SELECT ($games_total-dt.zapasov)*$wpoints+body as ce FROM (SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && shortname!='S20' ORDER BY $orderby LIMIT $playoff_line,8)dt ORDER BY ce DESC LIMIT 1");
    $deviaty = mysql_fetch_array($dev);
    $osm = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' ORDER BY $orderby LIMIT ".$pol.",1");
    $osmy = mysql_fetch_array($osm);
    $des = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' ORDER BY $orderby LIMIT 9,1");
    $desiaty = mysql_fetch_array($des);
    $uloha = mysql_query("(SELECT *, goals-ga as diff, 0 as hore FROM $teams_table WHERE league='$lid' && shortname!='S20')
UNION
(SELECT *, goals-ga as diff, 1 as hore FROM $teams_table WHERE league='$lid' && shortname='S20') ORDER BY hore asc, $orderby");
    $out = Insert_to_table($table_name, $uloha, $league_data, $show_clinch, $playoff_line, $sim, $deviaty, $osmy, $games_total, $wpoints, $desiaty);
    /*$dev = mysql_query("SELECT ($games_total-dt.zapasov)*$wpoints+body as ce FROM (SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$teams)."\") ORDER BY $orderby LIMIT $playoff_line,8)dt ORDER BY ce DESC LIMIT 1");
    $deviaty = mysql_fetch_array($dev);
    $osm = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$teams)."\") ORDER BY $orderby LIMIT ".$pol.",1");
    $osmy = mysql_fetch_array($osm);
    $uloha = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && shortname IN(\"".implode('","',$teams)."\") ORDER BY $orderby");
    $out = Insert_to_table($table_name, $uloha, $league_data, $show_clinch, $playoff_line, $sim, $deviaty, $osmy, $games_total, $wpoints);*/
    }
  // Mezdinárodný turnaj
  else
    {
    // tabuľka skupín
    if($table_type=="groups")
      {
      if($games_total>0) $show_clinch=1;
      else $show_clinch=0;
      $crop = explode("|", $league_data[groups]);
      $j=0;
      while ($j < count($crop))
        {
        $skup = $crop[$j];
        if($league_data[endbasic]==1)
          {
          $dev = mysql_query("SELECT ($games_total-(dt.w_basic+dt.l_basic))*$wpoints+p_basic as ce, dt.w_basic+dt.l_basic as gp_basic FROM (SELECT *, gf_basic-ga_basic as diff FROM $teams_table WHERE league='$lid' && skupina='$skup' ORDER BY p_basic desc, gp_basic asc, diff desc, gf_basic desc, w_basic desc, l_basic asc LIMIT $playoff_line,8)dt ORDER BY ce DESC LIMIT 1");
          $deviaty = mysql_fetch_array($dev);
          $osm = mysql_query("SELECT *, gf_basic-ga_basic as diff, w_basic+l_basic as gp_basic FROM $teams_table WHERE league='$lid' && skupina='$skup' ORDER BY p_basic desc, diff desc, gp_basic asc, gf_basic desc, w_basic desc, l_basic asc LIMIT ".$pol.",1");
          $osmy = mysql_fetch_array($osm);
          }
        else
          {
          $dev = mysql_query("SELECT ($games_total-dt.zapasov)*$wpoints+body as ce FROM (SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && skupina='$skup' ORDER BY body desc, diff desc, zapasov asc, goals desc, wins desc, losts asc LIMIT $playoff_line,8)dt ORDER BY ce DESC LIMIT 1");
          $deviaty = mysql_fetch_array($dev);
          $osm = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && skupina='$skup' ORDER BY body desc, diff desc, zapasov asc, goals desc, wins desc, losts asc LIMIT ".$pol.",1");
          $osmy = mysql_fetch_array($osm);
          }
        if($league_data[endbasic]==1) $uloha = mysql_query("SELECT *, gf_basic-ga_basic as diff FROM $teams_table WHERE league='$lid' && skupina='$skup' ORDER BY p_basic desc, diff desc, gf_basic desc, w_basic desc, l_basic asc");
        else $uloha = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' && skupina='$skup' ORDER BY body desc, zapasov asc, diff desc, goals desc, wins desc, losts asc");
        $table_name = LANG_TEAMTABLE_GROUP.' '.$skup;
        $out .= Insert_to_table($table_name, $uloha, $league_data, $show_clinch, $playoff_line, $sim, $deviaty, $osmy, $games_total, $wpoints);
        $j++;
        }
      }
    if($table_type=="league")
      {
      $show_clinch=0;
      $uloha = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$lid' ORDER BY $orderby");
      $out .= Insert_to_table($table_name, $uloha, $league_data, $show_clinch, $playoff_line, $sim, $deviaty, $osmy, $desiaty, $games_total, $wpoints);
      }
    }
  return $out;
  }
  
/*
* Funkcia pre vypísanie tabuľky
* version: 1.0.0 (24.1.2016 - vytvorenie novej funkcie)
* @param $table_name string - Nadpis tabuľky
* @param $uloha resource - dátová sada z databázy pre tabuľku
* @param $league_data resource - riadok z databázy o danej lige
* @param $show_clinch boolean - má sa zobrazovať dosiahnuteľnosť playoff? (0/1)
* @param $playoff_line integer - počet postupujúcich tímov do playoff (ak je 0, nezobrazí čiaru)
* @param $sim boolean - jedná sa o simuláciu všetkých zápasov? (0/1)
* @param $deviaty resource - riadok z databázy o prvom tíme pod čiarou
* @param $osmy resource - riadok z databázy o prvom tíme nad čiarou
* @param $games_total integer - počet zápasov jedného tímu v danej skupine
* @param $wpoints integer - počet bodov za výhru
* @return $ttable string
*/
  
function Insert_to_table($table_name, $uloha, $league_data, $show_clinch, $playoff_line, $sim, $deviaty, $osmy, $games_total, $wpoints, $desiaty=false)
  {
  Global $leaderwas, $clinchwas, $cannotwas, $relegwas, $npos, $leaguecolor, $json;
  if($playoff_line>0) $pol = $playoff_line-1;
  if(!$json) $ttable .= '<div class="card my-4 shadow animated--grow-in">
  <div class="card-header">
    <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
      '.$table_name.'
      <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
    </h6>
  </div>
  <div class="card-body">
  <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid">
    <thead>
      <tr>
        <th>#</th>
        <th style="width: 40%;">'.LANG_PLAYERSTATS_TEAM.'</th>
        <th class="text-center">'.LANG_TEAMSTATS_MATCHES.'</th>
        <th class="text-center">'.LANG_TEAMSTATS_WINS.'</th>
        <th class="text-center">'.LANG_TEAMSTATS_LOSTS.'</th>
        <th class="text-center">'.LANG_TEAMSTATS_SCORE.'</th>
        <th class="text-center">'.LANG_TEAMSTATS_POINTS.'</th>
      </tr>
    </thead>
    <tbody>';
  
  if(strstr($table_name, LANG_TEAMTABLE_GROUP))
    {
    $reord = h2h_reorder($uloha, $league_data[id], $league_data);
    $prem = count($reord);
    $rtp=1;
    }
  else
    {
    $prem = mysql_num_rows($uloha);
    $rtp=0;
    }
    
  // favorite team
  if($_SESSION[logged])
    {
    $q = mysql_query("SELECT user_favteam FROM e_xoops_users WHERE uid='$_SESSION[logged]'");
    $f = mysql_fetch_array($q);
    }
  
  $i=0;
  $p=1;
  while ($i < $prem)
    {
    if($rtp==0) $data = mysql_fetch_array($uloha);
    else { $exp=explode(":",$reord[$i][5]); $data[wins]=$reord[$i][2]; $data[w_basic]=$reord[$i][2]; $data[losts]=$reord[$i][3]; $data[l_basic]=$reord[$i][3]; $data[goals]=$exp[0]; $data[gf_basic]=$exp[0]; $data[ga]=$exp[1]; $data[ga_basic]=$exp[1]; $data[body]=$reord[$i][6]; $data[p_basic]=$reord[$i][6]; $data[shortname]=$reord[$i][0]; $data[longname]=$reord[$i][1]; $data[zapasov]=$reord[$i][8]; $data[id]=$reord[$i][9]; }
    $clinch=$bs=$be=$fav=$leader=$line="";
       
    if($_SESSION[lang] != 'sk') $data[longname] = TeamParser($data[longname]);
    if(!$data[mediumname] || $rtp==1) $data[mediumname]=$data[longname];
    $wins = $data[wins];
    $losts = $data[losts];
    $goals = "$data[goals]:$data[ga]";
    $points = $data[body];
    if($league_data[endbasic]==1) { $wins = $data[w_basic]; $losts = $data[l_basic]; $goals = "$data[gf_basic]:$data[ga_basic]"; $points = $data[p_basic]; }
    if($league_data[el]==0 && $league_data[endbasic]==1) { $data[zapasov] = $data[w_basic]+$data[l_basic]; $osmy[body] = $osmy[p_basic]; }
    // favorite team
    if($f[user_favteam]!="0" && $f[user_favteam]==$data[shortname]) $fav=" class='bg-gray-400'";
    // division leader
    if($data[leader]==1) { $leader="*"; $leaderwas=1; }
    // play-off line
    if($show_clinch==1 && $playoff_line>0 && $i==$pol) $line=" style='border-bottom:1px dashed black !important;'";
    if($show_clinch==1 && $playoff_line>0 && $i==9 && strstr($league_data[longname], "Tipos")) $line=" style='border-bottom:1px dashed black !important;'";
    // relegation line
    //if(strstr($league_data[longname], "MS") && $show_clinch==1 && $playoff_line>0 && $i==6) $line=" style='border-bottom:1px dashed black !important;'";
    // clinched playoff
    if($show_clinch==1 && $sim!=1 && $points > $deviaty[ce] && $p<=$playoff_line) { $bs="<span class='font-weight-bold'>"; $be="</span>"; $clinch = "<sup><span class='text-success font-weight-bold'>x</span></sup>"; $clinchwas=1; }
    if(!$json) $ttable .= "<tr$fav><td class='text-center'$line>$p.</td><td class='text-nowrap'$line><img class='flag-".($league_data[el]==0 ? 'iihf':'el')." ".$data[shortname]."-small' src='/img/blank.png' alt='".$data[longname]."'> $leader<a href='/team/".$data[id].$league_data[el]."-".SEOtitle($data[longname]);
    else 
      {
      $ttable["conference"][$table_name][$p]["shortname"] = $data[shortname];
      $ttable["conference"][$table_name][$p]["longname"] = iconv("cp1250", "utf-8", $data[longname]);
      $ttable["conference"][$table_name][$p]["gp"] = $data[zapasov];
      $ttable["conference"][$table_name][$p]["wins"] = $wins;
      $ttable["conference"][$table_name][$p]["losts"] = $losts;
      $ttable["conference"][$table_name][$p]["score"] = $goals;
      $ttable["conference"][$table_name][$p]["points"] = $points;
      }
    // cannot make playoff's
    if($show_clinch==1 && $sim!=1 && strstr($league_data[longname], "Tipos") && (($games_total-$data[zapasov])*$wpoints)+$points < $desiaty[body] || $data[shortname]=="S20" && $sim!=1 && $show_clinch==1 && strstr($league_data[longname], "Tipos") || $show_clinch==1 && $sim!=1 && $games_total-$data[zapasov]==0 && $p>$playoff_line && strstr($league_data[longname], "Tipos")) { $bs="<span class='font-italic'>"; $be="</span>"; $clinch = "<sup><span class='text-danger font-weight-bold'>y</span></sup>"; $cannotwas=1; }
    if($show_clinch==1 && $sim!=1 && !strstr($league_data[longname], "Tipos") && (($games_total-$data[zapasov])*$wpoints)+$points < $osmy[body] || $data[shortname]=="S20" && $sim!=1 && $show_clinch==1 && !strstr($league_data[longname], "Tipos") || $show_clinch==1 && $sim!=1 && $games_total-$data[zapasov]==0 && $p>$playoff_line && !strstr($league_data[longname], "Tipos")) { $bs="<span class='font-italic'>"; $be="</span>"; $clinch = "<sup><span class='text-danger font-weight-bold'>y</span></sup>"; $cannotwas=1; }
    // relegated to DIV.I
    //if(strstr($league_data[longname], "MS") && $show_clinch==1 && (($games_total-$data[zapasov])*$wpoints)+$data[body] < $reord[6][6] || strstr($league_data[longname], "MS") && $show_clinch==1 && ($data[zapasov]==7 && $i==7)) { $bs="<i>"; $be="</i>"; $clinch = "<sup><span class='text-primary font-weight-bold'>z</span></sup>"; $relegwas=1; }
    if(!$json) $ttable .= "'>$bs<span class='d-none d-md-inline'>$data[longname]</span><span class='d-inline d-md-none'>$data[mediumname]</span>$be</a> $clinch</td><td class='text-center'$line>$data[zapasov]</td><td class='text-center'$line>$wins</td><td class='text-center'$line>$losts</td><td class='text-center'$line>$goals</td><td class='text-center'$line><span class='font-weight-bold'>$points</span></td></tr>";
    else
      {
      if($clinchwas==1 || $cannotwas==1 || $relegwas==1) $ttable["conference"][$table_name][$p]["clinch"] = trim(strip_tags($clinch));
      else $ttable["conference"][$table_name][$p]["clinch"] = 0;
      }
    if($npos==$data[shortname] && $league_data[el]==1) { $a = "$p|$games_total|$wpoints"; return $a; break; }
    if($npos==$data[shortname] && $league_data[el]==0) { return $p; break; }
    $i++;
    $p++;
    }	
  if(!$json) $ttable .= "</tbody></table></div></div>";
  else $ttable = json_encode($ttable, JSON_UNESCAPED_UNICODE);

  if(!$npos) return $ttable;
  }
  
/*
* Funkcia pre zoradenie tímov podľa vzájomného zápasu v prípade rovnosti bodov v tabuľke
* version: 1.0.1 (24.1.2016 - prispôsobené pre novú stránku)
* @param $uloha resource - dátová sada z databázy pre tabuľku
* @param $lid integer - ID danej ligy
* @param $league_data resource - riadok z databázy o danej lige
* @return $reord array
*/
  
function h2h_reorder($uloha, $lid, $league_data)
  {
  $m=0;
  while($data = mysql_fetch_array($uloha))
    {
    $posun=0;
    if($league_data[endbasic]==1) $np = $data[p_basic];
    else $np = $data[body];
    if($points==$np)
      {
      $vzaj = mysql_query("SELECT IF(team1short='$data[shortname]',IF(goals1>goals2,1,0),IF(goals1>goals2,0,1)) as posun FROM 2004matches WHERE (team1short='$data[shortname]' && team2short='$tshort' || team1short='$tshort' && team2short='$data[shortname]') && league='$lid' && kedy='konečný stav'");
      $vzajom = mysql_fetch_array($vzaj);
      if($vzajom[posun]==1)
        {
        $mm = $m-1;
        $reord[$m] = $reord[$mm];
        $m--;
        $posun=1;
        }
      }
    $games = $data[zapasov];
    $wins = $data[wins];
    $losts = $data[losts];
    $goals = "$data[goals]:$data[ga]";
    $points = $data[body];
    $tshort = $data[shortname];
    $tid = $data[id];
    if($league_data[endbasic]==1) { $wins = $data[w_basic]; $losts = $data[l_basic]; $goals = "$data[gf_basic]:$data[ga_basic]"; $points = $data[p_basic]; $games=$data[w_basic]+$data[l_basic]; }
    $reord[$m] = array($data[shortname], $data[longname], $wins, $losts, $ties, $goals, $points, $data[league], $games, $tid);
    $m++;
    if($posun==1) $m++;
    }
  return $reord;
  }
  
/*
* Funkcia pre vypísanie aktuálneho playoff
* version: 1.0.0 (18.2.2016 - vytvorenie novej funkcie)
* @param $league_data resource - riadok z databázy o danej lige
* @param $po_type string - nazov casti playoff na zobrazenie (stvrt, semi, final, stanley)
* @param $playoff_wins integer - na kolko vitaznych zapasov sa hra seria
* @return $out string
*/

function Render_Playoff($league_data, $po_type, $playoff_wins)
  {
  $out .= '<div class="playoff">';
  if((strstr($league_data[longname], 'KHL') || strstr($league_data[longname], 'NHL')) && $po_type!="stanley") $conferences=true;
  if($conferences)
    {
    $out .= '<div class="h5 h5-fluid">'.LANG_TEAMTABLE_WESTCONF1.'</div>
             <div class="row">';
    $out .= Render_Playoff_Boxes($league_data, $po_type, $playoff_wins, 1);
    $out .= '</div>';
    $out .= '<div class="h5 h5-fluid">'.LANG_TEAMTABLE_EASTCONF1.'</div>
             <div class="row">';
    $out .= Render_Playoff_Boxes($league_data, $po_type, $playoff_wins, 2);
    $out .= '</div>';
    }
  else
    {
    $out .= '<div class="row">';
    $out .= Render_Playoff_Boxes($league_data, $po_type, $playoff_wins);
    $out .= '</div>';
    }
  $out .= '</div>';
  return $out;
  }
  
/*
* Funkcia pre vypísanie aktuálnych playoff dvojíc
* version: 1.0.0 (19.2.2016 - vytvorenie novej funkcie)
* @param $league_data resource - riadok z databázy o danej lige
* @param $po_type string - nazov casti playoff na zobrazenie (stvrt, semi, final, stanley)
* @param $playoff_wins integer - na kolko vitaznych zapasov sa hra seria
* @param $conf integer - cislo konferencie (1-zapadna, 2-vychodna)
* @return $out string
*/

function Render_Playoff_Boxes($league_data, $po_type, $playoff_wins, $conf=FALSE)
  {
  $uid = $_SESSION['logged'];
  if($league_data[el]==0)
    {
    if($po_type=="qualify") { $sel="Q"; $flex="25"; }
    if($po_type=="quarter") { $sel="QF"; $flex="25"; }
    if($po_type=="semi") { $sel="SF"; $flex="50"; }
    if($po_type=="bronze") { $sel="B"; $flex="100"; }
    if($po_type=="final") { $sel="F"; $flex="100"; }
    if($po_type) 
      {
      if($uid) $w = mysql_query("SELECT *, dt.tip1, dt.tip2 FROM 2004matches LEFT JOIN (SELECT matchid, userid, tip1, tip2, komentar FROM 2004tips WHERE userid='$uid')dt ON (dt.matchid=2004matches.id) WHERE league='$league_data[id]' && po_type='$sel' ORDER BY datetime ASC");
      else $w = mysql_query("SELECT * FROM 2004matches WHERE league='$league_data[id]' && po_type='$sel' ORDER BY datetime ASC");
      }
    else 
      {
      $q = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
      $q = mysql_query("SELECT po_type FROM 2004matches WHERE league='$league_data[id]' GROUP BY po_type ORDER BY id DESC LIMIT 1");
      $f = mysql_fetch_array($q);
      if($uid) $w = mysql_query("SELECT *, dt.tip1, dt.tip2 FROM 2004matches LEFT JOIN (SELECT matchid, userid, tip1, tip2, komentar FROM 2004tips WHERE userid='$uid')dt ON (dt.matchid=2004matches.id) WHERE league='$league_data[id]' && po_type='$f[po_type]' ORDER BY datetime ASC");
      else $w = mysql_query("SELECT * FROM 2004matches WHERE league='$league_data[id]' && po_type='$f[po_type]' ORDER BY datetime ASC");
      }
    }
  else
    {
    if(($po_type=="stvrt" || $po_type=="baraz") && $conf==1) $limit=" LIMIT 0,4";
    if($po_type=="semi" && $conf==1) $limit=" LIMIT 0,2";
    if($po_type=="final" && $conf==1) $limit=" LIMIT 0,1";
    if(($po_type=="stvrt" || $po_type=="baraz") && $conf==2) $limit=" LIMIT 4,4";
    if($po_type=="semi" && $conf==2) $limit=" LIMIT 2,2";
    if($po_type=="final" && $conf==2) $limit=" LIMIT 1,1";
    if($po_type=="baraz") { $flex="50"; $hl=LANG_QUALIFYROUND; }
    elseif($po_type=="stvrt") { $flex="25"; $hl=LANG_QUARTERFINAL; }
    elseif($po_type=="semi") { $flex="50"; $hl=LANG_SEMIFINAL; }
    elseif($po_type=="final") { $flex="100"; $hl=LANG_FINAL; $noshow=1; }
    elseif($po_type=="stanley" && strstr($league_data[longname], 'NHL')) { $flex="100"; $hl=LANG_STANLEY; $noshow=1; }
    elseif($po_type=="stanley" && strstr($league_data[longname], 'KHL')) { $flex="100"; $hl=LANG_GAGARIN; $noshow=1; }
    else $flex="100";
    $w = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
    $w = mysql_query("SELECT el_playoff.*, t1.mediumname as t1, t2.mediumname as t2 FROM el_playoff JOIN el_teams t1 ON (el_playoff.team1=t1.shortname && el_playoff.league=t1.league) JOIN el_teams t2 ON (el_playoff.team2=t2.shortname && el_playoff.league=t2.league) WHERE el_playoff.league='$league_data[id]' && el_playoff.potype='$po_type' GROUP BY id ORDER BY el_playoff.id ASC$limit");
    }
  $k=1;
  while($e = mysql_fetch_array($w))
    {
    $los1=$los2=$opt=$bets=$bckg="";
    if($league_data[el]==1)
      {
      if($e[status1]==$playoff_wins) $los2=" loser";
      if($e[status2]==$playoff_wins) $los1=" loser";
      }
    else
      {
      if($e[kedy]=="konečný stav" && $e[goals1]>$e[goals2]) $los2=" loser";
      if($e[kedy]=="konečný stav" && $e[goals1]<$e[goals2]) $los1=" loser";
      $e[team1]=$e[team1short];
      $e[team2]=$e[team2short];
      $e[t1]=$e[team1long];
      $e[t2]=$e[team2long];
      if($_SESSION[lang]!='sk') { $e[t1] = TeamParser($e[t1]); $e[t2] = TeamParser($e[t2]); }
      $e[status1]=$e[goals1];
      $e[status2]=$e[goals2];
      if($e[kedy]=="na programe") $e[status1]=$e[status2]="";
      $suffix = " shadow-sm";
      if($e[kedy]=="konečný stav") $opt .= '<a href="/report/'.$e[id].$league_data[el].'-'.SEOtitle($e[t1]." vs ".$e[t2]).'" class="btn btn-sm btn-light btn-icon-split">
                                              <span class="icon text-gray-600">
                                                <i class="fas fa-ellipsis-h"></i>
                                              </span>
                                              <span class="text text-gray-800">'.LANG_TEAMSTATS_INDETAIL.'</span>
                                            </a>';
      elseif($e[active]==1) 
        {
        // je live
        $opt .= '<a href="/report/'.$e[id].$league_data[el].'-'.SEOtitle($e[t1]." vs ".$e[t2]).'" class="btn btn-sm btn-light btn-icon-split">
                  <span class="icon text-gray-600">
                    <i class="fas fa-comments"></i>
                  </span>
                  <span class="text text-gray-800">'.LANG_NAV_LIVE.'</span>
                </a>';
        $bckg = " bg-warning";
        }
      else $opt .= '<a href="/game/'.$e[id].$league_data[el].'-'.SEOtitle($e[t1]." vs ".$e[t2]).'" class="btn btn-sm btn-light btn-icon-split">
                      <span class="icon text-gray-600">
                        <i class="fas fa-search"></i>
                      </span>
                      <span class="text text-gray-800">'.LANG_MATCHES_DETAIL.'</span>
                    </a>';
      // tipy
      if($uid && strtotime($e[datetime]) > mktime())
        {
        $bets = '<div class="row align-items-center bg-light border mb-4 no-gutters py-3 rounded " id="bet-'.$k.'">
                  <div class="col-12 font-weight-bold small text-center">'.LANG_MATCHES_PLACEBET.'</div>
                  <div class="col-6 text-center">
                    <div class="text-xs font-weight-bold">'.LANG_TEAMSTATS_SCORE.' 1</div>
                    <select id="tip1-'.$e[id].$league_data[el].'">'.Generate_Bet_List($e[tip1]).'</select>
                  </div>
                  <div class="col-6 text-center">
                    <div class="text-xs font-weight-bold">'.LANG_TEAMSTATS_SCORE.' 2</div>
                    <select id="tip2-'.$e[id].$league_data[el].'">'.Generate_Bet_List($e[tip2]).'</select>
                  </div>
                </div>';
        }
      }
    $out .= '<div class="col-12 col-sm-6 col-lg-3 animated--grow-in mb-3">
                  <div class="card shadow h-100">
                    <div class="card-header row no-gutters">
                      <div class="col text-xs font-weight-bold">
                        '.($league_data[el]==0 ? date("j.n.Y", strtotime($e[datetime])) : $hl).'
                      </div>
                      <div class="col text-xs font-weight-bold text-right">
                        '.($noshow==1 ? "" : ($league_data[el]==0 ? date("G:i", strtotime($e[datetime])) : LANG_NUM.$k)).'
                      </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                      <div class="row mb-4 align-items-center">
                        <div class="col-6 text-center'.$los1.'">
                          <img src="/images/vlajky/'.$e[team1].'.gif" alt="'.$e[t1].'" class="img-fluid'.$suffix.'">
                          <div class="h6 h6-fluid mb-0 mt-1 font-weight-bold text-gray-800">'.$e[t1].'</div>
                        </div>
                        <div class="col-6 text-center'.$los2.'">
                          <img src="/images/vlajky/'.$e[team2].'.gif" alt="'.$e[t2].'" class="img-fluid'.$suffix.'">
                          <div class="h6 h6-fluid mb-0 mt-1 font-weight-bold text-gray-800">'.$e[t2].'</div>
                        </div>
                      </div>
                      <div class="row mb-2 align-items-center"><div class="col-6 text-center h1 h1-fluid">'.$e[status1].'</div><div class="col-6 text-center h1 h1-fluid">'.$e[status2].'</div></div>
                      '.($league_data[el]==0 ? "" : Render_Playoff_Matches($league_data, $conf.$k, $e[team1], $e[team2], $e[status1], $e[status2], $po_type, $playoff_wins)).'
                      '.$bets.'
                      <div class="row no-gutters flex-fill align-items-end">
                        <div class="w-100 text-center">'.$opt.'</div>
                      </div>
                    </div>
                  </div>
                 </div>';
    $k++;
    }
  return $out;
  }
  
function Generate_Bet_List($sel)
  {
  $betlist = "<option name='-' value='-'"; if($sel==NULL) $betlist .= " selected"; $betlist .=">-</option>";
  $i=0;
  while($i <= 14)
    {
    $betlist .= "<option name='$i' value='$i'"; if($sel=="$i") $betlist .= " selected"; $betlist .=">$i</option>";
    $i++;
    }
  return $betlist;
  }

/*
* Funkcia pre vypísanie odohratých zápasov v jednotlivých sériách
* version: 1.1.1 (27.2.2019 - pridanie po_type kvoli kvalifikacii a zapasom na 3 vitazne)
* @param $league_data resource - riadok z databázy o danej lige
* @param $box_id integer - ID boxu, v ktorom sa budu zapasy vysuvat
* @param $team1 string - shortname prveho timu
* @param $team2 string - shortname druheho timu
* @param $status1 integer - stav serie prvy tim
* @param $status2 integer - stav serie druhy tim
* @param $status2 integer - stav serie druhy tim
* @param $po_type string - nazov casti playoff (stvrt, semi, final, stanley)
* @param $playoff_wins integer - na kolko vitaznych zapasov sa hra seria
* @return $zapasy string
*/
function Render_Playoff_Matches($league_data, $box_id, $team1, $team2, $status1, $status2, $po_type, $playoff_wins)
  {
  $zapasy = "";
  if($status1 > $status2)
    {
    $kopa = $status1+$status2;
    $zapasov = $playoff_wins-$status1+$kopa;
    }
  if($status1 < $status2)
    {
    $kopa = $status1+$status2;
    $zapasov = $playoff_wins-$status2+$kopa;
    }
  if($status1 == $status2)
    {
    $kopa = $status1+$status2;
    $zapasov = $playoff_wins-$status1+$kopa;
    }
  $zapasy .= "<table class='table-hover table-light table-striped w-100 small'>
              <thead>
                <tr>
                  <th>".LANG_DATE."</th>
                  <th>".LANG_MATCH1."</th>
                  <th class='text-center'>".LANG_MATCHES_STATUS."</th>
                </tr>
              </thead>
              <tbody>";
  $u = mysql_query("SELECT *, DATE_FORMAT(datetime, '%e.%c.') as datum FROM el_matches WHERE (team1short='$team1' && team2short='$team2' && kolo='0' && league='$league_data[id]') || (team1short='$team2' && team2short='$team1' && kolo='0' && league='$league_data[id]') ORDER BY datetime ASC");
  if(mysql_num_rows($u)==0) $zapasy .= '<tr><td colspan="3" class="border-left-secondary p-2"><i class="far fa-clock"></i> '.LANG_TEAMTABLE_SOON.'...</td></tr>';
  else
    {
    while ($j <= $zapasov-1)
      {
      $o = mysql_fetch_array($u);
      $l=$j+1;
      if($o[kedy]!="na programe") $goals="<a href='/report/".$o[id]."1-".SEOtitle($o[team1long]." vs ".$o[team2long])."' class='font-weight-bold'>$o[goals1]:$o[goals2]</a>";
      else $goals="<a href='/game/".$o[id]."1-".SEOtitle($o[team1long]." vs ".$o[team2long])."' class='badge badge-pill badge-secondary'>".date("H:i", strtotime($o[datetime]))."</a>";
      $zapasy .= "<tr>
                    <td>$o[datum]</td>
                    <td><img class='d-none d-sm-inline flag-".($league_data[el]==0 ? 'iihf':'el')." ".$o[team1short]."-small' src='/images/blank.png' alt='".$o[team1long]."'> <b>$o[team1short]</b> vs. <b>$o[team2short]</b> <img class='d-none d-sm-inline flag-".($league_data[el]==0 ? 'iihf':'el')." ".$o[team2short]."-small' src='/images/blank.png' alt='".$o[team2long]."'></td>
                    <td class='text-center'>$goals</td>
                  </tr>";
      $j++;
      }
    }
  $zapasy .= "</tbody></table>";
  return $zapasy;	
  }
?>