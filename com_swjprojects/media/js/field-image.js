/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

document.addEventListener("DOMContentLoaded", function () {
	let imageFields = document.querySelectorAll('[input-image="container"]');
	if (imageFields) {
		imageFields.forEach(function (container) {
			let enable = true;

			// Get elements
			let form = container.closest('form'),
				idField = form.querySelector('input[name*="' + container.getAttribute('data-pk') + '"'),
				available = container.querySelector('[input-image="available"]'),
				upload = container.querySelector('[input-image="upload"]'),
				field = container.querySelector('[input-image="field"]'),
				image = container.querySelector('[input-image="preview"]'),
				error = container.querySelector('[input-image="error"]'),
				deleteButton = container.querySelector('[input-image="delete"]'),
				viewButton = container.querySelector('[input-image="view"]');

			// Set noimage attr
			image.setAttribute('data-noimage', image.getAttribute('src'));

			// Get params
			let controller = container.getAttribute('data-controller'),
				section = container.getAttribute('data-section'),
				filename = container.getAttribute('data-filename'),
				pk = (idField) ? idField.value * 1 : 0,
				language = container.getAttribute('data-language'),
				loading = image.getAttribute('data-loading'),
				noimage = image.getAttribute('data-noimage');

			// Set enable
			if (pk === 0) {
				available.style.display = '';
				enable = false;
			}
			if (section === '' || filename === '' || language === '' || controller === '') {
				enable = false;
			}

			if (enable) {
				upload.style.display = '';
				loadImage();

				deleteButton.addEventListener('click', function (element) {
					preventDefaults(element);
					deleteImage();
				});

				viewButton.addEventListener('click', function (element) {
					preventDefaults(element);
					viewImage();
				});

				upload.addEventListener('dragenter', preventDefaults, false);
				upload.addEventListener('dragover', preventDefaults, false);
				upload.addEventListener('dragleave', preventDefaults, false);
				upload.addEventListener('drop', preventDefaults, false);
				upload.addEventListener('dragenter', highlightUpload, false);
				upload.addEventListener('dragover', highlightUpload, false);
				upload.addEventListener('dragleave', unHighlightUpload, false);
				upload.addEventListener('drop', unHighlightUpload, false);
				upload.addEventListener('drop', function (element) {
					uploadImage(element.dataTransfer.files);
				});
				field.addEventListener('change', function (element) {
					preventDefaults(element);
					uploadImage(element.target.files);
				});

			}

			// Stop function
			function preventDefaults(e) {
				e.preventDefault();
				e.stopPropagation();
			}

			// Set highlight
			function highlightUpload() {
				upload.classList.add('dragend')
			}

			// Unset highlight
			function unHighlightUpload() {
				upload.classList.remove('dragend')
			}

			// Load image
			function loadImage() {
				image.setAttribute('src', loading);
				error.style.display = 'none';

				let request = new XMLHttpRequest(),
					requestUrl = controller,
					formData = new FormData(form);
				formData.set('task', 'images.loadImage');
				formData.set('section', section);
				formData.set('pk', pk);
				formData.set('filename', filename);
				formData.set('language', language);

				request.open('POST', requestUrl);
				request.send(formData);
				request.onreadystatechange = function () {
					if (this.readyState === 4 && this.status === 200) {
						let response = false;
						try {
							response = JSON.parse(this.response);
						} catch (e) {
							response = false;
							image.setAttribute('src', noimage);
							error.innerText = e.message;
							error.style.display = '';
							return;
						}
						if (response.success) {
							image.setAttribute('src', (response.data) ? response.data : noimage);
						} else {
							image.setAttribute('src', noimage);
							error.innerText = response.message;
							error.style.display = '';
						}
					} else if (this.readyState === 4 && this.status !== 200) {
						image.setAttribute('src', noimage);
						error.innerText = request.status + ' ' + request.statusText;
						error.style.display = '';
					}
				};
			}

			// Delete image
			function deleteImage() {
				let current = image.getAttribute('src');
				image.setAttribute('src', loading);
				error.style.display = 'none';

				let request = new XMLHttpRequest(),
					requestUrl = controller,
					formData = new FormData(form);
				formData.set('task', 'images.deleteImage');
				formData.set('section', section);
				formData.set('pk', pk);
				formData.set('filename', filename);
				formData.set('language', language);

				request.open('POST', requestUrl);
				request.send(formData);
				request.onreadystatechange = function () {
					if (this.readyState === 4 && this.status === 200) {
						let response = false;
						try {
							response = JSON.parse(this.response);
						} catch (e) {
							response = false;
							image.setAttribute('src', current);
							error.innerText = e.message;
							error.style.display = '';
							return;
						}
						if (response.success) {
							image.setAttribute('src', (response.data) ? noimage : current);
						} else {
							image.setAttribute('src', current);
							error.innerText = response.message;
							error.style.display = '';
						}
					} else if (this.readyState === 4 && this.status !== 200) {
						image.setAttribute('src', current);
						error.innerText = request.status + ' ' + request.statusText;
						error.style.display = '';
					}
				};
			}

			// Upload image
			function uploadImage(files) {
				let current = image.getAttribute('src');
				image.setAttribute('src', loading);
				error.style.display = 'none';
				let request = new XMLHttpRequest(),
					requestUrl = controller,
					formData = new FormData(form);
				formData.set('task', 'images.uploadImage');
				formData.set('section', section);
				formData.set('pk', pk);
				formData.set('filename', filename);
				formData.set('language', language);
				Array.from(files).forEach(function (file) {
					formData.append('images[]', file);
				});

				request.open('POST', requestUrl);
				request.send(formData);
				request.onreadystatechange = function () {
					if (this.readyState === 4 && this.status === 200) {
						let response = false;
						try {
							response = JSON.parse(this.response);
						} catch (e) {
							response = false;
							image.setAttribute('src', current);
							error.innerText = e.message;
							error.style.display = '';
							return;
						}
						if (response.success) {
							image.setAttribute('src', (response.data) ? response.data : current);
						} else {
							image.setAttribute('src', current);
							error.innerText = response.message;
							error.style.display = '';
						}
					} else if (this.readyState === 4 && this.status !== 200) {
						image.setAttribute('src', current);
						error.innerText = request.status + ' ' + request.statusText;
						error.style.display = '';
					}
				};
			}

			// View image
			function viewImage() {
				openPopup(image.getAttribute('src'), '');
			}
		});
	}
});
