<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $exam; 

$thumbnail	=	yaexam_get_exam_thumbnail();

$link = esc_url( get_permalink( $exam->id ) );

?>

<li class="em-mb-3 media">

	<?php if( $display_thumbnail && $thumbnail ): ?>
		<a href="<?php echo $link; ?>" title="<?php echo esc_attr( $exam->get_title() ); ?>">
			<?php echo $thumbnail; ?>
		</a>
	<?php endif; ?>	

	<div class="media-body">

		<?php if($display_date): ?>
		<small><?php echo $exam->get_date_created(); ?></small>
		<?php endif; ?>
		
		<h5 class="mt-2 mb-1">
			<a href="<?php echo $link; ?>" title="<?php echo esc_attr( $exam->get_title() ); ?>"><?php echo $exam->get_title(); ?></a>
		</h5>

	</div>


</li>