<?php
  include("db.php");
  if(isset($_SESSION[lang])) {
    include("lang/lang_$_SESSION[lang].php");
  }
  else {
     $_SESSION[lang] = 'sk';
      include("lang/lang_sk.php");
  }

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$aColumns = array( ' ', ' ');
	
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
	
	
	$sQuery = "SELECT SQL_CALC_FOUND_ROWS * FROM 2004playerdiary WHERE name='".$_GET[name]."'
    ORDER BY msg_date DESC
		$sLimit";
	$rResult = mysql_query( $sQuery ) or die(mysql_error());
	
	/* Data set length after filtering */
	$sQuery = "
		SELECT FOUND_ROWS()
	";
	$rResultFilterTotal = mysql_query( $sQuery ) or die(mysql_error());
	$aResultFilterTotal = mysql_fetch_array($rResultFilterTotal);
	$iFilteredTotal = $aResultFilterTotal[0];
	
	/* Total data set length */
	$sQuery = mysql_query("SELECT * FROM 2004playerdiary WHERE name='".$_GET[name]."'");
	$rResultTotal = mysql_num_rows($sQuery);
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
	$aRow[msg] = str_replace('"', '\'', $aRow[msg]);
  $icon="";
  $datum = date("j.n.Y", strtotime($aRow[msg_date]));
  if(strtotime($aRow[msg_date])==mktime(0,0,0)) $datum='dnes';
  if(strtotime($aRow[msg_date])==mktime(0,0,0,date("n"),date("j")-1)) $datum='včera';
  if($aRow[msg_type]==1) $icon = "<i class='fas fa-exchange-alt text-danger'></i>"; //transfer
  if($aRow[msg_type]==2) $icon = "<i class='fas fa-user-plus text-success'></i>"; //pridal sa
  if($aRow[msg_type]==3) $icon = "<i class='fas fa-dice-three text-secondary'></i>"; //hattrick
  if($aRow[msg_type]==4) $icon = "<i class='fas fa-hockey-puck text-warning'></i>"; //gwg
  if($aRow[msg_type]==5) $icon = "<i class='fas fa-certificate text-primary'></i>"; //jubilejny gol
  if($aRow[msg_type]==6) $icon = "<i class='fab fa-creative-commons-zero text-dark'></i>"; //shutout
  if($aRow[msg_type]==7) $icon = "<i class='fas fa-user-injured text-danger'></i>"; //injury
  if($aRow[msg_type]==8) $icon = "<i class='fas fa-trophy text-warning'></i>"; //titul
  if($aRow[msg_type]==9) $icon = "<i class='fas fa-band-aid rotate-n-15 text-warning'></i>"; //uzdravil sa

    $sOutput .= '["'.$datum.'","'.$icon.' '.$aRow[msg].'"],';
		
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