<?
session_start();
include("db.php");
include("main_functions.php");
if(isset($_SESSION["lang"])) {
  include("lang/lang_".$_SESSION["lang"].".php");
}
else {
   $_SESSION["lang"] = 'sk';
    include("lang/lang_sk.php");
}

function DisplayTags($tags) {
    Global $link;
    $out = "";
    $tags = json_decode($tags, true);
    foreach($tags as $key => $tag) {
        if($key=="p") {
            $el = substr($tag, -1);
            $id = substr($tag, 0, -1);
            if($el==0) $q = mysqli_query($link, "SELECT * FROM 2004players WHERE id='".$id."'");
            else $q = mysqli_query($link, "SELECT * FROM el_players WHERE id='".$id."'");
            $f = mysqli_fetch_array($q);
            $out .= '<a href="/player/'.$id.$el.'-'.SEOtitle($f["name"]).'" class="tag badge badge-primary mr-1">'.$f["name"].'</a>';
        }
        elseif($key=="g") {
            $el = substr($tag, -1);
            $id = substr($tag, 0, -1);
            if($el==0) $q = mysqli_query($link, "SELECT * FROM 2004goalies WHERE id='".$id."'");
            else $q = mysqli_query($link, "SELECT * FROM el_goalies WHERE id='".$id."'");
            $f = mysqli_fetch_array($q);
            $out .= '<a href="/goalie/'.$tag.'-'.SEOtitle($f["name"]).'" class="tag badge badge-info mr-1">'.$f["name"].'</a>';
        }
        elseif($key=="t") {
            $el = substr($tag, -1);
            $id = substr($tag, 0, -1);
            if($el==0) $q = mysqli_query($link, "SELECT * FROM 2004teams WHERE id='".$id."'");
            else $q = mysqli_query($link, "SELECT * FROM el_teams WHERE id='".$id."'");
            $f = mysqli_fetch_array($q);
            $out .= '<a href="/team/'.$id.$el.'-'.SEOtitle($f["longname"]).'" class="tag badge badge-success mr-1">'.$f["longname"].'</a>';
        }
        elseif($key=="n") {
            $q = mysqli_query($link, "SELECT * FROM e_xoops_topics WHERE topic_id='".$tag."'");
            $f = mysqli_fetch_array($q);
            $out .= '<a href="/category/'.$tag.'-'.SEOtitle($f["topic_title"]).'" class="tag badge badge-warning mr-1">'.$f["topic_title"].'</a>';
        }
    }
return $out;
}

//appsecret_proof:
//echo hash_hmac('sha256', FB_ACCESS_TOKEN, 'bfa5a8b399abf1c6e95196d6ceffb083');

/*$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v9.0/397829000227/posts?fields=picture%2Cmessage%2Ccreated_time%2Cstory%2Ccomments&access_token=".FB_ACCESS_TOKEN."&appsecret_proof=f4db2f449d8064964841152ad2135dddbd896c21f5d30472c45801e2fba12754");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);

$feedData = curl_exec($ch);
curl_close($ch); 
$page_posts = json_decode($feedData, true);*/

// zmenit FB cas na unix cas
/*foreach($page_posts['data'] as &$value) {
    $value['created_time'] = strtotime($value['created_time']);
}*/
$page_posts['data'] = [];

// nacitat 10 poslednych Google News a zlucit ich s FB feedom
$s = mysqli_query($link, "SELECT s.* FROM gn_news s WHERE s.tags IS NOT NULL ORDER BY s.published DESC LIMIT 10") or die(mysqli_error($link));
$i=0;
while($a = mysqli_fetch_array($s))
  {
    if(strlen($a["summary"]) > 500) {
        $stringCut = substr($a["summary"], 0, 500);
        $a["summary"] = substr($stringCut, 0, strrpos($stringCut, ' ')).' ...';
    }
  array_push($page_posts['data'], array('id'=>$a["tags"], 'story'=>$a["title"], 'message'=>$a["summary"], 'created_time'=>strtotime($a["published"]), 'comments'=>$a["link"], 'image'=>$a["image"], 'publisher'=>$a["publisher"]));
  $i++;
  }

// nacitat nasich 5 poslednych rychlych noviniek a zlucit ich s FB feedom
$s = mysqli_query($link, "SELECT s.*, count(c.id) as comment_count FROM e_xoops_stories s LEFT JOIN comments c ON c.what='0' && c.whatid=s.storyid WHERE s.ihome='1' GROUP BY s.storyid ORDER BY s.published DESC LIMIT 5") or die(mysqli_error($link));
$i=0;
while($a = mysqli_fetch_array($s))
  {
  array_push($page_posts['data'], array('id'=>$a["storyid"], 'story'=>$a["title"], 'message'=>$a["hometext"], 'created_time'=>$a["created"], 'comments'=>$a["comment_count"], 'image'=>'', 'publisher'=>''));
  $i++;
  }
   
