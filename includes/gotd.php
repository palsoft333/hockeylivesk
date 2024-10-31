<?php
include("../includes/db.php");

$id = $_GET["id"];
$el = substr($id, -1);
$dl = strlen($id);
$ide = substr($id, 0, $dl-1);

if($el==1)
  {
  $q = mysqli_query($link, "SELECT * FROM el_matches WHERE id='".$ide."'");
  $f = mysqli_fetch_array($q);
  $w = mysqli_query($link, "SELECT t1.longname as team1, t2.longname as team2 FROM `el_teams` t1 LEFT JOIN el_teams t2 ON t2.shortname='".$f["team2short"]."' && t2.league='".$f["league"]."' WHERE t1.shortname='".$f["team1short"]."' && t1.league='".$f["league"]."'");
  $matches_table = "el_matches";
  }
else
  {
  $q = mysqli_query($link, "SELECT * FROM 2004matches WHERE id='".$ide."'");
  $f = mysqli_fetch_array($q);
  $l = mysqli_query($link, "SELECT * FROM 2004leagues WHERE id='".$f["league"]."'");
  $linfo = mysqli_fetch_array($l);
  $w = mysqli_query($link, "SELECT t1.longname as team1, t2.longname as team2 FROM `2004teams` t1 LEFT JOIN 2004teams t2 ON t2.shortname='".$f["team2short"]."' && t2.league='".$f["league"]."' WHERE t1.shortname='".$f["team1short"]."' && t1.league='".$f["league"]."'");
  $matches_table = "2004matches";
  }
if(mysqli_num_rows($w)>0) $e = mysqli_fetch_array($w);
else die("Zle ID zapasu");

/* Read the image into the object */
$im = new Imagick( '../xadm/2004/images/zapasdna-podklad1.png' );
$im->setImageFormat("png");

$t1 = new Imagick("../images/vlajky/".$f["team1short"]."_big.gif");
$t2 = new Imagick("../images/vlajky/".$f["team2short"]."_big.gif");

/* Make the image a little smaller, maintain aspect ratio */
$t1->thumbnailImage( 250, null );
$t2->thumbnailImage( 250, null );

/* Clone the current object */
$shadow1 = $t1->clone();
$shadow2 = $t2->clone();

/* Set image background color to black
        (this is the color of the shadow) */
$shadow1->setImageBackgroundColor( new ImagickPixel( 'white' ) );
$shadow2->setImageBackgroundColor( new ImagickPixel( 'white' ) );

/* Create the shadow */
$shadow1->shadowImage( 100, 20, 0, 0 );
$shadow2->shadowImage( 100, 20, 0, 0 );

/* Imagick::shadowImage only creates the shadow.
        That is why the original image is composited over it */
$shadow1->compositeImage( $t1, Imagick::COMPOSITE_OVER, 40, 40);
$shadow2->compositeImage( $t2, Imagick::COMPOSITE_OVER, 40, 40);

$d1 = $shadow1->getImageGeometry();
$h1 = $d1['height']/2;
$d2 = $shadow2->getImageGeometry();
$h2 = $d2['height']/2;

$im->compositeImage($shadow1, Imagick::COMPOSITE_OVER, 490, 352-$h1);
$im->compositeImage($shadow2, Imagick::COMPOSITE_OVER, 310, 710-$h2);

$text = new ImagickDraw();
$text->setFillColor('#fff');
$text->setFont('Nimbus-Sans-L-Bold-Italic');
$text->setFontSize( 40 );
$text->setTextAlignment(\Imagick::ALIGN_LEFT);

$text1 = new ImagickDraw();
$text1->setFillColor('#fff');
$text1->setFont('Nimbus-Sans-L-Bold-Italic');
$text1->setFontSize( 40 );
$text1->setTextAlignment(\Imagick::ALIGN_RIGHT);
$metrics1 = $im->queryFontMetrics($text, $team1);
$metrics2 = $im->queryFontMetrics($text1, $team2);

$im->annotateImage($text, 10, 320, 0, $e["team1"]);
$im->annotateImage($text1, 1069, 676, 0, $e["team2"]);

