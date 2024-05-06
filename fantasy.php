<?php
$params = explode("/", htmlspecialchars($_GET[id]));

$nazov = "Fantasy Championship";
$menu = "MS 2024";
$skratka = "MS";
$manazerov = 10;
$article_id = 2484;
$league_id = 153;
//$timeout = 480;
$predraftt = 1; // = draftuje sa do zásobníka. ak 1, upraviť počet manažérov aj v includes/fantasy_functions.php
$knownrosters = 0; // = su zname zostavy (do ft_choices pridat hracov, ktori sa zucastnia)
$article_rosters = 2487;
$draft_start = "2024-05-02 09:00:00";
$league_start = "2024-05-10 16:20:00";

/*
1. nastaviť dátum deadlinu
2. odremovať potrebné veci
3. vyprázdniť ft_players, ft_predraft, ft_teams, ft_choices a ft_changes
4. zmeniť link v menu
5. vypnúť/zapnúť cronjob pre neaktivitu a nulovanie bodov
6. ak je knownroster=1 do ft_choices pridat hracov a brankarov, ktori sa zucastnia
7. odoslať pozvánkové maily na základe reakčného času avg_time (menej ako 10k) v e_xoops_users
*/

if($_GET[cron]==1) {
  include("includes/db.php");
  include("includes/lang/lang_sk.php");
}
else include("includes/advert_bigscreenside.php");

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
//if($uid==2) { $uid=3203; /*$_SESSION[logged]=215;*/ } 

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
    Global $round, $skratka, $knownrosters;
    if($knownrosters==0) {
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
      $y = mysql_query("SELECT * FROM 2004goalies WHERE teamshort IN ($teams) && name NOT IN (SELECT c.name FROM `ft_players` JOIN ft_choices c ON ft_players.pid=c.id) ORDER BY rand() LIMIT 1");
    }
    else $y = mysql_query("SELECT * FROM `ft_choices` WHERE pos='GK' && id NOT IN (SELECT pid FROM ft_players) ORDER BY rand() LIMIT 1");
    $u = mysql_fetch_array($y);
    if($knownrosters==0) mysql_query("REPLACE INTO ft_choices (id, teamshort, teamlong, pos, name) VALUES ('$u[id]', '$u[teamshort]', '$u[teamlong]', 'GK', '$u[name]')");
    mysql_query("INSERT INTO ft_players (uid, pid, round, type, gk) VALUES ('$uid', '$u[id]', '$round', '1', '1')");
    }
    
  function PickPlayer($uid, $pos)
    {
    Global $round, $predraftt, $skratka, $knownrosters;
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
    if($knownrosters==0) mysql_query("REPLACE INTO ft_choices (id, teamshort, teamlong, pos, name) VALUES ('$x[id]', '$x[teamshort]', '$x[teamlong]', '$x[pos]', '$x[name]')");
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
        mysql_query("UPDATE ft_teams SET last_mail_round='".$round."' WHERE pos='".$narade."'");
        mysql_query("INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$narade."', '2', '0', NOW())");
        mail(ADMIN_MAIL, "Dalsi na rade v drafte", "Vykonany nahodny vyber. Nasleduje: $h[email]", $headers);
        }
    }
  if(mysql_num_rows($w)==0) {
    $n = mysql_query("SELECT * FROM ft_predraft WHERE uid='$narade'");
    if(mysql_num_rows($n)>0)
      {
      include("includes/fantasy_functions.php");
      PreDraft();
      $pd=1;
      }
  }
  mysql_close($link);
  exit;
  }

