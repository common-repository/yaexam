<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'quiz' ); ?>
	
	<?php do_action( 'yaexam_before_main_content' ); ?>
	
	<div class="em-container">
	<?php while ( have_posts() ) : the_post(); ?>

		<?php yaexam_get_template_part( 'content', 'single-exam' ); ?>

	<?php endwhile; // end of the loop. ?>

		<?php do_action( 'yaexam_after_main_content' ); ?>
	</div>
	

<?php get_footer( 'quiz' ); ?>