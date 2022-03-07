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
                if(data[0]=="GK") var gk=1;
                else var gk=0;
                picks.push({ "pid" : data[2], "round" : i, "gk" : gk });
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
                if(data[0]=="GK") var gk=1;
                else var gk=0;
                picks.push({ "pid" : data[2], "round" : i, "gk" : gk });
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
  
  $('body').on('click',".change-player", function(){
    var pid = $(this).data("pid");
    var butt = $(this);
    $.ajax({
      url: '/includes/dialog.php?action=change&pid='+pid,
      success: function(data){
          butt.tooltip('hide');
          $('#dialog').html(data);
          $('#dialog').modal('show');
      }   
    });
  });
  
  $('body').on('click', '.change', function() {
    var newpid = $('#newpid').val();
    var oldpid = $('#oldpid').val();
    $.ajax({
      url: '/includes/dialog.php?action=change&newpid='+newpid+'&oldpid='+oldpid,
      success: function(data){
          $('#dialog').modal('hide');
          location.href = '/fantasy/picks';
      }   
    });
  });