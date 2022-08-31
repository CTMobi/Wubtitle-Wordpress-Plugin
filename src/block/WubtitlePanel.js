/*  global wubtitle_button_object  */
import { useSelect, useDispatch, useEffect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { PanelBody, Button, SelectControl } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { useState, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import PendingSubtitle from './PendingSubtitle';
import SubtitleControl from './SubtitleControl';
import { selectOptions, allLanguages, languagesFree } from './labels';

const WubtitlePanel = (props) => {
	const extensionsFile =
		props.id !== undefined
			? props.src.substring(props.src.lastIndexOf('.') + 1)
			: 'mp4';
	/* eslint-disable camelcase */
	const languages =
		wubtitle_button_object.isFree === '1' ? languagesFree : allLanguages;
	/* eslint-disable camelcase */
	const lang = languages.includes(wubtitle_button_object.lang)
		? wubtitle_button_object.lang
		: 'en-US';
	const metaValues = useSelect((select) => {
		let attachment;
		if (props.id !== undefined) {
			attachment = select('core').getEntityRecord(
				'postType',
				'attachment',
				props.id
			);
		}
		let meta = '';
		if (attachment !== undefined) {
			meta = select('core').getEditedEntityRecord(
				'postType',
				'attachment',
				props.id
			).meta;
		}

		return meta;
	});

	let languageSaved;
	if (metaValues !== undefined) {
		languageSaved = metaValues.wubtitle_lang_video;
	}
	const noticeDispatcher = useDispatch('core/notices');
	const entityDispatcher = useDispatch('core');
	const [languageSelected, setLanguage] = useState(lang);
	const [isLoading, setIsLoading] = useState(false);
	const [status, setStatus] = useState(metaValues?.wubtitle_status);

	useEffect(() => {
		setStatus(metaValues?.wubtitle_status);
	}, [metaValues?.wubtitle_status]);

	const isDisabled =
		status === 'pending' || props.id === undefined || isLoading;
	const isPublished = status === 'enabled';
	const GenerateSubtitles = () => {
		const statusLabel =
			status === 'error'
				? __('Error', 'wubtitle')
				: __('None', 'wubtitle');
		return (
			<Fragment>
				<div>{__('Status:', 'wubtitle') + ' ' + statusLabel}</div>
				<SelectControl
					label={__('Select the video language', 'wubtitle')}
					value={languageSelected}
					onChange={(lingua) => {
						setLanguage(lingua);
					}}
					options={selectOptions}
				/>
				<Button
					disabled={isDisabled}
					name="sottotitoli"
					id={props.id}
					isPrimary
					onClick={onClick}
				>
					{__('GENERATE SUBTITLES', 'wubtitle')}
				</Button>
			</Fragment>
		);
	};

	const FormatNotSupported = () => (
		<Fragment>
			<div>
				{__('Unsupported video format for free plan', 'wubtitle')}
			</div>
		</Fragment>
	);

	function onClick() {
		const idAttachment = props.id;
		const srcAttachment = props.src;
		setIsLoading(true);
		apiFetch({
			/* eslint-disable camelcase */
			url: wubtitle_button_object.ajax_url,
			method: 'POST',
			headers: {
				'Content-Type':
					'application/x-www-form-urlencoded; charset=utf-8',
			},
			/* eslint-disable camelcase */
			body: `action=submitVideo&_ajax_nonce=${wubtitle_button_object.ajaxnonce}&id_attachment=${idAttachment}&src_attachment=${srcAttachment}&lang=${languageSelected}&`,
		}).then((res) => {
			setIsLoading(false);
			if (res.data === 201) {
				noticeDispatcher.createNotice(
					'success',
					__('Subtitle creation successfully started', 'wubtitle')
				);
				entityDispatcher.editEntityRecord(
					'postType',
					'attachment',
					props.id,
					{
						meta: {
							wubtitle_status: 'pending',
							wubtitle_lang_video: languageSelected,
						},
					}
				);
			} else {
				noticeDispatcher.createNotice('error', res.data);
			}
		});
	}

	const WubtitlePanelContent = () => {
		/* eslint-disable camelcase */
		if (wubtitle_button_object.isFree === '1' && extensionsFile !== 'mp4') {
			return <FormatNotSupported />;
		}
		switch (status) {
			case 'pending':
				return (
					<PendingSubtitle
						langText={languageSaved}
						statusText={status}
					/>
				);
			case 'draft':
			case 'enabled':
				return (
					<SubtitleControl
						statusText={status}
						langText={languageSaved}
						isPublished={isPublished}
						postId={props.id}
					/>
				);
			default:
				return <GenerateSubtitles />;
		}
	};

	return (
		<InspectorControls>
			<PanelBody title="Wubtitle">
				<WubtitlePanelContent
					status={status}
					languageSaved={languageSaved}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default WubtitlePanel;
