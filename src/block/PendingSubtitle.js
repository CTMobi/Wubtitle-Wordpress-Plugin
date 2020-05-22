import { Fragment } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { langExten, statusExten } from "./labels.js";

const PendingSubtitle = ({ statusText, langText }) => (
	<Fragment>
		<div>{__("Status: ", "ear2words") + statusExten[statusText]}</div>
		<div>{__("Language: ", "ear2words") + langExten[langText]}</div>
	</Fragment>
);

export default PendingSubtitle;
