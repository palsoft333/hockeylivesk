<?

/*
* Funkcia pre konverziu akéhokoľvek reťazca na URL v SEO forme
* version: 1.0.0 (23.11.2015 - vytvorenie novej funkcie)
* @param $title string - reťazec na konverziu
* @return $seotitle string
*/

function SEOtitle($title) {
		$znak = array("ď","ľ","š","č","ť","ž","ý","á","í","ĺ","Ĺ","é","ň","ä","ú","ô","ó","ö","ü","Ľ","Š","Č","Ť","Ž","Ý","Á","Í","É","Ě","Ň","Ú","Ó","Ö","Ü", "Ů", "Ř", "Ď", "ř", "ě", "Ä");
		$replacer = array("d","l","s","c","t","z","y","a","i","l","L","e","n","a","u","o","o","o","u","L","S","C","T","Z","Y","A","I","E","E","N","U","O","O","U","U", "R", "D", "r", "e", "A");
    $seotitle = str_replace($znak,$replacer,$title);
    $seotitle = preg_replace("/[^a-zA-Z0-9\s]/","",$seotitle);
    $seotitle = trim($seotitle);
    $seotitle = preg_replace("/\s+/"," ",$seotitle);
    $seotitle = str_replace(" ","-",$seotitle);
    $seotitle = strtolower($seotitle);
    return $seotitle;
}

/*
* Funkcia pre vrátenie farby na základe názvu ligy
* version: 1.0.0 (6.2.2020 - vytvorenie novej funkcie)
* @param $name string - názov ligy
* @return $color string - farba podľa Boostrap konvencie
*/

function LeagueColor($name) {
    $name = SEOtitle($name);
    if(strstr($name, 'nemecky')) $bg = "danger";
    elseif(strstr($name, 'tipsport') || strstr($name, 'extraliga')) $bg = "primary";
    elseif(strstr($name, 'khl')) $bg = "danger";
    elseif(strstr($name, 'nhl')) $bg = "warning";
    elseif(strstr($name, 'ms')) $bg = "success";
    elseif(strstr($name, 'zoh')) $bg = "info";
    elseif(strstr($name, 'kaufland') || strstr($name, 'slovakia') || strstr($name, 'loto')) $bg = "primary";
    elseif(strstr($name, 'challenge') || strstr($name, 'skoda')) $bg = "warning";
    elseif(strstr($name, 'svetovy')) $bg = "info";
    else $bg = "hl";
    return $bg;
}

/*
* Funkcia pre vrátenie font ikony ligy na základe názvu ligy
* version: 1.0.0 (13.2.2020 - vytvorenie novej funkcie)
* @param $name string - názov ligy
* @return $font string - názov ikony vo fonte (napr. ll-*tipsport*)
*/

function LeagueFont($name) {
    if(strstr($name, 'Tipsport') || strstr($name, 'Extraliga')) $font = "tipsport";
    elseif(strstr($name, 'KHL')) $font = "khl";
    elseif(strstr($name, 'NHL')) $font = "nhl";
    elseif(strstr($name, 'MS')) $font = "iihf";
    elseif(strstr($name, 'ZOH')) $font = "olympics";
    elseif(strstr($name, 'Kaufland') || strstr($name, 'Slovakia') || strstr($name, 'Loto')) $font = "kauflandcup";
    elseif(strstr($name, 'Challenge') || strstr($name, 'Škoda')) $font = "arosa";
    else $font = "dcup";
    return $font;
}

/*
* Funkcia pre vygenerovanie menu jednotlivých aktívnych líg
* version: 3.0.0 (29.1.2020 - prispôsobenie pre Boostrap4 template)
* @param $active_league int - ID aktuálne vybranej ligy
* @return $menu string
*/

function Generate_Menu($active_league = FALSE) {
    $q = mysql_query("SELECT dt.*, e_xoops_topics.topic_title FROM e_xoops_topics JOIN (SELECT * FROM 2004leagues WHERE position > '0' && id!='70')dt ON dt.topic_id=e_xoops_topics.topic_id ORDER BY el DESC, position ASC");
    while($f = mysql_fetch_array($q))
      {
      $badge_num=$live=0;
      $badge="";
      if($f[el]==0) $matches_table="2004matches";
      elseif($f[el]==1) $matches_table="el_matches";
      else $matches_table="al_matches";
      $font = LeagueFont($f[longname]);
      $bg = LeagueColor($f[longname]);
      if(strstr($f[longname], 'NHL')) $w = mysql_query("SELECT * FROM $matches_table WHERE datetime > '".date("Y-m-d",mktime())." 10:00' && datetime < '".date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+1, date('Y')))." 10:00' && league='$f[id]'");
      else $w = mysql_query("SELECT * FROM $matches_table WHERE datetime LIKE '".date("Y-m-d",mktime())."%' && league='$f[id]'");
      $badge_num = mysql_num_rows($w);
      if($badge_num>0)
        {
        while($g = mysql_fetch_array($w)) 
          {
          if($g[active]==1) $live=1;
          }
        $badge = '<span class="badge badge-'.$bg.' float-right mr-2 text-xs">'.$badge_num.'</span>';
        if($live==1) $badge .= '<span class="badge live" style="margin-right:2px;color:#FFEB3B;">LIVE</span>';
        }
      if($f[el]!=$elpred) $menu .= '<!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        '.($f[el]==1 ? 'Ligy':'Turnaje').'
      </div>';
      $menu .= '<li class="nav-item'.($active_league==$f[id] ? ' active':'').'" itemscope="itemscope" itemtype="http://www.schema.org/SiteNavigationElement">
                  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse-'.$f[id].'" aria-expanded="true" aria-controls="collapse-'.$f[id].'" role="button">
                    <i class="ll-'.$font.'"></i>
                    <span>'.($f[el]==1 ? substr($f[longname],0,-6):$f[longname]).'</span>
                  </a>
                  <div id="collapse-'.$f[id].'" class="collapse" aria-labelledby="heading-'.$f[id].'" data-parent="#accordionSidebar">
                    <div class="bg-white border-left-'.$bg.' py-2 collapse-inner rounded">
                      <h6 class="collapse-header">'.LANG_NAV_TABLE.':</h6>
                      <div class="text-xs pb-2">
                        '.($f[endbasic]==1 && $f[el]>0 ? Get_Series($f[id]) : Get_team_table($f[id])).'
                      </div>
                      <div class="collapse-divider"></div>
                      <h6 class="collapse-header">Odkazy:</h6>
                      <a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/category/'.$f[topic_id].'-'.SEOtitle($f[topic_title]).'"><span itemprop="name">'.LANG_NAV_NEWS.'</span><i class="fas fa-newspaper fa-fw float-right text-gray-500 my-1"></i></a>
                      '.($f[el]>0 && $f[endbasic]==1 ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/table/'.$f[id].'-'.SEOtitle($f[topic_title]).'/playoff"><span itemprop="name">'.LANG_TEAMTABLE_PLAYOFF.'</span><i class="fas fa-trophy fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      <a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="'.($f[endbasic]==1 && $f[el]==0 ? "/table/".$f[id]."-".SEOtitle($f[topic_title])."/playoff" : "/games/".$f[id]."-".SEOtitle($f[topic_title])).'"><span itemprop="name">'.LANG_NAV_UPCOMMING.'</span><i class="fas fa-hockey-puck fa-fw float-right text-gray-500 my-1"></i>'.$badge.'</a>
                      <a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/table/'.$f[id].'-'.SEOtitle($f[topic_title]).''.($f[groups]!="" ? "/groups" : "").'"><span itemprop="name">'.LANG_NAV_TABLE.'</span><i class="fas fa-list-ol fa-fw float-right text-gray-500 my-1"></i></a>
                      <a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/stats/'.$f[id].'-'.SEOtitle($f[topic_title]).'"><span itemprop="name">'.LANG_NAV_STATS.'</span><i class="fas fa-chart-bar fa-fw float-right text-gray-500 my-1"></i></a>
                      '.(strstr($f[longname], 'NHL') ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/slovaks/'.$f[id].'-'.SEOtitle($f[topic_title]).'"><span itemprop="name">'.LANG_NAV_SLOVAKS.'</span><i class="fas fa-user-shield fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.(strstr($f[longname], 'KHL') ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/slovaks/'.$f[id].'-'.SEOtitle($f[topic_title]).'"><span itemprop="name">'.LANG_NAV_SLOVAKI.'</span><i class="fas fa-user-shield fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.($f[el]>0 && $f[topic_id]!=60 ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/injured/'.$f[id].'-'.SEOtitle($f[topic_title]).'"><span itemprop="name">'.LANG_NAV_INJURED.'</span><i class="fas fa-user-injured fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.($f[el]>0 ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/transfers/'.$f[id].'-'.SEOtitle($f[topic_title]).'"><span itemprop="name">'.LANG_NAV_TRANSFERS.'</span><i class="fas fa-exchange-alt fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.($f[id]==141 ? '<a itemprop="url" class="collapse-item font-weight-bold text-success" href="/fantasy/draft"><span itemprop="name">Fantasy MS</span><i class="fas fa-magic fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.($f[id]==134 ? '<a itemprop="url" class="collapse-item font-weight-bold text-danger" href="/fantasy/main"><span itemprop="name">Fantasy KHL</span><i class="fas fa-magic fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                    </div>
                  </div>
                </li>';
      $elpred=$f[el];
      }
   return $menu;
}

