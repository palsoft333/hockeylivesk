<?
include("includes/advert_bigscreenside.php");

$leaguecolor = "hl";
$title = LANG_FORUM_TITLE;

$content .= "<div id='toasts' class='fixed-top' style='top: 80px; right: 23px; left: initial; z-index:3;'></div>
             <h1 class='h3 h3-fluid mb-1'>".LANG_FORUM_TITLE."</h1>
             <div class='row mb-4'>
                <div class='col-12' style='max-width: 1000px;'>";

                $content .= GetTopics();
                
                $i = mysql_query("SELECT c.*, u.uname, u.user_avatar FROM comments c LEFT JOIN e_xoops_users u ON u.uid=c.uid ORDER BY c.datum DESC LIMIT 10");
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
                
    $content .= '
                </div> <!-- end col -->
                <div class="col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block">
    '.$advert.'
                </div> <!-- end col -->
             </div> <!-- end row -->';
?>