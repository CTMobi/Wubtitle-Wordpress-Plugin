import React, { useState, useEffect } from 'react';
import { createRoot } from 'react-dom/client';
import CheckoutForm from './Components/CheckoutForm';
import InvoiceForm from './Components/InvoiceForm';
import InvoiceSummary from './Components/InvoiceSummary';
import InfoPriceColumn from './Components/InfoPriceColumn';
import { Elements } from '@stripe/react-stripe-js';
import { loadStripe } from '@stripe/stripe-js';

function App() {
	const {
		stripeKey,
		ajaxUrl,
		ajaxNonce,
		pricePlan,
		invoicePreValues,
		paymentPreValues,
		namePlan,
		expirationDate,
		taxAmount,
		taxPercentage,
		isTaxable,
	} = WP_GLOBALS;
	const stripePromise = loadStripe(stripeKey);
	const [error, setError] = useState(null);
	const [loading, setLoading] = useState(false);
	const [invoiceValues, setInvoiceValues] = useState(null);
	const [isBack, setIsBack] = useState(false);
	const [taxable, setTaxable] = useState(true);

	useEffect(() => {
		if (isTaxable !== null) {
			setTaxable(isTaxable);
		}
	}, [isTaxable]);

	const handleSubmit = (values) => {
		setLoading(true);
		fetch(ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: `action=check_vat_code&_ajax_nonce=${ajaxNonce}&vat_code=${values.vat_code}&country=${values.country}&price_plan=${pricePlan}&companyName=${values.company_name}`,
		})
			.then((resp) => resp.json())
			.then((response) => {
				setLoading(false);
				if (response.success) {
					setError(null);
					setTaxable(response.data);
					setInvoiceValues(values);
					if (isBack) {
						setIsBack(false);
					}
				} else {
					setError(response.data);
				}
			});
	};

	const sendPaymentMethod = (setupIntent, stripe, values) => {
		const { name, email } = values;
		fetch(ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: `action=confirm_subscription&actionCheckout=updateCard&name=${name}&email=${email}&_ajax_nonce=${ajaxNonce}&setupIntent=${JSON.stringify(
				setupIntent
			)}`,
		})
			.then((resp) => resp.json())
			.then((result) => {
				if (result.success) {
					setLoading(false);
					window.opener.thankYouPage('update');
				} else {
					setLoading(false);
					setError(result.data);
				}
			});
	};

	const confirmSetup = (clientSecret, cardNumber, values, stripe) => {
		const { name, email } = values;
		stripe
			.confirmCardSetup(clientSecret, {
				payment_method: {
					type: 'card',
					card: cardNumber,
					billing_details: {
						name,
						email,
					},
				},
			})
			.then((result) => {
				if (
					result.setupIntent &&
					result.setupIntent.status === 'succeeded'
				) {
					setError(null);
					sendPaymentMethod(result, stripe, values);
				}
				if (result.error) {
					setLoading(false);
					setError(result.error.message);
				}
			});
	};

	const createSubscription = (cardNumber, values, stripe) => {
		setLoading(true);
		const { email } = values;
		let actionCheckout = 'updateInvoice';
		if (cardNumber) {
			actionCheckout = 'updateCard';
		}
		fetch(ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: `action=create_subscription&actionCheckout=${actionCheckout}&email=${email}&_ajax_nonce=${ajaxNonce}&invoiceObject=${JSON.stringify(
				invoiceValues
			)}`,
		})
			.then((resp) => resp.json())
			.then((response) => {
				if (response.data === 'updateInvoice') {
					setLoading(false);
					setError(null);
					window.opener.thankYouPage('update');
				} else if (response.success) {
					confirmSetup(response.data, cardNumber, values, stripe);
				} else {
					setLoading(false);
					setError(response.data);
				}
			});
	};

	const backFunction = () => {
		setIsBack(true);
		setError(null);
	};

	const cancelFunction = () => {
		window.close();
	};

	return (
		<div className="main columns">
			<InfoPriceColumn
				update={true}
				price={pricePlan}
				name={namePlan}
				taxAmount={taxAmount}
				taxPercentage={taxPercentage}
				taxable={taxable}
				expirationDate={expirationDate}
				discountedPrice={false}
			/>
			<Elements stripe={stripePromise}>
				{invoiceValues && !isBack ? (
					<div className="wrapper-form column">
						<InvoiceSummary
							invoiceValues={invoiceValues}
							price={pricePlan}
						/>
						<CheckoutForm
							createSubscription={createSubscription}
							backFunction={backFunction}
							paymentPreValues={paymentPreValues}
							error={error}
							setError={setError}
							loading={loading}
						/>
					</div>
				) : (
					<InvoiceForm
						handleSubmit={handleSubmit}
						invoicePreValues={invoiceValues || invoicePreValues}
						error={error}
						cancelFunction={cancelFunction}
						loading={loading}
					/>
				)}
			</Elements>
		</div>
	);
}
if (document.getElementById('update-form')) {
	const container = document.getElementById('update-form');
	const root = createRoot(container);
	root.render(<App />);
}
