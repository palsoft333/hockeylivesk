<?
include("db.php");

function webpImage($source, $quality = 100)
    {
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "referer: https://www.iihf.com/\r\n" .
                    "sec-fetch-dest: image\r\n" .
                    "sec-fetch-mode: no-cors\r\n" .
                    "sec-fetch-site: cross-site\r\n"
            ]
        ];

        $context = stream_context_create($opts);

        $info = getimagesize($source);
        $isAlpha = false;
        if ($info['mime'] == 'image/jpeg')
            $image = imagecreatefromstring(file_get_contents($source, false, $context));
        elseif ($isAlpha = $info['mime'] == 'image/gif') {
            $image = imagecreatefromstring(file_get_contents($source, false, $context));
        } elseif ($isAlpha = $info['mime'] == 'image/png') {
            $image = imagecreatefromstring(file_get_contents($source, false, $context));
        } else {
            return $source;
        }
        if ($isAlpha && $image) {
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }
        if($image) $newImage = imagewebp($image, null, $quality);
        else return false;

        return $newImage;
    }

function ParseName($meno)
	{
	$kde = stripos($meno," ");
	$dlz = strlen($meno);
	$krstne = substr($meno, 0, $kde);
	$priezv = substr($meno, $kde+1, $dlz);
	$meno = $priezv." ".$krstne;
	$meno = str_replace("˝", "L", $meno);
	$meno = str_replace("Ł", "L", $meno);
	$znak = array("ľ","š","č","ť","ž","ý","á","í","ĺ","Ĺ","é","ň","ä","ú","ô","ó","ö","ü","Ľ","Š","Č","Ť","Ž","Ý","Á","Í","É","Ě","Ň","Ú","Ó","Ü", "Ů", "Ř", "Ď", "ř", "ě", " ", "Ă", "Ä", "Ö");
	$replacer = array("l","s","c","t","z","y","a","i","l","L","e","n","a","u","o","o","o","u","L","S","C","T","Z","Y","A","I","E","E","N","U","O","U","U", "R", "D", "r", "e" ,"%20", "A", "A", "O");
	$meno = str_replace($znak,$replacer,$meno);
	return $meno;
	}
	
function ParseName2($meno)
	{
	$meno = str_replace("˝", "L", $meno);
	$meno = str_replace("Ł", "L", $meno);
	$znak = array("ľ","š","č","ť","ž","ý","á","í","ĺ","Ĺ","é","ň","ä","ú","ô","ó","ö","ü","Ľ","Š","Č","Ť","Ž","Ý","Á","Í","É","Ě","Ň","Ú","Ó","Ü", "Ů", "Ř", "Ď", "ř", "ě", " ", "Ă", "Ä", "Ö");
	$replacer = array("l","s","c","t","z","y","a","i","l","L","e","n","a","u","o","o","o","u","L","S","C","T","Z","Y","A","I","E","E","N","U","O","U","U", "R", "D", "r", "e" ,"%20", "A", "A", "O");
	$meno = str_replace($znak,$replacer,$meno);
	return $meno;
	}
	
function Translate($player)
  {
	$player = str_replace("Achmed", "Akhmed", $player);
	$player = str_replace("Alexej", "Alexei", $player);
	$player = str_replace("Arťom", "Artyom", $player);
	$player = str_replace("Dmitrij", "Dmitry", $player);
	$player = str_replace("Jevgenij", "Evgeny", $player);
	$player = str_replace("Fjodor", "Fyodor", $player);
	$player = str_replace("Grigorij", "Grigory", $player);
	$player = str_replace("Iľja", "Ilya", $player);
	$player = str_replace("Jiří", "Jiri", $player);
	$player = str_replace("Michail", "Mikhail", $player);
	$player = str_replace("Nikolaj", "Nikolai", $player);
	$player = str_replace("Semion", "Semyon", $player);
	$player = str_replace("Sergej", "Sergei", $player);
	$player = str_replace("Timofej", "Timofei", $player);
	$player = str_replace("Vasilij", "Vasily", $player);
	$player = str_replace("Vitalij", "Vitaly", $player);
	$player = str_replace("Vjačeslav", "Vyacheslav", $player);
	$player = str_replace("Jakov", "Yakov", $player);
	$player = str_replace("Jegor", "Yegor", $player);
	$player = str_replace("Jurij", "Yury", $player);
	return $player;
  }
  
$name = $_GET["name"] ?? null;