// zoradit podla casu
usort($page_posts['data'], function($a,$b){ $c = $b["created_time"] - $a["created_time"]; return $c; });
// odstranit poslednych 5 prispevkov (limit je 25)
unset($page_posts['data'][25], $page_posts['data'][26], $page_posts['data'][27], $page_posts['data'][28], $page_posts['data'][29]);

$i=0;
foreach($page_posts['data'] as $post){
    $our=$gn=0;
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
      $url = "https://www.facebook.com/hockeylive/posts/".$post_id[1];
      }
    elseif(!is_numeric($post['comments'])) {
      // z Google News
      $comments = 0;
      $url = $post['comments'];
      $tags = json_decode($post_id[0], true);
      if($post["image"]!="") $picture = $post["image"];
      else {
        foreach($tags as $key => $tag) {
            if($key=="p" || $key=="g") {
              $el = substr($tag, -1);
              $id = substr($tag, 0, -1);
              if($key=="p") { $nonel_table="2004players"; $el_table="el_players"; }
              if($key=="g") { $nonel_table="2004goalies"; $el_table="el_goalies"; }
              if($el==0) $q = mysqli_query($link, "SELECT * FROM $nonel_table WHERE id='".$id."'");
              else $q = mysqli_query($link, "SELECT * FROM $el_table WHERE id='".$id."'");
              $f = mysqli_fetch_array($q);
              $picture = "/includes/player_photo.php?name=".$f["name"];
            }
            elseif($key=="t" && $picture=="") {
              $el = substr($tag, -1);
              $id = substr($tag, 0, -1);
              if($el==0) $q = mysqli_query($link, "SELECT * FROM 2004teams WHERE id='".$id."'");
              else $q = mysqli_query($link, "SELECT * FROM el_teams WHERE id='".$id."'");
              $f = mysqli_fetch_array($q);
              if($el==0) $picture = "/images/vlajky/".$f["shortname"].".gif";
              else $picture = "/images/vlajky/".$f["shortname"]."_big.gif";
            }
        }
      }
      $gn=1;
    }
    else
      {
      // nasa novinka
      $comments = $post['comments'];
      $message = str_replace("news-image","bg-gray-100 float-left img-thumbnail mr-2 p-1 shadow-sm w-25",$message);
      $url = "/news/".$post_id[0]."-".SEOtitle($story);
      $our=1;
      }
    if($i % 2 == 0) {$tableclass = "";} 
    else $tableclass = " bg-light";
    echo "<table class='card d-table w-100 my-0 mb-2'>
            <tr class='card-header$tableclass'>
              <td style='width:69%;' class='pl-2'>
                <b><a href='".$url."' ".($our==1 ? "" : " target='_blank'")." class='text-dark'>".($story ? $story : LANG_FLASH_FROMFB)."</a></b>
              </td>
              <td style='width:31%;' class='text-right align-top pr-2 pt-1 text-xs'>".date("j.n.Y G:i", $post_time)."</td>
            </tr>
            ".($gn==1 ? "<tr class='$tableclass'><td colspan='2' class='pl-2 pt-1'>".DisplayTags($post_id[0])."<span class='float-right mr-1'><a href='".$url."' target='_blank'>".$post["publisher"]."</a></span></td></tr>":"")."
            <tr class='$tableclass'>
              <td colspan='2' class='p-2'>".($picture ? "<img src='data:image/svg+xml,%3Csvg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 1 0.525\"%3E%3C/svg%3E' data-src='$picture' class='lazy bg-gray-100 float-left img-thumbnail mr-2 p-1 shadow-sm w-25'>" : "")."$message</td>
            </tr>";
    if($gn==0) echo "
            <tr class='$tableclass'>
              <td colspan='2' class='text-right'><div class='comment-count float-right'>".($our==1 ? "<a href='/news/".$post_id[0]."-".SEOtitle($story)."'><i class='far fa-comment'></i> <span>$comments ".LANG_COMMENTS."</span></a>" : "<a href='https://www.facebook.com/hockeylive/posts/".$post_id[1]."' target='_blank'><i class='far fa-comment'></i> $comments ".LANG_COMMENTS."</span></a>")."</div></td>
            </tr>";
        echo "
          </table>";
    $i++;
}

mysqli_close($link);
?>