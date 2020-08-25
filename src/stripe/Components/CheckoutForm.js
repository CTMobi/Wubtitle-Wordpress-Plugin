import { useState } from 'react';
import * as Yup from 'yup';
import { __ } from '@wordpress/i18n';
import { Formik, Form, Field } from 'formik';
import CardSection from './CardSection';
import {
	useStripe,
	useElements,
	CardNumberElement,
} from '@stripe/react-stripe-js';

export default function CheckoutForm(props) {
	const {
		createSubscription,
		backFunction,
		error,
		paymentPreValues,
		updatePrice,
		loading,
		coupon,
		setCoupon,
		planId,
	} = props;
	const { ajaxUrl, ajaxNonce } = WP_GLOBALS;
	const stripe = useStripe();
	const elements = useElements();
	const [changeOn, setChangeOn] = useState(false);
	const [couponMessage, setCouponMessage] = useState(null);
	const [loadingCoupon, setLoadingCoupon] = useState(false);
	const requiredMessage = __('Required', 'wubtitle');
	const DisplayingErrorMessagesSchema = Yup.lazy(() => {
		let yupObject = {};
		if (!paymentPreValues || changeOn) {
			yupObject = {
				name: Yup.string().required(requiredMessage),
				email: Yup.string()
					.email(__('Invalid email', 'wubtitle'))
					.required(requiredMessage),
			};
		}
		return Yup.object().shape(yupObject);
	});

	const handleSubmit = async (values) => {
		if (!stripe || !elements) {
			return;
		}
		let cardNumber = null;
		if (paymentPreValues && !changeOn) {
			values.name = paymentPreValues.name;
			values.email = paymentPreValues.email;
			createSubscription(cardNumber, values, stripe);
			return;
		}

		cardNumber = elements.getElement(CardNumberElement);
		createSubscription(cardNumber, values, stripe);
	};

	const checkCoupon = () => {
		setLoadingCoupon(true);
		fetch(ajaxUrl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: `action=check_coupon&_ajax_nonce=${ajaxNonce}&coupon=${coupon}&planId=${planId}`,
		})
			.then((resp) => resp.json())
			.then((response) => {
				setLoadingCoupon(false);
				if (response.success) {
					setCouponMessage(response.data.message);
					updatePrice(response.data);
				} else {
					setCouponMessage(response.data);
					updatePrice(false);
				}
			});
	};

	return (
		<div className="checkout-form">
			{!paymentPreValues ? (
				<div className="title-section">
					<h2>{__('Coupon', 'wubtitle')}</h2>
				</div>
			) : null}
			{!paymentPreValues ? (
				<div className="fields-container flex-container">
					<div className="form-field-container">
						<label htmlFor="name">
							{__('Discount Code', 'wubtitle')}
						</label>
						<input
							name="coupon"
							placeholder="Coupon"
							value={coupon}
							onChange={(event) => setCoupon(event.target.value)}
						/>
						<p className="coupon-message">{couponMessage}</p>
					</div>
					<div className="form-field-container flex-container center-items">
						<button
							className="coupon-button"
							disabled={!coupon || loadingCoupon}
							onClick={() => checkCoupon()}
						>
							{loadingCoupon && (
								<i className="fa fa-refresh fa-spin loading-margin" />
							)}
							{__('Apply Coupon', 'wubtitle')}
						</button>
					</div>
				</div>
			) : null}
			<div className="title-section">
				<h2>{__('Payment Details', 'wubtitle')}</h2>
				{paymentPreValues ? (
					<div className="switch-container">
						<p>
							{changeOn
								? __('editing enabled', 'wubtitle')
								: __('editing disabled', 'wubtitle')}
						</p>
						<label htmlFor="change-data" className="switch">
							<input
								type="checkbox"
								onClick={() => {
									setChangeOn(!changeOn);
								}}
							/>
							<span className="slider round" />
						</label>
					</div>
				) : null}
			</div>
			{paymentPreValues ? (
				<div className="summary columns">
					<div className="column">
						<p>
							<strong>{__('Email', 'wubtitle')}: </strong>
							{paymentPreValues.email}
						</p>
						<p>
							<strong>{__('Card Details', 'wubtitle')}: </strong>
							{paymentPreValues.cardNumber}
						</p>
					</div>
					<div className="column">
						<p>
							<strong>{__('Card Holder', 'wubtitle')}: </strong>
							{paymentPreValues.name}
						</p>
						<p>
							<strong>{__('Expires', 'wubtitle')}: </strong>
							{paymentPreValues.expiration}
						</p>
					</div>
				</div>
			) : null}
			<Formik
				initialValues={{
					name: '',
					email: '',
				}}
				validationSchema={DisplayingErrorMessagesSchema}
				onSubmit={(values) => {
					handleSubmit(values);
				}}
			>
				{({ errors, touched }) => (
					<Form>
						{(error && changeOn) || (error && !paymentPreValues) ? (
							<div
								className="error-message-container"
								role="alert"
							>
								<p className="error-message-notice">{error}</p>
							</div>
						) : (
							''
						)}
						{!paymentPreValues || changeOn ? (
							<div className="fields-container">
								<div className="form-field-container">
									<label htmlFor="email">E-Mail</label>
									<Field name="email" placeholder="Email" />
									<p className="error-message">
										{touched.name && errors.name}
									</p>
								</div>
								<div className="form-field-container">
									<label htmlFor="name">
										{__('Card Holder', 'wubtitle')}
									</label>
									<Field
										name="name"
										placeholder="Card Holder"
									/>
									<p className="error-message">
										{touched.name && errors.name}
									</p>
								</div>
								<div className="form-field-container card">
									<CardSection />
								</div>
							</div>
						) : null}
						<div className="button-bar">
							<input
								type="button"
								className="cancel"
								onClick={() => backFunction()}
								value={__('Back', 'wubtitle')}
							/>
							<button
								disabled={!stripe || loading}
								className={loading ? 'disabled' : ''}
							>
								{loading && (
									<i className="fa fa-refresh fa-spin loading-margin" />
								)}
								{__('Subscribe', 'wubtitle')}
							</button>
						</div>
					</Form>
				)}
			</Formik>
		</div>
	);
}
