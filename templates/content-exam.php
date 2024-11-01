<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

$has_thumbnail	=	yaexam_get_exam_thumbnail() ? true : false;

?>

<div class="em-col-sm-6 em-col-lg-4 exam">
	<div class="em-card">
	<?php
	/**
	 * yaexam_before_exam_loop_item hook.
	 *
	 */
	do_action( 'yaexam_before_exam_loop_item' );

	/**
	 * yaexam_exam_loop_item_summary hook.
	 *
	 */
	do_action( 'yaexam_exam_loop_item_summary' );

	/**
	 * yaexam_after_exam_loop_item hook.
	 *
	 */
	do_action( 'yaexam_after_exam_loop_item' );
	?>
	</div>
</div>