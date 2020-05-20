import { __ } from "@wordpress/i18n";

const langExten = {
	it: __("Italian", "ear2words"),
	en: __("English", "ear2words"),
	es: __("Spanish", "ear2words"),
	de: __("German ", "ear2words"),
	zh: __("Chinese", "ear2words"),
	fr: __("French", "ear2words")
};

const statusExten = {
	pending: __("Generating", "ear2words"),
	draft: __("Draft", "ear2words"),
	enabled: __("Enabled", "ear2words"),
	notfound: __("None", "ear2words")
};

const selectOptions = [
	{
		value: "it",
		label: __("Italian", "ear2words")
	},
	{
		value: "en",
		label: __("English", "ear2words")
	},
	{
		value: "es",
		label: __("Spanish", "ear2words")
	},
	{
		value: "de",
		label: __("German ", "ear2words")
	},
	{
		value: "zh",
		label: __("Chinese", "ear2words")
	},
	{
		value: "fr",
		label: __("French", "ear2words")
	}
];

const selectOptionsFreePlan = [
	{
		value: "it",
		label: __("Italian", "ear2words"),
		disabled: false
	},
	{
		value: "en",
		label: __("English", "ear2words"),
		disabled: false
	},
	{
		value: "es",
		label: __("Spanish (Pro only)", "ear2words"),
		disabled: true
	},
	{
		value: "de",
		label: __("German (Pro only)", "ear2words"),
		disabled: true
	},
	{
		value: "zh",
		label: __("Chinese (Pro only)", "ear2words"),
		disabled: true
	},
	{
		value: "fr",
		label: __("French (Pro only)", "ear2words"),
		disabled: true
	}
];

export { langExten, statusExten, selectOptions, selectOptionsFreePlan };
