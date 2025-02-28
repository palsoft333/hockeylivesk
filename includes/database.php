<?php
  include("db.php");
  include("main_functions.php");
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$aColumns = array( ' ', 'name', 'timy');
	
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "id";
	
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
	if ( $_GET['sSearch'] != "" || $_GET["vyb"] != "" || $_GET["tshort"] != "")
	{
    if($_GET["vyb"])
      {
      $regexp = "^".$_GET["vyb"];
      if($_GET["vyb"]=="A") $regexp = "^A|^Á";
      if($_GET["vyb"]=="C") $regexp = "^C|^Č";
      if($_GET["vyb"]=="D") $regexp = "^D|^Ď";
      if($_GET["vyb"]=="L") $regexp = "^L|^Ľ";
      if($_GET["vyb"]=="R") $regexp = "^R|^Ř";
      if($_GET["vyb"]=="S") $regexp = "^S|^Š";
      if($_GET["vyb"]=="T") $regexp = "^T|^Ť";
      if($_GET["vyb"]=="Z") $regexp = "^Z|^Ž";
      }
    
    if($_GET["vyb"]) $sWhere = "WHERE name REGEXP ?";
    if($_GET["tshort"]) $sWhere = "WHERE teamshort=?";
    if($_GET['sSearch']) { $sWhere = "WHERE name LIKE ?"; $_GET['sSearch']="%".$_GET['sSearch']."%"; }
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
	
	//if($sWhere == "") $sWhere = "";
	
	
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

    $sQuery = "SELECT SQL_CALC_FOUND_ROWS *, 
        GROUP_CONCAT(DISTINCT dt.teamshort ORDER BY dt.id ASC) AS timy, 
        GROUP_CONCAT(DISTINCT dt.teamlong ORDER BY dt.id ASC) AS timylong, 
        GROUP_CONCAT(DISTINCT dt.el ORDER BY dt.id ASC) AS timyel 
    FROM (
        (SELECT id, name, teamshort, teamlong, 1 as el FROM el_players ".$sWhere.") 
        UNION 
        (SELECT id, name, teamshort, teamlong, 0 as el FROM 2004players ".$sWhere.") 
        UNION 
        (SELECT id, name, teamshort, teamlong, 2 as el FROM el_goalies ".$sWhere.")
    ) dt 
    GROUP BY dt.name 
    $sOrder 
    $sLimit";

    $stmt = mysqli_prepare($link, $sQuery);

    if($_GET["vyb"]) mysqli_stmt_bind_param($stmt, 'sss', $regexp, $regexp, $regexp);
    if($_GET["tshort"]) mysqli_stmt_bind_param($stmt, 'sss', $_GET["tshort"], $_GET["tshort"], $_GET["tshort"]);
    if($_GET['sSearch']) mysqli_stmt_bind_param($stmt, 'sss', $_GET['sSearch'], $_GET['sSearch'], $_GET['sSearch']);

    mysqli_stmt_execute($stmt);

    $rResult = mysqli_stmt_get_result($stmt);
    if (!$rResult) {
        die("Chyba v dotaze: " . mysqli_error($link));
    }
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = mysqli_query($link, $sQuery ) or die(mysqli_error($link));
	$aResultFilterTotal = mysqli_fetch_array($rResultFilterTotal, MYSQLI_ASSOC);
	$iFilteredTotal = $aResultFilterTotal['FOUND_ROWS()'];
	
	/* Total data set length */
	$sQuery = mysqli_query($link, "SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysqli_error($link));
	$sQuery = mysqli_query($link, "SELECT * FROM ((SELECT id, name, teamshort, teamlong, 1 as el FROM el_players) UNION (SELECT id, name, teamshort, teamlong, 0 as el FROM 2004players) UNION (SELECT id, name, teamshort, teamlong, 2 as el FROM el_goalies))dt GROUP BY dt.name");
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
	while ( $aRow = mysqli_fetch_array( $rResult, MYSQLI_ASSOC ) )
	{
		$sOutput .= "[";
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if ( $aColumns[$i] == "name" )
			{
				/* Special output formatting for 'name' */
				if($aRow["el"]==2) { $aRow["el"]=""; $pag="goalie"; $aRow["el"]=1; }
				else $pag="player";
				$sOutput .= '"<a href=\'/'.$pag.'/'.$aRow["id"].$aRow["el"].'-'.SEOtitle($aRow["name"]).'\'>'.str_replace('"', '\"', $aRow[ $aColumns[$i] ]).'</a>",';
			}
			else if ( $aColumns[$i] == "timy" )
			{
				/* Special output formatting for 'timy' */
      $timy="";
      $tim = explode(",",$aRow[ $aColumns[$i] ]);
      $timl = explode(",",$aRow["timylong"]);
      $time = explode(",",$aRow["timyel"]);
      $k=0;
      while($k < count($tim))
        {
        $tt='t'.$j.$k;
        $timy .= '<img class=\'flag-'.($time[$k]==0 ? 'iihf':'el').' '.$tim[$k].'-small\' src=\'/images/blank.png\' alt=\''.$timl[$k].'\' data-toggle=\'tooltip\' data-placement=\'top\' title=\''.$timl[$k].'\'> ';
        $k++;
        }
      $sOutput .= '"'.$timy.'",';
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