/*
* Tabulka timov v pravom navigacnom menu
* version: 1.5.1 (8.12.2015 - zisťovanie pozície tímu v tabuľke)
* version: 1.5.2 (21.1.2016 - vylepšené počítanie tímov, ktoré sa dostali do playoff)
* version: 2.0.0 (30.1.2020 - prispôsobené pre Boostrap 4 template)
* @param $league integer - ID ligy
* @param $pos string - ak obsahuje teamshort, vrati len poziciu daneho timu v tabulke
* @return $ttable string
*/

function Get_team_table($league, $pos = FALSE) {
Global $another;

$ttable="";

$a = mysql_query("SELECT * FROM 2004leagues WHERE id='$league'");
$b = mysql_fetch_array($a);
$wpoints=$b[points];
if($b[el]==1) 
	{
	$teams_table="el_teams";
	$el=1;
	if(strstr($b[longname], 'KHL'))
    {
    $kol[kolo]=56;
    $dev = mysql_query("SELECT ($kol[kolo]-dt.zapasov)*$wpoints+body as ce FROM (SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' && (shortname='SPA' || shortname='VIT' || shortname='SOC' || shortname='JOK' || shortname='PET' || shortname='TNN' || shortname='DYN' || shortname='DMN' || shortname='LOK' || shortname='DIR' || shortname='CSK' || shortname='SEV') ORDER BY body desc, wins desc, diff DESC, zapasov asc LIMIT 8,8)dt ORDER BY ce DESC LIMIT 1");
	$deviaty = mysql_fetch_array($dev);
    $osm = mysql_query("SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' && (shortname='SPA' || shortname='VIT' || shortname='SOC' || shortname='JOK' || shortname='PET' || shortname='TNN' || shortname='DYN' || shortname='DMN' || shortname='LOK' || shortname='DIR' || shortname='CSK' || shortname='SEV') ORDER BY body desc, wins desc, diff DESC, zapasov asc LIMIT 7,1");
	$osmy = mysql_fetch_array($osm);
    }
	elseif(strstr($b[longname], 'NHL'))
    {
		$kol[kolo]=82;
		$wpoints=$b[points];
    if($another)
      {
      $dev = mysql_query("SELECT ($kol[kolo]-dt.zapasov)*$wpoints+body as ce FROM (SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' && (shortname='CHI' || shortname='WPG' || shortname='NSH' || shortname='STL' || shortname='CGY' || shortname='COL' || shortname='EDM' || shortname='MIN' || shortname='VAN' || shortname='ANA' || shortname='DAL' || shortname='LAK' || shortname='ARI' || shortname='SJS' || shortname='VGK') ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 8,8)dt ORDER BY ce DESC LIMIT 1");
      $deviaty = mysql_fetch_array($dev);
      $osm = mysql_query("SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' && (shortname='CHI' || shortname='WPG' || shortname='NSH' || shortname='STL' || shortname='CGY' || shortname='COL' || shortname='EDM' || shortname='MIN' || shortname='*VAN' || shortname='ANA' || shortname='DAL' || shortname='LAK' || shortname='ARI' || shortname='SJS' || shortname='VGK') ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 7,1");
      $osmy = mysql_fetch_array($osm);
      }
    else
      {
      $dev = mysql_query("SELECT ($kol[kolo]-dt.zapasov)*$wpoints+body as ce FROM (SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' && (shortname='NJD' || shortname='NYI' || shortname='NYR' || shortname='PHI' || shortname='PIT' || shortname='BOS' || shortname='BUF' || shortname='MTL' || shortname='OTT' || shortname='TOR' || shortname='WPG' || shortname='CAR' || shortname='FLA' || shortname='TBL' || shortname='WSH') ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 8,8)dt ORDER BY ce DESC LIMIT 1");
      $deviaty = mysql_fetch_array($dev);
      $osm = mysql_query("SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' && (shortname='NJD' || shortname='NYI' || shortname='NYR' || shortname='PHI' || shortname='PIT' || shortname='BOS' || shortname='BUF' || shortname='MTL' || shortname='OTT' || shortname='TOR' || shortname='WPG' || shortname='CAR' || shortname='FLA' || shortname='TBL' || shortname='WSH') ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 7,1");
      $osmy = mysql_fetch_array($osm);
      }
    }
  else
    {
    $kol[kolo]=50;
    /*$dev = mysql_query("SELECT ($kol[kolo]-dt.zapasov)*$wpoints+body as ce FROM (SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' && shortname!='S20' ORDER BY body desc, diff desc, goals desc, wins desc, losts asc, ties desc LIMIT 8,8)dt ORDER BY ce DESC LIMIT 1");
    $deviaty = mysql_fetch_array($dev);
    $osm = mysql_query("SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' ORDER BY body desc, diff desc, goals desc, wins desc, losts asc, ties desc LIMIT 7,1");
    $osmy = mysql_fetch_array($osm);*/
    $dev = mysql_query("SELECT ($kol[kolo]-dt.zapasov)*$wpoints+body as ce FROM (SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' && shortname!='S20' ORDER BY body desc, diff desc, goals desc, wins desc, losts asc, ties desc LIMIT 6,6)dt ORDER BY ce DESC LIMIT 1");
    $deviaty = mysql_fetch_array($dev);
    $osm = mysql_query("SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' ORDER BY body desc, diff desc, goals desc, wins desc, losts asc, ties desc LIMIT 5,1");
    $osmy = mysql_fetch_array($osm);
    $des = mysql_query("SELECT *, goals-ga as diff FROM el_teams WHERE league='$league' ORDER BY body desc, diff desc, goals desc, wins desc, losts asc, ties desc LIMIT 9,1");
    $desiaty = mysql_fetch_array($des);
    }
	}
else { $teams_table="2004teams"; $el="0"; }
if($b[el]==1 && strstr($b[longname], 'KHL'))
  {
  // Tabulka KHL zapadnej konferencie
  $b[longname] = "KHL";
	$uloha = mysql_query("(SELECT dt.* FROM ((SELECT *, goals-ga as diff, 1 as leader FROM el_teams WHERE league='$league' && (shortname='SPA' || shortname='VIT' || shortname='SOC' || shortname='JOK' || shortname='PET' || shortname='TNN') ORDER BY body desc, wins desc, diff DESC, zapasov asc, id asc LIMIT 1)
UNION
(SELECT *, goals-ga as diff, 1 as leader FROM el_teams WHERE league='$league' && (shortname='DYN' || shortname='DMN' || shortname='LOK' || shortname='DIR' || shortname='CSK' || shortname='SEV')  ORDER BY body desc, wins desc, diff DESC, zapasov asc, id asc LIMIT 1))dt)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM el_teams WHERE league='$league' && (shortname='SPA' || shortname='VIT' || shortname='SOC' || shortname='JOK' || shortname='PET' || shortname='TNN') ORDER BY body desc, wins desc, diff DESC, zapasov asc, id asc LIMIT 1,6)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM el_teams WHERE league='$league' && (shortname='DYN' || shortname='DMN' || shortname='LOK' || shortname='DIR' || shortname='CSK' || shortname='SEV')  ORDER BY body desc, wins desc, diff DESC, zapasov asc, id asc LIMIT 1,6)
ORDER BY leader asc, body desc, wins desc, diff desc, zapasov asc");
  }
