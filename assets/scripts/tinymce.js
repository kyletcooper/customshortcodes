(function () {

	var shortcodePickerPosts = [];

	async function shortcodePicker() {
		const dialog = await _shortcodePicker__createMarkup();
		dialog.showModal();

		return new Promise((resolve, reject) => {
			dialog.addEventListener('close', () => {
				if (dialog.returnValue === "confirm") {
					const value = _shortcodePicker__getSelected(dialog);
					resolve(value);
				}
				else {
					reject(null);
				}
			})
		});
	}

	async function _shortcodePicker__createMarkup(numItems = 50) {
		const dialog = document.createElement("dialog");
		dialog.classList.add('shortcodePicker');

		const markup = `
			<form class="shortcodePicker__form" method="dialog">
				<header class="shortcodePicker__header">
					<h2 class="shortcodePicker__title">
						Choose a Custom Shortcode
					</h2>
				</header>

				<main class="shortcodePicker__body">
					<a href="${ajaxurl.replace('admin-ajax.php', 'edit.php?post_type=wrd_shortcode')}" target="_blank" class="shortcodePicker__new">Create new shortcode</a>
					
					<div class="shortcodePicker__choices">
						${shortcodePickerPosts.map(_shortcodePicker__getItemMarkup)}
					</div>
				</main>

				<footer class="shortcodePicker__footer">
					<button data-shortcodepicker-confirm class="button-primary" value="confirm" disabled>
						Insert Shortcode
					</button>

					<button class="button-secondary" value="cancel">
						Cancel
					</button>
				</footer>
			</form>
		`;

		dialog.innerHTML = markup;
		document.body.append(dialog);

		_shortcodePicker__populateItems(dialog, numItems);

		const confirmButton = dialog.querySelector('[data-shortcodepicker-confirm]');
		dialog.addEventListener('change', () => {
			confirmButton?.toggleAttribute('disabled', !_shortcodePicker__getSelected(dialog));
		});

		return dialog;
	}

	async function _shortcodePicker__populateItems(dialog, numItems) {
		const posts = await _shortcodePicker__getPosts(numItems);
		const container = dialog.querySelector(".shortcodePicker__choices");
		container.innerHTML = posts.map(_shortcodePicker__getItemMarkup);
	}

	async function _shortcodePicker__getSelected(dialog) {
		const selectedElement = dialog.querySelector("[data-shortcodepicker-option]:checked");
		return selectedElement.value;
	}

	async function _shortcodePicker__getPosts(numItems) {
		if (shortcodePickerPosts.length !== numItems) {
			shortcodePickerPosts = await wp.apiRequest({ path: `/wp/v2/wrd_shortcode?per_page=${numItems}` });
		}

		return shortcodePickerPosts;
	}

	function _shortcodePicker__getItemMarkup(post) {
		return `
			<label class='shortcodePicker__item'>
				<input data-shortcodepicker-option class='shortcodePicker__item__input' type='radio' name='shortcode' value='[${post.title.rendered}]'>
				<span class='shortcodePicker__item__label'>
					[${post.title.rendered}]
				</span>
			</label>
		`;
	}



	tinymce.create('tinymce.plugins.customshortcodes', {
		init: function (ed, url) {
			ed.addButton('customshortcodes', {
				title: 'Custom Shortcodes',
				cmd: 'customshortcodes',
				image: url + '/shortcode.png'
			});

			ed.addCommand('customshortcodes', async function () {
				shortcodePicker()
					.then(shortcode => {
						ed.execCommand('mceInsertContent', 0, shortcode);
					})
					.catch(() => {

					});

			});
		},

		getInfo: function () {
			return {
				longname: 'Custom Shortcodes',
				author: 'Web Results Direct',
				authorurl: 'https://wrd.studio',
				infourl: 'https://wrd.studio',
				version: '1.0'
			};
		}
	});

	tinymce.PluginManager.add('customshortcodes', tinymce.plugins.customshortcodes);
})();