<?
session_start();
if(!isset($_GET['action'])) exit;
include("db.php");
if(isset($_SESSION["lang"])) {
  include("lang/lang_".$_SESSION["lang"].".php");
}
else {
   $_SESSION["lang"] = 'sk';
    include("lang/lang_sk.php");
}

$uid = $_SESSION['logged'];
if($uid==2) { $uid=1319; $_SESSION["logged"]=1319; } 

// vymena hraca vo Fantasy Championship
if($_GET["action"]=="change")
  {
  // vymena potvrdena
  if($_GET["action"]=="change" && $_GET["newpid"] && $_GET["oldpid"])
    {
    $newpid = mysqli_real_escape_string($link, $_GET['newpid']);
    $newp = MySQLi_Query($link, "SELECT * FROM ft_players WHERE pid='".$newpid."'");
    if(mysqli_num_rows($newp)==0) 
      {
      $oldpid = mysqli_real_escape_string($link, $_GET['oldpid']);
      $p = MySQLi_Query($link, "SELECT * FROM ft_players WHERE pid='".$oldpid."' && uid='".$uid."'");
      $o = mysqli_fetch_array($p);
      if($o["gk"]==1) $q = MySQLi_Query($link, "SELECT f.*, p.name, 'GK' as pos, p.league FROM `ft_players` f LEFT JOIN 2004goalies p ON p.id=f.pid WHERE pid='".$oldpid."' && uid='".$uid."'");
      else $q = MySQLi_Query($link, "SELECT f.*, p.name, p.pos, p.league FROM `ft_players` f LEFT JOIN 2004players p ON p.id=f.pid WHERE pid='".$oldpid."' && uid='".$uid."'");
      if(mysqli_num_rows($q)>0)
        {
        $f = mysqli_fetch_array($q);
        if($o["gk"]==1) $w = MySQLi_Query($link, "SELECT * FROM 2004goalies WHERE id='".$newpid."'");
        else $w = MySQLi_Query($link, "SELECT * FROM 2004players WHERE id='".$newpid."'");
        $e = mysqli_fetch_array($w);
        if($o["gk"]==1) $pos = "GK";
        else $pos = $e["pos"];
        mysqli_query($link, "UPDATE ft_players SET pid='".$newpid."', gk='".$o["gk"]."', g='0', a='0', w='0', so='0' WHERE pid='".$oldpid."' && uid='".$uid."'");
        mysqli_query($link, "REPLACE INTO ft_choices (id, teamshort, teamlong, pos, name) VALUES ('".$newpid."', '".$e["teamshort"]."', '".$e["teamlong"]."', '".$pos."', '".$e["name"]."')");
        mysqli_query($link, "INSERT INTO ft_changes (uid, old_pid, new_pid) VALUES ('".$uid."', '".$oldpid."', '".$newpid."')");
        echo "ok";
        }
      }
    }
  else
    {
    $pid = mysqli_real_escape_string($link, $_GET['pid']);
    $p = MySQLi_Query($link, "SELECT * FROM ft_players WHERE pid='".$pid."' && uid='".$uid."'");
    $o = mysqli_fetch_array($p);
    if($o["gk"]==1) {
      $q = MySQLi_Query($link, "SELECT f.*, (f.w*2)+(f.so)*2 as points, p.name, 'GK' as pos, p.league, p.teamshort FROM `ft_players` f LEFT JOIN 2004goalies p ON p.id=f.pid WHERE pid='".$pid."' && uid='".$uid."'");
      $f = mysqli_fetch_array($q);
      $hl = "Výmena brankára";
      $po = '('.$f["w"].' výhier + '.$f["so"].' čistých kont)';
    }
    else {
      $q = MySQLi_Query($link, "SELECT f.*, f.g+f.a as points, p.name, p.pos, p.league, p.teamshort FROM `ft_players` f LEFT JOIN 2004players p ON p.id=f.pid WHERE pid='".$pid."' && uid='".$uid."'");
      $f = mysqli_fetch_array($q);
      $hl = "Výmena hráča";
      $po = '('.$f["g"].'G + '.$f["a"].'A)';
    }
    echo '
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dialogTitle">'.$hl.'</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="'.LANG_CLOSE.'">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <p class="font-weight-bold h5 h5-fluid">'.$f["name"].'</p>
          <img src="/includes/player_photo.php?name='.$f["name"].'" class="rounded-circle img-thumbnail" style="width:100px; height:100px; object-fit: cover; object-position: center top;">
          <p>
            Nazbieral bodov: <strong>'.$f["points"].'</strong> '.$po.'
          </p>
          <p>'.LANG_FANTASY_POINTSSTAY.'</p>
            <select name="newpid" id="newpid" size="1" class="custom-select">
              <option value="0">Vyberte si nového hráča:</option>';
              $dnes = date("Y-m-d");
              $excl = mysqli_query($link, "SELECT * FROM `2004matches` WHERE datetime < now() && kedy='na programe' && league='".$f["league"]."'");
              $exc = [];
              while($exclude = mysqli_fetch_array($excl))
                {
                $exc[] = $exclude["team1short"];
                $exc[] = $exclude["team2short"];
                }
              if($f["pos"]=="D" || $f["pos"]=="LD" || $f["pos"]=="RD") $pos="D";
              if($f["pos"]=="F" || $f["pos"]=="CE" || $f["pos"]=="RW" || $f["pos"]=="LW") $pos="F";
              if($f["pos"]!="GK") $w = MySQLi_Query($link, "SELECT * FROM 2004players WHERE league='".$f["league"]."' && pos='".$pos."' && id NOT IN (SELECT pid FROM ft_players) ORDER BY teamlong, name");
              else $w = MySQLi_Query($link, "SELECT * FROM 2004goalies WHERE league='".$f["league"]."' && id NOT IN (SELECT pid FROM ft_players WHERE gk='1') ORDER BY teamlong, name");
              $teampred = "";
              while($e = mysqli_fetch_array($w))
                {
                if($teampred!=$e["teamlong"]) echo '<optgroup label="'.$e["teamlong"].'">';
                if(in_array($e["teamshort"], $exc)) $dis=' disabled style="color:#d8d8d8;"';
                else $dis='';
                echo '<option value="'.$e["id"].'"'.$dis.'>'.$e["name"].''.($dis!='' ? ' (práve hrá)':'').'</option>';
                $teampred = $e["teamlong"];
                }
              echo '</optgroup></select>
          <input type="hidden" name="oldpid" id="oldpid" value="'.$pid.'">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">'.LANG_CLOSE.'</button>
          <button type="button" class="btn btn-hl change"'.(in_array($f["teamshort"],$exc) ? " disabled":"").'>Vymeniť</button>
        </div>
      </div>
    </div>';
    }
  }

