<?
$id = explode("-", htmlspecialchars($_GET[id]));
$q = mysql_query("SELECT s.*, t.topic_title, u.name, u.uid, u.user_avatar FROM e_xoops_stories s LEFT JOIN e_xoops_topics t ON s.topicid=t.topic_id LEFT JOIN e_xoops_users u ON u.uid=s.uid WHERE storyid='".$id[0]."'");
if(mysql_num_rows($q)>0)
  {
  $f = mysql_fetch_array($q);
  $w = mysql_query("SELECT * FROM 2004leagues WHERE topic_id='".$f[topicid]."' ORDER BY id DESC LIMIT 1");
  $e = mysql_fetch_array($w);
  if($f[topicid]==4 || mysql_num_rows($w)==0) $e[color]="hl";

  $leaguecolor = $e[color];
  $active_league = $e[id];
  $title = $f[title];
  $author = $f[name];
  preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $f[hometext], $image);
  if($image['src']) $meta_image = "https://www.hockey-live.sk/".str_replace("../","",$image['src']);
  $desc = html_entity_decode(strip_tags($f[hometext]),ENT_COMPAT,"");
  $desc = str_replace("&scaron;","",$desc);
  $article_meta_tags = '<meta property="og:title" content="'.$f[title].'" />
      <meta property="og:type" content="article" />
      <meta property="og:url" content="https://'.$_SERVER[HTTP_HOST].$_SERVER[REQUEST_URI].'" />
      <meta property="og:description" content="'.$desc.'" />     
      <meta name="twitter:card" content="summary" />
      <meta name="twitter:title" content="'.$f[title].'" />
      <meta name="twitter:description" content="'.$desc.'" />
  ';

  $f[hometext] = str_replace("news-image","col-12 col-sm-5 col-xl-2 float-left img-thumbnail mr-3 mb-2 p-1",$f[hometext]);

  $content .= '
  <div class="row">
    <div class="col-lg-9 mb-3">
      <div class="card shadow mb-3">
        <div class="card-body">
          <p class="h6 h6-fluid font-weight-bold text-'.$leaguecolor.' text-uppercase">'.$f[topic_title].'</h2>
          <h2 class="h2 h2-fluid">'.$f[title].'</h2>
          <div class="text-justify p-fluid">'.$f[hometext].$f[bodytext].'</div>
        </div>
      </div>
      <div class="card shadow">
          <div class="card-body">
          '.GenerateComments(0,$id[0]).'
          </div>
      </div>
    </div>
    <div class="col-lg-3">
      <div class="card shadow mb-3">
        <div class="card-body text-center">
          <div class="m-auto w-25" style="max-width: 100px; min-width: 60px;">
            <img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="/images/user_avatars/'.$f[uid].".".$f[user_avatar].'" class="lazy img-thumbnail mb-2 rounded-circle shadow-sm">
          </div>
          <p class="h6 h6-fluid font-weight-bold">'.$f[name].'</p>
          <hr>
          <p class="small"><strong>'.LANG_PUBLISHED.':</strong> '.date("j.n.Y H:i",$f[published]).'</p>
        </div>
      </div>
      <div class="card shadow mb-2 articleBanner">
        <div class="card-body">
          <!--<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>-->
          <!-- HL v článku -->
          <!--<ins class="adsbygoogle"
               style="display:block"
               data-ad-client="ca-pub-8860983069832222"
               data-ad-slot="1141079059"
               data-ad-format="auto"
               data-full-width-responsive="true"></ins>
          <script>
               (adsbygoogle = window.adsbygoogle || []).push({});
          </script>-->
          <div id="101390-3">
            <script src="//ads.themoneytizer.com/s/gen.js?type=3"></script>
            <script src="//ads.themoneytizer.com/s/requestform.js?siteId=101390&formatId=3"></script>
          </div>
        </div>
      </div>
    </div>
  </div>';

  if(!in_array($id[0], $_SESSION["visited_articles"])) {
    mysql_query("UPDATE e_xoops_stories SET counter=counter+1 WHERE storyid='".$id[0]."'");
    $_SESSION["visited_articles"][] = $id[0];
    }

  }
else
  {
  $leaguecolor = "hl";
  $content = "<div class='alert alert-warning' role='alert'><i class='far fa-newspaper'></i> Neexistujúci článok</div>";
  }
?>