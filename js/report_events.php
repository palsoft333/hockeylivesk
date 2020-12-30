<?
include("../includes/db.php");

$_GET[id] = htmlspecialchars($_GET[id]);
$el = substr($_GET[id], -1);
$dl = strlen($_GET[id]);
$id = substr($_GET[id], 0, $dl-1);
if($el==1) $matches_table = "el_matches";
else $matches_table = "2004matches";
$q = mysql_query("SELECT * FROM $matches_table WHERE id='$id'");
$f = mysql_fetch_array($q);
?>

function ProcessMatch( data ){

var cook = $.cookie('hl-'+data['id']);
if(cook===null) $.cookie('hl-'+data['id'], data['tstamp'], { expires: 1, path: '/' });
else if(data['tstamp']!=cook) CheckUpdates(data['id']);

  $.each(data, function(key, value) { 
    var Item = $( "#" + key );
    var Itemvalue = value;
    if(key=="other") 
      {
      if(Itemvalue.search("-")!=-1)
        {
        var othm = Itemvalue.split("-");
        var i=0;
        var matche = "";
        while(i<othm.length)
          {
          var oth = othm[i].split("|");
          matche = matche + '<a href="/report/'+oth[5]+'-'+oth[0]+'-'+oth[1]+'" class="blacklink"><img style="vertical-align:top;" class="'+oth[0]+'_small" src="/images/blank.png" alt="'+oth[0]+'"> <b>'+oth[0]+'</b> vs. <b>'+oth[1]+'</b> <img style="vertical-align:top;" class="'+oth[1]+'_small" src="/images/blank.png" alt="'+oth[1]+'"> - <b>'+oth[2]+':'+oth[3]+'</b> ('+oth[4]+')</a><br>';
          i++;
          }
        Itemvalue = matche;
        }
      else
        {
        var oth = Itemvalue.split("|");
        Itemvalue = '<a href="/report/'+oth[5]+'-'+oth[0]+'-'+oth[1]+'" class="blacklink"><img style="vertical-align:top;" class="'+oth[0]+'_small" src="/images/blank.png" alt="'+oth[0]+'"> <b>'+oth[0]+'</b> vs. <b>'+oth[1]+'</b> <img style="vertical-align:top;" class="'+oth[1]+'_small" src="/images/blank.png" alt="'+oth[1]+'"> - <b>'+oth[2]+':'+oth[3]+'</b> ('+oth[4]+')</a>';
        }
      }
    if(Item.html()!=Itemvalue)
      {
      if(Item.html()!="")
        {
        Item.html( Itemvalue );
        Item.addClass('animated--grow-in bg-warning img-thumbnail');
        if(key=="goals1" || key=="goals2") Item.addClass('display-2');
        setTimeout(function () {
            Item.removeClass('animated--grow-in bg-warning display-2 img-thumbnail');
        }, 3000);
        if(key=="goals1" || key=="goals2") 
          {
          if(typeof responsiveVoice !== 'undefined')
            {
            if(key=="goals1")
              {
              var team = "<? echo $f[team1long]; ?>";
              var stav = $("#goals1").text()+':'+$("#goals2").text();
              }
            if(key=="goals2")
              {
              var team = "<? echo $f[team2long]; ?>";
              var stav = $("#goals2").text()+':'+$("#goals1").text();
              }
            var say = team+" dáva gól na "+stav;
            responsiveVoice.speak(say, "Slovak Female", {rate: 1.0});
            }
          else
            {
            var audio = $("#goalhorn")[0];
            audio.play();
            }
          }
        }
      else 
        {
        if(key=="other") $("#othermatches").slideToggle();
        Item.html( Itemvalue );
        }
      }
  });
  
  function CheckUpdates(id){

    //check goals
    $.ajax({
      type: "GET",
      url: "/includes/report_goals.php?id="+id,
      cache: false,
      success:  function(data) {
                ProcessGoals(data);
              }
    });
    //check penalties
    $.ajax({
      type: "GET",
      url: "/includes/report_penalty.php?id="+id+"&t=1",
      cache: false,
      success:  function(data) {
                ProcessPenalties1(data);
              }
    });
    $.ajax({
      type: "GET",
      url: "/includes/report_penalty.php?id="+id+"&t=2",
      cache: false,
      success:  function(data) {
                ProcessPenalties2(data);
              }
    });
    //check description
    $.ajax({
      type: "GET",
      url: "/includes/report_desc.php?id="+id,
      cache: false,
      success:  function(data) {
                ProcessDescription(data);
              }
    });
    $.cookie('hl-'+data['id'], data['tstamp'], { expires: 1, path: '/' });
  }
       
  function ProcessGoals(data){
  $(".report-goals").html(data);
  HighLight("highlight");
  $('[data-toggle="tooltip"]').tooltip();
  }
  
  function ProcessPenalties1(data){
  $(".report-pen1").html(data);
  HighLight("pen1high");
  }
  
  function ProcessPenalties2(data){
  $(".report-pen2").html(data);
  HighLight("pen2high");
  }
  
  function ProcessDescription(data){
  $(".report-desc").html(data);
  HighLight("deschigh");
  }
  
}

function HighLight(what) {
      $("."+what).addClass('animated--fade-in bg-warning font-weight-bold');
      setTimeout(function () {
          $("."+what).removeClass('animated--fade-in bg-warning font-weight-bold');
      }, 3000);
}

$().ready(function(){
   $.ajaxSetup ({
     cache: false
   });
  $.cookie('hl-<? echo $_GET[id]; ?>', 'firstrun', { expires: 1, path: '/' });
  $.ajax({
    type: "GET",
    url: "/includes/report_fetch.php?id=<? echo $_GET[id]; ?>",
    dataType: 'json',
    cache: false,
    success:  function(data) {
              ProcessMatch(data);
            }
  });
<?
if($f[kedy]!="konečný stav")
  {
  ?>
     setInterval(function(){
        $.ajax({
        type: "GET",
        url: "/includes/report_fetch.php?id=<? echo $_GET[id]; ?>",
        dataType: 'json',
        cache: false,
        success: function(data) {
                ProcessMatch(data);
                ga('send', 'pageview');
              }
        });
      }, 15000);
  <?
  }
  ?>
  }
  );
