<?
error_reporting(E_ALL);
session_start();
		
include("includes/db.php");
include("includes/main_functions.php");
include("includes/teamtable.class.php");
include("includes/league_specifics.php");

$_SESSION['logged'] = $_SESSION['logged'] ?? null;
$active_league = $active_league ?? null;
$leaguecolor = $leaguecolor ?? "hl";

if(isset($_SESSION['logged']))
  {
  $la = mysqli_query($link, "SELECT lang FROM e_xoops_users WHERE uid='".$_SESSION['logged']."'");
  if ($lng = mysqli_fetch_array($la)) {
      $_SESSION["lang"] = $lng["lang"] ?? "sk";
  } else {
      $_SESSION["lang"] = "sk";
  }
  include("includes/lang/lang_".strtolower($_SESSION["lang"]).".php");
  }
else
  {
  $langs = array();

  if(!isset($_SESSION["lang"]))
    {
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

        if (count($lang_parse[1])) {
            $langs = array_combine($lang_parse[1], $lang_parse[4]);
          
            foreach ($langs as $lang => $val) {
                if ($val === '') $langs[$lang] = 1;
            }

            if (!count(array_unique($langs)) === 1) {
              arsort($langs, SORT_NUMERIC);
            }
        }
    }
    
    foreach ($langs as $lang => $val) {
      if (strpos($lang, 'sk') === 0) {
        $_SESSION["lang"] = "sk";
        break;
      }
       else if (strpos($lang, 'en') === 0) {
        $_SESSION["lang"] = "en";
        break;
      }
      else {
        $_SESSION["lang"] = "sk";
        }
      }
    if(count($langs)==0) {
      $_SESSION["lang"] = "sk";
      }
    include("includes/lang/lang_".strtolower($_SESSION["lang"]).".php");
    }
  else
    {
    if($_SESSION["lang"]=="en" || $_SESSION["lang"]=="sk") include("includes/lang/lang_".strtolower($_SESSION["lang"]).".php");
    else include("includes/lang/lang_sk.php");
    }
  }
  
if(isset($_GET["changeLang"]) && $_GET["changeLang"] != '' && ($_GET["changeLang"] == 'sk' || $_GET["changeLang"] == 'en')) {
			$_SESSION["lang"] = $_GET["changeLang"];
			if(isset($_SESSION['logged'])) mysqli_query($link, "UPDATE e_xoops_users SET lang='".$_GET["changeLang"]."' WHERE uid='".$_SESSION['logged']."'");
			header("Location: index.php");
			die();
			}

$_GET["p"] = $_GET["p"] ?? null;
switch ($_GET["p"]) {
    case "articles":
        include("includes/articles_functions.php");
        include("articles.php");
        break;
    case "games":
        include("includes/games_functions.php");
        include("games.php");
        break;
    case "game":
        include("includes/games_functions.php");
        include("games.php");
        break;
    case "teams":
        include("includes/teams_functions.php");
        include("includes/table_functions.php");
        include("teams.php");
        break;
    case "table":
        include("includes/table_functions.php");
        include("table.php");
        break;
    case "stats":
        include("stats.php");
        break;
    case "report":
        include("includes/report_functions.php");
        include("report.php");
        break;
    case "players":
        include("includes/players_functions.php");
        include("players.php");
        break;
    case "privacy":
        include("privacypolicy.php");
        break;
    case "partners":
        include("partners.php");
        break;
    case "contact":
        include("contact.php");
        break;
    case "bets":
        include("includes/bets_functions.php");
        include("bets.php");
        break;
    case "users":
        include("user.php");
        break;
    case "forum":
        include("includes/forum_functions.php");
        include("forum.php");
        break;
    case "fantasy":
        if(strstr($_GET["id"], "select")) include("fantasyleague.php");
        elseif(strstr($_GET["id"], "main")) include("fantasyleague.php");
        elseif(strstr($_GET["id"], "roster")) include("fantasyleague.php");
        else {
        include("includes/fantasy_functions.php");
        include("fantasy.php");
        }
        break;
    default:
        include("includes/homepage_functions.php");
        if(isset($_GET["topicID"]) && $_GET["topicID"]!="all") {
          $q = mysqli_query($link, "SELECT * FROM 2004leagues WHERE topic_id='".$_GET["topicID"]."' ORDER BY id DESC LIMIT 1");
          if(mysqli_num_rows($q)>0) {
            $f = mysqli_fetch_array($q);
            $leaguecolor = LeagueColor($f["longname"]);
            $active_league = $f["id"];
            $title = Get_SEO_title($_GET["topicID"]);
          }
          else {
              $leaguecolor = "hl";
              $active_league = null;
              $title = LANG_NAV_NEWS;
          }
        }
        else {
          $leaguecolor = "hl";
          $active_league = null;
          if(isset($_GET["topicID"])) $title = LANG_NAV_NEWS;
        }
}
header('Content-Type: text/html; charset=utf-8');
if(!isset($title)) $title="hockey-LIVE.sk | Každodenná dávka hokejovej eufórie";
if(!isset($meta_image)) $meta_image = "https://www.hockey-live.sk/images/hl_avatar.png";

