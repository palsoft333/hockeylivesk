<?php
  session_start();
  include("db.php");
  include("main_functions.php");
  if(isset($_SESSION[lang])) {
    include("lang/lang_$_SESSION[lang].php");
  }
  else {
     $_SESSION[lang] = 'sk';
      include("lang/lang_sk.php");
  }
  $uid = $_SESSION['logged'];
  if($_GET[uid]) $uid=$_GET[uid];
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$aColumns = array( ' ', 'uname', 'points', ' ', ' ');
	
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
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
	}
	
	if($_GET[lid])
    {
    $lid = $_GET[lid];
    if($lid=="contest") {
      // sutaz o karticky
      $sQuery = "SELECT SQL_CALC_FOUND_ROWS t.userid,t.matchid,m.datetime,sum(t.points) as points, count(t.id) as poc, u.uname, u.uid, u.user_avatar FROM el_tips t LEFT JOIN el_matches m ON m.id=t.matchid LEFT JOIN e_xoops_users u ON u.uid=t.userid WHERE t.league='154' && m.datetime>'2024-02-14 00:00:00' GROUP BY t.userid ORDER BY points DESC $sLimit";
    }
    else {
      $sel = mysql_query("SELECT el FROM 2004leagues WHERE id='$lid'");
      $vyb = mysql_fetch_array($sel);
      if($vyb[el]==1) $tips_table="el_tips";
      else $tips_table="2004tips";
    $sQuery = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
    $sQuery = "SELECT SQL_CALC_FOUND_ROWS dt.*, e_xoops_users.uname as uname, e_xoops_users.uid as uid, e_xoops_users.user_avatar as user_avatar FROM e_xoops_users INNER JOIN (SELECT userid,sum(points) as points, count(id) as poc FROM $tips_table WHERE league='$lid' GROUP BY userid ORDER BY points DESC)dt ON (e_xoops_users.uid=dt.userid)
      $sLimit";
      }
		}
	else 
    {
    $sQuery = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
    $sQuery = "SELECT SQL_CALC_FOUND_ROWS *, f.pocel, g.pocnonel, e_xoops_users.user_avatar as user_avatar FROM `e_xoops_users` JOIN (SELECT userid, count(id) as pocel FROM el_tips GROUP BY userid) f ON f.userid=e_xoops_users.uid JOIN (SELECT userid, count(id) as pocnonel FROM 2004tips GROUP BY userid) g ON g.userid=e_xoops_users.uid ORDER BY `tip_points` DESC
		$sLimit";
		}
	$rResult = mysql_query( $sQuery ) or die(mysql_error());
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = mysql_query( $sQuery ) or die(mysql_error());
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
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
	while ( $aRow = mysql_fetch_array( $rResult ) )
	{
	//$aRow[uname] = iconv("windows-1250", "utf-8", $aRow[uname]);
	
    if($_GET[lid]) 
      {
      $points = $aRow[points];
      $pocet_tipov = $aRow[poc];
      }
    else 
      {
      $points = $aRow[tip_points];
      $pocet_tipov = $aRow[pocel]+$aRow[pocnonel];
      }
    
    $uspesnost = round(($points/($pocet_tipov*10))*100,1);
    $startPoint=$_GET['iDisplayStart'];
    $counter=($startPoint) + ($j);
    
    if($aRow[user_avatar]!="") $avatar = "<img class='rounded-circle mr-1' src='/images/user_avatars/".$aRow[uid].".".$aRow[user_avatar]."?".filemtime('../images/user_avatars/'.$aRow[uid].'.'.$aRow[user_avatar])."' alt='".$aRow[uname]."' style='width:2rem;height:2rem;vertical-align:-11px;'>";
    else $avatar = "<i class='text-gray-300 fas fa-user-circle fa-2x mr-1' style='width:2rem;height:2rem;vertical-align:-7px;'></i>";

    $sOutput .= '["'.$counter.'","<a href=\'/bets/'.$aRow[uid].'\'>'.$avatar.''.$aRow[uname].'</a>","<b>'.$points.'</b>","'.$pocet_tipov.'","'.$uspesnost.'%"],';
		
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