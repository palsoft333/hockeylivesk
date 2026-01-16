<?
include("includes/advert_bigscreenside.php");

$leaguecolor = "hl";
$title = LANG_NAV_FANSHOP;
$content = "";

$content .= "<div id='toasts' class='fixed-top' style='top: 80px; right: 23px; left: initial; z-index:3;'></div>
             <h1 class='h3 h3-fluid mb-1'>".LANG_NAV_FANSHOP."</h1>
             <div class='row mb-4'>
                <div class='col-12' style='max-width: 1000px;'>
                    <p>".LANG_SHOP_DESC."</p>
                    <div style='max-width: 100%; overflow-x: auto;'>
                        <script type='text/javascript' src='https://www.etsy.com/assets/js/etsy_mini_shop.js'></script>

                        <!-- Desktop Version -->
                        <div class='etsy-widget-desktop'>
                            <script type='text/javascript'>
                                new Etsy.Mini(61929576, 'gallery', 4, 2, 0, 'https://www.etsy.com');
                            </script>
                        </div>

                        <!-- Tablet Version -->
                        <div class='etsy-widget-tablet'>
                            <script type='text/javascript'>
                                new Etsy.Mini(61929576, 'gallery', 2, 4, 0, 'https://www.etsy.com');
                            </script>
                        </div>

                        <!-- Mobile Version -->
                        <div class='etsy-widget-mobile'>
                            <script type='text/javascript'>
                                new Etsy.Mini(61929576, 'gallery', 1, 8, 0, 'https://www.etsy.com');
                            </script>
                        </div>
                    </div>

                    <style>
                    /* Default: show desktop version */
                    .etsy-widget-desktop {
                        display: block;
                    }

                    .etsy-widget-tablet,
                    .etsy-widget-mobile {
                        display: none;
                    }

                    /* Tablet (between 768px and 1024px) */
                    @media screen and (min-width: 768px) and (max-width: 1024px) {
                        .etsy-widget-desktop {
                            display: none;
                        }

                        .etsy-widget-tablet {
                            display: block;
                        }
                    }

                    /* Mobile (up to 767px) */
                    @media screen and (max-width: 767px) {
                        .etsy-widget-desktop,
                        .etsy-widget-tablet {
                            display: none;
                        }

                        .etsy-widget-mobile {
                            display: block;
                        }
                    }
                    </style>
                </div> <!-- end col -->
                <div class='col-auto flex-grow-1 flex-shrink-1 d-none d-xl-block'>
                  ".$advert."
                </div> <!-- end col -->
             </div> <!-- end row -->";