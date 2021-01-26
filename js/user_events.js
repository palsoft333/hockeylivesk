$(function() {
    $( "#favourite" ).autocomplete({
      minLength: 0,
      source: teams,
      appendTo: "#suggestions",
      focus: function( event, ui ) {
        $( "#favourite" ).val( ui.item.label );
        return false;
      },
      select: function( event, ui ) {
        $( "#favourite" ).val( ui.item.label );
        $( "#tshort" ).val( ui.item.value );
        //$( "#fticon" ).attr( "class", ui.item.value + "_small" );
        return false;
      }
    })
  });

$(document).ready( function() {

  var today  = new Date();
  var smallText = today.toLocaleDateString("sk-SK");
  
  $("#change-pass").on('click', function(e) {
    e.preventDefault();
    var currentpass=$("#currentpass").val();
    var password=$("#pass").val();
    var passagain=$("#passagain").val();
    if(password!==passagain) Notification("key text-danger", LANG_ERROR, smallText, LANG_USERPROFILE_PASSDIDNTMATCH, 5000);
    else if(password.length<6) Notification("key text-danger", LANG_ERROR, smallText, LANG_USERPROFILE_PASSATLEAST6, 5000);
    else
      {
      var dataString = 'change=pass&currentpass='+currentpass+'&password='+password;
      if($.trim(password).length>0 && $.trim(currentpass).length>0)
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
            Notification("key text-success", LANG_USERPROFILE_PASSCHANGETITLE, smallText, LANG_USERPROFILE_PASSCHANGEOK, 5000);
            $("#currentpass").val("");
            $("#pass").val("");
            $("#passagain").val("");
            }
          else
            {
            Notification("key text-danger", LANG_ERROR, smallText, LANG_USERPROFILE_PASSCHANGEERROR, 5000);
            }
          }
        });

        }
      }
    return false;
  });
  
  var $uploadAvatar;
  
    $uploadAvatar = $('#upload-avatar').croppie({
    viewport: {
      width: 100,
      height: 100,
      type: 'circle'
    },
    enableExif: true
  });

  $('#upload').on('change', function () { readFile(this, 'avatar'); });
  
  function readFile(input, kde) {
    if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function (e) {
          $('.upload-avatar').addClass('ready');
              $uploadAvatar.croppie('bind', {
                url: e.target.result
              }).then(function(){
                console.log('jQuery bind complete');
              });
              
            }
            
            reader.readAsDataURL(input.files[0]);
        }
        else {
          alert("Sorry - you're browser doesn't support the FileReader API");
      }
  }
  
  $("#change-data").on('click', function() {
    if($('.upload-avatar').hasClass('ready')) {
      $uploadAvatar.croppie('result', {
          type: 'base64',
          size: {width:200, height:200},
          format: 'jpeg',
          circle: true
        }).then(function (resp) {
          $('#avatar').val(resp);
        });
    }
    var email=$("#email").val();
    var tshort=$("#tshort").val();
    var lang=$("#lang").val();
    var goalhorn=$('input[name=options]:checked').val();
    if($.trim(email).length>0 && $.trim(tshort).length>0)
      {
      setTimeout(function(){
        var avatar=$("#avatar").val();
        $.ajax({
        type: "POST",
        url: "/includes/login.php",
        contentType:"application/x-www-form-urlencoded; charset=utf-8",
        data: {change: "data", goalhorn: goalhorn, email: email, tshort: tshort, lang: lang, avatar: avatar},
        cache: false,
        success: function(data){
          if(data)
            {
            Notification("cogs text-success", LANG_SETTINGS, smallText, LANG_USERPROFILE_SETTINGSCHANGED, 3000);
            setTimeout(function(){ location.href = "/profile"; }, 3000);
            }
          else
            {
            Notification("cogs text-danger", LANG_SETTINGS, smallText, LANG_USERPROFILE_SETTINGSERROR, 5000);
            }
          }
        });
      }, 1);
     }
    return false;
  });
  
  $("#sound-1").on('click', function() {
    $("#sound-1").css("color", "#075988");
    responsiveVoice.speak(LANG_USERPROFILE_MIKECHECK, LANG_USERPROFILE_VOICE, {rate: 1.0, onend: function(){ $("#sound-1").css("color", "gray"); }});
    return false;
  });
  
  $("#sound-2").on('click', function() {
    $("#sound-2").css("color", "#075988");
    var audioElement = document.createElement('audio');
    audioElement.setAttribute('src', '/includes/sounds/goal.mp3');
    audioElement.play();
    setTimeout(function(){ $("#sound-2").css("color", "gray"); }, 500);
    return false;
  });
});