elseif($b[el]==1 && strstr($b[longname], 'NHL'))
  {
  $b[longname] = "NHL";
  // Tabulky konferencii NHL
	if($another)
    {
    $uloha = mysql_query("(SELECT dt.* FROM ((SELECT *, goals-ga as diff, 1 as leader FROM el_teams WHERE league='$league' && (shortname='STL' || shortname='COL' || shortname='CHI' || shortname='MIN' || shortname='DAL' || shortname='WPG' || shortname='NSH' || shortname='ARI') ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 3)
UNION
(SELECT *, goals-ga as diff, 1 as leader FROM el_teams WHERE league='$league' && (shortname='ANA' || shortname='SJS' || shortname='LAK' || shortname='SEA' || shortname='VAN' || shortname='CGY' || shortname='EDM' || shortname='VGK')  ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 3))dt)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM el_teams WHERE league='$league' && (shortname='STL' || shortname='COL' || shortname='CHI' || shortname='MIN' || shortname='DAL' || shortname='WPG' || shortname='NSH') ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 3,6)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM el_teams WHERE league='$league' && (shortname='ANA' || shortname='SJS' || shortname='LAK' || shortname='ARI' || shortname='VAN' || shortname='CGY' || shortname='EDM' || shortname='VGK')  ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 3,6)
ORDER BY leader asc, body desc, zapasov asc, wins desc, diff desc");
    }
  else
    {
    $uloha = mysql_query("(SELECT dt.* FROM ((SELECT *, goals-ga as diff, 1 as leader FROM el_teams WHERE league='$league' && (shortname='BOS' || shortname='MTL' || shortname='TBL' || shortname='DET' || shortname='TOR' || shortname='OTT' || shortname='FLA' || shortname='BUF') ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 3)
UNION
(SELECT *, goals-ga as diff, 1 as leader FROM el_teams WHERE league='$league' && (shortname='PIT' || shortname='NYR' || shortname='PHI' || shortname='CBJ' || shortname='WSH' || shortname='NJD' || shortname='CAR' || shortname='NYI')  ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 3))dt)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM el_teams WHERE league='$league' && (shortname='BOS' || shortname='MTL' || shortname='TBL' || shortname='DET' || shortname='TOR' || shortname='OTT' || shortname='FLA' || shortname='BUF') ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 3,7)
UNION
(SELECT *, goals-ga as diff, 2 as leader FROM el_teams WHERE league='$league' && (shortname='PIT' || shortname='NYR' || shortname='PHI' || shortname='CBJ' || shortname='WSH' || shortname='NJD' || shortname='CAR' || shortname='NYI')  ORDER BY body desc, zapasov asc, wins desc, diff desc LIMIT 3,7)
ORDER BY leader asc, body desc, zapasov asc, wins desc, diff desc");
    }
  }
