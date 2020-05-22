/* global settings_object */
/* exported redirectToCallback */
let BuyLicenseWindow = null;
let UpdatePlanWindow = null;
let CancelSubscriptionWindow = null;

/* eslint-disable */
function redirectToCallback(param) {
	window.location.href += "&" + param;
}
function cancelPayment(){
	BuyLicenseWindow.close();
	showBuyLicenseWindow();
}
function confirmPlanChange(){
	BuyLicenseWindow.close();
	confirmPlanChangeWindow();
}
/* eslint-enable */

document.addEventListener("DOMContentLoaded", function() {
	const buyButton = document.querySelector("#buy-license-button");
	if (buyButton) {
		buyButton.addEventListener("click", e => {
			e.preventDefault();
			showBuyLicenseWindow();
		});
	}

	const modifyPlan = document.querySelector("#modify-plan");
	if (modifyPlan) {
		modifyPlan.addEventListener("click", e => {
			e.preventDefault();
			showBuyLicenseWindow();
		});
	}

	const resetLicense = document.querySelector("#reset-license");
	if (resetLicense) {
		resetLicense.addEventListener("click", e => {
			e.preventDefault();
			resetLicenseFunction();
		});
	}

	const cancelButton = document.querySelector("#cancel-license-button");
	if (cancelButton) {
		cancelButton.addEventListener("click", e => {
			e.preventDefault();
			showCancelSubscriptionWindow();
		});
	}

	const updateButton = document.querySelector("#update-plan-button");
	if (updateButton) {
		updateButton.addEventListener("click", e => {
			e.preventDefault();
			showUpdatePlanWindow();
		});
	}

	const reactivateButton = document.querySelector("#reactivate-plan-button");
	if (reactivateButton) {
		reactivateButton.addEventListener("click", e => {
			e.preventDefault();
			reactivateFunction();
		});
	}
});
const showUpdatePlanWindow = () => {
	if (UpdatePlanWindow === null || UpdatePlanWindow.closed) {
		const windowFeatures = `
            left=500,
            top=200,
            width=500,
            height=500,
            scrollbars=yes,
        `;
		wp.ajax
			.send("update_template", {
				type: "GET"
			})
			.done(response => {
				UpdatePlanWindow = window.open(
					"",
					"Update-Plan",
					windowFeatures
				);
				UpdatePlanWindow.document.write(response);
			});
	} else {
		UpdatePlanWindow.focus();
		return UpdatePlanWindow;
	}
};
const showBuyLicenseWindow = () => {
	if (BuyLicenseWindow === null || BuyLicenseWindow.closed) {
		const windowFeatures = `
            left=500,
            top=200,
            width=1200,
            height=700,
            scrollbars=yes,
        `;
		wp.ajax
			.send("payment_template", {
				type: "GET"
			})
			.done(response => {
				BuyLicenseWindow = window.open(
					"",
					"Buy-license",
					windowFeatures
				);
				BuyLicenseWindow.document.write(response);
			});
	} else {
		BuyLicenseWindow.focus();
		return BuyLicenseWindow;
	}
};

const confirmPlanChangeWindow = () => {
	if (BuyLicenseWindow === null || BuyLicenseWindow.closed) {
		const windowFeatures = `
            left=500,
            top=200,
            width=1200,
            height=700,
            scrollbars=yes,
        `;
		wp.ajax
			.send("change_plan_template", {
				type: "GET"
			})
			.done(response => {
				BuyLicenseWindow = window.open(
					"",
					"Buy-license",
					windowFeatures
				);
				BuyLicenseWindow.document.write(response);
			});
	} else {
		BuyLicenseWindow.focus();
		return BuyLicenseWindow;
	}
};

const showCancelSubscriptionWindow = () => {
	if (CancelSubscriptionWindow === null || CancelSubscriptionWindow.closed) {
		const windowFeatures = `
			left=500,
			top=200,
			width=1200,
			height=700,
			scrollbars=yes,
        `;
		wp.ajax
			.send("cancel_template", {
				type: "GET"
			})
			.done(response => {
				CancelSubscriptionWindow = window.open(
					"",
					"Cancel subscription",
					windowFeatures
				);
				CancelSubscriptionWindow.document.write(response);
			});
	} else {
		CancelSubscriptionWindow.focus();
	}
};
const resetLicenseFunction = () => {
	fetch(settings_object.ajax_url, {
		method: "POST",
		credentials: "include",
		headers: new Headers({
			"Content-Type": "application/x-www-form-urlencoded"
		}),
		body: `action=reset_license&_ajax_nonce=${settings_object.ajaxnonce}`
	})
		.then(resp => resp.json())
		.then(response => {
			if (response.success) {
				redirectToCallback("notices-code=reset");
			}
		});
};
const reactivateFunction = () => {
	fetch(settings_object.ajax_url, {
		method: "POST",
		credentials: "include",
		headers: new Headers({
			"Content-Type": "application/x-www-form-urlencoded"
		}),
		body: `action=reactivate_plan&_ajax_nonce=${settings_object.ajaxnonce}`
	})
		.then(resp => resp.json())
		.then(response => {
			if (response.success) {
				redirectToCallback("notices-code=reactivate");
			}
		});
};
window.onunload = function() {
	if (BuyLicenseWindow !== null) {
		BuyLicenseWindow.close();
	}
	if (UpdatePlanWindow !== null) {
		UpdatePlanWindow.close();
	}
	if (CancelSubscriptionWindow !== null) {
		CancelSubscriptionWindow.close();
	}
};
