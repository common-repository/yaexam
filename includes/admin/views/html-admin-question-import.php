<div id="em-admin-question-importing" class="wrap">

	<nav class="em-mb-12 nav-tab-wrapper">
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions')) ?>" class="nav-tab"><?php esc_html_e('Questions', 'yaexam') ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=categories')) ?>" class="nav-tab"><?php esc_html_e('Categories', 'yaexam') ?></a>
		<a href="<?php echo esc_url(admin_url('admin.php?page=em-questions&tab=import')) ?>" class="nav-tab nav-tab-active"><?php esc_html_e('Importing', 'yaexam') ?></a>
	</nav>
	
	<h1><?php _e('Importing Questions', 'yaexam'); ?></h1>

	<div class="em-import" id="importing-questions">
		
		<form enctype="multipart/form-data" method="post" v-show="status == 1">
			
			<p><?php esc_html_e('Hi there! Upload a Excel, CSV file containing questions to import the contents into your question bank. Choose a file to upload, then click "Upload file and import".', 'yaexam'); ?></p>
			
			<p><a href="<?php echo esc_url(plugins_url('yaexam/assets/files/multilang_import.csv')); ?>"><?php esc_html_e('Click here to download a sample', 'yaexam'); ?></a></p>

			<p><?php esc_html_e('How to using options in file:', 'yaexam'); ?></p>

			<p>
				<ol>
					<li><?php esc_html_e('Type Question column: single or multiple', 'yaexam'); ?></li>
					<li><?php esc_html_e("Categories column: the id of question categories is separate by ',': 1,2,3", 'yaexam'); ?></li>
					<li><?php esc_html_e('Order Type column: the id of order type question', 'yaexam'); ?>
						<ul>
							<li><?php esc_html_e('0 : None', 'yaexam'); ?></li>
							<li><?php esc_html_e('1 : A,B,C,D', 'yaexam'); ?></li>
							<li><?php esc_html_e('2 : 1,2,3,4', 'yaexam'); ?></li>
							<li><?php esc_html_e('3 : I,II,III,IV', 'yaexam'); ?></li>
						</ul>
					</li>
					<li><?php esc_html_e('Correct Answer column: the index of correct answer start from 0', 'yaexam'); ?></li>
				</ol>
			</p>
			
			<table class="form-table">
				<tbody>
					<tr>
						<th>
							<label for="upload"><?php esc_html_e('Choose a file from your computer:', 'yaexam'); ?></label>
						</th>
						<td>
							<input type="file" id="upload" name="question_csv" size="25" v-on:change="send_data($event.target.name, $event.target.files)" accept=".csv,.xls,.xlsx">
							<small><?php esc_html_e('Maximum size:', 'yaexam'); ?> <?php echo size_format(wp_max_upload_size()); ?></small>
						</td>
					</tr>
				</tbody>
			</table>
			
			<p class="submit">
				<input type="submit" class="button" @click.prevent="start_import" value="<?php esc_html_e('Upload file and import', 'yaexam'); ?>">
			</p>
		</form>
		
		<table class="em-mt-3 wp-list-table widefat fixed striped posts" v-if="items.length > 0">
			<thead>
				<tr>
					<td class="manage-column column-index">#</td>
					<td class="manage-column column-title column-primary"><?php esc_html_e('Title', 'yaexam') ?></td>
					<td class="manage-column column-status"><?php esc_html_e('Status', 'yaexam'); ?></td>
				</tr>
			</thead>

			<tbody>
				<template v-for="(item, index) in items">
					
					<tr class="iedit csv-item">
						<td class="index column-index">{{index+1}}</td>
						<td class="title column-title has-row-actions column-primary page-title"><a v-bind:href="item.url">{{item[2]}}</a></td>
						<td class="index column-status" v-if="item.status > 0"><span class="em-badge em-badge-success"><?php esc_html_e('Success', 'yaexam') ?></span></td>
						<td class="index column-status" v-if="item.status <= 0"><span class="em-badge em-badge-danger"><?php esc_html_e('Fail', 'yaexam') ?></span></td>
					</tr>

				</template>
			</tbody>
		</table>

	</div>
</div>