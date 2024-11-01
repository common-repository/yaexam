<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="em-my-account-table__view-save-laters" class="container-myaccount-view-save-laters">

<?php do_action( 'yaexam_before_my_account' ); ?>

<div class="table-responsive">
	<table class="table table-striped">
		<thead class="thead-dark">
			<tr>
				<th scope="col">#</th>
				<th scope="col"><?php _e('Title', 'yaexam'); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			<?php if(isset($items) && $items): ?>
			<?php foreach($items as $index => $item): 

				$remove_link = add_query_arg('remove', $item['id'], yaexam_get_endpoint_url( 'view-save-laters', '', yaexam_get_page_permalink( 'myaccount' ) ));
			?>
			<tr>
				<td><?php echo $index + 1; ?></td>
				<td class="l"><a href="<?php echo get_permalink($item['exam_id']); ?>"><?php echo $item['exam_name']; ?></a></td>
				<td class="em-text-right">
					<a class="em-btn em-btn-sm em-btn-primary" href="<?php echo get_permalink($item['exam_id']); ?>"><?php _e('Play', 'yaexam'); ?></a>
					<a class="em-btn em-btn-sm em-btn-danger" href="<?php echo $remove_link; ?>"><?php _e('Remove', 'yaexam'); ?></a>
				</td>
			</tr>
			<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<?php do_action( 'yaexam_after_my_account' ); ?>
	
	
</div>