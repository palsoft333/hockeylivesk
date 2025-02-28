<?
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// save picks for new version of draft
if(isset($_GET["save"]) && $_GET["save"]==1)
  {
  $manazerov = 10;
  include("db.php");
  $uid = $_SESSION['logged'];
  $options = json_decode($_GET["json"], true);
  mysqli_query($link, "INSERT INTO ft_predraft (uid, predraft) VALUES ('".$uid."', '".$_GET["json"]."') ON DUPLICATE KEY UPDATE predraft='".$_GET["json"]."'") or die(mysqli_error($link));
  // save picks to ft_choices when there is no known rosters
  if($_SESSION["knownrosters"]==0) {
    $i=0;
    while($i < count($options)) {
      if($options[$i]["gk"]==1) $sp = mysqli_query($link, "SELECT * FROM 2004goalies WHERE id='".$options[$i]["pid"]."'");
      else $sp = mysqli_query($link, "SELECT * FROM 2004players WHERE id='".$options[$i]["pid"]."'");
      $selp = mysqli_fetch_array($sp);
      if($options[$i]["gk"]==1) $pos = "GK";
      else $pos = $selp[pos];
      if($pos=="LD" || $pos=="RD") $pos="D";
      if($pos=="CE" || $pos=="RW" || $pos=="LW") $pos="F";
      mysqli_query($link, "REPLACE INTO `ft_choices` (`id`, `teamshort`, `teamlong`, `pos`, `name`) VALUES ('".$selp["id"]."', '".$selp["teamshort"]."', '".$selp["teamlong"]."', '".$pos."', '".$selp["name"]."')");
      $i++;
    }
  }
  // check if change of already drafted player isn't happening
    $q = mysqli_query($link, "SELECT * FROM ft_players WHERE uid='".$uid."' ORDER BY round");
    while($f = mysqli_fetch_array($q)) {
      $i=0;
      while($i < count($options)) {
        if($options[$i]["round"]==$f["round"]) {
          // change already drafted player
          $e = mysqli_query($link, "SELECT * FROM ft_players WHERE pid='".$options[$i]["pid"]."'");
          if($options[$i]["pid"]!=$f["pid"] && mysqli_num_rows($e)==0) {
            mysqli_query($link, "UPDATE ft_players SET pid='".$options[$i]["pid"]."', type='0', gk='".$options[$i]["gk"]."', g='0', a='0', w='0', so='0' WHERE round='".$options[$i]["round"]."' && uid='".$uid."'");
            mysqli_query($link, "INSERT INTO ft_changes (uid, old_pid, new_pid, tstamp) VALUES ('".$uid."', '".$f["pid"]."', '".$options[$i]["pid"]."', NOW())");
            if($_SESSION["knownrosters"]==0) {
                if($options[$i]["gk"]==1) $sp = mysqli_query($link, "SELECT * FROM 2004goalies WHERE id='".$options[$i]["pid"]."'");
                else $sp = mysqli_query($link, "SELECT * FROM 2004players WHERE id='".$options[$i]["pid"]."'");
                $selp = mysqli_fetch_array($sp);
                if($options[$i]["gk"]==1) $pos = "GK";
                else $pos = $selp["pos"];
                if($pos=="LD" || $pos=="RD") $pos="D";
                if($pos=="CE" || $pos=="RW" || $pos=="LW") $pos="F";
                mysqli_query($link, "REPLACE INTO `ft_choices` (`id`, `teamshort`, `teamlong`, `pos`, `name`) VALUES ('".$selp["id"]."', '".$selp["teamshort"]."', '".$selp["teamlong"]."', '".$pos."', '".$selp["name"]."')");
            }
          }
        }
      $i++;
      }
    }
  $key = array_search(0, array_column($options, 'pid'));
  if(!$key && $key!==0 && count($options)==10) PreDraft();
  mysqli_close($link);
  }

