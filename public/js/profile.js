document.addEventListener('DOMContentLoaded', function () {
  var formulario = document.getElementById('profile-form');
  var botonEditar = document.getElementById('profile-edit-btn');
  var contenedorAcciones = document.getElementById('profile-actions');
  var botonGuardar = document.getElementById('profile-save-btn');

  if (!formulario || !botonEditar || !contenedorAcciones || !botonGuardar) {
    return;
  }

  var puedeEditarEmail = formulario.dataset.canEditEmail === '1';
  var selectorCampos = 'input[name="nombre"], input[name="apellido1"], input[name="apellido2"], input[name="localidad"], input[name="fecha_nacimiento"], input[name="passwd"]';

  if (puedeEditarEmail) {
    selectorCampos += ', input[name="email"]';
  }

  var camposEditables = formulario.querySelectorAll(selectorCampos);
  var valoresIniciales = {};
  var estaEnEdicion = false;

  // Guarda el valor original para poder cancelar cambios sin perder referencia.
  camposEditables.forEach(function (campo) {
    valoresIniciales[campo.name] = campo.value;
  });

  // Restaura el formulario al estado previo a la edicion.
  function restaurarValoresIniciales() {
    camposEditables.forEach(function (campo) {
      campo.value = valoresIniciales[campo.name] || '';
    });
  }

  // Habilita o bloquea los campos y actualiza los botones visibles.
  function establecerModoEdicion(estaEnEdicion) {
    camposEditables.forEach(function (campo) {
      campo.disabled = !estaEnEdicion;
    });

    contenedorAcciones.hidden = !estaEnEdicion;
    botonGuardar.disabled = !estaEnEdicion;
    botonEditar.textContent = estaEnEdicion ? 'Cancelar Edición' : 'Editar';

    if (estaEnEdicion) {
      var primerCampo = formulario.querySelector('input[name="nombre"]');
      if (primerCampo) {
        primerCampo.focus();
      }
    }
  }

  botonEditar.addEventListener('click', function () {
    if (estaEnEdicion) {
      restaurarValoresIniciales();
      establecerModoEdicion(false);
      estaEnEdicion = false;
      return;
    }

    establecerModoEdicion(true);
    estaEnEdicion = true;
  });

  establecerModoEdicion(false);
});