// registracia do Fantasy KHL
if($_GET["action"]=="signin")
  {
  $lid = mysqli_real_escape_string($link, $_GET['lid']);
  mysqli_query($link, "INSERT INTO fl_wallet (uid, balance, league) VALUES ('".$uid."', '180000', '".$lid."')");
  mysqli_query($link, "INSERT INTO fl_selects (uid) VALUES ('".$uid."')");
  echo "ok";
  }

// kupa hraca vo Fantasy KHL a cennik
if($_GET["action"]=="buy" || $_GET["action"]=="pricelist")
  {
  // tim vybrany, vyber hraca
  if($_GET["action"]=="buy" && $_GET["team"] || $_GET["action"]=="pricelist" && $_GET["team"])
    {
    $lid = mysqli_real_escape_string($link, $_GET['lid']);
    $teamshort = mysqli_real_escape_string($link, $_GET['team']);
    if($_GET["action"]=="pricelist")
      {
      $q = MySQLi_Query($link, "SELECT fl_prices.playerid, fl_prices.price, t1.name, t1.pos, t1.league, t1.teamshort, IF(pos='F',1,2) as zor FROM fl_prices JOIN el_players t1 ON t1.id=fl_prices.playerid WHERE t1.league='".$lid."' && t1.teamshort='".$teamshort."'
UNION
SELECT fl_prices_g.playerid, fl_prices_g.price, t1.name, 'GK' as pos, t1.league, t1.teamshort, 3 as zor FROM fl_prices_g JOIN el_goalies t1 ON t1.id=fl_prices_g.playerid WHERE t1.league='".$lid."' && t1.teamshort='".$teamshort."'
ORDER BY zor ASC, price DESC");
      }
    else
      {
      $pos = mysqli_real_escape_string($link, $_GET['pos']);
      $excl = mysqli_query($link, "SELECT * FROM `el_matches` WHERE datetime < now() && kedy='na programe' && league='".$lid."'");
      $exc = [];
      while($exclude = mysqli_fetch_array($excl))
        {
        $exc[] = $exclude["team1short"];
        $exc[] = $exclude["team2short"];
        }
      if($pos!="GK")
        {
        $q = MySQLi_Query($link, "SELECT * FROM fl_prices JOIN el_players t1 ON t1.id=fl_prices.playerid WHERE t1.league='".$lid."' && t1.teamshort='".$teamshort."' && t1.pos='".$pos."' && t1.id NOT IN (SELECT pid FROM fl_selects WHERE uid='".$uid."') ORDER BY price DESC");
        }
      else
        {
        $q = MySQLi_Query($link, "SELECT * FROM fl_prices_g JOIN el_goalies t1 ON t1.id=fl_prices_g.playerid WHERE t1.league='".$lid."' && t1.teamshort='".$teamshort."' && t1.id NOT IN (SELECT pid FROM fl_selects WHERE uid='".$uid."') ORDER BY price DESC");
        }
      }
    echo '<p>
    '.($_GET["action"]=="pricelist" ? LANG_FANTASY_PRICELISTTITLE : LANG_FANTASY_SELECTPLAYER).'
    :<br>
    <select name="player" id="player" size="1" class="custom-select">';
    $predzor=1;
    if($_GET["action"]=="pricelist") echo '<optgroup label="'.LANG_FANTASY_FORWARDS.'">';
    while($f = mysqli_fetch_array($q))
      {
      if($_GET["action"]=="pricelist" && $predzor!=$f["zor"])
        {
        if($f["zor"]==2) $zor = LANG_FANTASY_DEFENSE;
        else $zor = LANG_TEAMSTATS_GOALIES;
        echo '</optgroup><optgroup label="'.$zor.'">';
        }
      if(in_array($f["shortname"], $exc)) $dis=' disabled';
      else $dis='';
      if($_GET["action"]=="pricelist") { $disppos = "(".$f["pos"].") "; $val=""; }
      else $val = ' value="'.$f["playerid"].'-'.$pos.'"';
      echo '<option'.$val.$dis.'>'.$disppos.$f["name"].' | '.money_format('%.0n', $f["price"]).'</option>';
      $predzor=$f["zor"];
      }
    if($_GET["action"]=="pricelist") echo '</optgroup>';
    echo '</select><br><br>
    <a href="#" class="show-'.($_GET["action"]=="pricelist" ? "prices" : "dialog").'" id="buy-0-0-'.$pos.'"><i class="fas fa-angle-double-left"></i> '.LANG_FANTASY_BACKTOTEAMSEL.'</a>
    </p>';
    }
  // hrac potvrdeny, kupujem
  elseif($_GET["action"]=="buy" && $_GET["player"])
    {
    $lid = mysqli_real_escape_string($link, $_GET['lid']);
    $pid = mysqli_real_escape_string($link, $_GET['player']);
    $pos = $_GET['pos'];
    if($pos!="GK") $q = MySQLi_Query($link, "SELECT * FROM fl_prices JOIN el_players t1 ON t1.id=fl_prices.playerid WHERE t1.league='".$lid."' && t1.id='".$pid."'");
    else $q = MySQLi_Query($link, "SELECT * FROM fl_prices_g JOIN el_goalies t1 ON t1.id=fl_prices_g.playerid WHERE t1.league='".$lid."' && t1.id='".$pid."'");
    $f = mysqli_fetch_array($q);
    if($pos!="GK") $pos = $f["pos"];
    else $pos="GK";
    $w = mysqli_query($link, "SELECT * FROM fl_wallet WHERE uid='".$uid."' && league='".$lid."'");
    $e = mysqli_fetch_array($w);
    if($e["balance"]<$f["price"]) echo "notenough";
    else
      {
      mysqli_query($link, "INSERT INTO fl_selects (uid, pid, pos, price) VALUES ('".$uid."', '".$pid."', '".$pos."', '".$f["price"]."')");
      mysqli_query($link, "INSERT INTO fl_transactions (type, uid, pos, pid, price) VALUES ('0', '".$uid."', '".$pos."', '".$pid."', '".$f["price"]."')");
      $b = mysqli_query($link, "SELECT * FROM `fl_selects` LEFT JOIN el_players t1 ON t1.id=fl_selects.pid LEFT JOIN el_goalies t2 ON t2.id=fl_selects.pid WHERE uid='".$uid."' && t1.league='".$lid."' || uid='".$uid."' && t2.league='".$lid."'");
      if(mysqli_num_rows($b)==9) $act = 1;
      else $act = 0;
      mysqli_query($link, "UPDATE fl_wallet SET balance=balance-".$f["price"].", active='".$act."' WHERE uid='".$uid."' && league='".$lid."'");
      }
    }
  else
    {
    $lid = mysqli_real_escape_string($link, $_GET['lid']);
    $pos = $_GET['pos'];
    $dnes = date("Y-m-d");
    $zajtra = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+1, date('Y')));
    $tod = mysqli_query($link, "SELECT * FROM el_matches WHERE datetime > '".$dnes." 07:00:00' && datetime < '".$zajtra." 07:00:00' && league='".$lid."' && kedy='na programe' ORDER BY datetime ASC");
    $today = $exc = [];
    while($incl = mysqli_fetch_array($tod))
      {
      $today[] = $incl["team1short"];
      $today[] = $incl["team2short"];
      }
    if(mysqli_num_rows($tod)>0)
      {
      $excl = mysqli_query($link, "SELECT * FROM `el_matches` WHERE datetime > '".$dnes." 07:00:00' && datetime < now() && kedy!='konečný stav' && league='".$lid."'");
      while($exclude = mysqli_fetch_array($excl))
        {
        $exc[] = $exclude["team1short"];
        $exc[] = $exclude["team2short"];
        }
      }
    $q = MySQLi_Query($link, "SELECT * FROM el_teams WHERE league='".$lid."' ORDER BY longname ASC");
    echo '
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dialogTitle">'.($_GET["action"]=="pricelist" ? LANG_FANTASY_PRICELIST : LANG_FANTASY_BUYTITLE).'</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="'.LANG_CLOSE.'">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <p>'.($_GET["action"]=="pricelist" ? LANG_FANTASY_SELECTTEAMPRICEL : LANG_FANTASY_SELECTTEAM).':</p>
          <select name="team" id="'.($_GET["action"]=="pricelist" ? "prices" : "").'team" size="1" class="custom-select">
            <option value="0">'.LANG_FANTASY_SELECTTEAM1.':</option>';
          while($f = mysqli_fetch_array($q))
            {
            if(in_array($f["shortname"], $exc) && $_GET["action"]!="pricelist") $dis=' disabled';
            else $dis='';
            if(in_array($f["shortname"], $today)) $toda=' ('.LANG_FANTASY_PLAYSTODAY.')';
            else $toda='';
            echo '<option value="'.$pos.'-'.$f["shortname"].'"'.$dis.'>'.$f["longname"].$toda.'</option>';
            }
          echo '</select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">'.LANG_CLOSE.'</button>
          '.($_GET["action"]=="pricelist" ? '' : '<button type="button" class="btn btn-hl buy" disabled>'.LANG_FANTASY_BUY.'</button>').'
        </div>
      </div>
    </div>';
    }
  }
