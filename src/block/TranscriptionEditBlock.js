import { useSelect } from "@wordpress/data";
import { FormTokenField } from "@wordpress/components";
import { useState, useEffect } from "@wordpress/element";
import { useDebounce } from "../helper/utils.js";
import { __ } from "@wordpress/i18n";

const TranscriptionEditBlock = ({ attributes, setAttributes, className }) => {
	const [currentValue, setValue] = useState("");
	const [textSearch, setTextSearch] = useState("");
	const [tokens, setTokens] = useState([]);
	const debouncedCurrentValue = useDebounce(currentValue, 500);

	useEffect(() => {
		setTextSearch(debouncedCurrentValue);
	}, [debouncedCurrentValue]);

	useSelect(select => {
		if (attributes.contentId && tokens.length === 0) {
			const queryPost = {
				per_page: 1,
				include: attributes.contentId
			};
			const resultPost = select("core").getEntityRecords(
				"postType",
				"transcript",
				queryPost
			);
			if (resultPost !== null) {
				setTokens([resultPost[0].title.rendered]);
			}
		}
	});

	const postsCurrent = useSelect(select => {
		if (textSearch.length > 2) {
			const query = {
				per_page: 10,
				search: textSearch
			};
			const suggestions = select("core").getEntityRecords(
				"postType",
				"transcript",
				query
			);
			return suggestions !== null ? suggestions : [];
		}
		return [];
	});

	const options = new Map();
	const suggestions = [];
	for (let i = 0; i < postsCurrent.length; i++) {
		options.set(postsCurrent[i].title.rendered, postsCurrent[i].id);
		suggestions[i] = postsCurrent[i].title.rendered;
	}

	const setTokenFunction = token => {
		if (token.length === 0) {
			setAttributes({ contentId: null });
			setTokens(token);
		} else if (suggestions.includes(token[0])) {
			const contentId = options.get(token[0]);
			setTokens(token);
			setAttributes({ contentId });
		}
	};
	return (
		<FormTokenField
			className={className}
			label={__("Wubtitle transcriptions", "ear2words")}
			value={tokens}
			suggestions={suggestions}
			onChange={token => setTokenFunction(token)}
			placeholder={__("Insert transcriptions", "ear2words")}
			onInputChange={value => setValue(value)}
			maxLength={1}
		/>
	);
};

export default TranscriptionEditBlock;
