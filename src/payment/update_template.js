const paymentModule = (function(Stripe, document) {
	let stripe = null;

	const { adminAjax, nonce } = WP_GLOBALS;

	const openStripeForm = sessionId => {
		if (sessionId) {
			stripe.redirectToCheckout({ sessionId });
		}
	};

	const handleSubmit = () => {
		fetch(adminAjax, {
			method: "POST",
			credentials: "include",
			headers: new Headers({
				"Content-Type": "application/x-www-form-urlencoded"
			}),
			body: `action=update_payment_method&_ajax_nonce=${nonce}`
		})
			.then(resp => resp.json())
			.then(response => {
				if (response.success) {
					openStripeForm(response.data, stripe);
				} else {
					document.getElementById("error-message").innerHTML =
						response.data;
				}
			});
	};

	const init = () => {
		stripe = Stripe("pk_test_nfUYjFiwdkzYpPOfCZkVZiMK00lOAFcAK7");
		handleSubmit();
	};

	return {
		init
	};
})(Stripe, document);

paymentModule.init();
