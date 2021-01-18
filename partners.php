<?
$content = ""; 
// partneri

$title = LANG_PARTNERS_TITLE;
$leaguecolor = "hl";
$content .= '<h1 class="h3 h3-fluid mb-4">'.LANG_PARTNERS_TITLE.'</h1>

<div style="max-width: 1000px;">

<p class="p-fluid">'.LANG_PARTNERS_TEXT.'</p>
<div class="card shadow animated--grow-in mb-4">
  <div class="card-header">
    <h6 class="m-0 font-weight-bold text-hl">
      '.LANG_PARTNERS_TITLE.'
    </h6>
  </div>
  <div class="card-body">';
  
  $q = mysql_query("SELECT * FROM hl_partners ORDER BY refered DESC");
  while($data = mysql_fetch_array($q))
    {
    $content .= '
    <div class="mb-4">
      <a href="'.$data[url].'" target="_blank" rel="noopener">'.($data[image_url]==NULL ? '<i class="fas fa-globe fa-2x img-thumbnail mr-3 float-left text-center" style="width:98px; height:41px;"></i>':'<img src="data:image/png;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" data-src="'.$data[image_url].'" class="lazy img-thumbnail mr-3 float-left" style="width:98px; height:41px;" alt="'.$data[name].'">').'</a>
      <h5 class="h5-fluid mt-2 d-inline-block d-sm-block">'.$data[name].'</h5>
      <p class="p-fluid"><a href="'.$data[url].'" target="_blank" rel="noopener">'.$data[url].'</a></p>
    </div>';
    $i++;
    }
  
$content .= '    
  </div>
</div>

<div class="alert alert-info" role="alert">
  <i class="fab fa-slideshare"></i> '.LANG_PARTNERS_MAILUS.' <span class="email"><a href="mailto:redxakcia@hockeyx-lixve.sk" onmouseover="this.href=this.href.replace(/x/g,\'\');" class="alert-link">redakcia<span class="d-none">-anti-bot-bit</span>@hockey-live.sk</a></span>.
</div>

</div>';
?>