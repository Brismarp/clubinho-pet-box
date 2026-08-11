(() => {
  const STORAGE_KEY = 'cpb_quiz_v2_token';
  const root = document.querySelector('[data-quiz-root]');
  if (!root) return;

  const mount = document.getElementById('quizStepMount');
  const errEl = document.getElementById('quizError');
  const prevBtn = document.getElementById('quizPrev');
  const nextBtn = document.getElementById('quizNext');
  const progressBar = document.getElementById('progressBar');
  const currentStepEl = document.getElementById('currentStep');
  const moodImg = document.getElementById('quizMoodImage');
  const moodCaption = document.getElementById('quizMoodCaption');
  const resultEl = document.getElementById('quizResult');
  const panel = root.querySelector('.quiz-stage');

  const steps = [
    {
      key: 'tutor',
      title: 'Sobre você',
      mood: '/assets/images/mood/q01.jpg',
      caption: 'Momento tutor + pet',
      fields: [
        { name: 'name', label: 'Seu nome', type: 'text', required: true },
        { name: 'email', label: 'E-mail', type: 'email', required: true },
        { name: 'phone', label: 'Telefone / WhatsApp', type: 'tel', required: true },
      ],
    },
    {
      key: 'pet',
      title: 'Sobre o pet',
      mood: '/assets/images/mood/q02.jpg',
      caption: 'Conhecendo o pet',
      fields: [
        { name: 'name', label: 'Nome do pet', type: 'text', required: true },
        { name: 'species', label: 'Espécie', type: 'choice', required: true, options: [
          { value: 'dog', label: 'Cachorro' },
          { value: 'cat', label: 'Gato' },
        ]},
        { name: 'breed', label: 'Raça (opcional)', type: 'text' },
        { name: 'age_years', label: 'Idade (anos)', type: 'number', required: true },
        { name: 'sex', label: 'Sexo', type: 'choice', options: [
          { value: 'male', label: 'Macho' },
          { value: 'female', label: 'Fêmea' },
          { value: 'unknown', label: 'Prefiro não dizer' },
        ]},
        { name: 'size', label: 'Porte', type: 'choice', required: true, options: [
          { value: 'small', label: 'Pequeno' },
          { value: 'medium', label: 'Médio' },
          { value: 'large', label: 'Grande' },
        ]},
      ],
    },
    {
      key: 'routine',
      title: 'Rotina e energia',
      mood: '/assets/images/mood/q03.jpg',
      caption: 'Rotina do dia a dia',
      fields: [
        { name: 'energy', label: 'Nível de energia', type: 'choice', required: true, options: [
          { value: 'low', label: 'Baixo' },
          { value: 'medium', label: 'Médio' },
          { value: 'high', label: 'Alto' },
        ]},
        { name: 'play_freq', label: 'Frequência de brincadeiras', type: 'choice', options: [
          { value: 'rare', label: 'Rara' },
          { value: 'daily', label: 'Diária' },
          { value: 'several', label: 'Várias vezes ao dia' },
        ]},
        { name: 'alone_time', label: 'Tempo sozinho', type: 'choice', options: [
          { value: 'little', label: 'Pouco' },
          { value: 'halfday', label: 'Meio período' },
          { value: 'fullday', label: 'Quase o dia todo' },
        ]},
      ],
    },
    {
      key: 'personality',
      title: 'Personalidade',
      mood: '/assets/images/mood/q04.jpg',
      caption: 'Jeitinho do pet',
      fields: [
        { name: 'traits', label: 'Marque o que combina (pode várias)', type: 'multi', required: true, options: [
          { value: 'playful', label: 'Brincalhão' },
          { value: 'destructive', label: 'Destruidor' },
          { value: 'calm', label: 'Calmo' },
          { value: 'curious', label: 'Curioso' },
          { value: 'anxious', label: 'Ansioso' },
          { value: 'picky', label: 'Seletivo' },
          { value: 'social', label: 'Sociável' },
        ]},
      ],
    },
    {
      key: 'toys',
      title: 'Brinquedos',
      mood: '/assets/images/mood/q05.jpg',
      caption: 'Hora de brincar',
      fields: [
        { name: 'preferred', label: 'Tipos preferidos', type: 'multi', options: [
          { value: 'chew', label: 'Mordedores' },
          { value: 'plush', label: 'Pelúcias' },
          { value: 'interactive', label: 'Interativos' },
          { value: 'ball', label: 'Bolinhas' },
        ]},
        { name: 'destroys', label: 'Costuma destruir brinquedos?', type: 'choice', options: [
          { value: 'yes', label: 'Sim' },
          { value: 'no', label: 'Não' },
        ]},
      ],
    },
    {
      key: 'food',
      title: 'Alimentação e petiscos',
      mood: '/assets/images/mood/q06.jpg',
      caption: 'Sabores e formatos',
      fields: [
        { name: 'flavors', label: 'Sabores de interesse', type: 'multi', options: [
          { value: 'chicken', label: 'Frango' },
          { value: 'fish', label: 'Peixe' },
          { value: 'beef', label: 'Carne' },
          { value: 'veggie', label: 'Vegetais' },
        ]},
        { name: 'avoid', label: 'Não desejo receber', type: 'multi', options: [
          { value: 'chicken', label: 'Frango' },
          { value: 'dairy', label: 'Laticínios' },
          { value: 'grain', label: 'Grãos' },
        ]},
      ],
    },
    {
      key: 'restrictions',
      title: 'Restrições e cuidados',
      mood: '/assets/images/mood/q07.jpg',
      caption: 'Cuidados especiais',
      note: 'Informações fornecidas pelo tutor para seleção de produtos — não substituem orientação veterinária.',
      fields: [
        { name: 'allergies', label: 'Alergias / intolerâncias informadas', type: 'multi', options: [
          { value: 'chicken', label: 'Frango' },
          { value: 'beef', label: 'Carne bovina' },
          { value: 'grain', label: 'Grãos' },
          { value: 'none', label: 'Nenhuma conhecida' },
        ]},
        { name: 'notes', label: 'Observações (opcional)', type: 'textarea' },
      ],
    },
    {
      key: 'care',
      title: 'Higiene e acessórios',
      mood: '/assets/images/mood/q08.jpg',
      caption: 'Cuidados do dia a dia',
      fields: [
        { name: 'interests', label: 'Interesses', type: 'multi', options: [
          { value: 'higiene', label: 'Higiene' },
          { value: 'walk', label: 'Passeio' },
          { value: 'accessories', label: 'Acessórios' },
        ]},
      ],
    },
    {
      key: 'preferences',
      title: 'Preferências do tutor',
      mood: '/assets/images/mood/q09.jpg',
      caption: 'O que você quer receber',
      fields: [
        { name: 'want', label: 'Categorias que mais quer', type: 'multi', options: [
          { value: 'brinquedo', label: 'Brinquedos' },
          { value: 'petisco', label: 'Petiscos' },
          { value: 'higiene', label: 'Higiene' },
          { value: 'acessorio', label: 'Acessórios' },
          { value: 'premium', label: 'Itens premium' },
        ]},
        { name: 'dont_want', label: 'Categorias que não deseja', type: 'multi', options: [
          { value: 'brinquedo', label: 'Brinquedos' },
          { value: 'petisco', label: 'Petiscos' },
          { value: 'higiene', label: 'Higiene' },
          { value: 'acessorio', label: 'Acessórios' },
        ]},
        { name: 'variety', label: 'Nível de variedade', type: 'choice', required: true, options: [
          { value: 'low', label: 'Pouca' },
          { value: 'medium', label: 'Média' },
          { value: 'high', label: 'Alta' },
        ]},
      ],
    },
    {
      key: 'confirm',
      title: 'Confirmação',
      mood: '/assets/images/mood/q10.jpg',
      caption: 'Quase lá',
      fields: [
        { name: 'summary', label: '', type: 'summary' },
        {
          name: 'consent_lgpd',
          label: 'Concordo com o uso dos meus dados e do perfil do pet apenas para personalizar a box e atendimento (LGPD). Li a <a href="/privacidade" target="_blank" rel="noopener">Privacidade</a> e os <a href="/termos" target="_blank" rel="noopener">Termos</a>.',
          type: 'checkbox',
          required: true,
        },
        { name: 'consent_marketing', label: 'Aceito receber novidades por e-mail/WhatsApp.', type: 'checkbox' },
      ],
    },
  ];

  let token = localStorage.getItem(STORAGE_KEY) || '';
  let stepIndex = 0;
  /** @type {Record<string, Record<string, any>>} */
  let answers = {};

  function showError(msg) {
    if (!msg) {
      errEl.hidden = true;
      errEl.textContent = '';
      return;
    }
    errEl.hidden = false;
    errEl.textContent = msg;
  }

  async function api(method, path, body) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
      || window.__CSRF_TOKEN__
      || '';
    const res = await fetch(path, {
      method,
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        ...(csrf ? { 'X-CSRF-Token': csrf } : {}),
      },
      body: body ? JSON.stringify(body) : undefined,
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || 'Erro ao salvar o quiz.');
    return data;
  }

  async function ensureSession() {
    const data = await api('POST', '/api/v1/quiz/sessions', token ? { token } : {});
    token = data.token;
    localStorage.setItem(STORAGE_KEY, token);
    answers = data.answers || {};
    if (data.status === 'completed') {
      showCompleted(data.suggested_plan_slug || 'love');
      return false;
    }
    stepIndex = Math.max(0, Math.min(9, (data.current_step || 1) - 1));
    return true;
  }

  function val(stepKey, name) {
    return answers[stepKey]?.[name];
  }

  function setVal(stepKey, name, value) {
    if (!answers[stepKey]) answers[stepKey] = {};
    answers[stepKey][name] = value;
  }

  function render() {
    const step = steps[stepIndex];
    showError('');
    progressBar.style.width = `${((stepIndex + 1) / steps.length) * 100}%`;
    currentStepEl.textContent = String(stepIndex + 1);
    moodImg.src = step.mood;
    moodCaption.textContent = step.caption;
    prevBtn.hidden = stepIndex === 0;
    nextBtn.textContent = stepIndex === steps.length - 1 ? 'Confirmar e enviar' : 'Continuar';

    let html = `<div class="quiz-question active"><h2 class="question-title">${step.title}</h2>`;
    if (step.note) html += `<p class="muted" style="margin-bottom:1rem">${step.note}</p>`;

    for (const field of step.fields) {
      const current = val(step.key, field.name);
      if (field.type === 'summary') {
        const pet = answers.pet || {};
        const tutor = answers.tutor || {};
        html += `<div class="quiz-summary card" style="padding:1rem;margin-bottom:1rem;border:1px solid var(--color-line);border-radius:var(--radius-md)">
          <p><strong>Tutor:</strong> ${escapeHtml(tutor.name || '—')} · ${escapeHtml(tutor.email || '')}</p>
          <p><strong>Pet:</strong> ${escapeHtml(pet.name || '—')} (${escapeHtml(pet.species || '')}, porte ${escapeHtml(pet.size || '')})</p>
          <p class="muted">Ao confirmar, geramos atributos e uma sugestão de box para revisão da equipe.</p>
        </div>`;
        continue;
      }
      html += `<div class="form-group quiz-field" style="margin-bottom:1rem">`;
      if (field.label && field.type !== 'checkbox') {
        html += `<label>${field.label}${field.required ? ' *' : ''}</label>`;
      }
      if (field.type === 'text' || field.type === 'email' || field.type === 'tel' || field.type === 'number') {
        html += `<input class="quiz-input" data-field="${field.name}" type="${field.type}" value="${escapeAttr(current ?? '')}" ${field.required ? 'required' : ''}>`;
      } else if (field.type === 'textarea') {
        html += `<textarea class="quiz-input" data-field="${field.name}" rows="3">${escapeHtml(current ?? '')}</textarea>`;
      } else if (field.type === 'choice') {
        html += `<div class="quiz-options">`;
        for (const opt of field.options || []) {
          const sel = current === opt.value ? ' selected' : '';
          html += `<button type="button" class="quiz-option${sel}" data-field="${field.name}" data-value="${escapeAttr(opt.value)}">${opt.label}</button>`;
        }
        html += `</div>`;
      } else if (field.type === 'multi') {
        const selected = Array.isArray(current) ? current : [];
        html += `<div class="quiz-options">`;
        for (const opt of field.options || []) {
          const sel = selected.includes(opt.value) ? ' selected' : '';
          html += `<button type="button" class="quiz-option${sel}" data-multi="1" data-field="${field.name}" data-value="${escapeAttr(opt.value)}">${opt.label}</button>`;
        }
        html += `</div>`;
      } else if (field.type === 'checkbox') {
        const checked = current ? 'checked' : '';
        html += `<label class="cluster" style="display:flex;gap:.5rem;align-items:flex-start">
          <input type="checkbox" data-field="${field.name}" ${checked} style="width:auto;margin-top:.2rem">
          <span>${field.label}${field.required ? ' *' : ''}</span>
        </label>`;
      }
      html += `</div>`;
    }
    html += `</div>`;
    mount.innerHTML = html;
    bindFieldEvents(step.key);
  }

  function bindFieldEvents(stepKey) {
    mount.querySelectorAll('.quiz-option').forEach((btn) => {
      btn.addEventListener('click', () => {
        const field = btn.getAttribute('data-field');
        const value = btn.getAttribute('data-value');
        if (btn.hasAttribute('data-multi')) {
          const cur = Array.isArray(val(stepKey, field)) ? [...val(stepKey, field)] : [];
          const idx = cur.indexOf(value);
          if (idx >= 0) cur.splice(idx, 1); else cur.push(value);
          setVal(stepKey, field, cur);
          btn.classList.toggle('selected');
        } else {
          setVal(stepKey, field, value);
          mount.querySelectorAll(`.quiz-option[data-field="${field}"]`).forEach((b) => b.classList.remove('selected'));
          btn.classList.add('selected');
        }
      });
    });
    mount.querySelectorAll('.quiz-input').forEach((input) => {
      input.addEventListener('input', () => {
        const field = input.getAttribute('data-field');
        let v = input.value;
        if (input.type === 'number') v = v === '' ? '' : Number(v);
        setVal(stepKey, field, v);
      });
    });
    mount.querySelectorAll('input[type="checkbox"][data-field]').forEach((input) => {
      input.addEventListener('change', () => {
        setVal(stepKey, input.getAttribute('data-field'), input.checked);
      });
    });
  }

  function collectStep(step) {
    const out = { ...(answers[step.key] || {}) };
    mount.querySelectorAll('.quiz-input').forEach((input) => {
      const field = input.getAttribute('data-field');
      let v = input.value;
      if (input.type === 'number') v = v === '' ? '' : Number(v);
      out[field] = v;
    });
    mount.querySelectorAll('input[type="checkbox"][data-field]').forEach((input) => {
      out[input.getAttribute('data-field')] = input.checked;
    });
    answers[step.key] = out;
    return out;
  }

  function validate(step, data) {
    for (const field of step.fields) {
      if (!field.required) continue;
      const v = data[field.name];
      if (field.type === 'checkbox' && !v) {
        return field.name === 'consent_lgpd'
          ? 'Marque o consentimento LGPD para continuar.'
          : `Marque: ${field.label.replace(/<[^>]+>/g, '')}`;
      }
      if (field.type === 'multi' && (!Array.isArray(v) || v.length === 0)) return `Selecione ao menos uma opção em “${field.label}”.`;
      if (field.type !== 'checkbox' && field.type !== 'multi' && field.type !== 'summary' && (v === undefined || v === null || v === '')) {
        return `Preencha “${field.label}”.`;
      }
      if (field.type === 'email' && v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(v))) {
        return 'Informe um e-mail válido.';
      }
    }
    return '';
  }

  async function saveCurrent(goNext) {
    const step = steps[stepIndex];
    const data = collectStep(step);
    const err = validate(step, data);
    if (err) {
      showError(err);
      return;
    }
    showError('');
    nextBtn.disabled = true;
    try {
      if (stepIndex === steps.length - 1 && goNext) {
        const result = await api('POST', `/api/v1/quiz/sessions/${token}/complete`, {
          step_key: step.key,
          answers: data,
          consent_lgpd: !!data.consent_lgpd,
          consent_marketing: !!data.consent_marketing,
        });
        localStorage.removeItem(STORAGE_KEY);
        showCompleted(result.suggested_plan || 'love', result.checkout_url);
        if (window.ClubinhoTrack) {
          try { ClubinhoTrack.track('quiz_complete', { plan: result.suggested_plan }); } catch (_) {}
        }
        return;
      }
      await api('POST', `/api/v1/quiz/sessions/${token}/save`, {
        step: stepIndex + 1 + (goNext ? 1 : 0),
        step_key: step.key,
        answers: data,
      });
      if (goNext) {
        stepIndex = Math.min(steps.length - 1, stepIndex + 1);
        if (window.ClubinhoTrack) {
          try { ClubinhoTrack.track('quiz_step', { step: stepIndex + 1 }); } catch (_) {}
        }
        render();
      }
    } catch (e) {
      showError(e.message || 'Erro ao salvar.');
    } finally {
      nextBtn.disabled = false;
    }
  }

  function showCompleted(plan, checkoutUrl) {
    if (panel) panel.hidden = true;
    root.querySelector('.quiz-progress').hidden = true;
    resultEl.hidden = false;
    resultEl.classList.add('active');
    document.getElementById('quizResultLead').textContent =
      `Plano sugerido: Clubinho ${String(plan).charAt(0).toUpperCase()}${String(plan).slice(1)}. Sua montagem entrou na fila de revisão.`;
    const link = document.getElementById('quizCheckout');
    link.href = checkoutUrl || `/checkout?plan=${encodeURIComponent(plan)}&billing=monthly`;
    link.textContent = `Assinar ${plan}`;
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }
  function escapeAttr(s) {
    return escapeHtml(s).replace(/`/g, '');
  }

  prevBtn.addEventListener('click', async () => {
    const step = steps[stepIndex];
    collectStep(step);
    try {
      await api('POST', `/api/v1/quiz/sessions/${token}/save`, {
        step: stepIndex,
        step_key: step.key,
        answers: answers[step.key] || {},
      });
    } catch (_) {}
    stepIndex = Math.max(0, stepIndex - 1);
    render();
  });

  nextBtn.addEventListener('click', () => saveCurrent(true));

  ensureSession().then((ok) => {
    if (ok) {
      if (window.ClubinhoTrack) {
        try { ClubinhoTrack.track('quiz_start'); } catch (_) {}
      }
      render();
    }
  }).catch((e) => showError(e.message || 'Não foi possível iniciar o quiz.'));
})();
