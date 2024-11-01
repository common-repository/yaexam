<?php

use YaExam\YAEXAM_Exam;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="em-my-account-table__view-results" class="container-myaccount-view-results">

<?php do_action( 'yaexam_before_my_account' ); ?>

<div class="table-responsive">
	<table class="table table-striped">
		<thead class="thead-dark">
			<tr>
				<th scope="col">#</th>
				<th scope="col"><?php _e('Title', 'yaexam'); ?></th>
				<th scope="col"><?php _e('Score', 'yaexam'); ?></th>
				<th scope="col"><?php _e('Duration', 'yaexam'); ?></th>
				<th scope="col"><?php _e('Date', 'yaexam'); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if(isset($data) && $data): ?>
			<?php foreach($data as $index => $result): 

				$exam	=	new YAEXAM_Exam($result['exam_id']); 
				$settings	=	$exam->get_settings();
				?>
			<tr>
				<td><?php echo $result['index']; ?></td>
				<td class="l"><a href="<?php echo get_permalink($exam->get_id()); ?>" target="blank"><?php echo $exam->get_title(); ?></a></td>
				<td class="c">
					<?php echo ($result['score'] > 0 ? $result['score'] : 0) . '/' . $result['total_score']; ?>	
				</td>
				<td class="l"><?php echo $result['duration']; ?></td>
				<td class="c"><?php echo $result['date_start']; ?></td>
				<td class="em-text-right">
					<a class="em-btn em-btn-sm em-btn-info" href="<?php echo yaexam_get_endpoint_url( 'view-result', $exam->get_id(), yaexam_get_page_permalink( 'myaccount' ) ); ?>"><?php _e('View All', 'yaexam'); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<?php if(isset($data) && $data): ?>
	<?php yaexam_get_template('global/pagination.php', $pagination); ?>
<?php endif; ?>

<?php do_action( 'yaexam_after_my_account' ); ?>
	
	
</div>