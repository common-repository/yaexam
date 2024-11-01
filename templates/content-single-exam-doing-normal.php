<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

?>

	<div class="em-row" id="em-doing" data-pages="<?php echo count($user_questions); ?>">

		<?php do_action('yaexam_doing_header'); ?>

		<div class="em-col-md-8" id="em-doing_container">

			<div id="em-doing_body">

				<div id="em-doing_body_header">
					
					<div id="em-doing_body_header_title"><?php the_title(); ?></div>
					<div id="em-doing_body_header_info"><?php esc_html_e('Question', 'yaexam') ?> {{page}} of {{pages}}</div>

				</div>
				
				<form action="<?php the_permalink()?>" method="POST" class="em-doing-form">

					<div id="em-doing_body_content_container">

						<div class="em-loading" v-show="isLoading">
							<img src="<?php echo YAEXAM_URI . '/assets/images/loading.gif?v=1' ?>"/>
						</div>

						<div id="em-doing_body_content">

							<div :class="{'em-invisible': !isLoading}">
							<?php foreach( $user_questions as $index => $user_question ): 

								$question = $user_question['question_data'];	
								$answered = isset($user_question['question_answered']) ? (':answered="' . htmlspecialchars(json_encode(array('value' => $user_question['question_answered']), JSON_FORCE_OBJECT)) . '"') : '';
							?>

								<?php if($question['answer_type'] == 'single'): ?>
									<single-question 
										:class="{active: !isLoading && (page == <?php echo ($index + 1); ?>)}"
										:current_page="page" 
										:page="<?php echo ($index + 1); ?>" 
										:id="<?php echo $question['id']; ?>" 
										:reset="isReset" 
										<?php echo $answered; ?>
										v-on:mounted="questionMounted"
										v-on:loaded="loaded" 
										v-on:changed="changed"></single-question>

								<?php elseif($question['answer_type'] == 'multiple'): ?>
									<multiple-question 
										:class="{active: !isLoading && (page == <?php echo ($index + 1); ?>)}"
										:current_page="page" 
										:page="<?php echo ($index + 1); ?>" 
										:id="<?php echo $question['id']; ?>" 
										:reset="isReset" 
										<?php echo $answered; ?> 
										v-on:loaded="loaded" 
										v-on:changed="changed"></multiple-question>
								<?php endif; ?>

							<?php endforeach; ?>
							</div>
							
						</div>

						<div id="em-doing_body_actions">

							<div v-if="can_prev && (page > 1)" class="em-btn em-btn-primary" @click="prevPage">
								<?php esc_html_e('PREVIOUS', 'yaexam') ?></div>
							<div v-if="!isLastPage" class="em-btn em-btn-primary" @click="nextPage">
								<?php esc_html_e('NEXT', 'yaexam') ?></div>

						</div>

					</div>
					
				</form>

			</div>
			
			<?php do_action('yaexam_doing_footer'); ?>

		</div>

		<div class="em-col-md-4" id="em-doing_sidebar">

			<?php do_action('yaexam_doing_sidebar_header'); ?>

			<div v-if="settings.duration > 0" class="em-sidebar_section em-sidebar_section__timer">
				
				<div id="em-doing_sidebar_timer">

					<span class="time-value hour">{{display_timer[0]}}</span>
					<span class="time-text">:</span>
					<span class="time-value minute">{{display_timer[1]}}</span>
					<span class="time-text">:</span>
					<span class="time-value seconds">{{display_timer[2]}}</span>

				</div>

			</div>

			<div v-if="settings.duration > 0" class="em-sidebar_section em-sidebar_section__duration">

				<div class="em-doing_sidebar_title em-doing_sidebar_title__span">
					<div><?php esc_html_e('Time', 'yaexam') ?></div>
					<div>
						<span class="time-value hour">{{display_duration[0]}}</span>
						<span class="time-text">:</span>
						<span class="time-value minute">{{display_duration[1]}}</span>
						<span class="time-text">:</span>
						<span class="time-value seconds">{{display_duration[2]}}</span>
					</div>
				</div>

			</div>

			<div class="em-sidebar_section em-sidebar_section__summary em-mt-12">
				
				<div class="em-doing_sidebar_title"><?php esc_html_e('Summary', 'yaexam') ?></div>

				<div id="em-doing_sidebar_summary">
					
					<div class="em-summary em-summary__answered">
						<div class="em-summary_value">{{display_total_answered}}</div>
						<div class="em-summary_label"><?php esc_html_e('Answered', 'yaexam') ?></div>
					</div>

					<div class="em-summary em-summary__notanswered">
						<div class="em-summary_value">{{display_total_notanswered}}</div>
						<div class="em-summary_label"><?php esc_html_e('Not Answered', 'yaexam') ?></div>
					</div>

					<div class="em-summary em-summary__notvisited">
						<div class="em-summary_value">{{display_total_notvisited}}</div>
						<div class="em-summary_label"><?php esc_html_e('Not Visited', 'yaexam') ?></div>
					</div>

				</div>

			</div>

			<div v-if="!isFinished && can_submit" class="em-mt-2 em-mb-1 em-btn em-btn-danger em-btn-block" @click="finish"><?php esc_html_e('FINISH', 'yaexam') ?></div>
			
			<?php if($user_session['save_later'] == 'yes'):?>
			<div class="em-mt-2 em-mb-3 em-btn em-btn-info em-btn-block" @click="saveLater"><?php esc_html_e('SAVE LATER', 'yaexam') ?></div>
			<?php endif; ?>

			<div class="em-sidebar_section em-sidebar_section__tracking">

				<div id="em-doing_sidebar_tracking" :class="{backward: settings.is_backward}">
					
					<?php foreach( $user_questions as $index => $question ): ?>
					<span @click="toPage(<?php echo $index + 1; ?>)" :class="tracking_class(<?php echo $index + 1; ?>)"><?php echo $index + 1; ?></span>
					<?php endforeach; ?>

				</div>

			</div>

			<?php do_action('yaexam_doing_sidebar_footer'); ?>

		</div>
		
	</div>

