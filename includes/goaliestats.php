<?php
  include("db.php");
  include("main_functions.php");
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$lid = mysqli_real_escape_string($link, $_GET["lid"] );
	$el = mysqli_real_escape_string($link, $_GET["el"] );
	$aColumns = array( ' ', 'name', 'teamshort', 'id', 'gp', 'sog', 'svs', 'svsp', 'ga', 'gaa', 'so', 'penalty' );
	
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "id";
	
	
		/* DB table to use */
	if($el==1) $sTable = "el_goalies";
	else $sTable = "2004goalies";
	
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysqli_real_escape_string($link, $_GET['iDisplayStart'] ).", ".
			mysqli_real_escape_string($link, $_GET['iDisplayLength'] );
	}
	
	
	/*
	 * Ordering
	 */
	if ( isset( $_GET['iSortCol_0'] ) )
	{
		$sOrder = "ORDER BY  ";
		for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
		{
			if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
			{
				$sOrder .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
				 	".mysqli_real_escape_string($link, $_GET['sSortDir_'.$i] ) .", ";
			}
		}
		
		$sOrder = substr_replace( $sOrder, "", -2 );
		if ( $sOrder == "ORDER BY" )
		{
			$sOrder = "";
		}
	}
	
	
	/* 
	 * Filtering
	 * NOTE this does not match the built-in DataTables filtering which does it
	 * word by word on any field. It's possible to do here, but concerned about efficiency
	 * on very large tables, and MySQL's regex functionality is very limited
	 */
	$sWhere = "";
	if ( $_GET['sSearch'] != "" )
	{
		$sWhere = "WHERE name LIKE '%".mysqli_real_escape_string($link, $_GET['sSearch'] )."%' && league='$lid'";
	}
	
	/* Individual column filtering */
	for ( $i=0 ; $i<count($aColumns) ; $i++ )
	{
		if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
		{
			if ( $sWhere == "" )
			{
				$sWhere = "WHERE";
			}
			else
			{
				$sWhere .= " AND ";
			}
			$sWhere .= $aColumns[$i]." LIKE '%".mysqli_real_escape_string($link, $_GET['sSearch_'.$i])."%' ";
		}
	}
	
	if($sWhere == "") $sWhere = "WHERE league='$lid'";
	
	
	/*
	 * SQL queries
	 * Get data to display
	 */
	$sQuery = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS id, teamshort, name, sum(gp) as gp, sum(sog) as sog, sum(svs) as svs, sum(ga) as ga, sum(so) as so, (sum(svs)/sum(sog))*100 as svsp, sum(ga)/sum(gp) as gaa, sum(pim) as penalty FROM $sTable
		$sWhere ".($el==1 ? '&& IF(gp=1 && (svs/sog)=1, 1, 0)=0 && gp!=0':'')."
		GROUP BY name, league
		$sOrder
		$sLimit
	";
	/*$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS id, teamshort, name, sum(gp) as gp, sum(sog) as sog, sum(svs) as svs, sum(ga) as ga, sum(so) as so, (sum(svs)/sum(sog))*100 as svsp, sum(ga)/sum(gp) as gaa, sum(pim) as penalty, dt.aver FROM (SELECT avg(gp) as aver FROM $sTable WHERE league='$lid')dt, $sTable
		$sWhere && gp>dt.aver
		GROUP BY name, league
		$sOrder
		$sLimit
	";*/
	$rResult = mysqli_query($link, $sQuery ) or die(mysqli_error($link));
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = mysqli_query($link, $sQuery ) or die(mysqli_error($link));
	$aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = mysqli_query($link,"SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
	$sQuery = mysqli_query($link,"SELECT *		FROM   $sTable WHERE league='$lid' GROUP BY name, league");
	$rResultTotal = mysqli_num_rows($sQuery);
	//$aResultTotal = mysql_fetch_array($rResultTotal);
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
		$sOutput .= "[";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( $aColumns[$i] == "name" )
			{
				/* Special output formatting for 'version' */
				$sOutput .= '"<img class=\'flag-'.($el==0 ? 'iihf':'el').' '.$aRow["teamshort"].'-small\' src=\'/images/blank.png\' alt=\''.$aRow["teamlong"].'\'> <a href=\'/goalie/'.$aRow["id"].$el.'-'.SEOtitle($aRow["name"]).'\'>'.str_replace('"', '\"', $aRow[ $aColumns[$i] ]).'</a>",';
				//$sOutput .= iconv("windows-1250", "utf-8", $aRow[ $aColumns[$i] ]);
			}
			else if ( $aColumns[$i] != ' ' )
			{
				/* General output */
				$sOutput .= '"'.str_replace('"', '\"', $aRow[ $aColumns[$i] ]).'",';
			}
			else if ($i>0) $sOutput .= '" ",';
			else {
			$startPoint=$_GET['iDisplayStart'];
      $counter=($startPoint) + ($j);
			$sOutput .= '"'.$counter.'.",';
			}
		
			
		}
		
		/*
		 * Optional Configuration:
		 * If you need to add any extra columns (add/edit/delete etc) to the table, that aren't in the
		 * database - you can do it here
		 */
		
		
		$sOutput = substr_replace( $sOutput, "", -1 );
		$sOutput .= "],";
		$j++;
	}
	$sOutput = substr_replace( $sOutput, "", -1 );
	$sOutput .= '] }';
	
	echo $sOutput;
?>