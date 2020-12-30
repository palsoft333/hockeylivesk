<?
/*
* Funkcia pre výpis tipov užívateľa
* version: 1.0.0 (10.5.2016 - vytvorenie novej funkcie)
* @param $user integer - ak nie je prázdne, vráti tipy daného ID užívateľa (inak tipy aktuálne prihláseného)
* @return $bets string
*/

function GetBets($user = false)
  {
  Global $title, $script_end;
  $locale = explode(";",setlocale(LC_ALL, '0'));
  $locale = explode("=",$locale[0]);
  $locale = $locale[1];
  if($user)
    {
    $q = mysql_query("SELECT uname FROM e_xoops_users WHERE uid='$user'");
    $f = mysql_fetch_array($q);
    }
  $title = ($user ? LANG_BETS_TITLE2.' '.$f[uname] : LANG_BETS_TITLE);
  
$script_end .= '<script type="text/javascript">
	$(document).ready(function() {
	$("#bets").dataTable( {
		"bProcessing": true,
		"bServerSide": true,
		"searching": false,
		"ordering": false,
    "aoColumnDefs": [ { "bVisible": false, "aTargets": [ 7 ] }],
    "oLanguage": { "sUrl": "/includes/lang/datatables_'.$locale.'.txt" },
    "bAutoWidth": false,
    "aoColumns": [{ "sWidth": "10%", className: "text-center" }, { "sWidth": "23%", className: "text-nowrap" }, { "sWidth": "27%", className: "text-nowrap" }, { "sWidth": "10%", className: "text-center" }, { "sWidth": "10%", className: "text-center" }, { "sWidth": "10%", className: "text-center" }, { "sWidth": "10%", className: "text-center" }, { "sWidth": "10%", className: "text-center" }],
		"sPaginationType": "numbers",
    "bJQueryUI": false,
		"sAjaxSource": "/includes/bets.php'.($user ? '?uid='.$user : '').'",
		 "drawCallback": function( settings ) {
        $(\'[data-toggle="tooltip"]\').tooltip();
        }
	} );
} );
	</script>';

$bets .= '  <div class="card my-4 shadow animated--grow-in">
              <div class="card-header">
                <h6 class="m-0 font-weight-bold text-hl">
                  '.$title.'
                  <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                </h6>
              </div>
              <div class="card-body">
                  <table class="table-hover table-light table-striped table-responsive-lg w-100 p-fluid" id="bets">
                  <thead><tr>
                      <th class="text-center">'.LANG_BETS_BET.'</th>
                      <th>'.LANG_BETS_LEAGUE.'</th>
                      <th>'.LANG_MATCH1.'</th>
                      <th class="text-center">'.LANG_BETS_BET.'</th>
                      <th class="text-center">'.LANG_MATCHES_RESULT.'</th>
                      <th class="text-center">'.LANG_TEAMSTATS_POINTS.'</th>
                      <th class="text-center">'.LANG_BETS_OPTIONS.'</th>
                      <th>datetime</th>
                      </tr></thead>
                      <tbody>
                      <tr>
                        <td colspan="10" class="dataTables_empty">'.LANG_STATS_LOADING.'</td>
                      </tr>
                    </tbody></table>
              </div>
            </div>';
	return $bets;
	}
?>