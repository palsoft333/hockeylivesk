var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();

$(document).ready( function() {

  $(".register-proceed-button").on('click', function(e) {
      e.preventDefault();
      grecaptcha.ready(function() {
        grecaptcha.execute('6LdKMrcZAAAAAG6VxKDluvh6h9UnStzevg8HThd7', {action: 'submit'}).then(function(token) {

        var username=$("#user").val();
        var password=$("#pass").val();
        var passagain=$("#passagain").val();
        var email=$("#email").val();
        if(password!==passagain) $("#alert").html("Heslá sa nezhodujú!").removeClass("d-none");
        else if(username.length<3) $("#alert").html("Užívateľské meno musí mať aspoň 3 znaky!").removeClass("d-none");
        else
          {
          var dataString = 'username='+username+'&password='+password+'&email='+email+'&token='+token;
          if($.trim(username).length>0 && $.trim(password).length>0 && $.trim(email).length>0)
            {
            $.ajax({
            type: "POST",
            url: "/includes/register.php",
            dataType: "text",
            contentType:"application/x-www-form-urlencoded; charset=utf-8",
            data: dataString,
            cache: false,
            success: function(data){
              if(data=="OK")
                {
                location.href = "/";
                }
              else if(data=="CAPTCHAERROR")
                {
                $("#alert").html("Vaša registrácia neprešla spam testom. Skúste prosím znovu, alebo nám napíšte <a href='/contact' class='alert-link'>e-mail</a>").removeClass("d-none");
                }
              else if(data=="EMAILEXISTS")
                {
                $("#alert").html("Tento e-mail už bol u nás zaregistrovaný. <a href='/login#ModalCenter' class='alert-link'>Nezabudli ste heslo?</a>").removeClass("d-none");
                }
              else
                {
                $("#alert").html("Nesprávne vyplnené polia registrácie!").removeClass("d-none");
                }
              }
            });

            }
          }
        return false;

        });
      });
  });

  $('#user').keyup(function() {
    delay(function(){
      var $th = $('#user').val();
      if($th.length>2)
        {
        var dataString = 'check='+$th;
        $.ajax({
        type: "POST",
        url: "/includes/register.php",
        dataType: "text",
        contentType:"application/x-www-form-urlencoded; charset=utf-8",
        data: dataString,
        cache: false,
        success: function(data){
          if(data) 
            {
            $("#alert").html("Zadané užívateľské meno už existuje!").removeClass("d-none");
            $(".register-proceed-button").addClass("disabled");
            }
          else 
            {
            $("#alert").addClass("d-none");
            $(".register-proceed-button").removeClass("disabled");
            }
          }
        });
        }
    }, 1000);
  });

});