if(!isset($_SESSION['logged'])) CheckCookieLogin();
else mysqli_query($link, "UPDATE e_xoops_users SET last_login='".time()."' WHERE uid='".$_SESSION['logged']."'");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <? include_once("includes/google_analytics.php"); ?>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="<? if(!isset($_GET["p"]) && !isset($_GET["topicID"])) echo "Portál pokrývajúci slovenský a zahraničný ľadový hokej. Štatistiky, tipovanie, databáza hráčov, výsledkový servis z domácich líg, zahraničných turnajov, KHL a NHL."; else echo $title; ?>">

  <meta name="author" content="<? if(isset($author)) echo $author; else echo "hockey-LIVE.sk"; ?>" />
  <meta name="title" content="<? echo $title; ?>" />
  <meta property="og:image" content="<? echo $meta_image; ?>" />
  <meta name="twitter:image" content="<? echo $meta_image; ?>" />
  <? if(!isset($_GET["p"])) echo '<meta property="og:description" content="Portál pokrývajúci slovenský a zahraničný ľadový hokej. Štatistiky, tipovanie, databáza hráčov, výsledkový servis z domácich líg, zahraničných turnajov, KHL a NHL." />'; ?>
  <? if(isset($article_meta_tags)) echo $article_meta_tags; ?>
  <link rel="apple-touch-icon" sizes="57x57" href="/img/favicon/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="/img/favicon/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="/img/favicon/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="/img/favicon/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="/img/favicon/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/img/favicon/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="/img/favicon/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/img/favicon/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192"  href="/img/favicon/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="/img/favicon/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png">
  <link rel="manifest" href="/img/favicon/manifest.json">
  <link rel="preconnect" href="https://www.google.com/">
  <link rel="preconnect" href="https://www.google.sk/">
  <link rel="preconnect" href="https://adservice.google.com/">
  <link rel="preconnect" href="https://adservice.google.sk/">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="/img/favicon/ms-icon-144x144.png">
  <title><? echo $title; ?></title>
  <style>
    html {
      display: none;
    }
  </style>
  <?
  echo '<link href="/css/flagsprites.css?v=1.0.2" rel="stylesheet">';
  if($_GET["p"]=="stats") echo '<link href="/vendor/datatables/dataTables.bootstrap4.min.css?v=1.13.4" rel="stylesheet">';
  if($_GET["p"]=="users") echo '<link href="/css/croppie.min.css?v=2.6.4" rel="stylesheet">';
  if($_GET["p"]=="articles") echo '<link rel="stylesheet" href="/css/jquery.fancybox.min.css?v=3.5.7" />';
  if($_GET["p"]=="games" || $_GET["p"]=="teams" || $_GET["p"]=="report" || $_GET["p"]=="articles" || $_GET["p"]=="players" || $_GET["p"]=="fantasy") echo '<link rel="stylesheet" href="/css/jquery.emojiarea.css?v=1.0.0" />';
  ?>

