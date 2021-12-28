<?
if($_GET[lid]) 
  {
  $params = explode("/", htmlspecialchars($_GET[lid]));
  $lid = explode("-", htmlspecialchars($params[0]));
  $lid=$lid[0];
  if($params[1]!="")
    {
    if(is_numeric($params[1]) || strstr($params[1], "-")) $_GET[sel]=htmlspecialchars($params[1]);
    else { $_GET[sel]="0"; $potype=htmlspecialchars($params[1]); }
    }
  }
if($_GET[gid]) { $gid = explode("-", htmlspecialchars($_GET[gid])); $gid=$gid[0]; }

$content = "";
// vypisat zoznam zapasov danej ligy
if($lid)
  {
  $active_league = $lid;
  $content .= Get_matches($lid, $params, $_GET[sel], $potype);
  }
// detail zapasu
elseif($gid)
  {
	$el = substr($gid, -1);
	$dl = strlen($gid);
	$ide = substr($gid, 0, $dl-1);
	if($el==1)
    {
    $matches_table = "el_matches";
    $tips_table = "el_tips";
    $teams_table = "el_teams";
    $players_table = "el_players";
    $goals_table = "el_goals";
    }
	elseif($el==0)
    {
    $el="0";
    $matches_table = "2004matches";
    $tips_table = "2004tips";
    $teams_table = "2004teams";
    $players_table = "2004players";
    $goals_table = "2004goals";
    }
	else
    {
    $el="3";
    $matches_table = "al_matches";
    $tips_table = "al_tips";
    $teams_table = "al_teams";
    $players_table = "al_players";
    $goals_table = "al_goals";
    }
	$a = mysql_query("SELECT * FROM $matches_table WHERE id='$ide'");
	if(mysql_num_rows($a)>0)
    {
    $b = mysql_fetch_array($a);
    $c = mysql_query("SELECT *, dt.arena FROM $teams_table JOIN (SELECT * FROM el_infos)dt ON shortname=dt.teamshort WHERE shortname='$b[team1short]' && league='$b[league]'");
    $t1 = mysql_fetch_array($c);
    $d = mysql_query("SELECT * FROM $teams_table WHERE shortname='$b[team2short]' && league='$b[league]'");
    $t2 = mysql_fetch_array($d);
    $e = mysql_query("SELECT name, goals, asists FROM $players_table WHERE teamshort='$b[team1short]' && league='$b[league]' ORDER BY points DESC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC LIMIT 1");
    $p1 = mysql_fetch_array($e);
    $f = mysql_query("SELECT name, goals, asists FROM $players_table WHERE teamshort='$b[team2short]' && league='$b[league]' ORDER BY points DESC, goals DESC, asists DESC, gwg DESC, gtg DESC, shg DESC, ppg DESC, penalty ASC LIMIT 1");
    $p2 = mysql_fetch_array($f);
    $g = mysql_query("SELECT count(id) as poc, ROUND(sum(tip1)/count(id),2) as vys1, ROUND(sum(tip2)/count(id),2) as vys2 FROM $tips_table WHERE matchid='$b[id]'");
    $h = mysql_fetch_array($g);
    if($el==0) $suffix = ' shadow-sm';
    if($b[kedy]!="na programe") { $g1 = '<div class="goals">'.$b[goals1].'</div>'; $g2 = '<div class="goals">'.$b[goals2].'</div>'; }
    $linf = mysql_query("SELECT * FROM 2004leagues WHERE id='$b[league]'");
    $linfo = mysql_fetch_array($linf);
    if(strstr($linfo[longname],"U20")) $lowerdiv="U20";
    if($_SESSION[lang]!='sk') { $kedy = StatusParser($b[kedy]); $t1[longname] = TeamParser($t1[longname]); $t2[longname] = TeamParser($t2[longname]); }
    else $kedy = $b[kedy];
    $leaguecolor = $linfo[color];
    $active_league = $b[league];
    $title = LANG_MATCHES_DETAIL1.' '.$t1[longname].' vs. '.$t2[longname];
    $content .= '<i class="float-left h1 h1-fluid ll-'.LeagueFont($linfo[longname]).' text-gray-600 mr-1"></i>
               <h1 class="h3 h3-fluid mb-1">'.LANG_MATCHES_DETAIL1.' '.$t1[longname].' vs. '.$t2[longname].'</h1>
               <h2 class="h6 h6-fluid text-'.$leaguecolor.' text-uppercase font-weight-bold mb-3">'.$linfo[longname].'</h2>
                <div class="row">
                    <div class="col-12 match-detail" style="max-width: 1000px;">
                <div class="row my-4">
                  <div class="col-6 col-md-4 d-flex flex-column justify-content-between order-1 text-center animated--grow-in">
                    <div><img src="/images/vlajky/'.$b[team1short].'_big.gif" alt="'.$b[team1long].'" class="img-fluid'.$suffix.'"></div>
                    <div class="h5 h5-fluid mb-0 mt-1 font-weight-bold text-gray-800">'.$t1[longname].'</div>
                    '.($b[kedy]=="konečný stav" ? '<p class="display-3"><b>'.$b[goals1].'</b></p>':'').'
                  </div>
                  <div class="col-md-4 order-3 order-md-2 text-center">
                    <p class="p-fluid mt-3"><b>'.LIVE_GAME_START.':</b> '.date("j.n.Y G:i", strtotime($b[datetime])).'<br>';
                    if($el!=0) $content .= '<b>'.LANG_TEAMSTATS_ARENA.':</b> '.$t1[arena].'<br>';
                    $content .= '<b>'.LANG_MATCHES_BETS.':</b> '.$h[poc].'</p>
                    <p class="h5 h5-fluid"><b>'.$kedy.'</b></p>
                    '.($b[kedy]=="konečný stav" ? '<p><a href="/report/'.$b[id].$el.'-'.SEOtitle($b[team1long].' vs '.$b[team2long]).'" class="btn btn-sm btn-'.$leaguecolor.' btn-icon-split"><span class="icon text-white-50"><i class="fas fa-ellipsis-h"></i></span><span class="text">'.LANG_TEAMSTATS_INDETAIL.'</span></a></p>':'').'
                  </div>
                  <div class="col-6 col-md-4 d-flex flex-column justify-content-between order-2 order-md-3 text-center animated--grow-in">
                    <div><img src="/images/vlajky/'.$b[team2short].'_big.gif" alt="'.$b[team2long].'" class="img-fluid'.$suffix.'"></div>
                    <div class="h5 h5-fluid mb-0 mt-1 font-weight-bold text-gray-800">'.$t2[longname].'</div>
                    '.($b[kedy]=="konečný stav" ? '<p class="display-3"><b>'.$b[goals2].'</b></p>':'').'
                  </div>
                </div>
                <div class="row no-gutters my-4">
                  <div class="col-6 col-md-4 order-2 order-md-1 mt-3 p-fluid">';
                    if($lowerdiv) $i = mysql_query("(SELECT m.*, 1 as roz, l.longname FROM $matches_table m LEFT JOIN 2004leagues l ON l.id=m.league WHERE (m.team1short='$b[team1short]' || m.team2short='$b[team1short]') && m.kedy='konečný stav' && l.longname LIKE '%".$lowerdiv."%' ORDER BY m.datetime DESC LIMIT 5) UNION (SELECT m.*, 2 as roz, l.longname FROM $matches_table m LEFT JOIN 2004leagues l ON l.id=m.league WHERE (m.team1short='$b[team2short]' || m.team2short='$b[team2short]') && m.kedy='konečný stav' && l.longname LIKE '%".$lowerdiv."%' ORDER BY m.datetime DESC LIMIT 5)");
                    else $i = mysql_query("(SELECT *, 1 as roz FROM $matches_table WHERE (team1short='$b[team1short]' || team2short='$b[team1short]') && kedy='konečný stav' ORDER BY datetime DESC LIMIT 5) UNION (SELECT *, 2 as roz FROM $matches_table WHERE (team1short='$b[team2short]' || team2short='$b[team2short]') && kedy='konečný stav' ORDER BY datetime DESC LIMIT 5)");
                    $z1=$z2=0;
                    while($j = mysql_fetch_array($i))
                      {
                      if($j[roz]==1)
                        {
                        $lastt1[$z1] = array($j[team1short], $j[team1long], $j[team2short], $j[team2long], $j[goals1], $j[goals2], $j[datetime], $j[id]);
                        $roz1++;
                        $z1++;
                        }
                      else
                        {
                        $lastt2[$z2] = array($j[team1short], $j[team1long], $j[team2short], $j[team2long], $j[goals1], $j[goals2], $j[datetime], $j[id]);
                        $roz2++;
                        $z2++;
                        }
                      }
                    $lastt1 = array_reverse($lastt1);
                    $lastt2 = array_reverse($lastt2);
                    $x=$w1=$w2=0;
                    while($x < count($lastt1))
                      {
                      $add=$color1="";
                      if($lastt1[$x][0]==$b[team1short])
                        {
                        if($lastt1[$x][4]>$lastt1[$x][5]) { $color1="success"; $w1++; }
                        else $color1="danger";
                        $vs1 = $lastt1[$x][3];
                        $kde1 = "(d)";
                        }
                      else 
                        {
                        if($lastt1[$x][4]>$lastt1[$x][5]) $color1="danger";
                        else { $color1="success"; $w1++; }
                        $vs1 = $lastt1[$x][1];
                        $kde1 = "(v)";
                        }
                      $content .= '<div class="border-bottom"><a href="/report/'.$lastt1[$x][7].$el.'-'.SEOtitle($lastt1[$x][1].' vs '.$lastt1[$x][3]).'" class="badge badge-pill badge-'.$color1.'">'.$lastt1[$x][4].':'.$lastt1[$x][5].'</a> vs. '.$vs1.' '.$kde1.'</div>';
                      $x++;
                      }
                  $content .= '</div>
                  <div class="col-md-4 order-1 order-md-2 d-flex justify-content-center align-items-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_LAST5.'</p>
                  </div>
                  <div class="col-6 col-md-4 order-3 text-right mt-3 p-fluid">';
                    $x=0;
                    while($x < count($lastt2))
                      {
                      $add=$color2="";
                      if($lastt2[$x][0]==$b[team2short]) 
                        {
                        if($lastt2[$x][4]>$lastt2[$x][5]) { $color2="success"; $w2++; }
                        else $color2="danger";
                        $vs2 = $lastt2[$x][3];
                        $kde2 = "(d)";
                        }
                      else
                        {
                        if($lastt2[$x][4]>$lastt2[$x][5]) $color2="danger";
                        else { $color2="success"; $w2++; }
                        $vs2 = $lastt2[$x][1];
                        $kde2 = "(v)";
                        }
                      $content .= '<div class="border-bottom">vs. '.$vs2.' '.$kde2.' <a href="/report/'.$lastt2[$x][7].$el.'-'.SEOtitle($lastt2[$x][1].' vs '.$lastt2[$x][3]).'" class="badge badge-pill badge-'.$color2.'">'.$lastt2[$x][4].':'.$lastt2[$x][5].'</a></div>';
                      $x++;
                      }
                  $content .= '</div>
                </div>
                <div class="row no-gutters my-4">
                  <div class="col-6 col-md-4 order-2 order-md-1 mt-3 p-fluid">';
                    if($lowerdiv) $i = mysql_query("SELECT m.*, l.longname FROM $matches_table m LEFT JOIN 2004leagues l ON l.id=m.league WHERE (m.team1short='$b[team1short]' && m.team2short='$b[team2short]' && m.kedy='konečný stav' && l.longname LIKE '%".$lowerdiv."%') || (m.team1short='$b[team2short]' && m.team2short='$b[team1short]' && m.kedy='konečný stav' && l.longname LIKE '%".$lowerdiv."%') ORDER BY m.datetime DESC LIMIT 5");
                    else $i = mysql_query("SELECT * FROM $matches_table WHERE (team1short='$b[team1short]' && team2short='$b[team2short]' && kedy='konečný stav') || (team1short='$b[team2short]' && team2short='$b[team1short]' && kedy='konečný stav') ORDER BY datetime DESC LIMIT 5");
                    while($j = mysql_fetch_array($i))
                      {
                      $h2h[] = array($j[team1short], $j[team1long], $j[team2short], $j[team2long], $j[goals1], $j[goals2], $j[datetime], $j[id]);
                      }
                    $h2h = array_reverse($h2h);
                    $x=0;
                    while($x < count($h2h))
                      {
                      $add=$color1=$color2="";
                      if($h2h[$x][0]==$b[team1short])
                        {
                        if($h2h[$x][4]>$h2h[$x][5]) { $color1="success"; $hteam = "<b>".$h2h[$x][1]."</b>"; }
                        else { $color1="danger"; $hteam = $h2h[$x][1]; }
                        $g1=$h2h[$x][4];
                        $kde1 = "(d)";
                        }
                      else 
                        {
                        if($h2h[$x][4]>$h2h[$x][5]) { $color1="danger"; $hteam = $h2h[$x][3]; }
                        else { $color1="success"; $hteam = "<b>".$h2h[$x][3]."</b>"; }
                        $g1=$h2h[$x][5];
                        $kde1 = "(v)";
                        }
                      $content .= '<div class="border-bottom"><a href="/report/'.$h2h[$x][7].$el.'-'.SEOtitle($h2h[$x][1].' vs '.$h2h[$x][3]).'" class="badge badge-pill badge-'.$color1.'">'.$g1.'</a> '.$hteam.' '.$kde1.'</div>';
                      $x++;
                      }
                  $content .= '</div>
                  <div class="col-md-4 order-1 order-md-2 d-flex justify-content-center align-items-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_LAST5H2H.'</p>
                  </div>
                  <div class="col-6 col-md-4 order-3 text-right mt-3 p-fluid">';
                    $x=0;
                    while($x < count($h2h))
                      {
                      $add=$color1=$color2="";
                      if($h2h[$x][0]==$b[team1short])
                        {
                        if($h2h[$x][4]>$h2h[$x][5]) { $color2="danger"; $ateam = $h2h[$x][3]; }
                        else { $color2="success"; $ateam = "<b>".$h2h[$x][3]."</b>"; }
                        $g2=$h2h[$x][5];
                        $kde2 = "(v)";
                        }
                      else 
                        {
                        if($h2h[$x][4]>$h2h[$x][5]) { $color2="success"; $ateam = "<b>".$h2h[$x][1]."</b>"; }
                        else { $color2="danger"; $ateam = $h2h[$x][1]; }
                        $g2=$h2h[$x][4];
                        $kde2 = "(d)";
                        }
                      $content .= '<div class="border-bottom">'.$ateam.' '.$kde2.' <a href="/report/'.$h2h[$x][7].$el.'-'.SEOtitle($h2h[$x][1].' vs '.$h2h[$x][3]).'" class="badge badge-pill badge-'.$color2.'">'.$g2.'</a></div>';
                      $x++;
                      }
                  $content .= '</div>
                </div>';
                if($t1[wins]>$t2[wins]) { $color1="success"; $color2="danger"; }
                elseif($t1[wins]<$t2[wins]) { $color1="danger"; $color2="success"; }
                else { if($t1[losts]>$t2[losts]) { $color1="danger"; $color2="success"; } 
                        elseif($t1[losts]<$t2[losts]) { $color1="success"; $color2="danger"; } 
                        else { $color1=$color2="success"; } 
                      }
                $content .= '
                <div class="row no-gutters mt-4">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$t1[zapasov].'-'.$t1[wins].'-'.$t1[losts].'</span></div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_GWTL.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color2.'">'.$t2[zapasov].'-'.$t2[wins].'-'.$t2[losts].'</span></div>
                  </div>
                </div>';
                if($t1[cws]==0) 
                  {
                  if($t1[cls]==1) $hl = LANG_MATCHES_LOSS;
                  if($t1[cls]>1 && $t1[cls]<5) $hl = LANG_MATCHES_LOSTS;
                  if($t1[cls]>=5) $hl = LANG_MATCHES_LOSTS1;
                  $streak1 = $t1[cls]." ".$hl;
                  if($t2[cws]!=0 || $t1[cls]>$t2[cls]) { $color1="danger"; $color2="success"; }
                  else { $color1="success"; $color2="danger"; }
                  }
                else
                  {
                  if($t1[cws]==1) $hl = LANG_MATCHES_WIN;
                  if($t1[cws]>1 && $t1[cws]<5) $hl = LANG_MATCHES_WINS;
                  if($t1[cws]>=5) $hl = LANG_MATCHES_WINS1;
                  $streak1 = $t1[cws]." ".$hl;
                  if($t2[cls]!=0 || $t1[cws]>$t2[cws]) { $color1="success"; $color2="danger"; }
                  else { $color1="danger"; $color2="success"; }
                  }
                if($t2[cws]==0) 
                  {
                  if($t2[cls]==1) $hl = LANG_MATCHES_LOSS;
                  if($t2[cls]>1 && $t2[cls]<5) $hl = LANG_MATCHES_LOSTS;
                  if($t2[cls]>=5) $hl = LANG_MATCHES_LOSTS1;
                  $streak2 = $t2[cls]." ".$hl;
                  if($t1[cws]!=0 || $t2[cls]>$t1[cls]) { $color1="success"; $color2="danger"; }
                  else { $color1="danger"; $color2="success"; }
                  }
                else
                  {
                  if($t2[cws]==1) $hl = LANG_MATCHES_WIN;
                  if($t2[cws]>1 && $t2[cws]<5) $hl = LANG_MATCHES_WINS;
                  if($t2[cws]>=5) $hl = LANG_MATCHES_WINS1;
                  $streak2 = $t2[cws]." ".$hl;
                  if($t1[cls]!=0 || $t2[cws]>$t1[cws]) { $color1="danger"; $color2="success"; }
                  else { $color1="success"; $color2="danger"; }
                  }
                if($t1[cls]==$t2[cls] && $t1[cls]!=0) $color1=$color2="danger";
                if($t1[cws]==$t2[cws] && $t1[cws]!=0) $color1=$color2="success";
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$streak1.'</span></div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_STREAK.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color2.'">'.$streak2.'</span></div>
                  </div>
                </div>';
                if($t1[body]>$t2[body]) { $color1="success"; $color2="danger"; }
                elseif($t1[body]<$t2[body]) { $color1="danger"; $color2="success"; }
                else $color1=$color2="success";
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$t1[body].'</span></div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_TEAMSTATS_POINTS.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color2.'">'.$t2[body].'</span></div>
                  </div>
                </div>';
                $score1 = $t1[goals]-$t1[ga];
                $score2 = $t2[goals]-$t2[ga];
                if($score1>$score2) { $color1="success"; $color2="danger"; }
                elseif($score1<$score2) { $color1="danger"; $color2="success"; }
                else $color1=$color2="success";
                if($score1>=0) $score1 = "+".$score1;
                if($score2>=0) $score2 = "+".$score2;
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$score1.'</span> '.$t1[goals].':'.$t1[ga].'</div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_TEAMSTATS_SCORE.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom">'.$t2[goals].':'.$t2[ga].' <span class="badge badge-'.$color2.'">'.$score2.'</span></div>
                  </div>
                </div>';
                $score1 = $t1[ppgf]-$t1[shga];
                $score2 = $t2[ppgf]-$t2[shga];
                if($score1>$score2) { $color1="success"; $color2="danger"; }
                elseif($score1<$score2) { $color1="danger"; $color2="success"; }
                else $color1=$color2="success";
                if($score1>=0) $score1 = "+".$score1;
                if($score2>=0) $score2 = "+".$score2;
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$score1.'</span> '.$t1[ppgf].':'.$t1[shga].'</div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_PPG.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom">'.$t2[ppgf].':'.$t2[shga].' <span class="badge badge-'.$color2.'">'.$score2.'</span></div>
                  </div>
                </div>';
                $score1 = $t1[shgf]-$t1[ppga];
                $score2 = $t2[shgf]-$t2[ppga];
                if($score1>$score2) { $color1="success"; $color2="danger"; }
                elseif($score1<$score2) { $color1="danger"; $color2="success"; }
                else $color1=$color2="success";
                if($score1>=0) $score1 = "+".$score1;
                if($score2>=0) $score2 = "+".$score2;
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$score1.'</span> '.$t1[shgf].':'.$t1[ppga].'</div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_SHG.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom">'.$t2[shgf].':'.$t2[ppga].' <span class="badge badge-'.$color2.'">'.$score2.'</span></div>
                  </div>
                </div>';
                if($t1[so]>$t2[so]) { $color1="success"; $color2="danger"; }
                elseif($t1[so]<$t2[so]) { $color1="danger"; $color2="success"; }
                else $color1=$color2="success";
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$t1[so].'</span></div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_SO.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color2.'">'.$t2[so].'</span></div>
                  </div>
                </div>';
                if($t1[penalty]<$t2[penalty]) { $color1="success"; $color2="danger"; }
                elseif($t1[penalty]>$t2[penalty]) { $color1="danger"; $color2="success"; }
                else $color1=$color2="success";
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$t1[penalty].' min</span></div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_PENALTY.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color2.'">'.$t2[penalty].' min</span></div>
                  </div>
                </div>';
                if($p1[goals]+$p1[asists]>$p2[goals]+$p2[asists]) { $color1="success"; $color2="danger"; }
                elseif($p1[goals]+$p1[asists]<$p2[goals]+$p2[asists]) { $color1="danger"; $color2="success"; }
                else $color1=$color2="success";
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$p1[goals].'+'.$p1[asists].'</span> '.$p1[name].'</div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_BESTPLAYER.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom">'.$p2[name].' <span class="badge badge-'.$color2.'">'.$p2[goals].'+'.$p2[asists].'</span></div>
                  </div>
                </div>';
                if($el==1)
                  {
                  $goa1 = mysql_query("SELECT *, ROUND((svs/sog)*100,1) as svp FROM el_goalies WHERE league='$b[league]' && teamshort='$b[team1short]' && IF(gp=1 && (svs/sog)=1, 1, 0)=0 ORDER BY svp DESC LIMIT 1");
                  $goa2 = mysql_query("SELECT *, ROUND((svs/sog)*100,1) as svp FROM el_goalies WHERE league='$b[league]' && teamshort='$b[team2short]' && IF(gp=1 && (svs/sog)=1, 1, 0)=0 ORDER BY svp DESC LIMIT 1");
                  $goalie1 = mysql_fetch_array($goa1);
                  $goalie2 = mysql_fetch_array($goa2);
                  if($goalie1[svp]>$goalie2[svp]) { $color1="success"; $color2="danger"; }
                  elseif($goalie1[svp]<$goalie2[svp]) { $color1="danger"; $color2="success"; }
                  else $color1=$color2="success";
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$goalie1[svp].'%</span> '.$goalie1[name].'</div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_BESTGOALIE.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom">'.$goalie2[name].' <span class="badge badge-'.$color2.'">'.$goalie2[svp].'%</span></div>
                  </div>
                </div>';
                  }
                if($h[vys1]>$h[vys2]) { $color1="success"; $color2="danger"; }
                elseif($h[vys1]<$h[vys2]) { $color1="danger"; $color2="success"; }
                else $color1=$color2="success";
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$h[vys1].'</span></div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_AVGBET.'</p>
                  </div>
                  <div class="col-4 text-right p-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color2.'">'.$h[vys2].'</span></div>
                  </div>
                </div>';
                // sila tímu (dolezitost atributu v percentach)
                if($el==1) $dpp = 20; // domáce prostredie
                else $dpp = 0;
                $l5p = 80; // posledných 5 zápasov
                $twp = 50; // celkovo výhier
                $ptp = 60; // celkovo bodov
                $dp1 = 1*($dpp/(($dpp+$l5p+$twp+$ptp)/100));
                $l51 = ($w1/5)*($l5p/(($dpp+$l5p+$twp+$ptp)/100));
                $tw1 = ($t1[wins]/$t1[zapasov])*($twp/(($dpp+$l5p+$twp+$ptp)/100));
                $pt1 = ($t1[body]/($t1[zapasov]*$linfo[points]))*($ptp/(($dpp+$l5p+$twp+$ptp)/100));
                $st1 = round($dp1+$l51+$tw1+$pt1,0);
                $dp2 = 0;
                $l52 = ($w2/5)*($l5p/(($dpp+$l5p+$twp+$ptp)/100));
                $tw2 = ($t2[wins]/$t2[zapasov])*($twp/(($dpp+$l5p+$twp+$ptp)/100));
                $pt2 = ($t1[body]/($t1[zapasov]*$linfo[points]))*($ptp/(($dpp+$l5p+$twp+$ptp)/100));
                $st2 = round($dp2+$l52+$tw2+$pt2,0);
                if($st1>$st2) { $color1="success"; $color2="danger"; }
                elseif($st1<$st2) { $color1="danger"; $color2="success"; }
                else $color1=$color2="success";
                $content .= '
                <div class="row no-gutters my-1">
                  <div class="col-4 h4 h4-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color1.'">'.$st1.'</span></div>
                  </div>
                  <div class="col-4 text-center">
                    <p class="h6 h6-fluid text-uppercase font-weight-bold m-0">'.LANG_MATCHES_STRENGTH.'</p>
                  </div>
                  <div class="col-4 text-right h4 h4-fluid">
                    <div class="border-bottom"><span class="badge badge-'.$color2.'">'.$st2.'</span></div>
                  </div>
                </div>
                <div class="card shadow my-4">
                    <div class="card-body">
                    '.GenerateComments(2,$gid).'
                    </div>
                </div>

            <script type="application/ld+json">
            {
              "@context": "http://schema.org",
              "@type": "Event",
              "name": "'.$t1[longname].' vs. '.$t2[longname].'",
              "startDate" : "'.date("c", strtotime($b[datetime])).'",
              "endDate" : "'.date("c", strtotime($b[datetime])+9000).'",
              "url" : "https://www.hockey-live.sk/game/'.$gid.$el.'-'.SEOtitle($t1[longname].' vs '.$t2[longname]).'",
              "image" : "https://www.hockey-live.sk/images/vlajky/'.$b[team1short].'_big.gif",
              "description" : "'.LANG_MATCH1.' '.$t1[longname].' vs. '.$t2[longname].'",
              "performer": [{
                "@type": "SportsTeam",
                "name": "'.$t1[longname].'"
              },{
                "@type": "SportsTeam",
                "name": "'.$t2[longname].'"
              }],
              "location" : {
                "@type" : "Place",
                "name" : "'.$t1[arena].'",
                "address" : "'.$t1[arena].'"
              }
            }
            </script>
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
    $content .= "<div class='alert alert-warning' role='alert'><i class='fas fa-hockey-puck'></i> Neexistujúci zápas</div>";
    }
  }
// nebol vybrany ziaden zapas alebo liga
else
  {
  $leaguecolor = "hl";
  $content .= "Neexistujúca liga alebo zápas";
  }
?>