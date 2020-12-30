<?php
  include("db.php");
  include("main_functions.php");
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$lid = $_GET[lid];
	$el = $_GET[el];
	if($el==1 || $el==3) $aColumns = array( ' ', 'name', 'teamshort', 'id', 'pos', 'gp', 'goals', 'asists', 'points', 'penalty', 'ppg', 'shg', 'gwg' );
	else $aColumns = array( ' ', 'name', 'teamshort', 'id', ' ', 'goals', 'asists', 'points', 'penalty', 'ppg', 'shg', 'gwg' );
	
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "id";

	
		/* DB table to use */
	if($el==1) $sTable = "el_players";
	elseif($el==0) $sTable = "2004players";
	else $sTable = "al_players";
	
	/* 
	 * Paging
	 */
	$sLimit = "";
	if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
	{
		$sLimit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
			mysql_real_escape_string( $_GET['iDisplayLength'] );
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
				 	".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
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
		$sWhere = "WHERE name LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' && league='$lid'";
		/*for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
		}
		$sWhere = substr_replace( $sWhere, "", -3 );
		$sWhere .= ') && league=\'50\'';*/
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
			$sWhere .= $aColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
		}
	}
	
	if($sWhere == "") $sWhere = "WHERE league='$lid'";
	
	
	/*
	 * SQL queries
	 * Get data to display
	 */
	/*$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
		FROM   $sTable
		$sWhere
		$sOrder
		$sLimit
	";*/
	if($el==1 || $el==3) $gp=", sum(gp) as gp, pos";
	$sQuery = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
	$sQuery = "
		SELECT SQL_CALC_FOUND_ROWS id, teamshort, name$gp, sum(goals) as goals, sum(asists) as asists, sum(points) as points, sum(penalty) as penalty, sum(ppg) as ppg, sum(shg) as shg, sum(gwg) as gwg
		FROM   $sTable
		$sWhere
		GROUP BY name, league
		$sOrder
		$sLimit
	";
	$rResult = mysql_query( $sQuery ) or die(mysql_error());
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = mysql_query( $sQuery ) or die(mysql_error());
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
	$sQuery = mysql_query("SELECT *		FROM   $sTable WHERE league='$lid' GROUP BY name, league");
	$rResultTotal = mysql_num_rows($sQuery);
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
	while ( $aRow = mysql_fetch_array( $rResult ) )
	{
		$sOutput .= "[";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( $aColumns[$i] == "name" )
			{
				/* Special output formatting for 'version' */
				$sOutput .= '"<img class=\'flag-'.($el==0 ? 'iihf':'el').' '.$aRow[teamshort].'-small\' src=\'/images/blank.png\' alt=\''.$aRow[teamlong].'\'> <a href=\'/player/'.$aRow[id].$el.'-'.SEOtitle($aRow[name]).'\'>'.str_replace('"', '\"', $aRow[ $aColumns[$i] ]).'</a>",';
				//$sOutput .= iconv("windows-1250", "utf-8", $aRow[ $aColumns[$i] ]);
			}
			else if ( $aColumns[$i] != ' ' )
			{
				/* General output */
				$sOutput .= '"'.str_replace('"', '\"', $aRow[ $aColumns[$i] ]).'",';
			}
			else if ($i>0) $sOutput .= '"",';
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