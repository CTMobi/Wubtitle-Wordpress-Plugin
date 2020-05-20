/*  global ear2words_button_object  */
import { useSelect, useDispatch } from "@wordpress/data";
import apiFetch from "@wordpress/api-fetch";
import { PanelBody, Button, SelectControl } from "@wordpress/components";
import { InspectorControls } from "@wordpress/block-editor";
import { useState, Fragment } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import PendingSubtitle from "./PendingSubtitle";
import SubtitleControl from "./SubtitleControl";
import { selectOptions, selectOptionsFreePlan } from "./labels";

const Ear2WordPanel = props => {
	const extensionsFile =
		props.id !== undefined
			? props.src.substring(props.src.lastIndexOf(".") + 1)
			: "mp4";
	const languages =
		ear2words_button_object.isFree === "1"
			? ["it", "en"]
			: ["it", "en", "es", "de", "zh"];
	const lang = languages.includes(ear2words_button_object.lang)
		? ear2words_button_object.lang
		: "en";
	const metaValues = useSelect(select => {
		let attachment;
		if (props.id !== undefined) {
			attachment = select("core").getEntityRecord(
				"postType",
				"attachment",
				props.id
			);
		}
		let meta = "";
		if (attachment !== undefined) {
			meta = select("core").getEditedEntityRecord(
				"postType",
				"attachment",
				props.id
			).meta;
		}

		return meta;
	});

	let languageSaved;
	let status;
	if (metaValues !== undefined) {
		languageSaved = metaValues.ear2words_lang_video;
		status = metaValues.ear2words_status;
	}
	const noticeDispatcher = useDispatch("core/notices");
	const entityDispatcher = useDispatch("core");
	const [languageSelected, setLanguage] = useState(lang);
	const isDisabled = status === "pending" || props.id === undefined;
	const isPublished = status === "enabled";
	const optionLanguage =
		ear2words_button_object.isFree === "1"
			? selectOptionsFreePlan
			: selectOptions;
	const GenerateSubtitles = () => {
		status =
			status === "error"
				? __("Error", "ear2words")
				: __("None", "ear2words");
		return (
			<Fragment>
				<div>{__("Status: ", "ear2words") + status}</div>
				<SelectControl
					label={__("Select the video language", "ear2words")}
					value={languageSelected}
					onChange={lingua => {
						setLanguage(lingua);
					}}
					options={optionLanguage}
				/>
				<Button
					disabled={isDisabled}
					name="sottotitoli"
					id={props.id}
					isPrimary
					onClick={onClick}
				>
					{__("GENERATE SUBTITLES", "ear2words")}
				</Button>
			</Fragment>
		);
	};

	const FormatNotSupported = () => (
		<Fragment>
			<div>
				{__("Unsupported video format for free plan", "ear2words")}
			</div>
		</Fragment>
	);

	function onClick() {
		const idAttachment = props.id;
		const srcAttachment = props.src;
		apiFetch({
			url: ear2words_button_object.ajax_url,
			method: "POST",
			headers: {
				"Content-Type":
					"application/x-www-form-urlencoded; charset=utf-8"
			},
			body: `action=submitVideo&_ajax_nonce=${ear2words_button_object.ajaxnonce}&id_attachment=${idAttachment}&src_attachment=${srcAttachment}&lang=${languageSelected}&`
		}).then(res => {
			if (res.data === 201) {
				noticeDispatcher.createNotice(
					"success",
					__("Subtitle creation successfully started", "ear2words")
				);
				entityDispatcher.editEntityRecord(
					"postType",
					"attachment",
					props.id,
					{
						meta: {
							ear2words_status: "pending",
							ear2words_lang_video: languageSelected
						}
					}
				);
			} else {
				noticeDispatcher.createNotice("error", res.data);
			}
		});
	}

	const Ear2wordsPanelContent = () => {
		if (
			ear2words_button_object.isFree === "1" &&
			extensionsFile !== "mp4"
		) {
			return <FormatNotSupported />;
		}
		switch (status) {
			case "pending":
				return (
					<PendingSubtitle
						langText={languageSaved}
						statusText={status}
					/>
				);
			case "draft":
			case "enabled":
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
				<Ear2wordsPanelContent
					status={status}
					languageSaved={languageSaved}
				/>
			</PanelBody>
		</InspectorControls>
	);
};

export default Ear2WordPanel;
