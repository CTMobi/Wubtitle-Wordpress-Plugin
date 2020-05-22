<?php
/**
 * Pagina per la conferma dell'upgrade del piano.
 *
 * @author     Nicola Palermo
 * @since      0.1.0
 * @package    Ear2Words\Dashboar\Templates
 */

/**
 * Pagina per l'upgrade del piano.
 */
require EAR2WORDS_DIR . 'includes/Dashboard/Templates/plans_array.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Payment</title>
	<?php // phpcs:disable ?>
	<link href="https://fonts.googleapis.com/css?family=Days+One|Open+Sans&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="<?php echo esc_url( EAR2WORDS_URL . 'src/css/payment_template.css' ); ?>">
	<?php // phpcs:enable ?>
</head>
<body>
	<div class="container" id="content">
		<h1 class="title"><?php echo esc_html_e( 'Choose the right plan for your project', 'ear2words' ); ?></h1>
		<div class="row">
		<?php
		foreach ( $plans as $plan ) :
			?>
			<div class="column one-quarter">
				<div class="card <?php echo $plan['zoom'] ? 'zoom' : ''; ?>">
					<div class="card__header">
						<h2 class="card__title">
							<?php echo esc_html( $plan['name'] ); ?>
						</h2>
						<img class="card__logo" src="<?php echo esc_url( EAR2WORDS_URL . 'src/img/' . $plan['icon'] ); ?>">
					</div>
					<div class="card__price">
						<?php echo esc_html_e( 'Per month', 'ear2words' ); ?>
						<p class="price">
							<?php echo esc_html( '€' . $plan['price'] ); ?>
						</p>
					</div>
					<?php
					foreach ( $plan['features'] as $key => $feature ) :
						?>
					<p class="card__features">
						<span><?php echo esc_html( $key ); ?></span>
						<?php echo esc_html( $feature ); ?>
					</p>
						<?php
					endforeach;
					?>
					<div class="<?php echo esc_attr( $plan['class_button'] ); ?>" plan="<?php echo esc_html( $plan['stripe_code'] ); ?>">
						<?php echo esc_html( $plan['message_button'] ); ?>
					</div>
				</div>
			</div>
			<?php
		endforeach;
		?>
		</div>
		<div class="row">
		<?php
		foreach ( $plans as $plan ) :
			?>
			<div class="column one-quarter">
				<ul class="features-list">
					<?php
					foreach ( $plan['dot_list'] as $dot ) :
						?>
					<li><?php echo esc_html( $dot ); ?></li>
						<?php
					endforeach;
					?>
				</ul>
			</div>
			<?php
		endforeach;
		?>
		</div>
	</div>
	<?php // phpcs:disable ?>
	<script>
		const WP_GLOBALS = {
			adminAjax: "<?php echo esc_html( admin_url( 'admin-ajax.php' ) ); ?>",
			nonce: "<?php echo esc_js( wp_create_nonce( 'itr_ajax_nonce' ) ); ?>"
		}
	</script>
	<script src="https://js.stripe.com/v3/"></script>
	<script src="<?php echo esc_url(EAR2WORDS_URL . 'src/payment/payment_template.js'); ?>"></script>
	<?php // phpcs:enable ?>
</body>
</html>
