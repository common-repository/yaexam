<?php

$results		=	yaexam_get_lastest_results(array('limit' => 20));
$tests			=	yaexam_get_exams( array( 'numberposts' => 10, 'orderby' => 'date', 'order' => 'DESC') );

?>
<div class="wrap">
	
	<h1><?php esc_html_e('Dashboard', 'yaexam'); ?></h1>

	<div class="em-row em-dashboard-section">
		
		<div class="em-col-md-4">
			<div class="em-info-box-1-3">
				<h2 class="title"><?php esc_html_e('Total Exams', 'yaexam'); ?></h2>
				<div class="value">
					<?php echo yaexam_get_total_tests(); ?>
				</div>
			</div>
		</div>
	
		<div class="em-col-md-8">
			<div class="em-info-box-1-1">
				<h2 class="title"><?php esc_html_e('Total Questions', 'yaexam'); ?></h2>
				<div class="value">
					<?php echo yaexam_get_total_questions(); ?>
				</div>
			</div>
		</div>
	
	</div>

	<div class="em-row em-dashboard-section">
		
		<div class="em-col-md-4">
			
			<div class="em-info-box-2">
				<h2 class="title"><?php esc_html_e('New Exams', 'yaexam'); ?></h2>
				<div class="body">
					<table cellpadding="0" cellspacing="0">
						<thead>
							<tr>
								<th class="em-a-c em-s-1">#</th>
								<th><?php esc_html_e('Title', 'yaexam'); ?></th>
								<th class="em-s-5"><?php esc_html_e('Date', 'yaexam'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if($tests): ?>
								<?php foreach($tests as $key => $t): ?>
							<tr>
								<td class="em-a-c"><?php echo $key + 1; ?></td>
								<td><a href="<?php echo admin_url('post.php?action=edit&post=' . $t->ID); ?>"><?php echo $t->post_title; ?></a></td>
								<td><?php echo $t->post_date; ?></td>
							</tr>
								<?php endforeach; ?>
							<?php else: ?>
							<tr>
								<td colspan="4"  class="em-no-data"><?php esc_html_e('NO DATA', 'yaexam'); ?></td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
			
		</div>
		
		<div class="em-col-md-8">
			
			<div class="em-info-box-2">
				<h2 class="title"><?php esc_html_e('Lastest Results', 'yaexam'); ?></h2>
				<div class="body">
					<table cellpadding="0" cellspacing="0">
						<thead>
							<tr>
								<th class="em-a-c em-s-1">#</th>
								<th class="em-s-4"><?php esc_html_e('User', 'yaexam'); ?></th>
								<th class="em-s-1"><?php esc_html_e('Score', 'yaexam'); ?></th>
								<th class="em-s-4"><?php esc_html_e('Duration', 'yaexam'); ?></th>
								<th><?php esc_html_e('Tests', 'yaexam'); ?></th>
								<th class="em-s-5"><?php esc_html_e('Date', 'yaexam'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php if($results): ?>
								<?php foreach($results as $key => $result): ?>
							<tr>
								<td class="em-a-c"><?php echo $key + 1; ?></td>
								<td><?php echo $result['user_login']; ?></td>
								<td><?php echo $result['score'] . '/' . $result['total_score']; ?></td>
								<td><?php echo $result['duration']; ?></td>
								<td><a href="<?php echo $result['exam_admin_link']; ?>"><?php echo $result['exam_title']; ?></a></td>
								<td><?php echo $result['date_start']; ?></td>
							</tr>
								<?php endforeach; ?>
								<?php else: ?>
							<tr>
								<td colspan="6" class="em-no-data"><?php esc_html_e('NO DATA', 'yaexam'); ?></td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
			
		</div>
		
	</div>
</div>