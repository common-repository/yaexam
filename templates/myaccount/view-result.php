<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }


$settings	=	$exam->get_settings();

?>

<div id="em-my-account-table__view-results" class="container-myaccount-view-result">

<?php do_action( 'yaexam_before_my_account' ); ?>

	<h1 class="yaexam_result_title"><?php echo $exam->get_title(); ?></h5>
			
	<?php do_action( 'yaexam_before_my_account_view_result' ); ?>
	
	<table cellpadding="0" cellspacing="0" class="table table-striped">
		<thead class="thead-dark">
			<tr>
				<th>#</th>
				<th><?php _e('Score', 'yaexam'); ?></th>
				<th><?php _e('Duration', 'yaexam') ?></th>
				<th><?php _e('Date', 'yaexam') ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
	<?php if(isset($results['data']) && $results['data']): ?>
		<?php foreach($results['data'] as $index => $result): 
			?>
			<tr>
				<td class="c"><?php echo $index + 1 ?></td>
				<td class="c"><?php echo $result['score'] . '/' . $result['total_score']; ?></td>
				<td class="c"><?php echo $result['duration']; ?></td>
				<td class="c"><?php echo $result['date_start']; ?></td>
				<td class="em-text-right"><a class="em-btn em-btn-sm em-btn-info" href="<?php echo yaexam_get_endpoint_url( 'em-result', $result['id'], get_permalink( $result['exam_id'] )); ?>" target="blank"><?php _e('Detail', 'yaexam'); ?></a></td>
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
		</tbody>
	</table>
	
	<?php if(isset($results['data']) && $results['data']): ?>
	
	<?php yaexam_get_template('global/pagination.php', $results['pagination']); ?>
	<?php endif; ?>
	
	<?php do_action( 'yaexam_after_my_account_view_result' ); ?>
	<?php do_action( 'yaexam_after_my_account' ); ?>
</div>