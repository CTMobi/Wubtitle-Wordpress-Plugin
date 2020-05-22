const paymentModule = (function(Stripe, document) {
	let stripe = null;

	const { adminAjax, nonce } = WP_GLOBALS;

	const openStripeForm = sessionId => {
		if (sessionId) {
			stripe.redirectToCheckout({ sessionId });
		}
	};

	const handleChoice = plan => {
		fetch(adminAjax, {
			method: "POST",
			credentials: "include",
			headers: new Headers({
				"Content-Type": "application/x-www-form-urlencoded"
			}),
			body: `action=submit_plan&_ajax_nonce=${nonce}&pricing_plan=${plan}`
		})
			.then(resp => resp.json())
			.then(response => {
				if (response.success) {
					if (response.data === "change_plan") {
						window.opener.confirmPlanChange();
					}
					openStripeForm(response.data, stripe);
				}
			});
	};

	const handleUnsubscription = () => {
		fetch(adminAjax, {
			method: "POST",
			credentials: "include",
			headers: new Headers({
				"Content-Type": "application/x-www-form-urlencoded"
			}),
			body: `action=cancel_subscription&_ajax_nonce=${nonce}`
		}).then(() => {
			window.opener.redirectToCallback("notices-code=delete");
		});
	};

	const init = () => {
		stripe = Stripe("pk_test_nfUYjFiwdkzYpPOfCZkVZiMK00lOAFcAK7");
		const buttons = document.querySelectorAll(".button-choose-plan");
		buttons.forEach(button => {
			button.addEventListener("click", () => {
				const plan = button.getAttribute("plan");
				handleChoice(plan);
			});
		});

		const unsubscribeButton = document.querySelector("#unsubscribeButton");
		const closeButton = document.querySelector("#close");
		if (unsubscribeButton) {
			unsubscribeButton.addEventListener("click", () => {
				handleUnsubscription();
			});
		}
		if (closeButton) {
			closeButton.addEventListener("click", () => {
				window.close();
			});
		}
	};

	return {
		init
	};
})(Stripe, document);

paymentModule.init();