<!-- Quantcast Choice. Consent Manager Tag v2.0 (for TCF 2.0) -->
<script type="text/javascript" async=true>
(function() {
  var host = 'www.themoneytizer.com';
  var element = document.createElement('script');
  var firstScript = document.getElementsByTagName('script')[0];
  var url = 'https://cmp.quantcast.com'
    .concat('/choice/', '6Fv0cGNfc_bw8', '/', host, '/choice.js');
  var uspTries = 0;
  var uspTriesLimit = 3;
  element.async = true;
  element.type = 'text/javascript';
  element.src = url;

  firstScript.parentNode.insertBefore(element, firstScript);

  function makeStub() {
    var TCF_LOCATOR_NAME = '__tcfapiLocator';
    var queue = [];
    var win = window;
    var cmpFrame;

    function addFrame() {
      var doc = win.document;
      var otherCMP = !!(win.frames[TCF_LOCATOR_NAME]);

      if (!otherCMP) {
        if (doc.body) {
          var iframe = doc.createElement('iframe');

          iframe.style.cssText = 'display:none';
          iframe.name = TCF_LOCATOR_NAME;
          doc.body.appendChild(iframe);
        } else {
          setTimeout(addFrame, 5);
        }
      }
      return !otherCMP;
    }

    function tcfAPIHandler() {
      var gdprApplies;
      var args = arguments;

      if (!args.length) {
        return queue;
      } else if (args[0] === 'setGdprApplies') {
        if (
          args.length > 3 &&
          args[2] === 2 &&
          typeof args[3] === 'boolean'
        ) {
          gdprApplies = args[3];
          if (typeof args[2] === 'function') {
            args[2]('set', true);
          }
        }
      } else if (args[0] === 'ping') {
        var retr = {
          gdprApplies: gdprApplies,
          cmpLoaded: false,
          cmpStatus: 'stub'
        };

        if (typeof args[2] === 'function') {
          args[2](retr);
        }
      } else {
        if(args[0] === 'init' && typeof args[3] === 'object') {
          args[3] = { ...args[3], tag_version: 'V2' };
        }
        queue.push(args);
      }
    }

    function postMessageEventHandler(event) {
      var msgIsString = typeof event.data === 'string';
      var json = {};

      try {
        if (msgIsString) {
          json = JSON.parse(event.data);
        } else {
          json = event.data;
        }
      } catch (ignore) {}

      var payload = json.__tcfapiCall;

      if (payload) {
        window.__tcfapi(
          payload.command,
          payload.version,
          function(retValue, success) {
            var returnMsg = {
              __tcfapiReturn: {
                returnValue: retValue,
                success: success,
                callId: payload.callId
              }
            };
            if (msgIsString) {
              returnMsg = JSON.stringify(returnMsg);
            }
            if (event && event.source && event.source.postMessage) {
              event.source.postMessage(returnMsg, '*');
            }
          },
          payload.parameter
        );
      }
    }

    while (win) {
      try {
        if (win.frames[TCF_LOCATOR_NAME]) {
          cmpFrame = win;
          break;
        }
      } catch (ignore) {}

      if (win === window.top) {
        break;
      }
      win = win.parent;
    }
    if (!cmpFrame) {
      addFrame();
      win.__tcfapi = tcfAPIHandler;
      win.addEventListener('message', postMessageEventHandler, false);
    }
  };

  makeStub();

  var uspStubFunction = function() {
    var arg = arguments;
    if (typeof window.__uspapi !== uspStubFunction) {
      setTimeout(function() {
        if (typeof window.__uspapi !== 'undefined') {
          window.__uspapi.apply(window.__uspapi, arg);
        }
      }, 500);
    }
  };

  var checkIfUspIsReady = function() {
    uspTries++;
    if (window.__uspapi === uspStubFunction && uspTries < uspTriesLimit) {
      console.warn('USP is not accessible');
    } else {
      clearInterval(uspInterval);
    }
  };

  if (typeof window.__uspapi === 'undefined') {
    window.__uspapi = uspStubFunction;
    var uspInterval = setInterval(checkIfUspIsReady, 6000);
  }
})();
</script>
<!-- End Quantcast Choice. Consent Manager Tag v2.0 (for TCF 2.0) -->
</head>

