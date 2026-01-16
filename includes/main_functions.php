<?
require __DIR__ . '/../vendor/Webpush/vendor/autoload.php';
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

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

function Get_SEO_title($topicID, $provided=false) {
    Global $link;
    if($provided) $f["topic_title"]=$topicID;
    else {
        $q = mysqli_query($link, "SELECT * FROM e_xoops_topics WHERE topic_id='".$topicID."'");
        $f = mysqli_fetch_array($q);
    }
    if(isset($f["topic_title"])) {
      if(strstr($f["topic_title"], "U20")) $title = "Majstrovstvá sveta v hokeji do 20 rokov";
      elseif(strstr($f["topic_title"], "MS ")) $title = str_replace("MS ", "Majstrovstvá sveta v hokeji ", $f["topic_title"]);
      elseif(strstr($f["topic_title"], "ZOH ")) $title = str_replace("ZOH ", "Zimné olympijské hry ", $f["topic_title"]);
      else $title = $f["topic_title"];
    }
    else $title = "Novinky";
    return $title;
}

/*
* Funkcia pre vrátenie farby na základe názvu ligy
* version: 1.0.0 (6.2.2020 - vytvorenie novej funkcie)
* @param $name string - názov ligy
* @return $color string - farba podľa Boostrap konvencie
*/
function LeagueColor($name) {
    $name = SEOtitle($name);
    if (preg_match('/nemecky|tipsport|extraliga|khl|nhl|ms|zoh|kaufland|slovakia|loto|challenge|skoda|svetovy/', $name, $matches)) {
        switch ($matches[0]) {
            case 'nemecky':
            case 'khl':
                $bg = 'danger';
                break;
            case 'tipsport':
            case 'extraliga':
            case 'kaufland':
            case 'slovakia':
            case 'loto':
                $bg = 'primary';
                break;
            case 'nhl':
            case 'challenge':
            case 'skoda':
                $bg = 'warning';
                break;
            case 'ms':
                $bg = 'success';
                break;
            case 'zoh':
            case 'svetovy':
                $bg = 'info';
                break;
            default:
                $bg = 'hl';
        }
    } else {
        $bg = 'hl';
    }
    return $bg;
}

/*
* Funkcia pre vrátenie font ikony ligy na základe názvu ligy
* version: 1.0.0 (13.2.2020 - vytvorenie novej funkcie)
* @param $name string - názov ligy
* @return $font string - názov ikony vo fonte (napr. ll-*tipsport*)
*/

function LeagueFont($name) {
    if($name==null) return false;
    $name = mb_strtolower($name, 'UTF-8');

    $leagueFontMapping = [
        'tipsport' => ['tipsport', 'extraliga'],
        'khl' => ['khl'],
        'nhl' => ['nhl'],
        'iihf' => ['ms'],
        'olympics' => ['zoh'],
        'kauflandcup' => ['kaufland', 'slovakia', 'loto'],
        'arosa' => ['challenge', 'škoda'],
    ];

    foreach ($leagueFontMapping as $font => $leagueNames) {
        if (array_intersect($leagueNames, explode(' ', $name))) {
            return $font;
        }
    }

    return 'dcup';
}


function Tournament_Strip() {
  Global $link;
  $ts="";
  $q = mysqli_query($link, "SELECT l.*, (SELECT COUNT(*) FROM 2004players p WHERE p.league = l.id && p.teamshort='SVK') AS player_count, t.id as tid, t.longname as tlong, t.final_pos, m.datetime, m.id as mid, IF(m.team1short='SVK', m.team2long, m.team1long) as vsteam, m.team1long, m.team2long FROM 2004leagues l LEFT JOIN 2004teams t ON t.league=l.id && t.shortname='SVK' LEFT JOIN 2004matches m ON m.league=l.id && (m.team1short='SVK' || m.team2short='SVK') && m.datetime>NOW() && m.kedy='na programe' WHERE l.start_date <= DATE_ADD(CURDATE(), INTERVAL 1 WEEK) AND l.end_date >= DATE_SUB(CURDATE(), INTERVAL 3 DAY) LIMIT 1");
  if(mysqli_num_rows($q)>0) {
    $f = mysqli_fetch_array($q);
    $longname = $f["longname"];
    $newlongname = str_replace("Nemecký pohár", "Nemeckom pohári", $longname);
    $newlongname = str_replace("Vianočný Kaufland Cup", "Vianočnom Kaufland Cupe", $longname);
    if($newlongname == $longname) $newlongname = str_replace("Kaufland Cup", "Kaufland Cupe", $newlongname);
    $ts = '
    <div class="bg-secondary border-bottom border-dark d-flex justify-content-between justify-content-sm-start p-1 small text-white" id="tournamentStrip">
      <div class="align-self-center mr-2 tournamentName"><b class="text-gray-300">Slovensko</b> na <b>'.$newlongname.'</b></div>
      <div class="d-none d-sm-block vr mr-2"></div>
      <div class="text-center">';
        if($f["player_count"]>0) $ts .= '<a class="btn btn-dark btn-sm py-0 d-inline-flex mr-1" href="/team/'.$f["tid"].'0-'.SEOtitle($f["tlong"]).'">Zostava</a>';
        if($f["mid"]!=NULL) {

          $date = new DateTime($f["datetime"]);
          $today = new DateTime();
          $today->setTime(0, 0);
          $tomorrow = (clone $today)->modify('+1 day');
          $day_after_tomorrow = (clone $today)->modify('+2 days');

          if ($date->format('Y-m-d') == $today->format('Y-m-d')) {
              $hl = "dnes o ".$date->format("H:i");
          } elseif ($date->format('Y-m-d') == $tomorrow->format('Y-m-d')) {
              $hl = "zajtra";
          } elseif ($date->format('Y-m-d') == $day_after_tomorrow->format('Y-m-d')) {
              $hl = "pozajtra";
          } else {
              $hl = $date->format('j.n. H:i');
          }

          $ts .= '<a class="btn btn-dark btn-sm py-0 d-inline-flex" href="/game/'.$f["mid"].'0-'.SEOtitle($f["team1long"]." vs ".$f["team2long"]).'">Ďalší zápas<span class="d-none d-sm-block ml-1">'.$hl.' vs. <b>'.$f["vsteam"].'</b></span></a>';
        }
        if($f["final_pos"]!=0) $ts .= '<a class="btn btn-dark btn-sm py-0 d-inline-flex" href="/table/'.$f["id"].'-'.SEOtitle($f["longname"]).'">Konečné <b class="ml-1">'.$f["final_pos"].'.miesto</b></a>';
    $ts .= '
      </div>
    </div>';
  }
return $ts;
}

/*
* Funkcia pre vygenerovanie menu jednotlivých aktívnych líg
* version: 3.0.0 (29.1.2020 - prispôsobenie pre Boostrap4 template)
* @param $active_league int - ID aktuálne vybranej ligy
* @return $menu string
*/

