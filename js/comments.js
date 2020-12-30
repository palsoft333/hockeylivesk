function onRecaptchaLoadCallback() {
    var clientId = grecaptcha.render('inline-badge', {
        'sitekey': '6LdKMrcZAAAAAG6VxKDluvh6h9UnStzevg8HThd7',
        'badge': 'inline',
        'size': 'invisible'
    });
    
    $("#addcomment").click(function( event ) {
    
    event.preventDefault();
    grecaptcha.ready(function() {
      grecaptcha.execute(clientId, {action: 'submit'}).then(function(token) {

      var error=false;
      var name = $('#name').val();
      var uid = $('#uid').val();
      var what = $('#what').val();
      var whatid = $('#whatid').val();
      var replyid = $('#replyid').val();
      var comment = $('#comment').val();
      if(name=="") { $("#name").addClass("is-invalid"); error=true; }
      if(comment=="") { $("#comment").addClass("is-invalid"); error=true; }
      if(error==false)
        {
        $.post("/includes/comments.php",{ name:name,uid:uid,what:what,whatid:whatid,replyid:replyid,comment:comment,token:token } ,function(data) {
            if(data=="CAPTCHAERROR")
              {
              $(".alert").addClass("alert-danger").removeClass("alert-success").html("Vaša registrácia neprešla spam testom. Skúste prosím znovu, alebo nám napíšte <a href='/contact' class='alert-link'>e-mail</a>").slideDown();
              }
            else
              {
              $(".alert").addClass("alert-success").removeClass("alert-danger").html("Komentár bol úspešne pridaný.").slideDown();
              $('#comment').val("");
              $('#comments').html(data);
              }
          });
        }
      return false;

      });
    });
  });
  
}

$(document).ready(function(){

  $('#comment').emojiarea({wysiwyg: false});
  
  $('body').on('click',".replyComment", function(){
  var cid=$(this).attr("data-cid");
  $("#replyid").val(cid);
  $('html, body').animate({
      scrollTop: $("#comment").offset().top
  }, 2000);
  $("#comment").focus();
  });
  
  $('#deleteComment').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget)
    var cid = button.data('cid')
    var modal = $(this)
    modal.find('#cid').val(cid);
  });
  
  $(".deleteComment").click(function( event ) {
    event.preventDefault();
    var cid=$("#cid").val();
    $.post("/includes/comments.php",{ del:cid } ,function(data) {
     $('#deleteComment').modal('hide');
     $(".alert").addClass("alert-success").removeClass("alert-danger").html("Komentár bol úspešne vymazaný.").slideDown();
     $('#comments').html(data);
      });
    });
});