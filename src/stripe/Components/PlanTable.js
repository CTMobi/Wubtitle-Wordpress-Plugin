import React from 'react';
import { __ } from '@wordpress/i18n';

const PlanTable = (props) => {
	const {
		currentPlan,
		taxPercentage,
		renewal,
		taxable,
		taxAmount,
		price,
		total,
	} = props;
	const { siteDomain } = WP_GLOBALS;
	return (
		<table className="price-table">
			<tr>
				<td>{__('Domain', 'wubtitle')}</td>
				<td className="val">{siteDomain}</td>
			</tr>
			<tr>
				<td>{__('Your plan', 'wubtitle')}</td>
				<td className="val">{currentPlan}</td>
			</tr>
			<tr>
				<td>{__('Price', 'wubtitle')}</td>
				<td className="val">{price} &euro;</td>
			</tr>
			<tr>
				<td>
					{__('VAT', 'wubtitle')} ({taxPercentage}%)
				</td>
				{taxable ? (
					<td className="val">{taxAmount} &euro;</td>
				) : (
					<td className="val">
						0 &euro;{' '}
						<span className="description">
							{__('no Vat due for you', 'wubtitle')}
						</span>
					</td>
				)}
			</tr>
			<tr className="total">
				<td>{__('Total', 'wubtitle')}</td>
				<td className="val">
					{total} &euro;
					<span className="valxm">{__('per month', 'wubtitle')}</span>
				</td>
			</tr>
			{renewal ? (
				<tr className="total">
					<td>{__('Automatic renewal', 'wubtitle')}</td>
					<td className="val">{renewal}</td>
				</tr>
			) : null}
		</table>
	);
};

export default PlanTable;
