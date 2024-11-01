
<div id="em-admin-questions" class="wrap">

	<nav class="em-mb-12 nav-tab-wrapper">
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions')) ?>" class="nav-tab"><?php esc_html_e('Questions', 'yaexam') ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=categories')) ?>" class="nav-tab nav-tab-active"><?php esc_html_e('Categories', 'yaexam') ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=import')) ?>" class="nav-tab"><?php esc_html_e('Importing', 'yaexam') ?></a>

	</nav>

	<?php if(isset($_GET['id']) && $_GET['id']): ?>
	<h1 class="wp-heading-inline"><?php esc_html_e('Edit Category', 'yaexam'); ?></h1>
	<?php else: ?>
	<h1 class="wp-heading-inline"><?php esc_html_e('New Category', 'yaexam'); ?></h1>
	<?php endif; ?>

	<form class="em-mt-12" method="post">

		<input class="em-title-input" type="text" name="name" size="30" value="<?php echo esc_html_e($category['name']); ?>" id="name" spellcheck="true" autocomplete="off">

		<textarea name="content" class="em-textarea-input em-mt-12" rows="10"><?php echo esc_html_e($category['content']); ?></textarea>


		<div class="em-mt-24">
			<button class="button button-primary" type="submit" name="submit-category" value="1"><?php esc_html_e('Save', 'yaexam') ?></button>
		</div>

		<input type="hidden" name="id" value="<?php echo absint($id); ?>">

		<input type="hidden" name="tab" value="categories">

	</form>

</div>