// predaj hraca vo Fantasy KHL
elseif($_GET["action"]=="sell")
  {
  // predaj potvrdeny
  if($_GET["action"]=="sell" && $_GET["playerid"])
    {
    $lid = mysqli_real_escape_string($link, $_GET['lid']);
    $pid = mysqli_real_escape_string($link, $_GET['playerid']);
    $g = $_GET['g'];
    if($g==0)
      {
      $q = MySQLi_Query($link, "SELECT *, fl_selects.price as uvodna, t2.price as aktual FROM fl_selects JOIN el_players t1 ON t1.id=fl_selects.pid JOIN fl_prices t2 ON t2.playerid=fl_selects.pid WHERE t1.id='".$pid."' && uid='".$uid."'");
      }
    else
      {
      $q = MySQLi_Query($link, "SELECT *, fl_selects.price as uvodna, t2.price as aktual FROM fl_selects JOIN el_goalies t1 ON t1.id=fl_selects.pid JOIN fl_prices_g t2 ON t2.playerid=fl_selects.pid WHERE t1.id='".$pid."' && uid='".$uid."'");
      }
    if(mysqli_num_rows($q)>0)
      {
      $f = mysqli_fetch_array($q);
      if($g==0) $pos = $f["pos"];
      else $pos = "GK";
      $cena = $f["aktual"]-$f["uvodna"];
      mysqli_query($link, "DELETE FROM fl_selects WHERE pid='".$pid."' && uid='".$uid."'");
      mysqli_query($link, "UPDATE fl_wallet SET balance=balance+".$f["aktual"].", active='0' WHERE uid='".$uid."' && league='".$lid."'");
      mysqli_query($link, "INSERT INTO fl_transactions (type, uid, pos, pid, price) VALUES ('1', '".$uid."', '".$pos."', '".$pid."', '".$cena."')");
      echo "ok";
      }
    }
  else
    {
    $lid = mysqli_real_escape_string($link, $_GET['lid']);
    $pid = mysqli_real_escape_string($link, $_GET['pid']);
    $g = $_GET['g'];
    if($g==0)
      {
      $q = MySQLi_Query($link, "SELECT *, fl_selects.price as uvodna, t2.price as aktual FROM fl_selects JOIN el_players t1 ON t1.id=fl_selects.pid JOIN fl_prices t2 ON t2.playerid=fl_selects.pid WHERE t1.id='".$pid."' && uid='".$uid."'");
      }
    else
      {
      $q = MySQLi_Query($link, "SELECT *, fl_selects.price as uvodna, t2.price as aktual FROM fl_selects JOIN el_goalies t1 ON t1.id=fl_selects.pid JOIN fl_prices_g t2 ON t2.playerid=fl_selects.pid WHERE t1.id='".$pid."' && uid='".$uid."'");
      }
    $f = mysqli_fetch_array($q);
    if($f["aktual"]>$f["uvodna"]) $delta = LANG_FANTASY_YOUWILLEARN.' <span class="text-success">'.number_format($f["aktual"]-$f["uvodna"], 0, ',', ' ').'</span>';
    elseif($f["aktual"]<$f["uvodna"]) $delta = LANG_FANTASY_YOUWILLLOSE.' <span class="text-danger">'.number_format($f["aktual"]-$f["uvodna"], 0, ',', ' ').'</span>';
    else $delta = LANG_FANTASY_YOUWILLNOTLOSE;
    echo '
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dialogTitle">'.LANG_FANTASY_SELLTITLE.'</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="'.LANG_CLOSE.'">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <p class="font-weight-bold h5 h5-fluid">'.$f["name"].'</p>
          <img src="/includes/player_photo.php?name='.$f["name"].'" class="rounded-circle img-thumbnail" style="width:100px; height:100px;">
          <p>
            '.LANG_FANTASY_BUYINGPRICE.': '.number_format($f["uvodna"], 0, ',', ' ').'<br>
            '.LANG_FANTASY_CURRENTPRICE.': '.number_format($f["aktual"], 0, ',', ' ').'<br>
            '.$delta.'
          </p>
          <p>'.LANG_FANTASY_POINTSSTAY.'</p>
          <input type="hidden" name="pid" id="pid" value="'.$pid.'-'.$g.'">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">'.LANG_CLOSE.'</button>
          <button type="button" class="btn btn-hl sell">'.LANG_FANTASY_SELL.'</button>
        </div>
      </div>
    </div>';
    }
  }
