/* global wubtitle_object_modal */
document.addEventListener("DOMContentLoaded", function() {
	const button = document.getElementById("insert-my-media");
	if (button) {
		button.addEventListener("click", () => {
			wp.media.frame.detach();
			wp.media.editor.remove();
			openMediaWindow();
		});
	}
	const windowTrascriptions = wp.media({
		frame: "post",
		state: "embed"
	});
	windowTrascriptions.on("select", () => {
		const embedUrl = document.getElementById("embed-url-field").value;
		const languageSubtitle = document.getElementById(
			"transcript-select-lang"
		).value;
		if (languageSubtitle === "") {
			wp.media.editor.insert(
				"<p style='color:red'> Error, language not selected </p>"
			);
			return;
		}
		wp.ajax
			.send("get_transcript_yt", {
				type: "POST",
				data: {
					_ajax_nonce: wubtitle_object_modal.ajaxnonce,
					urlVideo: embedUrl,
					urlSubtitle: languageSubtitle,
					videoTitle
				}
			})
			.then(response => {
				wp.media.editor.insert(
					`[embed]  ${embedUrl} [/embed]
					<p> ${response} </p>`
				);
			})
			.fail(response => {
				wp.media.editor.insert(
					"<p style='color:red'>" + response + "</p>"
				);
			});
	});

	let isOpened = false;
	let videoTitle;
	const getLanguages = inputUrl => {
		wp.ajax
			.send("get_video_info", {
				type: "POST",
				data: {
					_ajax_nonce: wubtitle_object_modal.ajaxnonce,
					url: inputUrl
				}
			})
			.then(response => {
				document.getElementById(
					"transcript-select-lang"
				).innerHTML = `<option value="">${wp.i18n.__(
					"Select language",
					"ear2words"
				)}</option>`;
				videoTitle = response.title;
				response.languages.forEach(subtitle => {
					document.getElementById(
						"transcript-select-lang"
					).innerHTML += `<option value=${subtitle.baseUrl}>${subtitle.name.simpleText}</option>`;
				});
			})
			.fail(() => {
				document.getElementById(
					"transcript-select-lang"
				).innerHTML = `<option value="">${wp.i18n.__(
					"Select language",
					"ear2words"
				)}</option>`;
			});
	};

	const openMediaWindow = () => {
		windowTrascriptions.open();
		const inputUrl = document.getElementById("embed-url-field");
		inputUrl.addEventListener("change", () => {
			if (inputUrl.value === "") {
				document.getElementById(
					"transcript-select-lang"
				).innerHTML = `<option value="">${wp.i18n.__(
					"Select language",
					"ear2words"
				)}</option>`;
			}
			getLanguages(inputUrl.value);
		});

		if (!isOpened) {
			const divModal = document.getElementsByClassName(
				"embed-link-settings"
			);
			const header = document.createElement("h2");
			const textHeader = document.createTextNode(
				wp.i18n.__("Language of trascription ", "ear2words")
			);
			header.appendChild(textHeader);
			const select = document.createElement("SELECT");
			select.setAttribute("id", "transcript-select-lang");
			select.innerHTML = `<option value="">${wp.i18n.__(
				"Select language",
				"ear2words"
			)}</option>`;
			divModal[0].appendChild(header);
			divModal[0].appendChild(select);

			document.getElementById("menu-item-insert").remove();
			document.getElementById("menu-item-gallery").remove();
			document.getElementById("menu-item-playlist").remove();
			document.getElementById("menu-item-video-playlist").remove();
			document.getElementById("menu-item-featured-image").remove();
			document.getElementById("menu-item-embed").innerHTML = wp.i18n.__(
				"Wubtitle Transcription",
				"ear2words"
			);
			document.getElementById("media-frame-title").innerHTML =
				"<h1>" +
				wp.i18n.__("Wubtitle Transcription", "ear2words") +
				"</h1>";
			isOpened = true;
		}
	};
	return false;
});
