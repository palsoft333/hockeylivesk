$(document).ready( function() {
  if(window.location.href.indexOf('#ModalCenter') != -1) {
    $('#ModalCenter').modal('show');
  }

  $("#forgotok").on('click', function() {
    var email1=$("#email").val();
    var re1 = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if(re1.test(email1)==false) $("#alert").html(LANG_LOGIN_WRONGEMAIL).removeClass("d-none");
    else
      {
      var dataString = 'forgot='+email1;
      if($.trim(email1).length>0)
        {
        $.ajax({
        type: "POST",
        url: "/includes/login.php",
        dataType: "text",
        contentType:"application/x-www-form-urlencoded; charset=utf-8",
        data: dataString,
        cache: false,
        success: function(data){
          if(data)
            {
            $("#alert").removeClass("alert-danger").addClass("alert-success").html(LANG_LOGIN_EMAILSENT).removeClass("d-none");
            }
          else
            {
            $("#alert").html(LANG_LOGIN_EMAILDOESNTEXIST).removeClass("d-none");
            }
          }
        });

        }
      }
    return false;
  });
});