// predaj celeho timu vo Fantasy KHL
elseif($_GET["action"]=="sellteam")
  {
  // predaj potvrdeny
  if($_GET["action"]=="sellteam" && $_GET["ok"])
    {
    $lid = mysqli_real_escape_string($link, $_GET['ok']);
    $q = MySQLi_Query($link, "SELECT sum(t.uvodna) as uvodna, SUM(t.aktual) as aktual FROM (SELECT fl_selects.pid, fl_selects.price as uvodna, t2.price as aktual FROM fl_selects JOIN el_players t1 ON t1.id=fl_selects.pid JOIN fl_prices t2 ON t2.playerid=fl_selects.pid WHERE uid='".$uid."' && league='".$lid."' UNION SELECT fl_selects.pid, fl_selects.price as uvodna, t2.price as aktual FROM fl_selects JOIN el_goalies t1 ON t1.id=fl_selects.pid JOIN fl_prices_g t2 ON t2.playerid=fl_selects.pid WHERE uid='".$uid."' && league='".$lid."')t");
    if(mysqli_num_rows($q)>0)
      {
      $f = mysqli_fetch_array($q);
      $cena = $f["aktual"]-$f["uvodna"];
      mysqli_query($link, "DELETE FROM fl_selects WHERE uid='".$uid."'");
      mysqli_query($link, "UPDATE fl_wallet SET balance=balance+".$f["aktual"].", active='0' WHERE uid='".$uid."' && league='".$lid."'");
      mysqli_query($link, "INSERT INTO fl_transactions (type, uid, pos, pid, price) VALUES ('1', '".$uid."', 'T', '0', '".$cena."')");
      echo "ok";
      }
    }
  else
    {
    $lid = mysqli_real_escape_string($link, $_GET['lid']);
    $q = MySQLi_Query($link, "SELECT sum(t.uvodna) as uvodna, SUM(t.aktual) as aktual FROM (SELECT fl_selects.pid, fl_selects.price as uvodna, t2.price as aktual FROM fl_selects JOIN el_players t1 ON t1.id=fl_selects.pid JOIN fl_prices t2 ON t2.playerid=fl_selects.pid WHERE uid='".$uid."' && league='".$lid."' UNION SELECT fl_selects.pid, fl_selects.price as uvodna, t2.price as aktual FROM fl_selects JOIN el_goalies t1 ON t1.id=fl_selects.pid JOIN fl_prices_g t2 ON t2.playerid=fl_selects.pid WHERE uid='".$uid."' && league='".$lid."')t");
    $f = mysqli_fetch_array($q);
    if($f["aktual"]>$f["uvodna"]) $delta = 'Na transakcii zarobíte: <span class="text-success">'.number_format($f["aktual"]-$f["uvodna"], 0, ',', ' ').'</span>';
    elseif($f["aktual"]<$f["uvodna"]) $delta = 'Na transakcii prerobíte: <span class="text-danger">'.number_format($f["aktual"]-$f["uvodna"], 0, ',', ' ').'</span>';
    else $delta = 'Na transakcii neprerobíte';
    echo '
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dialogTitle">'.LANG_FANTASY_SELLROSTERTITLE.'</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="'.LANG_CLOSE.'">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <div class="alert alert-danger" role="alert">
            '.LANG_FANTASY_SELLROSTERSURE.'
          </div>
          <p>
            '.LANG_FANTASY_TEAMBUYINGPRICE.': '.number_format($f["uvodna"], 0, ',', ' ').'<br>
            '.LANG_FANTASY_TEAMCURRENTPRICE.': '.number_format($f["aktual"], 0, ',', ' ').'<br>
            '.$delta.'
          </p>
          <p>'.LANG_FANTASY_POINTSSTAY1.'</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">'.LANG_CLOSE.'</button>
          '.($_GET["action"]=="pricelist" ? '' : '<button type="button" class="btn btn-hl sellteam">'.LANG_FANTASY_SELL.'</button>').'
        </div>
      </div>
    </div>';
    }
  }
