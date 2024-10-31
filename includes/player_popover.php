<?
session_start();
include("db.php");
  if(isset($_SESSION["lang"])) {
    include("lang/lang_".$_SESSION["lang"].".php");
  }
  else {
     $_SESSION["lang"] = 'sk';
      include("lang/lang_sk.php");
  }
include("players_functions.php");

if($_GET["p"]) {
    $name = explode("|", $_GET["p"]);
    if($name[1]==1) $gk=1;
    else $gk=0;
    $name = mysqli_real_escape_string($link, $name[0]);
    if($gk==0) {
        // hrac
        $pinfo = GetBio($name, 0);
        $stat = mysqli_query($link, "SELECT dt.*, l.longname, IF(m1.datetime IS NOT NULL,m1.datetime,m3.datetime) as datum FROM (SELECT * FROM `2004players` WHERE name='$name' && gp>0 UNION SELECT * FROM `el_players` WHERE name='$name' && gp>0)dt LEFT JOIN 2004leagues l ON l.id=dt.league LEFT JOIN 2004matches m1 ON m1.league=dt.league && m1.datetime=(SELECT MAX(m2.datetime) FROM 2004matches m2 WHERE league=dt.league) LEFT JOIN el_matches m3 ON m3.league=dt.league && m3.datetime=(SELECT MAX(m4.datetime) FROM el_matches m4 WHERE league=dt.league) ORDER BY datum DESC LIMIT 1;");
        $stats = mysqli_fetch_array($stat);
    }
    else {
        // brankar
        $pinfo = GetBio($name, 1);
        $stat = mysqli_query($link, "SELECT dt.*, l.longname, IF(m1.datetime IS NOT NULL,m1.datetime,m3.datetime) as datum FROM (SELECT * FROM `2004goalies` WHERE name='$name' && gp>0 UNION SELECT * FROM `el_goalies` WHERE name='$name' && gp>0)dt LEFT JOIN 2004leagues l ON l.id=dt.league LEFT JOIN 2004matches m1 ON m1.league=dt.league && m1.datetime=(SELECT MAX(m2.datetime) FROM 2004matches m2 WHERE league=dt.league) LEFT JOIN el_matches m3 ON m3.league=dt.league && m3.datetime=(SELECT MAX(m4.datetime) FROM el_matches m4 WHERE league=dt.league) ORDER BY datum DESC LIMIT 1;");
        $stats = mysqli_fetch_array($stat);
    }
    echo '
    <div class="row">
        <div class="col-5 align-self-center">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/includes/player_photo.php?name='.$name.'" class="lazy rounded-circle img-thumbnail shadow-sm p-1" style="min-width:90px; min-height:90px; width:90px; height:90px; object-fit: cover; object-position: top;">
        </div>
        <div class="col-7 align-self-center">
            <p class="small m-0">';
                $i=0;
                while($i < count($pinfo)) {
                    echo '- '.$pinfo[$i].'<br>';
                    $i++;
                }
            echo '</p>';
        echo '</div>
    </div>';

if($gk==0)
    echo '
    <div class="row mt-2 bg-gray-200 align-items-end">
        <div class="col-7 font-weight-bold">
            '.$stats["longname"].'
        </div>
        <div class="col-1 text-xs font-weight-bold text-nowrap">GP</div>
        <div class="col-1 text-xs font-weight-bold">G</div>
        <div class="col-1 text-xs font-weight-bold">A</div>
        <div class="col-1 text-xs font-weight-bold">P</div>
    </div>
    <div class="row">
        <div class="col-7 text-xs align-items-start">
            '.$stats["teamlong"].'
        </div>
        <div class="col-1 text-xs text-nowrap">
            '.$stats["gp"].'
        </div>
        <div class="col-1 text-xs text-nowrap">
            '.$stats["goals"].'
        </div>
        <div class="col-1 text-xs text-nowrap">
            '.$stats["asists"].'
        </div>
        <div class="col-1 text-xs font-weight-bold text-nowrap">
            '.$stats["points"].'
        </div>
    </div>';
else echo '
    <div class="row mt-2 bg-gray-200 align-items-end">
        <div class="col-7 font-weight-bold">
            '.$stats["longname"].'
        </div>
        <div class="col-2 text-xs font-weight-bold text-nowrap">GP</div>
        <div class="col-2 text-xs font-weight-bold text-nowrap">SV%</div>
    </div>
    <div class="row">
        <div class="col-7 text-xs align-items-start">
            '.$stats["teamlong"].'
        </div>
        <div class="col-2 text-xs text-nowrap">
            '.$stats["gp"].'
        </div>
        <div class="col-2 text-xs font-weight-bold text-nowrap">
            '.round(($stats["svs"]/$stats["sog"])*100,1).'
        </div>
    </div>';
}
else echo "Nepodarilo sa mi načítať údaje o hráčovi";

mysqli_close($link);
?>