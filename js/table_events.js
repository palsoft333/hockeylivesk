function GetTable(lid) {
  $.ajax({
       type:"POST",
       url:"/includes/table.php?lid="+lid,
       dataType: "text",
       contentType:"application/x-www-form-urlencoded; charset=utf-8",
       beforeSend: function() { $('#table-spinner').show(); },
       complete: function() { $('#table-spinner').hide(); },
       success: function (data) {
           $('.container-fluid').html(data);
           window.history.pushState('page', 'hockey-LIVE.sk', '/table/'+lid);
           SwitchToggle();
            if(window.pageYOffset!=0) $('html,body').animate({
               scrollTop: $(".container-fluid").offset().top
            });
       }
  });
}

function SwitchToggle() {
  $("#switch").on('click', function() {
      if($("#switch").is(':checked')) { GetTable($("#switch").val()+"/simulation"); }
      else { GetTable($("#switch").val()); }
      $('.custom-switch').tooltip('hide');
  });  
}

function ToggleGames(id) {
  $('.game-middle').css('height', '0');
  $('#pg-'+id).slideToggle();
  $('.game-middle').css('height', '100%');
}

$(document).ready(function() {
SwitchToggle();
});