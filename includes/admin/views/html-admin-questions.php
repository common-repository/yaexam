
<div id="em-admin-questions" class="wrap">

	<nav class="em-mb-12 nav-tab-wrapper">
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions')) ?>" class="nav-tab nav-tab-active"><?php esc_html_e('Questions', 'yaexam') ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=categories')) ?>" class="nav-tab"><?php esc_html_e('Categories', 'yaexam') ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=import')) ?>" class="nav-tab"><?php esc_html_e('Importing', 'yaexam') ?></a>

	</nav>

	<h1 class="wp-heading-inline"><?php esc_html_e('Questions', 'yaexam') ?></h1>

	<a class="page-title-action" @click.prevent="addNew" href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=questions&id=0')) ?>"><?php esc_html_e('Add New', 'yaexam') ?></a>

	<hr class="wp-header-end">

	<form class="em-mt-12 posts-filter" method="get">

		<div class="tablenav top">

			<?php yaexam_html_select_categories( $category ); ?>
				
			<button type="submit" name="action" value="filter" class="button"><?php esc_html_e('Filter', 'yaexam') ?></button>

			<button type="submit" name="action" value="remove" class="button em-ml-12"><?php esc_html_e('Remove', 'yaexam') ?></button>

		</div>

		<table class="wp-list-table em-mt-12 widefat fixed striped posts">
			<thead>
				<tr>
					
					<th scope="col" id="cbs" class="em-txt-center em-w-30">
						<input type="checkbox" :value="1" v-model="questions_check_all" v-on:change="questions_toogle_checkall" class="em-nomargin">
					</th>

					<th scope="col" id="title" class="manage-column column-title column-primary"><?php esc_html_e('Title', 'yaexam') ?></th>

					<th scope="col" id="type" class="em-w-150"><?php esc_html_e('Type', 'yaexam') ?></th>

					<th scope="col" id="category" class="manage-column column-category"><?php esc_html_e('Category', 'yaexam') ?></th>

					<th scope="col" id="score" class="em-w-150"><?php esc_html_e('Score', 'yaexam') ?></th>
					
					<th scope="col" id="id" class="em-w-50">ID</th>

				</tr>
			</thead>

			
			<tbody id="the-list">
				<?php if( $results ): ?>
					<?php foreach($results as $index => $result): ?>
					<tr class="iedit author-self level-0 status-publish hentry">

						<td class="em-txt-center"><input type="checkbox" value="<?php echo esc_attr($result['id']); ?>" name="questions_checkall[]" class="em-nomargin questions_checkall"></td>
						
						<td>

							<a href="<?php echo admin_url('admin.php?page=em-questions&tab=questions&id=' . $result['id']) ?>"><strong><?php echo esc_html($result['title']); ?></strong></a>
								
						</td>

						<td><?php echo esc_html($result['answer_type']); ?></td>
						
						<td>
							<?php foreach($categories as $cat): ?>

							<?php if( $cat['id'] == $result['category_id'] ): ?>
								
								<?php echo esc_html($cat['name']); ?>

							<?php endif; ?>

							<?php endforeach; ?>
						</td>

						<td><?php echo esc_html($result['score']); ?></td>
						
						<td><?php echo esc_html($result['id']); ?></td>
					</tr>
					<?php endforeach; ?>

				<?php else: ?>

				<tr>
					<td colspan="5" align="center"><?php esc_html_e('No Data', 'yaexam'); ?></td>
				</tr>

				<?php endif; ?>
			</tbody>
			

		</table>

		<div class="tablenav bottom">
			
			<div class="tablenav-pages">

				<span class="displaying-num"><?php echo esc_html($paginated['total']); ?> <?php esc_html_e('questions', 'yaexam'); ?></span>

				<span class="pagination-links">

					<?php if( $paginated['page'] == 1 ):  ?>

					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>

					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>

					<?php else: ?>

					<a class="tablenav-pages-navspan button" href="<?php echo admin_url('admin.php?page=em-questions&tab=questions&p=1'); ?>">«</a>
					<a class="tablenav-pages-navspan button" href="<?php echo admin_url('admin.php?page=em-questions&tab=questions&p=' . ($paginated['page'] - 1)) ?>">‹</a>

					<?php endif; ?>

					<span class="screen-reader-text">Current Page</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text"><?php echo $paginated['page']; ?> of <span class="total-pages"><?php echo $paginated['pages']; ?></span></span></span>

					<?php if( $paginated['page'] == $paginated['pages'] ):  ?>

					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>

					<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span>

					<?php else: ?>

					<a class="next-page button" href="<?php echo admin_url('admin.php?page=em-questions&tab=questions&p=' . ($paginated['page'] + 1)) ?>"><span class="screen-reader-text"><?php esc_html_e('Next page', 'yaexam'); ?></span><span aria-hidden="true">›</span></a>

					<a class="last-page button" href="<?php echo admin_url('admin.php?page=em-questions&tab=questions&p=' . ($paginated['pages'])) ?>"><span class="screen-reader-text"><?php esc_html_e('Last page', 'yaexam'); ?></span><span aria-hidden="true">»</span></a>

					<?php endif; ?>

				</span>

			</div>

		</div>

		<input type="hidden" name="page" value="em-questions"/>
	</form>
	

</div>