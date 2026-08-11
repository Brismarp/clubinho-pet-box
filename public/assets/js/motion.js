(() => {
  const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  const header = document.querySelector(".header");
  if (header) {
    const onScroll = () => {
      header.classList.toggle("is-scrolled", window.scrollY > 8);
    };
    onScroll();
    window.addEventListener("scroll", onScroll, { passive: true });
  }

  const toggle = document.querySelector(".nav-toggle");
  const nav = document.querySelector("#primary-nav") || document.querySelector(".nav");

  const closeNav = () => {
    if (!nav || !toggle) return;
    nav.classList.remove("is-open");
    toggle.classList.remove("is-open");
    document.body.classList.remove("nav-open");
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-label", "Abrir menu");
  };

  const openNav = () => {
    if (!nav || !toggle) return;
    nav.classList.add("is-open");
    toggle.classList.add("is-open");
    document.body.classList.add("nav-open");
    toggle.setAttribute("aria-expanded", "true");
    toggle.setAttribute("aria-label", "Fechar menu");
  };

  if (toggle && nav) {
    toggle.setAttribute("aria-controls", nav.id || "primary-nav");
    toggle.addEventListener("click", () => {
      if (nav.classList.contains("is-open")) closeNav();
      else openNav();
    });
    nav.querySelectorAll("a").forEach((link) => {
      link.addEventListener("click", closeNav);
    });
  }

  const revealEls = document.querySelectorAll(".reveal");

  if (!reduce && "IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add("is-visible");
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12, rootMargin: "0px 0px -6% 0px" }
    );
    revealEls.forEach((el) => {
      const rect = el.getBoundingClientRect();
      if (rect.top < window.innerHeight * 0.92 && rect.bottom > 0) {
        el.classList.add("is-visible");
      } else {
        observer.observe(el);
      }
    });
  } else {
    revealEls.forEach((el) => el.classList.add("is-visible"));
  }

  // Vertical marquees — duplicate cards inside each track for a seamless loop
  document.querySelectorAll("[data-marquee-track]").forEach((track) => {
    if (reduce) return;
    const nodes = Array.from(track.children);
    nodes.forEach((node) => {
      const copy = node.cloneNode(true);
      copy.setAttribute("aria-hidden", "true");
      track.appendChild(copy);
    });
  });

  if (reduce) {
    document.querySelectorAll(".testimonials-wall__tilt").forEach((el) => {
      el.setAttribute("hidden", "");
    });
    document.querySelectorAll(".testimonials-wall__fade").forEach((el) => {
      el.setAttribute("hidden", "");
    });
    document.querySelectorAll(".testimonials-list-fallback").forEach((el) => {
      el.removeAttribute("hidden");
      el.style.display = "grid";
    });
  }

  let lastFocus = null;

  const openModal = (modal) => {
    if (!modal) return;
    lastFocus = document.activeElement;
    modal.classList.add("is-open");
    const focusable = modal.querySelector(
      'input, select, textarea, button, [href], [tabindex]:not([tabindex="-1"])'
    );
    if (focusable) focusable.focus();
  };

  const closeModal = (modal) => {
    if (!modal) return;
    modal.classList.remove("is-open");
    if (lastFocus && typeof lastFocus.focus === "function") lastFocus.focus();
  };

  document.querySelectorAll("[data-open-modal]").forEach((btn) => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      const id = btn.getAttribute("data-open-modal");
      openModal(id && document.getElementById(id));
    });
  });

  document.querySelectorAll("[data-close-modal]").forEach((btn) => {
    btn.addEventListener("click", () => closeModal(btn.closest(".modal")));
  });

  document.querySelectorAll(".modal").forEach((modal) => {
    modal.addEventListener("click", (e) => {
      if (e.target === modal) closeModal(modal);
    });
  });

  document.addEventListener("keydown", (e) => {
    if (e.key !== "Escape") return;
    closeNav();
    document.querySelectorAll(".modal.is-open").forEach((modal) => closeModal(modal));
  });

  const easeInOutCubic = (t) =>
    t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;

  const formatCount = (value, decimals) => {
    if (decimals > 0) {
      return value.toLocaleString("pt-BR", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
      });
    }
    return Math.round(value).toLocaleString("pt-BR");
  };

  const runCountUp = (el) => {
    if (el.dataset.countDone === "1") return;
    el.dataset.countDone = "1";
    const target = parseFloat(el.getAttribute("data-target") || "0");
    const duration = parseInt(el.getAttribute("data-duration") || "1600", 10);
    const decimals = parseInt(el.getAttribute("data-decimals") || "0", 10);
    const suffix = el.getAttribute("data-suffix") || "";
    const prefix = el.getAttribute("data-prefix") || "";
    if (reduce) {
      el.textContent = prefix + formatCount(target, decimals) + suffix;
      return;
    }
    const start = performance.now();
    const tick = (now) => {
      const t = Math.min(1, (now - start) / duration);
      const eased = easeInOutCubic(t);
      const current = target * eased;
      el.textContent = prefix + formatCount(current, decimals) + suffix;
      if (t < 1) requestAnimationFrame(tick);
      else el.textContent = prefix + formatCount(target, decimals) + suffix;
    };
    requestAnimationFrame(tick);
  };

  const countEls = document.querySelectorAll("[data-count-up]");
  if (countEls.length) {
    if (!reduce && "IntersectionObserver" in window) {
      const countObs = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              runCountUp(entry.target);
              countObs.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.35 }
      );
      countEls.forEach((el) => countObs.observe(el));
    } else {
      countEls.forEach((el) => runCountUp(el));
    }
  }
})();
