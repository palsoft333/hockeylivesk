  var options = [];
  
  function PreDraft() {
    var today  = new Date();
    var smallText = today.toLocaleDateString("sk-SK");
    var round = $("#round").text();
    var forward = $("#forward").val();
    var defense = $("#defense").val();
    var goalie = $("#goalie").val();
    var countf = $("#countf").text();
    var countd = $("#countd").text();
    var countgk = $("#countgk").text();
    if(forward>0 || defense>0 || goalie>0)
      {
      if(forward>0 && defense==0 && goalie==0 || defense>0 && forward==0 && goalie==0 || goalie>0 && forward==0 && defense==0)
        {
        if(forward>0 && countf>0) { var co="forward"; var co1="f"; var co2=forward; var co3="Útočník"; }
        if(defense>0 && countd>0) { var co="defense"; var co1="d"; var co2=defense; var co3="Obranca"; }
        if(goalie>0 && countgk>0) { var co="goalie"; var co1="gk"; var co2=goalie; var co3="Tím brankárov"; }
        if(co1=="f" || co1=="d" || co1=="gk")
          {
          if(co1=="gk") var tshort=$('#goalie :selected').attr('short');
          else var tshort=$('#'+co+' :selected').parent().attr('short');
          var player=$("#"+co+" option:selected").text();
          $("#predrafted").removeClass("d-none");
          $("#predrafted").append("<table width='100%'><tr><td width='20%'>"+round+".kolo</td width='20%'><td><img class='flag-iihf "+tshort+"-small' src='/images/blank.png' alt='vlajka' style='vertical-align:-1px;'> "+tshort+"</td><td width='60%'>"+player+"</td></tr></table>");
          $("#predrafted").effect("highlight", { }, 1500);
          options.push({ "pid" : co2, "round" : round });
          $('#'+co+' option:selected').remove();
          var newround = +($("#round").text()) + 1;
          $("#round").html(newround);
          var newcount = +($("#count"+co1).text()) - 1;
          $("#count"+co1).html(newcount);
          if(newcount==0) $("#"+co+"_container").hide();
          var hl = co3+" pridaný.";
          }
        }
      else 
        {
        $("#forward").val("0");
        $("#defense").val("0");
        $("#goalie").val("0");
        Notification("users text-danger", "Chyba", smallText, 'Hráčov vyberajte iba po jednom!', 5000);
        }
      }
    if($("#countf").text()==0 && $("#countd").text()==0 && $("#countgk").text()==0) Notification("user-plus text-success", "Draft", smallText, hl+' Potvrďte svoj tím.', 5000);
    else if(co) Notification("user-plus text-success", "Draft", smallText, hl+' Zvoľte si ďalšieho hráča.', 5000);
    if($("#draft_button").text()=="Potvrdiť môj výber")
      {
      var json = JSON.stringify(options);
        $.ajax({
        type: "POST",
        url: "/includes/fantasy_functions.php?predraft=1&json="+json,
        dataType: "text",
        contentType:"application/x-www-form-urlencoded; charset=utf-8",
        cache: false,
        success: function(data){
          if(data)
            {
            window.location.href = "/fantasy/draft";
            }
          else
            {
            Notification("users text-danger", "Chyba", smallText, 'Nastala chyba pri ukladaní vášho tímu.', 5000);
            }
          }
        });
      }
    if(round==10)
      {
      $("#round").html("10");
      $("#draft_button").html("Potvrdiť môj výber");
      $(".mdl-card__actions").delay(800).effect("pulsate", { }, 5).effect("highlight", { }, 1500);
      }
  }