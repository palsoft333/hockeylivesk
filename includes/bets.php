<?php
  session_start();
  include("db.php");
  include("main_functions.php");
  if(isset($_SESSION["lang"])) {
    include("lang/lang_".$_SESSION["lang"].".php");
  }
  else {
     $_SESSION["lang"] = 'sk';
      include("lang/lang_sk.php");
  }
  $uid = $_SESSION['logged'];
  if(isset($_GET["uid"])) $uid=$_GET["uid"];
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$aColumns = array( 'id', 'longname', ' ', ' ', ' ', 'points', ' ', 'datetime');
	
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "id";
	
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * If you just want to use the basic configuration for DataTables with PHP server-side, there is
	 * no need to edit below this line
	 */
	
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysqli_real_escape_string($link, $_GET['iDisplayStart'] ).", ".
			mysqli_real_escape_string($link, $_GET['iDisplayLength'] );
	}
	
	
	$sQuery = "SELECT SQL_CALC_FOUND_ROWS t1.id, t1.matchid, t1.tip1, t1.tip2, t1.points, 0 as el, t1.league, t5.longname, t2.team1long, t2.team2long, t2.goals1, t2.goals2, t2.kedy, t2.datetime FROM 2004tips t1 JOIN 2004matches t2 ON t2.id=t1.matchid JOIN 2004leagues t5 ON t5.id=t1.league WHERE t1.userid = '$uid' UNION SELECT t3.id, t3.matchid, t3.tip1, t3.tip2, t3.points, t3.el, t3.league, t6.longname, t4.team1long, t4.team2long, t4.goals1, t4.goals2, t4.kedy, t4.datetime FROM el_tips t3 JOIN el_matches t4 ON t4.id=t3.matchid JOIN 2004leagues t6 ON t6.id=t3.league WHERE t3.userid = '$uid'
    ORDER BY datetime DESC
		$sLimit";
	$rResult = mysqli_query($link, $sQuery ) or die(mysqli_error($link));
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = mysqli_query($link, $sQuery ) or die(mysqli_error($link));
	$aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = mysqli_query($link, "SELECT t1.id, t1.matchid, t1.tip1, t1.tip2, t1.points, 0 as el, t1.league FROM 2004tips t1 WHERE t1.userid = '".$uid."' UNION SELECT t3.id, t3.matchid, t3.tip1, t3.tip2, t3.points, t3.el, t3.league FROM el_tips t3 WHERE t3.userid = '".$uid."'");
	$rResultTotal = mysqli_num_rows($sQuery);
	$iTotal = $rResultTotal;
	

	/*
	 * Output
	 */
	 $j=1;
	$sOutput = '{';
	$sOutput .= '"sEcho": '.intval($_GET['sEcho']).', ';
	$sOutput .= '"iTotalRecords": '.$iTotal.', ';
	$sOutput .= '"iTotalDisplayRecords": '.$iFilteredTotal.', ';
	$sOutput .= '"aaData": [ ';
	while ( $aRow = mysqli_fetch_array( $rResult ) )
	{
	$edit='';
	$t1l = $aRow["team1long"];
	$t2l = $aRow["team2long"];
	if($aRow["kedy"]=="konečný stav") $pag = "report";
	else $pag = "game";
	if($aRow["el"]==0)
    {
    $day = explode(" ", $aRow["datetime"]);
    $vl = "/".$day[0];
    }
	if(strtotime($aRow["datetime"]) > time()) $edit = '<button class=\'btn btn-sm btn-block btn-hl\' onclick=\'location.href=\"/games/'.$aRow["league"].'-'.SEOtitle($aRow["longname"]).$vl.'\";\' data-toggle=\'tooltip\' data-placement=\'top\' title=\''.LANG_BETS_CHANGE.'\'><i class=\'fas fa-edit\'></i></button>';
  if(isset($_GET["uid"]))
    {
    if(strtotime($aRow["datetime"]) > time()) $aRow["tip1"]=$aRow["tip2"]="?";
    $edit="";
    }
  $sOutput .= '["# '.$aRow["id"].'","'.$aRow["longname"].'","<a href=\'/'.$pag.'/'.$aRow["matchid"].$aRow["el"].'-'.SEOTitle($aRow["team1long"].' vs '.$aRow["team2long"]).'\'>'.$t1l.' vs. '.$t2l.'</a>","'.$aRow["tip1"].':'.$aRow["tip2"].'","'.$aRow["goals1"].':'.$aRow["goals2"].'","<b>'.$aRow["points"].'</b>","'.$edit.'", "'.$aRow["datetime"].'"],';
		
		/*
		 * Optional Configuration:
		 * If you need to add any extra columns (add/edit/delete etc) to the table, that aren't in the
		 * database - you can do it here
		 */

		$j++;
	}
	$sOutput = substr_replace( $sOutput, "", -1 );
	$sOutput .= '] }';
	
	echo $sOutput;
?>