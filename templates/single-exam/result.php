<?php  

if ( ! defined( 'ABSPATH' ) ) { exit; }

?>

<div class="em-row" id="em-result" data-pages="<?php echo count($questions); ?>">
	
	<div id="em-doing_container" class="em-col-lg-8">

		<h1><?php the_title(); ?></h1>

		<?php do_action('yaexam_before_result_content', $result); ?>

		<?php if( $settings['show_ranking'] == 'yes' ): ?>
		<div class="em-mb-3">
			<div class="em-card">
				<div class="em-card-body">
				<h3 class="em-text-center"><?php esc_html_e('Your Ranking', 'yaexam') ?>: <span class="user_ranking">{{user_ranking.num}}</span>/<span class="total_ranking">{{total_ranking}}</span></h3>
				<table class="table table-striped">
					<thead>
						<tr>
						<th scope="col">#</th>
						<th scope="col"><?php esc_html_e('Name', 'yaexam') ?></th>
						<th scope="col"><?php esc_html_e('Score', 'yaexam') ?></th>
						<th scope="col"><?php esc_html_e('Duration', 'yaexam') ?></th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(r, index) in rankings" :key="r.id" :class="{'em-text-white': (user_ranking.user_id == r.user_id)}">
							<th scope="row" :class="{'bg-success': (user_ranking.user_id == r.user_id)}">{{index+1}}</th>
							<td :class="{'bg-success': (user_ranking.user_id == r.user_id)}">{{r.user_name}}</td>
							<td :class="{'bg-success': (user_ranking.user_id == r.user_id)}">{{r.score}}</td>
							<td :class="{'bg-success': (user_ranking.user_id == r.user_id)}">{{r.duration}}</td>
						</tr>
					</tbody>
				</table>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if( $settings['show_result'] == 'yes' ): ?>
			<div id="em-doing_body" class="em-card">

				<div id="em-doing_body_header">
					
					<div id="em-doing_body_header_info"><?php esc_html_e('Question', 'yaexam') ?> {{page}} of {{pages}}</div>

				</div>

				<div id="em-doing_body_content_container">

					<div id="em-doing_body_content">

						<?php foreach( $questions as $index => $q ): 

							$question = $q['question_data'];
							
						?>
							<div :class="{active: page == <?php echo $index + 1; ?>}" class="em-question <?php echo $question['answer_type']; ?>">

								<div class="em-question-content"><?php echo $question['content']; ?></div>

							<?php if($question['answer_type'] == 'single'): ?>

								<div class="em-answers em-single-answers">
			
									<?php if( $question['answers'] ): ?>

										<div class="em-row">

										<?php foreach( $question['answers'] as $answer ): 

											$is_user_answer = $answer['id'] == absint($q['question_answered']);
										?>

											<div class="em-col-sm-6">

												<div class="em-d-flex em-align-items-center em-answer <?php echo $answer['is_correct'] == 1 ? 'q_correct' : ''; ?> <?php echo $is_user_answer ? 'u_answered' : ''; ?>">
													
													<?php if($is_user_answer || $answer['is_correct'] == 1): ?>

													<span class="em-answer-check-icon">
														<i class="material-icons">radio_button_checked</i>
													</span>

													<?php else: ?>

													<span class="em-answer-check-icon">
														<i class="material-icons">radio_button_unchecked</i>
													</span>

													<?php endif; ?>
													
													<div class="em-ml-3 em-answer-content"><?php echo $answer['content']; ?></div>

												</div>

											</div>

										<?php endforeach; ?>

										</div>

									<?php endif; ?>

								</div>
							
							<?php elseif($question['answer_type'] == 'multiple'):?>

								<div class="em-answers em-multiple-answers">
			
									<?php if( $question['answers'] ): ?>

										<div class="em-row">

										<?php foreach( $question['answers'] as $answer ): 

											$is_user_answer = is_array($q['question_answered']) ? in_array($answer['id'], $q['question_answered']) : false;
										?>

											<div class="em-col-sm-6">

												<div class="em-d-flex em-align-items-center em-answer <?php echo $answer['is_correct'] == 1 ? 'q_correct' : ''; ?> <?php echo $is_user_answer ? 'u_answered' : ''; ?>">
													
													<?php if($is_user_answer || $answer['is_correct'] == 1): ?>

													<span class="em-answer-check-icon">
														<i class="material-icons">check_box</i>
													</span>

													<?php else: ?>

													<span class="em-answer-check-icon">
														<i class="material-icons">check_box_outline_blank</i>
													</span>

													<?php endif ?>

													<div class="em-ml-3 em-answer-content"><?php echo $answer['content']; ?></div>

												</div>

											</div>

										<?php endforeach; ?>

										</div>

									<?php endif; ?>

								</div>
							
							<?php endif; ?>

							</div>

						<?php endforeach; ?>

						

					</div>

				</div>

				<div id="em-doing_body_actions">

					<div v-if="page > 1" class="em-mr-2 em-btn em-btn-primary" @click="prevPage"><?php esc_html_e('PREVIOUS', 'yaexam') ?></div>
					<div v-if="pages > 1 && !isLastPage" class="em-btn em-btn-primary" @click="nextPage"><?php esc_html_e('NEXT', 'yaexam') ?></div>
					<a href="<?php the_permalink() ?>" class="em-ml-3 em-btn em-btn-danger"><?php esc_html_e('PLAY AGAIN', 'yaexam') ?></a>

				</div>

			</div>
		<?php else: ?>
			
		<?php endif; ?>

	</div>

	<div id="em-doing_sidebar" class="em-col-lg-4">
			
			<?php do_action('yaexam_before_result_sidebar', $result_id); ?>

			<div class="em-sidebar_section em-sidebar_section__result_score">

				<div class="em-result_score">

					<h3 class="em-result_score_title"><?php esc_html_e('Your Score', 'yaexam') ?></h3>

					<div class="em-result_score_value"><span><?php echo $score ?></span>/<span><?php echo $total_score ?></span></div>
					
				</div>

			</div>

			<div class="em-sidebar_section em-sidebar_section__timer">
				
				<div id="em-doing_sidebar_timer" data-duration="<?php echo $date_end - $date_start; ?>">

					<span class="time-value hour">{{display_timer[0]}}</span>
	                <span class="time-text">:</span>
	                <span class="time-value minute">{{display_timer[1]}}</span>
	                <span class="time-text">:</span>
	                <span class="time-value seconds">{{display_timer[2]}}</span>

				</div>

			</div>

			<?php if($exam_duration): ?>
			<div class="em-sidebar_section em-sidebar_section__result_info em-sidebar_section__duration">

				<div class="em-doing_sidebar_title em-doing_sidebar_title__span">
					<div class="em-sidebar_section_label"><?php esc_html_e('Time', 'yaexam') ?></div>
					<div class="em-sidebar_section_value" id="em-doing_sidebar_duration" data-duration="<?php echo $exam_duration; ?>">
						<span class="time-value hour">{{display_duration[0]}}</span>
						<span class="time-text">:</span>
						<span class="time-value minute">{{display_duration[1]}}</span>
						<span class="time-text">:</span>
						<span class="time-value seconds">{{display_duration[2]}}</span>
					</div>
				</div>

			</div>
			<?php endif; ?>
			
			<?php do_action( 'yaexam_exam_result_info', $result ) ?>
			
			<?php if( $settings['show_result'] == 'yes' ): ?>
			<div class="em-sidebar_section em-sidebar_section__tracking">

				<div id="em-doing_sidebar_tracking">
					
					<?php foreach( $questions as $index => $question ): ?>
					<span @click="toPage(<?php echo $index + 1; ?>)" :class="{active:page == <?php echo $index + 1; ?>}"><?php echo $index + 1; ?></span>
					<?php endforeach; ?>

				</div>

			</div>
			<?php endif; ?>

			<div class="em-sidebar_section em-sidebar_section__summary em-mt-12">
				
				<div class="em-doing_sidebar_title"><?php esc_html__('Summary', 'yaexam'); ?></div>

				<div id="em-doing_sidebar_summary">
					
						<div class="em-summary em-summary__answered">
							<div class="em-summary_value"><?php echo $corrects; ?></div>
							<div class="em-summary_label"><?php esc_html_e('Correct', 'yaexam') ?></div>
						</div>

						<div class="em-summary em-summary__notanswered">
							<div class="em-summary_value"><?php echo $wrongs; ?></div>
							<div class="em-summary_label"><?php esc_html_e('Wrong', 'yaexam') ?></div>
						</div>

						<div class="em-summary em-summary__notvisited">
							<div class="em-summary_value"><?php echo $notanswereds; ?></div>
							<div class="em-summary_label"><?php esc_html_e('Not Visited', 'yaexam') ?></div>
						</div>


				</div>

			</div>
			
	</div>
	
</div>