function PreDraft()
  {
  Global $manazerov, $link;
  include("lang/lang_".strtolower($_SESSION["lang"]).".php");
  $q = mysqli_query($link, "SELECT * FROM ft_players ORDER BY round DESC, id DESC");
  $f = mysqli_fetch_array($q);
  $po = mysqli_query($link, "SELECT * FROM ft_players WHERE round='".$f["round"]."'");
  $poc = mysqli_num_rows($po);
  if($poc<$manazerov)
    {
    $pick = $poc+1;
    $round = $f["round"];
    if(mysqli_num_rows($q)==0) $round=1;
    }
  else
    {
    $pick = 1;
    $round = $f["round"]+1;
    }
  if($round % 2 == 0) $narade = $manazerov-$pick+1;
  else $narade = $pick;
  if($pick==1 && $round==11) return false;
  $y = mysqli_query($link, "SELECT ft_teams.*, t1.predraft, t2.uname, t2.email FROM ft_teams LEFT JOIN ft_predraft t1 ON t1.uid=ft_teams.uid JOIN e_xoops_users t2 ON t2.uid=ft_teams.uid WHERE ft_teams.pos='".$narade."'");
  $u = mysqli_fetch_array($y);
  if($u["predraft"]!=NULL)
    {
    $options = json_decode($u["predraft"], true);
    $i=0;
    while($i < count($options))
      {
      $pid = $options[$i]["pid"];
      $rnd = $options[$i]["round"];
      if($rnd==$round) 
        {
        if($pid==0) {
          $mail = mysqli_query($link, "SELECT * FROM ft_teams WHERE pos='".$narade."' && last_mail_round='".$round."'");
          if(mysqli_num_rows($mail)==0) {
            $subject = LANG_FANTASY_MAILSUBJECT;
            $message = sprintf(LANG_FANTASY_MAILTEXT2, $nazov, $menu, $nazov);
            $headers = 'From: '.SITE_MAIL. "\r\n" .
            'Reply-To: '.SITE_MAIL. "\r\n" .
            'X-Mailer: PHP/' . phpversion();
            mail(ADMIN_MAIL, "Predraft čaká", "Kolo: ".$round.", Výber: ".$pick.", Užívateľ na ťahu: ".$u["uname"]." (".$u["email"].")", $headers);
            mail($u["email"], $subject, $message, $headers);
            mysqli_query($link, "UPDATE ft_teams SET last_mail_round='".$round."' WHERE pos='".$narade."'");
            echo "ok";
            }
          else echo "ok";
          break;
        }
        $w = mysqli_query($link, "SELECT * FROM ft_players WHERE pid='".$pid."'");
        if(mysqli_num_rows($w)>0) 
          {
          $mail = mysqli_query($link, "SELECT * FROM ft_teams WHERE pos='".$narade."' && last_mail_round='".$round."'");
          if(mysqli_num_rows($mail)==0) {
            $e = mysqli_query($link, "SELECT * FROM ft_choices WHERE id='".$pid."'");
            $r = mysqli_fetch_array($e);
            $subject = LANG_FANTASY_MAILSUBJECT;
            $message = sprintf(LANG_FANTASY_MAILTEXT1, $nazov, $r["name"], $menu, $nazov);
            $headers = 'From: '.SITE_MAIL. "\r\n" .
            'Reply-To: '.SITE_MAIL. "\r\n" .
            'X-Mailer: PHP/' . phpversion();
            mail(ADMIN_MAIL, "Predraft čaká", "Kolo: ".$round.", Výber: ".$pick.", Konfliktný hráč: ".$r["name"].", Užívateľ na ťahu: ".$u["uname"]." (".$u["email"].")", $headers);
            mail($u["email"], $subject, $message, $headers);
            mysqli_query($link, "UPDATE ft_teams SET last_mail_round='".$round."' WHERE pos='".$narade."'");
            }
          }
        else
          {
          $pl = mysqli_query($link, "SELECT * FROM ft_choices WHERE id='".$pid."'");
          $pla = mysqli_fetch_array($pl);
          if($pla["pos"]=="GK") $gk=1;
          else $gk=0;
          mysqli_query($link, "INSERT INTO ft_players (uid, pid, round, gk) VALUES ('".$u["uid"]."', '".$pid."', '".$round."', '".$gk."')");
          if($_SESSION["knownrosters"]==0) {
              if($gk==1) $sp = mysqli_query($link, "SELECT * FROM 2004goalies WHERE id='".$pid."'");
              else $sp = mysqli_query($link, "SELECT * FROM 2004players WHERE id='".$pid."'");
              $selp = mysqli_fetch_array($sp);
              if($gk==1) $pos = "GK";
              else $pos = $selp["pos"];
              mysqli_query($link, "REPLACE INTO `ft_choices` (`id`, `teamshort`, `teamlong`, `pos`, `name`) VALUES ('".$selp["id"]."', '".$selp["teamshort"]."', '".$selp["teamlong"]."', '".$pos."', '".$selp["name"]."');");
          }
          echo "ok";
          PreDraft();
          }
        break;
        }
      $i++;
      }
    }
  else
    {
    $mail = mysqli_query($link, "SELECT * FROM ft_teams WHERE pos='".$narade."' && last_mail_round='".$round."'");
    if(mysqli_num_rows($mail)==0) {
      $subject = LANG_FANTASY_MAILSUBJECT;
      $message = sprintf(LANG_FANTASY_MAILTEXT2, $nazov, $menu, $nazov);
      $headers = 'From: '.SITE_MAIL. "\r\n" .
      'Reply-To: '.SITE_MAIL. "\r\n" .
      'X-Mailer: PHP/' . phpversion();
      mail(ADMIN_MAIL, "Predraft čaká", "Kolo: ".$round.", Výber: ".$pick.", Užívateľ na ťahu: ".$u["uname"]." (".$u["email"].")", $headers);
      mail($u["email"], $subject, $message, $headers);
      mysqli_query($link, "UPDATE ft_teams SET last_mail_round='".$round."' WHERE pos='".$narade."'");
      echo "ok";
      }
    else echo "ok";
    }
  }

