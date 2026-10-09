/**
 * PrintPop — продуктова страница: раздели за медия, количество, конфигуратор,
 * добавяне в количката без презареждане и потвърждение.
 */
(function () {
	'use strict';

	var PP = window.PrintPop;
	var data = (PP && PP.data) || window.printpopData || { i18n: {} };
	var t = data.i18n || {};

	/* Раздели: снимки / видео / 3D --------------------------------------- */
	var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-pp-tab]'));
	function selectTab(tab) {
		tabs.forEach(function (b) {
			var on = b === tab;
			b.setAttribute('aria-selected', on ? 'true' : 'false');
			b.tabIndex = on ? 0 : -1;
			var panel = document.getElementById(b.getAttribute('aria-controls'));
			if (panel) { panel.hidden = !on; }
			if (!on && panel) {
				var v = panel.querySelector('video');
				if (v) { v.pause(); }
			}
		});
	}
	tabs.forEach(function (b, i) {
		b.addEventListener('click', function () { selectTab(b); });
		b.addEventListener('keydown', function (e) {
			var n = null;
			if (e.key === 'ArrowRight') { n = tabs[(i + 1) % tabs.length]; }
			if (e.key === 'ArrowLeft') { n = tabs[(i - 1 + tabs.length) % tabs.length]; }
			if (n) { e.preventDefault(); n.focus(); selectTab(n); }
		});
	});

	/* Количество: − / + ---------------------------------------------------- */
	var form = document.querySelector('form.cart');
	var qtyInput = form && form.querySelector('input.qty');
	if (qtyInput && !qtyInput.closest('.pp-qty')) {
		var wrap = document.createElement('div');
		wrap.className = 'pp-qty';
		qtyInput.parentNode.insertBefore(wrap, qtyInput);

		var mk = function (label, path, dir) {
			var b = document.createElement('button');
			b.type = 'button';
			b.setAttribute('aria-label', label);
			b.innerHTML = '<svg class="pp-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="' + path + '"/></svg>';
			b.addEventListener('click', function () {
				var step = parseFloat(qtyInput.step) || 1;
				var min = qtyInput.min !== '' ? parseFloat(qtyInput.min) : 1;
				var max = qtyInput.max !== '' && parseFloat(qtyInput.max) > 0 ? parseFloat(qtyInput.max) : Infinity;
				var val = (parseFloat(qtyInput.value) || min) + dir * step;
				qtyInput.value = Math.max(min, Math.min(max, val));
				qtyInput.dispatchEvent(new Event('change', { bubbles: true }));
			});
			return b;
		};
		wrap.appendChild(mk('−', 'M5 12h14', -1));
		wrap.appendChild(qtyInput);
		wrap.appendChild(mk('+', 'M12 5v14M5 12h14', 1));
	}

	/* Конфигуратор --------------------------------------------------------- */
	var cfg = document.querySelector('[data-pp-config]');
	if (cfg) {
		var inputs = {
			w: cfg.querySelector('#pp_w'),
			h: cfg.querySelector('#pp_h'),
			d: cfg.querySelector('#pp_d')
		};
		var presets = cfg.querySelectorAll('input[name="pp_size"]');
		var note = cfg.querySelector('[data-pp-distort]');
		var noteText = note && note.querySelector('span');
		var totalEl = cfg.querySelector('[data-pp-total]');
		var colorName = cfg.querySelector('[data-pp-color-name]');
		var unit = parseFloat(cfg.getAttribute('data-unit-price'));
		var currency = cfg.getAttribute('data-currency') || '';
		var base = (cfg.getAttribute('data-base') || '').split(',').map(parseFloat);
		var hasBase = base.length === 3 && base.every(function (n) { return n > 0; });

		var fmt = function (n) {
			try {
				return new Intl.NumberFormat(document.documentElement.lang || 'bg', { style: 'currency', currency: currency }).format(n);
			} catch (e) {
				return n.toFixed(2) + ' ' + currency;
			}
		};

		var num = function (el) { return el ? parseFloat(String(el.value).replace(',', '.')) : NaN; };
		var touched = false;

		function checkDistortion() {
			if (!note) { return; }
			var w = num(inputs.w), h = num(inputs.h), d = num(inputs.d);
			var warn = false;
			if (hasBase && w > 0 && h > 0 && d > 0) {
				var rs = [w / base[0], h / base[1], d / base[2]];
				var min = Math.min.apply(null, rs), max = Math.max.apply(null, rs);
				warn = (max / min) > 1.02;
			} else if (!hasBase) {
				warn = touched;
			}
			note.classList.toggle('is-warning', warn);
			if (noteText) { noteText.textContent = warn ? (t.distortWarning || '') : (t.distortHint || noteText.textContent); }
		}

		function updateTotal() {
			var qty = qtyInput ? (parseFloat(qtyInput.value) || 1) : 1;
			if (totalEl && !isNaN(unit)) { totalEl.textContent = fmt(unit * qty); }
		}

		function emit() {
			var val = function (name) {
				var c = cfg.querySelector('input[name="' + name + '"]:checked');
				return c ? c.value : null;
			};
			document.dispatchEvent(new CustomEvent('printpop:config', {
				detail: {
					productId: parseInt(cfg.getAttribute('data-product-id'), 10),
					size: { w: num(inputs.w), h: num(inputs.h), d: num(inputs.d) },
					material: val('pp_material'),
					color: val('pp_color'),
					quality: val('pp_quality'),
					quantity: qtyInput ? parseFloat(qtyInput.value) || 1 : 1
				}
			}));
		}

		presets.forEach(function (r) {
			r.addEventListener('change', function () {
				if (!r.checked || r.value === 'custom') { return; }
				inputs.w.value = r.dataset.w;
				inputs.h.value = r.dataset.h;
				inputs.d.value = r.dataset.d;
				touched = false;
				checkDistortion();
				emit();
			});
		});

		['w', 'h', 'd'].forEach(function (k) {
			inputs[k].addEventListener('input', function () {
				touched = true;
				var custom = cfg.querySelector('input[name="pp_size"][value="custom"]');
				if (custom) { custom.checked = true; }
				checkDistortion();
				emit();
			});
		});

		cfg.addEventListener('change', function (e) {
			if (e.target.name === 'pp_color' && colorName) { colorName.textContent = e.target.dataset.name || ''; }
			emit();
		});
		var firstColor = cfg.querySelector('input[name="pp_color"]:checked');
		if (firstColor && colorName) { colorName.textContent = firstColor.dataset.name || ''; }

		if (qtyInput) {
			['input', 'change'].forEach(function (ev) { qtyInput.addEventListener(ev, function () { updateTotal(); emit(); }); });
		}
		updateTotal();
		checkDistortion();
	}

	/* Добавяне в количката без презареждане ------------------------------- */
	if (!form) { return; }
	var submitBtn = form.querySelector('.single_add_to_cart_button');

	function refreshFragments() {
		if (!data.fragmentsUrl) { return; }
		fetch(data.fragmentsUrl, { method: 'POST', credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (json) {
				if (!json || !json.fragments) { return; }
				Object.keys(json.fragments).forEach(function (sel) {
					document.querySelectorAll(sel).forEach(function (node) {
						var tmp = document.createElement('div');
						tmp.innerHTML = json.fragments[sel];
						if (tmp.firstElementChild) { node.replaceWith(tmp.firstElementChild); }
					});
				});
			})
			.catch(function () { /* броячът ще се обнови при следващото зареждане */ });
	}

	form.addEventListener('submit', function (e) {
		if (!window.fetch || !window.FormData || !submitBtn || submitBtn.disabled) { return; }
		e.preventDefault();

		var fd = new FormData(form);
		if (submitBtn.name && !fd.has(submitBtn.name)) { fd.append(submitBtn.name, submitBtn.value); }

		submitBtn.classList.add('is-loading');
		submitBtn.setAttribute('aria-busy', 'true');

		fetch(form.getAttribute('action') || window.location.href, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function (res) {
				var redirectedToCart = data.cartUrl && res.url && res.url.indexOf(data.cartUrl.replace(/\/$/, '')) === 0;
				return res.text().then(function (html) { return { html: html, redirectedToCart: redirectedToCart, ok: res.ok }; });
			})
			.then(function (r) {
				var doc = new DOMParser().parseFromString(r.html, 'text/html');
				var errors = doc.querySelectorAll('.woocommerce-error li, .woocommerce-error');
				var seen = [];
				var firstError = null;
				errors.forEach(function (li) {
					var msg = li.textContent.trim().replace(/\s+/g, ' ');
					if (msg && seen.indexOf(msg) === -1 && !li.querySelector('li')) { seen.push(msg); firstError = firstError || msg; }
				});

				if (!r.ok || seen.length) {
					if (PP) { PP.toast({ message: seen.join(' ') || t.addError || '', icon: 'info', timeout: 8000 }); }
					return;
				}

				refreshFragments();
				submitBtn.classList.add('is-added');
				setTimeout(function () { submitBtn.classList.remove('is-added'); }, 2500);

				if (PP) {
					PP.toast({
						message: t.added || '',
						actions: [
							{ label: t.toCart || '', href: data.cartUrl || '#' },
							{ label: t.keepBrowsing || '' }
						],
						timeout: 9000
					});
				}
			})
			.catch(function () {
				// Резервен вариант: обикновено изпращане на формата.
				form.submit();
			})
			.then(function () {
				submitBtn.classList.remove('is-loading');
				submitBtn.removeAttribute('aria-busy');
			});
	});
})();
