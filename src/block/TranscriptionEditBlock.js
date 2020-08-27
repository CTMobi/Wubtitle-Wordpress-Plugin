import { withSelect } from '@wordpress/data';
import { FormTokenField } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useDebounce } from '../helper/utils.js';
import { __ } from '@wordpress/i18n';

const TranscriptionEditBlock = ({
	setAttributes,
	className,
	transcriptPost,
	selectCore,
}) => {
	const [currentValue, setValue] = useState('');
	const [textSearch, setTextSearch] = useState('');
	const [tokens, setTokens] = useState([]);
	const debouncedCurrentValue = useDebounce(currentValue, 500);
	const decodeHtmlEntities = (str) => {
		return str.replace(/&#(\d+);/g, (_match, dec) => {
			return String.fromCharCode(dec);
		});
	};

	const replaceBlock = (content) => {
		const Paragraph = wp.blocks.createBlock('core/paragraph', {
			content,
		});
		const selectedBlock = wp.data
			.select('core/editor')
			.getSelectedBlockClientId();
		wp.data.dispatch('core/editor').replaceBlocks(selectedBlock, Paragraph);
		wp.data.dispatch('core/editor').clearSelectedBlock();
	};
	useEffect(() => {
		setTextSearch(debouncedCurrentValue);
	}, [debouncedCurrentValue]);

	if (transcriptPost && tokens.length === 0) {
		setTokens([transcriptPost[0].title.rendered]);
		let text = transcriptPost[0].content.rendered;
		text = text.replace('<p>', '');
		text = text.replace('</p>', '');
		replaceBlock(text);
	}
	let postsCurrent = [];
	const query = {
		per_page: 10,
		search: textSearch,
	};
	let suggestions = [];
	const options = new Map();
	if (textSearch.length > 2) {
		suggestions = selectCore.getEntityRecords(
			'postType',
			'transcript',
			query
		);
		postsCurrent = suggestions !== null ? suggestions : [];
		suggestions = [];
		for (let i = 0; i < postsCurrent.length; i++) {
			options.set(
				decodeHtmlEntities(postsCurrent[i].title.rendered),
				postsCurrent[i].id
			);
			options.set(
				decodeHtmlEntities(`${postsCurrent[i].title.rendered} content`),
				postsCurrent[i].content.rendered
			);
			suggestions[i] = decodeHtmlEntities(postsCurrent[i].title.rendered);
		}
	}

	let contentText = '';
	const setTokenFunction = (token) => {
		if (token.length === 0) {
			setAttributes({ contentId: null });
			setTokens(token);
		} else if (suggestions.includes(token[0])) {
			const contentId = options.get(token[0]);
			const contentKey = `${token[0]} content`;
			contentText = options.get(contentKey);
			contentText = contentText.replace('<p>', '');
			contentText = contentText.replace('</p>', '');
			setTokens(token);
			setAttributes({ contentId });
			replaceBlock(contentText);
		}
	};

	return (
		<>
			<FormTokenField
				className={className}
				label={__('Wubtitle transcriptions', 'wubtitle')}
				value={tokens}
				suggestions={suggestions}
				onChange={(token) => setTokenFunction(token)}
				placeholder={__('Insert transcriptions', 'wubtitle')}
				onInputChange={(value) => setValue(value)}
				maxLength={1}
			/>
			<p className="helperText">
				{__(
					'Enter the title of the video you want to transcribe',
					'wubtitle'
				)}
			</p>
		</>
	);
};

export default withSelect((select, attributes) => {
	const object = { selectCore: select('core') };
	// TOKEN ?
	if (attributes.attributes.contentId) {
		const queryPost = {
			per_page: 1,
			include: attributes.attributes.contentId,
		};
		const transcriptPost = select('core').getEntityRecords(
			'postType',
			'transcript',
			queryPost
		);
		object.transcriptPost = transcriptPost;
	}
	return object;
})(TranscriptionEditBlock);
