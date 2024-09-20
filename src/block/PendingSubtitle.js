import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { langExten, statusExten } from './labels.js';

const PendingSubtitle = ({ statusText, langText }) => (
	<Fragment>
		<div>
			{__('Status:', 'wubtitle') + ' ' + statusExten[`${statusText}`]}
		</div>
		<div>
			{__('Language:', 'wubtitle') + ' ' + langExten[`${langText}`]}
		</div>
	</Fragment>
);

export default PendingSubtitle;
