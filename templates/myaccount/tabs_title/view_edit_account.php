<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<a href="<?php echo $view_edit_account_link; ?>" class="em-list-group-item em-list-group-item-action<?php echo ($active == 'view-edit-account') ? ' active':''; ?>">
	<?php esc_html_e('Edit Account', 'yaexam'); ?>
</a>