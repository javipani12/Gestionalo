const form = document.getElementById('formulario-registro');
const btnVolver = document.getElementById('btn-volver');
const btnAnterior = document.getElementById('btn-anterior');
const btnSiguiente = document.getElementById('btn-siguiente');
const submitBtn = document.getElementById('submit');
const loginLink = document.querySelector('.login-link');
const progressFill = document.getElementById('progress-fill');
const progressSteps = document.querySelectorAll('.progress-step');

let pasoActual = 0;
const pasos = document.querySelectorAll('.step');
const ultimoPaso = pasos.length - 1;

/**
 * Actualiza el progreso de la barra de progreso
 * @param {number} indicePaso 
 */
function actualizarProgreso(indicePaso) {
	const porcentaje = ultimoPaso === 0 ? 100 : (indicePaso / ultimoPaso) * 100;
	if (progressFill) {
		progressFill.style.width = `${porcentaje}%`;
	}

	progressSteps.forEach((step, index) => {
		step.classList.toggle('active', index === indicePaso);
		step.classList.toggle('done', index < indicePaso);
	});
}

/**
 * Valida el paso actual
 * @param {number} indicePaso 
 * @returns {boolean}
 */
function validarPaso(indicePaso) {
	const campos = pasos[indicePaso].querySelectorAll('input, select, textarea');

	for (const campo of campos) {
		if (!campo.checkValidity()) {
			campo.reportValidity();
			return false;
		}
	}

	if (indicePaso === 2) {
		const pass = form.querySelector('input[name="contrasena"]');
		const pass2 = form.querySelector('input[name="contrasena2"]');

		if (pass && pass2 && pass.value !== pass2.value) {
			pass2.setCustomValidity('Las contrasenas no coinciden.');
			pass2.reportValidity();
			pass2.setCustomValidity('');
			return false;
		}
	}

	return true;
}

/**
 * Actualiza la navegación del formulario
 * @param {number} indicePaso 
 */
function pintarNavegacion(indicePaso) {
	btnVolver.style.display = indicePaso === 0 ? 'inline-flex' : 'none';
	btnAnterior.style.display = indicePaso > 0 ? 'inline-flex' : 'none';
	btnSiguiente.style.display = 'inline-flex';
	submitBtn.style.display = 'none';

	if (loginLink) {
		loginLink.style.display = indicePaso === 0 ? 'block' : 'none';
	}

	btnSiguiente.textContent = indicePaso === ultimoPaso ? 'Crear cuenta' : 'Siguiente';
}

/**
 * Muestra el paso actual
 * @param {number} indicePaso 
 */
function mostrarPaso(indicePaso) {
	pasos.forEach((paso, index) => {
		paso.classList.toggle('active', index === indicePaso);
	});

	actualizarProgreso(indicePaso);
	pintarNavegacion(indicePaso);
}


btnSiguiente.addEventListener('click', () => {
	if (!validarPaso(pasoActual)) {
		return;
	}

	if (pasoActual === ultimoPaso) {
		form.requestSubmit();
		return;
	}

	pasoActual += 1;
	mostrarPaso(pasoActual);
});

btnAnterior.addEventListener('click', () => {
	if (pasoActual === 0) {
		return;
	}

	pasoActual -= 1;
	mostrarPaso(pasoActual);
});

mostrarPaso(pasoActual);