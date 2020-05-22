const changePlanModule = (function(document) {
	const { adminAjax, nonce } = WP_GLOBALS;

	const handleConfirm = () => {
		fetch(adminAjax, {
			method: "POST",
			credentials: "include",
			headers: new Headers({
				"Content-Type": "application/x-www-form-urlencoded"
			}),
			body: `action=change_plan&_ajax_nonce=${nonce}`
		})
			.then(resp => resp.json())
			.then(response => {
				if (response.success) {
					window.unonload = window.opener.redirectToCallback(
						"notices-code=payment"
					);
					window.close();
				} else {
					document.getElementById("error-message").innerHTML =
						response.data;
				}
			});
	};

	const init = () => {
		const confirmButton = document.querySelector("#confirm_changes");
		const closeButton = document.querySelector("#forget");
		if (confirmButton) {
			confirmButton.addEventListener("click", () => {
				handleConfirm();
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
})(document);

changePlanModule.init();
