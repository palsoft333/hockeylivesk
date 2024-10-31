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

header('Content-Type: text/html; charset=utf-8');

if(preg_match('/[0-9]+/', $_GET["id"])==1)
  {
  $id = $_GET["id"];
  $ide = urldecode($_COOKIE['hl-'.$id]);
  }
else
  {
  exit;
  }
  
$el = substr($_GET["id"], -1);
$dl = strlen($_GET["id"]);
$id = substr($_GET["id"], 0, $dl-1);
if($el==1) 
  {
  $matches_table = "el_matches";
  $penalty_table = "el_penalty";
  }
else 
  {
  $matches_table = "2004matches";
  $penalty_table = "2004penalty";
  }

$q = mysqli_query($link, "SELECT * FROM ".$matches_table." WHERE id='".$id."'");

$f = mysqli_fetch_array($q);
if($_GET["t"]==1)
  {
  $tshort = $f["team1short"];
  $tlong = $f["team1long"];
  $prid = "pen1high";
  }
else
  {
  $tshort = $f["team2short"];
  $tlong = $f["team2long"];
  $prid = "pen2high";
  }
$q = mysqli_query($link, "SELECT *, CAST(time as DECIMAL(5,2)) as cas FROM ".$penalty_table." WHERE matchno='".$id."' && teamshort='".$tshort."' ORDER BY cas ASC");

$out .= '<div class="card my-4 shadow animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold">
                      '.LANG_PENALTY.' <img class="flag-'.($el==0 ? 'iihf':'el').' '.$tshort.'-small" src="/img/blank.png" alt="'.$tlong.'"> '.$tlong.'
                      <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                    </h6>
                  </div>
                  <div class="card-body">
                    <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid"">
                      <thead><tr>
                        <th class="text-center">'.LANG_REPORT_TIME.'</th>
                        <th class="text-nowrap">'.LANG_PLAYERDB_PLAYER.'</th>
                        <th>'.LANG_REPORT_PENALTY.'</th>
                    </tr>
                  </thead>
                  <tbody>';

while($f = mysqli_fetch_array($q))
  {
  $pridaj="";
  if($f["tstamp"]>=$ide) $pridaj=" ".$prid;
  $out .= "<tr>
            <td class='text-center align-top$pridaj' style='width:15%;'>$f[time]</td>
            <td class='text-nowrap align-top$pridaj' style='width:45%;'>$f[player]</td>
            <td class='align-top$pridaj' style='width:40%;'>$f[kedy]<br><span class='small'><b>$f[minutes] min</b></span></td>
           </tr>";
  }

$out .= "</tbody></table>
        </div>
       </div>";

echo $out;

mysqli_close($link);
?>