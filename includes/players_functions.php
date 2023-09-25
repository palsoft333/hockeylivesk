<?
function Show_Draft_Button($playername,$pid)
  {
  $nejdu = array(
"ČAJKOVSKÝ Michal",
"BAKOŠ Martin"
  );
  $uid = $_SESSION['logged'];
  if($uid)
    {
    $m = mysql_query("SELECT * FROM ft_teams WHERE uid='$uid'");
    if(mysql_num_rows($m)>0) // ak je prihlasenym manazerom
      {
      $u = mysql_fetch_array($m);
      $q = mysql_query("SELECT * FROM ft_players ORDER BY round DESC, id DESC");
      $f = mysql_fetch_array($q);
      $po = mysql_query("SELECT * FROM ft_players WHERE round='$f[round]'");
      $poc = mysql_num_rows($po);
      $v = mysql_query("SELECT * FROM ft_choices c JOIN ft_players p ON p.pid=c.id WHERE c.name='$playername'");
      $c = mysql_num_rows($v);
      
      if($poc<10)
        {
        $pick = $poc+1;
        $round = $f[round];
        if(mysql_num_rows($q)==0) $round=1;
        }
      else
        {
        $pick = 1;
        $round = $f[round]+1;
        }
      if($round % 2 == 0) $narade = 10-$pick+1;
      else $narade = $pick;
      
      if($narade==$u[pos] || $_SESSION['olddraft'])
        {
        $draft .= '<div class="draft">';
        if($_SESSION['olddraft']) $add = '/'.$_SESSION['olddraft'];
        if($c==0) 
          {
          if(in_array($playername, $nejdu))
            {
            $draft .= '<button type="button" class="btn btn-primary" disabled>NEZÚČASTNÍ SA</button>';
            }
          else
            {
            $draft .= '<button type="button" class="btn btn-primary" onclick="location.href=\'/fantasy/draft/'.$pid.$add.'\';">DRAFTOVAŤ HRÁČA</button>';
            if($_SESSION['olddraft']) 
              {
              $x = mysql_query("SELECT * FROM ft_choices WHERE id='".$_SESSION['olddraft']."'");
              $z = mysql_fetch_array($x);
              $draft .= '<br>(za hráča '.$z[name].' <a href="'.$_SERVER[REQUEST_URI].'/newdraft"><i class="fas fa-window-close text-secondary"></i></a>)';
              }
            }
          }
        else $draft .= '<button type="button" class="btn btn-primary" disabled>UŽ BOL DRAFTOVANÝ</button>';
        $draft .= '</div>';
        }
      }
    }
  return $draft;
  }
  
function GetBio($name, $gk) {
  if($gk==0) {
    $pos=$born=$hold=$kg=$cm="";
    $bio=array();
    $q = mysql_query("SELECT id, pos, born, hold, kg, cm, league FROM 2004players WHERE name='".$name."' UNION SELECT id, pos, born, hold, kg, cm, league FROM el_players WHERE name='".$name."' ORDER BY league DESC, id DESC;");
    while($f = mysql_fetch_array($q)) {
      if($pos=="" && $f["pos"]!="") $pos=$f["pos"];
      if($born=="" && $f["born"]!="1970-01-01") $born=$f["born"];
      if($hold=="" && $f["hold"]!="") $hold=$f["hold"];
      if($kg=="" && $f["kg"]!=0) $kg=$f["kg"];
      if($cm=="" && $f["cm"]!=0) $cm=$f["cm"];
    }
    if($pos=="F") $hl=LANG_PLAYERSTATS_F;
    elseif($pos=="LW") $hl=LANG_PLAYERSTATS_LW;
    elseif($pos=="RW") $hl=LANG_PLAYERSTATS_RW;
    elseif($pos=="C" || $pos=="CE") $hl=LANG_PLAYERSTATS_C;
    elseif($pos=="D") $hl=LANG_PLAYERSTATS_D;
    elseif($pos=="LD") $hl=LANG_PLAYERSTATS_LD;
    elseif($pos=="RD") $hl=LANG_PLAYERSTATS_RD;
    elseif($pos=="GK" || $pos=="G") $hl=LANG_PLAYERSTATS_GK;
    if($hold=="L") $hl1=LANG_PLAYERSTATS_LHOLD;
    else $hl1=LANG_PLAYERSTATS_RHOLD;
    if($pos!="") $bio[] = $hl;
    if($born!="") $bio[] = date_diff(date_create($born), date_create('today'))->y.' '.LANG_AGE_YEARS;
    if($hold!="") $bio[] = $hl1;
    if($kg!="") $bio[] = $kg.' kg';
    if($cm!="") $bio[] = $cm.' cm';
  }
  else {
    $born=$hold=$kg=$cm="";
    $bio=array();
    $q = mysql_query("SELECT id, born, hold, kg, cm, league FROM 2004goalies WHERE name='".$name."' UNION SELECT id, born, hold, kg, cm, league FROM el_goalies WHERE name='".$name."' ORDER BY league DESC, id DESC;");
    while($f = mysql_fetch_array($q)) {
      if($born=="" && $f["born"]!="1970-01-01") $born=$f["born"];
      if($hold=="" && $f["hold"]!="") $hold=$f["hold"];
      if($kg=="" && $f["kg"]!=0) $kg=$f["kg"];
      if($cm=="" && $f["cm"]!=0) $cm=$f["cm"];
    }
    if($hold=="L") $hl1=LANG_PLAYERSTATS_LHOLD;
    else $hl1=LANG_PLAYERSTATS_RHOLD;
    $bio[] = LANG_PLAYERSTATS_GK;
    if($born!="") $bio[] = date_diff(date_create($born), date_create('today'))->y.' '.LANG_AGE_YEARS;
    if($hold!="") $bio[] = $hl1;
    if($kg!="") $bio[] = $kg.' kg';
    if($cm!="") $bio[] = $cm.' cm';
  }
  return $bio;
}
?>