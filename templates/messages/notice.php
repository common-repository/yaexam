<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! $messages ){
	return;
}

?>

<?php foreach ( $messages as $message ) : ?>
	<div class="em-alert em-alert-info" role="alert"><?php echo wp_kses_post( $message ); ?></div>
<?php endforeach; ?>
