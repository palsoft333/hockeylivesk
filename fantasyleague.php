<?php
$params = explode("/", htmlspecialchars($_GET[id]));

// pred zaciatkom ligy vyprazdnit tabulku fl_prices, fl_prices_g a fl_selects
$league = 134;
$uid = $_SESSION['logged'];
$m = mysql_query("SELECT * FROM 2004leagues WHERE id='$league'");
$n = mysql_fetch_array($m);
if(!$uid) 
  {
  $leaguecolor = $n[color];
  $active_league = $league;
  $title = 'Fantasy '.$n[longname];
  $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($n[longname])." text-gray-600 mr-1'></i>
                 <h1 class='h3 h3-fluid mb-1'>".LANG_FANTASY_TITLE."</h1>
                 <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$n[longname]."</h2>
                 <div style='max-width: 1000px;'>";
                 
  $content .= '<div class="alert alert-danger" role="alert">
                '.sprintf(LANG_FANTASY_NOTLOGGED, $n[longname]).'
               </div>
  </div>';
  }
else
{
function Generate_Roster($userid)
  {
  Global $league, $uid, $leaguecolor;
  if($userid==$uid) $hl = LANG_FANTASY_MYROSTER;
  else
    {
    $q = mysql_query("SELECT uname FROM e_xoops_users WHERE uid='$userid'");
    $f = mysql_fetch_array($q);
    $hl = LANG_FANTASY_ROSTEROF.' '.$f[uname];
    }
  $y = mysql_query("SELECT *, t2.price as aktual FROM fl_selects JOIN el_players t1 ON t1.id=fl_selects.pid JOIN fl_prices t2 ON t2.playerid=fl_selects.pid WHERE uid='$userid' && league='$league'");
  $roster .= '
     <div class="card shadow animated--grow-in">
      <div class="card-header">
        <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
          '.$hl.'
        </h6>
      </div>
      <div class="card-body">
        <h5 class="card-title text-danger h5-fluid">'.LANG_FANTASY_FORWARDS.'</h5>
        <div class="row">';
          $y = mysql_query("SELECT *, t2.price as aktual FROM fl_selects JOIN el_players t1 ON t1.id=fl_selects.pid JOIN fl_prices t2 ON t2.playerid=fl_selects.pid WHERE fl_selects.pos='F' && uid='$userid' && league='$league'");
          $i=0;
          while($u = mysql_fetch_array($y))
            {
            $players[$i] = array($u[teamshort], $u[name], $u[pos], $u[g], $u[a], $u[aktual], $u[delta], $u[pid]);
            $i++;
            }
          $i=0;
          while($i < 5)
            {
            if(!$players[$i][1])
              {
              if($userid==$uid) $butt = '<button class="btn btn-'.$leaguecolor.' btn-circle playerbutton show-dialog" id="buy-'.$i.'-0-F" data-toggle="tooltip" data-placement="bottom" title="'.LANG_FANTASY_BUYFORWARD.'"><i class="fas fa-user-plus"></i></button>';
              else $butt = '';
              $body = '<div class="col align-self-center text-center">'.$butt.'</div>';
              $volne=1;
              }
            else
              {
              if($userid==$uid) $butt = '<br><button class="btn btn-outline-'.$leaguecolor.' mt-2 playerbutton show-dialog" id="sell-'.$players[$i][7].'-0" data-toggle="tooltip" data-placement="bottom" title="'.LANG_FANTASY_SELLFORWARD.'"><i class="fas fa-money-bill-alt"></i> '.LANG_FANTASY_SELL.'</button>';
              else $butt = '';
              if($players[$i][6]>0) $delta = '<p class="text-center text-success small">(<i class="fas fa-caret-up"></i>'.money_format('%.0n', $players[$i][6]).')</p>';
              elseif($players[$i][6]<0) $delta = '<p class="text-center text-danger small">(<i class="fas fa-caret-down"></i>'.money_format('%.0n', $players[$i][6]).')</p>';
              else $delta = '<p class="small"></p>';
              $body = '<div class="col text-center">
                        <p class="text-center font-weight-bold p-fluid">'.money_format('%.0n', $players[$i][5]).'</p>
                        '.$delta.'
                        <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$players[$i][1].'" class="lazy rounded-circle img-thumbnail" style="width:100px; max-width:100px;">
                        <p class="p-fluid"><span class="font-weight-bold"><img class="flag-el '.$players[$i][0].'-small" src="/images/blank.png" alt="'.$players[$i][0].'">'.$players[$i][1].'</span><br>
                        '.LANG_PLAYERSTATS_F.'<br>
                        <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][3].'+'.$players[$i][4].'</span>
                        '.$butt.'</p>
                       </div>';
              $value = $value+$players[$i][5];
              $bol=1;
              }
            $roster .= $body;
            $i++;
            }
        $roster .= '
        </div>
        <h5 class="card-title text-danger h5-fluid">'.LANG_FANTASY_DEFENSE.'</h5>
        <div class="row">';
          $y = mysql_query("SELECT *, t2.price as aktual FROM fl_selects JOIN el_players t1 ON t1.id=fl_selects.pid JOIN fl_prices t2 ON t2.playerid=fl_selects.pid WHERE fl_selects.pos='D' && uid='$userid' && league='$league'");
          $i=5;
          while($u = mysql_fetch_array($y))
            {
            $players[$i] = array($u[teamshort], $u[name], $u[pos], $u[g], $u[a], $u[aktual], $u[delta], $u[pid]);
            $i++;
            }
          $i=5;
          while($i < 8)
            {
            if(!$players[$i][1])
              {
              if($userid==$uid) $butt = '<button class="btn btn-'.$leaguecolor.' btn-circle playerbutton show-dialog" id="buy-'.$i.'-0-D" data-toggle="tooltip" data-placement="bottom" title="'.LANG_FANTASY_BUYDEFENSE.'"><i class="fas fa-user-plus"></i></button>';
              else $butt = '';
              $body = '<div class="col align-self-center text-center">'.$butt.'</div>';
              $volne=1;
              }
            else
              {
              if($userid==$uid) $butt = '<br><button class="btn btn-outline-'.$leaguecolor.' mt-2 playerbutton show-dialog" id="sell-'.$players[$i][7].'-0" data-toggle="tooltip" data-placement="bottom" title="'.LANG_FANTASY_SELLDEFENSE.'"><i class="fas fa-money-bill-alt"></i> '.LANG_FANTASY_SELL.'</button>';
              else $butt = '';
              if($players[$i][6]>0) $delta = '<p class="text-center text-success small">(<i class="fas fa-caret-up"></i>'.money_format('%.0n', $players[$i][6]).')</p>';
              elseif($players[$i][6]<0) $delta = '<p class="text-center text-danger small">(<i class="fas fa-caret-down"></i>'.money_format('%.0n', $players[$i][6]).')</p>';
              else $delta = '<p class="small"></p>';
              $body = '<div class="col text-center">
                        <p class="text-center font-weight-bold p-fluid">'.money_format('%.0n', $players[$i][5]).'</p>
                        '.$delta.'
                        <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$players[$i][1].'" class="lazy rounded-circle img-thumbnail" style="width:100px; max-width:100px;">
                        <p class="p-fluid"><span class="font-weight-bold"><img class="flag-el '.$players[$i][0].'-small" src="/images/blank.png" alt="'.$players[$i][0].'">'.$players[$i][1].'</span><br>
                        '.LANG_PLAYERSTATS_D.'<br>
                        <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][3].'+'.$players[$i][4].'</span>
                        '.$butt.'</p>
                       </div>';
              $value = $value+$players[$i][5];
              $bol=1;
              }
            $roster .= $body;
            $i++;
            }
        $roster .= '
        </div>
        <h5 class="card-title text-danger h5-fluid">'.LANG_FANTASY_GOALIE.'</h5>
        <div class="row">';
          $y = mysql_query("SELECT *, t2.price as aktual FROM fl_selects JOIN el_goalies t1 ON t1.id=fl_selects.pid JOIN fl_prices_g t2 ON t2.playerid=fl_selects.pid WHERE fl_selects.pos='GK' && uid='$userid' && league='$league'");
          $i=8;
          while($u = mysql_fetch_array($y))
            {
            $players[$i] = array($u[teamshort], $u[name], $u[pos], $u[w], $u[s], $u[aktual], $u[delta], $u[pid]);
            $i++;
            }
          $i=8;
          if(!$players[$i][1])
            {
            if($userid==$uid) $butt = '<button class="btn btn-'.$leaguecolor.' btn-circle playerbutton show-dialog" id="buy-'.$i.'-1-GK" data-toggle="tooltip" data-placement="bottom" title="'.LANG_FANTASY_BUYGOALIE.'"><i class="fas fa-user-plus"></i></button>';
            else $butt = '';
            $body = '<div class="col align-self-center text-center">'.$butt.'</div>';
            $volne=1;
            }
          else
            {
            if($userid==$uid) $butt = '<br><button class="btn btn-outline-'.$leaguecolor.' mt-2 playerbutton show-dialog" id="sell-'.$players[$i][7].'-1" data-toggle="tooltip" data-placement="bottom" title="'.LANG_FANTASY_SELLGOALIE.'"><i class="fas fa-money-bill-alt"></i> '.LANG_FANTASY_SELL.'</button>';
            else $butt = '';
            if($players[$i][6]>0) $delta = '<p class="text-center text-success small">(<i class="fas fa-caret-up"></i>'.money_format('%.0n', $players[$i][6]).')</p>';
            elseif($players[$i][6]<0) $delta = '<p class="text-center text-danger small">(<i class="fas fa-caret-down"></i>'.money_format('%.0n', $players[$i][6]).')</p>';
            else $delta = '<p class="small"></p>';
            $body = '<div class="col text-center">
                      <p class="text-center font-weight-bold p-fluid">'.money_format('%.0n', $players[$i][5]).'</p>
                      '.$delta.'
                      <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$players[$i][1].'" class="lazy rounded-circle img-thumbnail" style="width:100px; max-width:100px;">
                      <p class="p-fluid"><span class="font-weight-bold"><img class="flag-el '.$players[$i][0].'-small" src="/images/blank.png" alt="'.$players[$i][0].'">'.$players[$i][1].'</span><br>
                      '.LANG_PLAYERSTATS_GK.'<br>
                      <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][3].' '.LANG_MATCHES_WINS1.'</span><br>
                      <span class="badge badge-pill badge-'.$leaguecolor.'">'.$players[$i][4].' '.LANG_FANTASY_SO.'</span>
                      '.$butt.'</p>
                     </div>';
            $value = $value+$players[$i][5];
            $bol=1;
            }
          $roster .= $body;
          $roster .= '
        </div>
      </div>
      <div class="card-footer">
        <p class="text-center">'.LANG_FANTASY_ROSTERVALUE.': <strong>'.money_format('%.0n', $value).'</strong></p>';
