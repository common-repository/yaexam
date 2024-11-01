<div id="em-admin-question" class="wrap">

	<nav class="em-mb-12 nav-tab-wrapper">
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions')) ?>" class="nav-tab nav-tab-active"><?php esc_html_e('Questions', 'yaexam') ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=categories')) ?>" class="nav-tab"><?php esc_html_e('Categories', 'yaexam') ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=import')) ?>" class="nav-tab"><?php esc_html_e('Importing', 'yaexam') ?></a>

	</nav>

	<h1 class="wp-heading-inline"><?php esc_html_e('Edit Question', 'yaexam'); ?></h1>

	<form class="em-mt-12" method="post">

		<div class="em-row">
			
			<div class="em-col-sm-9">
				
				<div>

					<input class="em-title-input" type="text" name="title" size="30" value="<?php echo $question['title'] ?>" id="title" spellcheck="true" autocomplete="off">
					
					<?php wp_editor($question['content'], 'content'); ?>

					<?php 

						$meta_type_box	=	'<label for="question-type"><select id="answer-type" name="answer_type" v-model="edit_question_answer_type"><optgroup label="' . esc_attr__( 'Answer Type', 'yaexam' ) . '">';
								
						foreach ( $answer_types as $value => $label ) {
							$meta_type_box .= '<option value="' . esc_attr( $value ) . '" ' . selected( $question['answer_type'], $value, false ) .'>' . esc_html( $label ) . '</option>';
						}

						$meta_type_box .= '</optgroup></select></label>';

					 ?>

					<div class="em-mt-24">
						
						<?php include('html-admin-answer-data-single-tab.php'); ?>
						
						<?php include('html-admin-answer-data-multiple-tab.php'); ?>
						
						<?php do_action('yaexam_admin_question_content'); ?>

					</div>

				</div>

			</div>

			<div class="em-col-sm-3">
				
				<div class="postbox em-postbox">
					
					<h2 class="hndle"><span><?php esc_html_e('Publish', 'yaexam') ?></span></h2>

					<div class="inside">
						
						<ul class="em-mb-0">

							<li class="em-box-input-select em-mb-12 em-d-none">
								<?php echo $meta_type_box; ?>
							</li>

							<li class="em-box-input-select em-mb-12">
								<label><?php esc_html_e('Category', 'yaexam') ?></label>
								<?php yaexam_html_select_categories((isset($question['category_id']) ? absint($question['category_id']) : 0)) ?>
							</li>

							<li class="em-box-input-text em-mb-12">
								<label><?php esc_html_e('Score', 'yaexam') ?></label>
								<input type="number" min="0" name="score" size="30" value="<?php echo isset($question['score']) ? esc_attr($question['score']) : 1 ?>" id="score" autocomplete="off" placeholder="Points">
							</li>

							<li class="em-box-input-text em-mb-12">
								<label for="video_audio"><?php esc_html_e('Video, Audio URL', 'yaexam') ?></label>
								<input type="text" @click.prevent="addMedia('video')" name="video" value="<?php echo isset($question['video']) ? esc_url($question['video']) : '' ?>" id="video_audio" autocomplete="off" placeholder="<?php esc_html_e('Add video', 'yaexam') ?>">
							</li>

							<li class="em-box-input-text em-mb-12">
								<label><?php esc_html_e('Explanation', 'yaexam') ?></label>
								<textarea class="form-control" name="explanation" rows="5" placeholder="Explanation"><?php echo isset($question['explanation']) ? esc_html($question['explanation']) : '' ?></textarea>
							</li>
							
							<li class="em-mt-24 em-d-flex em-justify-content-between">
								<button class="em-btn em-btn-primary"><?php esc_html_e('Update', 'yaexam') ?></button>
								<a class="em-btn em-btn-danger" href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=questions&action=remove&id=' . $question['id'])) ?>"><?php esc_html_e('Move to trash', 'yaexam') ?></a>	
							</li>
							
						</ul>

					</div>
				</div>

			</div>

		</div>

		<input type="hidden" name="tab" value="questions">

	</form>

</div>