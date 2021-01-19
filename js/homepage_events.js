var events = {};

function extract(rootObject, key) {
    var parts = key.split('.');
    var currentKey = parts.shift();
    return parts.length > 0 ? extract(rootObject[currentKey], parts.join('.')) : rootObject[currentKey];
}

function GetCalEvents(month, year) {
  $.ajax({
       type:"POST",
       url:"/includes/events.php?m="+month+"&y="+year,
       dataType: "text",
       contentType:"application/x-javascript; charset:utf-8",
       beforeSend: function() { $('#calendar-spinner').show(); },
       complete: function() { $('#calendar-spinner').hide(); },
       success: function (data) {
           events = JSON.parse(data);
           var colors = [];
            for (var rgb in events) {
                var cid = events[rgb][0]['color'].split('|');
                colors.push(cid);
            }
            var uniqueCoors = [];
            var doneCoors = [];
            for(var x = 0; x < colors.length; x++) {
                var coorStr = colors[x].toString();
                if(doneCoors.indexOf(coorStr) != -1) {
                    continue;
                }
                doneCoors.push(coorStr);
                uniqueCoors.push(colors[x]);
            }
           
           $( '#calendar' ).calendario('setData', events);
           for(var x = 0; x < uniqueCoors.length; x++) {
                var lid = uniqueCoors[x][0];
                var col = uniqueCoors[x][1];
                $(this).addClass("btn-"+col);
                //$('.league' + lid).css('background', convertHex(col,20));
            }
       }
  });
}

function GetFlashNews() {
  $.ajax({
  type: "POST",
  url: "/includes/flashnews.php",
  dataType: "text",
  contentType:"application/x-www-form-urlencoded; charset=utf-8",
  cache: false,
  complete: function() { $('#flash-spinner').hide(); },
  success: function(data){
    if(data)
      {
      $("#flash-container").html(data).fadeIn(1500);
      $(function() {
        $(".lazy").lazy(
          { effect: 'fadeIn',     
            afterLoad: function(e) {
              e.removeClass("lazy");
          }}
        );
      });
      }
    else
      {
      Notification("Nemôžem načítať rýchle novinky.");
      }
    }
  });
}

$(document).ready( function() {

            $(function() {
                function updateMonthYear() {
					$( '#custom-month' ).html( $( '#calendar' ).calendario('getMonthName') );
					$( '#custom-year' ).html( $( '#calendar' ).calendario('getYear'));
          GetCalEvents($('#calendar').calendario('getMonth'),$('#calendar').calendario('getYear'));
				}
				
				$(document).on('finish.calendar.calendario', function(e){
				GetCalEvents($('#calendar').calendario('getMonth'),$('#calendar').calendario('getYear'));
                    $( '#custom-month' ).html( $( '#calendar' ).calendario('getMonthName') );
					$( '#custom-year' ).html( $( '#calendar' ).calendario('getYear'));
					$( '#custom-next' ).on( 'click', function() {
						$( '#calendar' ).calendario('gotoNextMonth', updateMonthYear);
						ga('send', 'event', 'Page', 'calendarTurn', $('#calendar').calendario('getYear')+'-'+$('#calendar').calendario('getMonth'));
					} );
					$( '#custom-prev' ).on( 'click', function() {
						$( '#calendar' ).calendario('gotoPreviousMonth', updateMonthYear);
						ga('send', 'event', 'Page', 'calendarTurn', $('#calendar').calendario('getYear')+'-'+$('#calendar').calendario('getMonth'));
					} );
					$( '#custom-current' ).on( 'click', function() {
						$( '#calendar' ).calendario('gotoNow', updateMonthYear);
						ga('send', 'event', 'Page', 'calendarTurn', $('#calendar').calendario('getYear')+'-'+$('#calendar').calendario('getMonth'));
					} );
                });
				
				$('#calendar').on('shown.calendar.calendario', function(){
					$('div.fc-row > div').on('onDayClick.calendario', function(e, dateprop) {
						if(typeof dateprop.data.content !== 'undefined' && dateprop.data.content.length > 0 ) {
							showEvents(dateprop.data.html, dateprop);
						}
					});
				});
            
                var $wrapper = $( '#custom-inner' );

                function showEvents( contentEl, dateprop ) {
                    hideEvents();
                    var $events = $( '<div id="custom-content-reveal" class="animated--fade-in list-group border custom-content-reveal h-100 p-2 position-absolute top w-100" style="top:0; left:0; background:rgba(246, 246, 246, 0.9);"><h6 class="font-weight-bold text-center text-success text-uppercase">Zápasy pre ' + dateprop.day + '.'
					+ dateprop.monthname + ' ' + dateprop.year + '</h4></div>' ),
                    $close = $( '<div class="p-1 position-absolute" style="right: 0; top: 0;"><button type="button" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' ).on( 'click', hideEvents);
                    $events.append( contentEl.join('') , $close ).insertAfter( $wrapper );
                    setTimeout( function() {
                        $events.css( 'opacity', '1' );
                    }, 25);
                }
				
                function hideEvents() {
                    var $events = $( '#custom-content-reveal' );
                    if( $events.length > 0 ) {   
                        $events.css( 'opacity', '0' );
                        $events.remove();
                    }
                }
				
				$( '#calendar' ).calendario({
                    caldata : events,
                    displayWeekAbbr : true,
                    events: ['click', 'focus', 'hover']
                });
            
       
          });
      
     $('#flash-spinner').show();
     GetFlashNews();

});