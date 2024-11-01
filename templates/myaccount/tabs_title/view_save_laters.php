<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<a href="<?php echo $view_save_laters_link; ?>" class="em-list-group-item em-list-group-item-action<?php echo ($active == 'view-save-laters') ? ' active':''; ?>">
	<?php esc_html_e('Save Later', 'yaexam'); ?>
</a>