function Show_Drafted()
  {
  Global $timeout, $manazerov, $draft_start, $league_start, $script_end, $leaguecolor, $link;
  $uid = $_SESSION['logged'];
  //if($uid==2) $uid=2935;
  $o = mysqli_query($link, "SELECT tstamp + INTERVAL ".$timeout." MINUTE as tme FROM ft_players ORDER BY id DESC");
  $p = mysqli_fetch_array($o);
  if(mysqli_num_rows($o)==0) $min = floor((strtotime($draft_start.' + '.$timeout.' minute')-time())/60);
  else $min = floor((strtotime($p["tme"])-time())/60);
  if($min<0) $min=0;
  if(mysqli_num_rows($o)<($manazerov*10)) {
  $l = mysqli_query($link, "SELECT dt.uid, AVG(dt.diff) as avgtime FROM (select
      p.uid,
      p.pid,
      p.tstamp,
      TIME_TO_SEC(TIMEDIFF(p.tstamp, @laststamp)) as diff,
      @laststamp := p.tstamp
   from
      ft_players p,
      ( select @laststamp := 0 ) SQLVars
   order by
      p.id ASC)dt GROUP BY uid");
  while($k = mysqli_fetch_array($l))
    {
    $mid = $k["uid"];
    $avgtime[$mid] = $k["avgtime"];
    $k["avgtime"] = $k["avgtime"] ?? 0;
    mysqli_query($link, "UPDATE e_xoops_users SET avg_time='".$k["avgtime"]."' WHERE uid='".$mid."'");
    }
  $drafted = '
  <div class="row justify-content-center">
    <div class="col-sm-8 col-md-7">
      <div class="card mb-4 shadow animated--grow-in">
        <div class="card-header">
          <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
            '.LANG_FANTASY_ORDEROFMANAGERS.'
            <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
          </h6>
        </div>
        <div class="card-body">
          <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid">
            <thead>
              <tr>
                <th class="text-center">'.LANG_FANTASY_RANK.'</th>
                <th>'.LANG_FANTASY_MANAGER.'</th>
                <th data-toggle="tooltip" data-placement="top" title="'.LANG_FANTASY_ESTIMATETOOLTIP.'">'.LANG_FANTASY_ESTIMATE.' <i class="fas fa-question-circle"></i></th>
                <th data-toggle="tooltip" data-placement="top" title="'.LANG_FANTASY_AVERAGETIMETOOLTIP.'">'.LANG_FANTASY_AVERAGETIME.' <i class="fas fa-question-circle"></i></th>
              </tr>
            </thead>
            <tbody>';
  $q = mysqli_query($link, "SELECT * FROM ft_players ORDER BY round DESC, id DESC");
  $f = mysqli_fetch_array($q);
  $f["round"] = $f["round"] ?? null;
  $po = mysqli_query($link, "SELECT * FROM ft_players WHERE round='".$f["round"]."'");
  $poc = mysqli_num_rows($po);
  if($poc<$manazerov)
    {
    $pick = $poc+1;
    $round = $f["round"];
    }
  else
    {
    $pick = 1;
    $round = $f["round"]+1;
    }
  if(mysqli_num_rows($q)==0) $round=1;
  if($round % 2 == 0) { $narade = $manazerov-$pick+1; $otoc=1; }
  else { $narade = $pick; $otoc=0; }
  $t = mysqli_query($link, "SELECT ft_teams.*, et.uname, et.last_login, et.user_avatar, et.avg_time FROM ft_teams JOIN e_xoops_users et ON ft_teams.uid=et.uid WHERE ft_teams.active='1' ORDER BY pos ASC");
  while($y = mysqli_fetch_array($t))
    {
    if($otoc==0) $odhad = (($y["pos"]-$narade-1)*$timeout)+$min;
    else $odhad = (($narade-$y["pos"]-1)*$timeout)+$min;
    if($odhad<0)
      {
      if($round % 2 == 0) $odhad = $min+($narade-1+$y["pos"]-1)*$timeout;
      else $odhad = $min+($manazerov-$narade+$manazerov-$y["pos"])*$timeout;
      }
    $odhad = strtotime(date("Y-m-d H:i:s").' + '.$odhad.' minute');
    $start_minutes = date('i', $odhad);
    if($start_minutes % 10!=0)
      {
      $odhad = $odhad + ((10 - ($start_minutes % 10))*60);
      }
    if(date('Y-m-d',$odhad)==date("Y-m-d")) $hl="dnes o <b>".date('G:i', $odhad)."</b>";
    elseif(date('Y-m-d',$odhad)==date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+1, date('Y')))) $hl="zajtra o ".date('G:i', $odhad);
    else $hl=date('j.n.',$odhad). " o ".date('G:i', $odhad);
    if($narade==$y["pos"]) $hl="<b>".LANG_FANTASY_JUSTPICKING."</b> <i class='fas fa-hourglass-half'></i>";
    if($round==10 && $y["pos"]>$narade) $hl="-";
    if($round==1 && $y["pos"]>=$narade) {
        $avg = $y["avg_time"]/60;
    }
    else {
        $mid = $y["uid"];
        if(isset($avgtime)) $avg = $avgtime[$mid]/60;
        else $avg = 0;
    }
    if($avg>60) $avg = round($avg/60,0)." ".LANG_FANTASY_HRS;
    else $avg = round($avg,0)." ".LANG_FANTASY_MINS;
    if($y["user_avatar"]!="") $avatar = "<img class='rounded-circle mr-1' src='/images/user_avatars/".$y["uid"].".".$y["user_avatar"]."?".filemtime('images/user_avatars/'.$y["uid"].'.'.$y["user_avatar"])."' alt='".$y["uname"]."' style='width:2rem;height:2rem;vertical-align:-11px;'>";
    else $avatar = "<i class='text-gray-300 fas fa-user-circle fa-2x mr-1' style='vertical-align:-7px;'></i>";
    $drafted .= '<tr><td class="text-center">'.$y["pos"].'</td><td class="text-nowrap"><a href="/user/'.$y["uid"].'" class="blacklink">'.$avatar.$y["uname"].''.($y["last_login"]+300>time() ? '<i class="fas fa-circle live ml-1 rounded-circle text-success" style="font-size: 14px;" data-toggle="tooltip" data-placement="top" title="Online!"></i>':'').'</a></td><td class="text-nowrap">'.$hl.'</b></td><td class="text-nowrap">'.$avg.'</b></td></tr>';
    }
  
  $drafted .= '</tbody></table></div></div></div></div>';
  }
  else
  {
  $drafted = '
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
  $q = mysqli_query($link, "SELECT ft_changes.*, IF(p.pos='GK',1,0) as gk, u.uname FROM `ft_changes` LEFT JOIN ft_choices p ON ft_changes.old_pid=p.id JOIN e_xoops_users u ON ft_changes.uid=u.uid ORDER BY tstamp DESC LIMIT 10");
  while($f = mysqli_fetch_array($q))
    {
    if($f["gk"]==1) $p = mysqli_query($link, "SELECT g1.teamshort as old_tshort, g1.name as old_name, g2.teamshort as new_tshort, g2.name as new_name FROM 2004goalies g1 LEFT JOIN 2004goalies g2 ON g2.id='".$f["new_pid"]."' WHERE g1.id='".$f["old_pid"]."'");
    else $p = mysqli_query($link, "SELECT p1.teamshort as old_tshort, p1.name as old_name, p2.teamshort as new_tshort, p2.name as new_name FROM 2004players p1 LEFT JOIN 2004players p2 ON p2.id='".$f["new_pid"]."' WHERE p1.id='".$f["old_pid"]."'");
    $m = mysqli_fetch_array($p);
    if(date('Y-m-d',strtotime($f["tstamp"]))==date("Y-m-d")) $hl="dnes o <b>".date('G:i', strtotime($f["tstamp"]))."</b>";
    elseif(date('Y-m-d',strtotime($f["tstamp"]))==date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')))) $hl="včera o ".date('G:i', strtotime($f["tstamp"]));
    else $hl=date('j.n.',strtotime($f["tstamp"])). " ".LANG_AT." ".date('G:i', strtotime($f["tstamp"]));
    $drafted .= '<tr><td class="text-nowrap">'.$hl.'</td><td><a href="/user/'.$f["uid"].'">'.$f["uname"].'</a></td><td class="text-nowrap"><img class="flag-iihf '.$m["old_tshort"].'-small" src="/images/blank.png" alt="'.$m["old_tshort"].'"> <a href="/'.($f["gk"]==1 ? 'goalie':'player').'/'.$f["old_pid"].'0-'.SEOtitle($m["old_name"]).'" data-toggle="popover" data-player="'.$m["old_name"].'|'.$f["gk"].'">'.$m["old_name"].'</a></td><td class="text-nowrap"><img class="flag-iihf '.$m["new_tshort"].'-small" src="/images/blank.png" alt="'.$m["new_tshort"].'"> <a href="/'.($f["gk"]==1 ? 'goalie':'player').'/'.$f["new_pid"].'0-'.SEOtitle($m["new_name"]).'" data-toggle="popover" data-player="'.$m["new_name"].'|'.$f["gk"].'">'.$m["new_name"].'</a></td></tr>';
    }
  $drafted .= '</tbody></table></div></div></div></div>';
  }
  
  $drafted .= '<div id="101390-1"><script src="//ads.themoneytizer.com/s/gen.js?type=1"></script><script src="//ads.themoneytizer.com/s/requestform.js?siteId=101390&formatId=1"></script></div>';
  
  $drafted .= '
    <div class="card my-4 shadow animated--grow-in">
    <div class="card-header">
      <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'"">
        '.LANG_FANTASY_PICKSTITLE2.'
        <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
      </h6>
    </div>
    <div class="card-body">
      <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid">
        <thead>
          <tr>
            <th class="text-center">'.LANG_FANTASY_PICKNO.'</th>
            <th class="text-center">'.LANG_FANTASY_ROUND.'</th>
            <th>'.LANG_FANTASY_MANAGER.'</th>
            <th class="text-center">'.LANG_PLAYERSTATS_POS.'</th>
            <th>'.LANG_PLAYERDB_PLAYER.'</th>
          </tr>
        </thead>
        <tbody>';

  if(mysqli_num_rows($o)<($manazerov*10)) $drafted .= '<tfoot class="alert-'.$leaguecolor.'"><tr><td colspan="5" class="text-center">'.sprintf(LANG_FANTASY_PICKENDSIN, '<i class="fas fa-stopwatch"></i> <b><span id="mins">'.$min.'</span></b>').'</tr></tfoot>';

  $w = mysqli_query($link, "SELECT et.*, ft_choices.name as hrac, ft_choices.pos as pos, ft_choices.teamshort as tshort FROM ft_choices JOIN (SELECT dt.*, e_xoops_users.uname as nick FROM e_xoops_users JOIN (SELECT * FROM ft_players ORDER BY round ASC, id ASC)dt ON dt.uid=e_xoops_users.uid)et ON et.pid=ft_choices.id");
  $pi = $li = 1;
  while($e = mysqli_fetch_array($w))
    {
    $line=$link1=$link2=$add=$add1="";
    if($pi==$manazerov) $line=" border-bottom:1px dashed black !important;";
    if($e["type"]==2) $add1=' class="bg-gray-500"';
    if($e["pos"]=="GK") { $link1='<a href="/goalie/'.$e["pid"].'0-'.SEOtitle($e["hrac"]).'" data-toggle="popover" data-player="'.$e["hrac"].'|1">'; $link2="</a>"; }
    else { $link1='<a href="/player/'.$e["pid"].'0-'.SEOtitle($e["hrac"]).'" data-toggle="popover" data-player="'.$e["hrac"].'|0">'; $link2="</a>"; }
    if($e["type"]!=0)
      {
      if($e["type"]==1) { $icon = "robot"; $hl = LANG_FANTASY_AUTOPICK; }
      if($e["type"]==2) { $icon = "ban"; $hl = LANG_FANTASY_MUSTCHANGE; }
      $add='<i class="fas fa-'.$icon.' float-right  mr-1" data-toggle="tooltip" data-placement="top" title="'.$hl.'"></i>';
      }
    if($uid==$e["uid"]) $add1 = " class='bg-gray-300'";
    $drafted .= '<tr'.$add1.'><td class="text-center" style="width:5%;'.$line.'">'.$li.'</td><td class="text-center" style="width:10%;'.$line.'">'.$e["round"].'</td><td style="width:20%;'.$line.'">'.$e["nick"].'</td><td class="text-center" style="width:10%;'.$line.'">'.$e["pos"].'</td><td class="text-nowrap" style="width:40%;'.$line.'"><img class="flag-iihf '.$e["tshort"].'-small" src="/images/blank.png" alt="'.$e["tshort"].'"> '.$link1.$e["hrac"].$link2.$add.'</td></tr>';
    if($pi==$manazerov) $pi=0;
    $pi++;
    $li++;
    }
  $drafted .= "</tbody></table></div></div>";
  if($li<($manazerov*10)+1) $script_end .= "<script>
      $(document).ready(function(){
      setInterval(function(){ 
            var obsah = $(\"#mins\").html();
            if(obsah>=0) $(\"#mins\").html(obsah-1);
            }, 60000);
  });  
  </script>";
  return $drafted;
  }
?>