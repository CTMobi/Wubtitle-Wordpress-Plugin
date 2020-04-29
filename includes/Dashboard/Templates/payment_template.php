<?php
/**
 * This file is a template.
 *
 * @author     Nicola Palermo
 * @since      0.1.0
 * @package    Ear2Words\Dashboar\Templates
 */

/**
 * This is a template.
 */
$disable_downgrade_message = __( 'Unable this select this plan during the first month of subscription for current plan', 'ear2words' );
$message_free              = 'Choose this plan';
$message_standard          = 'Choose this plan';
$message_elite             = 'Choose this plan';
$class_free                = 'button-choose-plan';
$class_standard            = 'button-choose-plan';
$class_elite               = 'button-choose-plan';
switch ( get_option( 'ear2words_plan' ) ) {
	case 'plan_0':
		$class_free   = 'current-plan';
		$message_free = 'Current Plan';
		break;
	case 'plan_HBBbNjLjVk3w4w':
		if ( get_option( 'ear2words_is_first_month' ) ) {
			$class_free   = 'disable-downgrade';
			$message_free = $disable_downgrade_message;
		}
		$class_standard   = 'current-plan';
		$message_standard = 'Current Plan';
		break;
	case 'plan_HBBS5I9usXvwQR':
		if ( get_option( 'ear2words_is_first_month' ) ) {
			$class_free       = 'disable-downgrade';
			$class_standard   = 'disable-downgrade';
			$message_free     = $disable_downgrade_message;
			$message_standard = $disable_downgrade_message;
		}
		$class_elite   = 'current-plan';
		$message_elite = 'Current Plan';
		break;
}
require EAR2WORDS_DIR . 'includes/Dashboard/Templates/plans_array.php'
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Payment</title>
	<?php // phpcs:disable ?>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.typekit.net/auk4ruc.css">
	<link rel="stylesheet" href="<?php echo esc_url( EAR2WORDS_URL . 'src/css/payment_template.css' ); ?>">
	<?php // phpcs:enable ?>
</head>
<body>
	<div class="container">
		<h1 class="title"><?php echo esc_html_e( 'Choose the right plan for your project', 'ear2words' ); ?></h1>
		<div class="row">
		<?php
		foreach ( $plans as $plan ) :
			?>
			<div class="column one-quarter">
				<div class="card <?php echo $plan['zoom'] ? 'zoom' : ''; ?>">
					<h2 class="card__title">
						<?php echo esc_html( $plan['name'] ); ?>
					</h2>
					<div class="card__logo">
						<!-- TODO: placeholder logo, momentaneamente un quadrato grigio con css-->
					</div>
					<div class="card__price">
						<?php echo esc_html_e( 'Per year', 'ear2words' ); ?>
						<p class="price">
							<?php echo esc_html( '€' . $plan['price'] ); ?>
						</p>
					</div>
					<?php
					foreach ( $plan['features'] as $feature ) :
						?>
					<div class="card__features">
						<?php echo esc_html( $feature ); ?>
						<div><?php echo esc_html_e( 'include', 'ear2words' ); ?></div>
					</div>
						<?php
					endforeach;
					?>
					<div class="<?php echo $plan['current_plan'] ? 'current-plan' : 'button-choose-plan'; ?>" plan="<?php echo esc_html( $plan['stripe_code'] ); ?>">
						<?php echo $plan['current_plan'] ? esc_html_e( 'Your plan', 'ear2words' ) : esc_html_e( 'Choose this plan', 'ear2words' ); ?>
					</div>
				</div>
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
