
<?php 

$columns	=	isset($params['columns']) && $params['columns'] > 0 ? absint($params['columns']) : 2;

 ?>

<div v-show="edit_question_answer_type == 'single'" class="answer-type-panel" id="answer-type-single">

	<div class="answer-settings">
		
		<div class="group-field">
			<label><?php _e('Columns', 'yaexam'); ?>:</label>
			
			<select name="single_params[columns]">
				<?php foreach( array(1, 2, 3, 4, 6, 12) as $col ): ?>
				<option value="<?php echo esc_attr($col); ?>" <?php selected($columns, $col); ?>><?php echo esc_html($col); ?></option>
				<?php endforeach; ?>
			</select>


		</div>

	</div>

	<table class="widefat wp-list-table answers-box em-mt-12" cellspacing="0">
		<thead>
			<tr>
				<th class="em-w-50 is-correct"><?php _e('Correct', 'yaexam'); ?></th>
				<th><?php esc_html_e('Content', 'yaexam'); ?></th>
				<th class="em-w-50"></th>
			</tr>
		</thead>
		<tbody>
			<?php if( $answers && $answer_type == 'single'): ?>
				<?php foreach($answers as $index => $ans):
						
					$selected	=	$ans['is_correct'] == 1 ? ' checked':'';
					$image_tag	=	yaexam_image_tag($ans['image'], 'thumbnail', false);

				?>
					<tr>
						<td class="em-txt-center">
							<input type="radio" value="<?php echo esc_attr($index); ?>" class="ir-is-correct" name="answers_single_is-correct" <?php echo $selected; ?> />
						</td>
						<td>
							<div class="qm-answer-info">
								<div class="position-relative">
									<?php echo $image_tag ? '<span class="qm-answer-remove-image"><i class="material-icons">cancel</i></span>' : ''; ?>
									<span class="qm-answer-image" data-name="answers_single[<?php echo $index; ?>][image]">
										<?php echo $image_tag; ?>
										<input type="hidden" name="answers_single[<?php echo $index; ?>][image]" value="<?php echo esc_attr($ans['image']); ?>"/>
									</span>
								</div>
							</div>
							<div class="qm-answer-desc">
								<textarea name="answers_single[<?php echo $index; ?>][content]" class="qm-s-wide qm-answer-desc__editor"><?php echo $ans['content']; ?></textarea>
							</div>
						</td>
						<td class="em-txt-center"><button class="qm-remove"><i class="material-icons">cancel</i></button></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; //End answers  ?>

		</tbody>
		<tfoot>
			<tr>
				<td colspan="3" class="em-txt-center">
					<a @click.prevent="new_single_question" class="button-new-table" id="add-new-single-answer" href="#answers"><?php esc_html_e('Add answer', 'yaexam'); ?></a>
				</td>
			</tr>
		</tfoot>
	</table>

	<div class="em-mt-12 actions">
		<input type="hidden" name="id" value="<?php echo esc_attr($id); ?>">
	</div>
</div>