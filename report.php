<?
if($_GET["gid"]) 
  {
  $params = explode("/", htmlspecialchars($_GET["gid"]));
  $id = explode("-", htmlspecialchars($params[0]));
  $id=$id[0];
  }

$content = $suffix = "";
// report zo zápasu

$estats="";
$el = substr($id, -1);
$dl = strlen($id);
$id = substr($id, 0, $dl-1);
if($el==1) { $matches_table = "el_matches"; $matchstats_table = "el_matchstats"; }
else { $matches_table = "2004matches"; $matchstats_table = "2004matchstats"; $suffix = ' shadow-sm'; }

$q = mysqli_query($link, "SELECT m.*, l.color, l.longname, DATE_FORMAT(m.datetime, '%e.%c.%Y %k:%i') as datum FROM $matches_table m LEFT JOIN 2004leagues l ON l.id=m.league WHERE m.id='$id'");
if(mysqli_num_rows($q)>0)
  {
  $f = mysqli_fetch_array($q);
  
  if($_SESSION['logged'])
    {
    $w = mysqli_query($link, "SELECT * FROM e_xoops_users WHERE uid='".$_SESSION['logged']."'");
    $e = mysqli_fetch_array($w);
    }
    
  $ms = mysqli_query($link, "SELECT * FROM $matchstats_table WHERE matchid='".$f["id"]."'");
  if(mysqli_num_rows($ms)>0) Generate_extrastats($f["id"], $el); 
  $es=1;

  $leaguecolor = $f["color"];
  $active_league = $f["league"];
  if($_SESSION["lang"]!='sk') { $f["team1long"] = TeamParser($f["team1long"]); $f["team2long"] = TeamParser($f["team2long"]); }
  $title = LANG_MATCH1." ".$f["team1long"]." - ".$f["team2long"];
  
  if(isset($e["goalhorn"]) && $e["goalhorn"]==1) $content .= '<script src="https://code.responsivevoice.org/responsivevoice.js" defer></script>';
  else $content .= '<audio preload="auto" id="goalhorn">
    <source src="/includes/sounds/goal.mp3"></source>
    <source src="/includes/sounds/goal.ogg"></source>
    </audio>';

    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($f["longname"])." text-gray-600 mr-1'></i>
                 <h1 class='h3 h3-fluid mb-1'>".LANG_REPORT_TITLE." ".$f["team1long"]." - ".$f["team2long"]."</h1>
                 <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$f["longname"]."</h2>
                 <div class='row'>
                    <div class='col-12' style='max-width: 1000px;'>
                 <h3 class='small text-center'><b>".LIVE_GAME_START.":</b> ".$f["datum"]."</h3>";
    
    $content .= '
      <div id="othermatches" class="card" style="display:none;">
        <table>
            <thead>
              <tr>
                <th class="card-header">'.LANG_OTHER_GAMES_LIVE.'</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><span id="other"></span></td>
              </tr>
            </tbody>
         </table>
      </div>
      
      <div class="row my-4">
        <div class="col-6 col-md-4 order-1 text-center animated--grow-in">
          <img src="/images/vlajky/'.$f["team1short"].'_big.gif" alt="'.$f["team1long"].'" class="img-fluid'.$suffix.'">
          <div class="h5 h5-fluid mb-0 mt-1 font-weight-bold text-gray-800">'.$f["team1long"].'</div>
        </div>
        <div class="col-md-4 order-3 order-md-2 text-center text-gray-800">
          <p class="display-3"><b><span id="goals1"></span>:<span id="goals2"></span></b></p>
          <p class="h5 h5-fluid"><b><span id="kedy"></b></span></p>
        </div>
        <div class="col-6 col-md-4 order-2 order-md-3 text-center animated--grow-in">
          <img src="/images/vlajky/'.$f["team2short"].'_big.gif" alt="'.$f["team2long"].'" class="img-fluid'.$suffix.'">
          <div class="h5 h5-fluid mb-0 mt-1 font-weight-bold text-gray-800">'.$f["team2long"].'</div>
        </div>
      </div>
    
    '.($es==1 ? $estats : '').'
    
    <div class="report-goals">
      <div class="card my-4 shadow animated--grow-in">
        <div class="card-header">
          <h6 class="m-0 font-weight-bold">
            '.LANG_TEAMSTATS_GOALS.'
          </h6>
        </div>
        <div class="card-body d-flex" style="min-height: '.(42*($f["goals1"]+$f["goals2"])).'px;">
          <div class="align-self-center flex-fill text-center">
            <div class="spinner-border text-gray-400" role="status">
              <span class="sr-only">Loading...</span>
            </div>
          </div>
        </div>
       </div>
    </div>
    
    <div class="row report-penalties">
      <div class="col-md-6 report-pen1">
        <div class="card my-4 shadow animated--grow-in">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold">
              '.LANG_PENALTY.' <img class="flag-'.($el==0 ? 'iihf':'el').' '.$f["team1short"].'-small" src="/img/blank.png" alt="'.$f["team1long"].'"> '.$f["team1long"].'
            </h6>
          </div>
          <div class="card-body">
            <div class="text-center">
              <div class="spinner-border text-gray-400" role="status">
                <span class="sr-only">Loading...</span>
              </div>
            </div>
          </div>
         </div>
      </div>
      <div class="col-md-6 report-pen2">
        <div class="card my-4 shadow animated--grow-in">
          <div class="card-header">
            <h6 class="m-0 font-weight-bold">
              '.LANG_PENALTY.' <img class="flag-'.($el==0 ? 'iihf':'el').' '.$f["team2short"].'-small" src="/img/blank.png" alt="'.$f["team2long"].'"> '.$f["team2long"].'
            </h6>
          </div>
          <div class="card-body">
            <div class="text-center">
              <div class="spinner-border text-gray-400" role="status">
                <span class="sr-only">Loading...</span>
              </div>
            </div>
          </div>
         </div>
      </div>
    </div>
    
    <div class="report-desc">
      <div class="card my-4 shadow animated--grow-in">
        <div class="card-header">
          <h6 class="m-0 font-weight-bold">
            '.LANG_MATCH_DESCRIPTION.'
          </h6>
        </div>
        <div class="card-body">
          <div class="text-center">
            <div class="spinner-border text-gray-400" role="status">
              <span class="sr-only">Loading...</span>
            </div>
          </div>
        </div>
       </div>
    </div>
    <div class="card shadow my-4">
        <div class="card-body">
        '.GenerateComments(2,$id.$el).'
        </div>
    </div>
   </div> <!-- end col -->
   <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block">';
            include("includes/advert_bigscreenside.php");
            $content .= $advert;
        $content .= '
   </div> <!-- end col -->
   </div> <!-- end row -->';
  }
// nebol vybrany ziaden zapas
else
  {
  $leaguecolor = "hl";
  $content .= "<div class='alert alert-warning' role='alert'><i class='fas fa-hockey-puck'></i> Neexistujúci zápas</div>";
  }
?>