<body id="page-top" class="bg-gradient-<? echo $leaguecolor; ?>-darken">

  <? echo Tournament_Strip(); ?>
  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-<? echo $leaguecolor; ?> sidebar sidebar-dark accordion" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <div class="sidebar-brand-icon">
          <img src="/img/hockey_logo_big.svg" alt="hockey-LIVE.sk" class="img-fluid">
        </div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Nav Item - Main Page -->
      <li class="nav-item<? if(!isset($_GET["p"])) echo " active"; ?>">
        <a class="nav-link" href="/">
          <span><? echo LANG_NAV_MAINPAGE; ?></span></a>
      </li>
      
      <? echo Generate_Menu($active_league); ?>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        Stránka
      </div>
          
      <li class="nav-item<? if($_GET["p"]=="players" && isset($_GET["database"])) echo " active"; ?>">
        <a class="nav-link" href="/database">
          <span><? echo LANG_NAV_PLAYERDB; ?></span></a>
      </li>

      <li class="nav-item<? if($_GET["p"]=="players" && isset($_GET["watched"])) echo " active"; ?>">
        <a class="nav-link" href="/watched">
          <span><? echo LANG_NAV_PLAYERTRACKER; ?></span><span class="badge badge-light float-right" style="font-size: 12px; line-height: 16px;"><? echo LANG_NEW; ?></span></a>
      </li>

      <li class="nav-item<? if($_GET["p"]=="players" && isset($_GET["shooters"])) echo " active"; ?>">
        <a class="nav-link" href="/shooters">
          <span><? echo LANG_NAV_SHOOTERS; ?></span></a>
      </li>
      
      <li class="nav-item<? if($_GET["p"]=="forum") echo " active"; ?>">
        <a class="nav-link" href="/forum">
          <span><? echo LANG_NAV_FORUM; ?></span></a>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">
      
      <!-- Language dropdown -->
      <div class="dropdown text-center mb-3">
        <button class="btn btn-sm dropdown-toggle text-white-50" type="button" id="langselect" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border: 1px solid rgba(255,255,255,.15);">
          <? if($_SESSION["lang"]=="sk") echo '<img class="SVK-small flag-iihf rounded-pill" src="/img/blank.png" alt="Slovensky">';
             else echo '<img class="GBR-small flag-iihf rounded-pill" src="/img/blank.png" alt="English">';
          ?>
        </button>
        <div class="dropdown-menu bg-light" aria-labelledby="langselect">
          <a class="dropdown-item" href="/?changeLang=sk"><img class="SVK-small flag-iihf" src="/img/blank.png" alt="Slovensky"> Slovensky</a>
          <a class="dropdown-item" href="/?changeLang=en"><img class="GBR-small flag-iihf" src="/img/blank.png" alt="English"> English</a>
        </div>
      </div>

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle" aria-label="Toggle sidebar"></button>
      </div>

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn d-md-none rounded-circle mr-3" aria-label="Collapse menu">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Search -->
          <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
            <div class="input-group">
              <input type="text" class="form-control bg-light border-0 small search" placeholder="<? echo LANG_NAV_SEARCH; ?>" aria-label="Search" aria-describedby="basic-addon2">
              <div class="input-group-append">
                <button class="btn btn-<? echo $leaguecolor; ?>" type="button" aria-label="<? echo LANG_NAV_SEARCH; ?>">
                  <i class="fas fa-search fa-sm"></i>
                </button>
              </div>
            </div>
          </form>

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">

            <!-- Nav Item - Search Dropdown (Visible Only XS) -->
            <li class="nav-item dropdown no-arrow d-sm-none">
              <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="<? echo LANG_NAV_SEARCH; ?>">
                <i class="fas fa-search fa-fw"></i>
              </a>
              <!-- Dropdown - Messages -->
              <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                  <div class="input-group">
                    <input type="text" class="form-control bg-light border-0 small search" placeholder="<? echo LANG_NAV_SEARCH; ?>" aria-label="<? echo LANG_NAV_SEARCH; ?>" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                      <button class="btn btn-<? echo $leaguecolor; ?>" type="button">
                        <i class="fas fa-search fa-sm"></i>
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </li>

            <!-- Nav Item - Alerts -->
            <? echo Notifications(); ?>

            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <? echo User_Menu(); ?>

          </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid position-relative">
          <?
          $content=$content ?? "";
          if(!$_GET["p"]) include("homepage.php");
          else echo $content;
          ?>
        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright">
            <ul class="nav justify-content-center">
              <li class="nav-item text-center">
                <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Copyright &copy; hockey-LIVE.sk <? echo date("Y"); ?></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/privacy"><? echo LANG_PRIVACY; ?></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/partners"><? echo LANG_PARTNERS_TITLE; ?></a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="/contact"><? echo LANG_NAV_IMPERSSUM; ?></a>
              </li>
            </ul>
            <? if(!$_SESSION["logged"]) echo '
            <!--div class="d-flex d-none justify-content-center"><hr class="m-2 w-50"></div>
            <div class="text-center">
              <div class="badge badge-light badge-pill text-xs text-wrap mb-1 mb-xl-0"></div>
              <div class="badge badge-light badge-pill text-xs text-wrap"></div>
            </div-->'; 
            ?>
            <div class="d-flex d-none justify-content-center"><hr class="m-2 w-50"></div>
            <div class="text-center">
              <a href="https://www.instagram.com/hockeylive.sk" target="_blank" rel="noopener" class="text-danger"><i class="fa-2x fa-instagram-square fab"></i></a>
              <a href="https://www.facebook.com/hockeylive" target="_blank" rel="noopener" class="ml-2"><i class="fa-2x fa-facebook-square fab"></i></a>
              <a href="https://whatsapp.com/channel/0029Va8uZlAFsn0oxFqVNI3E" target="_blank" rel="noopener" class="ml-2 text-success"><i class="fa-2x fa-whatsapp-square fab"></i></a>
              <a href="https://www.buymeacoffee.com/palsoft"><img src="https://img.buymeacoffee.com/button-api/?text=<? echo LANG_BETS_BUYMEABEER; ?>&emoji=🍺&slug=palsoft&button_colour=0091e6&font_colour=ffffff&font_family=Poppins&outline_colour=ffffff&coffee_colour=FFDD00" style="height: 25px; vertical-align: -3px;" class="ml-2" alt="<? echo LANG_BETS_BUYMEABEER; ?>"></a>
            </div>
          </div>
        </div>
      </footer>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><? echo LANG_LOGOUT; ?>?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body"><? echo LANG_LOGIN_LOGOUTMSG; ?></div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal"><? echo LANG_CANCEL; ?></button>
          <a class="btn btn-hl" href="/logout"><? echo LANG_LOGOUT; ?></a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="/vendor/jquery/jquery.min.js?v=3.7.0"></script>
  <script src="/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="/vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="/js/jquery-ui.min.js?v=1.13.2"></script>
  <script src="/js/jquery.lazy.min.js"></script>
  <script src="/js/main.min.js?v=1.2.7"></script>
