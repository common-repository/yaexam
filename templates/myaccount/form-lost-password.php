<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="em-form container-em-myaccount-lost-password">
	
	<?php yaexam_print_messages(); ?>
		
	<form class="em-myaccount-lost-password" action="" method="post">
		
		<?php if( 'lost_password' === $args['form'] ) : ?>
		
		<div class="form-group">
			<label for="user_login"><?php _e( 'Username or email', 'yaexam' ); ?> <span class="required">*</span></label>
			<input type="text" class="form-control" name="user_login" id="user_login" />
		</div>
		
		<?php else : ?>
		
		<div class="form-group">
			<label for="password_1"><?php _e( 'Password', 'yaexam' ); ?></label>
			<input type="password" class="form-control" name="password_1" id="password_1" />
		</div>
		
		<div class="form-group">
			<label for="password_2"><?php _e( 'Password Again', 'yaexam' ); ?></label>
			<input type="password" class="form-control" name="password_2" id="password_2" />
		</div>
		
		<input type="hidden" name="reset_key" value="<?php echo isset( $args['key'] ) ? $args['key'] : ''; ?>" />
		<input type="hidden" name="reset_login" value="<?php echo isset( $args['login'] ) ? $args['login'] : ''; ?>" />
		
		<?php endif; ?>
		
		<?php do_action( 'yaexam_lostpassword_form' ); ?>
		
		<div class="form-action">
			<?php wp_nonce_field( 'yaexam-reset-password' ); ?>
			<input type="submit" class="em-btn em-btn-primary" name="lost_password" value="<?php esc_attr_e( 'Reset Password', 'yaexam' ); ?>" />
			<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
		</div>
		
	</form>
</div>