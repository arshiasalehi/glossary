<div id="pane-main" class="pane space-y-6">
  <div class="flex flex-wrap items-center gap-2">
    <button id="dir-fr-en" class="px-3 py-2 rounded-md border btn-secondary hover:border-cyan-400 transition">Fr → En</button>
    <button id="dir-en-fr" class="px-3 py-2 rounded-md border btn-secondary hover:border-cyan-400 transition">En → Fr</button>
    <span class="text-xs themed-muted" id="direction-note">Direction: French to English</span>
    <span class="sr-only" id="status"></span>
    <div id="lamp-toggle" class="lamp-shell ml-auto">
      <?php include __DIR__ . '/partials/lamp.php'; ?>
    </div>
  </div>

  <div class="grid grid-cols-1 xl:grid-cols-[1.3fr_auto_1fr] gap-8 items-stretch">
    <div class="flex flex-col gap-3">
      <label class="text-xs uppercase tracking-wide themed-muted" for="term" id="label-term">Term</label>
      <input id="term" class="w-full px-4 py-4 text-lg rounded-xl border themed-input outline-none transition shadow-lg shadow-black/10" placeholder="Enter a term..." />
      <button id="search" class="w-full px-6 py-4 text-base rounded-xl font-semibold transition shadow-lg mt-2 btn-primary">Search</button>
    </div>

    <div class="flex justify-center items-center">
      <div id="loader" class="loader scale-90 md:scale-100 lg:scale-[1.05]">
        <?php include __DIR__ . '/partials/robot.php'; ?>
      </div>
    </div>

    <div id="result" class="hidden rounded-lg border result-surface p-5 text-base shadow-inner shadow-black/30 transition-all duration-300 min-h-[280px]"></div>
  </div>
</div>
