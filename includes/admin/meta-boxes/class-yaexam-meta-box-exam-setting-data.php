<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class YAEXAM_Meta_Box_Exam_Setting_Data {
	
	public static function output( $post ) {
		global $post, $thepostid;

		$metadata = get_metadata('post', $post->ID);

		$publish_for 		= isset($metadata['_publish_for']) ? $metadata['_publish_for'][0] : 2;
		$attempt 			= isset($metadata['_attempt']) ? $metadata['_attempt'][0] : 0;
		$duration 			= isset($metadata['_duration']) ? $metadata['_duration'][0] : 0;
		$show_result 		= isset($metadata['_show_result']) ? $metadata['_show_result'][0] : 'yes';
		$show_ranking 		= isset($metadata['_show_ranking']) ? $metadata['_show_ranking'][0] : 'yes';
		$shuffle_questions 	= isset($metadata['_shuffle_questions']) ? $metadata['_shuffle_questions'][0] : 'yes';
		$shuffle_answers 	= isset($metadata['_shuffle_answers']) ? $metadata['_shuffle_answers'][0] : 'yes';
		$save_later 		= isset($metadata['_save_later']) ? $metadata['_save_later'][0] : 'yes';
		$email_user_result 	= isset($metadata['_email_user_result']) ? $metadata['_email_user_result'][0] : 'yes';

		$general_settings = apply_filters('yaexam_admin_exam_general_setting', [
			[
				'id' => 'publish_for', 
				'name' => 'publish_for', 
				'type'	=> 'radiobox',
				'label' => esc_html__('Publish for', 'yaexam'),
				'options' => apply_filters('yaexam_publish_for',[
					['label' => esc_html__('Login Users', 'yaexam'), 'value' => 2],
				]),
				'value' => $publish_for
			],
			['id' => 'duration', 'name' => 'duration', 'label' => esc_html__('Duration', 'yaexam'), 'type' => 'number', 'attrs' => 'min="0"', 'value' => $duration],
			['id' => 'attempt', 'name' => 'attempt', 'label' => esc_html__('Attempt', 'yaexam'), 'type' => 'number', 'attrs' => 'min="0"', 'value' => $attempt],
			['id' => 'save_later', 'name' => 'save_later', 'label' => esc_html__('Save Later', 'yaexam'), 'type' => 'checkbox', 'value' => $save_later],
			['id' => 'email_user_result', 'name' => 'email_user_result', 'label' => esc_html__('Email User Result', 'yaexam'), 'type' => 'checkbox', 'value' => $email_user_result],

		], $post->ID);

		$display_settings = apply_filters('yaexam_admin_exam_display_setting', [
			['id' => 'show_result', 'name' => 'show_result', 'label' => esc_html__('Show Result', 'yaexam'), 'value' => $show_result],
			['id' => 'show_ranking', 'name' => 'show_ranking', 'label' => esc_html__('Show Ranking', 'yaexam'), 'value' => $show_ranking],
			['id' => 'shuffle_questions', 'name' => 'shuffle_questions', 'label' => esc_html__('Shuffle Questions', 'yaexam'), 'value' => $shuffle_questions],
			['id' => 'shuffle_answers', 'name' => 'shuffle_answers', 'label' => esc_html__('Shuffle Answers', 'yaexam'), 'value' => $shuffle_answers],
		], $post->ID);
?>
			
	<div id="meta-box-exam-setting-data">
		
		<ul class="em-nav em-nav-tabs">
			<li class="em-nav-item em-mr-1">
				<a @click.prevent="setActiveTab('general')" class="em-nav-link" :class="{active: activeTab == 'general'}" href="#"><?php esc_html_e('General', 'yaexam') ?></a>
			</li>
			<li class="em-nav-item em-mr-1">
				<a @click.prevent="setActiveTab('display')" class="em-nav-link" :class="{active: activeTab == 'display'}" href="#"><?php esc_html_e('Display', 'yaexam') ?></a>
			</li>
		</ul>

		<table class="table em-mt-3" v-show="activeTab == 'general'">
			<tbody>
				<?php foreach( $general_settings as $setting ){ yaexam_setting_display($setting); } ?>
			</tbody>
		</table>

		<table class="table em-mt-3" v-show="activeTab == 'display'">
			<tbody>
				<?php foreach( $display_settings as $setting ){ yaexam_setting_display($setting); }?>
			</tbody>
		</table>
		
    </div>
			
<?php 
	}
	
	public static function save( $post_id, $post ) {
		
		if( isset($_POST['publish_for']) ){
			$publish_for = absInt($_POST['publish_for']);
		}else{
			$publish_for = 2;
		}

		if( isset($_POST['duration']) ){
			$duration = absInt($_POST['duration']);
		}else{
			$duration	=	0;
		}
		
		if( isset($_POST['attempt']) ){
			$attempt	=	absInt($_POST['attempt']);
		}else{
			$attempt	=	0;
		}
		
		if( isset($_POST['show_result']) ){
			$show_result	=	'yes';
		}else{
			$show_result	=	'no';
		}

		if( isset($_POST['show_ranking']) ){
			$show_ranking	=	'yes';
		}else{
			$show_ranking	=	'no';
		}

		if( isset($_POST['shuffle_questions']) ){
			$shuffle_questions	=	'yes';
		}else{
			$shuffle_questions	=	'no';
		}

		if( isset($_POST['shuffle_answers']) ){
			$shuffle_answers	=	'yes';
		}else{
			$shuffle_answers	=	'no';
		}if( isset($_POST['shuffle_answers']) ){
			$shuffle_answers	=	'yes';
		}else{
			$shuffle_answers	=	'no';
		}

		if( isset($_POST['save_later']) ){
			$save_later	=	'yes';
		}else{
			$save_later	=	'no';
		}

		if( isset($_POST['email_user_result']) ){
			$email_user_result	=	'yes';
		}else{
			$email_user_result	=	'no';
		}

		yaexam_update_post_meta( $post_id, apply_filters( 'yaexam_admin_save_exam_setting', array( 
			'attempt'			=>	$attempt,
			'duration' 			=>	$duration,
			'publish_for'		=>	$publish_for,
			'show_result'		=>	$show_result,
			'show_ranking'		=>	$show_ranking,
			'shuffle_questions'	=>	$shuffle_questions,
			'shuffle_answers'	=>	$shuffle_answers,
			'save_later'		=>	$save_later,
			'email_user_result' =>  $email_user_result,
			'exam_type'			=>	'normal',
		) ) );
	}
	
}