if($userid==$uid) $roster .= '
        <p class="text-center">
          <button class="btn btn-sm btn-'.$leaguecolor.' btn-icon-split show-dialog" id="sellteam"'.(!$bol ? ' disabled':'').'>
            <span class="icon">
              <i class="fas fa-money-bill-alt"></i>
            </span>
            <span class="text">'.LANG_FANTASY_SELLROSTER.'</span>
          </button>
          <button class="btn btn-sm btn-'.$leaguecolor.' btn-icon-split show-dialog" id="automat"'.($volne ? '':' disabled').'>
            <span class="icon">
              <i class="fas fa-robot"></i>
            </span>
            <span class="text">'.LANG_FANTASY_AUTOFILL.'</span>
          </button>
        </p>';
$roster .= '
      </div>
    </div>';
  return $roster;
  }
  
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => LANG_TIME_YEARS,
        'm' => LANG_TIME_MONTHS,
        'w' => LANG_TIME_WEEKS,
        'd' => LANG_TIME_DAYS,
        'h' => LANG_TIME_HOURS,
        'i' => LANG_TIME_MINUTES,
        's' => LANG_TIME_SECONDS,
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    if(strtolower($_SESSION[lang])=="sk") $hl = LANG_TIME_AGO.' '. implode(', ', $string);
    else $hl = implode(', ', $string).' '.LANG_TIME_AGO;
    return $string ? $hl : LANG_TIME_RIGHTNOW;
}

