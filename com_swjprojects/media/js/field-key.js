/*
 * @package    SW JProjects Component
 * @version    1.6.4
 * @author Septdir Workshop, <https://septdir.com>, Sergey Tolkachyov <https://web-tolk.ru>
 * @сopyright (c) 2018 - April 2023 Septdir Workshop, Sergey Tolkachyov. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link https://septdir.com, https://web-tolk.ru
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