if(isset($linfo) && strstr($linfo["longname"],"U20")) $i = mysqli_query($link, "(SELECT m.*, 1 as roz, l.longname FROM $matches_table m LEFT JOIN 2004leagues l ON l.id=m.league WHERE (m.team1short='".$f["team1short"]."' || m.team2short='".$f["team1short"]."') && m.kedy='konečný stav' && l.longname LIKE '%U20%' ORDER BY m.datetime DESC LIMIT 5) UNION (SELECT m.*, 2 as roz, l.longname FROM $matches_table m LEFT JOIN 2004leagues l ON l.id=m.league WHERE (m.team1short='".$f["team2short"]."' || m.team2short='".$f["team2short"]."') && m.kedy='konečný stav' && l.longname LIKE '%U20%' ORDER BY m.datetime DESC LIMIT 5)");
else $i = mysqli_query($link, "(SELECT *, 1 as roz FROM $matches_table WHERE (team1short='".$f["team1short"]."' || team2short='".$f["team1short"]."') && kedy='konečný stav' ORDER BY datetime DESC LIMIT 5) UNION (SELECT *, 2 as roz FROM $matches_table WHERE (team1short='".$f["team2short"]."' || team2short='".$f["team2short"]."') && kedy='konečný stav' ORDER BY datetime DESC LIMIT 5)");
$z1=$z2=0;
$lastt1=$lastt2=[];
while($j = mysqli_fetch_array($i))
  {
  if($j["roz"]==1)
    {
    $lastt1[$z1] = array($j["team1short"], $j["team1long"], $j["team2short"], $j["team2long"], $j["goals1"], $j["goals2"], $j["datetime"], $j["id"]);
    $roz1++;
    $z1++;
    }
  else
    {
    $lastt2[$z2] = array($j["team1short"], $j["team1long"], $j["team2short"], $j["team2long"], $j["goals1"], $j["goals2"], $j["datetime"], $j["id"]);
    $roz2++;
    $z2++;
    }
  }
$lastt1 = array_reverse($lastt1);
$lastt2 = array_reverse($lastt2);
$x=0;
$p=27;
$winstroke = "#38571a";
$winfill = "#96d35f";
$lossstroke = "#831100";
$lossfill = "#ff8647";
while($x < count($lastt1))
  {
  $strokecolor=$fillcolor=$letter="";
  if($lastt1[$x][0]==$f["team1short"])
    {
    if($lastt1[$x][4]>$lastt1[$x][5]) { $strokecolor=$winstroke; $fillcolor = $winfill; $letter = "V"; }
    else { $strokecolor=$lossstroke; $fillcolor = $lossfill; $letter = "P"; }
    }
  else 
    {
    if($lastt1[$x][4]>$lastt1[$x][5]) { $strokecolor=$lossstroke; $fillcolor = $lossfill; $letter = "P"; }
    else { $strokecolor=$winstroke; $fillcolor = $winfill; $letter = "V"; }
    }
  $draw = new ImagickDraw();

  $draw->setStrokeOpacity(1);
  $draw->setStrokeColor($strokecolor);
  $draw->setFillColor($fillcolor);

  $draw->setStrokeWidth(2);
  $draw->setFontSize(20);

  $draw->circle($p, 415, $p+18, 415);
  $draw->setFontFamily("Nimbus");
  $draw->setStrokeColor($strokecolor);
  $draw->setFillColor($strokecolor);
  $draw->annotation($p-7, 423, $letter);

  $im->drawImage($draw);
  $x++;
  $p=$p+45;
  }

$x=0;
$p=875;
while($x < count($lastt2))
  {
  $strokecolor=$fillcolor=$letter="";
  if($lastt2[$x][0]==$f["team2short"])
    {
    if($lastt2[$x][4]>$lastt2[$x][5]) { $strokecolor=$winstroke; $fillcolor = $winfill; $letter = "V"; }
    else { $strokecolor=$lossstroke; $fillcolor = $lossfill; $letter = "P"; }
    }
  else 
    {
    if($lastt2[$x][4]>$lastt2[$x][5]) { $strokecolor=$lossstroke; $fillcolor = $lossfill; $letter = "P"; }
    else { $strokecolor=$winstroke; $fillcolor = $winfill; $letter = "V"; }
    }
  $draw = new ImagickDraw();

  $draw->setStrokeOpacity(1);
  $draw->setStrokeColor($strokecolor);
  $draw->setFillColor($fillcolor);

  $draw->setStrokeWidth(2);
  $draw->setFontSize(20);

  $draw->circle($p, 770, $p+18, 770);
  $draw->setFontFamily("Nimbus");
  $draw->setStrokeColor($strokecolor);
  $draw->setFillColor($strokecolor);
  $draw->annotation($p-7, 778, $letter);

  $im->drawImage($draw);
  $x++;
  $p=$p+45;
  }

/* Display the image */
mysqli_close($link);

header( "Content-Type: image/jpeg" );
echo $im;
?>