$content .= '
  <div class="modal fade" id="dialog" tabindex="-1" role="dialog" aria-labelledby="dialogTitle" aria-hidden="true">
  </div>';

// hlavna stranka
if($params[0]=="main")
  {
  $locale = explode(";",setlocale(LC_ALL, '0'));
  $locale = explode("=",$locale[0]);
  $locale = $locale[1];
  $q = mysql_query("SELECT * FROM fl_wallet WHERE uid='$uid' && league='$league'");
  $leaguecolor = $n[color];
  $active_league = $league;
  $title = 'Fantasy '.$n[longname];
  if(mysql_num_rows($q)>0)
    {
    $f = mysql_fetch_array($q);
    
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($n[longname])." text-gray-600 mr-1'></i>
                 <h1 class='h3 h3-fluid mb-1'>".LANG_FANTASY_TITLE."</h1>
                 <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$n[longname]."</h2>
                 <div style='max-width: 1000px;'>";
    
    $content .= '<nav aria-label="Fantasy navigation">
                  <ul class="pagination pagination-sm">
                    <li class="page-item">
                      <a class="page-link text-gray-800" href="/fantasy/select" aria-label="'.LANG_FANTASY_MYROSTER.'">
                        <span aria-hidden="true"><i class="fas fa-users"></i> '.LANG_FANTASY_MYROSTER.'</span>
                      </a>
                    </li>
                    <li class="page-item">
                      <a class="page-link text-gray-800 show-prices" href="#" aria-label="'.LANG_FANTASY_PRICELIST.'" id="pricelist">
                        <span aria-hidden="true"><i class="fas fa-money-bill-alt"></i> '.LANG_FANTASY_PRICELIST.'</span>
                      </a>
                    </li>
                  </ul>
                 </nav>
    
    <div class="row">
    
      <div class="col-md-6 col-xl-4 mb-4">
        <div class="card shadow animated--grow-in h-100">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
              '.LANG_FANTASY_BEST.'
              <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
            </h6>
          </div>
          <div class="card-body px-1">
            <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid" id="ranking">
              <thead>
                <tr>
                  <th class="text-center">#</th>
                  <th>'.LANG_FANTASY_MANAGER.'</th>
                  <th>'.LANG_TEAMSTATS_POINTS.'</th>
                  <th>'.LANG_FANTASY_VALUE.'</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td colspan="4" class="dataTables_empty">'.LANG_STATS_LOADING.'</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-xl-4 mb-4">
        <div class="card shadow animated--grow-in h-100">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
              '.LANG_FANTASY_BIGGESTJUMP.'
              <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
            </h6>
          </div>
          <div class="card-body px-1">
            <table class="table-hover table-light table-striped table-responsive w-100 p-fluid">
              <thead>
                <tr>
                  <th>'.LANG_PLAYERDB_PLAYER.'</th>
                  <th class="text-center">'.LANG_FANTASY_PRICE.'</th>
                  <th>'.LANG_FANTASY_CHANGE.'</th>
                </tr>
              </thead>
              <tbody>';
            $w = mysql_query("SELECT id, price, delta, teamshort, name, pos FROM fl_prices JOIN el_players t1 ON t1.id=fl_prices.playerid WHERE league='$league' UNION SELECT id, price, delta, teamshort, name, 'GK' as pos FROM fl_prices_g JOIN el_goalies t1 ON t1.id=fl_prices_g.playerid WHERE league='$league' ORDER BY delta DESC LIMIT 10");
            while($e = mysql_fetch_array($w))
              {
              if($e[delta]>0) $delta = '<span class="text-success"><i class="fas fa-caret-up"></i>'.$e[delta].'</span>';
              elseif($e[delta]<0) $delta = '<span class="text-danger"><i class="fas fa-caret-down"></i>'.$e[delta].'</span>';
              else $delta = '-';
              $content .= '<tr>
              <td class="text-nowrap" style="width:50%;"><img class="flag-el '.$e[teamshort].'-small" src="/images/blank.png" alt="'.$e[teamshort].'"> '.$e[name].' <span class="text-muted text-xs">('.$e[pos].')</span></td>
              <td class="text-center" style="width:25%;">'.$e[price].'</td>
              <td class="text-nowrap" style="width:25%;">'.$delta.'</td>
              </tr>';
              }
            $content .= '
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <div class="col-md-6 col-xl-4 mb-4">
        <div class="card shadow animated--grow-in h-100">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
              '.LANG_FANTASY_BIGGESTFALLS.'
              <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
            </h6>
          </div>
          <div class="card-body px-1">
            <table class="table-hover table-light table-striped table-responsive w-100 p-fluid">
              <thead>
                <tr>
                  <th>'.LANG_PLAYERDB_PLAYER.'</th>
                  <th class="text-center">'.LANG_FANTASY_PRICE.'</th>
                  <th>'.LANG_FANTASY_CHANGE.'</th>
                </tr>
              </thead>
              <tbody>';
            $w = mysql_query("SELECT id, price, delta, teamshort, name, pos FROM fl_prices JOIN el_players t1 ON t1.id=fl_prices.playerid WHERE league='$league' UNION SELECT id, price, delta, teamshort, name, 'GK' as pos FROM fl_prices_g JOIN el_goalies t1 ON t1.id=fl_prices_g.playerid WHERE league='$league' ORDER BY delta ASC LIMIT 10");
            while($e = mysql_fetch_array($w))
              {
              if($e[delta]>0) $delta = '<span class="text-success"><i class="fas fa-caret-up"></i>'.$e[delta].'</span>';
              elseif($e[delta]<0) $delta = '<span class="text-danger"><i class="fas fa-caret-down"></i>'.$e[delta].'</span>';
              else $delta = '-';
              $content .= '<tr>
              <td class="text-nowrap" style="width:50%;"><img class="flag-el '.$e[teamshort].'-small" src="/images/blank.png" alt="'.$e[teamshort].'"> '.$e[name].' <span class="text-muted text-xs">('.$e[pos].')</span></td>
              <td class="text-center" style="width:25%;">'.$e[price].'</td>
              <td class="text-nowrap" style="width:25%;">'.$delta.'</td>
              </tr>';
              }
            $content .= '
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
    </div>
    
    <div class="card mb-4 shadow animated--grow-in">
      <div class="card-header">
        <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
          '.LANG_FANTASY_LATESTTRANSACTIONS.'
          <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
        </h6>
      </div>
      <div class="card-body">
        <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid">
          <thead>
            <tr>
              <th>'.LANG_REPORT_TIME.'</th>
              <th>'.LANG_FANTASY_TRANSACTION.'</th>
            </tr>
          </thead>
          <tbody>';
          $w = mysql_query("SELECT t1.*, t2.uname, t3.name as pname, t3.teamshort as pshort, t4.name as gname, t4.teamshort as gshort FROM `fl_transactions` t1 JOIN e_xoops_users t2 ON t2.uid=t1.uid LEFT JOIN el_players t3 ON t3.id=t1.pid LEFT JOIN el_goalies t4 ON t4.id=t1.pid ORDER BY tstamp DESC LIMIT 20");
          while($e = mysql_fetch_array($w))
            {
            if($e[pos]=="GK")
              {
              $meno = $e[gname];
              $tshort = '<img class="flag-el '.$e[gshort].'-small" src="/images/blank.png" alt="'.$e[gshort].'">';
              $koho = LANG_FANTASY_AGOALIE;
              }
            elseif($e[pos]=="T")
              {
              $koho = LANG_FANTASY_WHOLETEAM;
              $tshort = "";
              $meno = "";
              }
            else
              {
              if($e[pos]=="F") $koho = LANG_FANTASY_AFORWARD;
              if($e[pos]=="D") $koho = LANG_FANTASY_ADEFENSE;
              $meno = $e[pname];
              $tshort = '<img class="flag-el '.$e[pshort].'-small" src="/images/blank.png" alt="'.$e[pshort].'">';
              }
            if($e[type]==1)
              {
              if($e[price]>0) $delta = LANG_FANTASY_WITHAPROFIT.' <span class="text-success">+'.money_format('%.0n', $e[price]).'</span>';
              elseif($e[price]<0) $delta = LANG_FANTASY_WITHLOSSOF.' <span class="text-danger">'.money_format('%.0n', $e[price]).'</span>';
              else $delta = LANG_FANTASY_WITHOUTPROFIT;
              $hl = LANG_FANTASY_SOLD;
              }
            else
              {
              $hl = LANG_FANTASY_BOUGHT;
              $delta = LANG_FANTASY_FORPRICE." ".money_format('%.0n', $e[price]);
              }
            $content .= '<tr>
            <td class="text-nowrap align-top" style="width:15%;">'.time_elapsed_string($e[tstamp]).'</td>
            <td style="width:85%;">'.LANG_FANTASY_MANAGER.' <b>'.$e[uname].'</b> '.$hl.' '.$koho.' '.$tshort.' <b>'.$meno.'</b> '.$delta.'</td>
            </tr>';
            }
          $content .= '
          </tbody>
        </table>
      </div>
    </div>
    
    <div class="card my-4 shadow animated--grow-in">
      <div class="card-header">
        <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
          '.LANG_BETS_FORWHAT.'
        </h6>
      </div>
      <div class="card-body">
         <p>'.LANG_BETS_FORWHATTEXT.'</p>
         <div class="card-columns">
         
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/mikina.jpg" class="lazy card-img-top" alt="Tmavomodrá mikina">
            <div class="card-body">
              <h5 class="card-title">Tmavomodrá mikina s kapucňou</h5>
              <p class="card-text"><small class="text-muted">veľkosť L</small></p>
            </div>
          </div>
          
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/bunda.jpg" class="lazy card-img-top" alt="Prechodná tmavomodrá bunda">
            <div class="card-body">
              <h5 class="card-title">Prechodná tmavomodrá bunda</h5>
              <p class="card-text"><small class="text-muted">veľkosť L</small></p>
            </div>
          </div>
          
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/batoh.jpg" class="lazy card-img-top" alt="Cestovný batoh">
            <div class="card-body">
              <h5 class="card-title">Cestovný batoh</h5>
            </div>
          </div>
          
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/ciapka.jpg" class="lazy card-img-top" alt="zimná čiapka">
            <div class="card-body">
              <h5 class="card-title">zimná čiapka</h5>
            </div>
          </div>
          
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/usbled.jpg" class="lazy card-img-top" alt="USB LED lampa">
            <div class="card-body">
              <h5 class="card-title">USB LED lampa</h5>
            </div>
          </div>
          
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/odznaky.jpg" class="lazy card-img-top" alt="Odznaky s hokejovým logom">
            <div class="card-body">
              <h5 class="card-title">Odznaky s hokejovým logom</h5>
            </div>
          </div>
          
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/pero.jpg" class="lazy card-img-top" alt="Pero">
            <div class="card-body">
              <h5 class="card-title">Pero</h5>
            </div>
          </div>
          
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/nalepky.jpg" class="lazy card-img-top" alt="Živicové nálepky">
            <div class="card-body">
              <h5 class="card-title">Živicové nálepky</h5>
            </div>
          </div>
          
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/drziak.jpg" class="lazy card-img-top" alt="Držiak na puk">
            <div class="card-body">
              <h5 class="card-title">Držiak na puk</h5>
            </div>
          </div>
          
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/placka.jpg" class="lazy card-img-top" alt="Závesná placka Ľubomír Višňovský">
            <div class="card-body">
              <h5 class="card-title">Závesná placka Ľubomír Višňovský</h5>
            </div>
          </div>
          
          <div class="card">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/ceny/zastavka.jpg" class="lazy card-img-top" alt="2x zástavka Slovensko na auto">
            <div class="card-body">
              <h5 class="card-title">2x zástavka Slovensko na auto</h5>
            </div>
          </div>
          
         </div>
      </div>
    </div>';
            
	$script_end = '<script type="text/javascript">
	$(document).ready(function() {
	$("#ranking").dataTable( {
			"fnRowCallback": function( nRow, aData, iDisplayIndex ) {
        if ( aData[4] == "0" )
          {
          $("td", nRow).css("background-color", "rgba(255, 0, 0, 0.08)");
          }
			return nRow;
		},
        
		"bProcessing": true,
		"bServerSide": true,
		"bFilter": false,
		"bLengthChange": false,
		"bInfo": false,
		"bPaginate": false,
    "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 ] }, { "bVisible": false, "aTargets": [ 4 ] }],
    "oLanguage": { "sUrl": "/includes/lang/datatables_'.$locale.'.txt" },
    "aaSorting": [[2, "desc"], [3, "desc"]],
    "bAutoWidth": false,
    "aoColumns": [{ "sWidth": "5%", className: "text-center" }, { "sWidth": "60%" }, { "sWidth": "20%", className: "text-nowrap" }, { "sWidth": "20%", className: "text-nowrap"}],
		"sPaginationType": "numbers",
		"bJQueryUI": false,
		"sAjaxSource": "/includes/fantasyranking.php?lid='.$league.'"
	}
	 );	 
} );
</script>';
    }
  else
    {
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($n[longname])." text-gray-600 mr-1'></i>
                 <h1 class='h3 h3-fluid mb-1'>".LANG_FANTASY_TITLE."</h1>
                 <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$n[longname]."</h2>
                 <div style='max-width: 1000px;'>";
    
    $content .= '
    <div class="row">
      <div class="col-lg-7">
        <div class="card mb-4 shadow animated--grow-in">
          <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/khlfantasy.jpg" class="lazy card-img-top" alt="Fantasy '.$n[longname].'">
          <div class="card-body">
            <h5 class="card-title">Fantasy '.$n[longname].' - pravidlá</h5>
            <ol type="1">
              <li>Zúčastniť sa môžu iba <a href="/register">registrovaní</a> užívatelia stránky hockey-LIVE.sk (po prihlásení sa, kliknite na Fantasy '.$n[longname].' v ľavom menu '.$n[longname].').</li>
              <li>Každý manažér (užívateľ) má na začiatku na konte k dispozícii 180 000 EUR.</li>
              <li>Každý manažér si kúpi 5 útočníkov, 3 obrancov a 1 brankára z dostupných hráčov ligy.</li>
              <li>Manažér za hráčov nezačne zbierať body, až kým jeho tím nebude kompletný (8 hráčov a 1 brankár).</li>
              <li>Trhová cena hráčov sa mení podľa ich reálnej výkonnosti.</li>
              <li>Hráča alebo brankára je možné kedykoľvek predať za aktuálnu trhovú cenu.</li>
              <li>Nie je možné nakupovať z tímov, ktorých zápasy sa práve hrajú, alebo ešte neboli spracované systémom.</li>
              <li>Bodovanie:<br>Gól, asistencia = <b>1 bod</b>,<br>výhra brankára = <b>2 body</b>,<br>čisté konto brankára = <b>2 body</b>.</li>
            </ol>
            <a href="#" class="btn btn-'.$leaguecolor.' signin">OK, poďme na to!</a>
          </div>
        </div>
      </div>
    </div>';
  $script_end .= "<script>
    $(function() {
      $(document).on('click', '.signin', function() {
        $.ajax({
          url: '/includes/dialog.php?action=signin&lid=$league',
          success: function(data){
              location.href = '/fantasy/main';
          }   
        });
       });
      });
  </script>";
    }
  $content .= "</div>";
  }
  
