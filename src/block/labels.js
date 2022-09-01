/*  global wubtitle_button_object  */
import { __ } from '@wordpress/i18n';
/* eslint-disable camelcase */
const langExten = wubtitle_button_object.langExten;

const statusExten = {
	pending: __('Generating', 'wubtitle'),
	draft: __('Draft', 'wubtitle'),
	enabled: __('Enabled', 'wubtitle'),
	notfound: __('None', 'wubtitle'),
};

const selectOptions = Object.entries(langExten).map(([key, value]) => {
	return {
		value: key,
		label: value,
	};
});

const languagesFree = ['it-IT', 'en-US'];
const allLanguages = Object.keys(langExten);

const selectOptionsFreePlan = Object.entries(langExten).map(([key, value]) => {
	if (languagesFree.includes(key)) {
		return {
			value: key,
			label: value,
		};
	}
	return {
		value: key,
		label: `${value} ${__('(Pro Only)', 'wubtitle')}`,
		disabled: true,
	};
});

//support to old version
langExten.it = __('Italian', 'wubtitle');
langExten.en = __('English', 'wubtitle');
langExten.es = __('Spanish', 'wubtitle');
langExten.de = __('German', 'wubtitle');
langExten.zh = __('Chinese', 'wubtitle');
langExten.fr = __('French', 'wubtitle');

export {
	langExten,
	statusExten,
	selectOptions,
	selectOptionsFreePlan,
	allLanguages,
	languagesFree,
};
