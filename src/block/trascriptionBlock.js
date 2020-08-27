import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import TranscriptionEditBlock from './TranscriptionEditBlock';

registerBlockType('wubtitle/transcription', {
	title: __('Trascription', 'wubtitle'),
	icon: 'megaphone',
	description: __('Enter the transcript of your video', 'wubtitle'),
	category: 'embed',
	attributes: {
		contentId: {
			type: 'int',
		},
	},
	save: () => {
		return null;
	},
	edit: TranscriptionEditBlock,
});
