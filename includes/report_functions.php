<?
/*
* Funkcia pre generovanie rozšírených štatistík NHL zápasov
* version: 1.0.0 (11.11.2016 - prekopaná stará funkcia pre potreby novej verzie stránky)
* @param $mid integer - ID zápasu aj s EL identifikátorom
* @param $el integer - extraliga 0/1
* @return $estats string
*/
function Generate_extrastats($mid, $el) {
  Global $f,$estats;

  if($el==1) {
    $matchstats_table = "el_matchstats";
    $matches_table = "el_matches";
    }
  else {
    $matchstats_table = "2004matchstats";
    $matches_table = "2004matches";
  }
  $e = mysql_query("SELECT t1.*,t2.league, t3.longname FROM $matchstats_table t1 JOIN $matches_table t2 ON t2.id=t1.matchid JOIN 2004leagues t3 ON t3.id=t2.league WHERE matchid='$mid'");
  $es = mysql_fetch_array($e);
  if(strstr($es[longname], "KHL")) include("xadm/2005/frames/khl_name_parser.php");
  if(strstr($es[longname], "NHL")) include("xadm/2005/frames/nhl_name_parser.php");
  if($el==0) include("xadm/2005/frames/iihf_name_parser.php");
  
  $es[goalie2] = json_decode($es[goalie2]);
  $es[goalie1] = json_decode($es[goalie1]);
  $es[g1_goals] = json_decode($es[g1_goals]);
  $es[g1_shots] = json_decode($es[g1_shots]);
  $es[g2_goals] = json_decode($es[g2_goals]);
  $es[g2_shots] = json_decode($es[g2_shots]);
  $es[s1p1] = json_decode($es[s1p1]);
  $es[s1p2] = json_decode($es[s1p2]);
  $es[s1p3] = json_decode($es[s1p3]);
  $es[s1ot] = json_decode($es[s1ot]);
  $es[s2p1] = json_decode($es[s2p1]);
  $es[s2p2] = json_decode($es[s2p2]);
  $es[s2p3] = json_decode($es[s2p3]);
  $es[s2ot] = json_decode($es[s2ot]);
  $ot=0;
  $i=0;
  while($i < count($es[goalie1])) { $ot=$ot+$es[s1ot][$i]; $i++; }
  $i=0;
  while($i < count($es[goalie2])) { $ot=$ot+$es[s2ot][$i]; $i++; }
  if(count($es[goalie1])>1) $prid1 = ' rowspan="'.count($es[goalie1]).'"';
  if(count($es[goalie2])>1) $prid2 = ' rowspan="'.count($es[goalie2]).'"';
  $rs = count($es[goalie1])+count($es[goalie2]);
  
  $estats = '<div class="card my-4 shadow animated--grow-in">
                  <div class="card-body">
                    <p class="swipe d-none mb-1 text-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></p>
                    <div class="h5 h5-fluid">'.LANG_REPORT_GAMESTATS.'</div>
                    <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid"">
                      <thead>
                        <tr>
                          <th colspan="3"></th>
                          <th colspan="'.($ot>0 ? '4':'3').'" class="text-center">'.LANG_REPORT_SAVES.'</th>
                          <th colspan="2"></th>
                        </tr>
                        <tr>
                          <th></th>
                          <th class="text-nowrap">'.LANG_FANTASY_GOALIE.'</th>
                          <th class="text-center">'.LANG_TEAMSTATS_GOALS.'/'.LANG_REPORT_SHOTS.'</th>
                          <th class="text-center">1</th>
                          <th class="text-center">2</th>
                          <th class="text-center">3</th>
                          '.($ot>0 ? '<th class="text-center">OT</th>':'').'
                          <th class="text-center">'.LANG_REPORT_FACEOFFS.'</th>
                          <th class="text-center">'.LANG_REPORT_SPECTATORS.'</th>
                        </tr>
                  </thead>
                  <tbody>';
  $i=0;
  while($i < count($es[goalie1]))
    {
    if(!strstr($es[longname], "KHL") && !strstr($es[longname], "NHL") && $el!=0) $goalie = $es[goalie1][$i];
    else $goalie = ParseName($es[goalie1][$i]);
    if($i==0) $estats .= '<tr><td class="align-top" style="width:21%;"'.$prid1.'><b>'.$f[team1long].'</b></td>';
    else $estats .= '<tr>';
    $estats .= '<td class="text-nowrap" style="width:27%;">'.$goalie.'</td><td class="text-center" style="width:12%;">'.$es[g1_goals][$i].'/'.$es[g1_shots][$i].'</td><td class="text-center" style="width:3%;">'.$es[s1p1][$i].'</td><td class="text-center" style="width:3%;">'.$es[s1p2][$i].'</td><td class="text-center" style="width:3%;">'.$es[s1p3][$i].'</td>';
    if($ot>0) $estats .= '<td class="text-center'.($prid1 ? ' pr-1':'').'" style="width:3%;">'.$es[s1ot][$i].'</td>';
    if($i==0) $estats .= '<td class="text-center align-top'.($prid1 ? ' pr-1':'').'" style="width:18%;"'.$prid1.'>'.$es[fo1].'</td><td rowspan="'.$rs.'" class="text-center align-middle" style="width:10%;">'.$es[attendance].'</td></tr>';
    else $estats .= '</tr>';
    $i++;
    }
  
  $estats .= '';
  
    $i=0;
  while($i < count($es[goalie2]))
    {
    if(!strstr($es[longname], "KHL") && !strstr($es[longname], "NHL") && $el!=0) $goalie = $es[goalie2][$i];
    else $goalie = ParseName($es[goalie2][$i]);
    if($i==0) $estats .= '<tr><td class="align-top" style="width:21%;"'.$prid2.'><b>'.$f[team2long].'</b></td>';
    else $estats .= '<tr>';
    $estats .= '<td class="text-nowrap" style="width:27%;">'.$goalie.'</td><td class="text-center" style="width:12%;">'.$es[g2_goals][$i].'/'.$es[g2_shots][$i].'</td><td class="text-center" style="width:3%;">'.$es[s2p1][$i].'</td><td class="text-center" style="width:3%;">'.$es[s2p2][$i].'</td><td class="text-center" style="width:3%;">'.$es[s2p3][$i].'</td>';
    if($ot>0) $estats .= '<td class="text-center'.($prid2 ? ' pr-1':'').'" style="width:3%;">'.$es[s2ot][$i].'</td>';
    if($i==0) $estats .= '<td class="text-center pr-1 align-top" style="width:18%;"'.$prid2.'>'.$es[fo2].'</td></tr>';
    else $estats .= '</tr>';
    $i++;
    }
    
   $estats .= '</tbody></table>
            </div>
          </div>';
  
  return $estats;
  }
?>