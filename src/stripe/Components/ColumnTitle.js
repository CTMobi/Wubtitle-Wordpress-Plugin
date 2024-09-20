import React from 'react';
import { __ } from '@wordpress/i18n';

const ColumnTitle = (props) => {
	const { name, update } = props;

	return (
		<p className="price-name">
			{update
				? __('Update billing or payment details', 'wubtitle')
				: `${__('Subscribe to', 'wubtitle')} ${name} ${__(
						'plan',
						'wubtitle'
					)}`}
		</p>
	);
};

export default ColumnTitle;
