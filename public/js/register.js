// Simple multi-step form handler for public/register.html
(function () {
  const form = document.getElementById('register-form');
  if (!form) return;

  const steps = Array.from(form.querySelectorAll('.step'));
  const btnNext = form.querySelector('.btn-next');
  const btnBack = form.querySelector('.btn-back');
  const btnSubmit = form.querySelector('.btn-submit');
  const resultBox = document.getElementById('reg-result');
  let current = 0;
  const data = {};

  function showStep(index) {
    steps.forEach((s, i) => s.classList.toggle('active', i === index));
    current = index;
    btnBack.disabled = index === 0;
    // show submit only on last step
    const last = index === steps.length - 1;
    btnNext.style.display = last ? 'none' : '';
    btnSubmit.style.display = last ? '' : 'none';
  }

  function validateStep(index) {
    const step = steps[index];
    const inputs = Array.from(step.querySelectorAll('input[required]'));
    for (const input of inputs) {
      if (!input.checkValidity()) {
        input.reportValidity();
        return false;
      }
    }
    // custom validation: passwords match on step that contains password fields
    const pw = step.querySelector('input[name="password"]');
    const pw2 = step.querySelector('input[name="password2"]');
    if (pw && pw2 && pw.value !== pw2.value) {
      pw2.setCustomValidity('Las contraseñas no coinciden.');
      pw2.reportValidity();
      pw2.setCustomValidity('');
      return false;
    }
    return true;
  }

  function collectStep(index) {
    const step = steps[index];
    const inputs = Array.from(step.querySelectorAll('input'));
    inputs.forEach(i => {
      data[i.name] = i.value;
    });
  }

  btnNext.addEventListener('click', () => {
    if (!validateStep(current)) return;
    collectStep(current);
    showStep(Math.min(current + 1, steps.length - 1));
  });

  btnBack.addEventListener('click', () => {
    showStep(Math.max(current - 1, 0));
  });

  form.addEventListener('submit', (ev) => {
    ev.preventDefault();
    if (!validateStep(current)) return;
    collectStep(current);
    // Aquí puedes enviar `data` al servidor mediante fetch/XHR
    // Para demo mostramos resumen en pantalla
    resultBox.style.display = '';
    resultBox.classList.remove('error');
    resultBox.textContent = 'Registrando...';
    // Simular envío
    setTimeout(() => {
      resultBox.textContent = 'Registro completado. Datos: ' + JSON.stringify(data);
      form.reset();
      showStep(0);
    }, 600);
  });

  // Inicializar
  showStep(0);
})();
