<?php
/**
 * Stripe Form template.
 *
 * @author     Alessio Catania
 * @since      0.1.0
 * @package    Wubtitle\Dashboard\Templates
 */

/**
 * Stripe Form template.
 */
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Payment</title>
</head>
<body>
	<div id="root"></div>
	<?php
	if ( function_exists( 'wp_enqueue_block_template_skip_link' ) ) {
		// @phpstan-ignore-next-line
		wp_enqueue_block_template_skip_link();
	}
	wp_footer();
	?>
</body>
</html>
