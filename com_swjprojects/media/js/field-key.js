/**
 * @package       SW JProjects
 * @version       2.5.0-alhpa1
 * @Author        Sergey Tolkachyov
 * @copyright     Copyright (c) 2018 - 2025 Sergey Tolkachyov. All rights reserved.
 * @license       GNU/GPL3 http://www.gnu.org/licenses/gpl-3.0.html
 * @link       https://web-tolk.ru
 * @since         1.0.0
 */

document.addEventListener("DOMContentLoaded", function () {
	let keysFields = document.querySelectorAll('[input-key="container"]');
	if (keysFields) {
		keysFields.forEach(function (container) {
			let show = container.querySelector('[input-key="show"]'),
				generate = container.querySelector('[input-key="generate"]'),
				field = container.querySelector('[input-key="field"]'),
				key = container.querySelector('[input-key="key"]'),
				length = container.getAttribute('data-length') * 1;

			let characters = false;
			try {
				characters = JSON.parse(container.getAttribute('data-characters'));
				if (typeof  characters === 'object') {
					characters = Object.values(characters);
				}
			} catch (e) {
				characters = false;
				console.error(e.message);
			}

			// Show key
			show.addEventListener('click', function (element) {
				element.preventDefault();
				key.innerText = field.value;
				key.style.display = '';
			});

			// Generate
			generate.addEventListener('click', function (element) {
				element.preventDefault();
				key.innerText = '';
				key.style.display = 'none';
				if (characters && length > 0) {
					let secret = [];
					for (let i = 1; i <= length; i++) {
						let j = (Math.random() * (characters.length - 1)).toFixed();
						secret[i] = characters[j];
					}
					field.value = secret.join('');
				} else {
					console.error('Incorrect params');
				}
			});
		});
	}
});
