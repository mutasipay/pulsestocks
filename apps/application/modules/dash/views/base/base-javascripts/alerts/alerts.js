


$(document).ready(function() {
 
 $.ajax({
  'type': 'GET',
  'url': base_url('dash/alerts/alerts/users'),
  'dataType': 'json',
  'success': function(response) {
   if(response.status == true) {
    if('data' in response) {
     if('val_counts' in response.data) {
      let http_headers = new Headers({
       'Content-type': 'application/x-www-form-urlencoded',
      });
      let http_payloads = new URLSearchParams();
      http_payloads.append("alert_type", "drafted");
      const val_counts = parseInt(response.data.val_counts);
      if(val_counts > 0) {
       $('#user-alerts-counts').text('' + val_counts + '+');
       fetch(base_url('dash/alerts/alerts/get-data'), {
        'method': "POST",
        'headers': http_headers,
        'body': http_payloads,
        'redirect': "follow"
       }).then(function(res) {
        return res.json();
       }).then(function(results) {
        const plc_alerts = $('#plc-center-users-alerts');
        let alerts_html = '<h6 class="dropdown-header">Alerts Center</h6>';
        if(results.status == true) {
         if('data' in results) {
          if(results.data.length > 0) {
           for(let row of results) {
            if(row.draft_processed.toString().toUpperCase() == 'N') {
             var alert_styles = {
              'style': '',
              'font': 'font-weight-bold'
             };
            } else {
             var alert_styles = {
              'style': '',
              'font': 'font-weight-default'
             };
            }
            switch(row.stock_type.toString().toLowerCase()) {
             case 'deduct':
             case 'used':
              alert_styles.icon = 'icon-circle bg-warning';
             break;
             case 'topup':
              alert_styles.icon = 'icon-circle bg-success';
             break;
             case 'balance':
             default:
              alert_styles.icon = 'icon-circle bg-primary';
             break;
            }
            alerts_html += '<a class="dropdown-item d-flex align-items-center" href="' + base_url('dash/alerts/view/drafts/' + row.draft_id) + '">' +
             '<div class="mr-3">' +
              '<div class="' + alert_styles.icon + '">' +
               '<i class="fas fa-file-alt text-white"></i>' +
              '</div>' +
             '</div>' +
             '<div>' +
              '<div class="small text-gray-500">' + row.stock_created + '</div>' +
              '<span class="' + alert_styles.font + '">' + row.draft_message + '</span>' +
             '</div>' +
            '</a>';
           }
          }
         }
        }
        plc_alerts.html(alerts_html + '<a class="dropdown-item text-center small text-gray-500" href="' + base_url('dash/alerts/view/all') + '">Show All Alerts</a>');
       }).catch(function(e) {
        console.log(e);
       });
      }
     }
    }
   }
  },
  'error': function(e) {
   
  }
 });
 
});