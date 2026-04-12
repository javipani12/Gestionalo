const formulario = document.getElementById('formulario-registro');
const botonVolver = document.getElementById('btn-volver');
const botonAnterior = document.getElementById('btn-anterior');
const botonSiguiente = document.getElementById('btn-siguiente');
const botonEnviar = document.getElementById('submit');
const enlaceLogin = document.querySelector('.login-link');
const rellenoProgreso = document.getElementById('progress-fill');
const pasosProgreso = document.querySelectorAll('.progress-step');

let pasoActual = 0;
const pasos = document.querySelectorAll('.step');
const ultimoPaso = pasos.length - 1;

/**
 * Actualiza el progreso de la barra de progreso
 * @param {number} indicePaso 
 */
function actualizarProgreso(indicePaso) {
	const porcentaje = ultimoPaso === 0 ? 100 : (indicePaso / ultimoPaso) * 100;
	if (rellenoProgreso) {
		rellenoProgreso.style.width = `${porcentaje}%`;
	}

	pasosProgreso.forEach((step, index) => {
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

	// En el ultimo paso se comprueba que ambas contrasenas coincidan.
	if (indicePaso === 2) {
		const contrasena = formulario.querySelector('input[name="contrasena"]');
		const contrasenaConfirmacion = formulario.querySelector('input[name="contrasena2"]');

		if (contrasena && contrasenaConfirmacion && contrasena.value !== contrasenaConfirmacion.value) {
			contrasenaConfirmacion.setCustomValidity('Las contrasenas no coinciden.');
			contrasenaConfirmacion.reportValidity();
			contrasenaConfirmacion.setCustomValidity('');
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
	botonVolver.style.display = indicePaso === 0 ? 'inline-flex' : 'none';
	botonAnterior.style.display = indicePaso > 0 ? 'inline-flex' : 'none';
	botonSiguiente.style.display = 'inline-flex';
	botonEnviar.style.display = 'none';

	if (enlaceLogin) {
		enlaceLogin.style.display = indicePaso === 0 ? 'block' : 'none';
	}

	botonSiguiente.textContent = indicePaso === ultimoPaso ? 'Crear cuenta' : 'Siguiente';
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


botonSiguiente.addEventListener('click', () => {
	if (!validarPaso(pasoActual)) {
		return;
	}

	if (pasoActual === ultimoPaso) {
		formulario.requestSubmit();
		return;
	}

	pasoActual += 1;
	mostrarPaso(pasoActual);
});

botonAnterior.addEventListener('click', () => {
	if (pasoActual === 0) {
		return;
	}

	pasoActual -= 1;
	mostrarPaso(pasoActual);
});

mostrarPaso(pasoActual);