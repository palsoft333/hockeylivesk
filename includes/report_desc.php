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
if($el==1) $desc_table = "el_desc";
else $desc_table = "2004desc";

$q = mysql_query("SELECT * FROM $desc_table WHERE matchno='$id' && type != '5' ORDER BY time DESC, id DESC");
$n = mysql_num_rows($q);

$out .= '<div class="card my-4 shadow animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold">
                      '.LANG_MATCH_DESCRIPTION.'
                      <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                    </h6>
                  </div>
                  <div class="card-body">
                    <table class="table-hover table-light table-striped table-responsive-sm w-100 p-fluid"">
                      <thead><tr>
                        <th class="text-center">'.LANG_REPORT_TIME.'</th>
                        <th class="text-center"></th>
                        <th>'.LANG_REPORT_DESC.'</th>
                    </tr>
                  </thead>
                  <tbody>';

$i=$n;
while($f = mysql_fetch_array($q))
  {
  $pridaj="";
  if($f[tstamp]>=$ide) $pridaj=" deschigh";
  
  if($f[type]==0) $typ = ""; //normal
  if($f[type]==1) { $typ = "<i class='fas fa-hockey-puck'></i>"; $f[description] = "<b>$f[description]</b>"; } //goal
  if($f[type]==2) $typ = "<i class='fas fa-user-times text-danger'></i>"; //penalty
  if($f[type]==3) $typ = "<i class='fas fa-info-circle text-info'></i>"; //info
  if($f[type]==4) $typ = ""; //Singularity advert
  if($f[type]==5) $typ = ""; //SME advert
  
  if ($i % 20 == 0) {
  $out .= "<tr>
            <td class='$pridaj' colspan='3'><iframe src='/includes/advert_every20desc.php' width='100%' height='15' frameborder='0' scrolling='0' marginwidth='0' marginheight='0'></iframe></td>
           </tr>";
    }
  $i--;
  
  $out .= "<tr>
            <td class='text-center align-top$pridaj' style='width:7%;'>$f[time]</td>
            <td class='text-center align-top$pridaj' style='width:5%;'>$typ</td>
            <td class='$pridaj' style='width:88%;'>$f[description]</td>
           </tr>";
  }
  
  $out .= "</tbody></table>
          </div>
         </div>";
  
echo $out;

mysql_close($link);
?>