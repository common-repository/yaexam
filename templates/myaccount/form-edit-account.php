<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div id="yaexam-myaccount-form-edit-account" class="container-myaccount-edit-account">
	
<?php do_action( 'yaexam_before_my_account' ); ?>

<form class="edit-account" action="" method="post">

	<?php do_action( 'yaexam_edit_account_form_start' ); ?>

	<div class="em-form-group">
		<label for="account_first_name" class="col-form-label"><?php esc_html_e( 'First name', 'yaexam' ); ?> <span class="required">*</span></label>
		<div class="col-form-input">

		  <input type="text" class="form-control" name="account_first_name" id="account_first_name" value="<?php echo esc_attr( $user->first_name ); ?>">

		</div>
	</div>

	<div class="em-form-group">
		<label for="account_last_name" class="col-form-label"><?php esc_html_e( 'Last name', 'yaexam' ); ?> <span class="required">*</span></label>
		<div class="col-form-input">

		  <input type="text" class="form-control" name="account_last_name" id="account_last_name" value="<?php echo esc_attr( $user->last_name ); ?>">

		</div>
	</div>

	<div class="em-form-group">
		<label for="account_email" class="col-form-label"><?php esc_html_e( 'Email', 'yaexam' ); ?> <span class="required">*</span></label>
		<div class="col-form-input">

		  <input type="email" class="form-control" name="account_email" id="account_email" value="<?php echo esc_attr( $user->user_email ); ?>">

		</div>
	</div>

	<div class="em-form-group">
		<label for="account_username" class="col-form-label"><?php esc_html_e( 'Username', 'yaexam' ); ?> <span class="required">*</span></label>
		<div class="col-form-input">

		  <input type="text" class="form-control" name="account_username" id="account_username" value="<?php echo esc_attr( $user->user_login ); ?>">

		</div>
	</div>

	<div class="em-form-group">
		<label for="em_password_1" class="col-form-label"><?php esc_html_e( 'New Password', 'yaexam' ); ?></label>
		<div class="col-form-input">

		  <input type="password" class="form-control" name="password_1" id="em_password_1">
		  <small id="password_1Help" class="form-text text-muted"><?php esc_html_e('Leave blank to leave unchanged', 'yaexam'); ?></small>
		</div>
	</div>

	<div class="em-form-group">
		<label for="em_password_2" class="col-form-label"><?php esc_html_e( 'Confirm New Password', 'yaexam' ); ?></label>
		<div class="col-form-input">

		  <input type="password" class="form-control" name="password_2" id="em_password_2">

		</div>
	</div>

	<?php do_action( 'yaexam_edit_account_form_end' ); ?>
		
	<?php wp_nonce_field( 'yaexam_save_account_details' ); ?>
	<button type="submit" class="em-btn-submit"><?php esc_attr_e( 'Save changes', 'yaexam' ); ?></button>
	<input type="hidden" name="action" value="yaexam_save_account_details" />
	<?php if(isset($_GET['redirect'])): ?>
	<input type="hidden" name="redirect" value="<?php echo wp_sanitize_redirect(wp_unslash($_GET['redirect'])); ?>"/>
	<?php endif; ?>

</form>

	<?php do_action( 'yaexam_after_my_account' ); ?>
</div>