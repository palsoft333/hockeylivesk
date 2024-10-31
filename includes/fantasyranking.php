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
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$aColumns = array( ' ', 'uname', 'points', 'prev_points', 'aktual', 'active');
	
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "uid";
		
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
	
    $lid = $_GET["lid"];
	//$sQuery = "SELECT SQL_CALC_FOUND_ROWS dt.*, e_xoops_users.uname as uname FROM e_xoops_users INNER JOIN (SELECT * FROM fl_wallet WHERE league='$lid' ORDER BY points DESC)dt ON (e_xoops_users.uid=dt.uid) $sLimit";
	$sQuery = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
	$sQuery = "SELECT fl_selects.*, sum(t1.price)+sum(t2.price) as aktual, t3.points, t3.prev_points, t3.active, t4.uname FROM `fl_selects` LEFT JOIN fl_prices t1 ON t1.playerid=fl_selects.pid LEFT JOIN fl_prices_g t2 ON t2.playerid=fl_selects.pid JOIN fl_wallet t3 ON t3.uid=fl_selects.uid && t3.league='".$lid."' JOIN e_xoops_users t4 ON t4.uid=fl_selects.uid GROUP BY uid ORDER BY points DESC, aktual DESC $sLimit";
		
	$rResult = mysqli_query($link, $sQuery ) or die(mysqli_error($link));
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = mysqli_query($link, $sQuery ) or die(mysqli_error($link));
	$aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	//$sQuery = mysql_query("SELECT t1.id, t1.matchid, t1.tip1, t1.tip2, t1.points, 0 as el, t1.league FROM 2004tips t1 WHERE t1.userid = '$uid' UNION SELECT t3.id, t3.matchid, t3.tip1, t3.tip2, t3.points, t3.el, t3.league FROM el_tips t3 WHERE t3.userid = '$uid'");
	//$rResultTotal = mysql_num_rows($rResult);
	$iTotal = $aResultFilterTotal[0];
	

	/*
	 * Output
	 */
	 $j=1;
	$sOutput = '{';
	$sOutput .= '"draw": '.intval($_GET['sEcho']).', ';
	$sOutput .= '"recordsTotal": '.$iTotal.', ';
	$sOutput .= '"recordsFiltered": '.$iFilteredTotal.', ';
	$sOutput .= '"data": [ ';
	while ( $aRow = mysqli_fetch_array( $rResult ) )
	{
    $startPoint=$_GET['iDisplayStart'];
    $counter=($startPoint) + ($j);
    
    if($aRow["uid"]==$uid) $link = 'select';
    else $link = 'roster/'.$aRow["uid"].'/'.$lid;
    
    if ($aRow["aktual"] > 999 && $aRow["aktual"] <= 999999) {
    $result = floor($aRow["aktual"] / 1000) . ' K';
    } elseif ($aRow["aktual"] > 999999) {
        $result = floor($aRow["aktual"] / 1000000) . ' M';
    } else {
        $result = $aRow["aktual"];
    }
    if($aRow["aktual"]==NULL) $result=0;
    if($aRow["prev_points"]<>$aRow["points"]) 
      {
      $diffn = $aRow["points"]-$aRow["prev_points"];
      $diff = '<span class=\"text-success text-xs\"> (+'.$diffn.')</span>';
      }
    else $diff='';

    $sOutput .= '["'.$counter.'.","<a href=\'/fantasy/'.$link.'\'>'.$aRow["uname"].'</a>","<b>'.$aRow["points"].'</b>'.$diff.'","'.$result.'","'.$aRow["active"].'"],';
		
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