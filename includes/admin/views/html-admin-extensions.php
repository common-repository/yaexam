<?php
/**
 * Admin View: Extensions
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$section = 'premium';

if(isset($_GET['section']) && $_GET['section'] == 'free') {

	$section = 'free';
}

?>

<div class="wrap" id="em-extensions-wrap">

	<h1><?php esc_html_e('Extensions', 'yaexam'); ?></h1>

	<br class="clear"/>

	<div class="wp-list-table widefat extensions-install">
		
	<div id="the-list">

		<?php if( $extensions ): ?>
		
		<?php foreach( $extensions as $extension ): ?>

			<?php if( $section == 'premium' && $extension['price'] > 0 ): ?>
		<div class="plugin-card plugin-card-theme-check">
			
			<div class="plugin-card-top">
				
				<div class="name column-name">
					<h3>
						<a href="<?php echo esc_url($extension['detail']); ?>" class="thickbox open-plugin-details-modal">
						<?php echo esc_html($extension['name']); ?>				
						<img src="<?php echo esc_html($extension['thumbnail']); ?>" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>

				<div class="desc column-description">
					
					<?php echo esc_html($extension['description']); ?>

					<div class="amount"><span class="price"><?php echo esc_html_e('From', 'yaexam'); ?> $<?php echo esc_html($extension['price']); ?></span></div>

				</div>

			</div>

			<div class="plugin-card-bottom">
				<?php if($extension['status'] == 1): ?>

				<a class="button" href="<?php echo esc_url($extension['active']); ?>"><?php esc_html_e('Active Plugin', 'yaexam'); ?></a>
				
				<?php elseif($extension['status'] == 2): ?>

				<a class="button" href="<?php echo esc_url($extension['deactive']); ?>"><?php esc_html_e('Deactivate Plugin', 'yaexam'); ?></a>

				<?php else: ?>

				<a class="button" href="<?php echo esc_url($extension['detail']); ?>" target="blank"><?php esc_html_e('Download Plugin', 'yaexam'); ?></a>

				<?php endif; ?>

				<a class="button" href="<?php echo $extension['demo']; ?>" target="blank"><?php _e('Demo', 'yaexam'); ?></a>

			</div>

		</div>
			<?php endif; ?>

			<?php if( $section == 'free' && $extension['price'] == 0 ): ?>
		<div class="plugin-card plugin-card-theme-check">
			
			<div class="plugin-card-top">
				
				<div class="name column-name">
					<h3>
						<a href="<?php echo esc_url($extension['detail']); ?>" class="thickbox open-plugin-details-modal">
						<?php echo $extension['name']; ?>				
						<img src="<?php echo esc_url($extension['thumbnail']); ?>" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>

				<div class="desc column-description">
					
					<?php echo esc_html($extension['description']); ?>

				</div>

			</div>

			<div class="plugin-card-bottom">
				<?php if($extension['status'] == 1): ?>

				<a class="button" href="<?php echo admin_url('plugins.php'); ?>"><?php _e('Active Plugin', 'yaexam'); ?></a>
				
				<?php elseif($extension['status'] == 2): ?>

				<a class="button" href="<?php echo admin_url('plugins.php'); ?>"><?php _e('Deactivate Plugin', 'yaexam'); ?></a>

				<?php else: ?>

				<a class="button" href="<?php echo $extension['detail']; ?>" target="blank"><?php _e('Download Plugin', 'yaexam'); ?></a>
				
				<?php endif; ?>

				<a class="button" href="<?php echo $extension['demo']; ?>" target="blank"><?php _e('Demo', 'yaexam'); ?></a>

			</div>

		</div>
			<?php endif; ?>

		<?php endforeach; ?>

		<?php endif; ?>

	</div>

	<br class="clear"/>

	<div class="em-txt-center">
		
		<a href="https://yaexam.com/item-category/add-ons/" target="blank" id="em-addmore-extensions">

			<i class="material-icons">add_circle</i>

			<?php esc_html_e('Add more', 'yaexam') ?>
				
		</a>

	</div>

	</div>
</div>
