<?php
/**
 * Succes payment url template.
 *
 * @author     Alessio Catania
 * @since      0.1.0
 * @package    Wubtitle\Dashboard\Templates
 */

/**
 * Success payment page.
 */
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<meta charset="utf-8">
		<title>Success</title>
		<script>
		window.unonload = window.opener.redirectToCallback('notices-code=payment');
		window.close();
		</script>
	</head>
	<body>
	</body>
</html>
