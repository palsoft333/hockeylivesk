<?
function Show_Draft_Button($playername,$pid)
  {
  $nejdu = array(
  "MCDAVID Connor",
  "LINDHOLM Hampus",
  "RAKELL Rickard",
  "SILFVERBERG Jakob",
  "GRAŇÁK Dominik",
  "MALEC Tomáš",
  "BARKOV Aleksander");
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
?>