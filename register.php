<?
session_start();
if($_SESSION['logged']) header("Location:/");
include("includes/register.php");
$langs = array();

if(!isset($_SESSION[lang]))
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
      $_SESSION[lang] = "sk";
      break;
    }
     else if (strpos($lang, 'en') === 0) {
      $_SESSION[lang] = "en";
      break;
    }
    else {
      $_SESSION[lang] = "sk";
      }
    }
  if(count($langs)==0) {
    $_SESSION[lang] = "sk";
    }
  include("includes/lang/lang_".strtolower($_SESSION[lang]).".php");
  }
else
  {
  if($_SESSION[lang]=="en" || $_SESSION[lang]=="sk") include("includes/lang/lang_".strtolower($_SESSION[lang]).".php");
  else include("includes/lang/lang_sk.php");
  }
?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="hockey-LIVE.sk">
  <meta name="title" content="<? echo LANG_NAV_REGISTRATION; ?>" />
  <meta property="og:image" content="https://www.hockey-live.sk/images/hl_avatar.png" />
  <meta name="twitter:image" content="https://www.hockey-live.sk/images/hl_avatar.png" />
  <meta property="og:description" content="Portál pokrývajúci slovenský a zahraničný ľadový hokej. Štatistiky, tipovanie, databáza hráčov, výsledkový servis z domácich líg, zahraničných turnajov, KHL a NHL." />

  <title>hockey-LIVE.sk - <? echo LANG_NAV_REGISTRATION; ?></title>

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css?v=5.13.0" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="css/template.min.css?v=1.0.2" rel="stylesheet">
  <link href="css/main.css?v=1.2.1" rel="stylesheet">

</head>

<body class="bg-gradient-hl">

  <div class="container">
  
    <img src="/img/hockey_logo_big.svg" alt="hockey-LIVE.sk" class="col-sm-6 col-md-3 img-fluid mx-auto d-block pt-3">

    <div class="card o-hidden border-0 shadow-lg my-5">
      <div class="card-body p-0">
        <!-- Nested Row within Card Body -->
        <div class="row">
          <div class="col-lg-5 d-none d-lg-block animated--fade-in lazy" data-src="https://source.unsplash.com/collection/3668324/600x800" style="background-position: center; background-size: cover;"></div>
          <div class="col-lg-7">
            <div class="p-5">
              <div class="text-center">
                <h1 class="h4 text-gray-900 mb-4"><? echo LANG_NAV_REGISTRATION; ?></h1>
              </div>
              <div class="alert alert-danger d-none animated--fade-in" role="alert" id="alert"></div>
              <form class="user" id="register-form">
                <div class="form-group">
                  <input type="text" class="form-control form-control-user" id="user" placeholder="<? echo LANG_NAV_USERNAME; ?>" autocomplete="username" required>
                </div>
                <div class="form-group">
                  <input type="email" class="form-control form-control-user" id="email" placeholder="<? echo LANG_NAV_EMAIL; ?>" autocomplete="email" required>
                </div>
                <div class="form-group row">
                  <div class="col-sm-6 mb-3 mb-sm-0">
                    <input type="password" class="form-control form-control-user" id="pass" placeholder="<? echo LANG_NAV_PASSWORD; ?>" autocomplete="new-password" required>
                  </div>
                  <div class="col-sm-6">
                    <input type="password" class="form-control form-control-user" id="passagain" placeholder="<? echo LANG_NAV_PASSWORDAGAIN; ?>" autocomplete="new-password" required>
                  </div>
                </div>
                <a href="#" class="btn btn-hl btn-user btn-block register-proceed-button">
                  <? echo LANG_NAV_REGISTER; ?>
                </a>
              </form>
              <hr>
              <div class="text-center">
                <a class="small" href="/login"><? echo LANG_REGISTER_ALREADY; ?></a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="https://www.google.com/recaptcha/api.js?render=6LdKMrcZAAAAAG6VxKDluvh6h9UnStzevg8HThd7"></script>
  
  <script src="/js/jquery.lazy.min.js"></script>
  <script src="/js/main.min.js?v=1.1.7"></script>
  <script src="/js/register_events.js?v=1.0.4"></script>

</body>

</html>
