<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! $messages ){
	return;
}

?>
<div class="em-alert em-alert-danger" role="alert">
	<?php foreach ( $messages as $message ) : ?>
		<div><?php echo wp_kses_post( $message ); ?></div>
	<?php endforeach; ?>
</div>
