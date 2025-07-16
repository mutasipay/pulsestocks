const show_modal_loaders = function(plc, datas) {
 let modal_html = '' +
 '<div class="modal-dialog modal-lg" style="width:72%;" role="document">' +
  '<div class="modal-content">' +
   '<div class="modal-header">' +
    '<h5 class="modal-title">Loading....</h5>' +
    '<button class="close" type="button" data-dismiss="modal" aria-label="Close">' +
     '<span aria-hidden="true"><i class="fa fa-fw fa-times"></i></span>' +
    '</button>' +
   '</div>' +
   '<div class="modal-body">' +
    (('html' in datas) ? datas.html.toString() : 'Loading....') +
   '</div>' +
   '<div class="modal-footer">' +
    '<button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>' +
    '<a class="btn btn-primary" href="javascript:void(0);">Cancel</a>' +
   '</div>' +
  '</div>' +
 '</div>';
 plc.html(modal_html).modal({
  'show': true
 });
}



const tbl_provider_params = {
 'lengthMenu': [
  [5, 10, 25, 50, 100, -1],
  [5, 10, 25, 50, 100, 'All']
 ],
 'pageLength': 5,
 'searching': true,
 'pagingInfo': true,
 'pagingType': 'full_numbers',
 'info': true,
 'serverSide': true,
 'ajax': {
  'url': base_url('dash/stocks/providers/data'),
  'contentType': 'application/x-www-form-urlencoded; charset=UTF-8',
  'type': 'POST',
  'dataType': 'json',
  'data': function(datapost) {
   const form_dates = {
    'date_ranges': {
     'starting': '',
     'stopping': '',
    },
    'localdata': provider_params.users.localdata,
    'params': {
     'country_code': $('#opt-country-code').val(),
     'country_data': provider_params.country_data
    }
   };
   const form_search = {
    'draw': (('draw' in datapost) ? datapost.draw : 1),
    'start': 0,
    'limit': 10,
    'search_text': '',
    'names': [],
    'orders': {
     'by': 'provider_code',
     'sort': 'desc',
     'column': 0
    },
    'search_columns': []
   };
   if ('search' in datapost) {
    form_search.search_text = (('value' in datapost.search) ? datapost.search['value'] : '');
   }
   if ('length' in datapost) {
    form_search['limit'] = parseInt(datapost['length']);
   }
   if ('start' in datapost) {
    form_search.start = parseInt(datapost['start']);
   }
   if ('columns' in datapost) {
    if (datapost.columns.length > 0) {
     for (var column of datapost.columns) {
      if ('name' in column) {
       form_search.names.push(column.name);
      }
     }
    }
   }
   if ('order' in datapost) {
    if (datapost.order.length > 0) {
     if (('column' in datapost.order[0]) && ('dir' in datapost.order[0])) {
      form_search.orders.column = parseInt(datapost.order[0].column);
      form_search.orders.sort = ((typeof datapost.order[0].dir == 'string') ? datapost.order[0].dir.toString().toLowerCase() : 'desc');
      if(['asc', 'desc'].includes(form_search.orders.sort) != true) {
       form_search.orders.sort = 'desc';
      }
     }
     // const post_columns = Object.key();
     if (('name' in datapost.columns[form_search.orders.column]) && ('search' in datapost.columns[form_search.orders.column])) {
      form_search.search_columns.push(datapost.columns[form_search.orders.column].name);
     }
    }
   }
   return '' + $.param({
    'form_search': form_search,
    'form_dates': form_dates
   });
  }
 },
 'processing': true,
	'language': {
		'processing': '<span class="text-primary"><i class="fa fa-spinner fa-lg fa-xl"></i> Loading....</span>'
	},
 'columns': [
  {
   'data': null,
   'name': 'no',
   'render': function(data, type, full, meta) {
    return (meta.row + 1);
   }
  },
  {
   'data': 'country_name',
   'name': 'country_name',
   'render': function(data, type, full, meta) {
    return '<a class="text-primary" href="javascript:void(0);" data-country-code="' + full.country_code + '">' + full.country_name + '</a>';
   }
  },
  {
   'data': 'provider_code',
   'name': 'provider_code',
   'render': function(data, type, full, meta) {
    return '<ul class="list-group"><li class="list-group-item"><a class="text-primary" href="javascript:void(0);" data-provider-code="' + full.provider_code + '" data-country-code="' + full.country_code + '">' + full.provider_code + '</a></li><li class="list-group-item">' + full.country_code + '</li></ul>';
   }
  },
  {
   'data': null,
   'name': 'provider_name',
   'render': function(data, type, full, meta) {
    return '<a class="text-primary" href="javascript:void(0);" data-provider-code="' + full.provider_code + '" data-country-code="' + full.country_code + '">' + full.provider_name + '</a>';
   }
  },
  {
   'data': null,
   'name': 'provider_activated',
   'render': function(data, type, full, meta) {
    let html_response = '';
    return html_response;
   }
  },
  {
   'data': null,
   'name': 'id',
   'render': function(data, type, full, meta) {
    let html_response = '';
    return html_response;
   }
  }
 ]
}

const validate_country = (country_code) => {
 return true;
};

$(document).ready(function() {
 const tbl_provider_data = $('#tbl-provider-data').DataTable(tbl_provider_params);
 
 $('#frm-add-providers').on('click', '#btn-add-providers', function(e) {
  e.preventDefault();
  let html_errors = '';
  let is_errors = [];
  let add_params = {
   'country_code': $('#add-providers-country-code').val(),
  };
  if(!add_params.country_code) {
   is_errors.push('You must set country code.');
  } else {
   if(add_params.country_code.length < 1) {
    is_errors.push('Please select available country, cannot make empty country.');
   } else {
    if(!provider_params.country_codes.includes(add_params.country_code)) {
     is_errors.push('Country code not yet activated.');
    }
   }
  }
  
  if(is_errors.length > 0) {
   for(let row_msg of is_errors) {
    html_errors += ('<li class="list-group-item">' + row_msg.toString() + '</li>');
   }
   $('#error-providers-add').html(html_errors);
  } else {
   show_modal_loaders($('#quick-shop-modal'), {
    'html': "Loading Form for add new providers."
   });
   
   $.ajax({
    'type': 'POST',
    'url': base_url('dash/stocks/providers/add/form'),
    'data': add_params,
    'success': function(response) {
     $('#quick-shop-modal').html(response).modal({
      'show': true,
      'backdrop': 'static',
      'keyboard': false
     });
    },
    'error': function(reserr) {
     $('#error-providers-add').html(reserr);
    }
   });
  }
  
 });
 
 
 // Add new provider
 $(document).on('click', '#btn-add-providers-submit', function(e) {
  e.preventDefault();
  let submit_params = {
   'country_code': $("#inp-country-name option:selected").attr('data-country-code'),
   'country_name': $('#inp-country-name').val(),
   'provider_name': $('#inp-provider-name').val(),
  };
  
  console.log(submit_params);
 });












});