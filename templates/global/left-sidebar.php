<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php if(is_active_sidebar('yaexam-left-sidebar')): ?>
<div class="em-col-sm-3 em-left-sidebar">
	<?php dynamic_sidebar( 'yaexam-left-sidebar' ); ?>
</div>
<?php endif; ?>