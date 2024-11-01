
<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<?php if(yaexam_is_doing()): 
	
	do_action( 'yaexam_exam_doing' ); ?>

<?php elseif(is_doing_form()):
	
	yaexam_get_template('content-fillform.php'); ?>
	
<?php elseif(yaexam_is_result()):

	yaexam_get_template('content-single-exam-result.php'); ?>
	
<?php else: ?>
	<div class="em-row">
		<?php do_action( 'yaexam_left_sidebar' ); ?>
			
		<?php

			do_action( 'yaexam_before_single_exam' );

			if ( post_password_required() ) {
				echo get_the_password_form();
				return;
			}
		?>

		<div id="exam-<?php the_ID(); ?>" <?php yaexam_container_class('single-exam'); ?>>
			<div class="em-container-inner">
				<div class="yaexam-single-exam-summary">
					<?php do_action( 'yaexam_single_exam_summary' ); ?>
				</div>
			</div>
		</div>

		<?php do_action( 'yaexam_after_single_exam' ); ?>

		<?php do_action( 'yaexam_right_sidebar' ); ?>
	 </div>
<?php endif; ?>