<?
$params = explode("/", htmlspecialchars($_GET[uid]));
$content = "";
$uid = $_SESSION['logged'];
$leaguecolor="hl";
// prehlad tipov
if($uid)
  {
  // prehlad tipov ineho uzivatela
  if($params[0])
    {
    $content .= GetBets($params[0]);
    }
  // prehlad tipov prihlaseneho uzivatela
  else
    {
    $q = mysql_query("SELECT uname FROM e_xoops_users WHERE uid='$uid'");
    $f = mysql_fetch_array($q);
    $locale = explode(";",setlocale(LC_ALL, '0'));
    $locale = explode("=",$locale[0]);
    $locale = $locale[1];
    
$script_end = '  <script type="text/javascript">
    $(document).ready(function() {
    var betstable = $("#betstable").DataTable( {
      "searching": false,
      "ordering": false,
      "oLanguage": { "sUrl": "/includes/lang/datatables_'.$locale.'.txt" },
      "bAutoWidth": false,
      "aoColumns": [{ "sWidth": "5%", className: "text-center" }, { "sWidth": "35%", className: "text-nowrap" }, { "sWidth": "20%", className: "text-center" }, { "sWidth": "20%", className: "text-center" }, { "sWidth": "20%", className: "text-center" }],
      "sPaginationType": "numbers",
      "bJQueryUI": false,
      "ajax": "/includes/betstable.php",
      "createdRow": function( row, data, dataIndex ) {
        if ( data[1] == "<a href=\'/bets/'.$uid.'\'>'.$f[uname].'</a>" ) {
          $(row).addClass(\'highlight\');
        }
       }
    } );
      
    $("#leagues").on("change", function(){
                  betstable.ajax.url("/includes/betstable.php?lid="+$(this).val()).load();
              });
  } );
    </script>';
    
  $content .= "<h1 class='h3 h3-fluid mb-1'>".LANG_NAV_BETOVERVIEW."</h1>
               <div style='max-width: 1000px;'>";
    
$content .= '<div class="league-select">
              '.LANG_BETS_SHOWFOR.': 
              <select class="custom-select custom-select-sm w-auto" name="leagues" id="leagues">
                <option value="0">'.LANG_BETS_OVERALL.'</option>
                <optgroup label="'.LANG_BETS_ACTUAL.'">';
                $l1 = mysql_query("SELECT * FROM 2004leagues WHERE active='1' && id!='70' ORDER BY position ASC");
                while($l1d = mysql_fetch_array($l1))
                  {
                  $content .= '<option value="'.$l1d[id].'">'.$l1d[longname].'</option>';
                  }
                $content .= '</optgroup><optgroup label="'.LANG_BETS_PLAYED.'">';
                $l2 = mysql_query("SELECT * FROM 2004leagues WHERE active='0' && id!='70' ORDER BY longname ASC");
                while($l2d = mysql_fetch_array($l2))
                  {
                  $content .= '<option value="'.$l2d[id].'">'.$l2d[longname].'</option>';
                  }
                $content .= '</optgroup>
              </select>
            </div>

            <div class="card my-4 shadow animated--grow-in">
              <div class="card-header">
                <h6 class="m-0 font-weight-bold text-hl">
                  '.LANG_BETS_BEST.'
                  <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                </h6>
              </div>
              <div class="card-body">
                  <table class="table-hover table-light table-striped table-responsive-lg w-100 p-fluid" id="betstable">
                  <thead><tr>
                      <th class="text-center">'.LANG_BETS_RANK.'</th>
                      <th>'.LANG_LOGED_AS.'</th>
                      <th class="text-center">'.LANG_TEAMSTATS_POINTS.'</th>
                      <th class="text-center">'.LANG_BETS_BETCOUNT.'</th>
                      <th class="text-center">'.LANG_BETS_SUCCESS.'</th>
                      </tr></thead>
                      <tbody>
                      <tr>
                        <td colspan="10" class="dataTables_empty">'.LANG_STATS_LOADING.'</td>
                      </tr>
                    </tbody></table>
              </div>
            </div>
            '.GetBets();
        
    
    $content .= '<div class="card my-4 shadow animated--grow-in">
              <div class="card-header">
                <h6 class="m-0 font-weight-bold text-hl">
                  '.LANG_BETS_HOWWESCORE.'
                </h6>
              </div>
              <div class="card-body">
                 <p>'.LANG_BETS_HOWWESCORE1.'</p>
                 <ul>
                  <li>'.LANG_BETS_HOWWESCORE2.'</li>
                  <li>'.LANG_BETS_HOWWESCORE3.'</li>
                  <li>'.LANG_BETS_HOWWESCORE4.'</li>
                  <li>'.LANG_BETS_HOWWESCORE5.'</li>
                 </ul>
              </div>
            </div>
            
            <div class="card my-4 shadow animated--grow-in">
              <div class="card-header">
                <h6 class="m-0 font-weight-bold text-hl">
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
                      <p class="card-text"><small class="text-muted">veľkosť L, XL</small></p>
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
    }
  }
// nie je prihlaseny
else
  {
  $content = "<div class='alert alert-warning' role='alert'><i class='fas fa-chart-line'></i> Pre prehľad vašich tipov sa musíte <a href='/login' class='alert-link'>prihlásiť</a>.</div>";
  }
?>