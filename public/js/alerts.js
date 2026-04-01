document.addEventListener('DOMContentLoaded', function () {
  var alertas = document.querySelectorAll('.alert');

  alertas.forEach(function (alerta) {
    setTimeout(function () {
      alerta.style.transition = 'opacity 0.5s ease-out';
      alerta.style.opacity = '0';

      setTimeout(function () {
        alerta.remove();
      }, 500);
    }, 5000);
  });
});