else
  {
  // Tabulka extraligy
  if(strstr($b[longname], 'Tipos')) $b[longname] = "Tipos Extraliga";
  if($b[el]==1 && $b[endbasic]==1) $uloha = mysql_query("SELECT *, gf_basic-ga_basic as diff FROM $teams_table WHERE league='$league' && skupina!=1 ORDER BY p_basic desc, diff desc, gf_basic desc, w_basic desc, l_basic asc, t_basic desc");
  else $uloha = mysql_query("(SELECT *, goals-ga as diff, 0 as hore FROM $teams_table WHERE league='$league' && shortname!='S20')
  UNION
  (SELECT *, goals-ga as diff, 1 as hore FROM $teams_table WHERE league='$league' && shortname='S20') ORDER BY hore asc, body desc, diff desc, zapasov asc, goals desc, wins desc, losts asc, ties desc");
  }

$m = mysql_query("SELECT * FROM 2004teams WHERE final_pos!='' && league='$league'");
if($b[groups]=="" || $b[groups]!="" && $b[endbasic]==1 && $b[osem]==0 && mysql_num_rows($m)==0)
	{
	$ttable .= "<table class='w-100 table-striped table-hover'>
    <thead>
     <tr> 
          <td scope='col' class='text-center'>#</td>
          <td scope='col'><b>".LANG_PLAYERSTATS_TEAM."</b></td>
          <td scope='col' class='text-center'><b>".LANG_TEAMSTATS_POINTS."</b></td>
     </tr>
    </thead>
    <tbody>";
$i=0;
$p=1;
while ($i < mysql_num_rows($uloha))
		{
	$clinch=$line="";
	$data = mysql_fetch_array($uloha);
	//$data[mediumname] = iconv("windows-1250","utf-8",$data[mediumname]);
	//$data[mediumname] = urldecode($data[mediumname]);
	if($b[el]==1) $shortname = $data[mediumname];
	else $shortname = $data[longname];
	if($_SESSION[lang] != 'sk') $shortname = TeamParser($shortname);
	$points = $data[body];
	if($b[el]==1 && $b[endbasic]==1) $points = $data[p_basic];
  // play-off line
  if(strstr($b[longname], 'Tipos')) {
    if($i==5 || $i==9) $line=" style='border-bottom:1px dashed black !important;'";
    }
  elseif($i==7) $line=" style='border-bottom:1px dashed black !important;'";
	// clinched playoff
	//$kol[kolo]=55;
	if($b[el]==1 && $points > $deviaty[ce]) { $clinch = "<span class='text-success'>*</span>"; $clinchwas=1; }
	// cannot make playoff's
	if($b[el]==1 && strstr($b[longname], 'Tipos') && (($kol[kolo]-$data[zapasov])*$wpoints)+$points < $desiaty[body] || $data[shortname]=="S20") { $clinch = "<sup><span class='text-danger font-weight-bold'>x</span></sup>"; $cannotwas=1; }
	if($b[el]==1 && !strstr($b[longname], 'Tipos') && (($kol[kolo]-$data[zapasov])*$wpoints)+$points < $osmy[body] || $data[shortname]=="S20") { $clinch = "<sup><span class='text-danger font-weight-bold'>x</span></sup>"; $cannotwas=1; }
	$ttable .= "<tr><td class='text-center'$line'>$p.</td><td$line><a href='/team/$data[id]$el-".SEOtitle($data[longname])."'><img class='flag-".($b[el]==1 ? 'el':'iihf')." ".$data[shortname]."-small mr-1' src='/images/blank.png' alt='".$shortname."'>$shortname</a>$clinch</td><td class='text-center'$line'><b>$points</b></td></tr>";
	if($pos==$data[shortname] && $b[el]==1) { $a = array($p,$kol[kolo],$wpoints); return $a; }
	if($pos==$data[shortname] && $b[el]==0) { return $p; }
$i++;
$p++;
		}
  $ttable .= '</tbody>
  <tfoot class="small">';
	if($b[el]==1 && $clinchwas==1) $ttable .= "<tr><td colspan='3' class='pl-2'><span class='text-success'>*</span> - ".LANG_TEAMTABLE_CLINCHEDSHORT."</td></tr>";
	if($b[el]==1 && $cannotwas==1) $ttable .= "<tr><td colspan='3' class='pl-2'><span class='text-danger'>x</span> - ".LANG_TEAMTABLE_CANNOTMAKEPOSHORT."</td></tr>";
	if($b[el]==1 && (strstr($b[longname], 'KHL') || strstr($b[longname], 'NHL'))) $ttable .= "<tr><td colspan='3' class='text-right pr-2'><a href='/table/$league-".SEOtitle($b[longname])."'>".LANG_TEAMTABLE_FULLTABLE." &raquo;</a></td></tr>";
	$ttable .= "</tfoot>
	</table>";
  if($b[el]==1 && strstr($b[longname], 'NHL') && !$another)
    {
    $another=1;
    Get_team_table($league);
    }
	}
else
	{
	if($b[osem]==1)
		{
		// SKUPINY OSEMFINALE
		//$q = mysql_query("SELECT skup_osem FROM 2004teams WHERE league='$league' GROUP BY skup_osem ORDER BY skup_osem ASC");
		$q = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
		$q = mysql_query("SELECT skup_osem FROM 2004teams WHERE league='$league' && skup_osem!='G' GROUP BY skup_osem ORDER BY skup_osem ASC");
	$j=0;
	while ($j <= mysql_num_rows($q)-1)
			{
		$f = mysql_fetch_array($q);
	$ttable .= "<table class='w-100 table-striped table-hover'>
    <thead>
     <tr> 
          <td colspan='3' class='pl-2'><b>".LANG_TEAMTABLE_GROUP." $f[skup_osem]</b></td>
     </tr>
     <tr> 
          <td scope='col' class='text-center'>#</td>
          <td scope='col'><b>".LANG_PLAYERSTATS_TEAM."</b></td>
          <td scope='col' class='text-center'><b>".LANG_TEAMSTATS_POINTS."</b></td>
     </tr>
    </thead>
    <tbody>";
$coto = mysql_query("SELECT *, gf_osem-ga_osem as diff FROM $teams_table WHERE league='$league' && skup_osem='$f[skup_osem]' ORDER BY p_osem desc, diff desc, gf_osem, w_osem desc, l_osem asc, t_osem desc");
if($b[el]!=1)
{
$m=0;
while($data = mysql_fetch_array($coto))
	{
	$posun=0;
	if($points==$data[p_osem])
		{
		$vzaj = mysql_query("SELECT IF(team1short='$data[shortname]',IF(goals1>goals2,1,0),IF(goals1>goals2,0,1)) as posun FROM 2004matches WHERE (team1short='$data[shortname]' && team2short='$tshort' || team1short='$tshort' && team2short='$data[shortname]') && league='$league' && kedy='konečný stav'");
		$vzajom = mysql_fetch_array($vzaj);
		if($vzajom[posun]==1)
			{
			$mm = $m-1;
			$tabul[$m] = $tabul[$mm];
			$m--;
			$posun=1;
			}
		}
	$points = $data[p_osem];
	$tshort = $data[shortname];
	if($b[el]==1) $data[longname] = $data[mediumname];
	else $shortname = $data[longname];
	$tabul[$m] = array($data[id], $data[shortname], $shortname, $data[p_osem]);
	$m++;
	if($posun==1) $m++;
	}
$i=0;
$p=1;
while ($i < count($tabul))
	{
	if($_SESSION[lang] != 'sk') $tabul[$i][2] = TeamParser($tabul[$i][2]);
	$ttable .= "<tr><td class='text-center'>$p.</td><td><a href='/team/".$tabul[$i][0]."$el-".SEOtitle($tabul[$i][2])."'><img class='flag-iihf ".$tabul[$i][1]."-small mr-1' src='/images/blank.png' alt='".$tabul[$i][2]."'>".$tabul[$i][2]."</a></td><td class='text-center'><b>".$tabul[$i][3]."</b></td></tr>";
	if($pos==$tabul[$i][1]) return $p;
	$i++;
	$p++;
	}
}
else
{
$i=0;
$p=1;
while ($i <= mysql_num_rows($coto)-1)
		{
	$data = mysql_fetch_array($coto);
	if($pos==$data[shortname]) return $p;
	if($b[el]==1) $data[shortname] = $data[mediumname];
	else $shortname = $data[longname];
	if($_SESSION[lang] != 'sk') $data[longname] = TeamParser($data[longname]);
	$ttable .= "<tr><td class='text-center'>$p.</td><td><a href='/team/$data[id]$el-".SEOtitle($data[longname])."'>$shortname</a></td><td class='text-center'><b>$data[p_osem]</b></td></tr>";
$i++;
$p++;
		}
}
		$ttable .= "</tbody>
		</table>";
		$j++;
			}
		}
	else
		{
		// SKUPINOVY SYSTEM, MIMO OSEMFINALE
		if($b[endbasic]==1 && mysql_num_rows($m)>0)
			{
			// SKONCILI SKUPINY A ZOBRAZI SA FINALNE UMIESTNENIE
			$n = mysql_query("SELECT * FROM 2004teams WHERE league='$b[id]' ORDER BY final_pos ASC");
			$ttable .= "<table class='w-100 table-striped table-hover'>
			<thead>
        <tr> 
          <td colspan='3' class='pl-2'><b>".LANG_TEAMTABLE_FINALPOS."</b></td>
        </tr>
         <tr> 
              <td scope='col' class='text-center'>#</td>
              <td scope='col'><b>".LANG_PLAYERSTATS_TEAM."</b></td>
         </tr>
      </thead>
      <tbody>";
			$c=1;
			while($h = mysql_fetch_array($n))
				{
				$line="";
				if($c==1) $ci = "<img src='https://www.hockey-live.sk/img/medal_gold_3.png' class='align-top' alt='".LANG_TEAMTABLE_GOLD."' title='".LANG_TEAMTABLE_GOLD."'>";
				elseif($c==2) $ci = "<img src='https://www.hockey-live.sk/img/medal_silver_3.png' class='align-top' alt='".LANG_TEAMTABLE_SILVER."' title='".LANG_TEAMTABLE_SILVER."'>";
				elseif($c==3) $ci = "<img src='https://www.hockey-live.sk/img/medal_bronze_3.png' class='align-top' alt='".LANG_TEAMTABLE_BRONZE."' title='".LANG_TEAMTABLE_BRONZE."'>";
        else $ci = "$c.";
        if($c==14) $line=" style='border-bottom:1px dashed black !important;'";
				if($h[final_pos]) 
					{
					if($_SESSION[lang] != 'sk') $h[longname] = TeamParser($h[longname]);
					$ttable .= "<tr><td class='text-center'$line>$ci</td><td$line><a href='/team/$h[id]$el-".SEOtitle($h[longname])."'>$h[longname]</a></td></tr>";
					}
				else
					{
					$ttable .= "<tr><td class='text-center'$line>$ci</td><td$line>&nbsp;</td></tr>";
					}
				if($pos==$h[shortname]) return $c;
				$c++;
				}
			$ttable .= "</tbody>
			</table>";
			}
		else
			{
			// PREBIEHAJU ZAKLADNE SKUPINY
			$crop = explode("|", $b[groups]);
			$j=0;
			while ($j <= count($crop)-1)
				{
				$skup = $crop[$j];
				$ttable .= "<table class='w-100 table-striped table-hover'>
				<thead>
          <tr> 
            <td colspan='3' class='pl-2'><b>".LANG_TEAMTABLE_GROUP." $skup</b></td>
          </tr>
           <tr> 
                <td scope='col' class='text-center'>#</td>
                <td scope='col'><b>".LANG_PLAYERSTATS_TEAM."</b></td>
                <td scope='col' class='text-center'><b>".LANG_TEAMSTATS_POINTS."</b></td>
           </tr>
        </thead>
        <tbody>";
				if($b[endbasic]==1) $coto = mysql_query("SELECT *, gf_basic-ga_basic as diff FROM $teams_table WHERE league='$league' && skupina='$skup' ORDER BY p_basic desc, diff desc, w_basic desc, l_basic asc, t_basic desc");
				else $coto = mysql_query("SELECT *, goals-ga as diff FROM $teams_table WHERE league='$league' && skupina='$skup' ORDER BY body desc, zapasov asc, diff desc, wins desc, losts asc, ties desc");
				
				// BODY >> VZAJOMNY ZAPAS >> ROZDIEL SKORE (START)
        $m=0;
        while($data = mysql_fetch_array($coto))
          {
          $posun=0;
          if($points==$data[body])
            {
            $vzaj = mysql_query("SELECT IF(team1short='$data[shortname]',IF(goals1>goals2,1,0),IF(goals1>goals2,0,1)) as posun FROM 2004matches WHERE (team1short='$data[shortname]' && team2short='$tshort' || team1short='$tshort' && team2short='$data[shortname]') && league='$league' && kedy='konečný stav'");
            $vzajom = mysql_fetch_array($vzaj);
            if($vzajom[posun]==1)
              {
              $mm = $m-1;
              $tabul[$m] = $tabul[$mm];
              $m--;
              $posun=1;
              }
            }
          $points = $data[body];
          $tshort = $data[shortname];
          if($b[el]==1) $data[longname] = $data[mediumname];
          else $shortname = $data[longname];
          $tabul[$m] = array($data[id], $data[shortname], $shortname, $data[body]);
          $m++;
          if($posun==1) $m++;
          }
        $i=0;
        $p=1;
        while ($i < count($tabul))
          {
          $line="";
          if(strstr($b[longname], 'Svetový pohár'))
            {
            if($i==1) $line=" style='border-bottom:1px dashed black !important;'";
            }
          elseif(strstr($b[longname], 'ZOH'))
            {
            if($i==0) $line=" style='border-bottom:1px dashed black !important;'";
            }
          //elseif($i==3 || $i==6) $line=" style='border-bottom:1px dashed black !important;'";
          elseif($i==3) $line=" style='border-bottom:1px dashed black !important;'";
          if($_SESSION[lang] != 'sk') $tabul[$i][2] = TeamParser($tabul[$i][2]);
          $ttable .= "<tr><td class='text-center'$line>$p.</td><td$line><a href='/team/".$tabul[$i][0]."$el-".SEOtitle($tabul[$i][2])."'><img class='flag-iihf ".$tabul[$i][1]."-small mr-1' src='/images/blank.png' alt='".$tabul[$i][2]."'>".$tabul[$i][2]."</a></td><td class='text-center'$line><b>".$tabul[$i][3]."</b></td></tr>";
          if($pos==$tabul[$i][1]) return $p;
          $i++;
          $p++;
          }
        // BODY >> VZAJOMNY ZAPAS >> ROZDIEL SKORE (END)
         
        // BODY >> ROZDIEL SKORE (START)
        /*
				$i=0;
				$p=1;
				while ($i <= mysql_num_rows($coto)-1)
					{
					$data = mysql_fetch_array($coto);
					if($i % 2 == 0) $tableclass = 'light';
					else $tableclass = 'dark';
					if($b[el]==1) $data[shortname] = $data[mediumname];
					else $shortname = $data[longname];
					if($b[endbasic]==1) $points = $data[p_basic];
					else $points = $data[body];
					$ttable .= "<tr class='tabulka-$tableclass'><td style='text-align:center;'>$p.</td><td><img src='images/vlajky/$data[shortname]_small.gif' border=0 align=\"absmiddle\"> <a href='index.php?page=teams&action=show&tid=$data[id]$el' target='_top'>$shortname</a></td><td style='text-align:center;'><b>$points</b></td></tr>";
					$i++;
					$p++;
					}
        */
				// BODY >> ROZDIEL SKORE (END)       	
				
				$ttable .= "</tbody>
				</table>";
				$j++;
				}
			}
		}
	}
$ttable = str_replace(">Slovensko<", "><span class='font-weight-bold'>Slovensko</span><", $ttable);
$ttable = str_replace(">Slovensko U20<", "><span class='font-weight-bold'>Slovensko U20</span><", $ttable);
//$ttable = str_replace(">Slovan<", "><span class='font-weight-bold'>Slovan</span><", $ttable);
$ttable = str_replace(">Európa<", "><span class='font-weight-bold'>Európa</span><", $ttable);
return $ttable;
}

