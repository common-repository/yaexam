<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>


<div id="em-myaccount">
	
		<div class="container">
			<div class="em-row">

				<div class="em-col-lg-3 em-tabs-title-container">

					<?php do_action( 'yaexam_my_account_before_sidebar', $args ); ?>

					<div class="em-list-group">
						
						<?php apply_filters( 'yaexam_my_account_tabs_title', $args ); ?>
					</div>

					<a href="<?php echo wp_logout_url( apply_filters('yaexam_logout_redirect', site_url()) ); ?>" class="em-mt-3 em-mb-3 em-btn em-btn-danger em-btn-sm em-btn-block" id="em-btn-logout"><?php _e('Logout', 'yaexam'); ?></a>

			        

			        <?php do_action( 'yaexam_my_account_after_sidebar', $args ); ?>

				</div>
				<div class="em-col-lg-9 em-tabs-panel-container">
					<?php yaexam_print_messages(); ?>