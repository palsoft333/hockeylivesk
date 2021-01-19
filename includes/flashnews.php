<?
include("db.php");
include("main_functions.php");
if(isset($_SESSION[lang])) {
  include("lang/lang_$_SESSION[lang].php");
}
else {
   $_SESSION[lang] = 'sk';
    include("lang/lang_sk.php");
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v9.0/397829000227/posts?fields=picture%2Cmessage%2Ccreated_time%2Cstory%2Ccomments&access_token=".FB_ACCESS_TOKEN);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$feedData = curl_exec($ch);
curl_close($ch); 
$page_posts = json_decode($feedData, true);

// zmenit FB cas na unix cas
foreach($page_posts['data'] as &$value) {
    $value['created_time'] = strtotime($value['created_time']);
}
//$page_posts['data'] = [];

// nacitat nasich 5 poslednych rychlych noviniek a zlucit ich s FB feedom
$s = mysql_query("SET SESSION sql_mode = 'NO_ENGINE_SUBSTITUTION';") or die(mysql_error());
$s = mysql_query("SELECT s.*, count(c.id) as comment_count FROM e_xoops_stories s LEFT JOIN comments c ON c.what='0' && c.whatid=s.storyid WHERE s.ihome='1' GROUP BY s.storyid ORDER BY s.published DESC LIMIT 5") or die(mysql_error());
$i=0;
while($a = mysql_fetch_array($s))
  {
  array_push($page_posts['data'], array('id'=>$a[storyid], 'story'=>$a[title], 'message'=>$a[hometext], 'created_time'=>$a[created], 'comments'=>$a[comment_count]));
  $i++;
  }
   
// zoradit podla casu
usort($page_posts['data'], function($a,$b){ $c = $b[created_time] - $a[created_time]; return $c; });
// odstranit poslednych 5 prispevkov (limit je 25)
unset($page_posts['data'][25], $page_posts['data'][26], $page_posts['data'][27], $page_posts['data'][28], $page_posts['data'][29]);

$i=0;
foreach($page_posts['data'] as $post){
    $our=0;
    $picture="";
    $post_id = explode("_", $post['id']);
    $story = ($post['story']) ? $post['story'] : "";
    $message = ($post['message']) ? $post['message'] : " ";
    $post_time = $post['created_time'];
    if($post_id[1])
      {
      // z FB feedu
      $comments = count($post['comments']);
      $picture = ($post['picture']) ? $post['picture'] : "";
      }
    else
      {
      // nasa novinka
      $comments = $post['comments'];
      $message = str_replace("news-image","bg-gray-100 float-left img-thumbnail mr-2 p-1 shadow-sm w-25",$message);
      $our=1;
      }
    if($i % 2 == 0) {$tableclass = "";} 
    else $tableclass = " class='bg-light'";
    echo "<table class='w-100 my-0'>
            <tr$tableclass>
              <td style='width:60%;' class='py-2'><b><a href='".($our==1 ? "/news/".$post_id[0]."-".SEOtitle($story) : "https://www.facebook.com/hockeylive/posts/".$post_id[1])."' ".($our==1 ? "" : " target='_blank'").">".($story ? $story : "Príspevok z nášho Facebooku")."</a></b></td>
              <td style='width:40%;' class='text-right'>".date("j.n.Y H:i", $post_time)."</td>
            </tr>
            <tr$tableclass>
              <td colspan='2'>".($picture ? "<img src='data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=' data-src='$picture' class='lazy bg-gray-100 float-left img-thumbnail mr-2 p-1 shadow-sm w-25'>" : "")."$message</td>
            </tr>
            <tr$tableclass>
              <td colspan='2' class='text-right'><div class='comment-count float-right'>".($our==1 ? "<a href='/news/".$post_id[0]."-".SEOtitle($story)."'><i class='far fa-comment'></i> <span>$comments ".LANG_COMMENTS."</span></a>" : "<a href='https://www.facebook.com/hockeylive/posts/".$post_id[1]."' target='_blank'><i class='far fa-comment'></i> $comments ".LANG_COMMENTS."</span></a>")."</div></td>
            </tr>
          </table>";
    $i++;
}

mysql_close($link);
?>