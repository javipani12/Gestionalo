(() => {
  const TIEMPO_TRANSICION_MS = 160;

  // Evita animar enlaces que no deben pasar por la transicion.
  function omitirEnlace(enlace) {
    if (!enlace) return true;
    if (enlace.target && enlace.target !== '_self') return true;
    if (enlace.hasAttribute('download')) return true;
    if (enlace.getAttribute('href')?.startsWith('#')) return true;
    if (enlace.getAttribute('href')?.startsWith('javascript:')) return true;
    return false;
  }

  // Aplica la salida visual y navega cuando termina el retardo.
  function navegarConTransicion(url) {
    if (!url) return;

    document.body.classList.add('page-leaving');
    window.setTimeout(() => {
      window.location.href = url;
    }, TIEMPO_TRANSICION_MS);
  }

  document.addEventListener('click', (event) => {
    const elemento = event.target;
    if (!(elemento instanceof Element)) return;

    const enlaceNuevaPestana = elemento.closest('a[target="_blank"]');
    if (enlaceNuevaPestana instanceof HTMLAnchorElement) {
      event.preventDefault();
      window.open(enlaceNuevaPestana.href, '_blank', 'noopener,noreferrer');
      return;
    }

    const enlace = elemento.closest('a.js-page-transition');
    if (enlace && !omitirEnlace(enlace)) {
      event.preventDefault();
      navegarConTransicion(enlace.href);
      return;
    }

    const boton = elemento.closest('button.js-page-transition[data-url]');
    if (boton) {
      event.preventDefault();
      navegarConTransicion(boton.dataset.url);
    }
  });
})();
