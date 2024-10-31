function sortPlayers(attribute, asc = true) {
    const playerContainers = document.querySelectorAll('.playerContainer');
    const sorted = Array.from(playerContainers).sort((a, b) => {
        let aValue = a.getAttribute(`data-${attribute}`);
        let bValue = b.getAttribute(`data-${attribute}`);

        // Pre èísla
        aValue = isNaN(aValue) ? aValue : parseFloat(aValue);
        bValue = isNaN(bValue) ? bValue : parseFloat(bValue);

        if (asc) {
            return aValue > bValue ? 1 : -1;
        } else {
            return aValue < bValue ? 1 : -1;
        }
    });

    const container = document.querySelector('#playersContainer');
    sorted.forEach(player => container.appendChild(player));
}

$(document).ready( function() {

  document.querySelectorAll('.sort-option').forEach(option => {
      option.addEventListener('click', function() {
          const sortBy = this.getAttribute('data-sort');
          let isAscending = false;

          sortPlayers(sortBy, isAscending);
      });
  });

  document.querySelectorAll('i[data-sort]').forEach(icon => {
      icon.addEventListener('click', function() {
          const sortBy = this.getAttribute('data-sort');
          let isAscending = this.classList.contains('fa-arrow-down-wide-short');
          
          document.querySelectorAll('i[data-sort]').forEach(i => {
              i.classList.remove('fa-arrow-up-wide-short', 'text-gray-800');
              i.classList.add('fa-arrow-down-wide-short');
          });
          
          if (isAscending) {
              this.classList.remove('fa-arrow-down-wide-short');
              this.classList.add('fa-arrow-up-wide-short', 'text-gray-800');
          } else {
              this.classList.remove('fa-arrow-up-wide-short');
              this.classList.add('fa-arrow-down-wide-short', 'text-gray-800');
          }
          
          sortPlayers(sortBy, isAscending);
      });
  });

  $('body').on('focus',"#addPlayer", function(){
    $(this).autocomplete({
      source: "/includes/player_autocomplete.php",
      minLength: 3,
      select: function( event, ui ) {
        var str = ui.item.label.replace(/<(?:.|\n)*?>/gm, '');
        str = str.substring(1);
        $(this).val(str);
        $("#addPlayerId").val(ui.item.value);
        return false;
      }
    }).autocomplete( "instance" )._renderItem = function( ul, item ) {
      return $( "<li>" )
        .append( "<div>" + item.label + "</div>" )
        .appendTo( ul );
    };
  });

  $('body').on('click',"#addPlayerOK", function(event){
    var pid=$("#addPlayerId").val();
    $.post("/includes/players_functions.php",{ add:pid } ,function(data) {
        if(data) location.href = "/watched";
    });
  });

  var button;

  $('#deletePlayer').on('show.bs.modal', function (event) {
    button = $(event.relatedTarget)
    var pid = button.data('player') 
    var modal = $(this)
    modal.find('#delpid').val(pid);
  });
  
  $('body').on('click',".deletePlayerOK", function(event){
    event.preventDefault();
    var pid=$("#delpid").val();
    $.post("/includes/players_functions.php",{ del:pid } ,function(data) {
        if(data) {
            var today  = new Date();
            var smallText = today.toLocaleDateString("sk-SK");
            button.closest(".playerContainer").slideUp();
            Notification("user text-success", LANG_PLAYERWATCH, smallText, LANG_PLAYERS_DELETED, 5000);
        }
    });
    $('#deletePlayer').modal('hide')
  });

});