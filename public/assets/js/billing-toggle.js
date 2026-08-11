(() => {
  const STORAGE_KEY = "clubinho_billing";

  const formatBRL = (value) =>
    value.toLocaleString("pt-BR", { style: "currency", currency: "BRL" });

  const getBilling = () => {
    const saved = localStorage.getItem(STORAGE_KEY);
    return saved === "annual" ? "annual" : "monthly";
  };

  const setBilling = (billing) => {
    localStorage.setItem(STORAGE_KEY, billing);
    applyBilling(billing);
    window.dispatchEvent(new CustomEvent("billing:change", { detail: { billing } }));
  };

  const applyBilling = (billing) => {
    document.querySelectorAll("[data-billing-toggle]").forEach((group) => {
      group.querySelectorAll("[data-billing-value]").forEach((btn) => {
        const active = btn.getAttribute("data-billing-value") === billing;
        btn.classList.toggle("is-active", active);
        btn.setAttribute("aria-pressed", active ? "true" : "false");
      });
    });

    document.querySelectorAll(".plan-card[data-slug]").forEach((card) => {
      const monthly = parseFloat(card.dataset.priceMonthly || "0");
      const annual = parseFloat(card.dataset.priceAnnual || String(monthly * 10));
      const slug = card.dataset.slug;
      const priceEl = card.querySelector("[data-plan-price]");
      const periodEl = card.querySelector("[data-plan-period]");
      const equivEl = card.querySelector("[data-plan-equiv]");
      const cta = card.querySelector("[data-plan-cta]");

      card.dataset.billing = billing;

      if (billing === "annual") {
        if (priceEl) priceEl.textContent = formatBRL(annual);
        if (periodEl) periodEl.textContent = "/ano";
        if (equivEl) {
          equivEl.hidden = false;
          equivEl.textContent = `equiv. ${formatBRL(annual / 12)}/mês`;
        }
        if (cta) cta.setAttribute("href", `/checkout?plan=${slug}&billing=annual`);
      } else {
        if (priceEl) priceEl.textContent = formatBRL(monthly);
        if (periodEl) periodEl.textContent = "/mês";
        if (equivEl) {
          equivEl.hidden = true;
          equivEl.textContent = "";
        }
        if (cta) cta.setAttribute("href", `/checkout?plan=${slug}&billing=monthly`);
      }
    });

    document.querySelectorAll("[data-billing-select]").forEach((select) => {
      select.value = billing;
    });

    document.querySelectorAll("[data-billing-hidden]").forEach((input) => {
      input.value = billing;
    });

    window.__clubinhoBilling = billing;
  };

  document.querySelectorAll("[data-billing-toggle]").forEach((group) => {
    group.querySelectorAll("[data-billing-value]").forEach((btn) => {
      btn.addEventListener("click", () => {
        setBilling(btn.getAttribute("data-billing-value") === "annual" ? "annual" : "monthly");
      });
    });
  });

  document.querySelectorAll("[data-billing-select]").forEach((select) => {
    select.addEventListener("change", () => {
      setBilling(select.value === "annual" ? "annual" : "monthly");
    });
  });

  applyBilling(getBilling());

  window.ClubinhoBilling = {
    get: getBilling,
    set: setBilling,
    formatBRL,
    annualFromMonthly: (monthly) => monthly * 10,
  };
})();