<? 
if(!isset($_GET["p"]) && !isset($_GET["topicID"])) echo '  <script type="text/javascript" src="/js/jquery.calendario.min.js?v=1.0.6"></script>
  <script type="text/javascript" src="/includes/lang/lang_'.$_SESSION["lang"].'.js?v=1.0.0"></script>
  <script type="text/javascript" src="/js/homepage_events.min.js?v=1.1.3"></script>';
elseif($_GET["p"]=="games")
  {
  echo '  <script type="text/javascript" src="/includes/lang/lang_'.$_SESSION["lang"].'.js?v=1.0.0"></script>
  <script type="text/javascript" src="/js/games_events.min.js?v=1.0.5"></script>';
  if(isset($_GET["gid"])) echo '  <script src="/js/jquery.emojiarea.min.js?v=1.0.0"></script>
  <script src="/images/smilies/emojis.js?v=1.0.0"></script>
  <script src="/js/comments.min.js?v=1.0.2"></script>
  <script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=onRecaptchaLoadCallback" defer></script>';
  }
elseif($_GET["p"]=="table") 
  {
  if(strstr($_GET["lid"], "playoff")) echo '  <script type="text/javascript" src="/js/games_events.min.js?v=1.0.5"></script>';
  echo '  <script type="text/javascript" src="/js/table_events.min.js?v=1.0.1"></script>';
  }
elseif($_GET["p"]=="teams") echo '  <script type="text/javascript" src="/includes/lang/lang_'.$_SESSION["lang"].'.js?v=1.0.0"></script>
  <script type="text/javascript" src="/js/teams_events.js?v=1.0.1"></script>
  <script src="/js/jquery.emojiarea.min.js?v=1.0.0"></script>
  <script src="/images/smilies/emojis.js?v=1.0.0"></script>
  <script src="/js/comments.min.js?v=1.0.2"></script>
  <script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=onRecaptchaLoadCallback" defer></script>';
elseif($_GET["p"]=="fantasy") echo '  <script type="text/javascript" src="/includes/lang/lang_'.$_SESSION["lang"].'.js?v=1.0.0"></script>
  <script type="text/javascript" src="/js/fantasy_events.min.js?v=1.0.3"></script>
  <script src="/js/jquery.emojiarea.min.js?v=1.0.0"></script>
  <script src="/images/smilies/emojis.js?v=1.0.0"></script>
  <script src="/js/comments.min.js?v=1.0.2"></script>
  <script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=onRecaptchaLoadCallback" defer></script>';
