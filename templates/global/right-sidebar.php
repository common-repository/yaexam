<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php if(is_active_sidebar('yaexam-right-sidebar')): ?>
<div class="em-col-md-3 em-right-sidebar">
	<?php dynamic_sidebar( 'yaexam-right-sidebar' ); ?>
</div>
<?php endif; ?>