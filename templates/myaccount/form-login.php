<?php if ( ! defined( 'ABSPATH' ) ) { exit; } 

if(isset($_GET['redirect'])) {

	$redirect = esc_url($_GET['redirect']);

}

?>

<div class="container-em-login">
	
	<?php yaexam_print_messages(); ?>
	
	<div class="em-form">

		<div class="em-card">
			<div class="em-card-body">
				<form class="em-myaccount-login" action="" method="post">
				
					<?php do_action( 'yaexam_login_form_start' ); ?>
				
					<div class="form-group">
						<label for="username"><?php _e( 'Username or email', 'yaexam' ); ?> <span class="required">*</span></label>
						<input type="text" class="form-control" name="username" id="username" />
					</div>
					<div class="form-group">
						<label for="password"><?php _e( 'Password', 'yaexam' ); ?> <span class="required">*</span></label>
						<input class="form-control" type="password" name="password" id="password" />
						<small class="form-text text-muted">
							<a href="<?php echo yaexam_get_endpoint_url( 'em-lost-password', '', yaexam_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'Lost your password?', 'yaexam' ); ?></a>
						</small>
					</div>

					<div class="form-group form-check">
						<input name="rememberme" type="checkbox" class="form-check-input" id="rememberme">
						<label class="form-check-label" for="rememberme"><?php esc_html_e( 'Remember me', 'yaexam' ); ?></label>
					</div>
				
					<?php do_action( 'yaexam_login_form' ); ?>
				
					<div class="form-action">
						
						<input type="submit" class="em-btn em-btn-primary" name="login" value="<?php esc_attr_e( 'Login', 'yaexam' ); ?>" />
						<?php if(get_option( 'users_can_register' ) == 1): ?>
							<a class="em-ml-2 em-btn em-btn-secondary" href="<?php echo yaexam_get_page_permalink('register'); ?>?redirect=<?php echo urlencode($redirect); ?>" class="register-link"><?php esc_html_e('Register', 'yaexam'); ?></a>
						<?php endif; ?>
						
					</div>
				
					<?php do_action( 'yaexam_login_form_end' ); ?>
					<?php wp_nonce_field( 'yaexam-login' ); ?>
					<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
				</form>
				
				</div>
			</div>
	</div>
</div>