// automaticke doplnenie hracov vo Fantasy KHL
elseif($_GET["action"]=="automat")
  {
  // doplnenie potvrdene
  if($_GET["action"]=="automat" && $_GET["ok"])
    {
    $lid = mysqli_real_escape_string($link, $_GET['ok']);
    $f = MySQLi_Query($link, "SELECT * FROM `fl_selects` WHERE uid='".$uid."' && pos='F'");
    $d = MySQLi_Query($link, "SELECT * FROM `fl_selects` WHERE uid='".$uid."' && pos='D'");
    $gk = MySQLi_Query($link, "SELECT * FROM `fl_selects` WHERE uid='".$uid."' && pos='GK'");
    $pocf = mysqli_num_rows($f);
    $pocd = mysqli_num_rows($d);
    $pocgk = mysqli_num_rows($gk);
    $tof = 5-$pocf;
    $tod = 3-$pocd;
    $togk = 1-$pocgk;
    $q = mysqli_query($link, "SELECT * FROM `el_matches` WHERE datetime > now() && kedy='na programe' && league='".$lid."' ORDER BY datetime ASC LIMIT 1");
    $e = mysqli_fetch_array($q);
    if($pocf<5)
      {
      $w = mysqli_query($link, "SELECT p.id, p.pos, p.teamshort, pr.price FROM `el_matches` m JOIN el_players p ON (p.teamshort=m.team1short && p.league='".$lid."' && p.pos='F') OR (p.teamshort=m.team2short && p.league='".$lid."' && p.pos='F') LEFT JOIN fl_prices pr ON pr.playerid=p.id WHERE m.datetime > now() && m.kedy='na programe' && m.league='".$lid."' && m.kolo='".$e["kolo"]."' && p.id NOT IN (SELECT pid FROM fl_selects WHERE uid='".$uid."') GROUP BY p.id ORDER BY pr.price DESC");
      while($y = mysqli_fetch_array($w))
        {
        $r = mysqli_query($link, "SELECT balance FROM fl_wallet WHERE uid='".$uid."' && league='".$lid."'");
        $t = mysqli_fetch_array($r);
        $afford = $t["balance"]/($tof+$tod+$togk);
        if($y["price"]<=$afford)
          {
          mysqli_query($link, "INSERT INTO fl_selects (uid, pid, pos, price) VALUES ('$uid', '".$y["id"]."', 'F', '".$y["price"]."')");
          mysqli_query($link, "INSERT INTO fl_transactions (type, uid, pos, pid, price) VALUES ('0', '$uid', 'F', '".$y["id"]."', '".$y["price"]."')");
          $b = mysqli_query($link, "SELECT * FROM `fl_selects` LEFT JOIN el_players t1 ON t1.id=fl_selects.pid LEFT JOIN el_goalies t2 ON t2.id=fl_selects.pid WHERE uid='".$uid."' && t1.league='".$lid."' || uid='".$uid."' && t2.league='".$lid."'");
          if(mysqli_num_rows($b)==9) $act = 1;
          else $act = 0;
          mysqli_query($link, "UPDATE fl_wallet SET balance=balance-".$y["price"].", active='".$act."' WHERE uid='".$uid."' && league='".$lid."'");
          $tof--;
          }
        if($tof==0) break;
        }
      }
    if($pocd<3)
      {
      $w = mysqli_query($link, "SELECT p.id, p.pos, p.teamshort, pr.price FROM `el_matches` m JOIN el_players p ON (p.teamshort=m.team1short && p.league='".$lid."' && p.pos='D') OR (p.teamshort=m.team2short && p.league='".$lid."' && p.pos='D') LEFT JOIN fl_prices pr ON pr.playerid=p.id WHERE m.datetime > now() && m.kedy='na programe' && m.league='".$lid."' && m.kolo='".$e["kolo"]."' && p.id NOT IN (SELECT pid FROM fl_selects WHERE uid='".$uid."') GROUP BY p.id ORDER BY pr.price DESC");
      while($y = mysqli_fetch_array($w))
        {
        $r = mysqli_query($link, "SELECT balance FROM fl_wallet WHERE uid='".$uid."' && league='".$lid."'");
        $t = mysqli_fetch_array($r);
        $afford = $t["balance"]/($tof+$tod+$togk);
        if($y["price"]<=$afford)
          {
          mysqli_query($link, "INSERT INTO fl_selects (uid, pid, pos, price) VALUES ('".$uid."', '".$y["id"]."', 'D', '".$y["price"]."')");
          mysqli_query($link, "INSERT INTO fl_transactions (type, uid, pos, pid, price) VALUES ('0', '".$uid."', 'D', '".$y["id"]."', '".$y["price"]."')");
          $b = mysqli_query($link, "SELECT * FROM `fl_selects` LEFT JOIN el_players t1 ON t1.id=fl_selects.pid LEFT JOIN el_goalies t2 ON t2.id=fl_selects.pid WHERE uid='".$uid."' && t1.league='".$lid."' || uid='".$uid."' && t2.league='".$lid."'");
          if(mysqli_num_rows($b)==9) $act = 1;
          else $act = 0;
          mysqli_query($link, "UPDATE fl_wallet SET balance=balance-".$y["price"].", active='".$act."' WHERE uid='".$uid."' && league='".$lid."'");
          $tod--;
          }
        if($tod==0) break;
        }
      }
    if($pocgk==0)
      {
      $w = mysqli_query($link, "SELECT p.id, p.teamshort, pr.price FROM `el_matches` m JOIN el_goalies p ON (p.teamshort=m.team1short && p.league='".$lid."') OR (p.teamshort=m.team2short && p.league='".$lid."') LEFT JOIN fl_prices_g pr ON pr.playerid=p.id WHERE m.datetime > now() && m.kedy='na programe' && m.league='".$lid."' && m.kolo='".$e["kolo"]."' && p.id NOT IN (SELECT pid FROM fl_selects WHERE uid='".$uid."') GROUP BY p.id ORDER BY pr.price DESC");
      while($y = mysqli_fetch_array($w))
        {
        $r = mysqli_query($link, "SELECT balance FROM fl_wallet WHERE uid='".$uid."' && league='".$lid."'");
        $t = mysqli_fetch_array($r);
        $afford = $t["balance"]/($tof+$tod+$togk);
        if($y["price"]<=$afford)
          {
          mysqli_query($link, "INSERT INTO fl_selects (uid, pid, pos, price) VALUES ('".$uid."', '".$y["id"]."', 'GK', '".$y["price"]."')");
          mysqli_query($link, "INSERT INTO fl_transactions (type, uid, pos, pid, price) VALUES ('0', ".$uid."', 'GK', '".$y["id"]."', '".$y["price"]."')");
          $b = mysqli_query($link, "SELECT * FROM `fl_selects` LEFT JOIN el_players t1 ON t1.id=fl_selects.pid LEFT JOIN el_goalies t2 ON t2.id=fl_selects.pid WHERE uid='".$uid."' && t1.league='".$lid."' || uid='".$uid."' && t2.league='".$lid."'");
          if(mysqli_num_rows($b)==9) $act = 1;
          else $act = 0;
          mysqli_query($link, "UPDATE fl_wallet SET balance=balance-".$y[price].", active='".$act."' WHERE uid='".$uid."' && league='".$lid."'");
          $togk--;
          }
        if($togk==0) break;
        }
      }
      echo "ok";
    }
  else
    {
    $lid = mysqli_real_escape_string($link, $_GET['lid']);
    $q = MySQLi_Query($link, "SELECT sum(t.uvodna) as uvodna, SUM(t.aktual) as aktual FROM (SELECT fl_selects.pid, fl_selects.price as uvodna, t2.price as aktual FROM fl_selects JOIN el_players t1 ON t1.id=fl_selects.pid JOIN fl_prices t2 ON t2.playerid=fl_selects.pid WHERE uid='".$uid."' && league='".$lid."' UNION SELECT fl_selects.pid, fl_selects.price as uvodna, t2.price as aktual FROM fl_selects JOIN el_goalies t1 ON t1.id=fl_selects.pid JOIN fl_prices_g t2 ON t2.playerid=fl_selects.pid WHERE uid='".$uid."' && league='".$lid."')t");
    $f = mysqli_fetch_array($q);
    if($f["aktual"]>$f["uvodna"]) $delta = 'Na transakcii zarobíte: <span style="color:green;">'.iconv("windows-1250", "utf-8", number_format($f["aktual"]-$f["uvodna"], 0, ',', ' ')).'</span>';
    elseif($f["aktual"]<$f["uvodna"]) $delta = 'Na transakcii prerobíte: <span style="color:red;">'.iconv("windows-1250", "utf-8", number_format($f["aktual"]-$f["uvodna"], 0, ',', ' ')).'</span>';
    else $delta = 'Na transakcii neprerobíte';
    echo '
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="dialogTitle">'.LANG_FANTASY_AUTOFILLTITLE.'</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="'.LANG_CLOSE.'">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body text-center">
          <div class="alert alert-info" role="alert">
            '.LANG_FANTASY_AUTOFILLTEXT.'<br><br>
            '.LANG_FANTASY_CONTINUE.'
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">'.LANG_CLOSE.'</button>
          '.($_GET[action]=="pricelist" ? '' : '<button type="button" class="btn btn-hl automat">'.LANG_FANTASY_FILL.'</button>').'
        </div>
      </div>
    </div>';
    }
  }

mysqli_close($link);
?>