<?php
/**
 * Admin View: Notice - Update
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated yaexam-message em-connect">
	<p><strong><?php esc_html_e( 'YaExam Data Update', 'yaexam' ); ?></strong> &#8211; <?php _e( 'We need to update your database to the latest version.', 'yaexam' ); ?></p>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_yaexam', 'true', admin_url( 'admin.php?page=em-settings' ) ) ); ?>" class="em-update-now button-primary"><?php _e( 'Run the updater', 'yaexam' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery( '.em-update-now' ).click( 'click', function() {
		return window.confirm( '<?php echo esc_js( __( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'yaexam' ) ); ?>' ); // jshint ignore:line
	});
</script>
