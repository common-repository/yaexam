<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }
?>

<?php if(isset($pages) && absint($pages) > 0): 
	
?>

<div class="em-pagination">
	<ul class="em-ml-0 em-mr-0 em-justify-content-center pagination">
		<?php foreach(range(1, absint($pages)) as $i): ?>
		<li class="page-item page <?php echo em_active($i, $page); ?>"><a class="page-link em-text-decoration-none" href="<?php echo $link . '?em-p=' . $i; ?>"><?php echo $i; ?></a></li>
		<?php endforeach; ?>
	</ul>
</div>
<?php endif; ?>