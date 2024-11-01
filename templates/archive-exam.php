<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'yaexam' ); ?>

	<?php do_action( 'yaexam_before_main_content' ); ?>

		<div class="em-row">
			<?php do_action( 'yaexam_left_sidebar' ); ?>
			
			<?php if ( have_posts() ) : ?>
				<div <?php yaexam_container_class(); ?>>

					<?php echo apply_filters( 'yaexam_wrap_before_nav', '' ); ?>

					<?php do_action( 'yaexam_before_exam_loop' ); ?>

					<?php echo apply_filters( 'yaexam_wrap_after_nav', '' ); ?>
				
				<?php yaexam_exam_loop_start(); ?>
					
					<?php while ( have_posts() ) : the_post(); ?>

						<?php yaexam_get_template_part( 'content', 'exam' ); ?>
						
					<?php endwhile; ?>
					
				<?php yaexam_exam_loop_end(); ?>
					
				<?php do_action( 'yaexam_after_exam_loop' ); ?>
				</div>
			<?php endif; ?>
			
			<?php do_action( 'yaexam_right_sidebar' ); ?>

		</div>

		
	<?php do_action( 'yaexam_after_main_content' ); ?>

<?php get_footer( 'yaexam' ); ?>