if($params[0]=="draft")
  {
  // odosle info o dostupnych zostavach do draft_autocomplete.php
  if($knownrosters==1) $_SESSION["knownrosters"]=1;
  else $_SESSION["knownrosters"]=0;
  $title = $nazov." - ".LANG_FANTASY_PLAYERSDRAFT;
  $hra = mysql_query("SELECT t.*, u.push_id FROM ft_teams t LEFT JOIN e_xoops_users u ON u.uid=t.uid WHERE t.uid='$uid';");
  if(strtotime($draft_start)>time()) {
    // draft ešte nezačal
    $content .= "<div id='toasts' class='fixed-top' style='top: 80px; right: 23px; left: initial; z-index:3;'></div>
                 <i class='float-left h1 h1-fluid ll-".LeagueFont($league[longname])." text-gray-600 mr-1'></i>
                 <h1 class='h3 h3-fluid mb-1'>".$nazov."</h1>
                 <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".LANG_FANTASY_PLAYERSDRAFT."</h2>
                 <div class='row'>
                    <div class='col-12' style='max-width: 1000px;'>";
    $content .= '  
                      <div class="row justify-content-center">
                        <div class="col-sm-8 col-md-7">
                         <div class="card shadow animated--grow-in mb-4">
                          <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-success">
                              Draft sa ešte nezačal
                            </h6>
                          </div>
                          <div class="card-body alert-info">
                            <p class="text-center m-0"><i class="fas fa-info-circle mr-1"></i>Predpokladaný začiatok draftu je <strong>'.date("j.n. \o G:i", strtotime($draft_start)).'</strong></p>
                          </div>
                         </div>
                        </div>
                       </div>';
                       
    $content .= Show_Drafted();
  }
  else {
    if($uid) {
      if(mysql_num_rows($hra)>0) { // ak je prihlasenym manazerom
        $q = mysql_query("SELECT * FROM ft_players ORDER BY round DESC, id DESC");
        $f = mysql_fetch_array($q);
        $po = mysql_query("SELECT * FROM ft_players WHERE round='$f[round]'");
        $poc = mysql_num_rows($po);
        $push = mysql_fetch_array($hra);
        $title = $nazov." - ".LANG_FANTASY_PLAYERSDRAFT;
        
        $content .= "<div id='toasts' class='fixed-top' style='top: 80px; right: 23px; left: initial; z-index:3;'></div>
                     <i class='float-left h1 h1-fluid ll-".LeagueFont($league[longname])." text-gray-600 mr-1'></i>
                     <h1 class='h3 h3-fluid mb-1'>".$nazov."</h1>
                     <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".LANG_FANTASY_PLAYERSDRAFT."</h2>
                     <div class='row'>
                        <div class='col-12' style='max-width: 1000px;'>";
        
        // zistit aktualne kolo a poradie manazera na rade
        if($poc<$manazerov) {
          $pick = $poc+1;
          $round = $f[round];
          if(mysql_num_rows($q)==0) $round=1;
        }
        else {
          $pick = 1;
          $round = $f[round]+1;
        }
        if($round % 2 == 0) $narade = $manazerov-$pick+1;
        else $narade = $pick;

        // nacitat skor ulozene vybery do array
        $m = mysql_query("SELECT predraft FROM ft_predraft WHERE uid='".$uid."'");
        if(mysql_num_rows($m)>0) {
            $n = mysql_fetch_array($m);
            $picks = json_decode($n[predraft], true);
        }
        else $picks = array(0=>array('pid'=>0,'round'=>1,'gk'=>0), 1=>array('pid'=>0,'round'=>2,'gk'=>0),2=>array('pid'=>0,'round'=>3,'gk'=>0),3=>array('pid'=>0,'round'=>4,'gk'=>0),4=>array('pid'=>0,'round'=>5,'gk'=>0),5=>array('pid'=>0,'round'=>6,'gk'=>0),6=>array('pid'=>0,'round'=>7,'gk'=>0),7=>array('pid'=>0,'round'=>8,'gk'=>0),8=>array('pid'=>0,'round'=>9,'gk'=>0),9=>array('pid'=>0,'round'=>10,'gk'=>0));

        // zistit obsadene pozicie
        $k=0;
        $numgk = 1;
        $numd = 3;
        $numf = 6;
        while($k < count($picks)) {
            // zistit, ci manazer uz dane kolo nedraftoval
            $x = mysql_query("SELECT * FROM ft_players WHERE uid='".$uid."' && round='".$picks[$k][round]."'");
            if(mysql_num_rows($x)>0) {
                // naplnit kolo hracom z draftu namiesto hraca z ulozeneho vyberu
                $z = mysql_fetch_array($x);
                $picks[$k][pid] = $z[pid];
            }
            // overit, ci uz hrac nebol draftovany manazerom pred nim *alebo* samotnym manazerom v skorsom kole *alebo* sa hrac nezucastni
            $c = mysql_query("SELECT * FROM ft_players WHERE pid='".$picks[$k][pid]."' && (uid!='".$uid."' || uid='".$uid."' && round<'".$picks[$k][round]."' || type='2')");
            if(mysql_num_rows($c)==0) {
                $q = mysql_query("SELECT * FROM ft_choices WHERE id='".$picks[$k][pid]."'");
                $f = mysql_fetch_array($q);
                if($f[pos]=="GK") $numgk--;
                if($f[pos]=="D" || $f[pos]=="LD" || $f[pos]=="RD") $numd--;
                if($f[pos]=="F" || $f[pos]=="CE" || $f[pos]=="RW" || $f[pos]=="LW") $numf--;
            }
            $k++;
        }
        if($numgk<0) $numgk=0;
        if($numd<0) $numd=0;
        if($numf<0) $numf=0;
        if($numgk==0 && $numd==0 && $numf==0) { $status = LANG_FANTASY_PICKSACTIVE; $color = "success"; }
        else { $status = LANG_FANTASY_PICKSINACTIVE; $color = "danger"; }
            
        $content .= '
       <div class="row justify-content-center">
        <div class="col-sm-8 col-md-7">
         <div class="card shadow animated--grow-in mb-4">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
              '.LANG_FANTASY_PICKSTITLE3.'
            </h6>
          </div>
          <div class="card-body">
            '.($knownrosters==1 ? '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i>'.sprintf(LANG_FANTASY_ONLYFROMROSTERS, $menu, $article_rosters).'</div>':'<div class="alert alert-info"><i class="fas fa-info-circle mr-1"></i>'.sprintf(LANG_FANTASY_ONLYFROMDB1, $menu).'</div>').'
            '.($push["push_id"]==NULL ? '<div class="alert alert-warning"><i class="fas fa-mobile-screen-button mr-1"></i>'.LANG_FANTASY_TURNONPUSH.'</div>':'').'
            <p class="h6-fluid m-0">'.LANG_FANTASY_REMAINING1.':</p>
            <div class="row text-center mb-3 p-fluid">
                <div class="col-4 border rounded bg-gray-100 p-1"><span id="numf" class="font-weight-bold h5 text-primary">'.$numf.'</span><br>'.LANG_FANTASY_FORWARDS1.'</div>
                <div class="col-4 border rounded bg-gray-100 p-1"><span id="numd" class="font-weight-bold h5 text-success">'.$numd.'</span><br>'.LANG_FANTASY_DEFENSE1.'</div>
                <div class="col-4 border rounded bg-gray-100 p-1"><span id="numgk" class="font-weight-bold h5 text-danger">'.$numgk.'</span><br>'.LANG_PLAYERSTATS_GK.'</div>
            </div>
            <form id="picks-form">';
            $i=1;
            while($i<11) {
                $player=$isvalid=$readonly=$icon=$hidden=$tooltip='';
                $j=0;
                // naplnit kola uz vybratymi hracmi
                while($j < count($picks)) {
                    $already=0;
                    if($picks[$j][round]==$i) {
                        // zistit, ci manazer uz dane kolo nedraftoval
                        $x = mysql_query("SELECT * FROM ft_players WHERE uid='".$uid."' && round='".$i."'");
                        if(mysql_num_rows($x)>0) {
                            // naplnit kolo hracom z draftu namiesto hraca z ulozeneho vyberu
                            $z = mysql_fetch_array($x);
                            $picks[$j][pid] = $z[pid];
                        }
                        if($picks[$j][gk]==1) { $b = mysql_query("SELECT * FROM 2004goalies WHERE id='".$picks[$j][pid]."'"); $pos = "GK"; }
                        else $b = mysql_query("SELECT * FROM 2004players WHERE id='".$picks[$j][pid]."'");
                        $v = mysql_fetch_array($b);
                        if($v[pos]=="D" || $v[pos]=="LD" || $v[pos]=="RD") $pos = "D";
                        if($v[pos]=="F" || $v[pos]=="CE" || $v[pos]=="RW" || $v[pos]=="LW") $pos = "F";
                        if(mysql_num_rows($b)>0) $player = '('.$pos.') '.$v[name];
                        else $player = '';
                        $hidden = $pos."-0-".$picks[$j][pid];
                        // overit, ci uz hrac nebol draftovany manazerom pred nim *alebo* samotnym manazerom v skorsom kole *alebo* sa hrac nezucastni
                        $c = mysql_query("SELECT * FROM ft_players WHERE pid='".$picks[$j][pid]."' && (uid!='".$uid."' || uid='".$uid."' && round<'".$i."' || type='2')");
                        if(mysql_num_rows($c)>0 || mysql_num_rows($b)==0) {
                            // uz bol draftovany alebo hrac uplne chyba
                            $isvalid = " is-invalid";
                            $readonly = "";
                            $icon = '';
                        }
                        else {
                            // zobrazit upozornenie, ak tohto hraca uz ma naplanovaneho draftovat niekto pred nim
                            $user_po = mysql_query("SELECT pos FROM ft_teams WHERE uid='".$uid."'");
                            $user_pos = mysql_fetch_array($user_po);
                            $k=0;
                            while($k < $i) {
                              $zp = mysql_query("SELECT p.uid, t.pos, JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(CAST(predraft as CHAR), '$[".$k."]'),'$.pid')) as pid, JSON_UNQUOTE(JSON_EXTRACT(JSON_EXTRACT(CAST(predraft as CHAR), '$[".$k."]'),'$.round')) as round FROM ft_predraft p LEFT JOIN ft_teams t ON t.uid=p.uid WHERE p.uid!='".$uid."' ORDER BY pos");
                              while($zph = mysql_fetch_array($zp)) {
                                if(($k+1) % 2 == 0 && ($k+1)==$zph[round] && $zph[pos]<$user_pos[pos] && $zph[pid]==$picks[$j][pid]) {
                                  $already=1;
                                  break;
                                }
                                if(($k+1) % 2 != 0 && ($k+1)==$zph[round] && $zph[pos]>$user_pos[pos] && $zph[pid]==$picks[$j][pid]) {
                                  $already=1;
                                  break;
                                }
                              }
                            if($already==1) break;
                            $k++;
                            }
                            $isvalid = " is-valid";
                            if($already==1) {
                              $isvalid .= " is-doubt";
                              $tooltip = 'data-toggle="tooltip" data-placement="top" title="'.LANG_FANTASY_ALREADYTOOLTIP.'" ';
                            }
                            $readonly = " readonly";
                            $icon = '<a href="#" class="btn btn-danger btn-sm remove-pick" data-pick="'.$i.'"><i class="fas fa-times-circle"></i></a>';
                        }
                    }
                    $j++;
                }
                $content .= '
            <div class="row">
                <div class="col-12 col-lg-2 align-self-center">'.$i.'.'.LANG_ROUND.'</div>
                <div class="col-10 col-lg-9">
                    <input class="form-control pick-player'.$isvalid.'" '.$tooltip.'type="text" data-pick="'.$i.'" placeholder="'.LANG_FANTASY_PICKPLACEHOLDER.'" value="'.$player.'"'.$readonly.'>
                    <input type="hidden" id="pick-'.$i.'" value="'.$hidden.'">
                </div>
                <div class="col-2 col-lg-1 align-self-center"><span class="pick-icon">'.$icon.'</span></div>
            </div>';
                $i++;
            }
          $content .= '
            <input type="hidden" id="picks">
            </form>
          </div>
          <div class="card-footer p-fluid text-center text-white bg-'.$color.'" id="draft-status">'.$status.'</div>
         </div>
        </div>
       </div>';
      
        $content .= Show_Drafted();
      }
    }
    
    if(!$uid || mysql_num_rows($hra)==0) {
      $title = $nazov." - ".LANG_FANTASY_PLAYERSDRAFT;
      $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($league[longname])." text-gray-600 mr-1'></i>
                   <h1 class='h3 h3-fluid mb-1'>".$nazov."</h1>
                   <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".LANG_FANTASY_PLAYERSDRAFT."</h2>
                   <div class='row'>
                      <div class='col-12' style='max-width: 1000px;'>";
      $content .= '<div class="alert alert-info" role="alert">'.sprintf(LANG_FANTASY_DRAFTTEXT, $nazov, $manazerov).'</div>';
      $content .= Show_Drafted();
    }
  }
    
  $content .= '
    <div class="card shadow my-4">
        <div class="card-body">
        '.GenerateComments(4,0).'
        </div>
    </div>
   </div> <!-- end col -->
   <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block">
    '.$advert.'
   </div> <!-- end col -->
   </div> <!-- end row -->';
  }

