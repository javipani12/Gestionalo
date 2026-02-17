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
  const datos = {};

  function animarCambio(desde, hacia, direccion) {
    if (desde === hacia) return Promise.resolve();
    const salida = pasos[desde];
    const entrada = pasos[hacia];

    return new Promise(resolve => {
      // preparar clases
      if (direccion === 'forward') {
        salida.classList.add('turn-out-forward');
        entrada.classList.add('turn-in-forward');
      } else if (direccion === 'back') {
        salida.classList.add('turn-out-back');
        entrada.classList.add('turn-in-back');
      }

      // asegurar que la entrada es visible para la animación
      entrada.classList.add('active');

      // después de la animación, limpiar y establecer estado
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
    // No deshabilitar el botón "Volver" en el primer paso — lo usaremos para volver a index.php
    btnSiguiente.style.display = ultimo ? 'none' : '';
    btnEnviar.style.display = ultimo ? '' : 'none';

    if (direccion) {
      animarCambio(anterior, indice, direccion).then(() => {
        actual = indice;
      });
    } else {
      pasos.forEach((s, i) => s.classList.toggle('active', i === indice));
      actual = indice;
    }
  }

  function validarPaso(indice) {
    const paso = pasos[indice];
    const entradas = Array.from(paso.querySelectorAll('input[required]'));
    for (const entrada of entradas) {
      if (!entrada.checkValidity()) {
        entrada.reportValidity();
        return false;
      }
    }
    // validación personalizada: comprobar que las contraseñas coinciden
    const pw = paso.querySelector('input[name="contrasena"]');
    const pw2 = paso.querySelector('input[name="contrasena2"]');
    if (pw && pw2 && pw.value !== pw2.value) {
      pw2.setCustomValidity('Las contraseñas no coinciden.');
      pw2.reportValidity();
      pw2.setCustomValidity('');
      return false;
    }
    return true;
  }

  function recopilarPaso(indice) {
    const paso = pasos[indice];
    const entradas = Array.from(paso.querySelectorAll('input'));
    entradas.forEach(i => {
      datos[i.name] = i.value;
    });
  }

  btnSiguiente.addEventListener('click', () => {
    if (!validarPaso(actual)) return;
    recopilarPaso(actual);
    const siguiente = Math.min(actual + 1, pasos.length - 1);
    mostrarPaso(siguiente, 'forward');
  });

  btnVolver.addEventListener('click', () => {
    if (actual === 0) {
      // Si estamos en el primer paso, volver a la página principal
      window.location.href = 'index.php';
      return;
    }
    const anterior = Math.max(actual - 1, 0);
    mostrarPaso(anterior, 'back');
  });

  formulario.addEventListener('submit', (ev) => {
    ev.preventDefault();
    if (!validarPaso(actual)) return;
    recopilarPaso(actual);
    // Aquí podrías enviar `datos` al servidor con fetch/XHR
    // Para demo mostramos resumen en pantalla
    cajaResultado.style.display = '';
    cajaResultado.classList.remove('error');
    cajaResultado.textContent = 'Registrando...';
    // Simular envío
    setTimeout(() => {
      cajaResultado.textContent = 'Registro completado. Datos: ' + JSON.stringify(datos);
      formulario.reset();
      mostrarPaso(0);
    }, 600);
  });

  // Inicializar
  mostrarPaso(0);
})();
