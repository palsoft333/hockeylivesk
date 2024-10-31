<?
function GetTopics() {
    Global $link;
    $out = '<div class="card shadow my-4 animated--grow-in">
                  <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-hl">'.LANG_FORUM_TOPICS.'</h6>
                  </div>
                  <div class="card-body">
                    <h6 class="border-bottom font-weight-bold">'.LANG_FORUM_TEAMTOPICS.'</h6>';
    $q = mysqli_query($link, "SELECT c.*, count(c.id) as poc, MAX(datum) as maxdatum, IF(RIGHT(c.whatid,1)='0',t1.shortname,t2.shortname) as shortname, IF(RIGHT(c.whatid,1)='0',t1.longname,t2.longname) as longname FROM `comments` c LEFT JOIN 2004teams as t1 ON t1.id=LEFT(c.whatid,char_length(c.whatid)-1) LEFT JOIN el_teams as t2 ON t2.id=LEFT(c.whatid,char_length(c.whatid)-1) WHERE c.what=1 GROUP BY IF(RIGHT(c.whatid,1)='0',t1.shortname,t2.shortname) ORDER BY maxdatum DESC LIMIT 10");
    while($f = mysqli_fetch_array($q)) {
        $out .= '<div class="row p-fluid">
                    <div class="col-9 col-sm-6 col-xl-9">
                        <a href="/team/'.$f["whatid"].'-'.SEOtitle($f["longname"]).'#tocomments">'.$f["longname"].'</a>
                    </div>
                    <div class="col-3 col-sm-2 col-xl-1 text-right">
                        '.$f["poc"].'<i class="fas fa-message fa-sm ml-1"></i>
                    </div>
                    <div class="col-4 col-xl-2 text-right d-none d-sm-block">
                        '.time_elapsed_string($f["maxdatum"]).'
                    </div>
                </div>';
    }
    $out .= '
                    <h6 class="border-bottom font-weight-bold pt-4">'.LANG_FORUM_GAMETOPICS.'</h6>';
    $q = mysqli_query($link, "SELECT c.*, count(c.id) as poc, MAX(datum) as maxdatum, IF(RIGHT(c.whatid,1)='0',t1.team1long,t2.team1long) as team1long, IF(RIGHT(c.whatid,1)='0',t1.team2long,t2.team2long) as team2long FROM `comments` c LEFT JOIN 2004matches as t1 ON t1.id=LEFT(c.whatid,char_length(c.whatid)-1) LEFT JOIN el_matches as t2 ON t2.id=LEFT(c.whatid,char_length(c.whatid)-1) WHERE c.what=2 GROUP BY c.whatid ORDER BY maxdatum DESC LIMIT 10");
    while($f = mysqli_fetch_array($q)) {
        $out .= '<div class="row p-fluid">
                    <div class="col-9 col-sm-6 col-xl-9">
                        <a href="/game/'.$f["whatid"].'-'.SEOtitle($f["team1long"].' vs. '.$f["team2long"]).'#tocomments">'.$f["team1long"].' vs. '.$f["team2long"].'</a>
                    </div>
                    <div class="col-3 col-sm-2 col-xl-1 text-right">
                        '.$f["poc"].'<i class="fas fa-message fa-sm ml-1"></i>
                    </div>
                    <div class="col-4 col-xl-2 text-right d-none d-sm-block">
                        '.time_elapsed_string($f["maxdatum"]).'
                    </div>
                </div>';
    }
    $out .= '
                    <h6 class="border-bottom font-weight-bold pt-4">'.LANG_FORUM_PLAYERTOPICS.'</h6>';
    $q = mysqli_query($link, "SELECT c.*, count(DISTINCT c.id) as poc, MAX(datum) as maxdatum, IF(t1.name IS NOT NULL,CONCAT(t1.id,'0'),CONCAT(t1.id,'1')) as pid, LEFT(c.whatid,char_length(c.whatid)-1) as pname FROM `comments` c LEFT JOIN 2004players as t1 ON t1.name=LEFT(c.whatid,char_length(c.whatid)-1) LEFT JOIN el_players as t2 ON t2.name=LEFT(c.whatid,char_length(c.whatid)-1) WHERE c.what=3 GROUP BY LEFT(c.whatid,char_length(c.whatid)-1) ORDER BY maxdatum DESC LIMIT 10");
    while($f = mysqli_fetch_array($q)) {
        $out .= '<div class="row p-fluid">
                    <div class="col-9 col-sm-6 col-xl-9">
                        <a href="/player/'.$f["pid"].'-'.SEOtitle($f["pname"]).'#tocomments">'.$f["pname"].'</a>
                    </div>
                    <div class="col-3 col-sm-2 col-xl-1 text-right">
                        '.$f["poc"].'<i class="fas fa-message fa-sm ml-1"></i>
                    </div>
                    <div class="col-4 col-xl-2 text-right d-none d-sm-block">
                        '.time_elapsed_string($f["maxdatum"]).'
                    </div>
                </div>';
    }
    $out .= '
                    <h6 class="border-bottom font-weight-bold pt-4">'.LANG_FORUM_NEWSTOPICS.'</h6>';
    $q = mysqli_query($link, "SELECT c.*, count(c.id) as poc, MAX(datum) as maxdatum, s.title FROM `comments` c LEFT JOIN e_xoops_stories as s ON s.storyid=c.whatid WHERE c.what=0 GROUP BY c.whatid ORDER BY maxdatum DESC LIMIT 10");
    while($f = mysqli_fetch_array($q)) {
        $out .= '<div class="row p-fluid">
                    <div class="col-9 col-sm-6 col-xl-9">
                        <a href="/news/'.$f["whatid"].'-'.SEOtitle($f["title"]).'#tocomments">'.$f["title"].'</a>
                    </div>
                    <div class="col-3 col-sm-2 col-xl-1 text-right">
                        '.$f["poc"].'<i class="fas fa-message fa-sm ml-1"></i>
                    </div>
                    <div class="col-4 col-xl-2 text-right d-none d-sm-block">
                        '.time_elapsed_string($f["maxdatum"]).'
                    </div>
                </div>';
    }
    $out .= '       
                  </div>
                </div>';
    return $out;
}
?>