<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php foreach( $exam_info as $info ): ?>
	<?php if( $info['type'] == 'text' ): ?>

		<div class="gd">
			<div class="gd-label"><?php echo esc_html($info['label']); ?></div>
			<div class="gd-value"><?php echo esc_html($info['value']); ?></div>
		</div>
	
	<?php elseif( $info['type'] == 'recaptcha' ): ?>
	
		<div class="gd center">
			<div class="g-recaptcha" data-SiteKey="<?php echo esc_attr($info['value']); ?>"> </div>
		</div>
		
	<?php endif; ?>
<?php endforeach; ?>