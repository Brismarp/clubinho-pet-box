(function () {
  var meta = document.querySelector('meta[name="csrf-token"]');
  var token = meta && meta.getAttribute("content");
  if (token) {
    window.__CSRF_TOKEN__ = token;
    document.querySelectorAll("form").forEach(function (form) {
      if (form.querySelector('input[name="_csrf"]')) return;
      var method = (form.getAttribute("method") || "get").toLowerCase();
      if (method !== "post") return;
      var input = document.createElement("input");
      input.type = "hidden";
      input.name = "_csrf";
      input.value = token;
      form.appendChild(input);
    });
  }

  var shell = document.querySelector(".admin-shell");
  var sidebar = document.getElementById("adminSidebar");
  var toggle = document.getElementById("adminSidebarToggle");
  var backdrop = document.getElementById("adminSidebarBackdrop");

  function setOpen(open) {
    if (!shell || !toggle) return;
    shell.classList.toggle("is-sidebar-open", open);
    toggle.setAttribute("aria-expanded", open ? "true" : "false");
    toggle.setAttribute("aria-label", open ? "Fechar menu" : "Abrir menu");
    if (backdrop) backdrop.hidden = !open;
  }

  if (toggle) {
    toggle.addEventListener("click", function () {
      setOpen(!shell.classList.contains("is-sidebar-open"));
    });
  }
  if (backdrop) {
    backdrop.addEventListener("click", function () {
      setOpen(false);
    });
  }
  window.addEventListener("keydown", function (e) {
    if (e.key === "Escape") setOpen(false);
  });

  // Persist group open/closed
  document.querySelectorAll(".admin-nav-group").forEach(function (details) {
    var id = details.getAttribute("data-group");
    if (!id) return;
    var key = "admin-nav-" + id;
    try {
      var saved = localStorage.getItem(key);
      if (saved === "0") details.open = false;
      if (saved === "1") details.open = true;
    } catch (err) {}
    details.addEventListener("toggle", function () {
      try {
        localStorage.setItem(key, details.open ? "1" : "0");
      } catch (err) {}
    });
  });

  // Reveal animation
  var prefersReduced =
    window.matchMedia &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var targets = document.querySelectorAll(".admin-reveal, .admin-main .card, .admin-main h1, .admin-main .grid, .admin-main table, .admin-main .filters");
  if (prefersReduced) {
    targets.forEach(function (el) {
      el.classList.add("is-visible");
    });
    return;
  }
  if (!("IntersectionObserver" in window)) {
    targets.forEach(function (el) {
      el.classList.add("is-visible");
    });
    return;
  }
  var io = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          io.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.08, rootMargin: "0px 0px -24px 0px" }
  );
  targets.forEach(function (el) {
    el.classList.add("admin-reveal");
    io.observe(el);
  });
})();
