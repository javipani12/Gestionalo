(() => {
  const LEAVE_MS = 160;

  function shouldSkipLink(anchor) {
    if (!anchor) return true;
    if (anchor.target && anchor.target !== '_self') return true;
    if (anchor.hasAttribute('download')) return true;
    if (anchor.getAttribute('href')?.startsWith('#')) return true;
    if (anchor.getAttribute('href')?.startsWith('javascript:')) return true;
    return false;
  }

  function navigateWithTransition(url) {
    if (!url) return;

    document.body.classList.add('page-leaving');
    window.setTimeout(() => {
      window.location.href = url;
    }, LEAVE_MS);
  }

  document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Element)) return;

    const blankAnchor = target.closest('a[target="_blank"]');
    if (blankAnchor instanceof HTMLAnchorElement) {
      event.preventDefault();
      window.open(blankAnchor.href, '_blank', 'noopener,noreferrer');
      return;
    }

    const anchor = target.closest('a.js-page-transition');
    if (anchor && !shouldSkipLink(anchor)) {
      event.preventDefault();
      navigateWithTransition(anchor.href);
      return;
    }

    const button = target.closest('button.js-page-transition[data-url]');
    if (button) {
      event.preventDefault();
      navigateWithTransition(button.dataset.url);
    }
  });
})();