/*
* Vypisanie playoff serii danej ligy
* version: 1.1.5 (31.3.2016 - zistovanie nazvu ligy pre spravne vypisanie Stanley, Gagarin)
* @param $potype string - o aku cast PO sa jedna (stvrt, semi, final, stanley)
* @param $lid integer - ID ligy
* @return $series string
*/

function Get_Series($lid)
  {
  $series="";
  $q = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
	$q = mysql_query("SELECT potype, 2004leagues.longname FROM el_playoff JOIN 2004leagues ON 2004leagues.id=el_playoff.league WHERE league='$lid' && played='0' GROUP BY potype LIMIT 1");
	if(mysql_num_rows($q)==0) {
	$q = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
	$q = mysql_query("SELECT potype, 2004leagues.longname FROM el_playoff JOIN 2004leagues ON 2004leagues.id=el_playoff.league WHERE league='$lid' && played='1' GROUP BY potype ORDER BY el_playoff.id DESC LIMIT 1");
	}
	$f = mysql_fetch_array($q);
	$w = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
	$w = mysql_query("SELECT el_playoff.*, t1.longname as t1, t2.longname as t2, t1.id as t1id, t2.id as t2id FROM el_playoff JOIN el_teams t1 ON (el_playoff.team1=t1.shortname && el_playoff.league=t1.league) JOIN el_teams t2 ON (el_playoff.team2=t2.shortname && el_playoff.league=t2.league) WHERE el_playoff.league='$lid' && el_playoff.potype='$f[potype]' GROUP BY id ORDER BY el_playoff.id ASC");
  if($f[potype]=="baraz") $hl=LANG_TEAMTABLE_PLAYOFF." - ".LANG_QUALIFYROUND;
  elseif($f[potype]=="stvrt") $hl=LANG_TEAMTABLE_PLAYOFF." - ".LANG_QUARTERFINAL;
  elseif($f[potype]=="semi") $hl=LANG_TEAMTABLE_PLAYOFF." - ".LANG_SEMIFINAL;
  elseif($f[potype]=="final") $hl=LANG_TEAMTABLE_PLAYOFF." - ".LANG_FINAL;
  elseif($f[potype]=="stanley")
    {
    if(strstr($f[longname], 'NHL')) $hl=LANG_STANLEYFINALS;
    if(strstr($f[longname], 'KHL')) $hl=LANG_GAGARINFINALS;
    }
	$series .= "<table class='w-100 table-striped table-hover'>
                <thead>
                  <tr>
                    <td colspan='3' class='text-center font-weight-bold'>$hl</td>
                  </tr>
                </thead>
                <tbody>";
	$i=0;
	$k=1;
	while ($e = mysql_fetch_array($w))
		{
		$add1=$add2=$line="";
		if($e[status1]==4 || ($e[status1]==3 && $f[potype]=="baraz")) $add1 = " class='font-weight-bold'";
		if($e[status2]==4 || ($e[status2]==3 && $f[potype]=="baraz")) $add2 = " class='font-weight-bold'";
		
		if(strstr($f[longname], 'HL') && $f[potype]=="stvrt" && $i==3 || strstr($f[longname], 'HL') && $f[potype]=="semi" && $i==1 || strstr($f[longname], 'HL') && $f[potype]=="final" && $i==0) $line=" style='border-bottom:1px dashed black !important;'";
		
		$series .= "<tr>
                  <td$line><a href='/team/".$e[t1id]."1-".SEOTitle($e[t1])."'$add1><img class='flag-el ".$e[team1]."-small' src='/images/blank.png' alt='".$e[t1]."'> $e[team1]</a></td>
                  <td class='font-weight-bold text-center'$line>$e[status1]:$e[status2]</td>
                  <td class='text-right'$line><a href='/team/".$e[t2id]."1-".SEOTitle($e[t2])."'$add2>$e[team2] <img class='flag-el ".$e[team2]."-small' src='/images/blank.png' alt='".$e[t2]."'></a></td>
                </tr>";
		$i++;
		$k++;
		}
  $series .= "</tbody></table>";
return $series;
}

