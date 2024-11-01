<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="em-container container-em-myaccount-reset-password">
	
	<?php yaexam_print_messages(); ?>
	
	<h1 class="em-myaccount-title-login"><?php esc_html_e('Reset Password', 'yaexam'); ?></h1>
	
	<form class="em-myaccount-reset-password" action="" method="post">
		
		<?php do_action( 'yaexam_login_form_start' ); ?>
		
		<div class="form-row">
			<label for="password_1"><?php esc_html_e( 'Password', 'yaexam' ); ?></label>
			<div class="ginput">
				<input type="text" class="input-text" name="password_1" id="password_1" />
			</div>
		</div>
		
		<div class="form-row">
			<label for="password_2"><?php esc_html_e( 'Password Again', 'yaexam' ); ?></label>
			<div class="ginput">
				<input type="text" class="input-text" name="password_2" id="password_2" />
			</div>
		</div>
		
		<?php do_action( 'yaexam_reset_password_form' ); ?>
		
		<div class="form-action">
			<?php wp_nonce_field( 'yaexam-reset-password' ); ?>
			<input type="submit" class="button" name="reset_password" value="<?php esc_attr_e( 'Reset', 'yaexam' ); ?>" />
			<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
		</div>
		
		<?php do_action( 'yaexam_login_form_end' ); ?>
	</form>
</div>