// najprv skus nasu DB
$q = mysqli_query($link, "SELECT * FROM player_photos WHERE name='".mysqli_real_escape_string($link, $name)."'");
if(mysqli_num_rows($q)>0) {
    $f = mysqli_fetch_array($q);
    $our_url = $f["photo_url"];

    $iihf = webpImage($our_url);
    if($iihf) $ourpid=$iihf;
}
    
if(!$ourpid) {
    // NHL
    $nhlname = Translate($name);
    $nhlname = ParseName2($nhlname);
    $useragent="Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1";
    $ch = curl_init('https://search.d3.nhle.com/api/v1/search/player?culture=en-us&limit=1&q='.$nhlname);

    $file_path = '/data/2/5/25f84922-a486-4570-82bf-9b3e237ded76/hockey-live.sk/web/xadm/2004/cookies.txt';
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $file_path);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $file_path);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_REFERER, "http://www.hokejportal.sk/files/includes/szlh_parser.php");
    $r = curl_exec($ch);
    curl_close($ch);

    $out = json_decode($r, true);
    $out[0]["playerId"] = $out[0]["playerId"] ?? 0;
    $pid = $out[0]["playerId"];

    // KHL
    $khlname = str_replace("%20"," ",$name);
    $khlname = Translate($khlname);
    $khlname = ParseName2($khlname);
    $ch = curl_init('https://en.khl.ru/players/_namefeeder_en.php?term='.$khlname);

    $file_path = '/data/2/5/25f84922-a486-4570-82bf-9b3e237ded76/hockey-live.sk/web/xadm/2004/cookies.txt';
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $file_path);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $file_path);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_REFERER, "http://www.hokejportal.sk/files/includes/szlh_parser.php");
    $r = curl_exec($ch);
    curl_close($ch);

    $khl = json_decode($r, true);
    $khlpid = $khl[0]['id'];

    // SZLH
    $szlhname = urlencode($name);
    $ch = curl_init('https://www.hockeyslovakia.sk/sk/search-predictive?term='.$szlhname);

    $file_path = '/data/2/5/25f84922-a486-4570-82bf-9b3e237ded76/hockey-live.sk/web/xadm/2004/cookies.txt';
    curl_setopt($ch, CURLOPT_USERAGENT, $useragent); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $file_path);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $file_path);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_REFERER, "http://www.hokejportal.sk/files/includes/szlh_parser.php");
    $r = curl_exec($ch);
    curl_close($ch);

    $szlh = json_decode($r, true);
    $szlhpid = $szlh[0]['Photo'];
    $szlhpid = str_replace(" ","%20",$szlhpid);

    if($_GET["name"]=="MIKÚŠ Juraj") $khlpid = 15348;
    if($_GET["name"]=="ČACHO Viliam Sr.") $szlhpid = "https://img.hockeyslovakia.sk/Player/186408/ViliamČACHO.jpg";
    if($_GET["name"]=="ČACHO Viliam") $szlhpid = "https://img.hockeyslovakia.sk/Player/208912/ViliamČACHO.jpg";

    // our
    $filename = str_replace("%20"," ",$name);
    $filename = ParseName($filename);
    $filename = str_replace("%20","_",$filename);
    $path = $_SERVER['DOCUMENT_ROOT']."photos/".$filename.".jpg";
}

header('Content-type: image/webp');
if($ourpid)
  {
  // fotka citana cez URL z nasej DB
  echo $ourpid;
  }
elseif($khlpid && @is_array(getimagesize("https://en.khl.ru/images/teamplayers/0/".$khlpid.".jpg")))
  {
  // KHL
  echo webpImage("https://en.khl.ru/images/teamplayers/0/".$khlpid.".jpg");
  }
elseif($szlhpid && !strstr($szlhpid, "player_default") && @is_array(getimagesize($szlhpid)))
  {
  // SZLH
  echo webpImage($szlhpid);
  }
elseif($pid && @is_array(getimagesize("https://assets.nhle.com/mugs/nhl/latest/".$pid.".png")))
  {
  // NHL
  echo webpImage("https://assets.nhle.com/mugs/nhl/latest/".$pid.".png");
  }
elseif(file_exists($path))
  {
  // vlastna databaza fotiek
  echo webpImage($path);
  }
else
  {
  // neexistuje
  echo webpImage($_SERVER['DOCUMENT_ROOT']."/img/players/no_photo.jpg");
  }

mysqli_close($link);
?>