<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<a href="<?php echo $view_results_link; ?>" class="em-list-group-item em-list-group-item-action<?php echo ($active == 'view-results') ? ' active':''; ?>">
	<?php esc_html_e('Results', 'yaexam'); ?>
</a>