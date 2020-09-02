/* eslint-disable no-undef */
/* eslint-disable no-console */
import { useSelect, useDispatch } from '@wordpress/data';
import { PanelBody, Button, SelectControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';

const EmbedControlPanel = (props) => {
	const [message, setMessage] = useState('');
	const [status, setStatus] = useState(__('None', 'wubtitle'));
	const [languageSelected, setLanguage] = useState('');
	const [langReady, setReady] = useState(false);
	const [options, setOptions] = useState([]);
	const [title, setTitle] = useState('');
	const [disabled, setDisabled] = useState(true);
	const noticeDispatcher = useDispatch('core/notices');

	useSelect((select) => {
		if (props.url === undefined) {
			return;
		}
		const transcript = select('core').getEntityRecords(
			'postType',
			'transcript',
			{
				metaKey: '_video_id',
				metaValue: props.url,
			}
		);
		const createdStatus = __('Created', 'wubtitle');
		if (transcript && transcript.length > 0 && status !== createdStatus) {
			setStatus(createdStatus);
		}
	});

	const handleClick = () => {
		const selectedBlockIndex = wp.data
			.select('core/block-editor')
			.getBlockIndex(
				wp.data.select('core/block-editor').getSelectedBlock().clientId
			);

		setMessage(__('Getting transcript…', 'ear2words'));
		wp.ajax
			.send('get_transcript_yt', {
				type: 'POST',
				data: {
					urlVideo: props.url,
					urlSubtitle: languageSelected,
					videoTitle: title,
					from: 'default_post_type',
					_ajax_nonce: wubtitle_button_object.ajaxnonce,
				},
			})
			.then((response) => {
				const block = wp.blocks.createBlock('wubtitle/transcription', {
					contentId: response,
				});
				const blockPosition = selectedBlockIndex + 1;
				wp.data
					.dispatch('core/block-editor')
					.insertBlocks(block, blockPosition);
				setMessage('');
				setStatus(__('Created', 'wubtitle'));
			})
			.fail((response) => {
				noticeDispatcher.createNotice('error', response);
				setMessage('');
			});
	};

	const getLang = () => {
		wp.ajax
			.send('get_video_info', {
				type: 'POST',
				data: {
					url: props.url,
					_ajax_nonce: wubtitle_button_object.ajaxnonce,
				},
			})
			.then((response) => {
				if (!response.languages) {
					setMessage(
						__('Subtitles not available for this video', 'wubtitle')
					);
					return;
				}
				setMessage('');
				setReady(true);
				const arrayLang = response.languages.map((lang) => {
					return {
						value: lang.baseUrl,
						label: lang.name.simpleText,
					};
				});
				arrayLang.unshift({
					value: 'none',
					label: __('Select language', 'wubtitle'),
				});
				setOptions(arrayLang);
				setTitle(response.title);
			})
			.fail((response) => {
				console.log(response);
			});
	};

	return (
		<InspectorControls>
			<PanelBody title="Wubtitle">
				<p style={{ margin: '0', marginBottom: '20px' }}>
					{`${__('Transcript status:', 'wubtitle')} ${status}`}
				</p>
				{props.url && langReady ? (
					<SelectControl
						label={__('Select the video language', 'wubtitle')}
						value={languageSelected}
						onChange={(lingua) => {
							setLanguage(lingua);
							setDisabled(lingua === 'none');
						}}
						options={options}
					/>
				) : (
					getLang()
				)}
				<Button
					name="sottotitoli"
					id={props.id}
					isPrimary
					onClick={handleClick}
					disabled={disabled}
				>
					{__('Get Transcribe', 'wubtitle')}
				</Button>
				<p>{message}</p>
			</PanelBody>
		</InspectorControls>
	);
};

export default EmbedControlPanel;