elseif($_GET["p"]=="users" && !isset($_GET["notif"]) && !isset($id)) echo '  <script src="/js/croppie.min.js?v=2.6.4"></script>
  <script src="https://code.responsivevoice.org/responsivevoice.js?key=ZN9dlYeg" defer></script>
  <script type="text/javascript" src="/includes/lang/lang_'.$_SESSION["lang"].'.js?v=1.0.1"></script>
  <script type="text/javascript" src="/js/user_events.min.js?v=1.0.7"></script>';
elseif($_GET["p"]=="report") echo '  <script type="text/javascript" src="/includes/lang/lang_'.$_SESSION["lang"].'.js?v=1.0.0"></script>
  <script type="text/javascript" src="/js/jquery.cookie.js"></script>
  <script src="https://code.responsivevoice.org/responsivevoice.js?key=ZN9dlYeg" defer></script>
  <script type="text/javascript" src="/js/report_events.php?id='.$id.$el.'"></script>
  <script src="/js/jquery.emojiarea.min.js?v=1.0.0"></script>
  <script src="/images/smilies/emojis.js?v=1.0.0"></script>
  <script src="/js/comments.min.js?v=1.0.2"></script>
  <script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=onRecaptchaLoadCallback" defer></script>';
elseif($_GET["p"]=="articles") echo '  <script type="text/javascript" src="/includes/lang/lang_'.$_SESSION["lang"].'.js?v=1.0.0"></script>
  <script src="/js/jquery.fancybox.min.js?v=3.5.7"></script>
  <script src="/js/jquery.emojiarea.min.js?v=1.0.0"></script>
  <script src="/images/smilies/emojis.js?v=1.0.0"></script>
  <script src="/js/comments.min.js?v=1.0.2"></script>
  <script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=onRecaptchaLoadCallback" defer></script>';
elseif($_GET["p"]=="players") {
  echo '  <script type="text/javascript" src="/includes/lang/lang_'.$_SESSION["lang"].'.js?v=1.0.1"></script>';
  if(isset($_GET["pid"]) || isset($_GET["gid"])) {
    echo '  <script src="/js/jquery.emojiarea.min.js?v=1.0.0"></script>
  <script src="/images/smilies/emojis.js?v=1.0.0"></script>
  <script src="/js/comments.min.js?v=1.0.2"></script>
  <script src="https://www.google.com/recaptcha/api.js?render=explicit&onload=onRecaptchaLoadCallback" defer></script>';
  }
  if(isset($_GET["watched"])) {
      echo '  <script src="/js/watched_events.js?v=1.1.7"></script>';
  }
}

if($_GET["p"]=="stats" || $_GET["p"]=="players" || $_GET["p"]=="teams" || $_GET["p"]=="bets" || $_GET["p"]=="fantasy") echo '  <script type="text/javascript" src="/vendor/datatables/jquery.dataTables.min.js?v=1.13.4"></script>
  <script src="/vendor/datatables/dataTables.bootstrap4.min.js?v=1.13.4"></script>';

$script_end = $script_end ?? null;
echo $script_end;
?>
  <link href="/vendor/fontawesome-free/css/all.min.css?v=6.4.0" rel="stylesheet" type="text/css">
  <link href="/css/league-logos.min.css?v=1.0.8" rel="stylesheet" type="text/css">
  <link rel="preload" href="https://fonts.googleapis.com/css?family=Nunito:300,300i,400,400i,700,700i,800,800i,900,900i&display=swap" as="style" onload="this.rel='stylesheet'">
    <noscript class="async-css"><link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:300,300i,400,400i,700,700i,800,800i,900,900i&display=swap"></noscript>

    <script>
    function supportsToken(token) {
    return function(relList) {
        if (relList.supports && token) {
        return relList.supports(token)
        }
        return false
    }
    }
        
    ;(function () {
    var supportsPreload = supportsToken("preload")
    var rl = document.createElement("link").relList
    if (!supportsPreload(rl)) {
        var styles = document.getElementsByTagName('noscript')
        for (var i = 0 ; i < styles.length ; i++) {
        if (styles[i].getAttribute('class') === 'async-css') {
            var div = document.createElement('div')
            div.innerHTML = styles[i].innerHTML
            document.body.appendChild(div)
        }
        }
    }
    })()
    </script>

  <link href="/css/template.min.css?v=1.0.2" rel="stylesheet">
  <link href="/css/main.min.css?v=1.4.3" rel="stylesheet">
</body>

</html>
<?
mysqli_close($link);
?>