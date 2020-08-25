import React from 'react';
import { __ } from '@wordpress/i18n';

const Disclaimer = (props) => {
	const { infoMessage } = props;
	return (
		<div className="disclaimer">
			{infoMessage ? <p>{infoMessage}</p> : ''}
			<p>
				{__(
					'When ordering within the EU an order may be exempt to VAT if a valid VAT registration number is provided.',
					'wubtitle'
				)}
			</p>
			<p>
				<a
					href="https://stripe.com/checkout/terms"
					rel="noreferrer"
					target="_blank"
				>
					{__('Terms and conditions', 'wubtitle')}
				</a>
				<span> | </span>
				<a
					href="https://stripe.com/it/privacy"
					target="_blank"
					rel="noreferrer"
				>
					{__('Privacy', 'wubtitle')}
				</a>
			</p>
		</div>
	);
};

export default Disclaimer;
