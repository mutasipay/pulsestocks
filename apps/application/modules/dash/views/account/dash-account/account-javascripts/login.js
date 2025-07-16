


const validate_email = (email) => {
 return email.match(
  /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
 );
};
$(document).ready(function() {
 
 
 $('#btn-login-to-stock-system').on('click', function() {
  let is_errors = [];
  let logindata = {
   'login_email': $('#inp-account-email').val(),
   'login_password': $('#inp-account-password').val(),
   'login_server': 'localhost'
  };
  // Email
  logindata.login_email = logindata.login_email.toString().trim();
  if(logindata.login_email.length < 1) {
   is_errors.push('Cannot be an empty email.');
  } else {
   if(!validate_email(logindata.login_email)) {
    is_errors.push('Not a valid email address.');
   }
  }
  // Password
  if(logindata.login_password.length > 0) {
   logindata.login_password = logindata.login_password.toString();
  } else {
   is_errors.push('Cannot be an empty password.');
  }
 
 
  let html_errors = '';
  if(is_errors.length > 0) {
   for(let row_msg of is_errors) {
    html_errors += ('<li class="list-group-item">' + row_msg.toString() + '</li>');
   }
   $('#error-login-placeholder').html(html_errors);
  } else {
   let loginparams = {
    'login_server': 'localhost',
   };
   // Doing Encrypted Password Then Make a logged-session
   const keyParams = new Date().toString() + logindata.login_email;
   md5string(keyParams).then(async function(login_key) {
    loginparams.login_key = login_key;
    loginparams.login_email  = await sha256encrypt(logindata.login_email, login_key);
    loginparams.login_password = await sha256encrypt(logindata.login_password, login_key);
    $.ajax({
     'type': 'POST',
     'url': base_url('dash/account/login/sha256descrypt'),
     'dataType': 'json',
     'data': loginparams,
     'success': function(response) {
      if(response.status == true) {
       window.location = base_url('dash/dashboard/dashboard');
      } else {
       if('errors' in response) {
        if(response.errors.length > 0) {
         for(let err_msg of response.errors) {
          html_errors += ((typeof(err_msg) == 'string') ? ('<li class="list-group-item">' + err_msg.toString() + '</li>') : '');
         }
        }
       }
       $('#error-login-placeholder').html(html_errors);
      }
     }
    });
   });
  }
 });
 
 
 
});