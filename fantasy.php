<?php
$params = explode("/", htmlspecialchars($_GET[id]));

$nazov = "Fantasy Championship";
$menu = "MS 2021";
$skratka = "MS";
$manazerov = 10;
$article_id = 2184;
//$timeout = 480;
$predraftt = 0; // = draftuje sa do zásobníka. ak 1, upraviť počet manažérov aj v includes/fantasy_functions.php
$draft_start = "2021-05-04 08:00:00";
$league_start = "2021-05-19 10:00:00";

/*
1. nastaviť dátum deadlinu
2. odremovať potrebné veci
3. odremovať v players.php draft button
4. pridať brankárske tímy do ft_choices
5. vyprázdniť ft_players, ft_predraft a ft_teams
6. zmeniť link v menu
7. v includes/players_functions.php pridať hráčov, ktorí sa nezúčastnia
8. vypnúť/zapnúť cronjob
*/

if($_GET[cron]==1) include("includes/db.php");

$date1 = new DateTime(date("Y-m-d"));
$date2 = new DateTime(date("Y-m-d", strtotime($league_start)));
$interval = $date1->diff($date2);
$diff = $interval->days;
$u = mysql_query("SELECT * FROM ft_players");
$o = mysql_num_rows($u);
$timeout = floor(($diff*24*60)/(($manazerov)*10-$o));
$uid = $_SESSION['logged'];
$leag = mysql_query("SELECT * FROM 2004leagues WHERE longname LIKE '%$skratka%' && active='1'");
$league = mysql_fetch_array($leag);
$leaguecolor = $league[color];
$active_league = $league[id];
//if($uid==2) $uid=1319;

