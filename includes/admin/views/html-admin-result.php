
<div id="em-admin-results" class="wrap">

	<h1 class="wp-heading-inline"><?php esc_html_e('The result of ', 'yaexam') ?> <?php echo $user->display_name; ?>: <?php echo esc_html($exam->get_title()); ?></h1>

	<hr class="wp-header-end">

	<form class="em-mt-12 posts-filter" method="get">

		<?php if(isset($is_updated_attempt)):?>
		<div class="em-alert em-alert-success" role="alert">
			<?php esc_html_e('Update Success!', 'yaexam') ?>
		</div>
		<?php endif;?>
		
		<div class="form-group">
			<label><?php esc_html_e('Attempt', 'yaexam') ?></label>
			<input type="number" class="form-control" name="attempt" value="<?php echo $user_attempts; ?>"/>

			<button type="submit" name="action" value="update_attempt" class="em-mt-3 em-btn em-btn-primary"><?php esc_html_e('Update Attempt', 'yaexam') ?></button>
		</div>

		<div class="tablenav top">

			<button type="submit" name="action" value="remove" class="button"><?php esc_html_e('Remove', 'yaexam') ?></button>

		</div>

		<table class="wp-list-table em-mt-12 widefat fixed striped posts">
			<thead>
				<tr>
					
					<th scope="col" id="cbs" class="em-txt-center em-w-30">
						<input type="checkbox" :value="1" v-model="yaexam_checkall" v-on:change="yaexam_toogle_checkall" class="em-nomargin">
					</th>

					<th scope="col" id="duration" class="manage-column column-title column-primary"><?php esc_html_e('Duration', 'yaexam') ?></th>

					
					<th scope="col" id="score" class="em-w-150"><?php esc_html_e('Score', 'yaexam') ?></th>
					<th scope="col" id="score" class="em-w-150"><?php esc_html_e('Corrects', 'yaexam') ?></th>
					<th scope="col" id="score" class="em-w-150"><?php esc_html_e('Wrongs', 'yaexam') ?></th>
					<th scope="col" id="score" class="em-w-150"><?php esc_html_e('Not Visit', 'yaexam') ?></th>
					<th scope="col" id="time_start" class="em-w-150"><?php esc_html_e('Time Start', 'yaexam') ?></th>
					<th scope="col" id="time_end" class="em-w-150"><?php esc_html_e('Time End', 'yaexam') ?></th>
					<th scope="col" id="id" class="em-w-100"></th>
				</tr>
			</thead>

			
			<tbody id="the-list">
				<?php if( $results ): ?>
				<?php foreach($results as $index => $result):?>
				<tr class="iedit author-self level-0 status-publish hentry">

					<td class="em-txt-center"><input type="checkbox" value="<?php echo yaexam_clean_int($result['id']); ?>" name="yaexam_checkall[]" class="em-nomargin yaexam_select_item"></td>
					 
					<td><?php echo esc_html($result['duration']); ?></td>
					
					<td><?php echo esc_html($result['score']); ?></td>
					<td><?php echo esc_html($result['total_corrects']); ?></td>
					<td><?php echo esc_html($result['total_wrongs']); ?></td>
					<td><?php echo esc_html($result['total_notanswereds']); ?></td>
					<td><?php echo esc_html($result['date_start']); ?></td>
					<td><?php echo esc_html($result['date_end']); ?></td>
					<td><a target="blank" href="<?php echo yaexam_get_endpoint_url( 'em-result', yaexam_clean_int($result['id']), $exam_link ) ?>"><?php esc_html_e('Detail', 'yaexam') ?></a></td>
				</tr>
				<?php endforeach; ?>

				<?php else: ?>

				<tr>
					<td colspan="10" align="center"><?php esc_html_e('No Data', 'yaexam'); ?></td>
				</tr>

				<?php endif; ?>
			</tbody>
			

		</table>

		<input type="hidden" name="post_type" value="exam"/>
		<input type="hidden" name="page" value="em-results"/>
		<input type="hidden" name="id" value="<?php echo yaexam_clean_int($_GET['id']); ?>"/>
		<input type="hidden" name="user_id" value="<?php echo yaexam_clean_int($_GET['user_id']); ?>"/>

	</form>

</div>