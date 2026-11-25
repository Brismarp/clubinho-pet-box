(() => {
  const STORAGE_KEY = 'cpb_quiz_v2_token';
  const DRAFT_KEY = 'cpb_quiz_v2_draft';
  const root = document.querySelector('[data-quiz-root]');
  if (!root) return;

  const mount = document.getElementById('quizStepMount');
  const errEl = document.getElementById('quizError');
  const prevBtn = document.getElementById('quizPrev');
  const nextBtn = document.getElementById('quizNext');
  const nav = document.getElementById('quizNavigation');
  const progressBar = document.getElementById('progressBar');
  const progressLabel = document.getElementById('progressLabel');
  const chaptersEl = document.getElementById('quizChapters');
  const moodImg = document.getElementById('quizMoodImage');
  const moodImgNext = document.getElementById('quizMoodImageNext');
  const moodCaption = document.getElementById('quizMoodCaption');
  const resultEl = document.getElementById('quizResult');
  const ackEl = document.getElementById('quizAck');
  const introEl = document.getElementById('quizIntro');
  const playEl = document.getElementById('quizPlay');
  const startBtn = document.getElementById('quizStart');
  const prefersReducedMotion =
    window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const chapters = [
    { id: 'quem', label: 'Quem é' },
    { id: 'jeito', label: 'Jeitinho' },
    { id: 'brincar', label: 'Brincar' },
    { id: 'comer', label: 'Comer' },
    { id: 'cuidados', label: 'Cuidados' },
    { id: 'box', label: 'A box' },
    { id: 'contato', label: 'Onde mandamos' },
  ];

  const STEP_NUM = {
    pet: 1,
    routine: 2,
    personality: 3,
    toys: 4,
    food: 5,
    restrictions: 6,
    care: 7,
    preferences: 8,
    tutor: 9,
    confirm: 10,
  };

  const PLANS = {
    happy: {
      name: 'Clubinho Pet Happy',
      line: 'Box essencial',
      img: '/assets/images/mood/result-happy.jpg',
    },
    love: {
      name: 'Clubinho Pet Love',
      line: 'Box premium',
      img: '/assets/images/mood/result-love.jpg',
    },
    deluxe: {
      name: 'Clubinho Pet Deluxe',
      line: 'Box super premium',
      img: '/assets/images/mood/result-deluxe.jpg',
    },
  };

  function petName() {
    const n = String(answers.pet?.name || '').trim();
    return n || 'seu pet';
  }

  const questions = [
    {
      stepKey: 'pet',
      name: 'species',
      chapter: 'quem',
      type: 'choice',
      required: true,
      mood: '/assets/images/mood/q02.jpg',
      caption: 'Quem vai receber a box',
      prompt: () => 'Quem vai receber a box?',
      options: [
        { value: 'dog', label: 'Cachorro', ack: 'Um cachorro. A box já começa com o focinho certo.' },
        { value: 'cat', label: 'Gato', ack: 'Um gato. Vamos no ritmo dele.' },
      ],
    },
    {
      stepKey: 'pet',
      name: 'name',
      chapter: 'quem',
      type: 'text',
      required: true,
      mood: '/assets/images/mood/q02.jpg',
      caption: 'O nome que a gente vai usar',
      prompt: () => 'Qual é o nome?',
      hint: () => 'Ele aparece nas próximas perguntas.',
      placeholder: 'Thor, Luna, Mel…',
      emptyError: 'Conta o nome para a gente continuar.',
    },
    {
      stepKey: 'pet',
      name: 'breed',
      chapter: 'quem',
      type: 'text',
      mood: '/assets/images/mood/q02.jpg',
      caption: 'Cada um com seu jeito',
      prompt: () => `${petName()} é de qual raça?`,
      hint: () => 'Se não souber, pode pular.',
      placeholder: 'SRD também vale',
    },
    {
      stepKey: 'pet',
      name: 'age_years',
      chapter: 'quem',
      type: 'number',
      required: true,
      mood: '/assets/images/mood/q03.jpg',
      caption: 'Do filhote ao sênior',
      prompt: () => `Quantos anos tem ${petName()}?`,
      hint: () => 'Filhote pode ser 0.',
      emptyError: 'Conta a idade para a gente seguir.',
    },
    {
      stepKey: 'pet',
      name: 'sex',
      chapter: 'quem',
      type: 'choice',
      mood: '/assets/images/mood/q03.jpg',
      caption: 'Só para acertar o tom',
      prompt: () => `${petName()} é…`,
      options: [
        { value: 'male', label: 'Macho', ack: 'Anotado.' },
        { value: 'female', label: 'Fêmea', ack: 'Anotado.' },
        { value: 'unknown', label: 'Prefiro não dizer', ack: 'Sem problema.' },
      ],
    },
    {
      stepKey: 'pet',
      name: 'size',
      chapter: 'quem',
      type: 'choice',
      required: true,
      mood: '/assets/images/mood/q02.jpg',
      caption: 'Cada porte, uma curadoria',
      prompt: () => `Qual é o porte de ${petName()}?`,
      options: [
        { value: 'small', label: 'Pequeno', ack: 'Porte pequeno. Itens na medida.' },
        { value: 'medium', label: 'Médio', ack: 'Porte médio. Anotado.' },
        { value: 'large', label: 'Grande', ack: 'Porte grande. A box cresce junto.' },
      ],
    },
    {
      stepKey: 'routine',
      name: 'energy',
      chapter: 'jeito',
      type: 'choice',
      required: true,
      mood: '/assets/images/mood/q04.jpg',
      caption: 'Energia bem canalizada',
      prompt: () => `${petName()} é mais calminho ou um foguete?`,
      options: [
        { value: 'low', label: 'Calminho', ack: 'Anotado: calminho.' },
        { value: 'medium', label: 'No meio do caminho', ack: 'Anotado: equilibrado.' },
        { value: 'high', label: 'Um foguete', ack: 'Anotado: energia de sobra.' },
      ],
    },
    {
      stepKey: 'routine',
      name: 'play_freq',
      chapter: 'jeito',
      type: 'choice',
      mood: '/assets/images/mood/q03.jpg',
      caption: 'Hora de brincar',
      prompt: () => `Com que frequência ${petName()} brinca?`,
      options: [
        { value: 'rare', label: 'Quase não brinca', ack: 'Anotado.' },
        { value: 'daily', label: 'Todo dia', ack: 'Anotado: todo dia.' },
        { value: 'several', label: 'Várias vezes ao dia', ack: 'Anotado: brinca demais.' },
      ],
    },
    {
      stepKey: 'routine',
      name: 'alone_time',
      chapter: 'jeito',
      type: 'choice',
      mood: '/assets/images/mood/q09.jpg',
      caption: 'Ritmo da rotina',
      prompt: () => `${petName()} fica muito tempo sozinho?`,
      options: [
        { value: 'little', label: 'Pouco', ack: 'Anotado: companhia quase o tempo todo.' },
        { value: 'halfday', label: 'Meio período', ack: 'Anotado: meio período.' },
        { value: 'fullday', label: 'Quase o dia todo', ack: 'Anotado: passa boa parte do dia só.' },
      ],
    },
    {
      stepKey: 'routine',
      name: 'ambiente',
      chapter: 'jeito',
      type: 'choice',
      mood: '/assets/images/mood/q09.jpg',
      caption: 'Onde o dia acontece',
      prompt: () => `Onde ${petName()} passa o dia?`,
      options: [
        { value: 'apartment', label: 'Apartamento', ack: 'Anotado: apartamento.' },
        { value: 'house', label: 'Casa com quintal', ack: 'Anotado: casa com quintal.' },
        { value: 'outdoor', label: 'Acesso ao lado de fora', ack: 'Anotado: espaço aberto.' },
      ],
    },
    {
      stepKey: 'personality',
      name: 'traits',
      chapter: 'jeito',
      type: 'multi',
      required: true,
      mood: '/assets/images/mood/q04.jpg',
      caption: 'Jeitinho do pet',
      prompt: () => `O que combina com ${petName()}?`,
      hint: () => 'Pode marcar vários.',
      emptyError: 'Marca ao menos um jeito que combine.',
      options: [
        { value: 'playful', label: 'Brincalhão' },
        { value: 'destructive', label: 'Destruidor' },
        { value: 'calm', label: 'Calmo' },
        { value: 'curious', label: 'Curioso' },
        { value: 'anxious', label: 'Ansioso' },
        { value: 'picky', label: 'Seletivo' },
        { value: 'social', label: 'Sociável' },
      ],
    },
    {
      stepKey: 'toys',
      name: 'preferred',
      chapter: 'brincar',
      type: 'multi',
      mood: '/assets/images/mood/q05.jpg',
      caption: 'O que faz o rabo balançar',
      prompt: () => `Que brinquedo faz sucesso com ${petName()}?`,
      hint: () => 'Pode marcar vários. Ou pular.',
      options: [
        { value: 'chew', label: 'Mordedores' },
        { value: 'plush', label: 'Pelúcias' },
        { value: 'interactive', label: 'Interativos' },
        { value: 'ball', label: 'Bolinhas' },
      ],
    },
    {
      stepKey: 'toys',
      name: 'destroys',
      chapter: 'brincar',
      type: 'choice',
      mood: '/assets/images/mood/q05.jpg',
      caption: 'Resistência conta',
      prompt: () => `${petName()} costuma destruir brinquedo?`,
      options: [
        { value: 'yes', label: 'Sim, em pedacinhos', ack: 'Anotado: brinquedo resistente.' },
        { value: 'no', label: 'Não, cuida bem', ack: 'Anotado: cuida do que ganha.' },
      ],
    },
    {
      stepKey: 'toys',
      name: 'materials',
      chapter: 'brincar',
      type: 'multi',
      mood: '/assets/images/mood/q05.jpg',
      caption: 'Textura favorita',
      prompt: () => 'Algum material favorito?',
      hint: () => 'Pode marcar vários. Ou pular.',
      options: [
        { value: 'rubber', label: 'Borracha' },
        { value: 'fabric', label: 'Tecido' },
        { value: 'rope', label: 'Corda' },
        { value: 'wood', label: 'Madeira' },
      ],
    },
    {
      stepKey: 'toys',
      name: 'size',
      chapter: 'brincar',
      type: 'choice',
      mood: '/assets/images/mood/q05.jpg',
      caption: 'Na medida da boca',
      prompt: () => `Qual tamanho de brinquedo serve para ${petName()}?`,
      options: [
        { value: 'small', label: 'Pequeno', ack: 'Anotado: brinquedo pequeno.' },
        { value: 'medium', label: 'Médio', ack: 'Anotado: brinquedo médio.' },
        { value: 'large', label: 'Grande', ack: 'Anotado: brinquedo grande.' },
      ],
    },
    {
      stepKey: 'food',
      name: 'flavors',
      chapter: 'comer',
      type: 'multi',
      mood: '/assets/images/mood/q06.jpg',
      caption: 'Sabores que ganham o dia',
      prompt: () => `Quais sabores ${petName()} ama?`,
      hint: () => 'Pode marcar vários. Ou pular.',
      options: [
        { value: 'chicken', label: 'Frango' },
        { value: 'fish', label: 'Peixe' },
        { value: 'beef', label: 'Carne' },
        { value: 'veggie', label: 'Vegetais' },
      ],
    },
    {
      stepKey: 'food',
      name: 'avoid',
      chapter: 'comer',
      type: 'multi',
      mood: '/assets/images/mood/q06.jpg',
      caption: 'O que fica de fora',
      prompt: () => 'Tem algo que é melhor não mandar?',
      hint: () => 'Pode marcar vários. Ou pular.',
      options: [
        { value: 'chicken', label: 'Frango' },
        { value: 'dairy', label: 'Laticínios' },
        { value: 'grain', label: 'Grãos' },
      ],
    },
    {
      stepKey: 'restrictions',
      name: 'allergies',
      chapter: 'cuidados',
      type: 'multi',
      mood: '/assets/images/mood/q07.jpg',
      caption: 'Cuidado em cada escolha',
      prompt: () => `Alguma alergia que você já notou em ${petName()}?`,
      hint: () => 'Ajuda a escolher produtos. Não substitui o veterinário.',
      options: [
        { value: 'chicken', label: 'Frango' },
        { value: 'beef', label: 'Carne bovina' },
        { value: 'grain', label: 'Grãos' },
        { value: 'none', label: 'Nenhuma conhecida' },
      ],
    },
    {
      stepKey: 'restrictions',
      name: 'notes',
      chapter: 'cuidados',
      type: 'textarea',
      mood: '/assets/images/mood/q07.jpg',
      caption: 'O que mais a gente precisa saber',
      prompt: () => `Quer contar mais algum cuidado com ${petName()}?`,
      hint: () => 'Pode pular.',
      placeholder: 'Um detalhe, uma manha, um limite…',
    },
    {
      stepKey: 'care',
      name: 'interests',
      chapter: 'cuidados',
      type: 'multi',
      mood: '/assets/images/mood/q08.jpg',
      caption: 'Cuidados do dia a dia',
      prompt: () => 'No dia a dia, o que mais ajuda?',
      hint: () => 'Pode marcar vários. Ou pular.',
      options: [
        { value: 'higiene', label: 'Higiene' },
        { value: 'walk', label: 'Passeio' },
        { value: 'accessories', label: 'Acessórios' },
      ],
    },
    {
      stepKey: 'preferences',
      name: 'want',
      chapter: 'box',
      type: 'multi',
      mood: '/assets/images/mood/q09.jpg',
      caption: 'O que você quer receber',
      prompt: () => 'O que você mais quer ver na box?',
      hint: () => 'Pode marcar vários. Ou pular.',
      options: [
        { value: 'brinquedo', label: 'Brinquedos' },
        { value: 'petisco', label: 'Petiscos' },
        { value: 'higiene', label: 'Higiene' },
        { value: 'acessorio', label: 'Acessórios' },
        { value: 'premium', label: 'Itens premium' },
      ],
    },
    {
      stepKey: 'preferences',
      name: 'dont_want',
      chapter: 'box',
      type: 'multi',
      mood: '/assets/images/mood/q09.jpg',
      caption: 'O que pode ficar de fora',
      prompt: () => 'E o que pode ficar de fora?',
      hint: () => 'Pode marcar vários. Ou pular.',
      options: [
        { value: 'brinquedo', label: 'Brinquedos' },
        { value: 'petisco', label: 'Petiscos' },
        { value: 'higiene', label: 'Higiene' },
        { value: 'acessorio', label: 'Acessórios' },
      ],
    },
    {
      stepKey: 'preferences',
      name: 'variety',
      chapter: 'box',
      type: 'choice',
      required: true,
      mood: '/assets/images/mood/q10.jpg',
      caption: 'O essencial, bem escolhido',
      prompt: () => 'Quanta surpresa você quer na box?',
      options: [
        { value: 'low', label: 'O essencial, bem escolhido', ack: 'Anotado: curadoria enxuta.' },
        { value: 'medium', label: 'Um mix equilibrado', ack: 'Anotado: mix equilibrado.' },
        { value: 'high', label: 'Variedade toda vez', ack: 'Anotado: surpresa toda vez.' },
      ],
    },
    {
      stepKey: 'tutor',
      name: 'name',
      chapter: 'contato',
      type: 'text',
      required: true,
      autocomplete: 'name',
      mood: '/assets/images/mood/q01.jpg',
      caption: 'Onde a gente manda a box e o recado',
      prompt: () => 'Como a gente te chama?',
      hint: () => 'Onde a gente manda a box e o recado.',
      placeholder: 'Seu nome',
      emptyError: 'Falta o seu nome para a gente enviar a box.',
    },
    {
      stepKey: 'tutor',
      name: 'email',
      chapter: 'contato',
      type: 'email',
      required: true,
      autocomplete: 'email',
      mood: '/assets/images/mood/q01.jpg',
      caption: 'O recado da box',
      prompt: () => 'Qual e-mail usamos para o recado da box?',
      placeholder: 'voce@email.com',
      emptyError: 'Falta um e-mail para o recado da box.',
    },
    {
      stepKey: 'tutor',
      name: 'phone',
      chapter: 'contato',
      type: 'tel',
      required: true,
      autocomplete: 'tel',
      mood: '/assets/images/mood/q01.jpg',
      caption: 'Um aviso quando sair',
      prompt: () => 'Qual WhatsApp avisamos quando a box sair?',
      placeholder: '(11) 90000-0000',
      emptyError: 'Falta um telefone para avisar da box.',
    },
    {
      stepKey: 'confirm',
      name: 'consent',
      chapter: 'contato',
      type: 'confirm',
      mood: '/assets/images/mood/q10.jpg',
      caption: 'Quase lá',
      prompt: () => `A box de ${petName()} está quase pronta.`,
    },
  ];

  let moodBusy = false;
  let moodPending = null;
  let activeMoodSrc = moodImg ? moodImg.getAttribute('src') : '';
  let token = localStorage.getItem(STORAGE_KEY) || '';
  let qIndex = 0;
  let direction = 'forward';
  let navLock = false;
  let advanceTimer = 0;
  /** @type {Record<string, Record<string, any>>} */
  let answers = {};

  function showError(msg) {
    if (!errEl) return;
    if (!msg) {
      errEl.hidden = true;
      errEl.textContent = '';
      return;
    }
    errEl.hidden = false;
    errEl.textContent = msg;
  }

  function track(name, data) {
    if (window.ClubinhoTrack && typeof ClubinhoTrack.track === 'function') {
      try { ClubinhoTrack.track(name, data || {}); } catch (_) {}
    }
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

  function loadDraft(forToken) {
    try {
      const raw = JSON.parse(localStorage.getItem(DRAFT_KEY) || 'null');
      if (!raw || raw.token !== forToken) return null;
      return raw;
    } catch (_) {
      return null;
    }
  }

  function persistDraft() {
    if (!token) return;
    localStorage.setItem(DRAFT_KEY, JSON.stringify({ token, answers, qIndex }));
  }

  function mergeAnswers(server, draft) {
    const out = { ...(server || {}) };
    Object.entries(draft || {}).forEach(([key, value]) => {
      out[key] = { ...(out[key] || {}), ...(value || {}) };
    });
    return out;
  }

  function val(stepKey, name) {
    return answers[stepKey]?.[name];
  }

  function setVal(stepKey, name, value) {
    if (!answers[stepKey]) answers[stepKey] = {};
    answers[stepKey][name] = value;
    persistDraft();
  }

  function isEmpty(q) {
    const v = val(q.stepKey, q.name);
    if (q.type === 'multi') return !Array.isArray(v) || v.length === 0;
    if (q.type === 'number') return v === undefined || v === null || v === '';
    if (q.type === 'confirm') return !answers.confirm?.consent_lgpd;
    return v === undefined || v === null || String(v).trim() === '';
  }

  function answered(q) {
    if (q.type === 'confirm') {
      const bucket = answers.confirm;
      return !!bucket && (Object.prototype.hasOwnProperty.call(bucket, 'consent_lgpd')
        || Object.prototype.hasOwnProperty.call(bucket, 'consent_marketing'));
    }
    const bucket = answers[q.stepKey];
    return !!bucket && Object.prototype.hasOwnProperty.call(bucket, q.name);
  }

  function firstUnanswered() {
    for (let i = 0; i < questions.length; i += 1) {
      if (!answered(questions[i])) return i;
    }
    return questions.length - 1;
  }

  function textOf(fnOrStr) {
    return typeof fnOrStr === 'function' ? fnOrStr() : (fnOrStr || '');
  }

  function chapterById(id) {
    return chapters.find((c) => c.id === id) || chapters[0];
  }

  function chapterIndex(id) {
    return Math.max(0, chapters.findIndex((c) => c.id === id));
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
    const draft = loadDraft(token);
    if (draft?.answers) answers = mergeAnswers(answers, draft.answers);
    if (draft && Number.isInteger(draft.qIndex)) {
      qIndex = Math.max(0, Math.min(questions.length - 1, draft.qIndex));
    } else {
      qIndex = firstUnanswered();
    }
    return true;
  }

  function absoluteSrc(src) {
    try {
      return new URL(src, window.location.origin).href;
    } catch (_) {
      return src;
    }
  }

  function activeMoodEl() {
    if (moodImgNext && moodImgNext.classList.contains('is-active')) return moodImgNext;
    return moodImg;
  }

  function setMood(src, caption) {
    if (!moodImg) {
      if (moodCaption) moodCaption.textContent = caption;
      return;
    }
    const next = { src, caption };
    if (moodBusy) {
      moodPending = next;
      return;
    }
    if (absoluteSrc(activeMoodSrc || '') === absoluteSrc(src)) {
      if (moodCaption) moodCaption.textContent = caption;
      return;
    }
    if (prefersReducedMotion || !moodImgNext) {
      moodImg.src = src;
      moodImg.classList.add('is-active');
      if (moodImgNext) moodImgNext.classList.remove('is-active');
      activeMoodSrc = src;
      if (moodCaption) moodCaption.textContent = caption;
      return;
    }

    moodBusy = true;
    const outgoing = activeMoodEl();
    const incoming = outgoing === moodImg ? moodImgNext : moodImg;
    const done = () => {
      activeMoodSrc = src;
      moodBusy = false;
      if (moodPending) {
        const pending = moodPending;
        moodPending = null;
        setMood(pending.src, pending.caption);
      }
    };
    const swapCaption = () => {
      if (!moodCaption) return;
      moodCaption.classList.add('is-fading');
      window.setTimeout(() => {
        moodCaption.textContent = caption;
        moodCaption.classList.remove('is-fading');
      }, 180);
    };
    const startFade = () => {
      incoming.classList.add('is-active');
      outgoing.classList.remove('is-active');
      swapCaption();
      window.setTimeout(done, 500);
    };
    if (incoming.getAttribute('src') === src && incoming.complete && incoming.naturalWidth > 0) {
      startFade();
      return;
    }
    incoming.onload = () => {
      incoming.onload = null;
      startFade();
    };
    incoming.onerror = () => {
      incoming.onerror = null;
      moodBusy = false;
      if (moodCaption) moodCaption.textContent = caption;
    };
    incoming.src = src;
  }

  function clearAck() {
    if (!ackEl) return;
    ackEl.hidden = true;
    ackEl.textContent = '';
  }

  function flashAck(text) {
    if (!ackEl || !text || prefersReducedMotion) return;
    ackEl.hidden = false;
    ackEl.textContent = text;
  }

  function renderChapters(currentId) {
    if (!chaptersEl) return;
    const currentAt = chapterIndex(currentId);
    chaptersEl.innerHTML = chapters.map((c, i) => {
      const cls = i === currentAt ? 'is-current' : (i < currentAt ? 'is-done' : '');
      const current = i === currentAt ? ' aria-current="step"' : '';
      return `<li class="${cls}"${current}>${escapeHtml(c.label)}</li>`;
    }).join('');
  }

  function refreshNext(q) {
    if (q.type === 'confirm') {
      nextBtn.hidden = false;
      nextBtn.textContent = 'Ver a box';
    } else if (q.type === 'choice' && q.required) {
      nextBtn.hidden = true;
    } else if (q.type === 'choice') {
      nextBtn.hidden = false;
      nextBtn.textContent = 'Pular';
    } else {
      nextBtn.hidden = false;
      const optionalEmpty = !q.required && isEmpty(q);
      nextBtn.textContent = optionalEmpty ? 'Pular' : 'Continuar';
    }
    prevBtn.hidden = qIndex === 0;
    if (nav) nav.hidden = prevBtn.hidden && nextBtn.hidden;
  }

  function render() {
    const q = questions[qIndex];
    clearTimeout(advanceTimer);
    showError('');
    clearAck();
    if (progressBar) {
      progressBar.style.width = `${((qIndex + 1) / questions.length) * 100}%`;
    }
    if (progressLabel) progressLabel.textContent = chapterById(q.chapter).label;
    renderChapters(q.chapter);
    setMood(q.mood, q.caption);
    refreshNext(q);

    const dirClass = direction === 'back' ? 'is-back' : 'is-forward';
    const title = textOf(q.prompt);
    const hint = textOf(q.hint);
    let html = `<div class="quiz-question active ${dirClass}">`;
    html += `<p class="quiz-kicker">${escapeHtml(chapterById(q.chapter).label)}</p>`;
    html += `<h1 class="question-title">${escapeHtml(title)}</h1>`;
    if (hint) html += `<p class="quiz-hint">${escapeHtml(hint)}</p>`;

    if (q.type === 'confirm') {
      html += renderConfirm();
    } else if (q.type === 'text' || q.type === 'email' || q.type === 'tel' || q.type === 'number') {
      const current = val(q.stepKey, q.name);
      const autocomplete = q.autocomplete ? ` autocomplete="${escapeAttr(q.autocomplete)}"` : ' autocomplete="off"';
      const extra = q.type === 'number' ? ' inputmode="numeric" min="0" max="30"' : '';
      html += `<input class="quiz-input quiz-input--lg" data-field="${escapeAttr(q.name)}" type="${q.type}" value="${escapeAttr(current ?? '')}" placeholder="${escapeAttr(q.placeholder || '')}"${autocomplete}${extra}>`;
    } else if (q.type === 'textarea') {
      const current = val(q.stepKey, q.name);
      html += `<textarea class="quiz-input quiz-input--lg" data-field="${escapeAttr(q.name)}" rows="4" placeholder="${escapeAttr(q.placeholder || '')}">${escapeHtml(current ?? '')}</textarea>`;
    } else if (q.type === 'choice' || q.type === 'multi') {
      const layout = q.type === 'multi' ? 'quiz-options--chips' : 'quiz-options--cards';
      const current = val(q.stepKey, q.name);
      const selected = q.type === 'multi' ? (Array.isArray(current) ? current : []) : null;
      html += `<div class="quiz-options ${layout}">`;
      (q.options || []).forEach((opt) => {
        const on = q.type === 'multi' ? selected.includes(opt.value) : current === opt.value;
        const multi = q.type === 'multi' ? ' data-multi="1"' : '';
        html += `<button type="button" class="quiz-option${on ? ' selected' : ''}" data-field="${escapeAttr(q.name)}" data-value="${escapeAttr(opt.value)}"${multi}>${escapeHtml(opt.label)}</button>`;
      });
      html += `</div>`;
    }

    html += `</div>`;
    mount.innerHTML = html;
    bind(q);
    persistDraft();

    const focusTarget = mount.querySelector('.quiz-input') || mount.querySelector('.question-title');
    if (focusTarget) {
      if (focusTarget.classList.contains('question-title')) focusTarget.setAttribute('tabindex', '-1');
      focusTarget.focus({ preventScroll: true });
    }
  }

  function renderConfirm() {
    const species = answers.pet?.species === 'cat' ? 'gato' : (answers.pet?.species === 'dog' ? 'cachorro' : '');
    const size = { small: 'pequeno', medium: 'médio', large: 'grande' }[answers.pet?.size] || '';
    const energy = { low: 'calminho', medium: 'equilibrado', high: 'cheio de energia' }[answers.routine?.energy] || '';
    const bits = [];
    if (species) bits.push(size ? `${species} ${size}` : species);
    if (energy) bits.push(energy);
    const lgpd = answers.confirm?.consent_lgpd ? 'checked' : '';
    const marketing = answers.confirm?.consent_marketing ? 'checked' : '';
    let html = '';
    if (bits.length) html += `<p class="quiz-recap">${escapeHtml(bits.join(' · '))}</p>`;
    html += `<label class="quiz-consent"><input type="checkbox" data-field="consent_lgpd" ${lgpd}><span>Concordo com o uso dos meus dados e do perfil do pet apenas para personalizar a box e o atendimento (LGPD). Li a <a href="/privacidade" target="_blank" rel="noopener">Privacidade</a> e os <a href="/termos" target="_blank" rel="noopener">Termos</a>.</span></label>`;
    html += `<label class="quiz-consent"><input type="checkbox" data-field="consent_marketing" ${marketing}><span>Aceito receber novidades por e-mail e WhatsApp.</span></label>`;
    return html;
  }

  function bind(q) {
    mount.querySelectorAll('.quiz-option').forEach((btn) => {
      btn.addEventListener('click', () => onOption(q, btn));
    });
    mount.querySelectorAll('.quiz-input').forEach((input) => {
      input.addEventListener('input', () => {
        writeInput(q, input);
        refreshNext(q);
      });
      input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && input.tagName !== 'TEXTAREA') {
          event.preventDefault();
          goForward();
        }
      });
    });
    mount.querySelectorAll('input[type="checkbox"][data-field]').forEach((input) => {
      input.addEventListener('change', () => {
        if (!answers.confirm) answers.confirm = {};
        answers.confirm[input.getAttribute('data-field')] = input.checked;
        persistDraft();
      });
    });
  }

  function writeInput(q, input) {
    let v = input.value;
    if (input.type === 'number') v = v === '' ? '' : Number(v);
    setVal(q.stepKey, q.name, v);
  }

  function onOption(q, btn) {
    if (navLock) return;
    const value = btn.getAttribute('data-value');
    if (q.type === 'multi') {
      const cur = Array.isArray(val(q.stepKey, q.name)) ? [...val(q.stepKey, q.name)] : [];
      const idx = cur.indexOf(value);
      if (idx >= 0) cur.splice(idx, 1);
      else cur.push(value);
      setVal(q.stepKey, q.name, cur);
      btn.classList.toggle('selected');
      const picked = (q.options || []).find((opt) => opt.value === value);
      if (picked && cur.includes(value)) flashAck(`Anotado: ${picked.label.toLowerCase()}.`);
      refreshNext(q);
      return;
    }

    setVal(q.stepKey, q.name, value);
    mount.querySelectorAll('.quiz-option').forEach((el) => el.classList.remove('selected'));
    btn.classList.add('selected');
    const picked = (q.options || []).find((opt) => opt.value === value);
    flashAck(picked?.ack || (picked ? `Anotado: ${picked.label}.` : ''));
    clearTimeout(advanceTimer);
    const delay = prefersReducedMotion ? 0 : 280;
    advanceTimer = window.setTimeout(() => { goForward(); }, delay);
  }

  function markVisited(q) {
    if (q.type === 'confirm') {
      if (!answers.confirm) answers.confirm = {};
      if (!Object.prototype.hasOwnProperty.call(answers.confirm, 'consent_lgpd')) {
        answers.confirm.consent_lgpd = false;
      }
      persistDraft();
      return;
    }
    if (!answered(q)) {
      if (q.type === 'multi') setVal(q.stepKey, q.name, []);
      else if (q.type === 'number') setVal(q.stepKey, q.name, '');
      else setVal(q.stepKey, q.name, '');
    }
  }

  function validate(q) {
    if (q.type === 'confirm') {
      return answers.confirm?.consent_lgpd ? '' : 'Marque o consentimento para ver a box.';
    }
    if (!q.required) return '';
    if (q.type === 'multi' && isEmpty(q)) return q.emptyError || 'Marca ao menos uma opção.';
    if (q.type === 'email') {
      const email = String(val(q.stepKey, q.name) || '').trim();
      if (!email) return q.emptyError || 'Falta um e-mail.';
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return 'Esse e-mail parece incompleto.';
      return '';
    }
    if (q.type === 'number') {
      const n = val(q.stepKey, q.name);
      if (n === '' || n === undefined || n === null || Number.isNaN(Number(n)) || Number(n) < 0) {
        return q.emptyError || 'Conta a idade para a gente seguir.';
      }
      return '';
    }
    if (isEmpty(q)) return q.emptyError || 'Escolhe uma opção para seguir.';
    return '';
  }

  function syncDom(q) {
    const input = mount.querySelector('.quiz-input');
    if (input) writeInput(q, input);
  }

  async function commitStep(stepKey) {
    await api('POST', `/api/v1/quiz/sessions/${token}/save`, {
      step: STEP_NUM[stepKey] || 1,
      step_key: stepKey,
      answers: answers[stepKey] || {},
    });
  }

  async function moveTo(next, dir) {
    const from = questions[qIndex];
    const to = questions[next];
    if (!to || navLock) return;
    if (from.stepKey !== to.stepKey) {
      navLock = true;
      root.classList.add('is-saving');
      try {
        await commitStep(from.stepKey);
      } catch (e) {
        showError(e.message || 'Erro ao salvar.');
        return;
      } finally {
        navLock = false;
        root.classList.remove('is-saving');
      }
    }
    direction = dir;
    qIndex = next;
    track('quiz_step', { step: qIndex + 1, step_key: to.stepKey });
    render();
  }

  async function goForward() {
    if (navLock) return;
    clearTimeout(advanceTimer);
    const q = questions[qIndex];
    syncDom(q);
    const err = validate(q);
    if (err) {
      showError(err);
      return;
    }
    markVisited(q);
    if (qIndex >= questions.length - 1) {
      await finish();
      return;
    }
    await moveTo(qIndex + 1, 'forward');
  }

  async function goBack() {
    if (navLock || qIndex <= 0) return;
    clearTimeout(advanceTimer);
    syncDom(questions[qIndex]);
    await moveTo(qIndex - 1, 'back');
  }

  async function finish() {
    if (navLock) return;
    navLock = true;
    nextBtn.disabled = true;
    root.classList.add('is-saving');
    try {
      const data = answers.confirm || {};
      const result = await api('POST', `/api/v1/quiz/sessions/${token}/complete`, {
        step_key: 'confirm',
        answers: {
          consent_lgpd: !!data.consent_lgpd,
          consent_marketing: !!data.consent_marketing,
        },
        consent_lgpd: !!data.consent_lgpd,
        consent_marketing: !!data.consent_marketing,
      });
      localStorage.removeItem(STORAGE_KEY);
      localStorage.removeItem(DRAFT_KEY);
      showCompleted(result.suggested_plan || 'love', result.checkout_url);
      track('quiz_complete', { plan: result.suggested_plan });
    } catch (e) {
      showError(e.message || 'Erro ao salvar.');
      navLock = false;
      nextBtn.disabled = false;
      root.classList.remove('is-saving');
    }
  }

  function traitChips() {
    const chips = [];
    const energyMap = { low: 'Calminho', medium: 'Equilibrado', high: 'Cheio de energia' };
    const traitMap = {
      playful: 'Brincalhão',
      destructive: 'Destruidor',
      calm: 'Calmo',
      curious: 'Curioso',
      anxious: 'Ansioso',
      picky: 'Seletivo',
      social: 'Sociável',
    };
    const sizeMap = { small: 'Porte pequeno', medium: 'Porte médio', large: 'Porte grande' };
    if (answers.routine?.energy && energyMap[answers.routine.energy]) chips.push(energyMap[answers.routine.energy]);
    const traits = Array.isArray(answers.personality?.traits) ? answers.personality.traits : [];
    traits.forEach((t) => {
      if (chips.length >= 3) return;
      if (traitMap[t]) chips.push(traitMap[t]);
    });
    if (chips.length < 3 && sizeMap[answers.pet?.size]) chips.push(sizeMap[answers.pet.size]);
    return chips.slice(0, 3);
  }

  function showCompleted(plan, checkoutUrl) {
    const meta = PLANS[plan] || PLANS.love;
    const pet = petName();
    if (introEl) introEl.hidden = true;
    if (playEl) playEl.hidden = true;
    if (!resultEl) return;
    resultEl.hidden = false;
    resultEl.classList.add('active');
    const icon = document.getElementById('resultIcon');
    if (icon) {
      const img = document.createElement('img');
      img.src = meta.img;
      img.alt = meta.name;
      img.width = 400;
      img.height = 400;
      img.addEventListener('error', () => {
        img.src = '/assets/images/mood/q10.jpg';
      });
      icon.replaceChildren(img);
    }
    const warm = document.getElementById('resultWarm');
    const title = document.getElementById('resultTitle');
    const subtitle = document.getElementById('resultSubtitle');
    const lead = document.getElementById('quizResultLead');
    if (warm) warm.textContent = meta.line;
    if (title) title.textContent = `A box de ${pet}`;
    if (subtitle) subtitle.textContent = meta.name;
    if (lead) lead.textContent = `Esse plano combina com o jeito de ${pet}.`;
    const list = document.getElementById('resultTraits');
    if (list) {
      list.replaceChildren();
      traitChips().forEach((chip) => {
        const li = document.createElement('li');
        li.textContent = chip;
        list.appendChild(li);
      });
    }
    const link = document.getElementById('quizCheckout');
    if (link) {
      link.href = checkoutUrl || `/checkout?plan=${encodeURIComponent(plan)}&billing=monthly`;
      const short = meta.name.replace('Clubinho Pet ', '');
      link.textContent = `Assinar ${short}`;
    }
  }

  function hasProgress() {
    if (qIndex > 0) return true;
    return Object.keys(answers).some((key) => answers[key] && Object.keys(answers[key]).length > 0);
  }

  function begin() {
    if (introEl) introEl.hidden = true;
    if (playEl) playEl.hidden = false;
    track('quiz_start');
    render();
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  }
  function escapeAttr(s) {
    return escapeHtml(s).replace(/`/g, '');
  }

  let sessionReady = false;

  function boot() {
    sessionReady = false;
    if (startBtn) {
      startBtn.disabled = true;
      startBtn.textContent = 'Preparando…';
    }
    ensureSession().then((ok) => {
      if (!ok) return;
      sessionReady = true;
      if (startBtn) {
        startBtn.disabled = false;
        startBtn.textContent = 'Começar';
      }
      if (hasProgress()) begin();
    }).catch((e) => {
      showError(e.message || 'Não foi possível iniciar o quiz.');
      if (startBtn) {
        startBtn.disabled = false;
        startBtn.textContent = 'Tentar de novo';
      }
    });
  }

  prevBtn.addEventListener('click', () => { goBack(); });
  nextBtn.addEventListener('click', () => { goForward(); });
  if (startBtn) {
    startBtn.addEventListener('click', () => {
      if (startBtn.disabled) return;
      if (!sessionReady) {
        boot();
        return;
      }
      begin();
    });
  }

  boot();
})();
