function GetGames(lid) {
  $.ajax({
       type:"POST",
       url:"/includes/games.php?lid="+lid,
       dataType: "text",
       contentType:"application/x-www-form-urlencoded; charset=utf-8",
       beforeSend: function() { $('#games-spinner').show(); },
       complete: function() { $('#games-spinner').hide(); },
       success: function (data) {
           $('.container-fluid').html(data);
           window.history.pushState('page', 'hockey-LIVE.sk', '/games/'+lid);
           Bet_Initialize();
           Scroll_Here();
            if(window.pageYOffset!=0) $('html,body').animate({
               scrollTop: $(".container-fluid").offset().top
            });
       }
  });
}
  
function Bet(tipbox, previous) {
  var spl=tipbox.attr("id").split('-');
  var gid=spl[1];
  var tip1=$("#tip1-"+gid).val();
  var tip2=$("#tip2-"+gid).val();
  
  if(previous=="-")
    {
    if(tip1!=="-" && tip2!=="-") { var dataString = 'place='+gid+'&tip1='+tip1+'&tip2='+tip2; var what="place"; }
    }
  if(previous!=="-")
    {
    if(tip1!=="-" && tip2!=="-") { var dataString = 'place='+gid+'&tip1='+tip1+'&tip2='+tip2; var what="place"; }
    if(tip1=="-" || tip2=="-") { var dataString = 'delete='+gid; var what="delete"; }
    }
  
  if(previous=="-" && tip1!=="-" && tip2!=="-" || previous!=="-" && (tip1=="-" || tip2=="-") || previous!=="-" && tip1!=="-" && tip2!=="-")
    {
    var today  = new Date();
    var smallText = today.toLocaleDateString("sk-SK");
    $.ajax({
         type:"POST",
         url:"/includes/bet.php",
         dataType: "text",
         data: dataString,
         cache: false,
         contentType:"application/x-www-form-urlencoded; charset=utf-8",
         success: function (data) {
            if(previous=="-" && tip1!=="-" && tip2!=="-") 
              {
              ga('send', 'event', 'User', 'betPlace', gid+' - '+tip1+':'+tip2);
              }
            if(previous!=="-" && (tip1=="-" || tip2=="-"))
              {
              ga('send', 'event', 'User', 'betRemove', gid);
              }
            if(what=="place") Notification("chart-bar", LANG_GAMES_BETTING, smallText, LANG_GAMES_BET+' '+tip1+':'+tip2+' '+LANG_GAMES_BETADDED, 5000);
            else if(what=="delete") Notification("eraser", LANG_GAMES_BETTING, smallText, LANG_GAMES_BETREMOVED, 5000);
         }
    });
    }
}

function Bet_Initialize() {
  var previous;
  $("select[id^='tip']").on('focus', function () {
    previous = this.value;
    }).on('change', function() {
    Bet($(this),previous);
    previous = this.value;
  }); 
}

function Scroll_Here() {
  if($('.scrollhere').length)
    {
    $('html, body').stop().animate({
        scrollTop: ($('.scrollhere').offset().top-10)
      }, 1000, 'easeInOutExpo');
    }
}

$(document).ready(function() {
  Bet_Initialize();
  Scroll_Here();
});
