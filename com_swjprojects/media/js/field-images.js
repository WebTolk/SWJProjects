/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
 */

document.addEventListener("DOMContentLoaded", function () {
	let imagesFields = document.querySelectorAll('[input-images="container"]');
	if (imagesFields) {
		imagesFields.forEach(function (container) {
			let enable = true;

			// Get elements
			let form = container.closest('form'),
				available = container.querySelector('[input-images="available"]'),
				idField = form.querySelector('input[name*="' + container.getAttribute('data-pk') + '"'),
				upload = container.querySelector('[input-images="upload"]'),
				field = container.querySelector('[input-images="field"]'),
				error = container.querySelector('[input-images="error"]'),
				loading = container.querySelector('[input-images="loading"]'),
				result = container.querySelector('[input-images="result"]');

			// Get params
			let id = container.getAttribute('id'),
				controller = container.getAttribute('data-controller'),
				section = container.getAttribute('data-section'),
				folder = container.getAttribute('data-folder'),
				pk = (idField) ? idField.value * 1 : 0,
				language = container.getAttribute('data-language'),
				name = container.getAttribute('data-name');

			// Set enable
			if (pk === 0) {
				available.style.display = '';
				enable = false;
			}
			if (section === '' || folder === '' || language === '' || controller === '') {
				enable = false;
			}

			if (enable) {
				upload.style.display = '';
				loadImages();

				upload.addEventListener('dragenter', preventDefaults, false);
				upload.addEventListener('dragover', preventDefaults, false);
				upload.addEventListener('dragleave', preventDefaults, false);
				upload.addEventListener('drop', preventDefaults, false);
				upload.addEventListener('dragenter', highlightUpload, false);
				upload.addEventListener('dragover', highlightUpload, false);
				upload.addEventListener('dragleave', unHighlightUpload, false);
				upload.addEventListener('drop', unHighlightUpload, false);
				upload.addEventListener('drop', function (element) {
					uploadImages(element.dataTransfer.files);
				});
				field.addEventListener('change', function (element) {
					preventDefaults(element);
					uploadImages(element.target.files);
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

			// Load result
			function loadImages() {
				error.style.display = 'none';
				loading.style.display = '';
				result.style.display = 'none';

				let request = new XMLHttpRequest(),
					requestUrl = controller,
					formData = new FormData(form);
				formData.set('task', 'images.loadImages');
				formData.set('id', id);
				formData.set('section', section);
				formData.set('pk', pk);
				formData.set('folder', folder);
				formData.set('language', language);
				formData.set('name', name);
				formData.set('values', JSON.stringify(getValues()));

				request.open('POST', requestUrl);
				request.send(formData);
				request.onreadystatechange = function () {
					if (this.readyState === 4 && this.status === 200) {
						let response = false;
						try {
							response = JSON.parse(this.response);
						} catch (e) {
							response = false;
							error.innerText = e.message;
							error.style.display = '';
							return;
						}
						if (response.success) {
							if (response.data) {
								result.innerHTML = response.data.html;
								loadActions();
								result.style.display = '';
							}
						} else {
							error.innerText = response.message;
							error.style.display = '';
						}
					} else if (this.readyState === 4 && this.status !== 200) {
						error.innerText = request.status + ' ' + request.statusText;
						error.style.display = '';
					} else if (this.readyState === 3) {
						loading.style.display = 'none';
					}
				};
			}

			// Get field values
			function uploadImages(files) {
				loading.style.display = '';
				error.style.display = 'none';
				result.style.display = 'none';
				let request = new XMLHttpRequest(),
					requestUrl = controller,
					formData = new FormData(form);
				formData.set('task', 'images.uploadImages');
				formData.set('id', id);
				formData.set('section', section);
				formData.set('pk', pk);
				formData.set('folder', folder);
				formData.set('language', language);
				formData.set('name', name);
				formData.set('values', JSON.stringify(getValues()));
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
							result.style.display = '';
							error.innerText = e.message;
							error.style.display = '';
							return;
						}
						if (response.success) {
							loadImages();
						} else {
							result.style.display = '';
							error.innerText = response.message;
							error.style.display = '';
						}
					} else if (this.readyState === 4 && this.status !== 200) {
						result.style.display = '';
						error.innerText = request.status + ' ' + request.statusText;
						error.style.display = '';
					} else if (this.readyState === 3) {
						loading.style.display = 'none';
					}
				};
			}

			// Load field images actions
			function loadActions() {
				if (result.querySelectorAll('[input-images="image"]')) {

					// Move
					dragula([result.querySelector('.images')]).on('drop', function () {
						result.querySelectorAll('[input-images="image"]').forEach(function (img, o) {
							img.querySelector(' [input-images="ordering"]').value = o + 1;
						});
					});

					result.querySelectorAll('[input-images="image"]').forEach(function (image) {
						let preview = image.querySelector('[input-images="preview"]'),
							imgLoading = image.querySelector('[input-images="image_loading"]');

						// Set preview
						if (preview.getAttribute('src') === '') {
							preview.setAttribute('src',
								image.querySelector('[input-images="noimage"]').getAttribute('src'));
							image.querySelector('[input-images="noimage"]').style.display = 'none';
							preview.style.display = '';
						}
						let current = preview.getAttribute('src');

						// View
						image.querySelector('[input-images="view"]')
							.addEventListener('click', function (button) {
								preventDefaults(button);
								openPopup(preview.getAttribute('src'), '');
							});

						// Change
						image.querySelector('[input-images="image_field"]')
							.addEventListener('change', function (element) {
								preventDefaults(element);
								preview.style.display = 'none';
								error.style.display = 'none';
								imgLoading.style.display = '';

								let request = new XMLHttpRequest(),
									requestUrl = controller,
									formData = new FormData(form);
								formData.set('task', 'images.changeImages');
								formData.set('id', id);
								formData.set('section', section);
								formData.set('pk', pk);
								formData.set('folder', folder);
								formData.set('language', language);
								formData.set('filename', element.target.getAttribute('data-key'));
								Array.from(element.target.files).forEach(function (file) {
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
											preview.setAttribute('src', current);
											preview.style.display = '';
											error.innerText = e.message;
											error.style.display = '';
											return;
										}
										if (response.success) {
											preview.setAttribute('src', (response.data) ?
												response.data : current);
											preview.style.display = '';
										} else {
											preview.setAttribute('src', current);
											error.innerText = response.message;
											error.style.display = '';
										}
									} else if (this.readyState === 4 && this.status !== 200) {
										image.setAttribute('src', current);
										preview.style.display = '';
										error.innerText = request.status + ' ' + request.statusText;
										error.style.display = '';
									} else if (this.readyState === 3) {
										imgLoading.style.display = 'none';
									}
								};
							});

						// Delete
						image.querySelector('[input-images="delete"]')
							.addEventListener('click', function (element) {
								preventDefaults(element);
								loading.style.display = '';
								error.style.display = 'none';
								result.style.display = 'none';

								let request = new XMLHttpRequest(),
									requestUrl = controller,
									formData = new FormData(form);
								formData.set('task', 'images.deleteImages');
								formData.set('id', id);
								formData.set('section', section);
								formData.set('pk', pk);
								formData.set('folder', folder);
								formData.set('language', language);
								formData.set('filename', element.target.getAttribute('data-key'));

								request.open('POST', requestUrl);
								request.send(formData);
								request.onreadystatechange = function () {
									if (this.readyState === 4 && this.status === 200) {
										let response = false;
										try {
											response = JSON.parse(this.response);
										} catch (e) {
											response = false;
											result.style.display = '';
											error.innerText = e.message;
											error.style.display = '';
											return;
										}
										if (response.success) {
											image.remove();
											loadImages();
										} else {
											preview.setAttribute('src', current);
											error.innerText = response.message;
											error.style.display = '';
										}
									} else if (this.readyState === 4 && this.status !== 200) {
										result.style.display = '';
										error.innerText = request.status + ' ' + request.statusText;
										error.style.display = '';
									} else if (this.readyState === 3) {
										loading.style.display = 'none';
									}
								};
							});
					});
				}
			}

			// Get field values
			function getValues() {
				let value = {},
					fields = form.querySelectorAll('[name*="' + name + '"]');

				if (fields) {
					fields.forEach(function (field) {
						let selector = field.getAttribute('data-key'),
							type = field.getAttribute('data-type');
						if (!value[selector]) {
							value[selector] = {};
						}

						value[selector][type] = field.value;
					});
				}

				return value;
			}
		});
	}
});