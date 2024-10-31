<?php
    session_start();
  include("db.php");
  if(isset($_SESSION["lang"])) {
    include("lang/lang_".$_SESSION["lang"].".php");
  }
  else {
     $_SESSION["lang"] = 'sk';
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
		$sLimit = "LIMIT ".mysqli_real_escape_string($link, $_GET['iDisplayStart'] ).", ".
			mysqli_real_escape_string($link, $_GET['iDisplayLength'] );
	}
	
	
	$sQuery = "SELECT SQL_CALC_FOUND_ROWS * FROM 2004playerdiary WHERE name='".$_GET["name"]."'
    ORDER BY msg_date DESC
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
	$sQuery = mysqli_query($link, "SELECT * FROM 2004playerdiary WHERE name='".$_GET["name"]."'");
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
	$aRow["msg"] = str_replace('"', '\'', $aRow["msg"]);
  $icon="";
  $datum = date("j.n.Y", strtotime($aRow["msg_date"]));
  if(strtotime($aRow["msg_date"])==mktime(0,0,0)) $datum='dnes';
  if(strtotime($aRow["msg_date"])==mktime(0,0,0,date("n"),date("j")-1)) $datum='včera';
  if($aRow["msg_type"]==1) $icon = "<i class='fas fa-exchange-alt text-success'></i>"; //transfer
  if($aRow["msg_type"]==2) $icon = "<i class='fas fa-user-plus text-success'></i>"; //pridal sa
  if($aRow["msg_type"]==3) $icon = "<i class='fas fa-dice-three text-secondary'></i>"; //hattrick
  if($aRow["msg_type"]==4) $icon = "<i class='fas fa-certificate text-warning'></i>"; //jubilejny gol
  if($aRow["msg_type"]==5) $icon = "<i class='fas fa-hockey-puck text-primary'></i>"; //gwg
  if($aRow["msg_type"]==6) $icon = "<i class='fab fa-creative-commons-zero text-dark'></i>"; //shutout
  if($aRow["msg_type"]==7) $icon = "<i class='fas fa-user-injured text-danger'></i>"; //injury
  if($aRow["msg_type"]==8) $icon = "<i class='fas fa-trophy text-warning'></i>"; //titul
  if($aRow["msg_type"]==9) $icon = "<i class='fas fa-band-aid rotate-n-15 text-warning'></i>"; //uzdravil sa
  if($aRow["msg_type"]==10) $icon = "<i class='fas fa-user-slash text-success'></i>"; //volny hrac

    $sOutput .= '["'.$datum.'","'.$icon.' '.$aRow["msg"].'"],';
		
		/*
		 * Optional Configuration:
		 * If you need to add any extra columns (add/edit/delete etc) to the table, that aren't in the
		 * database - you can do it here
		 */

		$j++;
	}
	$sOutput = substr_replace( $sOutput, "", -1 );
	$sOutput .= '] }';
	
    if($_SESSION["lang"]=="en") {
        $sOutput = str_replace("Zranil sa", "Got injured", $sOutput);
        $sOutput = str_replace("brucho", "Abdomen", $sOutput);
        $sOutput = str_replace("achilovka", "Achilles", $sOutput);
        $sOutput = str_replace("členok", "Ankle", $sOutput);
        $sOutput = str_replace(">plece<", ">Arm<", $sOutput);
        $sOutput = str_replace("chrbát", "Back", $sOutput);
        $sOutput = str_replace("hruď", "Chest", $sOutput);
        $sOutput = str_replace("rozhodnutie trénera", "Coach`s Decision", $sOutput);
        $sOutput = str_replace("kľúčna kosť", "Collarbone", $sOutput);
        $sOutput = str_replace("zmluvný spor", "Contract Dispute", $sOutput);
        $sOutput = str_replace("otras mozgu", "Concussion", $sOutput);
        $sOutput = str_replace("kľúčna kosť", "Clavicle", $sOutput);
        $sOutput = str_replace("lakeť", "Elbow", $sOutput);
        $sOutput = str_replace(">oko<", ">Eye<", $sOutput);
        $sOutput = str_replace(">tvár<", ">Face<", $sOutput);
        $sOutput = str_replace(">prst<", ">Finger<", $sOutput);
        $sOutput = str_replace("chodidlo", "Foot", $sOutput);
        $sOutput = str_replace("chrípka", "Flu", $sOutput);
        $sOutput = str_replace("všeobecná bolesť", "General Soreness", $sOutput);
        $sOutput = str_replace("triesla", "Groin", $sOutput);
        $sOutput = str_replace(">ruka<", ">Hand<", $sOutput);
        $sOutput = str_replace("podkolenná šľacha", "Hamstring", $sOutput);
        $sOutput = str_replace(">hlava<", ">Head<", $sOutput);
        $sOutput = str_replace(">päta<", ">Heel<", $sOutput);
        $sOutput = str_replace("prietrž", "Hernia", $sOutput);
        $sOutput = str_replace(">bedro<", ">Hip<", $sOutput);
        $sOutput = str_replace("choroba", "Illness", $sOutput);
        $sOutput = str_replace("čeľusť", "Jaw", $sOutput);
        $sOutput = str_replace(">koleno<", ">Knee<", $sOutput);
        $sOutput = str_replace(">noha<", ">Leg<", $sOutput);
        $sOutput = str_replace("dolná časť tela", "Lower body", $sOutput);
        $sOutput = str_replace("dolná časť tela", "Lower Body", $sOutput);
        $sOutput = str_replace(">krk<", ">Neck<", $sOutput);
        $sOutput = str_replace(">nos<", ">Nose<", $sOutput);
        $sOutput = str_replace("nejde o zranenie", "Not Injury Related", $sOutput);
        $sOutput = str_replace(">panva<", ">Pelvis<", $sOutput);
        $sOutput = str_replace("hrudník", "Pectoral", $sOutput);
        $sOutput = str_replace("osobné dôvody", "Personal", $sOutput);
        $sOutput = str_replace("odpočinok", "Rest", $sOutput);
        $sOutput = str_replace("rebrá", "ribs", $sOutput);
        $sOutput = str_replace("rebrá", "Ribs", $sOutput);
        $sOutput = str_replace(">rameno<", ">Shoulder<", $sOutput);
        $sOutput = str_replace("suspenzácia", "Suspension", $sOutput);
        $sOutput = str_replace(">palec<", ">Thumb<", $sOutput);
        $sOutput = str_replace("horná časť tela", "Upper body", $sOutput);
        $sOutput = str_replace("horná časť tela", "Upper Body", $sOutput);
        $sOutput = str_replace("bližšie nešpecifikované", "Undisclosed", $sOutput);
        $sOutput = str_replace("zápästie", "Wrist", $sOutput);
        $sOutput = str_replace("Prestúpil z tímu", "Transferred from the team", $sOutput);
        $sOutput = str_replace("do tímu", "to the team", $sOutput);
        $sOutput = str_replace("Pridal sa k tímu", "He joined the team", $sOutput);
        $sOutput = str_replace("Bol nominovaný na turnaj", "He was nominated for the tournament", $sOutput);
        $sOutput = str_replace("Dosiahol hattrick v zápase proti", "He scored a hat trick in the game against", $sOutput);
        $sOutput = str_replace("Dosiahol jubilejný", "He scored the jubilee", $sOutput);
        $sOutput = str_replace(".gól v sezóne", "th goal of the season", $sOutput);
        $sOutput = str_replace("Strelil víťazný gól v zápase proti tímu", "He scored the winning goal in the game against", $sOutput);
        $sOutput = str_replace("Vychytal čisté konto v zápase proti", "He records a shutout in the game against", $sOutput);
        $sOutput = str_replace("S tímom ", "He won the title of champion of Slovakia with the team ", $sOutput);
        $sOutput = str_replace("získal titul majstra Slovenska", "", $sOutput);
        $sOutput = str_replace("Získal zlatú medailu s tímom", "He won a gold medal with the team", $sOutput);
        $sOutput = str_replace("Uzdravil sa", "He is no longer injured", $sOutput);
        $sOutput = str_replace("Stal sa neobmedzeným voľným hráčom", "He became an unrestricted free agent", $sOutput);
    }

	echo $sOutput;
?>