// cron job pre vyber random hraca pri necinnosti manazera
if($_GET[cron]==1)
  {
  function NaRade()
    {
    Global $manazerov, $round;
    $q = mysql_query("SELECT * FROM ft_players ORDER BY round DESC, id DESC LIMIT 1");
    $f = mysql_fetch_array($q);
    $po = mysql_query("SELECT * FROM ft_players WHERE round='$f[round]'");
    $poc = mysql_num_rows($po);
    if($poc<$manazerov)
      {
      $pick = $poc+1;
      $round = $f[round];
      if(mysql_num_rows($q)==0) $round=1;
      }
    else
      {
      $pick = 1;
      $round = $f[round]+1;
      }
    if($pick==1 && $round==$manazerov+1) break;
    if($round % 2 == 0) $narade = $manazerov-$pick+1;
    else $narade = $pick;
    $w = mysql_query("SELECT dt.*, e_xoops_users.email, e_xoops_users.uname FROM e_xoops_users JOIN (SELECT * FROM ft_teams WHERE pos='$narade')dt ON dt.uid=e_xoops_users.uid");
    $r = mysql_fetch_array($w);
    return $r[uid];
    }
    
  function PickGoalie($uid)
    {
    Global $round;
    $y = mysql_query("SELECT * FROM `ft_choices` WHERE pos='GK' && id NOT IN (SELECT pid FROM ft_players) ORDER BY rand() LIMIT 1");
    $u = mysql_fetch_array($y);
    mysql_query("INSERT INTO ft_players (uid, pid, round, type) VALUES ('$uid', '$u[id]', '$round', '1')");
    }
    
  function PickPlayer($uid, $pos)
    {
    Global $round, $predraftt, $skratka;
    $m = mysql_query("SELECT * FROM 2004leagues WHERE longname LIKE '%$skratka%' && active='1'");
    $n = mysql_fetch_array($m);
    $b = mysql_query("SELECT shortname FROM 2004teams WHERE league='$n[id]'");
    $i=0;
    while($v = mysql_fetch_array($b))
      {
      $team[$i] = "'$v[shortname]'";
      $i++;
      }
    $teams = implode($team, ",");
    if($predraftt==1) $ttable = "ft_choices";
    else $ttable = "2004players";
    $c = mysql_query("SELECT * FROM $ttable WHERE pos='$pos' && teamshort IN ($teams) && name NOT IN (SELECT c.name FROM `ft_players` JOIN ft_choices c ON ft_players.pid=c.id) ORDER BY rand() LIMIT 1");
    $x = mysql_fetch_array($c);
    mysql_query("INSERT INTO ft_choices (id, teamshort, teamlong, pos, name) VALUES ('$x[id]', '$x[teamshort]', '$x[teamlong]', '$x[pos]', '$x[name]')");
    mysql_query("INSERT INTO ft_players (uid, pid, round, type) VALUES ('$uid', '$x[id]', '$round', '1')");
    }
    
  $narade = NaRade();
  $w = mysql_query("SELECT tstamp + INTERVAL $timeout MINUTE as tme FROM ft_players ORDER BY id DESC LIMIT 1");
  $e = mysql_fetch_array($w);
  if(mysql_num_rows($w)==0) $tme = strtotime($draft_start.' + '.$timeout.' minute');
  else $tme = strtotime($e[tme]);
  if(mysql_num_rows($w)>0 && mktime()>$tme)
    {
    $r = mysql_query("SELECT * FROM `ft_players` JOIN ft_choices c ON ft_players.pid=c.id WHERE uid='$narade'");
    $forward=$defense=$goalie=0;
    while($t = mysql_fetch_array($r))
      {
      if($t[pos]=="F") $forward++;
      if($t[pos]=="D") $defense++;
      if($t[pos]=="GK") $goalie++;
      }
    if($goalie==0) PickGoalie($narade);
    elseif($forward<6) PickPlayer($narade, "F");
    elseif($defense<3) PickPlayer($narade, "D");
    else exit;
    $narade = NaRade();
    $n = mysql_query("SELECT * FROM ft_predraft WHERE uid='$narade'");
    if(mysql_num_rows($n)>0)
      {
      include("includes/fantasy_functions.php");
      PreDraft();
      $pd=1;
      }
    // maily
    $g = mysql_query("SELECT email FROM e_xoops_users WHERE uid='$narade'");
    $h = mysql_fetch_array($g);
    $subject = LANG_FANTASY_MAILSUBJECT;
    $message = sprintf(LANG_FANTASY_MAILTEXT, $nazov, $menu, $nazov);
    $headers = 'From: '.SITE_MAIL. "\r\n" .
        'Reply-To: '.SITE_MAIL. "\r\n" .
        'X-Mailer: PHP/' . phpversion();
    if($pd!=1)
        {
        mail($h[email], $subject, $message, $headers);
        mysql_query("INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$narade."', '2', '0', NOW())");
        mail(ADMIN_MAIL, "Dalsi na rade v drafte", "Vykonany nahodny vyber. Nasleduje: $h[email]", $headers);
        }
    }
  mysql_close($link);
  exit;
  }

if($params[0]=="picks")
  {
  $title = "$nazov - ".LANG_FANTASY_PICKSTITLE;
  $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($league[longname])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".$nazov."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".LANG_FANTASY_PICKSTITLE1."</h2>
               <div style='max-width: 1000px;'>";

  $r = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
  $r = mysql_query("SELECT ft.*, e_xoops_users.uname FROM e_xoops_users JOIN (SELECT et.*, 2004players.goals, 2004players.asists, SUM(wins)*2+SUM(so)*2+SUM(goals)+SUM(asists) as body FROM 2004players RIGHT JOIN (SELECT dt.pid, dt.uid, ft_choices.* FROM ft_choices RIGHT JOIN (SELECT * FROM ft_players)dt ON dt.pid=ft_choices.id)et ON et.pid=2004players.id GROUP BY uid ORDER BY body DESC)ft ON ft.uid=e_xoops_users.uid");
  //$content .= '<div class="alert alert-info">Keďže nastala na prvej priečke rovnosť bodov medzi dvoma manažérmi, museli sme zaviesť rozhodovacie pravidlo, ktorým je viac bodov jednotlivých draftovaných hráčov (bez brankárov). Tento rozstrel vyhral v pomere 66:58 manažér <b>lukias24</b>, ktorému gratulujeme! Aby sa ale <b>dodys</b> nehneval, takisto si môže vybrať z našich vecných cien :)</div>';
   
  if($uid)
    {
    $w = mysql_query("SELECT * FROM ft_teams WHERE uid='$uid'");
    if(mysql_num_rows($w)>0)
      {
    $content .='
     <div class="card shadow animated--grow-in mb-4">
      <div class="card-header">
        <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
          '.LANG_FANTASY_MYROSTER.'
        </h6>
      </div>
      <div class="card-body">
        <h5 class="card-title text-'.$leaguecolor.' h5-fluid">'.LANG_FANTASY_FORWARDS.'</h5>
        <div class="row">';
          $y = mysql_query("SELECT ft_players.*, t1.*, t2.goals, t2.asists, t2.goals+t2.asists as points, IF(t1.pos='F',1,IF(t1.pos='D',2,3)) as zor FROM ft_players JOIN ft_choices t1 ON t1.id=ft_players.pid LEFT JOIN 2004players t2 ON ft_players.pid=t2.id WHERE uid='$uid' ORDER BY zor ASC, ft_players.id ASC");
          while($u = mysql_fetch_array($y))
            {
            $players[] = array($u[teamshort], $u[name], $u[pos], $u[goals], $u[asists], $u[points], $u[wins], $u[so]);
            }
          $i=0;
          while($i < 6)
            {
            $content .= '
             <div class="col text-center">
              <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$players[$i][1].'" class="lazy rounded-circle img-thumbnail" style="width:100px; height:100px; max-width:100px;">
              <p class="p-fluid"><span class="font-weight-bold"><img class="flag-iihf '.$players[$i][0].'-small" src="/images/blank.png" alt="'.$players[$i][0].'"> '.$players[$i][1].'</span><br>
              '.LANG_PLAYERSTATS_F.'<br>
              <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][3].'+'.$players[$i][4].'</span></p>
             </div>';
            $i++;
            }
        $content .= '
        </div>
        <h5 class="card-title text-'.$leaguecolor.' h5-fluid">'.LANG_FANTASY_DEFENSE.'</h5>
        <div class="row">';
          while($i < 9)
            {
            $content .= '
             <div class="col text-center">
              <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$players[$i][1].'" class="lazy rounded-circle img-thumbnail" style="width:100px; height:100px; max-width:100px;">
              <p class="p-fluid"><span class="font-weight-bold"><img class="flag-iihf '.$players[$i][0].'-small" src="/images/blank.png" alt="'.$players[$i][0].'"> '.$players[$i][1].'</span><br>
              '.LANG_PLAYERSTATS_D.'<br>
              <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][3].'+'.$players[$i][4].'</span></p>
             </div>';
            $i++;
            }
        $content .= '
        </div>
        <h5 class="card-title text-'.$leaguecolor.' h5-fluid">'.LANG_TEAMSTATS_GOALIES.'</h5>
        <div class="row">
          <div class="col text-center">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/vlajky/'.$players[9][0].'_big.gif" class="lazy rounded-circle img-thumbnail" style="width:100px; height:100px; max-width:100px;">
            <p class="p-fluid"><span class="font-weight-bold">'.$players[9][1].'</span><br>
            <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][6].' '.LANG_MATCHES_WINS1.'</span><br>
            <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][7].' '.LANG_FANTASY_SO.'</span>
            '.$butt.'</p>
          </div>
        </div>
      </div>
    </div>';
      }
     }
     $content .='
     <div class="row justify-content-center">
      <div class="col-sm-8 col-md-7">
        <div class="card shadow animated--grow-in mb-4">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
              '.LANG_FANTASY_RANKING.'
            </h6>
          </div>
          <div class="card-body">
            <table class="table-hover table-light table-striped w-100 p-fluid">
              <thead>
                <tr>
                  <th class="text-center">#</th>
                  <th>'.LANG_FANTASY_MANAGER.'</th>
                  <th>'.LANG_TEAMSTATS_CURRENTPTS.'</th>
                </tr>
              </thead>
              <tbody>';
  $i=1;
  while($t = mysql_fetch_array($r))
    {
    $add="";
    if($uid==$t[uid]) { $add=" bg-gray-200"; }
    // zmazat
     $t[body]=0;
     //if($t[uid]==2932) $t[body]=$t[body]-1;
    $content .= "<tr><td class='text-center$add'>$i.</td><td class='$add'><a href='#$t[uname]'>$t[uname]</a></td><td class='$add'><b>$t[body]</b></td></tr>";
    $i++;
    }
  $content .= '</tbody></table>
              </div>
            </div>
            
        <div class="card shadow animated--grow-in mb-4">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
              '.LANG_FANTASY_POINTVALUE.'
            </h6>
          </div>
          <div class="card-body">
            <table class="table-light w-100 p-fluid">
              <thead>
                <tr>
                  <th>'.LANG_TEAMSTATS_EVENT.'</th>
                  <th>'.LANG_TEAMSTATS_CURRENTPTS.'</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>'.LANG_FANTASY_GOAL.'</td>
                  <td>1 '.LANG_GAMECONT_POINT.'</td>
                </tr>
                <tr>
                  <td>'.LANG_FANTASY_ASSIST.'</td>
                  <td>1 '.LANG_GAMECONT_POINT.'</td>
                </tr>
                <tr>
                  <td>'.LANG_FANTASY_GKWIN.'</td>
                  <td>2 '.LANG_GAMECONT_POINTS.'</td>
                </tr>
                <tr>
                  <td>'.LANG_FANTASY_GKSO.'</td>
                  <td>2 '.LANG_GAMECONT_POINTS.'</td>
                </tr>
              </tbody>
             </table>
            </div>
           </div>';
             
  $content .= "<div class='alert alert-info'>".sprintf(LANG_FANTASY_CHANGEPLAYERS, "<a href='mailto:redxakcia@hockeyx-lixve.sk'
    onmouseover='this.href=this.href.replace(/x/g,\"\");'>")."</a></span>.</div>
    </div>
   </div>";
   
  $content .= '
  <div class="row justify-content-center">
    <div class="col-sm-8 col-md-7">
      <div class="card mb-4 shadow animated--grow-in">
        <div class="card-header">
          <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
            '.LANG_FANTASY_LAST10CHANGES.'
            <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
          </h6>
        </div>
        <div class="card-body">
          <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid">
            <thead>
              <tr>
                <th>'.LANG_DATE.'</th>
                <th>'.LANG_FANTASY_MANAGER.'</th>
                <th>'.LANG_FANTASY_OLDPLAYER.'</th>
                <th>'.LANG_FANTASY_NEWPLAYER.'</th>
              </tr>
            </thead>
            <tbody>';
  $q = mysql_query("SELECT ft_changes.*, p.teamshort as old_tshort, p.name as old_name, p2.teamshort as new_tshort, p2.name as new_name, u.uname FROM `ft_changes` JOIN 2004players p ON ft_changes.old_pid=p.id JOIN ft_choices p2 ON ft_changes.new_pid=p2.id JOIN e_xoops_users u ON ft_changes.uid=u.uid ORDER BY tstamp DESC LIMIT 10");
  while($f = mysql_fetch_array($q))
    {
    if(date('Y-m-d',strtotime($f[tstamp]))==date("Y-m-d", mktime())) $hl="dnes o <b>".date('G:i', strtotime($f[tstamp]))."</b>";
    elseif(date('Y-m-d',strtotime($f[tstamp]))==date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')))) $hl="včera o ".date('G:i', strtotime($f[tstamp]));
    else $hl=date('j.n.',strtotime($f[tstamp])). " ".LANG_AT." ".date('G:i', strtotime($f[tstamp]));
    $content .= '<tr><td class="text-nowrap">'.$hl.'</td><td><a href="/user/'.$f[uid].'">'.$f[uname].'</a></td><td class="text-nowrap"><img class="flag-iihf '.$f[old_tshort].'-small" src="/images/blank.png" alt="'.$f[old_tshort].'"> <a href="/player/'.$f[old_pid].'0-'.SEOtitle($f[old_name]).'">'.$f[old_name].'</a></td><td class="text-nowrap"><img class="flag-iihf '.$f[new_tshort].'-small" src="/images/blank.png" alt="'.$f[new_tshort].'"> <a href="/player/'.$f[new_pid].'0-'.SEOtitle($f[new_name]).'">'.$f[new_name].'</a></td></tr>';
    }
  $content .= '</tbody></table></div></div></div></div>';
  
  $q = mysql_query("SELECT dt.*, e_xoops_users.uname FROM e_xoops_users JOIN (SELECT * FROM ft_teams ORDER BY pos ASC)dt ON dt.uid=e_xoops_users.uid");
  while($f = mysql_fetch_array($q))
    {
    $thead=$tfood=$tbody="";
    $thead = '
        <a name="'.$f[uname].'">
        <div class="card shadow animated--grow-in mb-2">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
              '.LANG_FANTASY_TEAMOFMANAGER.' '.$f[uname].'
              <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
            </h6>
          </div>
          <div class="card-body">
            <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid">
              <thead>
                <tr>
                  <th class="text-center">#</th>
                  <th class="text-center">'.LANG_PLAYERSTATS_POS.'</th>
                  <th>'.LANG_PLAYERDB_PLAYER.'</th>
                  <th class="text-center">'.LANG_TEAMSTATS_GOALS.'</th>
                  <th class="text-center">'.LANG_TEAMSTATS_ASISTS.'</th>
                  <th class="text-center">'.LANG_TEAMSTATS_WINS.'</th>
                  <th class="text-center">'.LANG_MATCHES_SO.'</th>
                </tr>
              </thead>';
    $w = mysql_query("SELECT ft_players.*, t1.*, t2.goals, t2.asists, t2.goals+t2.asists as points, IF(t1.pos='F',1,IF(t1.pos='D',2,3)) as zor FROM ft_players JOIN ft_choices t1 ON t1.id=ft_players.pid LEFT JOIN 2004players t2 ON ft_players.pid=t2.id WHERE uid='$f[uid]' ORDER BY zor ASC, points DESC, goals DESC, asists DESC, wins DESC, so DESC");
    $i = 1;
    $pts=$goals=$asists=$wins=$so=0;
    while($e = mysql_fetch_array($w))
      {
      // tiez prec
      //if($e[name]=="VAN RIEMSDYK James") $e[asists]=$e[asists]-1;
      $pts = $pts+$e[goals]+$e[asists]+($e[wins]*2)+($e[so]*2);
      $goals = $goals+$e[goals];
      $asists = $asists+$e[asists];
      $wins = $wins+$e[wins];
      $so = $so+$e[so];
      if($e[pos]!="GK") { $e[wins]=""; $e[so]=""; }
      // zmazat
       $e[goals]=$e[asists]=$goals=$asists=$pts=0;
      $tbody .= '<tr><td class="text-center">'.$i.'.</td><td class="text-center">'.$e[pos].'</td><td class="text-nowrap" style="width:30%;"><img class="flag-iihf '.$e[teamshort].'-small" src="/images/blank.png" alt="'.$e[teamshort].'"> '.$e[name].'</td><td class="text-center">'.$e[goals].'</td><td class="text-center">'.$e[asists].'</td><td class="text-center">'.$e[wins].'</td><td class="text-center">'.$e[so].'</td></tr>';
      $i++;
      }
    $tfoot = '<tfoot class="alert-'.$leaguecolor.' font-weight-bold">
            <tr>
              <td colspan="3">'.LANG_BETS_OVERALL.': '.$pts.' '.LANG_TEAMSTATS_PTS.'</td>
              <td class="text-center" style="width:13%;">'.$goals.'</td>
              <td class="text-center" style="width:14%;">'.$asists.'</td>
              <td class="text-center" style="width:14%;">'.$wins.'</td>
              <td class="text-center" style="width:14%;">'.$so.'</td>
            </tr>
          </tfoot>';
    $content .= $thead.$tfoot.'<tbody>'.$tbody.'</tbody></table></div></div>';
    }
  $content .= '
    <div class="card shadow my-4">
        <div class="card-body">
        '.GenerateComments(4,0).'
        </div>
    </div>
  </div>';
  }
elseif($params[0]=="draft")
  {
  $m = mysql_query("SELECT * FROM ft_teams WHERE uid='$uid'");
  if($uid || $params[2])
  {
  if(mysql_num_rows($m)>0 || $params[2]) // ak je prihlasenym manazerom
    {
    $q = mysql_query("SELECT * FROM ft_players ORDER BY round DESC, id DESC");
    $f = mysql_fetch_array($q);
    $po = mysql_query("SELECT * FROM ft_players WHERE round='$f[round]'");
    $poc = mysql_num_rows($po);
    $title = $nazov." - ".LANG_FANTASY_PLAYERSDRAFT;
    
    $content .= "<div id='toasts' class='fixed-top' style='top: 80px; right: 23px; left: initial; z-index:3;'></div>
                 <i class='float-left h1 h1-fluid ll-".LeagueFont($league[longname])." text-gray-600 mr-1'></i>
                 <h1 class='h3 h3-fluid mb-1'>".$nazov."</h1>
                 <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".LANG_FANTASY_PLAYERSDRAFT."</h2>
                 <div style='max-width: 1000px;'>";
    
    if($poc<$manazerov)
      {
      $pick = $poc+1;
      $round = $f[round];
      if(mysql_num_rows($q)==0) $round=1;
      }
    else
      {
      $pick = 1;
      $round = $f[round]+1;
      }
    if($round % 2 == 0) $narade = $manazerov-$pick+1;
    else $narade = $pick;
    
    if(isset($_POST['forward']) || isset($_POST['defense']) || isset($_POST['goalie']) || $params[1])
      {
      if(isset($_POST['forward']) || isset($_POST['defense']) || isset($_POST['goalie'])) session_unset($_SESSION['olddraft']);
      if(!$_POST[forward]) $_POST[forward]='0';
      if(!$_POST[defense]) $_POST[defense]='0';
      if(!$_POST[goalie]) $_POST[goalie]='0';
      if($_POST[forward]!='0')
        {
        if($_POST[defense]!='0' || $_POST[goalie]!='0') $content .= '<div class="alert alert-danger" role="alert">'.LANG_FANTASY_ONLYONE.'</div>';
        else
          {
          mysql_query("INSERT INTO ft_players (uid, pid, round) VALUES ('$uid', '$_POST[forward]', '$round')");
          }
        }
      if($_POST[defense]!='0')
        {
        if($_POST[forward]!='0' || $_POST[goalie]!='0') $content .= '<div class="alert alert-danger" role="alert">'.LANG_FANTASY_ONLYONE.'</div>';
        else
          {
          mysql_query("INSERT INTO ft_players (uid, pid, round) VALUES ('$uid', '$_POST[defense]', '$round')");
          }
        }
      if($_POST[goalie]!='0')
        {
        if($_POST[forward]!='0' || $_POST[defense]!='0') $content .= '<div class="alert alert-danger" role="alert">'.LANG_FANTASY_ONLYONE.'</div>';
        else
          {
          mysql_query("INSERT INTO ft_players (uid, pid, round) VALUES ('$uid', '$_POST[goalie]', '$round')");
          }
        }
      // draft z databazy hracov
      if($params[1])
        {
        if(!$params[2]) session_unset($_SESSION['olddraft']);
        else
          {
          $a = mysql_query("SELECT * FROM ft_choices WHERE id='".$_SESSION['olddraft']."'");
          $b = mysql_fetch_array($a);
          }
        $def=$off=0;
        $el = substr($params[1], -1);
        $dl = strlen($params[1]);
        $ide = substr($params[1], 0, $dl-1);
        if($el==1) $players_table = "el_players";
        elseif($el==0) $players_table = "2004players";
        $h = mysql_query("SELECT * FROM $players_table WHERE id='$ide'");
        $j = mysql_fetch_array($h);
        $l = mysql_query("SELECT dt.* FROM ft_players JOIN (SELECT * FROM ft_choices)dt ON dt.id=ft_players.pid WHERE ft_players.uid='$uid'");
        while($m = mysql_fetch_array($l))
          {
          if($m[pos]=="D") $def++;
          if($m[pos]=="F") $off++;
          }
        if($params[2] && $b[pos]=="D") $def--;
        if($params[2] && $b[pos]=="F") $off--;
        if($def==3 && ($j[pos]=="D" || $j[pos]=="LD" || $j[pos]=="RD"))
          {
          $content .= '<div class="alert alert-danger" role="alert">'.LANG_FANTASY_ALLD.'</div>';
          $error=1; 
          }
        elseif($off==6 && ($j[pos]=="F" || $j[pos]=="CE" || $j[pos]=="RW" || $j[pos]=="LW"))
          {
          $content .= '<div class="alert alert-danger" role="alert">'.LANG_FANTASY_ALLF.'</div>';
          $error=1; 
          }
        else
          {
          $k = mysql_query("SELECT * FROM ft_choices c JOIN ft_players p ON p.pid=c.id WHERE c.name='$j[name]'");
          if(mysql_num_rows($k)>0) 
            {
            $content .= '<div class="alert alert-danger" role="alert">'.LANG_FANTASY_ALREADYDRAFTED.'</div>';
            $error=1; 
            }
          else
            {
            if($j[pos]=="D" || $j[pos]=="LD" || $j[pos]=="RD") $npos="D";
            if($j[pos]=="F" || $j[pos]=="CE" || $j[pos]=="RW" || $j[pos]=="LW") $npos="F";
            $z = mysql_query("SELECT * FROM 2004players WHERE name='$j[name]' ORDER BY id DESC LIMIT 1");
            if(mysql_num_rows($z)>0)
              {
              $x = mysql_fetch_array($z);
              $ide = $x[id];
              $tshort = $x[teamshort];
              $tlong = $x[teamlong];
              }
            else
              {
              $tshort = $j[teamshort];
              $tlong = $j[teamlong];
              }
            mysql_query("INSERT INTO ft_choices (id, teamshort, teamlong, pos, name) VALUES ('$ide', '$tshort', '$tlong', '$npos', '$j[name]')");
            if($params[2]) 
              {
              mysql_query("UPDATE ft_players SET pid='$ide', type='0' WHERE pid='$params[2]'");
              mysql_query("INSERT INTO ft_changes (uid, old_pid, new_pid) VALUES ('$uid', '$params[2]', '$ide')");
              }
            else mysql_query("INSERT INTO ft_players (uid, pid, round) VALUES ('$uid', '$ide', '$round')");
            }
          }
        $content .= '</div>';
        }
      // overenie a mail dalsiemu
      if($pick==$manazerov && $round==10) { $content .= "<script>top.location.href= '/fantasy/picks'; </script>"; }
      $q = mysql_query("SELECT * FROM ft_players ORDER BY round DESC, id DESC");
      $f = mysql_fetch_array($q);
      $po = mysql_query("SELECT * FROM ft_players WHERE round='$f[round]'");
      $poc = mysql_num_rows($po);
      if($poc<$manazerov)
        {
        $pick = $poc+1;
        $round = $f[round];
        }
      else
        {
        $pick = 1;
        $round = $f[round]+1;
        }
      if($round % 2 == 0) $narade = $manazerov-$pick+1;
      else $narade = $pick;
      $w = mysql_query("SELECT dt.*, e_xoops_users.email, e_xoops_users.uname FROM e_xoops_users JOIN (SELECT * FROM ft_teams WHERE pos='$narade')dt ON dt.uid=e_xoops_users.uid");
      $r = mysql_fetch_array($w);
      $n = mysql_query("SELECT * FROM ft_predraft WHERE uid='$r[uid]'");
      if(mysql_num_rows($n)>0)
        {
        PreDraft();
        $pd=1;
        }
      // maily
      $subject = LANG_FANTASY_MAILSUBJECT;
      $message = sprintf(LANG_FANTASY_MAILTEXT, $nazov, $menu, $nazov);
      $headers = 'From: '.SITE_MAIL. "\r\n" .
          'Reply-To: '.SITE_MAIL. "\r\n" .
          'X-Mailer: PHP/' . phpversion();
      if($error!=1 && !$params[2] && $pd!=1) 
        {
        mail($r[email], $subject, $message, $headers);
        mysql_query("INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$r[uid]."', '2', '0', NOW())");
        }
      if(!$params[2] && $pd!=1) mail(ADMIN_MAIL, "Dalsi na rade v drafte", "Nasleduje: $r[email] ($r[uname])", $headers);
      else mail(ADMIN_MAIL, "Nahradny vyber", "Uskutocnenyyy: $j[name] za $b[name]", $headers);
      
      if($error!=1) $content .= "<script>top.location.href= '/fantasy/draft'; </script>"; 
      }
   
    $cof = mysql_query("SELECT dt.*, ft_choices.pos FROM ft_choices JOIN (SELECT * FROM ft_players WHERE uid='$uid')dt ON dt.pid=ft_choices.id WHERE ft_choices.pos='F'");
    $countf = 6-mysql_num_rows($cof);
    $cod = mysql_query("SELECT dt.*, ft_choices.pos FROM ft_choices JOIN (SELECT * FROM ft_players WHERE uid='$uid')dt ON dt.pid=ft_choices.id WHERE ft_choices.pos='D'");
    $countd = 3-mysql_num_rows($cod);
    $cogk = mysql_query("SELECT dt.*, ft_choices.pos FROM ft_choices JOIN (SELECT * FROM ft_players WHERE uid='$uid')dt ON dt.pid=ft_choices.id WHERE ft_choices.pos='GK'");
    $countgk = 1-mysql_num_rows($cogk);
    
    $pdhl = LANG_FANTASY_YOUR;
    $y = mysql_query("SELECT * FROM ft_teams WHERE uid='$uid'");
    $u = mysql_fetch_array($y);

    $n = mysql_query("SELECT * FROM ft_predraft WHERE uid='$uid'");
    if(mysql_num_rows($n)>0 && $narade==$u[pos])
      {
      $b = mysql_fetch_array($n);
      $options = json_decode($b[predraft], true);
      $i=0;
      while($i < count($options))
        {
        $pid = $options[$i]["pid"];
        $rnd = $options[$i]["round"];
        if($rnd==$round)
          {
          $w = mysql_query("SELECT * FROM ft_choices WHERE id='$pid'");
          $e = mysql_fetch_array($w);
          if($e[pos]=="F") { $countf=1; $countd=0; $countgk=0; }
          if($e[pos]=="D") { $countf=0; $countd=1; $countgk=0; }
          if($e[pos]=="GK") { $countf=0; $countd=0; $countgk=1; }
          $pdhl = LANG_FANTASY_SUBSTITUTE1;
          }
        $ids[] = $pid;
        $i++;
        }
      }
      
    if($uid==2606)
      {
      $h = mysql_query("SELECT * FROM e_xoops_users WHERE uid='2606'");
      $j = mysql_fetch_array($h);
      if($j[email]=="rrrrrrr@centrum.sk")
        {
        $content .= '<div class="alert alert-danger" role="alert">Dôležité! Prosím zmeňte si e-mail vo svojom <a href="/profile">profile</a>, pretože Vás nevieme kontaktovať ohľadne nasledujúceho výberu. Váš aktuálny e-mail <i>'.$j[email].'</i> nechce prijímať poštu. Ďakujeme.</div>';
        }
      }
    
    $content .= '
   <div class="row justify-content-center">
    <div class="col-sm-8 col-md-7">
     <div class="card shadow animated--grow-in mb-4">
      <div class="card-header">
        <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
          '.(mysql_num_rows($q)<100 ? sprintf(LANG_FANTASY_YOURPICK, $pdhl, $round):LANG_FANTASY_PLAYERSDRAFT).'
        </h6>
      </div>
      <div class="card-body">';
    if($pdhl=='Váš' && $predraftt==1) $content .= '<p class="p-fluid">'.LANG_FANTASY_PREDRAFTTEXT.'</p>'; 
    
    if($round % 2 == 0) $narade = $manazerov-$pick+1;
    else $narade = $pick;
    // predraft
    $pre = mysql_query("SELECT * FROM ft_players WHERE uid='$uid'");
    if(mysql_num_rows($pre)==0 && mysql_num_rows($n)==0 && $predraftt==1) $predraft=1;
    // zobrazit vyberacie polia
    if($narade==$u[pos] || $predraft==1)
      {
      $o = mysql_query("SELECT tstamp + INTERVAL $timeout MINUTE as tme FROM ft_players ORDER BY id DESC");
      $p = mysql_fetch_array($o);
      if(mysql_num_rows($o)==0) $min = $timeout;
      else $min = floor((strtotime($p[tme])-mktime())/60);
    //$content .= '<p class="p-fluid">Základný draft hráčov sa skončil a teraz sa čaká na oficiálne súpisky jednotlivých tímov Majstrovstiev sveta. Ak sa niektorý z hráčov neobjaví v konečnej zostave, bude tu uvedený šedou farbou a môžete si kliknutím na ikonku pri jeho mene vybrať náhradného hráča.</p>';
    $content .= '<div class="alert alert-info" role="alert"><i class="fas fa-hourglass-half"></i> '.sprintf(LANG_FANTASY_REMAINING, $min).'</div>
                 <form id="form" method="post" action="/fantasy/draft" enctype="multipart/form-data">';

    $content .= '<div class="alert alert-warning" role="alert"><i class="fas fa-users"></i> '.sprintf(LANG_FANTASY_ONLYFROMDB, $skratka).'</div>';
    
    if($countf>0)
      {
      $content .= '<div class="p-fluid m-0 mt-2" id="forward_container">'.LANG_FANTASY_YOUCANPICK.' <span class="text-primary"><b><span id="countf">'.$countf.'</span></b> '.LANG_FANTASY_FORWARDS1.'</span> (F):';
      $content .= "<select name='forward' id='forward' size='1' class='custom-select'><optgroup label='".LANG_FANTASY_FORWARDS."'><option value='0'>".LANG_FANTASY_NOCHOICE."</option>";
      $lab = "";
      if($pdhl==LANG_FANTASY_SUBSTITUTE1) { $in = implode($ids,","); $add = " && id NOT IN ($in) "; }
      else $add="";
      $r = mysql_query("SELECT * FROM ft_choices WHERE pos='F' && id NOT IN (SELECT pid FROM ft_players)$add ORDER BY teamlong ASC, name ASC");
      while($t = mysql_fetch_array($r))
        {
        $t[name] = eregi_replace("ELSE","E<b></b>LSE",$t[name]);
        if($lab!=$t[teamlong]) $content .= "</optgroup><optgroup label='$t[teamlong]' short='$t[teamshort]'>";
        $content .= "<option value='$t[id]'>$t[name]</option>";
        $lab = $t[teamlong];
        }
      $content .= "</optgroup></select>
                  </div>";
      }
    
    if($countd>0)
      {
      $content .= '<div class="p-fluid m-0 mt-2" id="defense_container">'.LANG_FANTASY_OR.' <span class="text-success"><b><span id="countd">'.$countd.'</span></b> '.LANG_FANTASY_DEFENSE1.'</span> (D):';
      $content .= "<select name='defense' id='defense' size='1' class='custom-select'><optgroup label='".LANG_FANTASY_DEFENSE."'><option value='0'>".LANG_FANTASY_NOCHOICE."</option>";
      $lab = "";
      if($pdhl==LANG_FANTASY_SUBSTITUTE1) { $in = implode($ids,","); $add = " && id NOT IN ($in) "; }
      else $add="";
      $r = mysql_query("SELECT * FROM ft_choices WHERE pos='D' && id NOT IN (SELECT pid FROM ft_players)$add ORDER BY teamlong ASC, name ASC");
      while($t = mysql_fetch_array($r))
        {
        $t[name] = eregi_replace("ELSE","E<b></b>LSE",$t[name]);
        if($lab!=$t[teamlong]) $content .= "</optgroup><optgroup label='$t[teamlong]' short='$t[teamshort]'>";
        $content .= "<option value='$t[id]'>$t[name]</option>";
        $lab = $t[teamlong];
        }
      $content .= "</optgroup></select>
                  </div>";
      }
    
    if($countgk>0)
      {
      $content .= '<div class="p-fluid m-0 mt-2" id="goalie_container">'.LANG_FANTASY_ORYOUCANDRAFT.' <span class="text-danger"><b><span id="countgk">'.$countgk.'</span></b> '.LANG_FANTASY_GOALIETEAM.'</span> (GK):';
      $content .= "<select name='goalie' id='goalie' size='1' class='custom-select'><optgroup label='".LANG_TEAMSTATS_GOALIES."'><option value='0'>".LANG_FANTASY_NOCHOICE."</option>";
      $lab = "";
      if($pdhl=="Náhradný") { $in = implode($ids,","); $add = " && id NOT IN ($in) "; }
      else $add="";
      $r = mysql_query("SELECT * FROM ft_choices WHERE pos='GK' && id NOT IN (SELECT pid FROM ft_players)$add ORDER BY teamlong ASC, name ASC");
      while($t = mysql_fetch_array($r))
        {
        $t[name] = eregi_replace("ELSE","E<b></b>LSE",$t[name]);
        $content .= "<option value='$t[id]' short='$t[teamshort]'>$t[name]</option>";
        $lab = $t[teamlong];
        }
      $content .= "</optgroup></select>
                  </div>";
      }
    
    $content .= '</form>';
    if($predraft==1) $content .= "<div id='predrafted' class='d-none'><p class='font-weight-bold text-center'>".LANG_FANTASY_YOURPREDRAFT.":</p></div>";
    }
    else
    {
    //$content .= '<div class="alert alert-info" role="alert">'.LANG_FANTASY_NOTYOURTURN.'<br><br>'.LANG_FANTASY_MISSED.'</div>';
    $content .= '<div class="alert alert-info" role="alert">'.LANG_FANTASY_NOTYOURTURN.'<br><br>'.LANG_FANTASY_MISSEDICON.'</div>';
    //$content .= '<div class="alert alert-info" role="alert"><i class="fas fa-hourglass-half"></i> '.LANG_FANTASY_WAITINGFORROSTERS.'</div>';
    }
    
    $n = mysql_query("SELECT * FROM ft_predraft WHERE uid='$uid'");
    if(mysql_num_rows($n)>0)
      {
      $b = mysql_fetch_array($n);
      $content .= "<div id='predrafted'><p class='font-weight-bold text-center'>".LANG_FANTASY_YOURPREDRAFT.":</p>";
      $options = json_decode($b[predraft], true);
      $i=0;
      while($i < count($options))
        {
        $pid = $options[$i]["pid"];
        $rnd = $options[$i]["round"];
        $v = mysql_query("SELECT * FROM ft_choices WHERE id='$pid'");
        $c = mysql_fetch_array($v);
        $content .= "<table class='table-hover table-light table-striped table-responsive-sm w-100 p-fluid'><tr".($rnd==$round ? " class='bg-gray-200'" : "")."><td width='20%'>".$rnd.".".LANG_ROUND."</td width='20%'><td><img class='flag-iihf ".$c[teamshort]."-small' src='/images/blank.png' alt='".$c[teamlong]."'> ".$c[teamshort]."</td><td width='60%'>".$c[name]."</td></tr></table>";
        $i++;
        }
      $content .= "</div>";
      }
    
      if($narade==$u[pos] && ($countf>0 || $countd>0 || $countgk>0) || $predraft==1) $content .= '<button class="btn btn-'.$leaguecolor.' mt-3" id="draft_button" onclick="'.($predraft==1 ? "PreDraft();" : "$('#form').submit()").'">
            '.($predraft==1 ? LANG_FANTASY_ADDTOTEAM : LANG_FANTASY_SELECTTHIS).'
          </button>';
          $content .= '
       </div>
     </div>
    </div>
   </div>';
  
    $content .= Show_Drafted();
    }
  }
 
  if(!$uid || mysql_num_rows($m)==0)
    {
    $title = $nazov." - ".LANG_FANTASY_PLAYERSDRAFT;
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($league[longname])." text-gray-600 mr-1'></i>
                 <h1 class='h3 h3-fluid mb-1'>".$nazov."</h1>
                 <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".LANG_FANTASY_PLAYERSDRAFT."</h2>
                 <div style='max-width: 1000px;'>";
    $content .= '<div class="alert alert-info" role="alert">'.sprintf(LANG_FANTASY_DRAFTTEXT, $nazov, $manazerov).'</div>';
    $content .= Show_Drafted();
    }
    
  $content .= '
    <div class="card shadow my-4">
        <div class="card-body">
        '.GenerateComments(4,0).'
        </div>
    </div>
  </div>';
  }
elseif($params[0]=="signin")
  {
		if(isset($_POST['active']))
			{
			$uid = $_SESSION['logged'];
      $title = $nazov;
      $q = mysql_query("SELECT * FROM ft_teams WHERE uid='$uid'");
      $w = mysql_query("SELECT * FROM ft_teams WHERE active='1'");
      $count = mysql_num_rows($w);
      $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($league[longname])." text-gray-600 mr-1'></i>
                   <h1 class='h3 h3-fluid mb-1'>".$nazov."</h1>
                   <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".LANG_FANTASY_SIGNIN."</h2>
                   <div style='max-width: 1000px;'>";
      if(mysql_num_rows($q)>0)
        {
        $content .= '<div class="alert alert-warning" role="alert">
                      <p><i class="fas fa-hourglass-half"></i> '.LANG_FANTASY_NOTAGAIN.'</p>
                      <p>'.LANG_FANTASY_WAITFOROTHERS.'</p>
                      <p class="font-weight-bold">'.sprintf(LANG_FANTASY_CURRENTLYSIGNED, $count, $manazerov).'</p>
                     </div>';
        }
      else 
        {
        mysql_query("INSERT INTO ft_teams (uid, active) VALUES ('$uid', '$_POST[active]')");
        $headers = 'From: '.SITE_MAIL. "\r\n" .
            'Reply-To: '.SITE_MAIL. "\r\n" .
            'X-Mailer: PHP/' . phpversion();
        $count++;
        mail(ADMIN_MAIL, "Novy manazer v drafte", "user ID: ".$uid.". Pocet: ".$count."/".$manazerov, $headers);
        $content .= '<div class="alert alert-success" role="alert">
                      <p><i class="fas fa-hourglass-half"></i> '.LANG_FANTASY_SUCCESSSIGN.'</p>
                      <p>'.LANG_FANTASY_WAITFOROTHERS.'</p>
                      <p class="font-weight-bold">'.sprintf(LANG_FANTASY_CURRENTLYSIGNED, $count, $manazerov).'</p>
                     </div>';
        }
      $content .= '</div>';
			}
	else
    {
	if($_SESSION['logged'])
    {
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($league[longname])." text-gray-600 mr-1'></i>
                 <h1 class='h3 h3-fluid mb-1'>".$nazov."</h1>
                 <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".LANG_FANTASY_SIGNIN."</h2>
                 <div style='max-width: 1000px;'>";
                 
    $w = mysql_query("SELECT * FROM ft_teams WHERE uid='$uid'");
    if(mysql_num_rows($w)>0)
      {
      $e = mysql_query("SELECT * FROM ft_teams WHERE active='1'");
      $count = mysql_num_rows($e);
      $title = $nazov;
                   
      $content .= '<div class="alert alert-warning" role="alert"><i class="fas fa-hourglass-half"></i> '.LANG_FANTASY_WAITFOROTHERS.'</div>
                   <div class="alert alert-info font-weight-bold" role="alert"><i class="fas fa-users"></i> '.sprintf(LANG_FANTASY_CURRENTLYSIGNED, $count, $manazerov).'</div>';
      if($count>=$manazerov) $content .= '
                   <div class="alert alert-success" role="alert"><i class="fas fa-thumbs-up text-success"></i> '.sprintf(LANG_FANTASY_ALLSET, $manazerov, date("j.n.Y H:i",strtotime($draft_start))).'</div>';
      }
    else
      {
		$q = mysql_query("SELECT * FROM ft_teams");
		$title = $nazov;
                   
    $content .= '<form id="form" method="post" action="/fantasy/signin" enctype="multipart/form-data">';
    if(mysql_num_rows($q)<$manazerov) { $content .= '
                  <div class="custom-control custom-checkbox text-center">
                    <input type="checkbox" class="custom-control-input" id="checkbox-2" value="1" name="active">
                    <label class="custom-control-label" for="checkbox-2">'.sprintf(LANG_FANTASY_SIGNINTEXT, $article_id, $nazov, LANG_FANTASY_MANAGER1).'</label>
                  </div>'; }
		else { $content .= '
                  <div class="alert alert-info" role="alert">
                    '.LANG_FANTASY_SIGNININFO.'
                  </div>
                  <div class="custom-control custom-checkbox text-center">
                    <input type="checkbox" class="custom-control-input" id="checkbox-2" value="0" name="active">
                    <label class="custom-control-label" for="checkbox-2">'.sprintf(LANG_FANTASY_SIGNINTEXT, $article_id, $nazov, LANG_FANTASY_SUBSTITUTE).'</label>
                  </div>'; }
    $content .= ' <p class="text-center"><button class="btn btn-'.$leaguecolor.' my-2" type="submit" form="form">'.LANG_NAV_LOGIN.'</button></p>
                </form>';
      }
    $content .= '</div>';
    }
  else
    {
    $title = $nazov;
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($league[longname])." text-gray-600 mr-1'></i>
                   <h1 class='h3 h3-fluid mb-1'>".$nazov."</h1>
                   <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$league[longname]."</h2>
                   <div style='max-width: 1000px;'>";
                   
    $content .= '<div class="alert alert-danger" role="alert">
                  '.sprintf(LANG_FANTASY_NOTLOGGED1, $nazov).'
                 </div>
    </div>';
    }
    }
  }
?>