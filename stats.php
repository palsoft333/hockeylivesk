<?
if($_GET["lid"]) 
  {
  $params = explode("/", htmlspecialchars($_GET["lid"]));
  $lid = explode("-", htmlspecialchars($params[0]));
  $lid=$lid[0];
  }

$content = ""; 
// ligove statistiky
if(isset($lid))
  {
  $hl = "";
  $incr = 0;
  $players = "";
  $sel = mysqli_query($link, "SELECT * FROM 2004leagues WHERE id='$lid'");
  $vyb = mysqli_fetch_array($sel);
  if(!isset($vyb["id"])) {
    $leaguecolor = "primary";
    $content .= "Neexistujúca liga";
  }
  else {
    if(isset($_SESSION["logged"])) {
        $q = mysqli_query($link, "SELECT user_favteam FROM e_xoops_users WHERE uid='".$_SESSION["logged"]."'");
        $f = mysqli_fetch_array($q);
    }
    $locale = explode(";",setlocale(LC_ALL, '0'));
    $locale = explode("=",$locale[0]);
    $locale = $locale[1];
    if(isset($vyb["el"]) && $vyb["el"]==1)	
        {
        $players_table = "el_players";
        $matches_table = "el_matches";
        $goals_table = "el_goals";
        $goalies_table = "el_goalies";
        $title = LANG_STATS_TITLE.' '.$vyb["longname"];
        $hl = LANG_STATS_TITLE;
        $incr = 6;
        $sortby = 8;
        $players = '"aoColumns": [{ "sWidth": "5%", className: "text-center" }, { "sWidth": "30%", className: "text-nowrap" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }],';
        }
    elseif(isset($vyb["el"]) && $vyb["el"]==0)
        {
        $players_table = "2004players";
        $matches_table = "2004matches";
        $goals_table = "2004goals";
        $goalies_table = "2004goalies";
        $title = LANG_STATS_TITLE2.' '.$vyb["longname"];
        $hl = LANG_STATS_TITLE2;
        $incr = 6;
        $sortby = 8;
        $players = '"aoColumns": [{ "sWidth": "5%", className: "text-center" }, { "sWidth": "30%", className: "text-nowrap" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }, { "sWidth": "7%", className: "text-center" }],';
        }
    else
        {
        $players_table = "al_players";
        $matches_table = "al_matches";
        $goals_table = "al_goals";
        }
    $leaguecolor = $vyb["color"];
    $active_league = $lid;
    $content .= "<i class='float-left h1 h1-fluid ll-".LeagueFont($vyb["longname"])." text-gray-600 mr-1'></i>
                <h1 class='h3 h3-fluid mb-1'>".$hl."</h1>
                <h2 class='h6 h6-fluid text-".$leaguecolor." text-uppercase font-weight-bold mb-3'>".$vyb["longname"]."</h2>
                    <div class='row'>
                        <div class='col-12' style='max-width: 1000px;'>";
    
    $script_end = '<script type="text/javascript">
        $(document).ready(function() {
        $("#players").dataTable( {
                "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
                    $("td:eq('.$incr.')", nRow).html( "<b>"+aData[8]+"</b>" );
                    '.($_SESSION["logged"] ? 'if(aData[2]=="'.$f["user_favteam"].'") $(nRow).addClass("bg-gray-400");':'').'
                return nRow;
            },
            "bProcessing": true,
            "bServerSide": true,
        "aoColumnDefs": [ { "bVisible": false, "aTargets": [ 2 ] }, {"bVisible": false, "aTargets": [ 3 ] }, { "bSortable": false, "aTargets": [ 0 ] }],
        "oLanguage": { "sUrl": "/includes/lang/datatables_'.$locale.'.txt" },
        "aaSorting": [[8, "desc"], [5, "desc"], [6, "desc"], [7, "desc"]],
        "bAutoWidth": false,
        '.$players.'
            "sPaginationType": "full_numbers",
        "bJQueryUI": false,
            "sAjaxSource": "/includes/playerstats.php?lid='.$lid.'&el='.$vyb["el"].'"
        } );
        
        $("#goalies").dataTable( {
                "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
                    $("td:eq(5)", nRow).html( "<b>"+aData[7]+"</b>" );
                    '.($_SESSION["logged"] ? 'if(aData[2]=="'.$f["user_favteam"].'") $(nRow).addClass("bg-gray-400");':'').'
                return nRow;
            },
            
            "bProcessing": true,
            "bServerSide": true,
        "aoColumnDefs": [ { "bVisible": false, "aTargets": [ 2 ] }, {"bVisible": false, "aTargets": [ 3 ] }, { "bSortable": false, "aTargets": [ 0 ] }],
        "oLanguage": { "sUrl": "/includes/lang/datatables_'.$locale.'.txt" },
        "aaSorting": [[7, "desc"], [6, "desc"]],
        "bAutoWidth": false,
        "aoColumns": [{ "sWidth": "5%", className: "text-center" }, { "sWidth": "31%", className: "text-nowrap" }, { "sWidth": "8%", className: "text-center" }, { "sWidth": "8%", className: "text-center" }, { "sWidth": "8%", className: "text-center" }, { "sWidth": "8%", className: "text-center" }, { "sWidth": "8%", className: "text-center" }, { "sWidth": "8%", className: "text-center" }, { "sWidth": "8%", className: "text-center" }, { "sWidth": "8%", className: "text-center" }, { "sWidth": "8%", className: "text-center" }, { "sWidth": "8%", className: "text-center" }],
            "sPaginationType": "full_numbers",
            "bJQueryUI": false,
            "sAjaxSource": "/includes/goaliestats.php?lid='.$lid.'&el='.$vyb["el"].'"
        }
        );
        
        $("#attendance").dataTable( {
                "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
                    $("td:eq(5)", nRow).html( "<b>"+aData[5]+"</b>" );
                return nRow;
            },
        "bFilter": false,
            "bProcessing": true,
            "bServerSide": true,
        "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 0 ] }],
        "oLanguage": { "sUrl": "/includes/lang/datatables_'.$locale.'.txt" },
        "aaSorting": [[5, "desc"], [3, "desc"]],
        "bAutoWidth": false,
        "aoColumns": [{ "sWidth": "5%", className: "text-center" }, { "sWidth": "35%", className: "text-nowrap" }, { "sWidth": "30%", className: "text-nowrap" }, { "sWidth": "10%", className: "text-center" }, { "sWidth": "10%", className: "text-center" }, { "sWidth": "10%", className: "text-center" }],
            "sPaginationType": "full_numbers",
            "bJQueryUI": false,
            "sAjaxSource": "/includes/attendance.php?lid='.$lid.'"
        }
        );
    } );
    </script>';

    $content .= '<div class="card my-4 shadow animated--grow-in">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                    '.LANG_TEAMSTATS_PLAYERS.'
                    <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table-hover table-light table-striped table-responsive-lg w-100 p-fluid" id="players">
                    <thead><tr>
                        <th class="text-center">#</th>
                        <th>'.LANG_PLAYERDB_PLAYER.'</th>
                        <th>'.LANG_PLAYERSTATS_TEAM.'</th>
                        <th>ID</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_PLAYERSTATS_POS.'">POS</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAMES.'">GP</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GOALS.'">G</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_ASISTS.'">A</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_POINTS.'">P</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PPG.'">PPG</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SHG.'">SHG</th>
                        <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GWG.'">GWG</th>
                        </tr></thead>
                        <tbody>
                        <tr>
                            <td colspan="10" class="dataTables_empty">'.LANG_STATS_LOADING.'</td>
                        </tr>
                        </tbody></table>
                </div>
                </div>
                
        <div class="card my-4 shadow animated--grow-in">
            <div class="card-header">
            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                '.LANG_TEAMSTATS_GOALIES.'
                <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
            </h6>
            </div>
            <div class="card-body">
            <table class="table-hover table-light table-striped table-responsive-lg w-100 p-fluid" id="goalies">
            <thead><tr>
                <th class="text-center">#</th>
                <th>'.LANG_PLAYERDB_PLAYER.'</th>
                <th>'.LANG_PLAYERSTATS_TEAM.'</th>
                <th>ID</th>
                <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAMES.'">GP</th>
                <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SOG.'">SOG</th>
                <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SVS.'">SVS</th>
                <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SVP.'">SV%</th>
                <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GA.'">GA</th>
                <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_GAA.'">&oslash;GA</th>
                <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_SO.'">SO</th>
                <th class="text-center" data-toggle="tooltip" data-placement="top" title="'.LANG_TEAMSTATS_PIM.'">PIM</th>
                </tr></thead>
                <tbody>
                <tr>
                    <td colspan="10" class="dataTables_empty">'.LANG_STATS_LOADING.'</td>
                </tr>
                </tbody></table>
            </div>
        </div>';
        
        if($vyb["el"]==1) {
        
        $content .= '
        <div class="card my-4 shadow animated--grow-in">
            <div class="card-header">
            <h6 class="m-0 font-weight-bold text-'.$leaguecolor.'">
                '.LANG_STATS_ATTENDANCE.'
                <span class="swipe d-none float-right text-gray-800"><i class="fas fa-hand-point-up"></i> <i class="fas fa-exchange-alt align-text-top text-xs"></i></span>
            </h6>
            </div>
            <div class="card-body">
            <table class="table-hover table-light table-striped table-responsive-lg w-100 p-fluid" id="attendance">
            <thead><tr>
                <th class="text-center">#</th>
                <th>'.LANG_STATS_CLUB.'</th>
                <th>'.LANG_TEAMSTATS_ARENA.'</th>
                <th class="text-center">'.LANG_STATS_AVGATTEND.'</th>
                <th class="text-center">'.LANG_TEAMSTATS_ARENACAP.'</th>
                <th class="text-center">'.LANG_STATS_AVAILABILITY.'</th>
                </tr></thead>
                <tbody>
                <tr>
                    <td colspan="6" class="dataTables_empty">'.LANG_STATS_LOADING.'</td>
                </tr>
                </tbody></table>
            </div>
        </div>';
        }
        
    $content .= '
    </div> <!-- end col -->
    <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block mt-4">';
                include("includes/advert_bigscreenside.php");
                $content .= $advert;
            $content .= '
    </div> <!-- end col -->
    </div> <!-- end row -->';

    // nebol vybrany ziaden tim
  }
  }
else
  {
  $leaguecolor = "primary";
  $content .= "Neexistujúca liga";
  }
?>