<?php
/**
 * Admin View: Notice - Updating
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated yaexam-message qm-connect">
	<p><strong><?php esc_html_e( 'YaExam Data Update', 'yaexam' ); ?></strong> &#8211; <?php esc_html_e( 'Your database is being updated in the background.', 'yaexam' ); ?> <a href="<?php echo esc_url( add_query_arg( 'force_update_yaexam', 'true', admin_url( 'admin.php?page=em-settings' ) ) ); ?>"><?php esc_html_e( 'Taking a while? Click here to run it now.', 'yaexam' ); ?></a></p>
</div>
