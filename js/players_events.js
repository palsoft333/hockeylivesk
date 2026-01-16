$(document).ready(function() {   
  $("#team").on('change', function() {
    var tshort = this.value;
    window.location.href = "/database/"+tshort;
  });
});