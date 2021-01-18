function Notification(icon, title, smallText, text, delay) {
  var id = new Date().getTime();
  var toastContent = $('<div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="'+delay+'" id="'+id+'"><div class="toast-header"><i class="fas fa-'+icon+'"></i><strong class="mr-auto px-2">'+title+'</strong><small class="text-muted px-2">'+smallText+'</small><button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="toast-body">'+text+'</div></div>');
  $('#toasts').append(toastContent);
  $('#'+id).toast('show');
  }
  
function SwipeButtonCheck() {
  var rt = $('.table-responsive, .table-responsive-sm, .table-responsive-md, .table-responsive-lg, .table-responsive-xl');
  rt.each(function( i ) {
    if (this.offsetWidth < this.scrollWidth) {
      $(this).parents('.card-body').siblings('.card-header').find(".swipe").removeClass("d-none");
    } else {
      $(this).parents('.card-body').siblings('.card-header').find(".swipe").addClass("d-none");
    }
  });
  }

$(document).ready( function() {
  "use strict"; // Start of use strict
  
  $('[data-toggle="tooltip"]').tooltip();
  SwipeButtonCheck();

  // Toggle the side navigation
  $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
  });
 
  // Apply background filter when menu accordion is opened on mobile
  $('.sidebar .collapse').on('show.bs.collapse', function () {
  if ($(window).width() < 768) {
    $('.sidebar .collapse').collapse('hide');
    $('#content-wrapper').css("filter", "brightness(60%) blur(1px)");
    }
  });
  
  $('.sidebar .collapse').on('shown.bs.collapse', function () {
  if ($(window).width() < 768) {
    var $anchor = $(this);
    $('html, body').stop().animate({
      scrollTop: ($($anchor).offset().top-45)
    }, 500, 'easeInOutExpo');
    }
  });
  
  // Open menu accordion for active league on desktop
  if ($(window).width() > 767) {
    $('.sidebar .nav-item.active').children('.collapse').collapse('show');
  }
  
  // Cancel background filters
  $('.sidebar .collapse').on('hide.bs.collapse', function () {
  if ($(window).width() < 768) {
    $('#content-wrapper').css("filter", "none");
    }
  });

  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $('body.fixed-nav .sidebar').on('mousewheel DOMMouseScroll wheel', function(e) {
    if ($(window).width() > 768) {
      var e0 = e.originalEvent,
        delta = e0.wheelDelta || -e0.detail;
      this.scrollTop += (delta < 0 ? 1 : -1) * 30;
      e.preventDefault();
    }
  });
  
  // Show swipe icon when the responsive tables are scrollable
  $( window ).resize(function() {
    SwipeButtonCheck();
  });

  // Scroll to top button appear
  $(document).on('scroll', function() {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $('.scroll-to-top').fadeIn();
    } else {
      $('.scroll-to-top').fadeOut();
    }
  });

  // Smooth scrolling using jQuery easing
  $(document).on('click', 'a.scroll-to-top', function(e) {
    var $anchor = $(this);
    $('html, body').stop().animate({
      scrollTop: ($($anchor.attr('href')).offset().top)
    }, 1000, 'easeInOutExpo');
    e.preventDefault();
  });
  
  // Player, team and league autocomplete search
  $('body').on('focus',".search", function(){
    $(this).autocomplete({
      source: "/includes/autocomplete.php",
      minLength: 3,
      select: function( event, ui ) {
        var str = ui.item.label.replace(/<(?:.|\n)*?>/gm, '');
        str = str.substring(1);
        $(this).val(str);
        var id = ui.item.value.split('-');
        if(id[0]==0 || id[0]==1) location.href = "/player/"+id[1];
        if(id[0]==2 || id[0]==3) location.href = "/team/"+id[1];
        if(id[0]==4) location.href = "/goalie/"+id[1];
        if(id[0]==5) location.href = "/table/"+id[1];
        return false;
      }
    }).autocomplete( "instance" )._renderItem = function( ul, item ) {
      return $( "<li>" )
        .append( "<div>" + item.label + "</div>" )
        .appendTo( ul );
    };
  });
  
  // set read flag on notification click
  $('body').on('click',".notifications .dropdown-item.d-flex", function(){
    if($(this).hasClass('alert-warning'))
      {
      var nid = $(this).data('id');
      var dataString = "markread="+nid;
      $.ajax({
      type: "POST",
      url: "/includes/notifications.php",
      dataType: "text",
      contentType:"application/x-www-form-urlencoded; charset=utf-8",
      data: dataString,
      cache: false
        });
      }
  });

}); // End of use strict

$(function() {
  $(".lazy").lazy(
    { effect: 'fadeIn',     
      afterLoad: function(e) {
        e.removeClass("lazy");
    }}
  );
});