// zostava ineho manazera
if($params[0]=="roster")
  {
  $lid = $params[2];
  $userid = $params[1];
  $leaguecolor = $n[color];
  $active_league = $league;
  $title = 'Fantasy '.$n[longname].' - zostava manažéra';
  
  $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($n[longname])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_FANTASY_TITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$n[longname]."</h2>
               <div style='max-width: 1000px;'>";
  
  $content .= '<nav aria-label="Fantasy navigation">
                  <ul class="pagination pagination-sm">
                    <li class="page-item">
                      <a class="page-link text-gray-800" href="/fantasy/main" aria-label="'.LANG_BETS_RANK.'">
                        <span aria-hidden="true"><i class="fas fa-list-ol"></i> '.LANG_BETS_RANK.'</span>
                      </a>
                    </li>
                    <li class="page-item">
                      <a class="page-link text-gray-800 show-prices" href="#" aria-label="'.LANG_FANTASY_PRICELIST.'" id="pricelist">
                        <span aria-hidden="true"><i class="fas fa-money-bill-alt"></i> '.LANG_FANTASY_PRICELIST.'</span>
                      </a>
                    </li>
                    <li class="page-item">
                      <a class="page-link text-gray-800" href="/fantasy/select" aria-label="'.LANG_FANTASY_MYROSTER.'">
                        <span aria-hidden="true"><i class="fas fa-users"></i> '.LANG_FANTASY_MYROSTER.'</span>
                      </a>
                    </li>
                  </ul>
                 </nav>';
  $q = mysql_query("SELECT * FROM fl_wallet WHERE uid='$userid' && league='$lid'");
  if(mysql_num_rows($q)>0)
    {
    $content .= Generate_Roster($userid);
    }
  else
    {
    $content .= '<div class="message" style="text-align:center;">Tento užívateľ nehrá Fantasy ligu.</div>';
    }
  $content .= '</div>';
  }

