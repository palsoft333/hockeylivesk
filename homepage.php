            <!-- Video modal -->
            <div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="videoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                    <div class="modal-content bg-gradient-secondary">
                        <div class="modal-header border-0 pb-0 pt-2">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="clearVideoUrl()">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe class="embed-responsive-item border-0" id="videoFrame" src="" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          <?
          if(!isset($_GET["topicID"])) {
          ?>
          
          <?
          if(!isset($_SESSION["logged"])) echo "
            <!-- Top mobile advert -->
            <div id='101390-31' class='text-center mb-3'></div>
            
            <script>
               if (window.matchMedia(\"(max-width: 767px)\").matches) {
                const adDiv = document.getElementById('101390-31');

                const script1 = document.createElement('script');
                script1.src = '//ads.themoneytizer.com/s/gen.js?type=31';
                script1.defer = true;
                adDiv.appendChild(script1);

                const script2 = document.createElement('script');
                script2.src = '//ads.themoneytizer.com/s/requestform.js?siteId=101390&formatId=31';
                script2.defer = true;
                adDiv.appendChild(script2);
              }
            </script>
          ";
          
          ?>

          <!-- Content Row -->
          <div class="row">

            <!-- Game of the day -->
            <div class="col-xl-3 col-md-6 mb-4">
              <? echo gotd(); ?>
            </div>

            <!-- Calendar -->
            <div class="col-xl-3 col-md-6 mb-4">
              <div class="card border-left-success shadow h-100">
                <div class="card-body p-3">
                  <div class="row no-gutters align-items-center pb-2">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-success text-uppercase mb-1"><? echo LANG_CARDS_CALENDAR; ?></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                  </div>
                  <div class="custom-calendar-wrap position-relative">
                    <div id="calendar-spinner" class="position-absolute" style="top:50%; left:50%; z-index: 2;">
                      <div class="spinner-border text-success" role="status">
                        <span class="sr-only">Loading...</span>
                      </div>
                    </div>
                    <div id="custom-inner" class="custom-inner">
                      <div class="row no-gutters align-items-center mb-2">
                        <div class="col-1">
                          <a id="custom-prev" class="btn btn-sm btn-light text-gray-700"><i class="fas fa-caret-left"></i></a>
                        </div>
                        <div class="col-10 small text-center">
                          <span id="custom-month"></span> <span id="custom-year"></span>
                        </div>
                        <div class="col-1 text-right">
                          <a id="custom-next" class="btn btn-sm btn-light text-gray-700"><i class="fas fa-caret-right"></i></a>
                        </div>
                      </div>
                    <div id="calendar" class="fc-calendar-container"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Flash news -->
            <div class="d-none d-md-block col-xl-3 col-md-6 mb-4">
              <div class="card border-left-info shadow h-100">
                <div class="card-body p-3">
                  <div id="flash-spinner" class="position-absolute" style="top: 50%; left: 50%; z-index: 2; display: none;">
                    <div class="spinner-border text-success" role="status">
                      <span class="sr-only">Loading...</span>
                    </div>
                  </div>
                  <div class="row no-gutters align-items-center pb-2">
                    <div class="col mr-2">
                      <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><? echo LANG_CARDS_FLASH; ?><span id="flashnews-refresh" class="ml-2"><i class="fas fa-sync-alt" aria-hidden="true"></i></span></div>
                    </div>
                    <div class="col-auto">
                      <i class="fas fa-comments fa-2x text-gray-300"></i>
                    </div>
                  </div>
                  <div class="overflow-auto mt-1 small-scrollbar" style="height:186px;">
                    <div id="flash-container" class="small"></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Player of the week -->
            <div class="col-xl-3 col-md-6 mb-4">
              <? echo potw(); ?>
            </div>
          </div>
          
          <?
          }
          ?>
          
          <!-- Page Heading -->
          <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="d-none d-lg-block h3 mb-0 text-gray-800"><? if(isset($_GET["topicID"]) && $_GET["topicID"]!="all") echo Get_SEO_title($_GET["topicID"]); else echo LANG_NAV_NEWS; ?></h1>
            <!--<div class="banner468x60">-->
            <div class="banner468x60 col-12 col-xl-6">
          <?
              echo "
              <div id='101390-1'></div>
              <script>
                if (window.innerWidth >= 768) {
                  window.addEventListener('load', function() {
                    const adDiv = document.getElementById('101390-1');

                    const script1 = document.createElement('script');
                    script1.src = '//ads.themoneytizer.com/s/gen.js?type=1';
                    script1.defer = true;
                    adDiv.appendChild(script1);

                    const script2 = document.createElement('script');
                    script2.src = '//ads.themoneytizer.com/s/requestform.js?siteId=101390&formatId=1';
                    script2.defer = true;
                    adDiv.appendChild(script2);
                  });
                }
              </script>";
          ?>
            </div>
            <!--<div class="d-none d-lg-block col-3"></div>-->
            <div class="d-none d-lg-block"></div>
          </div>

          <!-- Content Row -->

          <div class="row">
            <div class="col-xl-9 mb-4 order-2 order-lg-1">
            
              <!-- Page Heading mobile only -->
              <div class="d-lg-none mb-4">
              <h2 class="h3 mb-0 text-gray-800"><? if(isset($_GET["topicID"])) echo Get_SEO_title($_GET["topicID"]); else echo LANG_NAV_NEWS; ?></h2>
              </div>
              <? 
              $_GET["page"] = $_GET["page"] ?? null;
              $_GET["topicID"] = $_GET["topicID"] ?? null;
              echo Get_news(10, $_GET["page"], $_GET["topicID"]); ?>
                         
            </div>
            
            <div class="col-xl-3 mb-4 order-<? echo (!$_GET["topicID"] ? '1':'3'); ?> order-lg-2">
              <?
              if(!$_GET["topicID"])
                {
                echo Get_upcomming();
                /*if(!isset($_SESSION["logged"])) echo '
              <div class="advert-container card shadow mb-4 text-center">
                <p class="m-auto p-2 small"></p>
              </div>';*/
                echo Get_Latest_Stats();
                /*if(!isset($_SESSION["logged"])) echo '
              <div class="advert-container card shadow mb-4">
                <p class="m-auto p-2 small"></p>
              </div>';*/
                echo Transfers();
                echo Favourite_Team();
                echo Users_Online();
                echo Sending_Prize();
                if(!isset($_SESSION["logged"])) echo '
              <div class="advert-container card shadow">
                <p class="m-auto p-2 small">Najlepšie <a href="http://casino-hry.sk/">Slovenské Online Casino</a> recenzie a hry zadarmo</p>
              </div>';
          
                echo '
              <div class="card shadow my-4 articleBanner">
                <div class="card-body">
                  <!--<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                  <ins class="adsbygoogle"
                       style="display:block"
                       data-ad-client="ca-pub-8860983069832222"
                       data-ad-slot="1141079059"
                       data-ad-format="auto"
                       data-full-width-responsive="true"></ins>
                  <script>
                       (adsbygoogle = window.adsbygoogle || []).push({});
                  </script>-->
                  <div id="101390-19" class="text-center"></div>
                  <script>
                      window.addEventListener("load", function() {
                        const adDiv = document.getElementById("101390-19");

                        const script1 = document.createElement("script");
                        script1.src = "//ads.themoneytizer.com/s/gen.js?type=19";
                        script1.defer = true;
                        adDiv.appendChild(script1);

                        const script2 = document.createElement("script");
                        script2.src = "//ads.themoneytizer.com/s/requestform.js?siteId=101390&formatId=19";
                        script2.defer = true;
                        adDiv.appendChild(script2);
                      });
                  </script>
                </div>
              </div>';
              
              if(!$_SESSION["logged"]) echo '
              <!--<div class="advert-container card shadow mt-4">
                <p class="m-auto p-2 small"></p>
              </div>-->';
                }
              else
                {
                $topicid = explode("-", $_GET["topicID"]);
                echo '
                  <div class="card shadow mb-2 articleBanner">
                    <div class="card-body">
                      <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
                      <ins class="adsbygoogle"
                           style="display:block"
                           data-ad-client="ca-pub-8860983069832222"
                           data-ad-slot="1141079059"
                           data-ad-format="auto"
                           data-full-width-responsive="true"></ins>
                      <script>
                           (adsbygoogle = window.adsbygoogle || []).push({});
                      </script>
                    </div>
                  </div>
                  
                  '.GoogleNews("n", $topicid[0]);
                }
              ?>
            </div>
            
          </div>

          <script type="application/ld+json">
            {
                "@context":"http://schema.org",
                "@type":"Organization",
                "name":"hockey-LIVE.sk",
                "url":"https://www.hockey-live.sk/",
                "sameAs":["https://www.facebook.com/hockeylive"],
                "logo":"https://www.hockey-live.sk/img/favicon/android-icon-192x192.png"
            }
          </script>

          <script type="application/ld+json">
            {
                "@context":"http://schema.org",
                "@type":"WebSite",
                "name":"hockey-LIVE.sk",
                "url":"https://www.hockey-live.sk/"
            }
          </script>