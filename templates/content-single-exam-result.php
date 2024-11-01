<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

?>

<?php do_action( 'yaexam_before_single_exam_result' ); ?>

<div class="em-col-xs-12 em-col-sm-12" id="exam-<?php the_ID(); ?>-result">
	
	<?php
		/**
		 * quizmaker_single_test_result_summary hook.
		 *
		 */
		do_action( 'yaexam_single_exam_result_summary' );
	?>

</div>

<?php do_action( 'yaexam_after_single_exam_result' ); ?>