function ShowComment($item, $from_latest=false)
  {
  $nick = $item['main'][1];
  if($item['main'][3]==0 && $item['main'][4]!=NULL) $item['main'][1] = $item['main'][4];
  if($item['main'][3]==0 && $item['main'][4]==NULL) $item['main'][1] = "neregistrovaný";
  $comm = $item['main'][6];
  $comm = str_replace(':hladkamsa:','<img src="/images/smilies/boast.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':agresivny:','<img src="/images/smilies/aggressive.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':tlieskam:','<img src="/images/smilies/clapping.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':auu:','<img src="/images/smilies/vava.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':beee:','<img src="/images/smilies/beee.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':nono:','<img src="/images/smilies/nea.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':ee:','<img src="/images/smilies/nea.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':stop:','<img src="/images/smilies/stop.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':pocuvam:','<img src="/images/smilies/music2.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':bomba:','<img src="/images/smilies/bomb.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':pifko:','<img src="/images/smilies/drinks.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':king:','<img src="/images/smilies/king.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':ok:','<img src="/images/smilies/ok.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':plazujem:','<img src="/images/smilies/beach.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':placem:','<img src="/images/smilies/cray.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':tancujem:','<img src="/images/smilies/dance4.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':rofl:','<img src="/images/smilies/ROFL.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':cmuk:','<img src="/images/smilies/kiss3.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':lenivy:','<img src="/images/smilies/lazy.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':sok:','<img src="/images/smilies/shok.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':anjel:','<img src="/images/smilies/angel.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':ciga:','<img src="/images/smilies/smoke.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':hladam:','<img src="/images/smilies/search.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':jo:','<img src="/images/smilies/yes3.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':mojesrdce:','<img src="/images/smilies/give_heart2.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':dik:','<img src="/images/smilies/thank_you2.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':sisi:','<img src="/images/smilies/wacko.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':lol:','<img src="/images/smilies/lol.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':sorry:','<img src="/images/smilies/sorry.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':toto:','<img src="/images/smilies/this.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':bodycheck:','<img src="/images/smilies/bodycheck.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':modry:','<img src="/images/smilies/modry.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':cerveny:','<img src="/images/smilies/cerveny.gif" class="align-text-bottom">',$comm);
  $comm = str_replace(':)','<img src="/images/smilies/smiley.png">',$comm);
  $comm = str_replace(':-)','<img src="/images/smilies/smiley.png">',$comm);
  $comm = str_replace(':D','<img src="/images/smilies/smiley-lol.png">',$comm);
  $comm = str_replace(':-D','<img src="/images/smilies/smiley-lol.png">',$comm);
  $comm = str_replace(';)','<img src="/images/smilies/smiley-wink.png">',$comm);
  $comm = str_replace(';-)','<img src="/images/smilies/smiley-wink.png">',$comm);
    $comment .= '<div class="card message bg-light mb-2"'.($item['main'][3]==$_SESSION[logged] ? ' style="background-color: #f5f5f5;"':'').'>
            <div class="card-body p-2">
                <div class="media d-block d-sm-flex">';
    if($item['main'][3]==0) $comment .= '    
                <div class="align-self-start mr-3 text-center float-left float-sm-none">
                    <img class="img-thumbnail rounded-circle align-self-start" src="'.($item['main'][5]!='' ? '/images/user_avatars/'.$item['main'][3].'.'.$item['main'][5].'?'.filemtime('images/user_avatars/'.$item['main'][3].'.'.$item['main'][5]) : '/img/players/no_photo.jpg').'" style="width: 50px; height: 50px;">
                    <div class="text-muted text-center message-author"><small>'.$item['main'][1].'</small></div>
                </div>';
    else $comment .= '    
                <a href="/user/'.$item['main'][3].'-'.SEOtitle($nick).'" class="align-self-start mr-3 text-center float-left float-sm-none">
                    <img class="img-thumbnail rounded-circle align-self-start" src="'.($item['main'][5]!='' ? '/images/user_avatars/'.$item['main'][3].'.'.$item['main'][5].'?'.filemtime('images/user_avatars/'.$item['main'][3].'.'.$item['main'][5]) : '/img/players/no_photo.jpg').'" style="width: 50px; height: 50px;">
                    <div class="text-muted text-center message-author"><small>'.$item['main'][1].'</small></div>
                </a>';
    $comment .='
                <div class="media-body">
                    <div class="text-right small">';
                    if($from_latest)
                      {
                      if($item['main'][9]==0)
                        {
                        $q = mysql_query("SELECT * FROM e_xoops_stories WHERE storyid='".$item['main'][10]."'");
                        $f = mysql_fetch_array($q);
                        $hl = LANG_USERPROFILE_TOTHESTORY;
                        $hl1 = $f[title];
                        $url = "/news/".$item['main'][10]."-".SEOtitle($f[title]);
                        }
                      elseif($item['main'][9]==1)
                        {
                        $el = substr($item['main'][10], -1);
                        $ide = substr($item['main'][10], 0, -1);
                        if($el==1) $q = mysql_query("SELECT * FROM el_teams WHERE id='".$ide."'");
                        else $q = mysql_query("SELECT * FROM 2004teams WHERE id='".$ide."'");
                        $f = mysql_fetch_array($q);
                        $hl = LANG_USERPROFILE_TOTHETEAM;
                        $hl1 = $f[longname];
                        $url = "/team/".$item['main'][10]."-".SEOtitle($f[longname]);
                        }
                      elseif($item['main'][9]==2)
                        {
                        $el = substr($item['main'][10], -1);
                        $ide = substr($item['main'][10], 0, -1);
                        if($el==1) $q = mysql_query("SELECT * FROM el_matches WHERE id='".$ide."'");
                        else $q = mysql_query("SELECT * FROM 2004matches WHERE id='".$ide."'");
                        $f = mysql_fetch_array($q);
                        $hl = LANG_USERPROFILE_TOTHEGAME;
                        if($_SESSION[lang]!='sk') { $f[team1long] = TeamParser($f[team1long]); $f[team2long] = TeamParser($f[team2long]); }
                        $hl1 = $f[team1long]." vs. ".$f[team2long];
                        $url = "/game/".$item['main'][10]."-".SEOtitle($f[team1long]." vs ".$f[team2long]);
                        }
                      elseif($item['main'][9]==3)
                        {
                        $el = substr($item['main'][10], -1);
                        $ide = substr($item['main'][10], 0, -1);
                        if($el=="p") 
                          {
                          $q = mysql_query("SELECT id, 0 as el FROM `el_players` WHERE name='".$ide."' UNION SELECT id, 1 as el FROM `2004players` WHERE name='".$ide."' ORDER BY id DESC");
                          $f = mysql_fetch_array($q);
                          $url = "/player/".$f[id].$f[el]."-".SEOtitle($f[name]);
                          }
                        else 
                          {
                          $q = mysql_query("SELECT * FROM el_goalies WHERE name='".$ide."' ORDER BY id DESC");
                          $f = mysql_fetch_array($q);
                          $url = "/goalie/".$f[id]."-".SEOtitle($f[name]);
                          }
                        $hl = LANG_USERPROFILE_TOTHEPLAYER;
                        $hl1 = $ide;
                        }
                      $comment .='<p class="p-fluid"><a href="'.$url.'">'.$hl.' '.$hl1.'</a></p>';
                      }
                    $comment .= date("j.n.Y G:i",strtotime($item['main'][8]));
                    if(!$from_latest) $comment .=' <a href="#" class="btn btn-success btn-sm ml-2 replyComment" data-cid="'.($item['main'][7]!=0 ? $item['main'][7]:$item['main'][0]).'"><i class="fas fa-reply"></i></a>'.($item['main'][3]==$_SESSION[logged] ? ' <a href="#" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteComment" data-cid="'.$item['main'][0].'"><i class="far fa-trash-alt"></i></a>':'');
        $comment .='</div>
                    <div class="clearfix"></div>
                    <p class="p-fluid">'.nl2br($comm).'</p>
                </div>
                </div>
            </div>
            </div>';
  return $comment;
  }

function GetComments($what, $whatid)
  {
  $w = mysql_query("SELECT c.*, u.uname, u.user_avatar FROM comments c LEFT JOIN e_xoops_users u ON u.uid=c.uid WHERE what='$what' && whatid='$whatid' ORDER BY c.datum");
  while($e=mysql_fetch_array($w))
    {
    $id = $e[id];
    if($e[replyto]!=0) 
      {
      $replyto = $e[replyto];
      $comments[$replyto]['replies'][$id]['main'] = array($e[id], $e[uname], 0, $e[uid], $e[name], $e[user_avatar], $e[comment], $e[replyto], $e[datum], $what, $whatid);
      }
    else $comments[$id]['main'] = array($e[id], $e[uname], 0, $e[uid], $e[name], $e[user_avatar], $e[comment], $e[replyto], $e[datum], $what, $whatid);
    }
  foreach($comments as $item)
    {
    $comms .= ShowComment($item);
    foreach($item['replies'] as $reply)
      {
      $comms .= '<div class="offset-1">'.ShowComment($reply).'</div>';
      }
    }
  return $comms;
  }
  
function GenerateComments($what, $whatid)
  {
  Global $leaguecolor;
  if($_SESSION[logged])
    {
    $q = mysql_query("SELECT * FROM e_xoops_users WHERE uid='".$_SESSION[logged]."'");
    $f = mysql_fetch_array($q);
    }
  $comms = '<!-- Delete comment modal -->      
      <div class="modal" id="deleteComment" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">'.LANG_MAIN_REMOVECOMMENT.'</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="'.LANG_CLOSE.'">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <p>'.LANG_MAIN_REMOVECOMMENTTEXT.'</p>
            </div>
            <div class="modal-footer">
              <input type="hidden" id="cid">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">'.LANG_NO.'</button>
              <button type="button" class="btn btn-danger deleteComment">'.LANG_REMOVE.'</button>
            </div>
          </div>
        </div>
      </div>
  
  <a name="tocomments"></a>
  <h5 class="h5-fluid font-weight-bold">'.LANG_MAIN_COMMENTS.'</h5>
  <div id="comments">';
  $comms .= GetComments($what, $whatid);
  $comms .= '</div>
  <div class="alert alert-success mt-3" role="alert" style="display:none;"></div>
  <form>
    <div class="form-group">
      <label for="name" class="p-fluid">'.LANG_TEAMSTATS_NAME.'</label>
      <input type="text" class="form-control" id="name" placeholder="'.LANG_TEAMSTATS_NAME.'"'.($_SESSION[logged] ? ' value="'.$f[uname].'" readonly':'').'>
      <input type="hidden" id="uid"'.($_SESSION[logged] ? ' value="'.$_SESSION[logged].'"':'').'>
      <input type="hidden" id="what" value="'.$what.'">
      <input type="hidden" id="whatid" value="'.$whatid.'">
      <input type="hidden" id="replyid">
    </div>
    <div class="form-group">
      <label for="comment" class="p-fluid">'.LANG_MAIN_COMMENT.'</label>
      <textarea class="form-control" rows="3" id="comment" placeholder="'.LANG_MAIN_COMMENT.'"></textarea>
    </div>
    <div id="inline-badge" class="float-sm-right mb-3 mb-sm-0"></div>
    <a href="#tocomments" class="btn btn-'.$leaguecolor.'" id="addcomment">'.LANG_SEND.'</a>
  </form>';
  return $comms;
  }
  
/*
* Kontrola a zobrazenie notifikačnej ikony a notifikačný dropdown
* version: 1.0.0 (20.5.2020 - vytvorenie funkcie)
* @param $lite bool - 0/1 ci sa jedna len o zistenie neprečítaných notifikácií a vrátenie ich počtu
* @return $notif string
*/

function Notifications($lite=FALSE)
  {
  if($_SESSION['logged'])
    {
    $q = mysql_query("SELECT * FROM user_notifications WHERE uid='$_SESSION[logged]' ORDER BY datetime DESC LIMIT 5");
    $w = mysql_query("SELECT * FROM user_notifications WHERE uid='$_SESSION[logged]' && isread='0'");
    $notif .= '<li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="'.LANG_NAV_NOTIFTITLE.'">
                <i class="fas fa-bell fa-fw"></i>
                '.(mysql_num_rows($w)>0 ? '<span class="badge badge-danger badge-counter">'.mysql_num_rows($w).'</span>':'').'
              </a>
              <!-- Dropdown - Notifications -->
              <div class="notifications dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                  '.LANG_NAV_NOTIFTITLE.'
                </h6>';
           while($f = mysql_fetch_array($q))
               {
               // zmenit aj v user.php
               if($f[what]==0)
                {
                $el = substr($f[whatid], -1);
                $dl = strlen($f[whatid]);
                $ide = substr($f[whatid], 0, $dl-1);
                if($el==1) $e = mysql_query("SELECT * FROM el_matches WHERE id='".$ide."'");
                else $e = mysql_query("SELECT * FROM 2004matches WHERE id='".$ide."'");
                $r = mysql_fetch_array($e);
                $icon = 'trophy';
                $color = 'success';
                $url = '/report/'.$f[whatid].'-'.SEOtitle($r[team1long]." vs. ".$r[team2long]);
                $text = sprintf(LANG_NOTIF_FAVTEAM, $r[team1long], $r[team2long], $r[goals1], $r[goals2]);
                }
               elseif($f[what]==1)
                {
                $exp = explode("-", $f[whatid]);
                $e = mysql_query("SELECT * FROM 2004leagues WHERE id='".$exp[0]."'");
                $r = mysql_fetch_array($e);
                $icon = 'plus';
                $color = 'primary';
                $url = '/bets';
                $text = sprintf(LANG_NOTIF_BET, $r[longname], $exp[1]);
                }
               elseif($f[what]==2)
                {
                $icon = 'user-clock';
                $color = 'danger';
                $url = '/fantasy/draft';
                $text = LANG_FANTASY_MAILSUBJECT;
                }
               elseif($f[what]==3)
                {
                $e = mysql_query("SELECT c.*, u.uname FROM comments c LEFT JOIN e_xoops_users u ON u.uid=c.uid WHERE c.id='".$f[whatid]."'");
                $r = mysql_fetch_array($e);
                if($r[what]==0) { $url = "/news/".$r[whatid]."#comments"; }
                if($r[what]==1) { $url = "/team/".$r[whatid]."#comments"; }
                if($r[what]==2) { $url = "/game/".$r[whatid]."#comments"; }
                if($r[what]==3) 
                  {
                  if(substr($r[whatid], -1)=="p") $url = "/player/".substr($r[whatid], 0, -1)."#comments";
                  if(substr($r[whatid], -1)=="g") $url = "/goalie/".substr($r[whatid], 0, -1)."#comments";
                  }
                $icon = 'reply';
                $color = 'danger';
                if($r[uid]==0) $name = LANG_NOTIF_SOMEBODY;
                else $name = LANG_LOGED_AS." ".$r[uname];
                $text = sprintf(LANG_NOTIF_REPLY, $name);
                }
               $notif .= '
                <a class="dropdown-item d-flex align-items-center'.($f[isread]==0 ? ' alert-warning':'').'" href="'.$url.'" data-id="'.$f[id].'">
                  <div class="mr-3">
                    <div class="icon-circle bg-'.$color.'">
                      <i class="fas fa-'.$icon.' text-white"></i>
                    </div>
                  </div>
                  <div>
                    <div class="small text-gray-500">'.date("j.n.Y H:i", strtotime($f[datetime])).'</div>
                    <span'.($f[isread]==0 ? ' class="font-weight-bold"':'').'>'.$text.'</span>
                  </div>
                </a>';
               }
    if(mysql_num_rows($q)==0) $notif .= '<p class="dropdown-item text-gray-600">'.LANG_NAV_NOTIFNO.' ...</p>';
    $notif .= ' <a class="dropdown-item text-center small text-gray-500" href="/alerts">'.LANG_NAV_NOTIFALL.'</a>
              </div>
            </li>';
    }
  return $notif;
  }
  
/*
* Vytvorenie novej notifikácie pre užívateľa
* version: 1.0.0 (10.6.2020 - vytvorenie funkcie)
* @param $uid int - ID užívateľa, ktorý bude notifikovaný (0 pre všetkých, ktorých sa to týka)
* @param $what int - typ notifikácie:
                   - 0=výhra obľúbeného tímu
                   - 1=bodový zisk za tipovanie za posledné ligové kolo
                   - 2=ste na rade v draftovom výbere
                   - 3=odpoveď na komentár
* @param $whatid string - bližšie ID udalosti, o ktorú sa jedná (napr. shortname obľúbeného tímu)
* @return false
*/

function Insert_Notification($uid, $what, $whatid)
  {
  if($what==0)
    {
    // whatid = tshort-matchid
    $exp = explode("-", $whatid);
    $q = mysql_query("SELECT * FROM `e_xoops_users` WHERE user_favteam='".$exp[0]."'");
    while($f = mysql_fetch_array($q))
      {
      mysql_query("INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$f[uid]."', '0', '".$exp[1]."', NOW())");
      }
    }
  elseif($what==1)
    {
    // whatid = leagueid-kolo
    $exp = explode("-", $whatid);
    $q = mysql_query("SELECT et.userid, SUM(et.points) as poc FROM `el_matches` em LEFT JOIN el_tips et ON et.matchid=em.id WHERE em.league='".$exp[0]."' && em.kolo='".$exp[1]."' GROUP BY userid");
    while($f = mysql_fetch_array($q))
      {
      mysql_query("INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$f[uid]."', '1', '".$exp[0]."-".$f[poc]."', NOW())");
      }
    }
  elseif($what==2)
    {
    mysql_query("INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$uid."', '2', '0', NOW())");
    }
  elseif($what==3)
    {
    // whatid = comment_id
    mysql_query("INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$uid."', '3', '".$whatid."', NOW())");
    }
  return false;
  }

/*
* Kontrola a zobrazenie prihlaseneho uzivatela
* version: 1.0.0 (8.2.2016 - vytvorenie funkcie)
* version: 1.0.5 (26.6.2016 - pridanie lite verzie)
* @param $lite integer - 0/1 ci sa jedna len o vypisanie avataru a nicku do horneho menu
* @return $usermenu string
*/

function User_Menu()
  {
  if($_SESSION['logged'])
    {
    $q = mysql_query("SELECT * FROM e_xoops_users WHERE uid='$_SESSION[logged]'");
    $f = mysql_fetch_array($q);
    if($f[user_avatar]!="") $avatar = '<img class="img-profile rounded-circle" src="/images/user_avatars/'.$_SESSION[logged].'.'.$f[user_avatar].'?'.filemtime('images/user_avatars/'.$_SESSION['logged'].'.'.$f[user_avatar]).'" alt="'.$f[uname].'">';
    else $avatar = '<i class="fas fa-user-circle fa-2x"></i>';
    $usermenu .= '
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="'.LANG_USERPROFILE_TITLE.'">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">'.$f[uname].'</span>
                '.$avatar.'
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="/profile">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  '.LANG_NAV_USERHOMEPAGE.'
                </a>
                <a class="dropdown-item" href="/bets">
                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                  '.LANG_NAV_BETOVERVIEW.'
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  '.LANG_LOGOUT.'
                </a>
              </div>
            </li>';
    }
  else
    {
    $usermenu .= '<li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="'.LANG_USERPROFILE_TITLE.'">
                      <i class="fas fa-user-circle fa-2x"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                      <a class="dropdown-item" href="/login">
                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                        '.LANG_NAV_LOGIN.'
                      </a>
                      <a class="dropdown-item" href="/register">
                        <i class="fas fa-id-card-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        '.LANG_NAV_REGISTRATION.'
                      </a>
                    </div>
                  </li>';
    }
  return $usermenu;
  }
  
/*
* Prekladač názvov tímov zo slovenského jazyka do aktuálne zvoleného jazyka
* version: 1.5.0 (23.2.2016 - vytvorenie funkcie zo starej stránky pre novú)
* @param $name string - slovenský názov tímu
* @return $newname string
*/

function TeamParser($name)
  {
  Global $foreign_teams;
  $slovak_teams = array("Bielorusko","Dánsko","Česko","Európa","Fínsko","Francúzsko","Japonsko","Kanada","Kazachstan","Lotyšsko","Maďarsko","Nemecko","Nórsko","Rakúsko","Rusko","Severná Amerika","Slovensko","Slovinsko","Taliansko","Ukrajina","USA","Švajčiarsko","Švédsko");
  $newname = str_replace($slovak_teams,$foreign_teams,$name);
  return $newname;
  }
  
/*
* Prekladač stavov tretín zo slovenského jazyka do aktuálne zvoleného jazyka
* version: 1.0.0 (25.1.2021 - vytvorenie funkcie)
* @param $name string - slovenský stav tretiny
* @return $newname string
*/

function StatusParser($name)
  {
  Global $foreign_statuses;
  $slovak_statuses = array("konečný stav","na programe","pripravte sa","v 1.tretine","po 1.tretine","v 2.tretine","po 2.tretine","v 3.tretine","po 3.tretine","v predlzeni","po predlzeni");
  $newname = str_replace($slovak_statuses,$foreign_statuses,$name);
  return $newname;
  }

/*
* Funkcia na overenie zapamätaného prihlásenia
* version: 1.0.0 (3.7.2016 - vytvorenie funkcie)
* @return false
*/

function CheckCookieLogin() {
    $uname = $_COOKIE['uname']; 
    if (!empty($uname)) {
        $q = mysql_query("SELECT dt.* FROM (SELECT uid, JSON_SEARCH(login_session, 'one', '".$uname."') as search FROM `e_xoops_users`)dt WHERE dt.search IS NOT NULL");
        if(mysql_num_rows($q)>0)
          {
          $f = mysql_fetch_array($q);
          $_SESSION['logged'] = $f[uid];
          setcookie("uname",$uname,time()+3600*24*365,'/','.hockey-live.sk');
          }
        }
}

function SendMail($to, $subject, $message)
  {
  Global $link;
  if($_SESSION[user]!=1) {
    $headers = 'From: '.SITE_MAIL. "\r\n" .
      'Reply-To: '.SITE_MAIL. "\r\n" .
      'X-Mailer: PHP/' . phpversion();
    mail($to, $subject, $message, $headers);
    }
  }
?>