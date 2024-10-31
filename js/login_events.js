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