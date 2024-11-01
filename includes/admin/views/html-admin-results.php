
<div id="em-admin-result" class="wrap">

	<h1 class="wp-heading-inline"><?php esc_html_e('Results', 'yaexam') ?></h1>

	<hr class="wp-header-end">

	<form class="em-mt-12 posts-filter" method="get">

		<div class="tablenav top">

			<?php yaexam_html_select_exams( $exam ); ?>
				
			<button type="submit" name="action" value="filter" class="button"><?php esc_html_e('Filter', 'yaexam') ?></button>
			
		</div>

		<table class="wp-list-table em-mt-12 widefat fixed striped posts">
			<thead>
				<tr>
					
					<th scope="col" id="user" class="em-w-150"><?php esc_html_e('User', 'yaexam') ?></th>

					<th scope="col" id="exam" class="manage-column column-category"><?php esc_html_e('Exam', 'yaexam') ?></th>
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
				<?php foreach($results as $index => $result): ?>
				<tr class="iedit author-self level-0 status-publish hentry">
					 
					<td>
						<a href="<?php echo admin_url('edit.php?post_type=exam&page=em-results&id=' . absint($result['exam_id']) . '&user_id=' . absint($result['user_id'])) ?>"><strong><?php echo esc_html($result['user_name']); ?></strong></a>
							
					</td>
					<td>
						<?php foreach($exams as $e): ?>

						<?php if( $e->ID == $result['exam_id'] ): ?>
							
							<?php echo esc_html($e->post_title); ?>

						<?php endif; ?>

						<?php endforeach; ?>
					</td>
					<td><?php echo yeaxam_clean(wp_unslash($result['score'])); ?></td>
					<td><?php echo yeaxam_clean(wp_unslash($result['total_corrects'])); ?></td>
					<td><?php echo yeaxam_clean(wp_unslash($result['total_wrongs'])); ?></td>
					<td><?php echo yeaxam_clean(wp_unslash($result['total_notanswereds'])); ?></td>
					<td><?php echo yeaxam_clean(wp_unslash($result['date_start'])); ?></td>
					<td><?php echo yeaxam_clean(wp_unslash($result['date_end'])); ?></td>
					<td><a href="<?php echo admin_url('user-edit.php?user_id=' . absint($result['user_id'])) ?>"><?php esc_html_e('Profile', 'yaexam') ?></a></td>
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
	</form>

</div>