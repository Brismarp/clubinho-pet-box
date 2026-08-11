(() => {
  const root = document.querySelector("[data-blog-carousel]");
  if (!root) return;

  const slides = Array.from(root.querySelectorAll("[data-slide]"));
  const dots = Array.from(root.querySelectorAll("[data-carousel-dot]"));
  const prev = root.querySelector("[data-carousel-prev]");
  const next = root.querySelector("[data-carousel-next]");
  if (slides.length < 2) return;

  const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const intervalMs = Number(root.getAttribute("data-interval") || 5000);
  let index = 0;
  let timer = null;
  let paused = false;

  const show = (i) => {
    index = (i + slides.length) % slides.length;
    slides.forEach((slide, n) => {
      const active = n === index;
      slide.classList.toggle("is-active", active);
      if (active) slide.removeAttribute("hidden");
      else slide.setAttribute("hidden", "");
    });
    dots.forEach((dot, n) => dot.classList.toggle("is-active", n === index));
  };

  const stop = () => {
    if (timer) {
      clearInterval(timer);
      timer = null;
    }
  };

  const start = () => {
    if (reduce || paused) return;
    stop();
    timer = setInterval(() => show(index + 1), intervalMs);
  };

  const pause = () => {
    paused = true;
    stop();
  };

  const resume = () => {
    paused = false;
    start();
  };

  prev?.addEventListener("click", () => {
    show(index - 1);
    stop();
    start();
  });
  next?.addEventListener("click", () => {
    show(index + 1);
    stop();
    start();
  });
  dots.forEach((dot) => {
    dot.addEventListener("click", () => {
      show(Number(dot.getAttribute("data-carousel-dot") || 0));
      stop();
      start();
    });
  });

  root.addEventListener("mouseenter", pause);
  root.addEventListener("mouseleave", resume);
  root.addEventListener("focusin", pause);
  root.addEventListener("focusout", (e) => {
    if (!root.contains(e.relatedTarget)) resume();
  });

  document.addEventListener("visibilitychange", () => {
    if (document.hidden) stop();
    else start();
  });

  show(0);
  start();
})();
