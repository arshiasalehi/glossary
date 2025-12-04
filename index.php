<?php
require __DIR__ . '/config.php';
require_once __DIR__ . '/models/Glossary.php';
require_once __DIR__ . '/models/Dashboard.php';
require_once __DIR__ . '/models/Feedback.php';
require_once __DIR__ . '/controllers/GlossaryController.php';
require_once __DIR__ . '/controllers/AuthController.php';

$started = session_status() === PHP_SESSION_ACTIVE;
if (!$started) {
    session_start();
}
$MODEL = env('GEMINI_MODEL', 'gemini-2.5-flash');
$GEMINI_API_KEY = env('GEMINI_API_KEY');

$glossary = new Glossary($MODEL, $GEMINI_API_KEY);
$dashboard = new Dashboard($glossary);
$feedbackModel = new Feedback();
$auth = new AuthController();
$glossary->seedSampleTerms();

// API handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $_GET['action'] ?? $input['action'] ?? $_POST['action'] ?? '';
    if ($action === 'login') {
        $auth->login($input);
    }
    if (in_array($action, ['list_terms', 'delete_term', 'create_term', 'update_term'], true)) {
        $auth->requireAuth();
    }
    $controller = new GlossaryController($glossary);
    $controller->handle($action, $input);
}

$termsCount = $dashboard->termCount();
$page = $_GET['page'] ?? 'glossary';
?>
<!doctype html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>French ↔ English Glossary</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { mono: ['JetBrains Mono', 'SFMono-Regular', 'Consolas', 'monospace'] },
        }
      },
      darkMode: 'class'
    };
  </script>
  <style>
    :root {
      --bg: #0b1221;
      --panel: #0f172a;
      --surface: #0b1221;
      --text: #e2e8f0;
      --muted: #94a3b8;
      --border: #1f2937;
      --accent: #22d3ee;
      --badge-bg: #0f172a;
      --shadow: rgba(0, 0, 0, 0.35);
      --on: 1;
      --shade-hue: 320;
      --glow-color: hsl(320, 40%, 45%);
      --glow-color-dark: hsl(320, 40%, 35%);
    }
    :root[data-theme="light"] {
      --bg: #f8fafc;
      --panel: #ffffff;
      --surface: #eef2ff;
      --text: #0f172a;
      --muted: #475569;
      --border: #cbd5e1;
      --accent: #0891b2;
      --badge-bg: #e2e8f0;
      --shadow: rgba(15, 23, 42, 0.1);
      --on: 0;
    }
    body {
      background: var(--bg);
      color: var(--text);
    }
    .themed-panel {
      background: var(--panel);
      border-color: var(--border);
      color: var(--text);
      box-shadow: 0 10px 30px var(--shadow);
    }
    .themed-surface {
      background: var(--surface);
      border-color: var(--border);
      color: var(--text);
    }
    .themed-muted { color: var(--muted); }
    .themed-input {
      background: var(--surface);
      border-color: var(--border);
      color: var(--text);
    }
    .themed-input:focus {
      border-color: var(--accent);
      box-shadow: 0 0 0 1px var(--accent);
    }
    .btn-primary {
      background: var(--accent);
      color: #0b1221;
      box-shadow: 0 10px 20px color-mix(in srgb, var(--accent) 45%, transparent);
    }
    .btn-primary:hover { filter: brightness(1.05); }
    .btn-secondary {
      background: var(--panel);
      color: var(--text);
      border-color: var(--border);
    }
    .status-chip, .badge-chip {
      background: var(--badge-bg);
      border-color: var(--border);
      color: var(--accent);
    }
    .result-surface {
      background: color-mix(in srgb, var(--surface) 85%, transparent);
      border-color: var(--border);
      color: var(--text);
    }

    /* Lamp toggle */
    .lamp-shell { width: 90px; height: 90px; cursor: pointer; }
    .lamp { width: 100%; height: 100%; }
    .lamp__tongue { fill: #e06952; }
    .lamp__hit { cursor: pointer; opacity: 0; }
    .lamp__feature { fill: #0a0a0a; }
    .lamp__stroke { stroke: #0a0a0a; }
    .lamp__mouth, .lamp__light { opacity: var(--on, 0); transition: opacity 0.3s ease; }
    .shade__opening { fill: hsl(50, calc((10 + (var(--on, 0) * 80)) * 1%), calc((20 + (var(--on, 0) * 70)) * 1%)); }
    .shade__opening-shade { opacity: calc(1 - var(--on, 0)); }
    .post__body { fill: hsl(var(--accent), 0%, calc((20 + (var(--on, 0) * 40)) * 1%)); }
    .base__top { fill: hsl(var(--accent), 0%, calc((40 + (var(--on, 0) * 40)) * 1%)); }
    .base__side { fill: hsl(var(--accent), 0%, calc((20 + (var(--on, 0) * 40)) * 1%)); }
    .top__body { fill: hsl(var(--shade-hue), calc((0 + (var(--on, 0) * 20)) * 1%), calc((30 + (var(--on, 0) * 60)) * 1%)); }
    .lamp__eye { transition: transform 0.4s ease; transform-origin: 50% 50%; transform: rotate(calc(180deg * (1 - var(--on, 0)))) translateY(calc(50% * (1 - var(--on, 0)))); }
    .tab-bar { display: grid; grid-auto-flow: column; grid-auto-columns: max-content; gap: 4px; align-items: center; }
    .tab-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 12px;
      border-radius: 10px;
      background: var(--panel);
      border: 1px solid var(--border);
      color: var(--text);
      font-size: 12px;
      transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
    }
    .tab-link .icon { font-size: 13px; }
    .tab-link.active {
      border-color: var(--accent);
      box-shadow: 0 0 0 1px color-mix(in srgb, var(--accent) 40%, transparent);
      background: color-mix(in srgb, var(--panel) 70%, var(--accent) 10%);
    }

    /* EVA loader */
    .modelViewPort {
      perspective: 1200px;
      width: 24rem;
      aspect-ratio: 1;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      background: #000;
      overflow: hidden;
    }
    .eva {
      --EVA-ROTATION-DURATION: 4s;
      transform-style: preserve-3d;
      animation: rotateRight var(--EVA-ROTATION-DURATION) linear infinite alternate;
    }
    .head {
      position: relative;
      width: 6rem;
      height: 4rem;
      border-radius: 48% 53% 45% 55% / 79% 79% 20% 22%;
      background: linear-gradient(to right, white 45%, gray);
    }
    .eyeChamber {
      width: 4.5rem;
      height: 2.75rem;
      position: relative;
      left: 50%;
      top: 55%;
      border-radius: 45% 53% 45% 48% / 62% 59% 35% 34%;
      background-color: #0c203c;
      box-shadow: 0px 0px 2px 2px white, inset 0px 0px 0px 2px black;
      transform: translate(-50%, -50%);
      animation: moveRight var(--EVA-ROTATION-DURATION) linear infinite alternate;
    }
    .eye {
      width: 1.2rem;
      height: 1.5rem;
      position: absolute;
      border-radius: 50%;
    }
    .eye:first-child {
      left: 12px;
      top: 50%;
      background: repeating-linear-gradient(65deg, #9bdaeb 0px, #9bdaeb 1px, white 2px);
      box-shadow: inset 0px 0px 5px #04b8d5, 0px 0px 15px 1px #0bdaeb;
      transform: translate(0, -50%) rotate(-65deg);
    }
    .eye:nth-child(2) {
      right: 12px;
      top: 50%;
      background: repeating-linear-gradient(-65deg, #9bdaeb 0px, #9bdaeb 1px, white 2px);
      box-shadow: inset 0px 0px 5px #04b8d5, 0px 0px 15px 1px #0bdaeb;
      transform: translate(0, -50%) rotate(65deg);
    }
    .body {
      width: 6rem;
      height: 8rem;
      position: relative;
      margin-block-start: 0.25rem;
      border-radius: 47% 53% 45% 55% / 12% 9% 90% 88%;
      background: linear-gradient(to right, white 35%, gray);
    }
    .hand {
      position: absolute;
      left: -1.5rem;
      top: 0.75rem;
      width: 2rem;
      height: 5.5rem;
      border-radius: 40%;
      background: linear-gradient(to left, white 15%, gray);
      box-shadow: 5px 0px 5px rgba(0, 0, 0, 0.25);
      transform: rotateY(55deg) rotateZ(10deg);
    }
    .hand:first-child { animation: compensateRotation var(--EVA-ROTATION-DURATION) linear infinite alternate; }
    .hand:nth-child(2) {
      left: 92%;
      background: linear-gradient(to right, white 15%, gray);
      transform: rotateY(55deg) rotateZ(-10deg);
      animation: compensateRotationRight var(--EVA-ROTATION-DURATION) linear infinite alternate;
    }
    .scannerThing {
      width: 0;
      height: 0;
      position: absolute;
      left: 60%;
      top: 8%;
      border-top: 240px solid #9bdaeb;
      border-left: 320px solid transparent;
      border-right: 320px solid transparent;
      transform-origin: top left;
      mask: linear-gradient(to right, white, transparent 35%);
      animation: none;
    }
    .scannerOrigin {
      position: absolute;
      width: 12px;
      aspect-ratio: 1;
      border-radius: 50%;
      left: 60%;
      top: 8%;
      background: #9bdaeb;
      box-shadow: inset 0px 0px 5px rgba(0, 0, 0, 0.5);
      animation: none;
    }
    .scan-on .scannerThing { animation: glow 2s cubic-bezier(0.86, 0, 0.07, 1) infinite; }
    .scan-on .scannerOrigin { animation: moveRight var(--EVA-ROTATION-DURATION) linear infinite; }
    @keyframes rotateRight { from { transform: rotateY(0deg); } to { transform: rotateY(25deg); } }
    @keyframes moveRight { from { transform: translate(-50%, -50%); } to { transform: translate(-40%, -50%); } }
    @keyframes compensateRotation { from { transform: rotateY(55deg) rotateZ(10deg); } to { transform: rotatey(30deg) rotateZ(10deg); } }
    @keyframes compensateRotationRight { from { transform: rotateY(55deg) rotateZ(-10deg); } to { transform: rotateY(70deg) rotateZ(-10deg); } }
    @keyframes glow { from { opacity: 0; } 20% { opacity: 1; } 45% { transform: rotate(-25deg); } 75% { transform: rotate(5deg); } 100% { opacity: 0; } }
  </style>
</head>
<body class="font-mono transition-colors duration-300">
  <div class="min-h-screen flex items-center justify-center px-6 py-12">
    <div class="w-full max-w-7xl">
      <div class="themed-panel border rounded-2xl shadow-2xl overflow-hidden min-h-[720px]">
        <div class="flex items-center gap-2 px-5 py-3" style="background: color-mix(in srgb, var(--panel) 85%, #000);">
          <span class="w-3 h-3 rounded-full bg-red-500"></span>
          <span class="w-3 h-3 rounded-full bg-amber-400"></span>
          <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
          <div class="tab-bar ml-4">
            <button class="tab-link btn-secondary" data-target="pane-main">
              <span>IT Glossary</span>
            </button>
            <button class="tab-link btn-secondary" data-target="pane-admin">
              <span>Dashboard</span>
            </button>
            <button class="tab-link btn-secondary" data-target="pane-feedback">
              <span>Feedback</span>
            </button>
          </div>
          <span class="ml-auto text-xs themed-muted" id="file-name">glossary/app.php</span>
        </div>

        
        <div class="p-7 space-y-6" style="background: linear-gradient(135deg, color-mix(in srgb, var(--panel) 95%, transparent), color-mix(in srgb, var(--bg) 90%, transparent));">
          <?php include __DIR__ . '/views/glossary.php'; ?>
          <?php include __DIR__ . '/views/dashboard.php'; ?>
          <?php include __DIR__ . '/views/feedback.php'; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    const statusEl = document.getElementById('status');
    const resultEl = document.getElementById('result');
    const termInput = document.getElementById('term');
    const searchBtn = document.getElementById('search');
    const dirFrEnBtn = document.getElementById('dir-fr-en');
    const dirEnFrBtn = document.getElementById('dir-en-fr');
    const directionNote = document.getElementById('direction-note');
    const labelTerm = document.getElementById('label-term');
    const lampToggle = document.getElementById('lamp-toggle');
    const loaderEl = document.getElementById('loader');
    const tabs = document.querySelectorAll('.tab-link');
    const panes = document.querySelectorAll('.pane');
    const adminLoginBtn = document.getElementById('admin-login');
    const adminMsg = document.getElementById('admin-message');
    const adminUser = document.getElementById('admin-username');
    const adminPass = document.getElementById('admin-password');
    const adminPanel = document.getElementById('admin-panel');
    const refreshTermsBtn = document.getElementById('refresh-terms');
    const termsList = document.getElementById('terms-list');
    const adminLoginForm = document.getElementById('admin-login-form');
    const adminSearch = document.getElementById('admin-search');
    const termIdField = document.getElementById('term-id');
    const termFrField = document.getElementById('term-fr');
    const termEnField = document.getElementById('term-en');
    const termDefFrField = document.getElementById('term-def-fr');
    const termDefEnField = document.getElementById('term-def-en');
    const saveTermBtn = document.getElementById('save-term');
    const clearTermBtn = document.getElementById('clear-term');
    const termFormMsg = document.getElementById('term-form-message');
    const feedbackForm = document.getElementById('feedback-form');
    const feedbackStatus = document.getElementById('feedback-status');
    const termCatField = document.getElementById('term-category');
    const adminFilterCat = document.getElementById('admin-filter-category');

    const categories = <?php echo json_encode(Glossary::CATEGORIES); ?>;

    let direction = 'fr_to_en';

    const uiText = {
      fr: {
        title: 'Glossaire Français ↔ Anglais',
        topline: 'Glossaire · Style éditeur',
        termLabel: 'Terme',
        placeholder: 'Saisir un terme...',
        directionNote: 'Direction : Français vers Anglais',
        search: 'Chercher',
        statusIdle: 'Statut : en attente',
        statusReady: 'Statut : prêt',
        statusOk: (source) => `Statut : ok (${source})`,
        statusSearching: 'Statut : recherche...',
        statusError: (msg) => `Statut : erreur - ${msg}`,
        fromDb: 'Depuis la base',
        fromAi: 'Depuis Gemini',
        french: 'Français',
        frenchDef: 'Définition (FR)',
        english: 'Anglais',
        englishDef: 'Définition (EN)',
      },
      en: {
        title: 'French ↔ English Glossary',
        topline: 'Glossary · Code Editor Style',
        termLabel: 'Term',
        placeholder: 'Enter a term...',
        directionNote: 'Direction: English to French',
        search: 'Search',
        statusIdle: 'Status: idle',
        statusReady: 'Status: ready',
        statusOk: (source) => `Status: ok (${source})`,
        statusSearching: 'Status: searching...',
        statusError: (msg) => `Status: error - ${msg}`,
        fromDb: 'From database',
        fromAi: 'From Gemini',
        french: 'French',
        frenchDef: 'Definition (FR)',
        english: 'English',
        englishDef: 'Definition (EN)',
      }
    };

    function getLang() {
      return direction === 'fr_to_en' ? 'fr' : 'en';
    }

    function setScanner(active) {
      if (!loaderEl) return;
      loaderEl.classList.toggle('scan-on', active);
    }

    function setActivePane(targetId) {
      panes.forEach(p => p.classList.toggle('hidden', p.id !== targetId));
      tabs.forEach(t => {
        const active = t.dataset.target === targetId;
        t.classList.toggle('btn-primary', active);
        t.classList.toggle('btn-secondary', !active);
      });
    }

    function applyUI() {
      const lang = getLang();
      const t = uiText[lang];
      labelTerm.textContent = t.termLabel;
      termInput.placeholder = t.placeholder;
      directionNote.textContent = t.directionNote;
      searchBtn.textContent = t.search;
      statusEl.textContent = t.statusIdle;
    }

    function setDirection(dir) {
      direction = dir;
      dirFrEnBtn.classList.toggle('btn-primary', dir === 'fr_to_en');
      dirFrEnBtn.classList.toggle('btn-secondary', dir !== 'fr_to_en');
      dirEnFrBtn.classList.toggle('btn-primary', dir === 'en_to_fr');
      dirEnFrBtn.classList.toggle('btn-secondary', dir !== 'en_to_fr');
      applyUI();
    }

    async function ping() {
      try {
        const res = await fetch('?action=ping', {method:'POST'});
        const data = await res.json();
        const t = uiText[getLang()];
        statusEl.textContent = data.ok ? t.statusReady : t.statusError(data.error || 'error');
      } catch (err) {
        statusEl.textContent = uiText[getLang()].statusError('backend');
      }
    }

    function renderEntry(entry, source) {
      const t = uiText[getLang()];
      const badge = source === 'database' ? t.fromDb : t.fromAi;
      const html = `
        <div class="flex items-center gap-2 mb-3">
          <span class="text-xs px-2 py-1 rounded border badge-chip">${badge}</span>
          <span class="text-[11px] themed-muted">${new Date().toLocaleTimeString()}</span>
        </div>
        <div class="space-y-3 text-sm">
          <div>
            <span class="themed-muted">${t.french}:</span>
            <div class="mt-1" style="color: var(--accent)">${entry.french_term || '-'}</div>
          </div>
          <div>
            <span class="themed-muted">${t.frenchDef}:</span>
            <div class="mt-1">${entry.french_definition || '-'}</div>
          </div>
          <div>
            <span class="themed-muted">${t.english}:</span>
            <div class="mt-1" style="color: var(--accent)">${entry.english_term || '-'}</div>
          </div>
          <div>
            <span class="themed-muted">${t.englishDef}:</span>
            <div class="mt-1">${entry.english_definition || '-'}</div>
          </div>
          <div>
            <span class="themed-muted">Category:</span>
            <div class="mt-1">${entry.category || '-'}</div>
          </div>
        </div>
      `;
      resultEl.innerHTML = html;
      resultEl.classList.remove('hidden');
    }

    async function lookup() {
      const term = termInput.value.trim();
      if (!term) return;
      searchBtn.disabled = true;
      const t = uiText[getLang()];
      statusEl.textContent = t.statusSearching;
      resultEl.classList.add('hidden');
      setScanner(false);
      try {
        const res = await fetch('?action=lookup', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ term, direction })
        });
        const data = await res.json();
        if (!res.ok || data.error) {
          throw new Error(data.error || 'Request failed');
        }
        renderEntry(data.entry, data.source);
        setScanner(true);
        statusEl.textContent = t.statusOk(data.source);
      } catch (err) {
        statusEl.textContent = t.statusError(err.message);
        resultEl.classList.remove('hidden');
        resultEl.innerHTML = `<div class="text-rose-300">${err.message}</div>`;
        setScanner(true);
      } finally {
        searchBtn.disabled = false;
      }
    }

    async function adminLogin() {
      if (!adminUser || !adminPass || !adminMsg) return;
      adminMsg.textContent = 'Logging in...';
      try {
        const res = await fetch('?action=login', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ username: adminUser.value.trim(), password: adminPass.value })
        });
        const data = await res.json();
        if (!res.ok || data.error) throw new Error(data.error || 'Login failed');
        adminMsg.textContent = `Welcome, ${data.username}`;
        if (adminPanel) adminPanel.classList.remove('hidden');
        if (adminLoginForm) adminLoginForm.classList.add('hidden');
        await loadTerms();
      } catch (err) {
        adminMsg.textContent = err.message;
      }
    }

    async function loadTerms() {
      if (!termsList) return;
      const q = adminSearch?.value || '';
      const category = adminFilterCat?.value || '';
      termsList.textContent = 'Loading...';
      try {
        const res = await fetch('?action=list_terms', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({ q, category })
        });
        const data = await res.json();
        if (!res.ok || data.error) throw new Error(data.error || 'Load failed');
        const rows = data.terms || [];
        if (!rows.length) {
          termsList.textContent = 'No terms found.';
          return;
        }
        termsList.innerHTML = rows.map(r => `
          <div class="flex items-center justify-between rounded border border-transparent hover:border-slate-700 px-2 py-1">
            <div class="text-xs cursor-pointer" data-edit="${r.id}">
              <div><strong>${r.french_term}</strong> → ${r.english_term}</div>
              <div class="themed-muted">${r.french_definition || ''}</div>
              <div class="themed-muted text-[11px]">Category: ${r.category || '-'}</div>
            </div>
            <div class="flex gap-2">
              <button data-edit="${r.id}" class="px-2 py-1 text-xs rounded btn-secondary">Edit</button>
              <button data-del="${r.id}" class="px-2 py-1 text-xs rounded btn-secondary">Delete</button>
            </div>
          </div>
        `).join('');
        termsList.querySelectorAll('[data-edit]').forEach(btn => {
          btn.addEventListener('click', () => fillTermForm(rows.find(r => r.id == btn.dataset.edit)));
        });
        termsList.querySelectorAll('[data-del]').forEach(btn => {
          btn.addEventListener('click', async () => {
            const id = btn.dataset.del;
            try {
              const delRes = await fetch('?action=delete_term', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id })
              });
              const delData = await delRes.json();
              if (!delRes.ok || delData.error) throw new Error(delData.error || 'Delete failed');
              await loadTerms();
            } catch (err) {
              adminMsg.textContent = err.message;
            }
          });
        });
      } catch (err) {
        termsList.textContent = err.message;
      }
    }

    async function saveTerm() {
      if (!termFrField || !termEnField || !termFormMsg) return;
      termFormMsg.textContent = 'Saving...';
      const payload = {
        id: termIdField?.value || undefined,
        french_term: termFrField.value.trim(),
        english_term: termEnField.value.trim(),
        french_definition: termDefFrField?.value || '',
        english_definition: termDefEnField?.value || '',
        category: termCatField?.value || ''
      };
      const isUpdate = payload.id && Number(payload.id) > 0;
      const action = isUpdate ? 'update_term' : 'create_term';
      try {
        const res = await fetch(`?action=${action}`, {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok || data.error) throw new Error(data.error || 'Save failed');
        termFormMsg.textContent = 'Saved';
        await loadTerms();
        clearTermForm();
      } catch (err) {
        termFormMsg.textContent = err.message;
      }
    }

    function fillTermForm(term) {
      if (!term) return;
      if (termIdField) termIdField.value = term.id || '';
      if (termFrField) termFrField.value = term.french_term || '';
      if (termEnField) termEnField.value = term.english_term || '';
      if (termDefFrField) termDefFrField.value = term.french_definition || '';
      if (termDefEnField) termDefEnField.value = term.english_definition || '';
      if (termCatField) termCatField.value = term.category || '';
    }

    function clearTermForm() {
      if (termIdField) termIdField.value = '';
      if (termFrField) termFrField.value = '';
      if (termEnField) termEnField.value = '';
      if (termDefFrField) termDefFrField.value = '';
      if (termDefEnField) termDefEnField.value = '';
      if (termCatField) termCatField.value = '';
      if (termFormMsg) termFormMsg.textContent = '';
    }

    function applyTheme(useDark) {
      document.documentElement.dataset.theme = useDark ? 'dark' : 'light';
      document.documentElement.classList.toggle('dark', useDark);
      localStorage.setItem('theme', useDark ? 'dark' : 'light');
    }

    function initTheme() {
      const stored = localStorage.getItem('theme');
      const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      const useDark = stored ? stored === 'dark' : prefersDark;
      applyTheme(useDark);
    }

    if (lampToggle) {
      lampToggle.addEventListener('click', () => {
        const nextDark = document.documentElement.dataset.theme !== 'dark';
        applyTheme(nextDark);
        const hue = Math.floor(Math.random() * 360);
        document.documentElement.style.setProperty('--shade-hue', hue.toString());
      });
    }

    tabs.forEach(t => t.addEventListener('click', () => setActivePane(t.dataset.target)));
    if (adminLoginBtn) adminLoginBtn.addEventListener('click', adminLogin);
    if (refreshTermsBtn) refreshTermsBtn.addEventListener('click', loadTerms);
    if (saveTermBtn) saveTermBtn.addEventListener('click', saveTerm);
    if (clearTermBtn) clearTermBtn.addEventListener('click', clearTermForm);
    if (adminSearch) adminSearch.addEventListener('input', () => loadTerms());
    if (adminFilterCat) adminFilterCat.addEventListener('change', () => loadTerms());
    if (feedbackForm) {
      feedbackForm.addEventListener('submit', async (e) => {
        if (!feedbackStatus) return;
        feedbackStatus.textContent = 'Sending...';
        // allow native form post to Formspree; also let JS handle success message without navigation
        e.preventDefault();
        const formData = new FormData(feedbackForm);
        try {
          const res = await fetch(feedbackForm.action, {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
          });
          if (!res.ok) throw new Error('Failed to send feedback');
          feedbackStatus.textContent = 'Thank you for your feedback!';
          feedbackForm.reset();
        } catch (err) {
          feedbackStatus.textContent = err.message || 'Error sending feedback';
        }
      });
    }

    dirFrEnBtn.addEventListener('click', () => setDirection('fr_to_en'));
    dirEnFrBtn.addEventListener('click', () => setDirection('en_to_fr'));
    searchBtn.addEventListener('click', lookup);
    termInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') lookup(); });

    initTheme();
    setDirection('fr_to_en');
    applyUI();
    setActivePane('pane-main');
    ping();
  </script>
</body>
</html>
