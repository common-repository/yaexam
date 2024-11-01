<?php
/**
 * Admin View: Notice - Updated
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="updated yaexam-message em-connect">
	<a class="yaexam-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'em-hide-notice', 'update', remove_query_arg( 'do_update_yaexam' ) ), 'yaexam_hide_notices_nonce', '_em_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'yaexam' ); ?></a>

	<p><?php esc_html_e( 'YaExam data update complete. Thank you for updating to the latest version!', 'yaexam' ); ?></p>
</div>
