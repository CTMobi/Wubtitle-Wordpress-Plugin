/*  global wubtitle_button_object  */
import { ToggleControl, Button } from '@wordpress/components';
import { Fragment, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { langExten, statusExten } from './labels.js';
import { useDispatch } from '@wordpress/data';

const SubtitleControl = ({ statusText, langText, isPublished, postId }) => {
	const [message, setMessage] = useState('');
	const entityDispatcher = useDispatch('core');
	const updateStatus = (published) => {
		published = !published;

		let state = 'draft';
		if (published) {
			state = 'enabled';
		}

		editStatus(state);

		entityDispatcher.saveEditedEntityRecord(
			'postType',
			'attachment',
			postId
		);
	};

	const onClick = () => {
		setMessage(__('Getting transcript…', 'wubtitle'));
		wp.ajax
			.send('get_transcript_internal_video', {
				type: 'POST',
				data: {
					id: postId,
					/* eslint-disable camelcase */
					_ajax_nonce: wubtitle_button_object.ajaxnonce,
				},
			})
			.then((response) => {
				setMessage('Done');
				const selectedBlockIndex = wp.data
					.select('core/block-editor')
					.getBlockIndex(
						wp.data.select('core/block-editor').getSelectedBlock()
							.clientId
					);
				const block = wp.blocks.createBlock('wubtitle/transcription', {
					contentId: response,
				});
				wp.data
					.dispatch('core/block-editor')
					.insertBlocks(block, selectedBlockIndex + 1);
			})
			.fail((response) => {
				setMessage(response);
			});
	};

	const editStatus = (statusToEdit) => {
		entityDispatcher.editEntityRecord('postType', 'attachment', postId, {
			meta: { wubtitle_status: statusToEdit },
		});
	};

	return (
		<Fragment>
			<p style={{ margin: '0' }}>
				{__('Status:', 'wubtitle') + ' ' + statusExten[statusText]}
			</p>
			<p style={{ margin: '8px 0' }}>
				{__('Language:', 'wubtitle') + ' ' + langExten[langText]}
			</p>
			<ToggleControl
				label={__('Published', 'wubtitle')}
				checked={isPublished}
				onChange={() => {
					updateStatus(isPublished);
				}}
			/>
			<Button name="sottotitoli" id={postId} isPrimary onClick={onClick}>
				{__('Get Transcribe', 'wubtitle')}
			</Button>
			<p>{message}</p>
		</Fragment>
	);
};

export default SubtitleControl;
