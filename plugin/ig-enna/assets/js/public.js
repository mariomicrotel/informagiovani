/* Informagiovani Enna — public.js */
(function () {
	'use strict';
	if (typeof window === 'undefined') return;

	var cfg = window.IG_ENNA || {};
	window.IG_ENNA = cfg;

	function ready(fn) {
		if (document.readyState !== 'loading') { fn(); }
		else { document.addEventListener('DOMContentLoaded', fn); }
	}
	cfg.ready = ready;

	function api(path, opts) {
		opts = opts || {};
		opts.headers = Object.assign({
			'X-WP-Nonce': cfg.nonce || '',
			'Content-Type': 'application/json'
		}, opts.headers || {});
		opts.credentials = 'same-origin';
		return fetch((cfg.restUrl || '/wp-json/ig-enna/v1/') + path, opts).then(function (r) {
			if (!r.ok) throw new Error('HTTP ' + r.status);
			return r.json();
		});
	}

	function onSaveClick(e) {
		var btn = e.currentTarget;
		var id = btn.getAttribute('data-ig-save');
		if (!id) return;
		var isSaved = btn.classList.contains('is-saved');
		btn.disabled = true;
		var p = isSaved
			? api('saves/' + id, { method: 'DELETE' })
			: api('saves/' + id, { method: 'POST' });
		p.then(function (res) {
			btn.classList.toggle('is-saved', !!res.saved);
			btn.setAttribute('aria-pressed', res.saved ? 'true' : 'false');
		}).catch(function () {
			alert(cfg.i18n && cfg.i18n.error ? cfg.i18n.error : 'Errore');
		}).then(function () {
			btn.disabled = false;
		});
	}

	/* Hamburger sitenav */
	function initHamburger() {
		var btn    = document.querySelector('.ig-enna-sitenav__hamburger');
		var drawer = document.getElementById('ig-enna-sitenav-mobile');
		if (!btn || !drawer) return;
		btn.addEventListener('click', function () {
			var open = btn.getAttribute('aria-expanded') === 'true';
			btn.setAttribute('aria-expanded', open ? 'false' : 'true');
			if (open) { drawer.setAttribute('hidden', ''); }
			else      { drawer.removeAttribute('hidden'); }
		});
		/* Chiude il drawer se si ridimensiona oltre 900px */
		window.addEventListener('resize', function () {
			if (window.innerWidth > 900) {
				btn.setAttribute('aria-expanded', 'false');
				drawer.setAttribute('hidden', '');
			}
		});
	}

	ready(function () {
		var btns = document.querySelectorAll('.ig-enna-save-btn');
		for (var i = 0; i < btns.length; i++) {
			btns[i].addEventListener('click', onSaveClick);
		}
		initHamburger();
	});
})();
