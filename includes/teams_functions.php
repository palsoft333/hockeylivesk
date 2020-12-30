<?
/*
* Funkcia pre preklad ročníkov sezóny z názvu ligy
* version: 1.5.0 (7.12.2015 - prekopaná stará funkcia pre potreby novej verzie stránky)
* @param $longname string - názov ligy
* @return $sez string
*/
function Get_Seasson($longname)
	{
	$orez = substr($longname, -5);
	if(strstr($orez, '/')!==false) 
		{
		$ore = explode("/", $orez);
		$sez = "20".$ore[0]."/20".$ore[1];
		}
	else
		{
		$rok = date("Y")+1;
		$sez = date("Y")."/".$rok;
		}
	return $sez;
	}
	
/*
* Funkcia pre generovanie selectboxu so vsetkymi vystupeniami timu na turnajoch/sezonach
* version: 1.0.0 (11.4.2016 - vytvorenie funkcie)
* @param $shortnmae string - skratka timu
* @param $el integer - 0/1 pre extraligu
* @param $current integer - momentalne zvolene ID timu pre vysvietenie v selectboxe
* @return $select string
*/
function Get_Appearances($shortname, $el, $current)
  {
	if($el==1) $r = mysql_query("SELECT dt.*, 2004leagues.longname FROM 2004leagues JOIN (SELECT * FROM el_teams WHERE shortname='$shortname')dt ON 2004leagues.id=dt.league ORDER BY dt.id ASC");
	else $r = mysql_query("SELECT dt.*, 2004leagues.longname, 2004leagues.country, LEFT(2004leagues.longname, LENGTH(2004leagues.longname)-5) as tr, RIGHT(2004leagues.longname, 4) as tr1 FROM 2004leagues JOIN (SELECT * FROM 2004teams WHERE shortname='$shortname')dt ON 2004leagues.id=dt.league ORDER BY 2004leagues.country ASC, tr1 ASC, tr ASC, dt.league ASC");
	$nab = "";
	$nabpred = "";
	$i=0;
	while($k = mysql_fetch_array($r))
		{
    if($k[country]=="SVK") $optlabel = "Slovenské";
    elseif($k[country]=="GER") $optlabel = "Nemecké";
    elseif($k[country]=="SUI") $optlabel = "Švajčiarske";
    elseif($k[country]=="INT") $optlabel = "Medzinárodné";
		if($i==0) 
      {
      $select = "<select id='league' class='custom-select custom-select-sm w-auto ml-2'>";
      if($el==0) $select .= "<optgroup label='".$optlabel."'>";
      $nabpred=$k[country];
      }
		$nab = $k[country];
		//if($k[tr]=="Swiss Ice Hockey Challenge") { $nab="Arosa Challenge"; }
		//if(substr($k[tr],0,3)=="ZOH") { $nab="ZOH"; }
    if($nab!=$nabpred && $i!=0)
      {
      //if($k[tr]=="MS") $k[tr] = "Majstrovstvá sveta";
      //if(substr($k[tr],0,3)=="ZOH") $k[tr] = "Zimné olympijské hry";
      $select .= "</optgroup><optgroup label='".$optlabel."'>";
      $nabpred=$nab;
      }
		$select .= "<option value='".$k[id].$el."'".($k[id]==$current ? " selected" : "").">".$k[longname]."</option>";
		$i++;
		}
  if($el==0) $select .= "</optgroup>";
  $select .= "</select>";
  return $select;
  }
?>