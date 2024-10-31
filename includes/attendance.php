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
	
	
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "id";
		
		/* DB table to use */
	$sTable = "el_matchstats";
	
	/*$q = mysql_query("SELECT longname FROM 2004leagues WHERE id='$lid'");
	$f = mysql_fetch_array($q);
	if(strstr($f[longname], "NHL")) $ident = 2;
	else $ident = 1;*/
	
	$ident = 1;
	
	$aColumns = array( ' ', 'team'.$ident.'long', 'arena', 'navst', 'capacity', 'perc' );

	
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
		SELECT SQL_CALC_FOUND_ROWS t1.*, t2.league, t2.team".$ident."short, t2.team".$ident."long, t3.arena, round(sum(attendance)/count(team".$ident."short),0) as navst, t3.capacity, (sum(attendance)/count(team".$ident."short))/t3.capacity as perc FROM `el_matchstats` t1 JOIN el_matches t2 ON t2.id=t1.matchid JOIN el_infos t3 ON t3.teamshort=t2.team".$ident."short WHERE league='$lid' && attendance>0 GROUP BY team".$ident."short
		$sOrder
		$sLimit
	";
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
	$sQuery = mysqli_query($link,"SELECT t1.*, t2.league, t2.team1short FROM `el_matchstats` t1 JOIN el_matches t2 ON t2.id=t1.matchid WHERE league='$lid' && attendance>0 GROUP BY team1short");
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
     	if ( $aColumns[$i] == "perc" )
			{
				$aRow["perc"] = round($aRow["perc"]*100,1)."%";
				$sOutput .= '"'.$aRow["perc"].'",';
			}
     	elseif ( $aColumns[$i] == "team".$ident."long" )
			{
        if(strstr($f["longname"], "NHL")) { $obr = $aRow["team2short"]; $text = $aRow["team2long"]; }
        else { $obr = $aRow["team1short"]; $text = $aRow["team1long"]; }
				$sOutput .= '"<img class=\'flag-'.($el==0 ? 'iihf':'el').' '.$obr.'-small\' src=\'/images/blank.png\' alt=\''.$obr.'\'> <b>'.$text.'</b>",';
			}
     	elseif ( $aColumns[$i] == "arena" )
			{
				$sOutput .= '"'.$aRow["arena"].'",';
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