if($params[0]=="picks")
  {
  $title = "$nazov - ".LANG_FANTASY_PICKSTITLE;
  $content .= "  <div class='modal fade' id='dialog' tabindex='-1' role='dialog' aria-labelledby='dialogTitle' aria-hidden='true'></div>
                <i class='float-left h1 h1-fluid ll-".LeagueFont($league[longname])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".$nazov."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".LANG_FANTASY_PICKSTITLE1."</h2>
                <div class='row'>
                    <div class='col-12' style='max-width: 1000px;'>";

  $r = mysql_query("SELECT t.uid, t.points, t.prev_points, u.uname, u.user_avatar FROM ft_teams t LEFT JOIN e_xoops_users u ON u.uid=t.uid WHERE t.active='1' ORDER BY t.points DESC, t.pos ASC");
  //$content .= '<div class="alert alert-info">Keďže nastala na prvej priečke rovnosť bodov medzi dvoma manažérmi, museli sme zaviesť rozhodovacie pravidlo, ktorým je menší počet výmen hráčov v zostave. Tento rozstrel vyhral v pomere 31:121 manažér <b>Athletix</b>, ktorému gratulujeme! Aby sa ale <b>pegina</b> nehnevala, odmeňujeme takisto aj druhé miesto v drafte :)</div>';
   
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
        <div class="row no-gutters">';
          $dnes = date("Y-m-d", mktime());
          $excl = mysql_query("SELECT * FROM `2004matches` WHERE datetime > '$dnes 00:00:00' && datetime < now() && kedy='na programe' && league='".$league_id."'");
          while($exclude = mysql_fetch_array($excl))
            {
            $exc[] = $exclude[team1short];
            $exc[] = $exclude[team2short];
            }
          $y = mysql_query("SELECT ft_players.*, ft_players.g+ft_players.a as points, t1.*, IF(t1.pos='F',1,IF(t1.pos='D',2,3)) as zor FROM ft_players JOIN ft_choices t1 ON t1.id=ft_players.pid WHERE uid='$uid' ORDER BY zor ASC, ft_players.id ASC");
          while($u = mysql_fetch_array($y))
            {
            $players[] = array($u[teamshort], $u[name], $u[pos], $u[g], $u[a], $u[points], $u[w], $u[so], $u[pid]);
            }
          $i=0;
          while($i < 6)
            {
            $content .= '
             <div class="col text-center mb-3">
              <div class="card h-100 '.($i==0 ? 'mr-1':($i==5 ? 'ml-1':'mx-1')).'">
                <div class="card-header p-fluid font-weight-bold p-1">
                  <img class="flag-iihf '.$players[$i][0].'-small" src="/images/blank.png" alt="'.$players[$i][0].'"> '.$players[$i][1].'
                </div>
                <div class="card-body">
                  <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$players[$i][1].'" class="lazy rounded-circle img-thumbnail" style="width:100px; height:100px; max-width:100px; object-fit: cover; object-position: center top;">
                  <p class="p-fluid">'.LANG_PLAYERSTATS_F.'<br>
                  <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][3].'+'.$players[$i][4].'</span></p>
                </div>
                <div class="footer">
                  '.(in_array($players[$i][0], $exc) ? '<span data-toggle="tooltip" data-placement="bottom" title="Momentálne sa nedá vymeniť. Hrá zápas."><a href="#" class="btn btn-sm btn-block btn-secondary disabled"><i class="fas fa-ban"></i></a></span>':'<a href="#" class="btn btn-sm btn-block btn-light change-player" data-pid="'.$players[$i][8].'" data-toggle="tooltip" data-placement="bottom" title="Vymeniť hráča"><i class="fas fa-retweet"></i></a>').'
                </div>
              </div>
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
             <div class="col text-center mb-3">
              <div class="card h-100">
                <div class="card-header p-fluid font-weight-bold p-1">
                  <img class="flag-iihf '.$players[$i][0].'-small" src="/images/blank.png" alt="'.$players[$i][0].'"> '.$players[$i][1].'
                </div>
                <div class="card-body">
                  <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$players[$i][1].'" class="lazy rounded-circle img-thumbnail" style="width:100px; height:100px; max-width:100px; object-fit: cover; object-position: center top;">
                  <p class="p-fluid">'.LANG_PLAYERSTATS_D.'<br>
                  <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][3].'+'.$players[$i][4].'</span></p>
                </div>
                <div class="footer">
                  '.(in_array($players[$i][0], $exc) ? '<span data-toggle="tooltip" data-placement="bottom" title="Momentálne sa nedá vymeniť. Hrá zápas."><a href="#" class="btn btn-sm btn-block btn-secondary disabled"><i class="fas fa-ban"></i></a></span>':'<a href="#" class="btn btn-sm btn-block btn-light change-player" data-pid="'.$players[$i][8].'" data-toggle="tooltip" data-placement="bottom" title="Vymeniť hráča"><i class="fas fa-retweet"></i></a>').'
                </div>
              </div>
             </div>';
            $i++;
            }
        $content .= '
        </div>
        <h5 class="card-title text-'.$leaguecolor.' h5-fluid">'.LANG_TEAMSTATS_GOALIES.'</h5>
        <div class="row">
         <div class="col text-center mb-3">
          <div class="card h-100">
            <div class="card-header p-fluid font-weight-bold p-1">
              '.$players[9][1].'
            </div>
            <div class="card-body">
              <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$players[$i][1].'" class="lazy rounded-circle img-thumbnail" style="width:100px; height:100px; max-width:100px; object-fit: cover; object-position: center top;">
              <p class="p-fluid">'.LANG_PLAYERSTATS_GK.'<br>
                <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][6].' '.LANG_MATCHES_WINS1.'</span><br>
                <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][7].' '.LANG_FANTASY_SO.'</span>
              </p>
            </div>
            <div class="footer">
              '.(in_array($players[$i][0], $exc) ? '<span data-toggle="tooltip" data-placement="bottom" title="Momentálne sa nedá vymeniť. Hrá zápas."><a href="#" class="btn btn-sm btn-block btn-secondary disabled"><i class="fas fa-ban"></i></a></span>':'<a href="#" class="btn btn-sm btn-block btn-light change-player" data-pid="'.$players[$i][8].'" data-toggle="tooltip" data-placement="bottom" title="Vymeniť brankára"><i class="fas fa-retweet"></i></a>').'
            </div>
          </div>
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
     //$t[points]=0;
     //if($t[uid]==2932) $t[points]=$t[points]-1;
    if($t[prev_points]<>$t[points]) 
      {
      $diffn = $t[points]-$t[prev_points];
      $diff = '<span class="text-success text-xs"> (+'.$diffn.')</span>';
      }
    else $diff='';
    if($t[user_avatar]!="") $avatar = "<img class='rounded-circle mr-1' src='/images/user_avatars/".$t[uid].".".$t[user_avatar]."?".filemtime('images/user_avatars/'.$t[uid].'.'.$t[user_avatar])."' alt='".$t[uname]."' style='width:2rem;height:2rem;vertical-align:-11px;'>";
    else $avatar = "<i class='text-gray-300 fas fa-user-circle fa-2x mr-1' style='width:2rem;height:2rem;vertical-align:-7px;'></i>";
    $content .= "<tr><td class='text-center$add'>$i.</td><td class='$add'><a href='#$t[uname]'>".$avatar."$t[uname]</a></td><td class='$add'><b>$t[points]</b>".$diff."</td></tr>";
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
             
  $content .= "
      <div class='alert alert-info'>
        <p>".sprintf(LANG_FANTASY_CHANGEPLAYERS, "<a href='mailto:redxakcia@hockeyx-lixve.sk' onmouseover='this.href=this.href.replace(/x/g,\"\");'>")."</a></span>.</p>
        <p>".sprintf(LANG_BETS_BUYMEABEERTEXT, $nazov, LANG_BETS_BUYMEABEER, "1cc88a")."</p>
      </div>
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
  $q = mysql_query("SELECT ft_changes.*, IF(p.pos='GK',1,0) as gk, u.uname FROM `ft_changes` LEFT JOIN ft_choices p ON ft_changes.old_pid=p.id JOIN e_xoops_users u ON ft_changes.uid=u.uid ORDER BY tstamp DESC LIMIT 10");
  while($f = mysql_fetch_array($q))
    {
    if($f[gk]==1) $p = mysql_query("SELECT g1.teamshort as old_tshort, g1.name as old_name, g2.teamshort as new_tshort, g2.name as new_name FROM 2004goalies g1 LEFT JOIN 2004goalies g2 ON g2.id='".$f[new_pid]."' WHERE g1.id='".$f[old_pid]."'");
    else $p = mysql_query("SELECT p1.teamshort as old_tshort, p1.name as old_name, p2.teamshort as new_tshort, p2.name as new_name FROM 2004players p1 LEFT JOIN 2004players p2 ON p2.id='".$f[new_pid]."' WHERE p1.id='".$f[old_pid]."'");
    $o = mysql_fetch_array($p);
    if(date('Y-m-d',strtotime($f[tstamp]))==date("Y-m-d", mktime())) $hl="dnes o <b>".date('G:i', strtotime($f[tstamp]))."</b>";
    elseif(date('Y-m-d',strtotime($f[tstamp]))==date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')))) $hl="včera o ".date('G:i', strtotime($f[tstamp]));
    else $hl=date('j.n.',strtotime($f[tstamp])). " ".LANG_AT." ".date('G:i', strtotime($f[tstamp]));
    $content .= '<tr><td class="text-nowrap">'.$hl.'</td><td><a href="/user/'.$f[uid].'">'.$f[uname].'</a></td><td class="text-nowrap"><img class="flag-iihf '.$o[old_tshort].'-small" src="/images/blank.png" alt="'.$o[old_tshort].'"> <a href="/'.($f[gk]==1 ? 'goalie':'player').'/'.$f[old_pid].'0-'.SEOtitle($o[old_name]).'" data-toggle="popover" data-player="'.$o[old_name].'|'.$f[gk].'">'.$o[old_name].'</a></td><td class="text-nowrap"><img class="flag-iihf '.$o[new_tshort].'-small" src="/images/blank.png" alt="'.$o[new_tshort].'"> <a href="/'.($f[gk]==1 ? 'goalie':'player').'/'.$f[new_pid].'0-'.SEOtitle($o[new_name]).'" data-toggle="popover" data-player="'.$o[new_name].'|'.$f[gk].'">'.$o[new_name].'</a></td></tr>';
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
    $w = mysql_query("SELECT ft_players.*, ft_players.g+ft_players.a as points, t1.teamshort, t1.teamlong, t1.pos, t1.name, IF(t1.pos='F',1,IF(t1.pos='D',2,3)) as zor FROM ft_players JOIN ft_choices t1 ON t1.id=ft_players.pid WHERE uid='$f[uid]' ORDER BY zor ASC, points DESC, ft_players.g DESC, ft_players.a DESC, ft_players.w DESC, ft_players.so DESC");
    $i = 1;
    $pts=$goals=$asists=$wins=$so=0;
    while($e = mysql_fetch_array($w))
      {
      // tiez prec
      //if($e[name]=="VAN RIEMSDYK James") $e[a]=$e[a]-1;
      $pts = $pts+$e[g]+$e[a]+($e[w]*2)+($e[so]*2);
      $goals = $goals+$e[g];
      $asists = $asists+$e[a];
      $wins = $wins+$e[w];
      $so = $so+$e[so];
      if($e[pos]!="GK") { $e[w]=""; $e[so]=""; }
      // zmazat
       //$e[g]=$e[a]=$goals=$asists=$pts=0;
      $tbody .= '<tr><td class="text-center">'.$i.'.</td><td class="text-center">'.$e[pos].'</td><td class="text-nowrap" style="width:30%;"><img class="flag-iihf '.$e[teamshort].'-small" src="/images/blank.png" alt="'.$e[teamshort].'"> '.$e[name].'</td><td class="text-center">'.$e[g].'</td><td class="text-center">'.$e[a].'</td><td class="text-center">'.$e[w].'</td><td class="text-center">'.$e[so].'</td></tr>';
      $i++;
      }
    $tfoot = '<tfoot class="alert-'.$leaguecolor.' font-weight-bold">
            <tr>
              <td colspan="3">'.LANG_BETS_ACTUAL.': '.$pts.' '.LANG_TEAMSTATS_PTS.'</td>
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
   </div> <!-- end col -->
   <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block">
    '.$advert.'
   </div> <!-- end col -->
   </div> <!-- end row -->';
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
        $count++;
        SendMail(ADMIN_MAIL, "Novy manazer v drafte", "user ID: ".$uid.". Pocet: ".$count."/".$manazerov);
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