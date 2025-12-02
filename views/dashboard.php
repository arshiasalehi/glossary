<div id="pane-admin" class="pane hidden">
  <div class="rounded-xl border result-surface p-6 shadow-inner shadow-black/30 space-y-4">
    <div class="text-sm themed-muted mb-3">Admin Login</div>
    <div class="space-y-3" id="admin-login-form">
      <label class="block text-xs uppercase tracking-wide themed-muted" for="admin-username">Username</label>
      <input id="admin-username" class="w-full px-3 py-2 rounded-lg border themed-input outline-none" placeholder="admin" />
      <label class="block text-xs uppercase tracking-wide themed-muted" for="admin-password">Password</label>
      <input id="admin-password" type="password" class="w-full px-3 py-2 rounded-lg border themed-input outline-none" placeholder="******" />
      <button id="admin-login" class="px-4 py-2 rounded-lg btn-primary">Login</button>
      <div id="admin-message" class="text-sm themed-muted"></div>
    </div>
    <div id="admin-panel" class="hidden space-y-4">
      <div class="flex items-center gap-2 justify-between">
        <div class="text-sm themed-muted">Terms</div>
        <input id="admin-search" class="px-3 py-1.5 rounded-lg border themed-input outline-none text-sm" placeholder="Search terms..." />
        <button id="refresh-terms" class="px-3 py-1 rounded-md btn-secondary">Refresh</button>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="space-y-2">
          <input type="hidden" id="term-id" />
          <label class="text-xs uppercase tracking-wide themed-muted" for="term-fr">French term</label>
          <input id="term-fr" class="w-full px-3 py-2 rounded-lg border themed-input outline-none" />
          <label class="text-xs uppercase tracking-wide themed-muted" for="term-en">English term</label>
          <input id="term-en" class="w-full px-3 py-2 rounded-lg border themed-input outline-none" />
          <label class="text-xs uppercase tracking-wide themed-muted" for="term-def-fr">French definition</label>
          <textarea id="term-def-fr" class="w-full h-20 rounded-lg border themed-input p-2"></textarea>
          <label class="text-xs uppercase tracking-wide themed-muted" for="term-def-en">English definition</label>
          <textarea id="term-def-en" class="w-full h-20 rounded-lg border themed-input p-2"></textarea>
          <div class="flex gap-2">
            <button id="save-term" class="px-4 py-2 rounded-lg btn-primary">Save</button>
            <button id="clear-term" class="px-4 py-2 rounded-lg btn-secondary">Clear</button>
          </div>
          <div id="term-form-message" class="text-xs themed-muted"></div>
        </div>
        <div>
          <div id="terms-list" class="text-sm space-y-2 max-h-80 overflow-y-auto pr-2"></div>
        </div>
      </div>
    </div>
  </div>
</div>