// moja zostava
if($params[0]=="select")
  {
  $leaguecolor = $n[color];
  $active_league = $league;
  $title = 'Fantasy '.$n[longname].' - moja zostava';
  
  $content .= '<div id="toasts" class="fixed-top" style="top: 80px; right: 23px; left: initial; z-index:1051;"></div>';
  $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($n[longname])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".LANG_FANTASY_TITLE."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$n[longname]."</h2>
               <div style='max-width: 1000px;'>";
               
  $content .= '<nav aria-label="Fantasy navigation">
                  <ul class="pagination pagination-sm">
                    <li class="page-item">
                      <a class="page-link text-gray-800" href="/fantasy/main" aria-label="'.LANG_BETS_RANK.'">
                        <span aria-hidden="true"><i class="fas fa-list-ol"></i> '.LANG_BETS_RANK.'</span>
                      </a>
                    </li>
                    <li class="page-item">
                      <a class="page-link text-gray-800 show-prices" href="#" aria-label="'.LANG_FANTASY_PRICELIST.'" id="pricelist">
                        <span aria-hidden="true"><i class="fas fa-money-bill-alt"></i> '.LANG_FANTASY_PRICELIST.'</span>
                      </a>
                    </li>
                  </ul>
                 </nav>';
  
    $w = mysql_query("SELECT * FROM fl_wallet WHERE uid='$uid' && league='$league'");
    $e = mysql_fetch_array($w);
    
    $content .='
    <div class="row justify-content-center">
      <div class="col-sm-8 col-md-5">
        <div class="card shadow animated--grow-in mb-4 text-center">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
              '.LANG_FANTASY_BALANCE.'
            </h6>
          </div>
          <div class="card-body">
            <p class="h4 h4-fluid">'.money_format('%.0n', $e[balance]).'</p>
            <p class="h6 h6-fluid">'.$e[points].' '.LANG_TEAMSTATS_PTS.'</p>
          </div>
          <div class="card-footer text-light '.($e[active]==0 ? 'bg-danger':'bg-success').'">
            '.($e[active]==0 ? LANG_FANTASY_NOTACTIVE:LANG_FANTASY_ACTIVE).'
          </div>
        </div>
      </div>
    </div>
    
    '.Generate_Roster($uid).'
  
  </div>';
                
  $script_end .= "
  <script>  
  $(function() {
      $(document).on('click', '.show-dialog', function() {
        var action = $(this).attr('id').split('-');
        var lid = $league;
        var butt = $(this);
        $.ajax({
          url: '/includes/dialog.php?action='+action[0]+'&pid='+action[1]+'&g='+action[2]+'&lid='+lid+'&pos='+action[3],
          success: function(data){
              butt.tooltip('hide');
              $('#dialog').html(data);
              $('#dialog').modal('show');
          }   
        });
      });
      $(document).on('change', '#team', function() {
        if(this.value!='0') {
        var orez = this.value.split('-');
        var pos = orez[0];
        var team = orez[1];
        var lid = $league;
        $.ajax({
          url: '/includes/dialog.php?action=buy&lid='+lid+'&team='+team+'&pos='+pos,
          success: function(data){
              $('.modal-body').html(data);
              $('.buy').prop('disabled', false);
          }   
        });
        }
      });
      $(document).on('click', '.buy', function() {
        var lid = $league;
        var orez = $('#player').val().split('-');
        var player = orez[0];
        var pos = orez[1];
        $.ajax({
          url: '/includes/dialog.php?action=buy&lid='+lid+'&player='+player+'&pos='+pos,
          success: function(data){
              if(data=='notenough')
                {
                var today  = new Date();
                var smallText = today.toLocaleDateString('sk-SK');
                Notification('money-bill-alt text-danger', '".LANG_FANTASY_TITLE."', smallText, '".LANG_FANTASY_NOTENOUGHMONEY."', 5000);
                }
              else
                {
                $('#dialog').modal('hide');
                location.href = '/fantasy/select';
                }
          }   
        });
      });
      $(document).on('click', '.sell', function() {
        var lid = $league;
        var orez = $('#pid').val().split('-');
        var pid = orez[0];
        var g = orez[1];
        $.ajax({
          url: '/includes/dialog.php?action=sell&lid='+lid+'&playerid='+pid+'&g='+g,
          success: function(data){
              $('#dialog').modal('hide');
              location.href = '/fantasy/select';
          }   
        });
      });
      $(document).on('click', '.sellteam', function() {
        var lid = $league;
        $.ajax({
          url: '/includes/dialog.php?action=sellteam&ok='+lid,
          success: function(data){
              $('#dialog').modal('hide');
              location.href = '/fantasy/select';
          }   
        });
      });
      $(document).on('click', '.automat', function() {
        var lid = $league;
        $.ajax({
          url: '/includes/dialog.php?action=automat&ok='+lid,
          success: function(data){
              $('#dialog').modal('hide');
              location.href = '/fantasy/select';
          }   
        });
      });
    });
  </script>";
  }
$script_end .= '
<script>
    $(document).on("click", ".show-prices", function() {
      var lid = '.$league.';
      $.ajax({
        url: "/includes/dialog.php?action=pricelist&lid="+lid,
        success: function(data){
            $("#dialog").html(data);
            $("#dialog").modal(\'show\');
        }   
      });
    });

    $(document).on("change", "#pricesteam", function() {
      if(this.value!="0") {
      var orez = this.value.split("-");
      var team = orez[1];
      var lid = '.$league.';
      $.ajax({
        url: "/includes/dialog.php?action=pricelist&lid="+lid+"&team="+team,
        success: function(data){
             $(".modal-body").html(data);
        }   
      });
      }
    });
</script>';
}
?>