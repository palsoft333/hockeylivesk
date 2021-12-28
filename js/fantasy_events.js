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
        if(forward>0 && countf>0) { var co="forward"; var co1="f"; var co2=forward; var co3=LANG_FANTASY_F; }
        if(defense>0 && countd>0) { var co="defense"; var co1="d"; var co2=defense; var co3=LANG_FANTASY_D; }
        if(goalie>0 && countgk>0) { var co="goalie"; var co1="gk"; var co2=goalie; var co3=LANG_FANTASY_GK; }
        if(co1=="f" || co1=="d" || co1=="gk")
          {
          if(co1=="gk") var tshort=$('#goalie :selected').attr('short');
          else var tshort=$('#'+co+' :selected').parent().attr('short');
          var player=$("#"+co+" option:selected").text();
          $("#predrafted").removeClass("d-none");
          $("#predrafted").append("<table width='100%'><tr><td width='20%'>"+round+"."+LANG_FANTASY_ROUND+"</td width='20%'><td><img class='flag-iihf "+tshort+"-small' src='/images/blank.png' alt='vlajka' style='vertical-align:-1px;'> "+tshort+"</td><td width='60%'>"+player+"</td></tr></table>");
          $("#predrafted").effect("highlight", { }, 1500);
          options.push({ "pid" : co2, "round" : round });
          $('#'+co+' option:selected').remove();
          var newround = +($("#round").text()) + 1;
          $("#round").html(newround);
          var newcount = +($("#count"+co1).text()) - 1;
          $("#count"+co1).html(newcount);
          if(newcount==0) $("#"+co+"_container").hide();
          var hl = co3+" "+LANG_FANTASY_ADDED+".";
          }
        }
      else 
        {
        $("#forward").val("0");
        $("#defense").val("0");
        $("#goalie").val("0");
        Notification("users text-danger", LANG_ERROR, smallText, LANG_FANTASY_PICKJUSTONE, 5000);
        }
      }
    if($("#countf").text()==0 && $("#countd").text()==0 && $("#countgk").text()==0) Notification("user-plus text-success", LANG_FANTASY_DRAFT, smallText, hl+' '+LANG_FANTASY_CONFIRMTEAM, 5000);
    else if(co) Notification("user-plus text-success", LANG_FANTASY_DRAFT, smallText, hl+' '+LANG_FANTASY_PICKANOTHER, 5000);
    if($("#draft_button").text()==LANG_FANTASY_CONFIRMBUTTON)
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
            Notification("users text-danger", LANG_ERROR, smallText, LANG_FANTASY_ERRORSAVING, 5000);
            }
          }
        });
      }
    if(round==10)
      {
      $("#round").html("10");
      $("#draft_button").html(LANG_FANTASY_CONFIRMBUTTON);
      $(".mdl-card__actions").delay(800).effect("pulsate", { }, 5).effect("highlight", { }, 1500);
      }
  }

  var picks = [];

  $('body').on('focus',".pick-player", function(){
    var pick = $(this).data('pick');
    var icon = $(this).parent('div').next().find('.pick-icon');
    var numgk = eval($('#numgk').html());
    var numd = eval($('#numd').html());
    var numf = eval($('#numf').html());
    $(this).autocomplete({
      source: "/includes/draft_autocomplete.php?g="+numgk+"&d="+numd+"&f="+numf,
      minLength: 3,
      select: function( event, ui ) {
        var str = ui.item.label.replace(/<(?:.|\n)*?>/gm, '');
        str = str.substring(1);
        $(this).val(str);
        var id = ui.item.value.split('-');

        // check for already picked players
        for (let i = 1; i < 11; ++i) {
            var pickdata = $('#pick-'+i).val();
            var data = pickdata.split('-');
            if(data[2]==id[2]) {
                $(this).addClass("is-invalid");
                var today  = new Date();
                var smallText = today.toLocaleDateString("sk-SK");
                Notification("users text-danger", LANG_ERROR, smallText, LANG_FANTASY_ALREADYPICKED, 5000);
                return false;
            }
        }
        $('#pick-'+pick).val(id[0]+"-"+id[1]+"-"+id[2]);

        // fill JSON string of picked players
        picks = [];
        for (let i = 1; i < 11; ++i) {
            var pickdata = $('#pick-'+i).val();
            if(pickdata!="") {
                var data = pickdata.split('-');
                picks.push({ "pid" : data[2], "round" : i });
            }
        }
        $('#picks').val(JSON.stringify(picks));

        // save current picks to DB
        var json = $('#picks').val();
            $.ajax({
            type: "POST",
            url: "/includes/fantasy_functions.php?save=1&json="+json,
            dataType: "text",
            contentType:"application/x-www-form-urlencoded; charset=utf-8",
            cache: false
        });

        // add remove icon and validate
        icon.html('<a href="#" class="btn btn-danger btn-sm remove-pick" data-pick="'+pick+'"><i class="fas fa-times-circle"></i></a>');
        $(this).removeClass("is-invalid").addClass("is-valid");
        $(this).prop('readonly', true);

        // show draft status message
        if($(".pick-player.is-valid").length==10) {
            $("#draft-status").removeClass("bg-danger").addClass("bg-success").html(LANG_FANTASY_PICKSACTIVE);
        }
        else {
            $("#draft-status").removeClass("bg-success").addClass("bg-danger").html(LANG_FANTASY_PICKSINACTIVE);
        }

        // decrement needed positions
        if(id[0]=="GK") {
            numgk--;
            $('#numgk').html(numgk);
        }
        if(id[0]=="D") {
            numd--;
            $('#numd').html(numd);
        }
        if(id[0]=="F") {
            numf--;
            $('#numf').html(numf);
        }
        return false;
      }
    }).autocomplete( "instance" )._renderItem = function( ul, item ) {
      return $( "<li>" )
        .append( "<div>" + item.label + "</div>" )
        .appendTo( ul );
    };
  });

  $('body').on('click',".remove-pick", function(){
        var pick = $(this).data('pick');
        $(this).remove();
        var data = $('#pick-'+pick).val().split('-');
        var pos = data[0];

        // increment needed positions
        if(pos=="GK") {
            var numgk = eval($('#numgk').html());
            numgk++;
            $('#numgk').html(numgk);
        }
        if(pos=="D") {
            var numd = eval($('#numd').html());
            numd++;
            $('#numd').html(numd);
        }
        if(pos=="F") {
            var numf = eval($('#numf').html());
            numf++;
            $('#numf').html(numf);
        }

        $('#pick-'+pick).val('');

        // fill JSON string of picked players
        picks = [];
        for (let i = 1; i < 11; ++i) {
            var pickdata = $('#pick-'+i).val();
            if(pickdata!="") {
                var data = pickdata.split('-');
                picks.push({ "pid" : data[2], "round" : i });
            }
        }
        $('#picks').val(JSON.stringify(picks));

        // save current picks to DB
        var json = $('#picks').val();
        $.ajax({
            type: "POST",
            url: "/includes/fantasy_functions.php?save=1&json="+json,
            dataType: "text",
            contentType:"application/x-www-form-urlencoded; charset=utf-8",
            cache: false
        });

        $('input[data-pick="' + pick + '"]').prop('readonly', false).val('').removeClass('is-valid').focus();

        // show draft status message
        if($(".pick-player.is-valid").length==10) {
            $("#draft-status").removeClass("bg-danger").addClass("bg-success").html(LANG_FANTASY_PICKSACTIVE);
        }
        else {
            $("#draft-status").removeClass("bg-success").addClass("bg-danger").html(LANG_FANTASY_PICKSINACTIVE);
        }
  });