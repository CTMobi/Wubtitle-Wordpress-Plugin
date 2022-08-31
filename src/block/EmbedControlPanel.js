/* eslint-disable no-undef */
/* eslint-disable no-console */
import { useDispatch } from '@wordpress/data';
import { PanelBody, Button, SelectControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { langExten } from './labels.js';

const EmbedControlPanel = (props) => {
	const [message, setMessage] = useState('');
	const [status, setStatus] = useState(__('None', 'wubtitle'));
	const [languageSelected, setLanguage] = useState('');
	const [langReady, setReady] = useState(false);
	const [options, setOptions] = useState([]);
	const [title, setTitle] = useState('');
	const [videoUrl, setVideoUrl] = useState('');
	const [disabled, setDisabled] = useState(true);
	const noticeDispatcher = useDispatch('core/notices');
	const disabledGetInfo = langReady || !videoUrl;
	if (videoUrl !== props.url) {
		setVideoUrl(props.url);
		setReady(false);
		setMessage('');
	}
	const handleClick = () => {
		setDisabled(true);
		const selectedBlockIndex = wp.data
			.select('core/block-editor')
			.getBlockIndex(
				wp.data.select('core/block-editor').getSelectedBlock().clientId
			);
		setMessage(__('Getting transcript…', 'ear2words'));
		wp.ajax
			.send('get_transcript_embed', {
				type: 'POST',
				data: {
					urlVideo: videoUrl,
					subtitle: languageSelected,
					videoTitle: title,
					from: 'default_post_type',
					/* eslint-disable camelcase */
					_ajax_nonce: wubtitle_button_object.ajaxnonce,
				},
			})
			.then((response) => {
				setDisabled(false);
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
				setDisabled(false);
				noticeDispatcher.createNotice('error', response);
				setMessage('');
			});
	};

	const getLang = () => {
		setReady(true);
		setOptions([]);
		wp.ajax
			.send('get_video_info', {
				type: 'POST',
				data: {
					url: videoUrl,
					/* eslint-disable camelcase */
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
				const arrayLang = response.languages.map((lang) => {
					if (response.source === 'youtube') {
						return {
							value: lang.baseUrl,
							label: lang.name.simpleText,
						};
					}
					let label = lang.name;
					if (!label && lang.code.includes('autogen')) {
						label = langExten[lang.code?.split('-')?.[0]] ?? '';
					}
					return {
						value: lang.code,
						label,
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
				noticeDispatcher.createNotice('error', response);
				setMessage('');
			});
	};

	if (!langReady && videoUrl && 'core-embed/youtube' === props.block) {
		getLang();
	}

	return (
		<InspectorControls>
			<PanelBody title="Wubtitle">
				<p style={{ margin: '0', marginBottom: '20px' }}>
					{`${__('Transcript status:', 'wubtitle')} ${status}`}
				</p>
				{props.block === 'core-embed/vimeo' && !langReady ? (
					<Button
						name=""
						isPrimary
						onClick={getLang}
						disabled={disabledGetInfo}
					>
						{__('Select transcript language', 'wubtitle')}
					</Button>
				) : (
					''
				)}
				{videoUrl && langReady ? (
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
					''
				)}
				{props.block === 'core-embed/youtube' || langReady ? (
					<Button
						name="sottotitoli"
						id={props.id}
						isPrimary
						onClick={handleClick}
						disabled={disabled}
					>
						{__('Get Transcribe', 'wubtitle')}
					</Button>
				) : (
					''
				)}
				<p>{message}</p>
			</PanelBody>
		</InspectorControls>
	);
};

export default EmbedControlPanel;
