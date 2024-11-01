<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div>

	<div class="em-question-content">
		<?php echo $content; ?>
	</div>

	<?php if($video):?>
	<div>
		<video controls>
			<source src="<?php echo $video; ?>" type="audio/mpeg">
		</video>
	</div>
	<?php endif; ?>
	
	<div class="em-answers em-multiple-answers">
		
		<?php if( $answers ): ?>

			<div class="em-row">

			<?php foreach( $answers as $answer ): 
				
				$image = isset($answer['image']) && $answer['image'] ? wp_get_attachment_url($answer['image']) : false;
			?>

				<div class="em-col-sm-6">

					<div class="em-d-flex em-align-items-center em-answer" :class="{selected: child.isSelected(<?php echo $answer['id'] ?>)}" @click="child.change(<?php echo $answer['id'] ?>)">
						
						<span v-if="!child.isSelected(<?php echo $answer['id'] ?>)" class="em-answer-check-icon">
							<i class="material-icons">check_box_outline_blank</i>
						</span>

						<span v-if="child.isSelected(<?php echo $answer['id'] ?>)" class="em-answer-check-icon">
							<i class="material-icons">check_box</i>
						</span>

						<div class="em-ml-3 em-answer-content">

							<?php if( $image ): ?>
							<img class="em-mb-2 em-answer-single-image" src="<?php echo $image; ?>" alt="Answer image" />
							<?php endif; ?>

							<?php echo $answer['content']; ?>
						</div>

					</div>

				</div>

			<?php endforeach; ?>

			</div>

		<?php endif; ?>

	</div>

</div>	
