var delay = (function(){
  var timer = 0;
  return function(callback, ms){
    clearTimeout (timer);
    timer = setTimeout(callback, ms);
  };
})();

$(document).ready( function() {

  fetch('/includes/unsplash_get.php?collection=3668324')
      .then(response => response.json())
      .then(data => {
          const dynamicImage = document.getElementById('unsplash-image');
          dynamicImage.style.backgroundImage = `url(${data.image})`;

          const authorInfo = document.getElementById('author-info');
          authorInfo.innerHTML = `Photo by <a href="${data.link}" target="_blank">${data.author}</a> on <a href="https://unsplash.com/?utm_source=hockey-LIVE.sk&utm_medium=referral">Unsplash</a>`;
      })
      .catch(error => console.error('Error fetching unsplash image:', error));

  $(".register-proceed-button").on('click', function(e) {
      e.preventDefault();
      grecaptcha.ready(function() {
        grecaptcha.execute('6LdKMrcZAAAAAG6VxKDluvh6h9UnStzevg8HThd7', {action: 'submit'}).then(function(token) {

        var username=$("#user").val();
        var password=$("#pass").val();
        var passagain=$("#passagain").val();
        var email=$("#email").val();
        var optin = $('#optin').is(':checked') ? 1 : 0;
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if(password!==passagain) $("#alert").html(LANG_USERPROFILE_PASSDIDNTMATCH).removeClass("d-none");
        else if(username.length<3) $("#alert").html(LANG_REGISTER_USERATLEAST3).removeClass("d-none");
        else if(!regex.test(email)) $("#alert").html(LANG_LOGIN_WRONGEMAIL).removeClass("d-none");
        else
          {
          var dataString = 'username='+username+'&password='+password+'&email='+email+'&optin='+optin+'&token='+token;
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
                $("#alert").html(LANG_COMMENTS_CAPTCHAERROR+" <a href='/contact' class='alert-link'>"+LANG_MAIL+"</a>").removeClass("d-none");
                }
              else if(data=="EMAILEXISTS")
                {
                $("#alert").html(LANG_REGISTER_ALREADY+" <a href='/login#ModalCenter' class='alert-link'>"+LANG_REGISTER_DIDYOUFORGETPASS+"</a>").removeClass("d-none");
                }
              else
                {
                $("#alert").html(LANG_REGISTER_INCORRECTFIELDS).removeClass("d-none");
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
            $("#alert").html(LANG_REGISTER_USERTAKEN).removeClass("d-none");
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