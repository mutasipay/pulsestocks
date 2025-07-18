<?php
if(!defined('BASEPATH')) {
 exit('Cannot load script directly.');
}
?>
<!-- Outer Row -->
<div class="row justify-content-center">
 <div class="col-xl-10 col-lg-12 col-md-9">
  <div class="card o-hidden border-0 shadow-lg my-5">
   <div class="card-body p-0">
    <!-- Nested Row within Card Body -->
    <div class="row">
     <div class="col-lg-4 col-md-4 d-none d-lg-block bg-login-image">
      <div class="p-5">
       <div class="text-center">
        <h1 class="h4 text-gray-900 mb-4">Stock System</h1>
       </div>
      </div>
     </div>
     <div class="col-lg-8 col-md-8">
      <div class="p-5">
       <div class="text-center">
        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
       </div>
       <div class="form-group">
        <ul id="error-login-placeholder" class="list-group text-danger"></ul>
       </div>
       <form id="frm-login-to-stock-system" class="user">
        <div class="form-group">
         <input type="email" class="form-control form-control-user" id="inp-account-email" aria-describedby="emailHelp" placeholder="Enter Email Address..." />
        </div>
        <div class="form-group">
         <input type="password" class="form-control form-control-user" id="inp-account-password" placeholder="Password" />
        </div>
        <div class="form-group">
         <div class="custom-control custom-checkbox small">
          <input type="checkbox" class="custom-control-input" id="customCheck" />
          <label class="custom-control-label" for="customCheck">Remember Me</label>
         </div>
        </div>
        <a id="btn-login-to-stock-system" href="javascript:void(0);" class="btn btn-primary btn-user btn-block">Login</a>
        <!--
        <hr/>
        <a href="index.html" class="btn btn-google btn-user btn-block">
         <i class="fab fa-google fa-fw"></i> Login with Google
        </a>
        <a href="index.html" class="btn btn-facebook btn-user btn-block">
         <i class="fab fa-facebook-f fa-fw"></i> Login with Facebook
        </a>
        -->
       </form>
       <?php
       /*
       <hr/>
       <div class="text-center">
        <a class="small" href="<?= base_url();?>">Forgot Password?</a>
       </div>
       <div class="text-center">
        <a class="small" href="<?= base_url();?>">Create an Account!</a>
       </div>
       */
       ?>
      </div>
     </div>
    </div>
   </div>
  </div>
 </div>
</div>