<?
/*
* Funkcia pre vypis zapasov jednotliveho kola/dna/playoff
* version: 1.0.1 (26.11.2015 - len drobne upravy pre pouzitie s novou verziou stranky)
* version: 1.5.0 (13.2.2020 - prispôsobené pre Bootstrap4 template)
* @param $lid integer - ID ligy
* @param $params array - URL parametre aktualnej ligy
* @param $sel integer - vybrane kolo
* @param $potype string - vybrane kolo playoff
* @return $matches string
*/

function Get_Matches($lid, $params, $sel, $potype)
  {
  Global $title, $leaguecolor, $link;
  $uid = $_SESSION['logged'];
  $matches="";
	// zistenie ci sa jedna o EL
	$q = mysqli_query($link, "SELECT * FROM 2004leagues WHERE id='$lid'");
	$f = mysqli_fetch_array($q);
    $f["el"]=$f["el"] ?? null;
    $f["longname"]=$f["longname"] ?? "";
	// JEDNA SA O EL
	if($f["el"]==1)
		{
    $dnes = date("Y-m-d");
		if($f["el"]==1) { $matches_table="el_matches"; $tips_table="el_tips"; $el=1; }
		if($f["active"]==1) $a = mysqli_query($link, "SELECT * FROM ".$matches_table." WHERE datetime > '".$dnes." 07:00:00' && league='".$lid."' ORDER BY datetime ASC LIMIT 0,1");
		else $a = mysqli_query($link, "SELECT * FROM ".$matches_table." WHERE kolo='1' && league='".$lid."' ORDER BY datetime ASC LIMIT 0,1");
		$b = mysqli_fetch_array($a);
        if(!isset($b["kolo"])) $b["kolo"]=0;
		// zistenie aktualneho, predosleho a dalsieho kola
		$c = mysqli_query($link, "SELECT * FROM ".$matches_table." WHERE league='".$lid."' ORDER BY kolo DESC LIMIT 0,1");
		$d = mysqli_fetch_array($c); 
		$maxim = $d["kolo"];
		if(!isset($_GET["sel"])) { $act_round = $b["kolo"]; }
		else $act_round = $_GET["sel"];
		$prev_round=$act_round-1;
		$next_round=$act_round+1;
		$e = mysqli_query($link, "SELECT DATE_FORMAT(datetime, '%e.%c.%Y') as datum, DATE_FORMAT(datetime, '%c/%e/%Y') as datumus FROM $matches_table WHERE kolo='$act_round' && league='$lid' ORDER BY datetime ASC LIMIT 0,1");
		$g = mysqli_fetch_array($e);
        $g["datumus"] = $g["datumus"] ?? null;
        $g["datum"] = $g["datum"] ?? null;
        $po=0;
		if($act_round!=0) 
			{
			if(strstr($f["longname"], 'NHL') || strstr($f["longname"], 'KHL'))
        {
        if($_SESSION["lang"] != 'sk') $hl = LANG_MATCHES_GAMEDAY.' '.$g["datumus"];
        else $hl = LANG_MATCHES_GAMEDAY.' '.$g["datum"];
        }
      else
        {
        if($_SESSION["lang"] != 'sk') $hl = LANG_ROUND.' '.$act_round;
        else $hl = $act_round.'.'.LANG_ROUND;
        }
			}
		else 
			{
			$hl = "Playoff";
			$po=1;
			if(!$potype)
				{
				$posel = mysqli_query($link, "SELECT league, potype FROM el_playoff WHERE league='$lid' ORDER BY id DESC LIMIT 1");
				$pofet = mysqli_fetch_array($posel);
				$potype = $pofet["potype"];
				}
      elseif($potype=="quarter") $potype="stvrt";
			}
		if(!$uid)
			{
			// neprihlaseny, nenataha tipy
			if($po==1) 
        {
        $e = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
        $e = mysqli_query($link, "SELECT dt.*, NULL as tip1, NULL as tip2, NULL as komentar, MAX(CAST(g.time as DECIMAL(5,2))) as cas FROM el_playoff JOIN (SELECT * FROM $matches_table WHERE kolo='0' && league='$lid')dt ON (dt.team1short=el_playoff.team1 && dt.team2short=el_playoff.team2 || dt.team2short=el_playoff.team1 && dt.team1short=el_playoff.team2) LEFT JOIN el_goals g ON g.matchno=dt.id WHERE el_playoff.potype='$potype' && el_playoff.league='$lid' GROUP BY id ORDER BY datetime");
        }
			else $e = mysqli_query($link, "SELECT m.id, m.team1short, m.team1long, m.team2short, m.team2long, m.goals1, m.goals2, m.pp1, m.pp2, m.kedy, m.t1_pres, m.t2_pres, m.goal, m.datetime, m.kolo, m.next_refresh, m.fs_tv, m.league, m.active, NULL as tip1, NULL as tip2, NULL as komentar, MAX(CAST(g.time as DECIMAL(5,2))) as cas FROM $matches_table m LEFT JOIN el_goals g ON g.matchno=m.id WHERE kolo='$act_round' && league='$lid' GROUP BY m.id ORDER BY datetime ASC");
			}
		else
			{
			// prihlaseny, tahat aj tipy
			if($po==1) 
        {
        $e = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
        $e = mysqli_query($link, "SELECT dt.*, MAX(CAST(g.time as DECIMAL(5,2))) as cas FROM el_playoff JOIN (SELECT $matches_table.*, et.tip1, et.tip2, et.komentar, ft.col, ft.rate, ft.amount, gt.k1, gt.kx, gt.k2 FROM $matches_table LEFT JOIN (SELECT matchid, userid, tip1, tip2, komentar FROM $tips_table WHERE userid='$uid')et ON (et.matchid=$matches_table.id) LEFT JOIN (SELECT matchid, col, rate, amount FROM 2004bets WHERE userid='$uid' && el='1')ft ON (ft.matchid=$matches_table.id) LEFT JOIN (SELECT matchid, k1, kx, k2 FROM 2004rates WHERE el='1')gt ON (ft.matchid=$matches_table.id) WHERE kolo='0' && league='$lid')dt ON (dt.team1short=el_playoff.team1 && dt.team2short=el_playoff.team2 || dt.team2short=el_playoff.team1 && dt.team1short=el_playoff.team2) LEFT JOIN el_goals g ON g.matchno=dt.id WHERE el_playoff.potype='$potype' && el_playoff.league='$lid' GROUP BY id ORDER BY datetime ASC, id ASC");
        }
			else $e = mysqli_query($link, "SELECT $matches_table.*, dt.tip1, dt.tip2, dt.komentar, et.col, et.rate, et.amount, ft.k1, ft.kx, ft.k2, MAX(CAST(g.time as DECIMAL(5,2))) as cas FROM $matches_table LEFT JOIN (SELECT matchid, userid, tip1, tip2, komentar FROM $tips_table WHERE userid='$uid')dt ON (dt.matchid=$matches_table.id) LEFT JOIN (SELECT matchid, col, rate, amount FROM 2004bets WHERE userid='$uid' && el='1')et ON (et.matchid=$matches_table.id) LEFT JOIN (SELECT matchid, k1, kx, k2 FROM 2004rates WHERE el='1')ft ON (ft.matchid=$matches_table.id) LEFT JOIN el_goals g ON g.matchno=el_matches.id WHERE kolo='$act_round' && league='$lid' GROUP BY el_matches.id ORDER BY datetime ASC, id ASC");
			}
		}
	// NEJEDNA SA O EL
	else 
		{
		if(!isset($_GET["sel"]))
			{
			$dnes = date("Y-m-d");
			$c = mysqli_query($link, "SELECT datetime FROM 2004matches WHERE datetime LIKE '$dnes%' && league='$lid' LIMIT 1");
			if(mysqli_num_rows($c)==1) 
        {
        $_GET["sel"] = $dnes;
        $b["datumik"] = date("j.n.Y");
        }
			else 
				{
				$a = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
				$a = mysqli_query($link, "SELECT DATE_FORMAT(datetime, '%Y-%m-%d') as datum, DATE_FORMAT(datetime, '%e.%c.%Y') as datumik FROM 2004matches WHERE league='".$lid."' GROUP BY datum ORDER BY datetime ASC LIMIT 1");
				$b = mysqli_fetch_array($a);
                $b["datum"]=$b["datum"] ?? "";
                $b["datumik"]=$b["datumik"] ?? "";
				$_GET["sel"] = $b["datum"];
				}
			}
		else
			{
			$a = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
			$a = mysqli_query($link, "SELECT DATE_FORMAT(datetime, '%Y-%m-%d') as datum, DATE_FORMAT(datetime, '%e.%c.%Y') as datumik FROM 2004matches WHERE league='".$lid."'  && datetime LIKE '".$_GET["sel"]."%' GROUP BY datum ORDER BY datetime ASC LIMIT 1");
			$b = mysqli_fetch_array($a);
            $b["datum"]=$b["datum"] ?? "";
            $b["datumik"]=$b["datumik"] ?? "";
			}
		$da = explode("-", $_GET["sel"]);
        $da[1]=$da[1] ?? "";
        $da[2]=$da[2] ?? "";
		if($_SESSION["lang"] != 'sk') { $hl = LANG_MATCHES_GAMEDAY.' - '.$da[1].'/'.$da[2]; }
		else { $hl = LANG_MATCHES_GAMEDAY.' - '.$b["datumik"]; }
		if(!$uid) $e = mysqli_query($link, "SELECT m.id, m.team1short, m.team1long, m.team2short, m.team2long, m.goals1, m.goals2, m.kedy, m.datetime, NULL as kolo, m.next_refresh, m.fs_tv, m.league, m.active, NULL as tip1, NULL as tip2, NULL as komentar, MAX(CAST(g.time as DECIMAL(5,2))) as cas FROM 2004matches m LEFT JOIN 2004goals g ON g.matchno=m.id WHERE m.league='".$lid."' && m.datetime LIKE '".$_GET["sel"]."%' GROUP BY m.id ORDER BY m.datetime ASC, m.id ASC");
		else $e = mysqli_query($link, "SELECT 2004matches.*, NULL as kolo, dt.tip1, dt.tip2, dt.komentar, et.col, et.rate, et.amount, ft.k1, ft.kx, ft.k2, MAX(CAST(g.time as DECIMAL(5,2))) as cas FROM 2004matches LEFT JOIN (SELECT matchid, userid, tip1, tip2, komentar FROM 2004tips WHERE userid='$uid')dt ON (dt.matchid=2004matches.id) LEFT JOIN (SELECT matchid, col, rate, amount FROM 2004bets WHERE userid='".$uid."' && el='0')et ON (et.matchid=2004matches.id) LEFT JOIN (SELECT matchid, k1, kx, k2 FROM 2004rates WHERE el='0')ft ON (ft.matchid=2004matches.id) LEFT JOIN 2004goals g ON g.matchno=2004matches.id WHERE league='".$lid."' && datetime LIKE '".$_GET["sel"]."%' GROUP BY 2004matches.id ORDER BY datetime ASC, id ASC");
		$el=0;
		}
  // VYPIS ZAPASOV
  $title = LANG_TEAMSTATS_MATCHES." ".$f["longname"]." - ".$hl;
  $leaguecolor = LeagueColor($f["longname"]);
  $matches .= '<div id="toasts" class="fixed-top" style="top: 80px; right: 23px; left: initial; z-index:3;"></div>';
  $matches .= "<div id='games-spinner' class='position-absolute' style='top: 50%; left: 50%; z-index: 2; display: none;'>
                <div class='spinner-border text-".$leaguecolor."' role='status'>
                  <span class='sr-only'>Loading...</span>
                </div>
               </div>
               <i class='float-left h1 h1-fluid ll-".LeagueFont($f["longname"])." text-gray-600 mr-1'></i>
               <h1 class='h3 h3-fluid mb-1'>".$f["longname"]."</h1>
               <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$hl."</h2>";
               
  $matches .= '<nav aria-label="Game navigation">';
  // navigacia
	// kola alebo playoff (EL)
	if($f["el"]==1)
		{
		if(strstr($f["longname"], 'NHL')) 
      {
      $next = LANG_MATCHES_NEXTDAY;
      $prev = LANG_MATCHES_PREVDAY;
      }
    else
      {
      $next = LANG_MATCHES_NEXTROUND;
      $prev = LANG_MATCHES_PREVROUND;
      }
		if($act_round==1) $matches .= '<ul class="pagination pagination-sm">
                                    <li class="page-item">
                                      <a class="page-link text-gray-800" href="#" aria-label="'.$next.'" onclick="GetGames(\''.$params[0].'/'.$next_round.'\');">
                                        <span aria-hidden="true">'.$next.' <i class="fas fa-angle-double-right"></i></span>
                                      </a>
                                    </li>
                                  </ul>';
		elseif($act_round==$maxim) $matches .= '<ul class="pagination pagination-sm">
                                              <li class="page-item">
                                                <a class="page-link text-gray-800" href="#" aria-label="'.$prev.'" onclick="GetGames(\''.$params[0].'/'.$prev_round.'\');">
                                                  <span aria-hidden="true"><i class="fas fa-angle-double-left"></i> '.$prev.'</span>
                                                </a>
                                              </li>
                                            </ul>';
		elseif($po==1)
			{
			$g = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
			$g = mysqli_query($link, "SELECT potype FROM el_playoff WHERE league='$lid' GROUP BY potype ORDER BY id ASC");
			if(mysqli_num_rows($g)>1)
				{
        $matches .= '<ul class="pagination pagination-sm">';
                $params[1]=$params[1] ?? null;
				$i=0;
				while($i<mysqli_num_rows($g))
					{
					$h = mysqli_fetch_array($g);
					$dis="";
					if($params[1]=="") $params[1]=$potype;
					if($params[1]==$h["potype"]) $dis = " disabled";
					if($h["potype"]=="baraz") $nam = LANG_QUALIFYROUND;
					elseif($h["potype"]=="stvrt") $nam = LANG_QUARTERFINAL;
					elseif($h["potype"]=="semi") $nam = LANG_SEMIFINAL;
					elseif($h["potype"]=="final") $nam = LANG_FINAL;
					elseif($h["potype"]=="stanley")
            {
            if(strstr($f["longname"], 'NHL')) $nam = LANG_STANLEY;
            if(strstr($f["longname"], 'KHL')) $nam = LANG_GAGARIN;
            }
					else $nam = LANG_RELEGATION;
					if($params[1]==$h["potype"]) $matches .= '<li class="page-item disabled">
                                                    <a class="page-link" tabindex="-1" href="#" aria-disabled="true">'.$nam.'</a>
                                                  </li>';
					else $matches .= '<li class="page-item">
                              <a class="page-link text-'.$leaguecolor.'" href="#" aria-label="'.$h["potype"].'" onclick="GetGames(\''.$params[0].'/'.$h["potype"].'\');">
                                <span aria-hidden="true">'.$nam.'</span>
                              </a>
                            </li>';
					$i++;
					}
        $matches .= '</ul>';
				}
			}
		else $matches .= '<ul class="pagination pagination-sm">
                        <li class="page-item">
                          <a class="page-link text-gray-800" href="#" aria-label="'.$prev.'" onclick="GetGames(\''.$params[0].'/'.$prev_round.'\');">
                            <span aria-hidden="true"><i class="fas fa-angle-double-left"></i> '.$prev.'</span>
                          </a>
                        </li>
                        <li class="page-item">
                          <a class="page-link text-gray-800" href="#" aria-label="'.$next.'" onclick="GetGames(\''.$params[0].'/'.$next_round.'\');">
                            <span aria-hidden="true">'.$next.' <i class="fas fa-angle-double-right"></i></span>
                          </a>
                        </li>
                      </ul>';
		}
	// hracie dni (NON-EL)
	else
		{
    $matches .= LANG_MATCHES_GAMEDAY1.': 
    <ul class="pagination pagination-sm">';
    $gd = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
    $gd = mysqli_query($link, "SELECT DATE_FORMAT(datetime, '%e.%c.') as date, DATE_FORMAT(datetime, '%c/%e') as dateus, DATE_FORMAT(datetime, '%Y-%m-%d') as datum FROM 2004matches WHERE league='$lid' GROUP BY date ORDER BY datetime ASC");
    while(list($datem,$dateus,$datum) = mysqli_fetch_array($gd)) 
      {
      if($_SESSION["lang"] != 'sk') $datem = $dateus;
      if($_GET["sel"]==$datum) $matches .= '<li class="page-item disabled">
                                            <a class="page-link" tabindex="-1" href="#" aria-disabled="true">'.$datem.'</a>
                                          </li>';
      else $matches .= '<li class="page-item">
                          <a class="page-link text-'.$leaguecolor.'" href="#" aria-label="'.$datem.'" onclick="GetGames(\''.$params[0].'/'.$datum.'\');">
                            <span aria-hidden="true">'.$datem.'</span>
                          </a>
                        </li>';
      }
    $matches .= "</ul>";
		}
  $matches .= '
  </nav>
  <div class="row">';
  $poc=0;
  // vyfiltrovat zranenych hracov
  if(strstr($f["longname"], 'NHL') || strstr($f["longname"], 'KHL'))
    {
    if(strstr($f["longname"], 'NHL')) include('slovaks.php');
    else include('slovaki.php');
    $z = mysqli_query($link, "SELECT * FROM el_injuries WHERE league='".$lid."'");
    while($zr = mysqli_fetch_array($z))
      {
      $zra[] = $zr["name"];
      }
    }
  $struct_data=array();
  $pred="";
  while($g = mysqli_fetch_array($e))
    {
    $opt=$los1=$los2=$goals=$slov=$bets=$suffix=$scroll=$bckg=$kedy=$tv=$ot="";
    // vyfiltrovat prestupenych hracov
    if(strstr($f["longname"], 'NHL') || strstr($f["longname"], 'KHL')) {
      $tran1 = $tran2 = array();
      if(date("n")<8) {
        $rok = date("Y")-1;
        $season_start = $rok."-01-08";
        }
      else $season_start = date("Y")."-01-08";
      $tr = mysqli_query($link, "SELECT from_team, pname FROM transfers WHERE (from_team='".$g["team1short"]."' || from_team='".$g["team2short"]."') && datetime>'".$season_start."'");
      while($tra = mysqli_fetch_array($tr))
        {
        if($tra["from_team"]==$g["team1short"]) $tran1[] = $tra["pname"];
        if($tra["from_team"]==$g["team2short"]) $tran2[] = $tra["pname"];
        }
    }
    // TV live
    if($g["fs_tv"]!=NULL && $g["fs_tv"]!='[]' && $g["kedy"]!="konečný stav")
      {
      $tvarr = json_decode($g["fs_tv"], true);
      $tvarr = implode(", ",$tvarr);
      $tv .= '<div class="bg-gray-300 border-bottom p-1 text-center text-xs">
                  <p class="m-0"><strong><i class="fas fa-tv"></i> LIVE:</strong> '.$tvarr.'</p>
              </div>';
      }
    // tipy
    if($uid && strtotime($g["datetime"]) > time())
      {
      $bets .= '<div class="row align-items-center bg-light border-top border-bottom no-gutters py-3" id="bet-'.$poc.'">
                  <div class="col-12 font-weight-bold small text-center">'.LANG_MATCHES_PLACEBET.'</div>
                  <div class="col-6 text-center">
                    <div class="text-xs font-weight-bold">'.LANG_TEAMSTATS_SCORE.' 1</div>
                    <select id="tip1-'.$g["id"].$f["el"].'">'.Generate_Bet_List($g["tip1"]).'</select>
                  </div>
                  <div class="col-6 text-center">
                    <div class="text-xs font-weight-bold">'.LANG_TEAMSTATS_SCORE.' 2</div>
                    <select id="tip2-'.$g["id"].$f["el"].'">'.Generate_Bet_List($g["tip2"]).'</select>
                  </div>
                </div>';
      }
    // slovaci v akcii
    if((strstr($f["longname"], 'NHL') || strstr($f["longname"], 'KHL')) && $g["kedy"]!="konečný stav")
      {
      $pia1 = array_keys($slovaks, $g["team1short"]);
      $gia1 = array_keys($brankari, $g["team1short"]);
      $inaction1 = array_merge($pia1, $gia1);
      if(count($zra)>0) $inaction1 = array_diff($inaction1, $zra);
      if(count($tran1)>0) $inaction1 = array_diff($inaction1, $tran1);
      $inaction1 = array_values($inaction1);
      $pia2 = array_keys($slovaks, $g["team2short"]);
      $gia2 = array_keys($brankari, $g["team2short"]);
      $inaction2 = array_merge($pia2, $gia2);
      if(count($zra)>0) $inaction2 = array_diff($inaction2, $zra);
      if(count($tran2)>0) $inaction2 = array_diff($inaction2, $tran2);
      $inaction2 = array_values($inaction2);
      if(count($inaction1)>0 || count($inaction2)>0)
        {
        $slov .= '<div class="row no-gutters mb-4 mt-2 align-items-center">
          <div class="col-6 text-center text-xs">';
        $y=0;
        while($y<count($inaction1))
          {
          $slov .= '<i class="fas fa-user"></i> '.$inaction1[$y].'<br>';
          $y++;
          }
        $slov .= '</div><div class="col-6 text-center text-xs">';
        $y=0;
        while($y<count($inaction2))
          {
          $slov .= '<i class="fas fa-user"></i> '.$inaction2[$y].'<br>';
          $y++;
          }
        $slov .= "</div></div>";
        }
      }
    // samotna zapasova karta
    if(($potype=="stvrt" || $potype=="semi" || $potype=="baraz") && $pred=="konečný stav" && $g["kedy"]!="konečný stav") { $scroll = '<div class="col-12 mb-3 ml-1 h6 h6-fluid text-uppercase font-weight-bold scrollhere">Nadchádzajúce zápasy</div>';}
    if(strtotime($g["datetime"]) > time()) $opt .= '<a href="/game/'.$g["id"].$f["el"].'-'.SEOtitle($g["team1long"]." vs ".$g["team2long"]).'" class="btn btn-sm btn-light btn-icon-split">
                                                      <span class="icon text-gray-600">
                                                        <i class="fas fa-search"></i>
                                                      </span>
                                                      <span class="text text-gray-800">'.LANG_MATCHES_DETAIL.'</span>
                                                    </a>';
    elseif($g["kedy"]!="konečný stav") $opt .= '<span class="small"><i class="fas fa-clock"></i> '.LANG_MATCHES_WAITING.'</span>';
    if($g["kedy"]!="na programe") 
      {
      if($g["kedy"]=="konečný stav" && $g["goals1"]>$g["goals2"]) $los2=" loser";
      elseif($g["kedy"]=="konečný stav" && $g["goals1"]<$g["goals2"]) $los1=" loser";
      if($g["kedy"]=="konečný stav" && ($g["cas"]=="65.00" || $g["cas"]=="70.00" || $g["cas"]=="80.00")) $ot = LANG_SO;
      elseif($g["kedy"]=="konečný stav" && ($g["cas"]>"60.00")) $ot = LANG_OT;
      $goals = '<div class="row mb-2 align-items-center no-gutters">
                    <div class="col-6 text-center h1 h1-fluid font-weight-bold">'.$g["goals1"].'</div>
                    '.($ot!="" ? '<div class="position-absolute text-center" style="left:0;right:0;"><span class="badge badge-secondary">'.$ot.'</span></div>':'').'
                    <div class="col-6 text-center h1 h1-fluid font-weight-bold">'.$g["goals2"].'</div>
                </div>';
      if($g["kedy"]=="konečný stav") $opt .= '<a href="/report/'.$g["id"].$f["el"].'-'.SEOtitle($g["team1long"]." vs ".$g["team2long"]).'" class="btn btn-sm btn-light btn-icon-split">
                                              <span class="icon text-gray-600">
                                                <i class="fas fa-ellipsis-h"></i>
                                              </span>
                                              <span class="text text-gray-800">'.LANG_TEAMSTATS_INDETAIL.'</span>
                                            </a>';
      else 
        {
        // prebieha, ale nie je live
        $opt = '<a href="/report/'.$g["id"].$f["el"].'-'.SEOtitle($g["team1long"]." vs ".$g["team2long"]).'" class="btn btn-sm btn-light btn-icon-split live">
                  <span class="icon text-gray-600">
                    <i class="fas fa-comments"></i>
                  </span>
                  <span class="text text-gray-800">'.LANG_NAV_LIVE.'</span>
                </a>';
        $bckg = " bg-gradient-light";
        $kedy = '<div class="row mb-2 no-gutters align-items-center"><div class="col-12 text-center font-weight-bold text-gray-900">'.$g["kedy"].'</div></div>';
        }
      }
    if($g["active"]==1) 
      {
      // je live
      $opt = '<a href="/report/'.$g["id"].$f["el"].'-'.SEOtitle($g["team1long"]." vs ".$g["team2long"]).'" class="btn btn-sm btn-light btn-icon-split live">
                <span class="icon text-gray-600">
                  <i class="fas fa-comments"></i>
                </span>
                <span class="text text-gray-800">'.LANG_NAV_LIVE.'</span>
              </a>';
      $bckg = " bg-gradient-light";
      $kedy = '<div class="row mb-2 no-gutters align-items-center"><div class="col-12 text-center font-weight-bold text-gray-900">'.$g["kedy"].'</div></div>';
      }
    if($f["el"]==0) $suffix = " shadow-sm";
    if(strtotime($g["datetime"])>mktime(7,0,0) && strtotime($g["datetime"])<mktime(7,0,0,date("n"),date("j")+1,date("Y")) && $g["active"]==0) $bckg .= " border-".$leaguecolor;
    $matches .= $scroll.'
                <div class="col-12 col-sm-6 col-lg-4 col-xl-3 animated--grow-in mb-3">
                  <div class="card shadow h-100'.$bckg.'">
                    <div class="card-header row no-gutters">
                      <div class="col text-xs font-weight-bold">
                        '.date("j.n.Y", strtotime($g["datetime"])).'
                      </div>
                      <div class="col text-xs font-weight-bold text-right">
                        '.date("G:i", strtotime($g["datetime"])).'
                      </div>
                    </div>
                    <div class="card-body d-flex flex-column p-0">
                      <div class="row no-gutters my-3">
                        <div class="col-6 text-center'.$los1.'">
                          <img src="/images/vlajky/'.$g["team1short"].'.gif" alt="'.$g["team1long"].'" class="img-fluid'.$suffix.'">
                          <div class="h6 h6-fluid mb-0 mt-1 font-weight-bold text-gray-800">'.$g["team1long"].'</div>
                        </div>
                        <div class="col-6 text-center'.$los2.'">
                          <img src="/images/vlajky/'.$g["team2short"].'.gif" alt="'.$g["team2long"].'" class="img-fluid'.$suffix.'">
                          <div class="h6 h6-fluid mb-0 mt-1 font-weight-bold text-gray-800">'.$g["team2long"].'</div>
                        </div>
                      </div>
                      '.$tv.'
                      '.$goals.'
                      '.$kedy.'
                      '.$bets.'
                      '.$slov.'
                      <div class="row no-gutters flex-fill align-items-end">
                        <div class="w-100 text-center py-3">'.$opt.'</div>
                      </div>
                    </div>
                  </div>
                 </div>';
    $struct_data[] = array($g["team1long"], $g["team2long"], 'https://www.hockey-live.sk/images/vlajky/'.$g["team1short"].'_big.gif','https://www.hockey-live.sk/images/vlajky/'.$g["team2short"].'_big.gif', 'https://www.hockey-live.sk/game/'.$g["id"].$f["el"].'-'.SEOtitle($g["team1long"]." vs ".$g["team2long"]), $g["datetime"], 'https://www.hockey-live.sk/includes/gotd.php?id='.$g["id"].$f["el"]);
    $pred=$g["kedy"];
    $poc++;
    }

    // strukturovane data pre Google
    if(count($struct_data)>0) {
    $matches .= '<script type="application/ld+json">[';
    foreach($struct_data as $event) {
        $matches .= '
  {
    "@context": "https://schema.org",
    "@type": "SportsEvent",
    "name": "'.$event[0].' vs. '.$event[1].'",
    "url": "'.$event[4].'",
    "image": "'.$event[6].'",
    "description" : "'.LANG_MATCH1.' '.$event[0].' vs. '.$event[1].'",
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
    "location": "'.$f["longname"].'"
  },';
    }
    $matches = substr($matches, 0, -1);
    $matches .= ']
    </script>';
    }

  $matches .= '</div>';
  

  return $matches;
  }
  
/*
* Funkcia pre vygenerovanie moznosti tipovania
* version: 1.0.1 (20.2.2016 - len drobne upravy pre pouzitie s novou verziou stranky)
* @param $sel string - užívateľom vybraná možnosť
* @return $betlist string
*/
  
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
?>