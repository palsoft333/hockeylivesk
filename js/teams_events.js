$(document).ready(function() {
	
    $('#goalies').DataTable( {
        "paging":   false,
        "info":     false,
        "searching":     false,
        "order": [[ 5, 'desc' ], [ 7, 'asc' ]]
    } );
    
    $('#players').DataTable( {
        "paging":   false,
        "info":     false,
        "searching":     false,
        "order": [[ 5, 'desc' ], [ 2, 'asc' ], [ 3, 'desc' ], [ 4, 'desc' ], [ 9, 'desc' ], [ 8, 'desc' ], [ 6, 'asc' ]]
    } );
    
    $('#nonelplayers').DataTable( {
        "paging":   false,
        "info":     false,
        "searching":     false,
        "order": [[ 5, 'desc' ], [ 2, 'asc' ], [ 3, 'desc' ], [ 4, 'desc' ], [ 9, 'desc' ], [ 8, 'desc' ], [ 6, 'asc' ]]
    } );
    
  $("#league").on('change', function() {
    var lid = this.value;
    window.location.href = "/team/"+lid;
  });

} );