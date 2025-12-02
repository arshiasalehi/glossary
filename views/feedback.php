<div id="pane-feedback" class="pane hidden">
  <div class="rounded-xl border result-surface p-6 shadow-inner shadow-black/30 space-y-4">
    <div class="text-sm themed-muted mb-2">Feedback</div>
    <form id="feedback-form" action="https://formspree.io/f/mblnyonz" method="POST" class="space-y-3">
      <label class="block text-xs uppercase tracking-wide themed-muted" for="fb-email">Email (optional)</label>
      <input id="fb-email" name="_replyto" type="email" class="w-full px-3 py-2 rounded-lg border themed-input outline-none" placeholder="you@example.com" />

      <label class="block text-xs uppercase tracking-wide themed-muted" for="fb-text">Share your thoughts</label>
      <textarea id="fb-text" name="message" class="w-full h-40 rounded-lg border themed-input p-3" placeholder="Tell us what to improve..." required></textarea>

      <input type="hidden" name="_subject" value="Glossary Feedback" />
      <input type="hidden" name="origin" value="glossary-app" />

      <button type="submit" class="px-4 py-2 rounded-lg btn-primary w-fit">Submit</button>
      <div id="feedback-status" class="text-xs themed-muted"></div>
    </form>
  </div>
</div>