function Generate_Menu($active_league = FALSE) {
    Global $link;
    $menu = $elpred = "";
    $q = mysqli_query($link, "SELECT dt.*, e_xoops_topics.topic_title FROM e_xoops_topics JOIN (SELECT * FROM 2004leagues WHERE position > '0' && id!='70')dt ON dt.topic_id=e_xoops_topics.topic_id ORDER BY el DESC, position ASC");
    while($f = mysqli_fetch_array($q))
      {
      $badge_num=$live=0;
      $badge="";
      if($f["el"]==0) $matches_table="2004matches";
      elseif($f["el"]==1) $matches_table="el_matches";
      else $matches_table="al_matches";
      $font = LeagueFont($f["longname"]);
      $bg = LeagueColor($f["longname"]);
      if(strstr($f["longname"], 'NHL')) $w = mysqli_query($link, "SELECT * FROM $matches_table WHERE datetime > '".date("Y-m-d")." 10:00' && datetime < '".date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')+1, date('Y')))." 10:00' && league='".$f["id"]."'");
      else $w = mysqli_query($link, "SELECT * FROM $matches_table WHERE datetime LIKE '".date("Y-m-d")."%' && league='".$f["id"]."'");
      $badge_num = mysqli_num_rows($w);
      if($badge_num>0)
        {
        while($g = mysqli_fetch_array($w)) 
          {
          if($g["active"]==1) $live=1;
          }
        $badge = '<span class="badge badge-'.$bg.' float-right mr-2 text-xs">'.$badge_num.'</span>';
        if($live==1) $badge .= '<span class="badge live" style="margin-right:2px;color:#FFEB3B;">LIVE</span>';
        }
      $sim=0;
      $tt = LeagueSpecifics($f["id"], $f["longname"], 1);
      if(strstr($f["longname"], "KHL") || strstr($f["longname"], "NHL")) $table_type="conference";
      if(strstr($f["longname"], "liga")) $table_type="conference";
      if($f["el"]==0) $table_type="division";
      if($f["el"]!=$elpred) $menu .= '<!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        '.($f["el"]==1 ? 'Ligy':'Turnaje').'
      </div>';
      $menu .= '<li class="nav-item'.($active_league==$f["id"] ? ' active':'').'" itemscope="itemscope" itemtype="http://www.schema.org/SiteNavigationElement">
                  <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapse-'.$f["id"].'" aria-expanded="true" aria-controls="collapse-'.$f["id"].'" role="button">
                    <i class="ll-'.$font.'"></i>
                    <span>'.($f["el"]==1 ? substr($f["longname"],0,-6):$f["longname"]).'</span>
                  </a>
                  <div id="collapse-'.$f["id"].'" class="collapse" aria-labelledby="heading-'.$f["id"].'" data-parent="#accordionSidebar">
                    <div class="bg-white border-left-'.$bg.' py-2 collapse-inner rounded">
                      <h6 class="collapse-header">'.LANG_NAV_TABLE.':</h6>
                      <div class="text-xs pb-2">
                        '.($f["endbasic"]==1 && $f["el"]>0 ? Get_Series($f["id"]) : ($f["endbasic"]==1 && $f["el"]==0 ? Get_team_table($f["id"]):$tt->render_table($table_type, 1))).'
                      </div>
                      <div class="collapse-divider"></div>
                      <h6 class="collapse-header">Odkazy:</h6>
                      '.($f["info_id"]!=null ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/info/'.$f["info_id"].'-'.SEOtitle(Get_SEO_title($f["topic_title"],1)).'"><span itemprop="name">'.LANG_NAV_INFO.'</span><i class="fas fa-circle-info fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      <a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/category/'.$f["topic_id"].'-'.SEOtitle(Get_SEO_title($f["topic_title"],1)).'"><span itemprop="name">'.LANG_NAV_NEWS.'</span><i class="fas fa-newspaper fa-fw float-right text-gray-500 my-1"></i></a>
                      '.($f["el"]>0 && $f["endbasic"]==1 ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/table/'.$f["id"].'-'.SEOtitle(Get_SEO_title($f["topic_title"],1)).'/playoff"><span itemprop="name">'.LANG_TEAMTABLE_PLAYOFF.'</span><i class="fas fa-trophy fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      <a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="'.($f["endbasic"]==1 && $f["el"]==0 ? "/table/".$f["id"]."-".SEOtitle(Get_SEO_title($f["topic_title"],1))."/playoff" : "/games/".$f["id"]."-".SEOtitle(Get_SEO_title($f["topic_title"],1))).'"><span itemprop="name">'.LANG_NAV_UPCOMMING.'</span><i class="fas fa-hockey-puck fa-fw float-right text-gray-500 my-1"></i>'.$badge.'</a>
                      <a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/table/'.$f["id"].'-'.SEOtitle(Get_SEO_title($f["topic_title"],1)).''.($f["groups"]!="" ? "/groups" : "").'"><span itemprop="name">'.LANG_NAV_TABLE.'</span><i class="fas fa-table-list fa-fw float-right text-gray-500 my-1"></i></a>
                      <a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/stats/'.$f["id"].'-'.SEOtitle(Get_SEO_title($f["topic_title"],1)).'"><span itemprop="name">'.LANG_NAV_STATS.'</span><i class="fas fa-chart-bar fa-fw float-right text-gray-500 my-1"></i></a>
                      '.(strstr($f["longname"], 'NHL') ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/slovaks/'.$f["id"].'-'.SEOtitle(Get_SEO_title($f["topic_title"],1)).'"><span itemprop="name">'.LANG_NAV_SLOVAKS.'</span><i class="fas fa-user-shield fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.(strstr($f["longname"], 'KHL') ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/slovaks/'.$f["id"].'-'.SEOtitle(Get_SEO_title($f["topic_title"],1)).'"><span itemprop="name">'.LANG_NAV_SLOVAKI.'</span><i class="fas fa-user-shield fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.($f["el"]>0 && $f["topic_id"]!=60 ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/injured/'.$f["id"].'-'.SEOtitle(Get_SEO_title($f["topic_title"],1)).'"><span itemprop="name">'.LANG_NAV_INJURED.'</span><i class="fas fa-user-injured fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.($f["el"]>0 ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/transfers/'.$f["id"].'-'.SEOtitle(Get_SEO_title($f["topic_title"],1)).'"><span itemprop="name">'.LANG_NAV_TRANSFERS.'</span><i class="fas fa-exchange-alt fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.($f["id"]==144 ? '<a itemprop="url" class="collapse-item font-weight-bold text-danger" href="/fantasy/main"><span itemprop="name">Fantasy KHL</span><i class="fas fa-wand-magic-sparkles fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.($f["trip_id"]!=null ? '<a itemprop="url" class="collapse-item font-weight-bold text-gray-700" href="/trip/'.$f["trip_id"].'-'.SEOtitle(Get_SEO_title($f["topic_title"],1)).'"><span itemprop="name">'.LANG_NAV_TRIP.'</span><i class="fas fa-suitcase fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                      '.($f["id"]==166 ? '<a itemprop="url" class="collapse-item font-weight-bold text-success" href="/fantasy/picks"><span itemprop="name">Fantasy MS</span><i class="fas fa-wand-magic-sparkles fa-fw float-right text-gray-500 my-1"></i></a>':'').'
                    </div>
                  </div>
                </li>';
      $elpred=$f["el"];
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
Global $link;

$ttable="";

$a = mysqli_query($link, "SELECT * FROM 2004leagues WHERE id='".$league."'");
$b = mysqli_fetch_array($a);
$wpoints=$b["points"];
$teams_table="2004teams"; 
$el="0";

$m = mysqli_query($link, "SELECT * FROM 2004teams WHERE final_pos!='' && league='".$league."'");
if($b["groups"]=="" || $b["groups"]!="" && $b["endbasic"]==1 && $b["osem"]==0 && mysqli_num_rows($m)==0)
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
while ($i < mysqli_num_rows($m))
		{
	$clinch=$line="";
	$data = mysqli_fetch_array($m);
	$shortname = $data["longname"];
	if($_SESSION["lang"] != 'sk') $shortname = TeamParser($shortname);
	$points = $data["body"];

    // play-off line
    if($i==7) $line=" style='border-bottom:1px dashed black !important;'";
	$ttable .= "<tr><td class='text-center'$line'>$p.</td><td$line><a href='/team/$data[id]$el-".SEOtitle($data["longname"])."'><img class='flag-".($b["el"]==1 ? 'el':'iihf')." ".$data["shortname"]."-small mr-1' src='/images/blank.png' alt='".$shortname."'>$shortname</a>$clinch</td><td class='text-center'$line'><b>$points</b></td></tr>";
	if($pos==$data["shortname"] && $b["el"]==0) { return $p; }
$i++;
$p++;
		}
  $ttable .= '</tbody>
  <tfoot class="small">';
	$ttable .= "</tfoot>
	</table>";
	}
else
	{
	if($b["osem"]==1)
		{
		// SKUPINY OSEMFINALE
		$q = mysqli_query($link, "SELECT skup_osem FROM 2004teams WHERE league='".$league."' && skup_osem!='G' GROUP BY skup_osem ORDER BY skup_osem ASC");
	$j=0;
	while ($j <= mysqli_num_rows($q)-1)
			{
		$f = mysqli_fetch_array($q);
	$ttable .= "<table class='w-100 table-striped table-hover'>
    <thead>
     <tr> 
          <td colspan='3' class='pl-2'><b>".LANG_TEAMTABLE_GROUP." ".$f["skup_osem"]."</b></td>
     </tr>
     <tr> 
          <td scope='col' class='text-center'>#</td>
          <td scope='col'><b>".LANG_PLAYERSTATS_TEAM."</b></td>
          <td scope='col' class='text-center'><b>".LANG_TEAMSTATS_POINTS."</b></td>
     </tr>
    </thead>
    <tbody>";
$coto = mysqli_query($link, "SELECT *, gf_osem-ga_osem as diff FROM ".$teams_table." WHERE league='".$league."' && skup_osem='".$f["skup_osem"]."' ORDER BY p_osem desc, diff desc, gf_osem, w_osem desc, l_osem asc, t_osem desc");

$m=$points=0;
$tshort="";
$tabul=[];
while($data = mysqli_fetch_array($coto))
	{
	$posun=0;
	if($points==$data["p_osem"])
		{
		$vzaj = mysqli_query($link, "SELECT IF(team1short='".$data["shortname"]."',IF(goals1>goals2,1,0),IF(goals1>goals2,0,1)) as posun FROM 2004matches WHERE (team1short='".$data["shortname"]."' && team2short='".$tshort."' || team1short='".$tshort."' && team2short='".$data["shortname"]."') && league='".$league."' && kedy='konečný stav'");
		$vzajom = mysqli_fetch_array($vzaj);
		if($vzajom["posun"]==1)
			{
			$mm = $m-1;
			$tabul[$m] = $tabul[$mm];
			$m--;
			$posun=1;
			}
		}
	$points = $data["p_osem"];
	$tshort = $data["shortname"];
	if($b["el"]==1) $data["longname"] = $data["mediumname"];
	else $shortname = $data["longname"];
	$tabul[$m] = array($data["id"], $data["shortname"], $shortname, $data["p_osem"]);
	$m++;
	if($posun==1) $m++;
	}
$i=0;
$p=1;
while ($i < count($tabul))
	{
	if($_SESSION["lang"] != 'sk') $tabul[$i][2] = TeamParser($tabul[$i][2]);
	$ttable .= "<tr><td class='text-center'>".$p.".</td><td><a href='/team/".$tabul[$i][0].$el."-".SEOtitle($tabul[$i][2])."'><img class='flag-iihf ".$tabul[$i][1]."-small mr-1' src='/images/blank.png' alt='".$tabul[$i][2]."'>".$tabul[$i][2]."</a></td><td class='text-center'><b>".$tabul[$i][3]."</b></td></tr>";
	if($pos==$tabul[$i][1]) return $p;
	$i++;
	$p++;
	}

		$ttable .= "</tbody>
		</table>";
		$j++;
			}
		}
	else
		{
		// SKUPINOVY SYSTEM, MIMO OSEMFINALE
		if($b["endbasic"]==1 && mysqli_num_rows($m)>0)
			{
			// SKONCILI SKUPINY A ZOBRAZI SA FINALNE UMIESTNENIE
			$n = mysqli_query($link, "SELECT * FROM 2004teams WHERE league='".$b["id"]."' ORDER BY final_pos ASC");
            $teamsnum = mysqli_num_rows($n);
            $i=1;
            $finalpos=array();
			while($h = mysqli_fetch_array($n))
				{
                $posi = $h["final_pos"];
                if($posi>0) $finalpos[$posi] = array($h["id"], $h["longname"], $h["shortname"]);
                $i++;
                }
            echo $pos;
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
			while($c <= $teamsnum)
				{
				$line="";
				if($c==1) $ci = "<img src='https://www.hockey-live.sk/img/medal_gold_3.png' class='align-top' alt='".LANG_TEAMTABLE_GOLD."' title='".LANG_TEAMTABLE_GOLD."'>";
				elseif($c==2) $ci = "<img src='https://www.hockey-live.sk/img/medal_silver_3.png' class='align-top' alt='".LANG_TEAMTABLE_SILVER."' title='".LANG_TEAMTABLE_SILVER."'>";
				elseif($c==3) $ci = "<img src='https://www.hockey-live.sk/img/medal_bronze_3.png' class='align-top' alt='".LANG_TEAMTABLE_BRONZE."' title='".LANG_TEAMTABLE_BRONZE."'>";
        else $ci = $c.".";
        if($c==14) $line=" style='border-bottom:1px dashed black !important;'";
				if(array_key_exists($c, $finalpos))
					{
                    if($pos==$finalpos[$c][2]) return $c;
					if($_SESSION["lang"] != 'sk') $finalpos[$c][1] = TeamParser($finalpos[$c][1]);
					$ttable .= "<tr><td class='text-center'".$line.">".$ci."</td><td".$line."><a href='/team/".$finalpos[$c][0].$el."-".SEOtitle($finalpos[$c][1])."'>".$finalpos[$c][1]."</a></td></tr>";
					}
				else
					{
					$ttable .= "<tr><td class='text-center'".$line.">".$ci."</td><td".$line.">&nbsp;</td></tr>";
					}
				$c++;
				}
			$ttable .= "</tbody>
			</table>";
			}
		else
			{
			// PREBIEHAJU ZAKLADNE SKUPINY
			$crop = explode("|", $b["groups"]);
			$j=0;
			while ($j <= count($crop)-1)
				{
				$skup = $crop[$j];
				$ttable .= "<table class='w-100 table-striped table-hover'>
				<thead>
          <tr> 
            <td colspan='3' class='pl-2'><b>".LANG_TEAMTABLE_GROUP." ".$skup."</b></td>
          </tr>
           <tr> 
                <td scope='col' class='text-center'>#</td>
                <td scope='col'><b>".LANG_PLAYERSTATS_TEAM."</b></td>
                <td scope='col' class='text-center'><b>".LANG_TEAMSTATS_POINTS."</b></td>
           </tr>
        </thead>
        <tbody>";
				if($b["endbasic"]==1) $coto = mysqli_query($link, "SELECT *, gf_basic-ga_basic as diff FROM ".$teams_table." WHERE league='".$league."' && skupina='".$skup."' ORDER BY p_basic desc, diff desc, w_basic desc, l_basic asc, t_basic desc");
				else $coto = mysqli_query($link, "SELECT *, goals-ga as diff FROM ".$teams_table." WHERE league='".$league."' && skupina='".$skup."' ORDER BY body desc, zapasov asc, diff desc, wins desc, losts asc, ties desc");
				
				// BODY >> VZAJOMNY ZAPAS >> ROZDIEL SKORE (START)
        $m=$points=0;
        $tshort="";
        $tabul=[];
        while($data = mysqli_fetch_array($coto))
          {
          $posun=0;
          if($points==$data["body"])
            {
            $vzaj = mysqli_query($link, "SELECT IF(team1short='".$data["shortname"]."',IF(goals1>goals2,1,0),IF(goals1>goals2,0,1)) as posun FROM 2004matches WHERE (team1short='".$data["shortname"]."' && team2short='".$tshort."' || team1short='".$tshort."' && team2short='".$data["shortname"]."') && league='".$league."' && kedy='konečný stav'");
            $vzajom = mysqli_fetch_array($vzaj);
            if($vzajom["posun"]==1)
              {
              $mm = $m-1;
              $tabul[$m] = $tabul[$mm];
              $m--;
              $posun=1;
              }
            }
          $points = $data["body"];
          $tshort = $data["shortname"];
          if($b["el"]==1) $data["longname"] = $data["mediumname"];
          else $shortname = $data["longname"];
          $tabul[$m] = array($data["id"], $data["shortname"], $shortname, $data["body"]);
          $m++;
          if($posun==1) $m++;
          }
        $i=0;
        $p=1;
        while ($i < count($tabul))
          {
          $line="";
          if(strstr($b["longname"], 'Svetový pohár'))
            {
            if($i==1) $line=" style='border-bottom:1px dashed black !important;'";
            }
          elseif(strstr($b["longname"], 'ZOH'))
            {
            if($i==0) $line=" style='border-bottom:1px dashed black !important;'";
            }
          //elseif($i==3 || $i==6) $line=" style='border-bottom:1px dashed black !important;'";
          elseif($i==3) $line=" style='border-bottom:1px dashed black !important;'";
          if($_SESSION["lang"] != 'sk') $tabul[$i][2] = TeamParser($tabul[$i][2]);
          $ttable .= "<tr><td class='text-center'".$line.">".$p.".</td><td".$line."><a href='/team/".$tabul[$i][0].$el."-".SEOtitle($tabul[$i][2])."'><img class='flag-iihf ".$tabul[$i][1]."-small mr-1' src='/images/blank.png' alt='".$tabul[$i][2]."'>".$tabul[$i][2]."</a></td><td class='text-center'".$line."><b>".$tabul[$i][3]."</b></td></tr>";
          if($pos==$tabul[$i][1]) return $p;
          $i++;
          $p++;
          } 	
				
				$ttable .= "</tbody>
				</table>";
				$j++;
				}
			}
		}
	}
$ttable = str_replace(">Slovensko<", "><span class='font-weight-bold'>Slovensko</span><", $ttable);
$ttable = str_replace(">Slovensko U20<", "><span class='font-weight-bold'>Slovensko U20</span><", $ttable);
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
  Global $link;
  $series="";
  $q = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
	$q = mysqli_query($link, "SELECT potype, 2004leagues.longname FROM el_playoff JOIN 2004leagues ON 2004leagues.id=el_playoff.league WHERE league='".$lid."' && played='0' GROUP BY potype LIMIT 1");
	if(mysqli_num_rows($q)==0) {
	$q = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
	$q = mysqli_query($link, "SELECT potype, 2004leagues.longname FROM el_playoff JOIN 2004leagues ON 2004leagues.id=el_playoff.league WHERE league='".$lid."' && played='1' GROUP BY potype ORDER BY el_playoff.id DESC LIMIT 1");
	}
	$f = mysqli_fetch_array($q);
    $f["potype"] = $f["potype"] ?? null;
    $hl = "";
	$w = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
	$w = mysqli_query($link, "SELECT el_playoff.*, t1.longname as t1, t2.longname as t2, t1.id as t1id, t2.id as t2id FROM el_playoff JOIN el_teams t1 ON (el_playoff.team1=t1.shortname && el_playoff.league=t1.league) JOIN el_teams t2 ON (el_playoff.team2=t2.shortname && el_playoff.league=t2.league) WHERE el_playoff.league='".$lid."' && el_playoff.potype='".$f["potype"]."' GROUP BY id ORDER BY el_playoff.id ASC");
  if($f["potype"]=="baraz") $hl=LANG_TEAMTABLE_PLAYOFF." - ".LANG_QUALIFYROUND;
  elseif($f["potype"]=="stvrt") $hl=LANG_TEAMTABLE_PLAYOFF." - ".LANG_QUARTERFINAL;
  elseif($f["potype"]=="semi") $hl=LANG_TEAMTABLE_PLAYOFF." - ".LANG_SEMIFINAL;
  elseif($f["potype"]=="final") $hl=LANG_TEAMTABLE_PLAYOFF." - ".LANG_FINAL;
  elseif($f["potype"]=="stanley")
    {
    if(strstr($f["longname"], 'NHL')) $hl=LANG_STANLEYFINALS;
    if(strstr($f["longname"], 'KHL')) $hl=LANG_GAGARINFINALS;
    }
	$series .= "<table class='w-100 table-striped table-hover'>
                <thead>
                  <tr>
                    <td colspan='3' class='text-center font-weight-bold'>".$hl."</td>
                  </tr>
                </thead>
                <tbody>";
	$i=0;
	$k=1;
	while ($e = mysqli_fetch_array($w))
		{
		$add1=$add2=$line="";
		if($e["status1"]==4 || ($e["status1"]==3 && $f["potype"]=="baraz")) $add1 = " class='font-weight-bold'";
		if($e["status2"]==4 || ($e["status2"]==3 && $f["potype"]=="baraz")) $add2 = " class='font-weight-bold'";
		
		if(strstr($f["longname"], 'HL') && $f["potype"]=="stvrt" && $i==3 || strstr($f["longname"], 'HL') && $f["potype"]=="semi" && $i==1 || strstr($f["longname"], 'HL') && $f["potype"]=="final" && $i==0) $line=" style='border-bottom:1px dashed black !important;'";
		
		$series .= "<tr>
                  <td$line><a href='/team/".$e["t1id"]."1-".SEOTitle($e["t1"])."'".$add1."><img class='flag-el ".$e["team1"]."-small' src='/images/blank.png' alt='".$e["t1"]."'> ".$e["team1"]."</a></td>
                  <td class='font-weight-bold text-center'".$line.">".$e["status1"].":".$e["status2"]."</td>
                  <td class='text-right'".$line."><a href='/team/".$e["t2id"]."1-".SEOTitle($e["t2"])."'".$add2.">".$e["team2"]." <img class='flag-el ".$e["team2"]."-small' src='/images/blank.png' alt='".$e["t2"]."'></a></td>
                </tr>";
		$i++;
		$k++;
		}
  $series .= "</tbody></table>";
return $series;
}

function ShowComment($item, $from_latest=false)
  {
  Global $link;
  if(!isset($item["main"])) return false;
  $comment=$url=$hl=$hl1="";
  $nick = $item['main'][1];
  if($item['main'][3]==0 && $item['main'][4]!=NULL) $item['main'][1] = $item['main'][4];
  if($item['main'][3]==0 && $item['main'][4]==NULL) $item['main'][1] = "neregistrovaný";
  $comm = $item['main'][6];
  $comm = str_replace(':hladkamsa:','<img src="/images/smilies/boast.gif" class="align-text-bottom" alt=":hladkamsa:">',$comm);
  $comm = str_replace(':agresivny:','<img src="/images/smilies/aggressive.gif" class="align-text-bottom" alt=":agresivny:">',$comm);
  $comm = str_replace(':tlieskam:','<img src="/images/smilies/clapping.gif" class="align-text-bottom" alt=":tlieskam:">',$comm);
  $comm = str_replace(':auu:','<img src="/images/smilies/vava.gif" class="align-text-bottom" alt=":auu:">',$comm);
  $comm = str_replace(':beee:','<img src="/images/smilies/beee.gif" class="align-text-bottom" alt=":beee:">',$comm);
  $comm = str_replace(':nono:','<img src="/images/smilies/nea.gif" class="align-text-bottom" alt=":nono:">',$comm);
  $comm = str_replace(':ee:','<img src="/images/smilies/nea.gif" class="align-text-bottom" alt=":ee:">',$comm);
  $comm = str_replace(':stop:','<img src="/images/smilies/stop.gif" class="align-text-bottom" alt=":stop:">',$comm);
  $comm = str_replace(':pocuvam:','<img src="/images/smilies/music2.gif" class="align-text-bottom" alt=":pocuvam:">',$comm);
  $comm = str_replace(':bomba:','<img src="/images/smilies/bomb.gif" class="align-text-bottom" alt=":bomba:">',$comm);
  $comm = str_replace(':pifko:','<img src="/images/smilies/drinks.gif" class="align-text-bottom" alt=":pifko:">',$comm);
  $comm = str_replace(':king:','<img src="/images/smilies/king.gif" class="align-text-bottom" alt=":king:">',$comm);
  $comm = str_replace(':ok:','<img src="/images/smilies/ok.gif" class="align-text-bottom" alt=":ok:">',$comm);
  $comm = str_replace(':plazujem:','<img src="/images/smilies/beach.gif" class="align-text-bottom" alt=":plazujem:">',$comm);
  $comm = str_replace(':placem:','<img src="/images/smilies/cray.gif" class="align-text-bottom" alt=":placem:">',$comm);
  $comm = str_replace(':tancujem:','<img src="/images/smilies/dance4.gif" class="align-text-bottom" alt=":tancujem:">',$comm);
  $comm = str_replace(':rofl:','<img src="/images/smilies/ROFL.gif" class="align-text-bottom" alt=":rofl:">',$comm);
  $comm = str_replace(':cmuk:','<img src="/images/smilies/kiss3.gif" class="align-text-bottom" alt=":cmuk:">',$comm);
  $comm = str_replace(':lenivy:','<img src="/images/smilies/lazy.gif" class="align-text-bottom" alt=":lenivy:">',$comm);
  $comm = str_replace(':sok:','<img src="/images/smilies/shok.gif" class="align-text-bottom" alt=":sok:">',$comm);
  $comm = str_replace(':anjel:','<img src="/images/smilies/angel.gif" class="align-text-bottom" alt=":anjel:">',$comm);
  $comm = str_replace(':ciga:','<img src="/images/smilies/smoke.gif" class="align-text-bottom" alt=":ciga:">',$comm);
  $comm = str_replace(':hladam:','<img src="/images/smilies/search.gif" class="align-text-bottom" alt=":hladam:">',$comm);
  $comm = str_replace(':jo:','<img src="/images/smilies/yes3.gif" class="align-text-bottom" alt=":jo:">',$comm);
  $comm = str_replace(':mojesrdce:','<img src="/images/smilies/give_heart2.gif" class="align-text-bottom" alt=":mojesrdce:">',$comm);
  $comm = str_replace(':dik:','<img src="/images/smilies/thank_you2.gif" class="align-text-bottom" alt=":dik:">',$comm);
  $comm = str_replace(':sisi:','<img src="/images/smilies/wacko.gif" class="align-text-bottom" alt=":sisi:">',$comm);
  $comm = str_replace(':lol:','<img src="/images/smilies/lol.gif" class="align-text-bottom" alt=":lol:">',$comm);
  $comm = str_replace(':sorry:','<img src="/images/smilies/sorry.gif" class="align-text-bottom" alt=":sorry:">',$comm);
  $comm = str_replace(':toto:','<img src="/images/smilies/this.gif" class="align-text-bottom" alt=":toto:">',$comm);
  $comm = str_replace(':bodycheck:','<img src="/images/smilies/bodycheck.gif" class="align-text-bottom" alt=":bodycheck:">',$comm);
  $comm = str_replace(':modry:','<img src="/images/smilies/modry.gif" class="align-text-bottom" alt=":modry:">',$comm);
  $comm = str_replace(':cerveny:','<img src="/images/smilies/cerveny.gif" class="align-text-bottom" alt=":cerveny:">',$comm);
  $comm = str_replace(':)','<img src="/images/smilies/smiley.png" alt=":)">',$comm);
  $comm = str_replace(':-)','<img src="/images/smilies/smiley.png" alt=":-)">',$comm);
  $comm = str_replace(':D','<img src="/images/smilies/smiley-lol.png" alt=":D">',$comm);
  $comm = str_replace(':-D','<img src="/images/smilies/smiley-lol.png" alt=":-D">',$comm);
  $comm = str_replace(';)','<img src="/images/smilies/smiley-wink.png" alt=";)">',$comm);
  $comm = str_replace(';-)','<img src="/images/smilies/smiley-wink.png" alt=";-)">',$comm);
    $comment .= '<div class="card message bg-light mb-2"'.($item['main'][3]==$_SESSION["logged"] ? ' style="background-color: #f5f5f5;"':'').'>
            <div class="card-body p-2">
                <div class="media d-block d-sm-flex">';
    if($item['main'][3]==0) $comment .= '    
                <div class="align-self-start mr-3 text-center float-left float-sm-none">
                    <img class="img-thumbnail rounded-circle align-self-start" src="'.($item['main'][5]!='' ? '/images/user_avatars/'.$item['main'][3].'.'.$item['main'][5].'?'.filemtime('images/user_avatars/'.$item['main'][3].'.'.$item['main'][5]) : '/img/players/no_photo.jpg').'" style="width: 50px; height: 50px;" alt="'.$nick.'">
                    <div class="text-muted text-center message-author"><small>'.$item['main'][1].'</small></div>
                </div>';
    else $comment .= '    
                <a href="/user/'.$item['main'][3].'-'.SEOtitle($nick).'" class="align-self-start mr-3 text-center float-left float-sm-none">
                    <img class="img-thumbnail rounded-circle align-self-start" src="'.($item['main'][5]!='' ? '/images/user_avatars/'.$item['main'][3].'.'.$item['main'][5].'?'.filemtime('images/user_avatars/'.$item['main'][3].'.'.$item['main'][5]) : '/img/players/no_photo.jpg').'" style="width: 50px; height: 50px;" alt="'.$nick.'">
                    <div class="text-muted text-center message-author"><small>'.$item['main'][1].'</small></div>
                </a>';
    $comment .='
                <div class="media-body">
                    <div class="text-right small">';
                    if($from_latest)
                      {
                      if($item['main'][9]==0)
                        {
                        $q = mysqli_query($link, "SELECT * FROM e_xoops_stories WHERE storyid='".$item['main'][10]."'");
                        $f = mysqli_fetch_array($q);
                        $hl = LANG_USERPROFILE_TOTHESTORY;
                        $hl1 = $f["title"];
                        $url = "/news/".$item['main'][10]."-".SEOtitle($f["title"]);
                        }
                      elseif($item['main'][9]==1)
                        {
                        $el = substr($item['main'][10], -1);
                        $ide = substr($item['main'][10], 0, -1);
                        if($el==1) $q = mysqli_query($link, "SELECT * FROM el_teams WHERE id='".$ide."'");
                        else $q = mysqli_query($link, "SELECT * FROM 2004teams WHERE id='".$ide."'");
                        $f = mysqli_fetch_array($q);
                        $hl = LANG_USERPROFILE_TOTHETEAM;
                        $hl1 = $f["longname"];
                        $url = "/team/".$item['main'][10]."-".SEOtitle($f["longname"]);
                        }
                      elseif($item['main'][9]==2)
                        {
                        $el = substr($item['main'][10], -1);
                        $ide = substr($item['main'][10], 0, -1);
                        if($el==1) $q = mysqli_query($link, "SELECT * FROM el_matches WHERE id='".$ide."'");
                        else $q = mysqli_query($link, "SELECT * FROM 2004matches WHERE id='".$ide."'");
                        $f = mysqli_fetch_array($q);
                        $hl = LANG_USERPROFILE_TOTHEGAME;
                        if($_SESSION["lang"]!='sk') { $f["team1long"] = TeamParser($f["team1long"]); $f["team2long"] = TeamParser($f["team2long"]); }
                        $hl1 = $f["team1long"]." vs. ".$f["team2long"];
                        $url = "/game/".$item['main'][10]."-".SEOtitle($f["team1long"]." vs ".$f["team2long"]);
                        }
                      elseif($item['main'][9]==3)
                        {
                        $el = substr($item['main'][10], -1);
                        $ide = substr($item['main'][10], 0, -1);
                        if($el=="p") 
                          {
                          $q = mysqli_query($link, "SELECT id, 1 as el, name FROM `el_players` WHERE name='".$ide."' UNION SELECT id, 0 as el, name FROM `2004players` WHERE name='".$ide."' ORDER BY id DESC");
                          $f = mysqli_fetch_array($q);
                          $url = "/player/".$f["id"].$f["el"]."-".SEOtitle($f["name"]);
                          }
                        else 
                          {
                          $q = mysqli_query($link, "SELECT id, 1 as el, name FROM `el_goalies` WHERE name='".$ide."' UNION SELECT id, 0 as el, name FROM `2004goalies` WHERE name='".$ide."' ORDER BY id DESC");
                          $f = mysqli_fetch_array($q);
                          $url = "/goalie/".$f["id"].$f["el"]."-".SEOtitle($f["name"]);
                          }
                        $hl = LANG_USERPROFILE_TOTHEPLAYER;
                        $hl1 = $ide;
                        }
                      $comment .='<p class="p-fluid"><a href="'.$url.'">'.$hl.' '.$hl1.'</a></p>';
                      }
                    $comment .= date("j.n.Y G:i",strtotime($item['main'][8]));
                    if(!$from_latest) $comment .=' <a href="#" class="btn btn-success btn-sm ml-2 replyComment" data-cid="'.($item['main'][7]!=0 ? $item['main'][7]:$item['main'][0]).'"><i class="fas fa-reply"></i></a>'.($item['main'][3]==$_SESSION["logged"] ? ' <a href="#" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteComment" data-cid="'.$item['main'][0].'"><i class="far fa-trash-alt"></i></a>':'');
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
  Global $link;
  $comments=[];
  $comms="";
  $w = mysqli_query($link, "SELECT c.*, u.uname, u.user_avatar FROM comments c LEFT JOIN e_xoops_users u ON u.uid=c.uid WHERE what='".$what."' && whatid='".$whatid."' ORDER BY c.datum");
  while($e=mysqli_fetch_array($w))
    {
    $id = $e["id"];
    if($e["replyto"]!=0) 
      {
      $replyto = $e["replyto"];
      $comments[$replyto]['replies'][$id]['main'] = array($e["id"], $e["uname"], 0, $e["uid"], $e["name"], $e["user_avatar"], $e["comment"], $e["replyto"], $e["datum"], $what, $whatid);
      }
    else $comments[$id]['main'] = array($e["id"], $e["uname"], 0, $e["uid"], $e["name"], $e["user_avatar"], $e["comment"], $e["replyto"], $e["datum"], $what, $whatid);
    }
  foreach($comments as $item) {
    $comms .= ShowComment($item);
    if(isset($item['replies'])) {
    foreach($item['replies'] as $reply) {
      $comms .= '<div class="offset-1">'.ShowComment($reply).'</div>';
      }
    }
  }
  return $comms;
  }
  
function GenerateComments($what, $whatid)
  {
  Global $leaguecolor, $link;
  if(isset($_SESSION["logged"]))
    {
    $q = mysqli_query($link, "SELECT * FROM e_xoops_users WHERE uid='".$_SESSION["logged"]."'");
    $f = mysqli_fetch_array($q);
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
      <input type="text" class="form-control" id="name" placeholder="'.LANG_TEAMSTATS_NAME.'"'.($_SESSION["logged"] ? ' value="'.$f["uname"].'" readonly':'').'>
      <input type="hidden" id="uid"'.($_SESSION["logged"] ? ' value="'.$_SESSION["logged"].'"':'').'>
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
  Global $link;
  $notif="";
  if(isset($_SESSION['logged']))
    {
    $q = mysqli_query($link, "SELECT * FROM user_notifications WHERE uid='".$_SESSION["logged"]."' ORDER BY datetime DESC LIMIT 5");
    $w = mysqli_query($link, "SELECT * FROM user_notifications WHERE uid='".$_SESSION["logged"]."' && isread='0'");
    $notif .= '<li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="'.LANG_NAV_NOTIFTITLE.'">
                <i class="fas fa-bell fa-fw"></i>
                '.(mysqli_num_rows($w)>0 ? '<span class="badge badge-danger badge-counter">'.mysqli_num_rows($w).'</span>':'').'
              </a>
              <!-- Dropdown - Notifications -->
              <div class="notifications dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                  '.LANG_NAV_NOTIFTITLE.'
                </h6>';
           while($f = mysqli_fetch_array($q))
               {
               // zmenit aj v user.php
               if($f["what"]==0)
                {
                $el = substr($f["whatid"], -1);
                $dl = strlen($f["whatid"]);
                $ide = substr($f["whatid"], 0, $dl-1);
                if($el==1) $e = mysqli_query($link, "SELECT * FROM el_matches WHERE id='".$ide."'");
                else $e = mysqli_query($link, "SELECT * FROM 2004matches WHERE id='".$ide."'");
                $r = mysqli_fetch_array($e);
                $icon = 'trophy';
                $color = 'success';
                $url = '/report/'.$f["whatid"].'-'.SEOtitle($r["team1long"]." vs. ".$r["team2long"]);
                $text = sprintf(LANG_NOTIF_FAVTEAM, $r["team1long"], $r["team2long"], $r["goals1"], $r["goals2"]);
                }
               elseif($f["what"]==1)
                {
                $exp = explode("-", $f["whatid"]);
                $e = mysqli_query($link, "SELECT * FROM 2004leagues WHERE id='".$exp[0]."'");
                $r = mysqli_fetch_array($e);
                $icon = 'plus';
                $color = 'primary';
                $url = '/bets';
                $text = sprintf(LANG_NOTIF_BET, $r["longname"], $exp[1]);
                }
               elseif($f["what"]==2)
                {
                $icon = 'user-clock';
                $color = 'danger';
                $url = '/fantasy/draft';
                $text = LANG_FANTASY_MAILSUBJECT;
                }
               elseif($f["what"]==3)
                {
                $e = mysqli_query($link, "SELECT c.*, u.uname FROM comments c LEFT JOIN e_xoops_users u ON u.uid=c.uid WHERE c.id='".$f["whatid"]."'");
                $r = mysqli_fetch_array($e);
                if($r["what"]==0) { $url = "/news/".$r["whatid"]."#comments"; }
                if($r["what"]==1) { $url = "/team/".$r["whatid"]."#comments"; }
                if($r["what"]==2) { $url = "/game/".$r["whatid"]."#comments"; }
                if($r["what"]==3) 
                  {
                  if(substr($r["whatid"], -1)=="p") $url = "/player/".substr($r["whatid"], 0, -1)."#comments";
                  if(substr($r["whatid"], -1)=="g") $url = "/goalie/".substr($r["whatid"], 0, -1)."#comments";
                  }
                $icon = 'reply';
                $color = 'danger';
                if($r["uid"]==0) $name = LANG_NOTIF_SOMEBODY;
                else $name = LANG_LOGED_AS." ".$r["uname"];
                $text = sprintf(LANG_NOTIF_REPLY, $name);
                }
               $notif .= '
                <a class="dropdown-item d-flex align-items-center'.($f["isread"]==0 ? ' alert-warning':'').'" href="'.$url.'" data-id="'.$f["id"].'">
                  <div class="mr-3">
                    <div class="icon-circle bg-'.$color.'">
                      <i class="fas fa-'.$icon.' text-white"></i>
                    </div>
                  </div>
                  <div>
                    <div class="small text-gray-500">'.date("j.n.Y H:i", strtotime($f["datetime"])).'</div>
                    <span'.($f["isread"]==0 ? ' class="font-weight-bold"':'').'>'.$text.'</span>
                  </div>
                </a>';
               }
    if(mysqli_num_rows($q)==0) $notif .= '<p class="dropdown-item text-gray-600">'.LANG_NAV_NOTIFNO.' ...</p>';
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
  Global $link;
  if($what==0)
    {
    // whatid = tshort-matchid
    $exp = explode("-", $whatid);
    $q = mysqli_query($link, "SELECT * FROM `e_xoops_users` WHERE user_favteam='".$exp[0]."'");
    while($f = mysqli_fetch_array($q))
      {
      mysqli_query($link, "INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$f["uid"]."', '0', '".$exp[1]."', NOW())");
      }
    }
  elseif($what==1)
    {
    // whatid = leagueid-kolo
    $exp = explode("-", $whatid);
    $q = mysqli_query($link, "SELECT et.userid, SUM(et.points) as poc FROM `el_matches` em LEFT JOIN el_tips et ON et.matchid=em.id WHERE em.league='".$exp[0]."' && em.kolo='".$exp[1]."' GROUP BY userid");
    while($f = mysqli_fetch_array($q))
      {
      mysqli_query($link, "INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$f["uid"]."', '1', '".$exp[0]."-".$f["poc"]."', NOW())");
      }
    }
  elseif($what==2)
    {
    mysqli_query($link, "INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$uid."', '2', '0', NOW())");
    }
  elseif($what==3)
    {
    // whatid = comment_id
    mysqli_query($link, "INSERT INTO user_notifications (uid, what, whatid, datetime) VALUES ('".$uid."', '3', '".$whatid."', NOW())");
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
  Global $link;
  $usermenu="";
  if(isset($_SESSION['logged']))
    {
    $q = mysqli_query($link, "SELECT * FROM e_xoops_users WHERE uid='".$_SESSION["logged"]."'");
    $f = mysqli_fetch_array($q);
    if(!isset($f)) { $f["user_avatar"]=""; $f["uname"]=""; }
    if($f["user_avatar"]!="") $avatar = '<img class="img-profile rounded-circle" src="/images/user_avatars/'.$_SESSION["logged"].'.'.$f["user_avatar"].'?'.filemtime('images/user_avatars/'.$_SESSION['logged'].'.'.$f["user_avatar"]).'" alt="'.$f["uname"].'">';
    else $avatar = '<i class="fas fa-user-circle fa-2x"></i>';
    if($f["user_favteam"]!=0) {
        $w = mysqli_query($link, "SELECT et.* FROM ((SELECT id, shortname as name, longname as pos, 0 as el FROM `2004teams` WHERE shortname='".$f["user_favteam"]."' ORDER BY id DESC LIMIT 5)
UNION
(SELECT id, shortname as name, longname as pos, 1 as el FROM `el_teams` WHERE shortname='".$f["user_favteam"]."' ORDER BY id DESC LIMIT 5))et GROUP BY et.name, et.pos");
        $e = mysqli_fetch_array($w);
    }
    $usermenu .= '
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="'.LANG_USERPROFILE_TITLE.'">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">'.$f["uname"].'</span>
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
                </a>';
                if(isset($e["id"])) {
                    $usermenu .= '
                <a class="dropdown-item" href="/team/'.$e["id"].$e["el"].'-'.SEOtitle($e["pos"]).'">
                  <i class="fas fa-star fa-sm fa-fw mr-2 text-gray-400"></i>
                  '.LANG_USERPROFILE_FAVTEAM.'
                </a>
                    ';
                }
    $usermenu .= '
                <a class="dropdown-item" href="/watched">
                  <i class="fas fa-magnifying-glass fa-sm fa-fw mr-2 text-gray-400"></i>
                  '.LANG_PLAYERS_WATCHEDTITLE.'
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
  $slovak_teams = array("Bielorusko","Dánsko","Čína","Česko","Európa","Fínsko","Francúzsko","Japonsko","Kanada","Kazachstan","Lotyšsko","Maďarsko","Nemecko","Nórsko","Rakúsko","Rusko","Severná Amerika","Slovensko","Slovinsko","Taliansko","Ukrajina","USA","Švajčiarsko","Švédsko");
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
* Zobrazenie najnovšieho spravodajského servisu z Google News
* @param $type string - p/g/t/n pre hráčov/brankárov/tímy/kategórie noviniek
* @param $id int - ID daného záznamu v jednotlivých tabuľkách DB
* @return $news string
*/

function GoogleNews($type, $id)
  {
  Global $link, $leaguecolor;
  $news = "";
    if($type=="p") {
        $el = substr($id, -1);
        $id = substr($id, 0, -1);
        if($el==0) $q = mysqli_query($link, "SELECT * FROM 2004players WHERE id='".$id."'");
        else $q = mysqli_query($link, "SELECT * FROM el_players WHERE id='".$id."'");
        $f = mysqli_fetch_array($q);
        if(!isset($f["name"])) $f["name"]="";
        $search = $f["name"];
        $w = mysqli_query($link, "SELECT * FROM (SELECT g.*, IF(SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.p')),-1)=0, p1.name, p2.name) as name FROM gn_news g LEFT JOIN 2004players p1 ON p1.id=SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.p')),1,LENGTH(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.p')))-1) LEFT JOIN el_players p2 ON p2.id=SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.p')),1,LENGTH(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.p')))-1))dt WHERE dt.name='".$search."' ORDER BY dt.published DESC LIMIT 5");
    }
    elseif($type=="g") {
        $el = substr($id, -1);
        $id = substr($id, 0, -1);
        if($el==0) $q = mysqli_query($link, "SELECT * FROM 2004goalies WHERE id='".$id."'");
        else $q = mysqli_query($link, "SELECT * FROM el_goalies WHERE id='".$id."'");
        $f = mysqli_fetch_array($q);
        if(!isset($f["name"])) $f["name"]="";
        $search = $f["name"];
        $w = mysqli_query($link, "SELECT * FROM (SELECT g.*, IF(SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.g')),-1)=0, p1.name, p2.name) as name FROM gn_news g LEFT JOIN 2004goalies p1 ON p1.id=SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.g')),1,LENGTH(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.g')))-1) LEFT JOIN el_goalies p2 ON p2.id=SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.g')),1,LENGTH(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.g')))-1))dt WHERE dt.name='".$search."' ORDER BY dt.published DESC LIMIT 5");
    }
    elseif($type=="t") {
        $el = substr($id, -1);
        $id = substr($id, 0, -1);
        if($el==0) $q = mysqli_query($link, "SELECT * FROM 2004teams WHERE id='".$id."'");
        else $q = mysqli_query($link, "SELECT * FROM el_teams WHERE id='".$id."'");
        $f = mysqli_fetch_array($q);
        if(!isset($f["shortname"])) $f["shortname"]="";
        $search = $f["shortname"];
        $w = mysqli_query($link, "SELECT * FROM (SELECT g.*, IF(SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.t')),-1)=0, p1.shortname, p2.shortname) as shortname FROM gn_news g LEFT JOIN 2004teams p1 ON p1.id=SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.t')),1,LENGTH(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.t')))-1) LEFT JOIN el_teams p2 ON p2.id=SUBSTRING(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.t')),1,LENGTH(JSON_UNQUOTE(JSON_EXTRACT(g.tags, '$.t')))-1))dt WHERE dt.shortname='".$search."' ORDER BY dt.published DESC LIMIT 5");
    }
    elseif($type=="n") {
        $w = mysqli_query($link, "SELECT * FROM gn_news WHERE JSON_UNQUOTE(JSON_EXTRACT(tags, '$.n'))='".$id."' ORDER BY published DESC LIMIT 5");
    }
  if(mysqli_num_rows($w)>0) {
    $news = '
    <div class="card shadow mb-2">
        <div class="card-header">
            <div class="font-weight-bold text-'.$leaguecolor.''.($type=="n" ? " text-uppercase":"").'">Spravodajský servis</div>
        </div>
        <div class="card-body">';
    $i=0;
    while($e = mysqli_fetch_array($w)) {
        $picture="";
        $tags = json_decode($e["tags"], true);
        if($e["image"]!="") $picture = $e["image"];
        elseif($type=="n" || $type=="t") {
            foreach($tags as $key => $tag) {
                if($key=="p" || $key=="g") {
                    $el = substr($tag, -1);
                    $id = substr($tag, 0, -1);
                    if($key=="p") { $nonel_table="2004players"; $el_table="el_players"; }
                    if($key=="g") { $nonel_table="2004goalies"; $el_table="el_goalies"; }
                    if($el==0) $q = mysqli_query($link, "SELECT * FROM ".$nonel_table." WHERE id='".$id."'");
                    else $q = mysqli_query($link, "SELECT * FROM $el_table WHERE id='".$id."'");
                    $f = mysqli_fetch_array($q);
                    $picture = "/includes/player_photo.php?name=".$f["name"];
                }
                elseif($type!="t" && $key=="t" && $picture=="") {
                    $el = substr($tag, -1);
                    $id = substr($tag, 0, -1);
                    if($el==0) $q = mysqli_query($link, "SELECT * FROM 2004teams WHERE id='".$id."'");
                    else $q = mysqli_query($link, "SELECT * FROM el_teams WHERE id='".$id."'");
                    $f = mysqli_fetch_array($q);
                    if($el==0) $picture = "/images/vlajky/".$f["shortname"].".gif";
                    else $picture = "/images/vlajky/".$f["shortname"]."_big.gif";
                }
            }
        }
        if($i % 2 == 0) {$tableclass = "";} 
        else $tableclass = " bg-light";
        if(strlen($e["summary"]) > 1000) {
            $stringCut = substr($e["summary"], 0, 1000);
            $e["summary"] = substr($stringCut, 0, strrpos($stringCut, ' ')).' ...';
        }
        $news .= "<table class='card d-table w-100 my-0 mb-2 position-relative small'>
                <tr class='card-header$tableclass'>
                <td style='width:60%;' class='pl-2'>
                    <b><a href='".$e["link"]."' target='_blank' class='stretched-link'>".$e["title"]."</a></b>
                </td>
                  <td style='width:40%;' class='text-right align-top pr-2'>".date("j.n.Y H:i", strtotime($e["published"]))."</td>
                </tr>
                <tr class='$tableclass'>
                  <td colspan='2' class='p-2'>".($picture!="" ? "<img src='data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 1 0.525\"%3E%3C/svg%3E' data-src='$picture' class='lazy bg-gray-100 float-left img-thumbnail mr-2 p-1 shadow-sm col-8 col-sm-5 col-md-4 col-lg-3'>" : "").$e["summary"]."</td>
                </tr>
                <tr class='$tableclass'>
                  <td colspan='2' class='px-2 text-right'>".$e["publisher"]."</td>
                </tr>
            </table>";
        $i++;
    }
    $news .= '
        </div>
    </div>';
  }
  return $news;
  }

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $weeks = intdiv($diff->days, 7);
    $days = $diff->days % 7;

    $string = array(
        'y' => $diff->y ? $diff->y . ' ' . LANG_TIME_YEARS : '',
        'm' => $diff->m ? $diff->m . ' ' . LANG_TIME_MONTHS : '',
        'w' => $weeks ? $weeks . ' ' . LANG_TIME_WEEKS : '',
        'd' => $days ? $days . ' ' . LANG_TIME_DAYS : '',
        'h' => $diff->h ? $diff->h . ' ' . LANG_TIME_HOURS : '',
        'i' => $diff->i ? $diff->i . ' ' . LANG_TIME_MINUTES : '',
        's' => $diff->s ? $diff->s . ' ' . LANG_TIME_SECONDS : '',
    );

    $string = array_filter($string);

    if (!$full) {
        $string = array_slice($string, 0, 1);
    }

    if (strtolower($_SESSION["lang"]) == "sk") {
        $hl = LANG_TIME_AGO . ' ' . implode(', ', $string);
    } else {
        $hl = implode(', ', $string) . ' ' . LANG_TIME_AGO;
    }

    return $string ? $hl : LANG_TIME_RIGHTNOW;
}


/*
* Funkcia na overenie zapamätaného prihlásenia
* version: 1.0.0 (3.7.2016 - vytvorenie funkcie)
* @return false
*/

function CheckCookieLogin() {
    Global $link;
    $uname = $_COOKIE['uname'] ?? null; 
    if (!empty($uname)) {
        $q = mysqli_query($link, "SELECT dt.* FROM (SELECT uid, JSON_SEARCH(login_session, 'one', '".$uname."') as search FROM `e_xoops_users`)dt WHERE dt.search IS NOT NULL");
        if(mysqli_num_rows($q)>0)
          {
          $f = mysqli_fetch_array($q);
          $_SESSION['logged'] = $f["uid"];
          setcookie("uname",$uname,time()+3600*24*365,'/','.hockey-live.sk');
          }
        }
}

function SendMail($to, $subject, $message) {
    Global $link;
    $q = mysqli_query($link, "SELECT * FROM e_xoops_users WHERE email='".$to."'");
    if(mysqli_num_rows($q)>0) {
        $f = mysqli_fetch_array($q);
        if($f["mail_notify"]==1) {
            // send e-mail
            $headers = 'From: '.SITE_MAIL. "\r\n" .
            'Reply-To: '.SITE_MAIL. "\r\n" .
            'X-Mailer: PHP/' . phpversion();
            mail($to, $subject, $message, $headers);
        }
        if($f["push_id"]!=NULL) {
            // send push notification
            SendPush($to, $subject, $message);
        }
    }
}

function SendPush($to, $subject, $message) {
    Global $link;
    $q = mysqli_query($link, "SELECT * FROM e_xoops_users WHERE email='".$to."'");
    if(mysqli_num_rows($q)>0) {
        $f = mysqli_fetch_array($q);

        $auth = array(
            'VAPID' => array(
                'subject' => 'https://www.hockey-live.sk/vendor/Webpush',
                'publicKey' => 'BIntrrrigzs9rUaiccEhsTjz4D-bLL5r25T7wp36awV9vNzaeDDlVxT3OcreF9VR0xG6dlzBb2XyPvCSEFenwjY',
                'privateKey' => WEBPUSH_PRIVATE_KEY,
            ),
        );

        $webPush = new WebPush($auth);

        $sub = json_decode($f["push_id"], true);
        $subscription = Subscription::create([
            'endpoint' => $sub["endpoint"],
            'publicKey' => $sub["key"],
            'authToken' => $sub["token"]
        ]);

        $res = $webPush->sendOneNotification(
            $subscription,
            $message,
        );
    }
}
?>