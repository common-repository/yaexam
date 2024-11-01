<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="action">
	
	<form name="yaexam_start_exam" action="<?php echo esc_url(yaexam_get_start_exam_url($exam_id)); ?>" method="post" enctype="multipart/form-data">
		
		<div class="table-data">

			<?php apply_filters( 'yaexam_single_exam_start_exam_info', $exam_info, $exam_id ); ?>

		</div>

		<?php if( yaexam_can_do_exam($exam_id) ): ?>

			<button type="submit" name="yaexam_start_exam" value="1" class="em-btn-single-start"><?php esc_html_e('START', 'yaexam'); ?></button>

			<?php if(yaexam_has_save_later($exam_id, $user_id)):?>
				<button type="submit" name="yaexam_start_exam_later" value="1" class="em-mt-3 em-btn-single-start"><?php esc_html_e('CONTINUE EXAM', 'yaexam'); ?></button>
			<?php endif; ?>
			
		<?php do_action('yaexam_after_start_exam_template', $exam_id) ?>

		<?php else: ?>
			
			<?php if( $settings['publish_for'] == 1 || !is_user_logged_in()): ?>
				<a href="<?php echo yaexam_get_page_permalink( 'myaccount' ); ?>?redirect=<?php echo urlencode(esc_url(yaexam_get_start_exam_url($exam_id))); ?>" class="em-btn-single-start-login"><?php _e('LOGIN/REGISTER', 'yaexam'); ?></a>
			<?php endif; ?>

			<?php do_action('yaexam_after_not_start_exam_template', $exam_id) ?>
				
		<?php endif; ?>
		
		<input type="hidden" name="id" value="<?php echo esc_attr($exam_id); ?>"/>
		
	</form>
	
</div>
