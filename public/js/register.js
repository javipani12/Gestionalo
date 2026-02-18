// Manejador simple de formulario por pasos para public/register.php
(function () {
  const formulario = document.getElementById('formulario-registro');
  if (!formulario) return;

  const pasos = Array.from(formulario.querySelectorAll('.step'));
  const btnSiguiente = formulario.querySelector('.btn-siguiente');
  const btnVolver = formulario.querySelector('.btn-volver');
  const btnEnviar = formulario.querySelector('.btn-enviar');
  const cajaResultado = document.getElementById('resultado-registro');
  let actual = 0;

  function animarCambio(desde, hacia, direccion) {
    if (desde === hacia) return Promise.resolve();
    const salida = pasos[desde];
    const entrada = pasos[hacia];

    return new Promise(resolve => {
      if (direccion === 'forward') {
        salida.classList.add('turn-out-forward');
        entrada.classList.add('turn-in-forward');
      } else if (direccion === 'back') {
        salida.classList.add('turn-out-back');
        entrada.classList.add('turn-in-back');
      }
      entrada.classList.add('active');
      setTimeout(() => {
        salida.classList.remove('active', 'turn-out-forward', 'turn-out-back');
        entrada.classList.remove('turn-in-forward', 'turn-in-back');
        resolve();
      }, 540);
    });
  }

  function mostrarPaso(indice, direccion) {
    const anterior = actual;
    const ultimo = indice === pasos.length - 1;
    btnSiguiente.style.display = ultimo ? 'none' : '';
    btnEnviar.style.display = ultimo ? '' : 'none';
    const card = document.querySelector('.card');
    function _updateVolver(idx) {
      if (!btnVolver) return;
      if (idx === 0) {
        btnVolver.textContent = 'Volver a inicio';
        btnVolver.setAttribute('aria-label', 'Volver a inicio');
      } else {
        btnVolver.textContent = 'Volver';
        btnVolver.setAttribute('aria-label', 'Volver');
      }
    }

    if (direccion) {
      animarCambio(anterior, indice, direccion).then(() => {
        actual = indice;
        // Asegurar que sólo el paso actual tiene la clase active
        pasos.forEach((s, i) => s.classList.toggle('active', i === indice));
        // aplicar/remover .shrink solo después de la animación para evitar mostrar todos los pasos
        if (card) {
          if (ultimo) card.classList.add('shrink'); else card.classList.remove('shrink');
        }
        _updateVolver(indice);
      });
    } else {
      pasos.forEach((s, i) => s.classList.toggle('active', i === indice));
      actual = indice;
      if (card) {
        if (ultimo) card.classList.add('shrink'); else card.classList.remove('shrink');
      }
      _updateVolver(indice);
    }
  }

  function validarPaso(indice) {
    const paso = pasos[indice];
    const entradas = Array.from(paso.querySelectorAll('input[required]'));
    for (const entrada of entradas) {
      if (entrada.type === 'checkbox') {
        if (!entrada.checked) {
          entrada.reportValidity();
          return false;
        }
        continue;
      }
      if (!entrada.checkValidity()) {
        entrada.reportValidity();
        return false;
      }
    }
    // Solo en el paso de contraseñas comprobar coincidencia
    const pw = formulario.querySelector('input[name="contrasena"]');
    const pw2 = formulario.querySelector('input[name="contrasena2"]');
    if (pw && pw2 && pw.value !== pw2.value) {
      pw2.setCustomValidity('Las contraseñas no coinciden.');
      pw2.reportValidity();
      pw2.setCustomValidity('');
      return false;
    }
    return true;
  }

  btnSiguiente.addEventListener('click', () => {
    if (!validarPaso(actual)) return;
    const siguiente = Math.min(actual + 1, pasos.length - 1);
    mostrarPaso(siguiente, 'forward');
  });

  btnVolver.addEventListener('click', () => {
    if (actual === 0) {
      window.location.href = 'index.php';
      return;
    }
    const anterior = Math.max(actual - 1, 0);
    mostrarPaso(anterior, 'back');
  });

  formulario.addEventListener('submit', (ev) => {
    ev.preventDefault();
    if (!validarPaso(actual)) return;
    // Validar todos los pasos antes de enviar
    for (let i = 0; i < pasos.length - 1; i++) {
      if (!validarPaso(i)) return;
    }
    const formData = new FormData(formulario);

    if (cajaResultado) {
      cajaResultado.style.display = '';
      cajaResultado.classList.remove('error');
      cajaResultado.textContent = 'Enviando...';
    }

    fetch('register_submit.php', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data && data.success) {
          // mostrar paso final inmediatamente (sin modal)
          const mensaje = document.getElementById('mensaje-final');
          if (mensaje) mensaje.textContent = data.message || 'Se ha enviado un correo para verificar la cuenta.';
          if (cajaResultado) cajaResultado.style.display = 'none';
          const nav = document.querySelector('.form-nav');
          if (nav) nav.style.display = 'none';
          mostrarPaso(pasos.length - 1, 'forward');
          formulario.reset();
        } else {
          if (cajaResultado) {
            cajaResultado.classList.add('error');
            cajaResultado.textContent = (data && data.error) ? data.error : 'Error en el registro';
          }
        }
      })
      .catch(err => {
        if (cajaResultado) {
          cajaResultado.classList.add('error');
          cajaResultado.textContent = 'Error de red. Inténtalo más tarde.';
        }
      });
  });

  // Inicializar
  mostrarPaso(0);

  // Modal removed: funcionalidad eliminada intencionadamente
})();
