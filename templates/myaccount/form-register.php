<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="em-form">
	
	<?php yaexam_print_messages(); ?>
		
	<form class="em-myaccount-register" action="" method="post">
	
		<?php do_action( 'yaexam_register_form_start' ); ?>
		
		<div class="form-group">
			<label for="username"><?php _e( 'Username', 'yaexam' ); ?> <span class="required">*</span></label>
			<input type="text" class="form-control" name="username" id="username" />
		</div>
		
		<div class="form-group">
			<label for="email"><?php _e( 'Email address', 'yaexam' ); ?> <span class="required">*</span></label>
			<input type="email" class="form-control" name="email" id="email" />
		</div>
		
		<div class="form-group">
			<label for="password"><?php _e( 'Password', 'yaexam' ); ?> <span class="required">*</span></label>
			<input type="password" class="form-control" name="password" id="password" />
		</div>
		
		<?php do_action( 'yaexam_register_form' ); ?>
		
		<div class="em-mt-4 form-action">
			<?php wp_nonce_field( 'yaexam_save_register' ); ?>
			<input type="submit" class="em-btn em-btn-primary" name="yaexam_save_register" value="<?php esc_attr_e( 'Register', 'yaexam' ); ?>" />
			<input type="hidden" name="action" value="yaexam_save_register" />

			<?php if(isset($_GET['redirect'])): ?>
			<input type="hidden" name="redirect" value="<?php echo esc_url(urldecode($_GET['redirect'])); ?>" />
			<?php endif; ?>
		</div>
	
		<?php do_action( 'yaexam_register_form_end' ); ?>
	</form>
	
</div>