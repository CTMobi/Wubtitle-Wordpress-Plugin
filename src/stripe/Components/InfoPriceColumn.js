import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { ReactComponent as InfoIcon } from '../../../assets/img/info-white-18dp.svg';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faArrowLeft } from '@fortawesome/free-solid-svg-icons';
import PriceTable from './PriceTable';
import Disclaimer from './Disclaimer';
import ColumnTitle from './ColumnTitle';
import PlanTable from './PlanTable';

const InfoPriceColumn = (props) => {
	const {
		update,
		price,
		name,
		taxAmount,
		taxPercentage,
		taxable,
		expirationDate,
		discountedPrice,
	} = props;
	let messagePrice = ` ${__('per month', 'wubtitle')}`;
	let infoMessage = null;
	if (discountedPrice && discountedPrice.duration === 'once') {
		messagePrice = ` ${__('for this month*', 'wubtitle')}`;
		infoMessage = `*${__(
			'After the first month the monthly price will be',
			'wubtitle'
		)} ${price}€ + ${taxAmount}€ ${__('(VAT)', 'wubtitle')}`;
	}
	if (discountedPrice && discountedPrice.duration === 'repeating') {
		messagePrice = ` ${__('for the firsts', 'wubtitle')} ${
			discountedPrice.durationInMonths
		} ${__('months*', 'wubtitle')}`;
		infoMessage = `*${__('After the firsts', 'wubtitle')} ${
			discountedPrice.durationInMonths
		} ${__(
			'months the monthly price will be',
			'wubtitle'
		)} ${price}€ + ${taxAmount}€ ${__('(VAT)', 'wubtitle')}`;
	}
	let total = parseFloat(price);
	if (taxable) {
		total = parseFloat(price) + parseFloat(taxAmount);
	}
	const [isOpen, setIsOpen] = useState(false);
	return (
		<div className="column price-column">
			<div className="price">
				<ColumnTitle name={name} update={update} />
				{update ? null : (
					<p className="mobile-price-info is-hidden-on-desktop">
						{discountedPrice ? (
							<span className="cut-vat">
								<span className="total">
									{total} &euro;
									<span className="cut-line" />
								</span>
								<span className="total">
									{parseFloat(discountedPrice.newTotal)}{' '}
									&euro;
								</span>
							</span>
						) : (
							<span className="total">{total} &euro; </span>
						)}
						<span className="valxm">{messagePrice}</span>
						<InfoIcon
							className="info-icon"
							onClick={() => setIsOpen(!isOpen)}
						/>
					</p>
				)}
				{update ? (
					<PlanTable
						currentPlan={name}
						renewal={expirationDate}
						taxable={taxable}
						taxAmount={taxAmount}
						price={price}
						taxPercentage={taxPercentage}
						total={total}
					/>
				) : (
					<PriceTable
						price={price}
						taxPercentage={taxPercentage}
						taxAmount={taxAmount}
						taxable={taxable}
						total={total}
						discountedPrice={discountedPrice}
						messagePrice={messagePrice}
					/>
				)}
			</div>
			<Disclaimer infoMessage={infoMessage} />
			<div
				className={
					isOpen ? 'mobile-price-view opened' : 'mobile-price-view'
				}
			>
				<div className="top">
					<div
						className="nav-back"
						onClick={() => setIsOpen(!isOpen)}
						aria-hidden="true"
					>
						<span>
							<FontAwesomeIcon icon={faArrowLeft} />
							Subscription details
						</span>
					</div>
					<ColumnTitle name={name} update={update} />
					<PriceTable
						price={price}
						taxPercentage={taxPercentage}
						taxAmount={taxAmount}
						taxable={taxable}
						total={total}
						discountedPrice={discountedPrice}
						messagePrice={messagePrice}
					/>
				</div>
				<Disclaimer infoMessage={infoMessage} />
			</div>
		</div>
	);
};

export default InfoPriceColumn;
