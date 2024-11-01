
<div id="em-admin-questions" class="wrap">

	<nav class="em-mb-12 nav-tab-wrapper">
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions')) ?>" class="nav-tab"><?php esc_html_e('Questions', 'yaexam') ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=categories')) ?>" class="nav-tab nav-tab-active"><?php esc_html_e('Categories', 'yaexam') ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=import')) ?>" class="nav-tab"><?php esc_html_e('Importing', 'yaexam') ?></a>
	</nav>

	<h1 class="wp-heading-inline"><?php esc_html_e('Categories', 'yaexam') ?></h1>

	<a class="page-title-action" href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=categories&id=0')) ?>"><?php esc_html_e('Add category', 'yaexam') ?></a>

	<hr class="wp-header-end">

	<form class="em-mt-12 posts-filter" method="post">

		<table class="wp-list-table em-mt-12 widefat fixed striped posts">
			<thead>
				<tr>
					
					<th scope="col" id="cbs" class="em-txt-center em-w-30">
						<input type="checkbox" :value="1" v-model="questions_check_all" v-on:change="questions_toogle_checkall" class="em-nomargin">
					</th>

					<th scope="col" id="title" class="manage-column column-title column-primary"><?php esc_html_e('Title', 'yaexam') ?></th>

					<th scope="col" id="id" class="em-w-100"></th>

				</tr>
			</thead>

			
			<tbody id="the-list">
				<?php if( $results ): ?>

				<?php foreach($results as $index => $result):?>
				<tr class="iedit author-self level-0 status-publish hentry">

					<td class="em-txt-center"><input type="checkbox" value="<?php echo esc_attr($result['id']) ?>" name="questions_checkall[]" class="em-nomargin questions_checkall"></td>
					
					<td>
						<a href="<?php echo admin_url('admin.php?page=em-questions&tab=categories&id=' . absint($result['id'])) ?>"><?php echo esc_html($result['name']); ?></a>
					</td>

					<td><a href="<?php echo admin_url('admin.php?page=em-questions&tab=categories&action=remove&id=' . absint($result['id'])) ?>"><?php esc_html_e('Remove', 'yaexam') ?></a></td>
				</tr>
				<?php endforeach; ?>

				<?php else: ?>

				<tr>
					<td colspan="5" align="center"><?php esc_html_e('Create Category', 'yaexam'); ?></td>
				</tr>

				<?php endif; ?>
			</tbody>
			

		</table>
		<input type="hidden" name="tab" value="categories">
	</form>

</div>