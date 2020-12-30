<?
include("db.php");
if(isset($_SESSION[lang])) {
  include("lang/lang_$_SESSION[lang].php");
}
else {
   $_SESSION[lang] = 'sk';
    include("lang/lang_sk.php");
}

header('Content-Type: text/html; charset=utf-8');

if(preg_match('/[0-9]+/', $_GET[id])==1)
  {
  $id = $_GET[id];
  $ide = urldecode($_COOKIE['hl-'.$id]);
  }
else
  {
  exit;
  }
  
$el = substr($_GET[id], -1);
$dl = strlen($_GET[id]);
$id = substr($_GET[id], 0, $dl-1);
if($el==1) $goals_table = "el_goals";
else $goals_table = "2004goals";

$q = mysql_query("SELECT * FROM $goals_table WHERE matchno='$id' ORDER BY time AsC, id ASC");

$out .= '<div class="card my-4 shadow animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold">
                      '.LANG_TEAMSTATS_GOALS.'
                      <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                    </h6>
                  </div>
                  <div class="card-body">
                    <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid"">
                      <thead><tr>
                        <th class="text-center">'.LANG_REPORT_TIME.'</th>
                        <th class="text-nowrap">'.LANG_PLAYERSTATS_TEAM.'</th>
                        <th class="text-nowrap">'.LANG_REPORT_GOALER.'</th>
                        <th class="text-center">'.LANG_MATCHES_STATUS.'</th>
                        <th class="text-center"></th>
                    </tr>
                  </thead>
                  <tbody>';

while($f = mysql_fetch_array($q))
  {
  $pridaj="";
  if($f[kedy]=="pres/1") $kedy = "<span class='badge badge-pill text-xs badge-success' data-toggle='tooltip' data-placement='top' title='".LANG_REPORT_PP1."'>PP1</span>";
  if($f[kedy]=="pres/2") $kedy = "<span class='badge badge-pill text-xs badge-success' data-toggle='tooltip' data-placement='top' title='".LANG_REPORT_PP2."'>PP2</span>";
  if($f[kedy]=="oslab/1") $kedy = "<span class='badge badge-pill text-xs badge-danger' data-toggle='tooltip' data-placement='top' title='".LANG_REPORT_SH1."'>SH1</span>";
  if($f[kedy]=="oslab/2") $kedy = "<span class='badge badge-pill text-xs badge-danger' data-toggle='tooltip' data-placement='top' title='".LANG_REPORT_SH2."'>SH2</span>";
  if($f[kedy]=="trestne") $kedy = "<span class='badge badge-pill text-xs badge-primary' data-toggle='tooltip' data-placement='top' title='".LANG_REPORT_PS."'>PS</span>";
  if($f[kedy]=="normal") $kedy = "";
  if($f[asister1]=="bez asistencie" && $f[asister2]=="bez asistencie") $gol = "<div class='goaler'>$f[goaler]</div>";
  if($f[asister1]!="bez asistencie" && $f[asister2]=="bez asistencie") $gol = "<div class='goaler'>$f[goaler]</div><div class='asisters font-weight-light text-xs'>($f[asister1])</div>";
  if($f[asister1]!="bez asistencie" && $f[asister2]!="bez asistencie") $gol = "<div class='goaler'>$f[goaler]</div><div class='asisters font-weight-light text-xs'>($f[asister1], $f[asister2])</div>";
  if($f[tstamp]>=$ide) $pridaj=" highlight";
  $out .= "<tr>
            <td class='text-center align-top$pridaj' style='width:10%;'>$f[time]</td>
            <td class='text-nowrap align-top$pridaj' style='width:10%;'><img class='flag-".($el==0 ? 'iihf':'el')." ".$f[teamshort]."-small' src='/img/blank.png' alt='".$f[teamlong]."'> $f[teamshort]</td>
            <td class='text-nowrap align-top$pridaj' style='width:60%;'>$gol</td>
            <td class='text-center align-top$pridaj' style='width:10%;'>$f[status]</td>
            <td class='text-center align-top$pridaj' style='width:10%;'>$kedy</td>
           </tr>";
  }
  
$out .= "</tbody></table>
        </div>
       </div>";

echo $out;

mysql_close($link);
?>