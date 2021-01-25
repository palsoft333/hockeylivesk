<?
session_start();
if($_SESSION['logged']) header("Location:/");
include("includes/login.php");
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
  <meta name="title" content="<? echo LANG_NAV_LOGIN2; ?>" />
  <meta property="og:image" content="https://www.hockey-live.sk/images/hl_avatar.png" />
  <meta name="twitter:image" content="https://www.hockey-live.sk/images/hl_avatar.png" />
  <meta property="og:description" content="Portál pokrývajúci slovenský a zahraničný ľadový hokej. Štatistiky, tipovanie, databáza hráčov, výsledkový servis z domácich líg, zahraničných turnajov, KHL a NHL." />

  <title>hockey-LIVE.sk - <? echo LANG_NAV_LOGIN2; ?></title>

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.min.css?v=5.13.0" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

  <!-- Custom styles for this template-->
  <link href="css/template.min.css?v=1.0.2" rel="stylesheet">
  <link href="css/main.css?v=1.2.1" rel="stylesheet">

</head>

<body class="bg-gradient-hl">

  <!-- Forgot password modal -->
  <div class="modal fade" id="ModalCenter" tabindex="-1" role="dialog" aria-labelledby="ModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="ModalCenterTitle"><? echo LANG_LOGIN_FORGOT1; ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="<? echo LANG_CLOSE; ?>">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Zadajte svoj e-mail použitý pri registrácii a my Vám zašleme nové heslo ...</p>
          <div class="alert alert-danger d-none animated--fade-in" role="alert" id="alert"></div>
          <form class="user">
            <div class="form-group">
              <input type="text" id="email" class="form-control form-control-user" aria-describedby="emailHelp" placeholder="<? echo LANG_NAV_EMAIL; ?>...">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal"><? echo LANG_CLOSE; ?></button>
          <button type="button" class="btn btn-primary" id="forgotok"><? echo LANG_LOGIN_SENDMAIL;?></button>
        </div>
      </div>
    </div>
  </div>

  <div class="container">

    <img src="/img/hockey_logo_big.svg" alt="hockey-LIVE.sk" class="col-sm-6 col-md-3 img-fluid mx-auto d-block pt-3">
    <!-- Outer Row -->
    <div class="row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block animated--fade-in lazy" data-src="https://source.unsplash.com/collection/3668324/600x800" style="    background-position: center; background-size: cover;"></div>
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4"><? echo LANG_LOGIN_WELCOMEBACK; ?></h1>
                  </div>
                  <?
                  if($alert)
                    {
                    echo '<div class="alert alert-danger" role="alert">
                            '.$alert.'
                          </div>';
                    }
                  ?>
                  <form class="user" method="post" action="/login">
                    <div class="form-group">
                      <input type="text" name="username" class="form-control form-control-user" aria-describedby="emailHelp" placeholder="<? echo LANG_NAV_EMAIL; ?>..." autocomplete="email">
                    </div>
                    <div class="form-group">
                      <input type="password" name="password" class="form-control form-control-user" placeholder="<? echo LANG_NAV_PASSWORD; ?>" autocomplete="current-password">
                    </div>
                    <div class="form-group">
                      <div class="custom-control custom-checkbox small">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                        <label class="custom-control-label" for="remember"><? echo LANG_NAV_REMEMBER; ?></label>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-hl btn-user btn-block">
                      <? echo LANG_NAV_LOGIN; ?>
                    </button>
                  </form>
                  <hr>
                  <div class="text-center">
                    <a class="small" href="#" data-toggle="modal" data-target="#ModalCenter"><? echo LANG_LOGIN_FORGOT; ?></a>
                  </div>
                  <div class="text-center">
                    <a class="small" href="/register"><? echo LANG_NAV_REGISTRATION; ?></a>
                  </div>
                </div>
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
  
  <script src="/js/jquery.lazy.min.js"></script>
  <script src="/js/main.min.js?v=1.1.7"></script>
  <script src="/js/login_events.js?v=1.0.1"></script>

</body>

</html>
