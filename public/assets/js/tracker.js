(function () {
  'use strict';

  var ENDPOINT = '/api/v1/events';
  var QUEUE = [];
  var FLUSH_MS = 1200;
  var flushTimer = null;

  function uuid() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      var r = (Math.random() * 16) | 0;
      var v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
  }

  function getVisitorId() {
    try {
      var key = 'cpb_vid';
      var id = localStorage.getItem(key);
      if (!id) {
        id = uuid();
        localStorage.setItem(key, id);
      }
      return id;
    } catch (e) {
      return uuid();
    }
  }

  function getSessionId() {
    try {
      var key = 'cpb_sid';
      var metaKey = 'cpb_sid_ts';
      var now = Date.now();
      var ts = parseInt(sessionStorage.getItem(metaKey) || '0', 10);
      var id = sessionStorage.getItem(key);
      if (!id || !ts || now - ts > 30 * 60 * 1000) {
        id = uuid();
        sessionStorage.setItem(key, id);
      }
      sessionStorage.setItem(metaKey, String(now));
      return id;
    } catch (e) {
      return uuid();
    }
  }

  function utmParams() {
    var out = {};
    try {
      var q = new URLSearchParams(window.location.search);
      ['utm_source', 'utm_medium', 'utm_campaign'].forEach(function (k) {
        var v = q.get(k);
        if (v) out[k] = v;
      });
    } catch (e) {}
    return out;
  }

  function csrfToken() {
    try {
      var meta = document.querySelector('meta[name="csrf-token"]');
      return (meta && meta.getAttribute('content')) || window.__CSRF_TOKEN__ || '';
    } catch (e) {
      return '';
    }
  }

  function flush() {
    flushTimer = null;
    if (!QUEUE.length) return;
    var batch = QUEUE.splice(0, QUEUE.length);
    var body = JSON.stringify({
      _csrf: csrfToken(),
      visitor_id: getVisitorId(),
      session_id: getSessionId(),
      events: batch,
    });
    try {
      if (navigator.sendBeacon) {
        var blob = new Blob([body], { type: 'application/json' });
        if (navigator.sendBeacon(ENDPOINT, blob)) return;
      }
    } catch (e) {}
    try {
      fetch(ENDPOINT, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': csrfToken(),
        },
        body: body,
        keepalive: true,
        credentials: 'same-origin',
      }).catch(function () {});
    } catch (e) {}
  }

  function scheduleFlush() {
    if (flushTimer) return;
    flushTimer = setTimeout(flush, FLUSH_MS);
  }

  function track(eventName, data) {
    data = data || {};
    var utm = utmParams();
    var evt = {
      event_name: eventName,
      path: data.path || window.location.pathname + window.location.search,
      plan_slug: data.plan_slug || null,
      email: data.email || null,
      properties: data.properties || null,
      referrer: document.referrer || null,
      utm_source: utm.utm_source || null,
      utm_medium: utm.utm_medium || null,
      utm_campaign: utm.utm_campaign || null,
    };
    QUEUE.push(evt);
    if (QUEUE.length >= 12) flush();
    else scheduleFlush();
  }

  function planFromHref(href) {
    try {
      var u = new URL(href, window.location.origin);
      return u.searchParams.get('plan');
    } catch (e) {
      return null;
    }
  }

  function bindCheckoutClicks() {
    document.addEventListener(
      'click',
      function (e) {
        var a = e.target && e.target.closest ? e.target.closest('a[href*="/checkout"]') : null;
        if (!a) return;
        var href = a.getAttribute('href') || '';
        var plan = planFromHref(href);
        var billing = null;
        try {
          billing = new URL(href, window.location.origin).searchParams.get('billing');
        } catch (err) {}
        track('checkout_click', {
          plan_slug: plan,
          properties: { billing: billing, href: href, source: 'cta' },
        });
        flush();
      },
      true
    );
  }

  function bindFormHints() {
    document.addEventListener(
      'submit',
      function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        var action = (form.getAttribute('action') || '').toLowerCase();
        if (action.indexOf('/subscribe') !== -1 || form.id === 'subscription-form') {
          var plan = form.querySelector('[name="plan"]');
          var email = form.querySelector('[name="email"]');
          track('subscribe_submit', {
            plan_slug: plan ? plan.value : null,
            email: email ? email.value : null,
            properties: { source: 'form' },
          });
          flush();
        } else if (action.indexOf('/contact') !== -1 || form.id === 'contact-form') {
          var cEmail = form.querySelector('[name="email"]');
          track('contact_submit', {
            email: cEmail ? cEmail.value : null,
            properties: { source: 'form' },
          });
          flush();
        } else if (form.id === 'form-lead' || action.indexOf('/lead') !== -1) {
          var lEmail = form.querySelector('[name="email"]');
          track('lead_submit', {
            email: lEmail ? lEmail.value : null,
            properties: { source: 'modal' },
          });
          flush();
        }
      },
      true
    );
  }

  window.ClubinhoTrack = {
    track: track,
    flush: flush,
    visitorId: getVisitorId,
    sessionId: getSessionId,
  };

  track('page_view');
  bindCheckoutClicks();
  bindFormHints();

  window.addEventListener('pagehide', flush);
  document.addEventListener('visibilitychange', function () {
    if (document.visibilityState === 'hidden') flush();
  });
})();
