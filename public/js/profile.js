document.addEventListener('DOMContentLoaded', function () {
  var formulario = document.getElementById('profile-form');
  var botonEditar = document.getElementById('profile-edit-btn');
  var botonCancelar = document.getElementById('profile-cancel-btn');
  var contenedorAcciones = document.getElementById('profile-actions');

  if (!formulario || !botonEditar || !botonCancelar || !contenedorAcciones) {
    return;
  }

  var botonGuardar = formulario.querySelector('button[type="submit"]');
  if (!botonGuardar) {
    return;
  }

  var camposEditables = formulario.querySelectorAll('input[name="nombre"], input[name="apellido1"], input[name="apellido2"], input[name="localidad"], input[name="fecha_nacimiento"], input[name="passwd"]');
  var valoresIniciales = {};

  camposEditables.forEach(function (campo) {
    valoresIniciales[campo.name] = campo.value;
  });

  function restaurarValoresIniciales() {
    camposEditables.forEach(function (campo) {
      campo.value = valoresIniciales[campo.name] || '';
    });
  }

  function establecerModoEdicion(estaEnEdicion) {
    camposEditables.forEach(function (campo) {
      campo.disabled = !estaEnEdicion;
    });

    contenedorAcciones.hidden = !estaEnEdicion;
    botonCancelar.disabled = !estaEnEdicion;
    botonGuardar.disabled = !estaEnEdicion;
    botonEditar.disabled = estaEnEdicion;

    if (estaEnEdicion) {
      var primerCampo = formulario.querySelector('input[name="nombre"]');
      if (primerCampo) {
        primerCampo.focus();
      }
    }
  }

  botonEditar.addEventListener('click', function () {
    establecerModoEdicion(true);
  });

  botonCancelar.addEventListener('click', function () {
    restaurarValoresIniciales();
    establecerModoEdicion(false);
  });

  establecerModoEdicion(false);
});
