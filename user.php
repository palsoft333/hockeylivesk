<?
if($_GET[id]) 
  {
  $params = explode("/", htmlspecialchars($_GET[id]));
  $id = explode("-", htmlspecialchars($params[0]));
  $id=$id[0];
  }

$content = ""; 
// moj profil

if($_GET[profile])
  {
  if($_SESSION['logged'])
    {
    $q = mysql_query("SELECT *, JSON_LENGTH(login_session) as num_devices FROM e_xoops_users WHERE uid='".$_SESSION['logged']."'");
    $f = mysql_fetch_array($q);
    $r = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
    $r = mysql_query("SELECT shortname, longname FROM 2004teams WHERE shortname='".$f[user_favteam]."' GROUP BY shortname UNION SELECT shortname, longname FROM el_teams WHERE shortname='".$f[user_favteam]."' GROUP BY shortname ORDER BY longname ASC");
    $t = mysql_fetch_array($r);
    if($f[user_avatar]!="") $avatar = '/images/user_avatars/'.$_SESSION['logged'].'.'.$f[user_avatar];
    else $avatar = '/img/players/no_photo.jpg';
    $leaguecolor = "hl";
    $title = LANG_NAV_USERHOMEPAGE;
    
    $content .= '<!-- Logout devices modal-->
      <div class="modal fade" id="logoutallModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">'.LANG_LOGOUT.'?</h5>
              <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
              </button>
            </div>
            <div class="modal-body">'.LANG_USERPROFILE_LOGOUTCONF.'</div>
            <div class="modal-footer">
              <button class="btn btn-secondary" type="button" data-dismiss="modal">'.LANG_CANCEL.'</button>
              <a class="btn btn-hl" href="/logoutall">'.LANG_LOGOUT.'</a>
            </div>
          </div>
        </div>
      </div>';
    
    $content .= "<div id='toasts' class='fixed-top' style='top: 80px; right: 23px; left: initial; z-index:3;'></div>
                 <img class='float-left img-profile img-thumbnail mr-2 rounded-circle' src='".$avatar."' style='width: 55px;'>
                 <h1 class='h3 h3-fluid mb-1'>".LANG_NAV_USERHOMEPAGE."</h1>
                 <h2 class='h6 h6-fluid text-hl text-uppercase font-weight-bold mb-3'>".$f[uname]."</h2>
                 <div style='max-width: 1000px;'>";
    
    $content .= '
    <div class="card-deck pt-2">
    
      <div class="card shadow">
        <div class="card-header">
          <h6 class="m-0 font-weight-bold text-hl">'.LANG_USERPROFILE_SETTINGS.'</h6>
        </div>
        <div class="card-body">
          <form class="user">
            <div class="form-group">
              <label for="email">'.LANG_NAV_EMAIL.'</label>
              <input class="form-control form-control-user" type="email" id="email" value="'.$f[email].'" autocomplete="username" required>
            </div>
            <div class="form-group">
              <label for="favourite">'.LANG_USERPROFILE_FAVTEAM.'</label>
              <input class="form-control form-control-user" type="text" id="favourite" value="'.$t[longname].'">
              <input type="hidden" id="tshort" value="'.$f[user_favteam].'">
              <div id="suggestions"></div>
            </div>
            <div class="form-group">
              <label for="lang">'.LANG_USERPROFILE_LANGUAGE.'</label>
              <select class="custom-select" id="lang" required>
                <option value="sk"'.($f[lang]=="sk" ? ' selected':'').'>Slovensky</option>
                <option value="en"'.($f[lang]=="en" ? ' selected':'').'>English</option>
              </select>
            </div>
            <div class="form-group">
              <label for="profilepic">'.LANG_USERPROFILE_PICTURE.'</label>
              <div class="col-auto current-avatar"><img class="img-profile img-thumbnail mr-2 rounded-circle" src="'.$avatar.'" style="width: 70px;"></div>
              <div class="upload-avatar">
                <div class="actions">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input d-none" id="upload" accept="image/*">
                    <label class="btn btn-light btn-sm mt-2" for="upload">'.LANG_USERPROFILE_UPLOAD.'</label>
                  </div>
                </div>
                <div class="upload-avatar-wrap">
                  <div id="upload-avatar"></div>
                </div>
              </div>
              <input type="hidden" name="avatar" id="avatar">
            </div>
            <div class="form-group">
              <label for="option-1">'.LANG_USERPROFILE_GOALNOTIFY.'</label>
              <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" name="options" id="option-1" value="1"'.($f[goalhorn]==1 ? " checked" : "").'>
                <label class="custom-control-label" for="option-1">
                  '.LANG_USERPROFILE_SPOKEN.' <i class="fas fa-volume-up" id="sound-1"></i>
                </label>
              </div>
              <div class="custom-control custom-radio">
                <input class="custom-control-input" type="radio" name="options" id="option-2" value="2"'.($f[goalhorn]==2 ? " checked" : "").'>
                <label class="custom-control-label" for="option-2">
                  '.LANG_USERPROFILE_SOUND.' <i class="fas fa-volume-up" id="sound-2"></i>
                </label>
              </div>
            </div>
            <button type="submit" class="btn btn-sm btn-light btn-icon-split" id="change-data">
              <span class="icon text-gray-600">
                <i class="fas fa-pencil-alt"></i>
              </span>
              <span class="text text-gray-800">'.LANG_USERPROFILE_CHANGEDATA.'</span>
            </button>
          </form>
        </div>
      </div>
      
      <div class="card shadow">
        <div class="card-header">
          <h6 class="m-0 font-weight-bold text-hl">'.LANG_USERPROFILE_CHANGEPASS1.'</h6>
        </div>
        <div class="card-body">
          <form class="user">
            <div class="form-group">
              <input class="d-none" type="text" name="email" value="'.$f[email].'" autocomplete="username">
              <input class="form-control form-control-user" type="password" id="currentpass" placeholder="'.LANG_USERPROFILE_CURRENTPASS.'..."  autocomplete="current-password" required>
            </div>
            <div class="form-group">
              <input class="form-control form-control-user" type="password" id="pass" placeholder="'.LANG_NAV_PASSWORD.'..." pattern=".{6,}" autocomplete="new-password" aria-describedby="passwordHelpBlock" required>
              <small id="passwordHelpBlock" class="form-text text-muted ml-2">
                '.LANG_USERPROFILE_PASSREQ.'
              </small>
            </div>
            <div class="form-group">
              <input class="form-control form-control-user" type="password" id="passagain" placeholder="'.LANG_NAV_PASSWORDAGAIN.'..." pattern=".{6,}" autocomplete="new-password" required>
            </div>
            <button type="submit" class="btn btn-sm btn-light btn-icon-split" id="change-pass">
              <span class="icon text-gray-600">
                <i class="fas fa-key"></i>
              </span>
              <span class="text text-gray-800">'.LANG_USERPROFILE_CHANGEPASS.'</span>
            </button>
          </form>
          '.($f[num_devices]>0 ? '<p class="alert alert-info mt-4 small">'.sprintf(LANG_USERPROFILE_CURRENTLYLOGGED, '<strong><i class="fas fa-desktop"></i> '.$f[num_devices].'</strong>').'<br><br><a href="#" data-toggle="modal" data-target="#logoutallModal" class="alert-link">'.LANG_USERPROFILE_LOGOUTFROMALL.'</a></p>':'').'
        </div>
      </div>
      
    </div>';
  if($f[posts]>0) {
    $i = mysql_query("SELECT c.*, u.uname, u.user_avatar FROM comments c LEFT JOIN e_xoops_users u ON u.uid=c.uid WHERE c.uid='".$_SESSION['logged']."' ORDER BY c.datum DESC LIMIT 5");
    $num = mysql_num_rows($i);
    $content .= '
    <div class="card shadow my-4 animated--grow-in">
      <div class="card-header">
        <h6 class="m-0 font-weight-bold text-hl">'.sprintf(LANG_USERPROFILE_LASTCOMMENTS, $num).'</h6>
      </div>
      <div class="card-body">';
        while($o = mysql_fetch_array($i))
          {
          $cid = $o[id];
          $comments[$cid]['main'] = array($o[id], $o[uname], 0, $o[uid], $o[name], $o[user_avatar], $o[comment], $o[replyto], $o[datum], $o[what], $o[whatid]);
          }
        foreach($comments as $item)
          {
          $content .= ShowComment($item, true);
          }
     $content .= '
      </div>
    </div>
    ';
    }
$content .= '
  </div>
    
    <script>
      var teams = [';
    $w = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
    $w = mysql_query("SELECT shortname, longname FROM 2004teams GROUP BY shortname UNION SELECT shortname, longname FROM el_teams GROUP BY shortname ORDER BY longname ASC");
    while($e = mysql_fetch_array($w))
      {
      $content .= '
        {
          value: "'.$e[shortname].'",
          label: "'.$e[longname].'"
        },';
      }
    $content = substr_replace( $content, "", -1 );
    $content .= '];
    </script>';
    }
  else
    {
    $content .= "Nie ste prihlásený!";
    }
  }
// stranka vsetkych notifikacii
if($_GET[notif])
  {
  if($_SESSION['logged'])
    {
    $q = mysql_query("SELECT * FROM e_xoops_users WHERE uid='".$_SESSION['logged']."'");
    $f = mysql_fetch_array($q);
    $w = mysql_query("SELECT * FROM user_notifications WHERE uid='".$_SESSION['logged']."' ORDER BY datetime DESC LIMIT 20");
    if($f[user_avatar]!="") $avatar = '/images/user_avatars/'.$_SESSION['logged'].'.'.$f[user_avatar];
    else $avatar = '/img/players/no_photo.jpg';

    $leaguecolor = "hl";
    $title = LANG_NAV_NOTIFTITLE;
    
    $content .= "<div id='toasts' class='fixed-top' style='top: 80px; right: 23px; left: initial; z-index:3;'></div>
                 <img class='float-left img-profile img-thumbnail mr-2 rounded-circle' src='".$avatar."' style='width: 55px;'>
                 <h1 class='h3 h3-fluid mb-1'>".LANG_NAV_NOTIFTITLE."</h1>
                 <h2 class='h6 h6-fluid text-hl text-uppercase font-weight-bold mb-3'>".$f[uname]."</h2>
                 <div style='max-width: 1000px;'>";
                 
    if(mysql_num_rows($w)>0) $content .= '<p class="text-right"><button class="btn btn-sm btn-hl" id="markread"><i class="fas fa-check-double"></i> '.LANG_NAV_NOTIFMARK.'</button></p>';
    $content .= '<div class="card notifications shadow py-2">';
    while($e = mysql_fetch_array($w))
      {
       if($e[what]==0)
        {
        $el = substr($e[whatid], -1);
        $dl = strlen($e[whatid]);
        $ide = substr($e[whatid], 0, $dl-1);
        if($el==1) $t = mysql_query("SELECT * FROM el_matches WHERE id='".$ide."'");
        else $t = mysql_query("SELECT * FROM 2004matches WHERE id='".$ide."'");
        $r = mysql_fetch_array($t);
        $icon = 'trophy';
        $color = 'success';
        $url = '/report/'.$e[whatid].'-'.SEOtitle($r[team1long]." vs. ".$r[team2long]);
        $text = sprintf(LANG_NOTIF_FAVTEAM, $r[team1long], $r[team2long], $r[goals1], $r[goals2]);
        }
       elseif($e[what]==1)
        {
        $exp = explode("-", $e[whatid]);
        $t = mysql_query("SELECT * FROM 2004leagues WHERE id='".$exp[0]."'");
        $r = mysql_fetch_array($t);
        $icon = 'plus';
        $color = 'primary';
        $url = '/bets';
        $text = sprintf(LANG_NOTIF_BET, $r[longname], $exp[1]);
        }
       elseif($e[what]==2)
        {
        $icon = 'user-clock';
        $color = 'danger';
        $url = '/fantasy/draft';
        $text = LANG_FANTASY_MAILSUBJECT;
        }
       elseif($e[what]==3)
        {
        $t = mysql_query("SELECT c.*, u.uname FROM comments c LEFT JOIN e_xoops_users u ON u.uid=c.uid WHERE c.id='".$e[whatid]."'");
        $r = mysql_fetch_array($t);
        if($r[what]==0) { $url = "/news/".$r[whatid]."#comments"; }
        if($r[what]==1) { $url = "/team/".$r[whatid]."#comments"; }
        if($r[what]==2) { $url = "/game/".$r[whatid]."#comments"; }
        if($r[what]==3) 
          {
          if(substr($r[whatid], -1)=="p") $url = "/player/".substr($r[whatid], 0, -1)."#comments";
          if(substr($r[whatid], -1)=="g") $url = "/goalie/".substr($r[whatid], 0, -1)."#comments";
          }
        $icon = 'reply';
        $color = 'danger';
        if($r[uid]==0) $name = LANG_NOTIF_SOMEBODY;
        else $name = LANG_LOGED_AS." ".$r[uname];
        $text = sprintf(LANG_NOTIF_REPLY, $name);
        }
      $content .= '
                <a class="dropdown-item d-flex align-items-center'.($e[isread]==0 ? ' alert-warning':'').'" href="'.$url.'" data-id="'.$e[id].'">
                  <div class="align-self-start mr-3">
                    <div class="icon-circle bg-'.$color.'">
                      <i class="fas fa-'.$icon.' text-white"></i>
                    </div>
                  </div>
                  <div class="text-wrap p-fluid">
                    <div class="small text-gray-500">'.date("j.n.Y H:i", strtotime($e[datetime])).'</div>
                    <span'.($e[isread]==0 ? ' class="font-weight-bold"':'').'>'.$text.'</span>
                  </div>
                </a>';
      }
    if(mysql_num_rows($e)==0) $notif .= '<div class="card-body"><p class="dropdown-item text-gray-600">'.LANG_NAV_NOTIFNO.' ...</p></div>';
    $content .= '</div>
    </div>';
    $script_end = '
    <script>
    $("#markread").on(\'click\', function() {
      var dataString = "markread=all";
      $.ajax({
      type: "POST",
      url: "/includes/notifications.php",
      dataType: "text",
      contentType:"application/x-www-form-urlencoded; charset=utf-8",
      data: dataString,
      cache: false,
      success: function(data){
        if(data)
          {
          var today  = new Date();
          var smallText = today.toLocaleDateString("sk-SK");
          var notif = $(\'.dropdown-item\');
          notif.each(function( i ) {
              $(this).removeClass("alert-warning");
              $(this).find("span").removeClass("font-weight-bold");
            });
          $(".badge-counter").hide();
          Notification("check-double text-success", "Notifikácie", smallText, \'Všetky notifikácie boli označené ako prečítané.\', 5000);
          }
        else
          {
          Notification("check-double text-danger", "Chyba", smallText, \'Nastala chyba pri označovaní notifikácií.\', 5000);
          }
        }
      });
      return false;
    });
    </script>';
    }
  else
    {
    $content .= "Nie ste prihlásený!";
    }
  }
// profil ineho uzivatela
elseif($id)
  {
  $leaguecolor = "hl";
  $q = mysql_query("SELECT * FROM e_xoops_users WHERE uid='$id'");
  if(mysql_num_rows($q)>0)
    {
    $f = mysql_fetch_array($q);
    $r = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
    $r = mysql_query("SELECT id, shortname, longname, 0 as el FROM 2004teams where shortname='".$f[user_favteam]."' UNION SELECT id, shortname, longname, 1 as el FROM el_teams WHERE shortname='".$f[user_favteam]."' ORDER BY id DESC LIMIT 1");
    $t = mysql_fetch_array($r);
    $y = mysql_query("SELECT sum(points) as poc FROM `fl_wallet` WHERE uid='$id' GROUP BY uid");
    if(mysql_num_rows($y)>0) $u = mysql_fetch_array($y);
    if($f[tip_points]>0)
      {
      $i = mysql_query("SET @rank=0;");
      $i = mysql_query("SELECT * FROM (SELECT @rank:=@rank+1 AS rank, uid, tip_points FROM e_xoops_users ORDER BY tip_points DESC)dt WHERE dt.uid='".$id."'");
      $o = mysql_fetch_array($i);
      }
    if($f[user_avatar]!="") $avatar = '/images/user_avatars/'.$id.'.'.$f[user_avatar];
    else $avatar = '/img/players/no_photo.jpg';
    $title = LANG_USERPROFILE_TITLE." ".$f[uname];
       
    $content .= "<img class='animated--fade-in float-left img-profile img-thumbnail mr-2 rounded-circle' src='".$avatar."' style='width: 55px;'>
                 <h1 class='h3 h3-fluid mb-1'>".LANG_USERPROFILE_TITLE."</h1>
                 <h2 class='h6 h6-fluid text-hl text-uppercase font-weight-bold mb-3'>".$f[uname]."</h2>
                 <div style='max-width: 1000px;'>";
    
    $content .= '
    <div class="card-deck pt-2">
    
      <div class="card shadow animated--grow-in">
        <div class="card-header">
          <h6 class="m-0 font-weight-bold text-hl">Základné informácie</h6>
        </div>
        <div class="card-body">
          <dl class="row">
            '.($f[name]!="" ? '<dt class="col-sm-6">'.LANG_USERPROFILE_NAME.'</dt><dd class="col-sm-6">'.$f[name].'</dd>' : '').'
            '.($f[user_from]!="" ? '<dt class="col-sm-6">Pochádza z</dt><dd class="col-sm-6">'.$f[user_from].'</dd>' : '').'
            '.($f[user_occ]!="" ? '<dt class="col-sm-6">Zamestnanie</dt><dd class="col-sm-6">'.$f[user_occ].'</dd>' : '').'
            '.($f[user_intrest]!="" ? '<dt class="col-sm-6">Záľuby</dt><dd class="col-sm-6">'.$f[user_intrest].'</dd>' : '').'
            '.($f[user_favteam]!="0" ? '<dt class="col-sm-6">'.LANG_USERPROFILE_FAVTEAM.'</dt><dd class="col-sm-6"><img class="flag-'.($t[el]==0 ? 'iihf':'el').' '.$t[shortname].'-small" src="/img/blank.png" alt="'.$t[longname].'"> '.$t[longname].'</dd>' : '').'

            <dt class="col-sm-6">'.LANG_USERPROFILE_REGISTERED.'</dt>
            <dd class="col-sm-6">'.date("j.n.Y H:i",$f[user_regdate]).'</dd>

            <dt class="col-sm-6">'.LANG_USERPROFILE_LASTLOGIN.'</dt>
            <dd class="col-sm-6">'.($f[last_login]==0 ? 'nikdy':date("j.n.Y H:i",$f[last_login])).'</dd>
            
            '.($f[url]!="" ? '<dt class="col-sm-6">Webstránka</dt><dd class="col-sm-6"><a href="'.$f[url].'" target="_blank" rel="noopener"><i class="fas fa-globe-americas"></i></a></dd>' : '').'
          </dl>
        </div>
      </div>
      
      <div class="card shadow animated--grow-in">
        <div class="card-header">
          <h6 class="m-0 font-weight-bold text-hl">Štatistiky</h6>
        </div>
        <div class="card-body">
          <dl class="row">
            <dt class="col-sm-8">'.LANG_USERPROFILE_POINTS.'</dt>
            <dd class="col-sm-4"><span class="text-hl">'.$f[tip_points].'</span>'.($f[tip_points]>0 ? ' <span class="text-xs">('.$o[rank].'.miesto)':'').'</span></dd>
            
            <dt class="col-sm-8">Počet bodov vo Fantasy KHL</dt>
            <dd class="col-sm-4 text-hl">'.($u[poc] ? $u[poc] : '0').'</dd>

            <dt class="col-sm-8">Počet komentárov</dt>
            <dd class="col-sm-4 text-hl">'.$f[posts].'</dd>
            
          </dl>
        </div>
      </div>
      
    </div>';
    
  if($f[posts]>0) {
    $i = mysql_query("SELECT c.*, u.uname, u.user_avatar FROM comments c LEFT JOIN e_xoops_users u ON u.uid=c.uid WHERE c.uid='".$id."' ORDER BY c.datum DESC LIMIT 5");
    $num = mysql_num_rows($i);
    $content .= '
    <div class="card shadow my-4 animated--grow-in">
      <div class="card-header">
        <h6 class="m-0 font-weight-bold text-hl">'.sprintf(LANG_USERPROFILE_LASTCOMMENTS, $num).'</h6>
      </div>
      <div class="card-body">';
        while($o = mysql_fetch_array($i))
          {
          $id = $o[id];
          $comments[$id]['main'] = array($o[id], $o[uname], 0, $o[uid], $o[name], $o[user_avatar], $o[comment], $o[replyto], $o[datum], $o[what], $o[whatid]);
          }
        foreach($comments as $item)
          {
          $content .= ShowComment($item, true);
          }
     $content .= '
      </div>
    </div>
    ';
    }
$content .= '
  </div>';
    }
  else
    {
    $content .= "<div class='alert alert-warning' role='alert'><i class='fas fa-user-slash'></i> Neexistujúci užívateľ</div>";
    }
  }
?>