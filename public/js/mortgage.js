(function () {
  const formulario = document.getElementById("mortgage-form");
  const botonRestablecer = document.getElementById("mortgage-reset");
  const FILAS_AMORTIZACION_POR_PAGINA = 12;

  const estadoPaginacionAmortizacion = {
    calendario: [],
    paginaActual: 1,
    totalPaginas: 1,
  };

  if (!formulario) {
    return;
  }

  const salidas = {
    cuota: document.getElementById("resultadoCuota"),
    capital: document.getElementById("resultadoCapital"),
    intereses: document.getElementById("resultadoIntereses"),
    pagado: document.getElementById("resultadoPagado"),
    ratio: document.getElementById("resultadoRatio"),
    plazoFinal: document.getElementById("resultadoPlazoFinal"),
    aportacionInicial: document.getElementById("resultadoAportacionInicial"),
    costeOperacion: document.getElementById("resultadoCosteOperacion"),
    ahorroIntereses: document.getElementById("resultadoAhorroIntereses"),
    ahorroPlazo: document.getElementById("resultadoAhorroPlazo"),
    tablaAmortizacion: document.querySelector("#tablaAmortizacion tbody"),
    tablaEscenarios: document.querySelector("#tablaEscenarios tbody"),
    grafico: document.getElementById("mortgageChart"),
    botonAmortAnterior: document.getElementById("amort-prev"),
    botonAmortSiguiente: document.getElementById("amort-next"),
    informacionPaginaAmortizacion: document.getElementById("amort-page-info"),
  };

  // Lee y normaliza un valor numérico del formulario.
  function obtenerNumero(name, fallback = 0) {
    const input = formulario.elements[name];
    const value = Number.parseFloat(input ? input.value : fallback);
    return Number.isFinite(value) ? value : fallback;
  }

  // Formatea importes en euros con configuración local española.
  function formatearMoneda(value) {
    return new Intl.NumberFormat("es-ES", {
      style: "currency",
      currency: "EUR",
      maximumFractionDigits: 2,
    }).format(value);
  }

  // Formatea porcentajes con dos decimales.
  function formatearPorcentaje(value) {
    return `${value.toFixed(2)}%`;
  }

  // Convierte un número de meses a una etiqueta legible en años y meses.
  function formatearPlazoEnAnios(meses) {
    const anios = Math.floor(meses / 12);
    const mesesRestantes = meses % 12;
    return `${anios} años y ${mesesRestantes} meses`;
  }

  // Calcula la cuota mensual teórica de una hipoteca amortizable.
  function calcularCuotaMensual(capital, interesAnual, meses) {
    if (meses <= 0) {
      return 0;
    }

    const tasaMensual = interesAnual / 12 / 100;

    if (tasaMensual === 0) {
      return capital / meses;
    }

    const factor = Math.pow(1 + tasaMensual, meses);
    return capital * ((tasaMensual * factor) / (factor - 1));
  }

  // Genera el cuadro de amortización completo con extras opcionales.
  function generarAmortizacion(params) {
    const {
      capital,
      interesAnual,
      meses,
      aportacionExtraMensual,
      pagoUnico,
      mesPagoUnico,
    } = params;

    const tasaMensual = interesAnual / 12 / 100;
    const cuotaProgramada = calcularCuotaMensual(
      capital,
      interesAnual,
      meses,
    );
    const calendario = [];

    let saldo = capital;
    let interesTotal = 0;
    let totalPagado = 0;
    let mes = 1;

    while (saldo > 0.01 && mes <= meses + 360) {
      const interes = tasaMensual > 0 ? saldo * tasaMensual : 0;
      let capitalAmortizadoPorCuota = cuotaProgramada - interes;

      if (capitalAmortizadoPorCuota < 0) {
        capitalAmortizadoPorCuota = 0;
      }

      let aportacionExtra = aportacionExtraMensual;
      if (pagoUnico > 0 && mes === mesPagoUnico) {
        aportacionExtra += pagoUnico;
      }

      let cuota = cuotaProgramada;

      if (capitalAmortizadoPorCuota + aportacionExtra > saldo) {
        const exceso = capitalAmortizadoPorCuota + aportacionExtra - saldo;

        if (aportacionExtra >= exceso) {
          aportacionExtra -= exceso;
        } else {
          cuota -= exceso - aportacionExtra;
          aportacionExtra = 0;
        }
      }

      const reduccionCapital = Math.min(
        saldo,
        Math.max(0, capitalAmortizadoPorCuota + aportacionExtra),
      );
      const capitalAmortizado = Math.max(0, reduccionCapital - aportacionExtra);

      saldo = Math.max(0, saldo - reduccionCapital);

      interesTotal += interes;
      totalPagado += cuota + aportacionExtra;

      calendario.push({
        mes,
        cuota,
        interes,
        capitalAmortizado,
        aportacionExtra,
        balance: saldo,
      });

      mes += 1;

      if (tasaMensual === 0 && cuotaProgramada + aportacionExtraMensual <= 0) {
        break;
      }
    }

    return {
      calendario,
      cuotaMensual: cuotaProgramada,
      interesTotal,
      totalPagado,
      mesesFinales: calendario.length,
    };
  }

  // Recoge los datos del formulario y prepara los valores derivados.
  function obtenerEntradas() {
    const precioVivienda = Math.max(0, obtenerNumero("precioVivienda"));
    const entradaInicial = Math.max(0, obtenerNumero("entradaInicial"));
    const gastosCompra = Math.max(0, obtenerNumero("gastosCompra"));

    const capitalFinanciado = Math.max(0, precioVivienda - entradaInicial);
    const desembolsoInicial = entradaInicial + gastosCompra;

    return {
      precioVivienda,
      entradaInicial,
      gastosCompra,
      capitalFinanciado,
      desembolsoInicial,
      interesAnual: Math.max(0, obtenerNumero("interesAnual")),
      plazoMeses: Math.max(1, Math.round(obtenerNumero("plazoAnos") * 12)),
      tipoHipoteca: formulario.elements.tipoHipoteca.value,
      ingresosMensuales: Math.max(0, obtenerNumero("ingresosMensuales")),
      deudasMensuales: Math.max(0, obtenerNumero("deudasMensuales")),
      aportacionExtraMensual: Math.max(0, obtenerNumero("extraMensual")),
      pagoUnico: Math.max(0, obtenerNumero("pagoUnico")),
      mesPagoUnico: Math.max(1, Math.round(obtenerNumero("mesPagoUnico", 1))),
      variationLow: obtenerNumero("variacionInteresBaja", -0.5),
      variationHigh: obtenerNumero("variacionInteresAlta", 0.75),
      variationTermYears: Math.round(obtenerNumero("variacionPlazo", -5)),
    };
  }

  // Devuelve el texto y el estado visual del ratio de esfuerzo.
  function construirRatioEsfuerzo(cuotaMensual, deudasMensuales, ingresosMensuales) {
    if (ingresosMensuales <= 0) {
      return {
        text: "Sin datos",
        tone: "neutral",
      };
    }

    const ratio = ((cuotaMensual + deudasMensuales) / ingresosMensuales) * 100;

    if (ratio < 30) {
      return {
        text: `${formatearPorcentaje(ratio)} (saludable)`,
        tone: "healthy",
      };
    }

    if (ratio <= 40) {
      return {
        text: `${formatearPorcentaje(ratio)} (moderado)`,
        tone: "moderate",
      };
    }

    return {
      text: `${formatearPorcentaje(ratio)} (riesgo alto)`,
      tone: "high",
    };
  }

  // Aplica la clase visual que corresponde al estado del ratio.
  function aplicarEstadoRatio(tone) {
    const tones = ["mortgage-ratio--healthy", "mortgage-ratio--moderate", "mortgage-ratio--high", "mortgage-ratio--neutral"];

    salidas.ratio.classList.remove(...tones);
    salidas.ratio.classList.add(`mortgage-ratio--${tone}`);
  }

  // Añade una nota contextual cuando la hipoteca no es fija.
  function textoAyudaTipo(type) {
    if (type === "variable") {
      return " Estimación calculada con el interés inicial.";
    }

    if (type === "mixta") {
      return " Estimación aproximada para tramo mixto.";
    }

    return "";
  }

  // Calcula los importes totales de la operación.
  function construirTotalesOperacion(entradas, resultado) {
    return {
      initialOutlay: entradas.desembolsoInicial,
      totalLoanPaid: resultado.totalPagado,
      totalOperationCost: entradas.desembolsoInicial + resultado.totalPagado,
    };
  }

  // Compara el escenario actual con uno sin amortización anticipada.
  function construirMetricasAmortizacionAnticipada(entradas, resultadoActual) {
    const resultadoBase = generarAmortizacion({
      capital: entradas.capitalFinanciado,
      interesAnual: entradas.interesAnual,
      meses: entradas.plazoMeses,
      aportacionExtraMensual: 0,
      pagoUnico: 0,
      mesPagoUnico: 1,
    });

    return {
      resultadoBase,
      savedInterest: Math.max(
        0,
        resultadoBase.interesTotal - resultadoActual.interesTotal,
      ),
      savedMonths: Math.max(
        0,
        resultadoBase.mesesFinales - resultadoActual.mesesFinales,
      ),
    };
  }

  // Actualiza las tarjetas-resumen con los valores calculados.
  function renderizarResumen(entradas, resultado, metricasAmortizacionAnticipada) {
    const estadoRatio = construirRatioEsfuerzo(
      resultado.cuotaMensual,
      entradas.deudasMensuales,
      entradas.ingresosMensuales,
    );
    const totales = construirTotalesOperacion(entradas, resultado);

    salidas.cuota.textContent = formatearMoneda(resultado.cuotaMensual);
    salidas.capital.textContent = `Capital financiado: ${formatearMoneda(entradas.capitalFinanciado)}${textoAyudaTipo(entradas.tipoHipoteca)}`;
    salidas.intereses.textContent = formatearMoneda(resultado.interesTotal);
    salidas.pagado.textContent = formatearMoneda(totales.totalLoanPaid);
    salidas.ratio.textContent = estadoRatio.text;
    aplicarEstadoRatio(estadoRatio.tone);
    salidas.plazoFinal.textContent = formatearPlazoEnAnios(resultado.mesesFinales);
    salidas.aportacionInicial.textContent = formatearMoneda(totales.initialOutlay);
    salidas.costeOperacion.textContent = formatearMoneda(totales.totalOperationCost);
    salidas.ahorroIntereses.textContent = formatearMoneda(metricasAmortizacionAnticipada.savedInterest);
    salidas.ahorroPlazo.textContent =
      metricasAmortizacionAnticipada.savedMonths > 0
        ? formatearPlazoEnAnios(metricasAmortizacionAnticipada.savedMonths)
        : "Sin ahorro";
  }

  // Genera una fila HTML reutilizable para las tablas del módulo.
  function generarFilaHTML(cells) {
    const cols = cells.map((cell) => `<td>${cell}</td>`).join("");
    return `<tr>${cols}</tr>`;
  }

  // Habilita o deshabilita un botón de paginación.
  function aplicarEstadoBotonPaginacion(button, isDisabled) {
    if (!button) {
      return;
    }

    button.disabled = isDisabled;
    button.classList.toggle("transactions-pagination__link--disabled", isDisabled);
  }

  // Pinta una página concreta del cuadro de amortización.
  function renderizarPaginaAmortizacion(pagina) {
    if (!estadoPaginacionAmortizacion.calendario.length) {
      salidas.tablaAmortizacion.innerHTML = "";
      if (salidas.informacionPaginaAmortizacion) {
        salidas.informacionPaginaAmortizacion.textContent = "Sin datos";
      }
      aplicarEstadoBotonPaginacion(salidas.botonAmortAnterior, true);
      aplicarEstadoBotonPaginacion(salidas.botonAmortSiguiente, true);
      return;
    }

    estadoPaginacionAmortizacion.paginaActual = Math.min(
      Math.max(1, pagina),
      estadoPaginacionAmortizacion.totalPaginas,
    );

    const inicio = (estadoPaginacionAmortizacion.paginaActual - 1) * FILAS_AMORTIZACION_POR_PAGINA;
    const fin = inicio + FILAS_AMORTIZACION_POR_PAGINA;
    const filasParaMostrar = estadoPaginacionAmortizacion.calendario.slice(inicio, fin);

    salidas.tablaAmortizacion.innerHTML = filasParaMostrar
      .map((fila) =>
        generarFilaHTML([
          fila.mes,
          formatearMoneda(fila.cuota),
          formatearMoneda(fila.interes),
          formatearMoneda(fila.capitalAmortizado),
          formatearMoneda(fila.aportacionExtra),
          formatearMoneda(fila.balance),
        ]),
      )
      .join("");

    if (salidas.informacionPaginaAmortizacion) {
      salidas.informacionPaginaAmortizacion.textContent = `Año ${estadoPaginacionAmortizacion.paginaActual} de ${estadoPaginacionAmortizacion.totalPaginas}`;
    }

    aplicarEstadoBotonPaginacion(salidas.botonAmortAnterior, estadoPaginacionAmortizacion.paginaActual === 1);
    aplicarEstadoBotonPaginacion(
      salidas.botonAmortSiguiente,
      estadoPaginacionAmortizacion.paginaActual === estadoPaginacionAmortizacion.totalPaginas,
    );
  }

  // Guarda el calendario de amortización y reinicia la paginación.
  function renderizarAmortizacion(calendario) {
    estadoPaginacionAmortizacion.calendario = calendario;
    estadoPaginacionAmortizacion.totalPaginas = Math.max(
      1,
      Math.ceil(calendario.length / FILAS_AMORTIZACION_POR_PAGINA),
    );
    renderizarPaginaAmortizacion(1);
  }

  // Genera la tabla comparativa de escenarios de hipoteca.
  function renderizarComparacionEscenarios(entradas) {
    const aniosBase = Math.max(1, Math.round(entradas.plazoMeses / 12));

    const escenarios = [
      {
        name: "Escenario base",
        rate: entradas.interesAnual,
        years: aniosBase,
      },
      {
        name: "Interés bajo",
        rate: Math.max(0, entradas.interesAnual + entradas.variationLow),
        years: aniosBase,
      },
      {
        name: "Interés alto",
        rate: Math.max(0, entradas.interesAnual + entradas.variationHigh),
        years: aniosBase,
      },
      {
        name: "Cambio de plazo",
        rate: entradas.interesAnual,
        years: Math.max(1, aniosBase + entradas.variationTermYears),
      },
    ];

    const filas = escenarios.map((escenario) => {
      const meses = escenario.years * 12;
      const resultado = generarAmortizacion({
        capital: entradas.capitalFinanciado,
        interesAnual: escenario.rate,
        meses,
        aportacionExtraMensual: 0,
        pagoUnico: 0,
        mesPagoUnico: 1,
      });

      return generarFilaHTML([
        escenario.name,
        formatearPorcentaje(escenario.rate),
        `${escenario.years} años`,
        formatearMoneda(resultado.cuotaMensual),
        formatearMoneda(resultado.interesTotal),
      ]);
    });

    salidas.tablaEscenarios.innerHTML = filas.join("");
  }

  // Agrupa el calendario mensual en una serie anual para el gráfico.
  function construirSerieGrafico(calendario) {
    const datosAgrupados = [];
    let interesAnualTemporal = 0;
    let interesAcumuladoTotal = 0;

    calendario.forEach((fila) => {
      interesAnualTemporal += fila.interes;
      interesAcumuladoTotal += fila.interes;

      if (fila.mes % 12 === 0 || fila.mes === calendario.length) {
        datosAgrupados.push({
          anio: Math.ceil(fila.mes / 12),
          balance: fila.balance,
          interesAnual: interesAnualTemporal,
          interesAcumulado: interesAcumuladoTotal,
        });

        interesAnualTemporal = 0;
      }
    });

    return datosAgrupados;
  }

  // Dibuja el gráfico de evolución del capital e intereses acumulados.
  function dibujarGrafico(calendario) {
    const lienzo = salidas.grafico;
    if (!lienzo || typeof lienzo.getContext !== "function") {
      return;
    }

    const context = lienzo.getContext("2d");
    const serieGrafico = construirSerieGrafico(calendario);

    context.clearRect(0, 0, lienzo.width, lienzo.height);

    if (serieGrafico.length === 0) {
      return;
    }

    const padding = { top: 20, right: 20, bottom: 36, left: 55 };
    const width = lienzo.width - padding.left - padding.right;
    const height = lienzo.height - padding.top - padding.bottom;

    const maxValue = Math.max(
      ...serieGrafico.map((dato) => dato.balance),
      ...serieGrafico.map((dato) => dato.interesAcumulado),
      1,
    );

    context.strokeStyle = "rgba(1, 10, 18, 0.25)";
    context.lineWidth = 1;
    context.beginPath();
    context.moveTo(padding.left, padding.top);
    context.lineTo(padding.left, padding.top + height);
    context.lineTo(padding.left + width, padding.top + height);
    context.stroke();

    const stepX = serieGrafico.length > 1 ? width / (serieGrafico.length - 1) : width;

    context.strokeStyle = "rgba(48, 165, 255, 0.9)";
    context.lineWidth = 2;
    context.beginPath();

    serieGrafico.forEach((dato, index) => {
      const x = padding.left + stepX * index;
      const y = padding.top + (1 - dato.balance / maxValue) * height;
      if (index === 0) {
        context.moveTo(x, y);
      } else {
        context.lineTo(x, y);
      }
    });

    context.stroke();

    context.strokeStyle = "rgba(255, 138, 48, 0.9)";
    context.lineWidth = 2;
    context.beginPath();

    serieGrafico.forEach((dato, index) => {
      const x = padding.left + stepX * index;
      const y = padding.top + (1 - dato.interesAcumulado / maxValue) * height;
      if (index === 0) {
        context.moveTo(x, y);
      } else {
        context.lineTo(x, y);
      }
    });

    context.stroke();

    const maxYearsToLabel = Math.min(6, serieGrafico.length);
    const labelStep = Math.max(1, Math.floor(serieGrafico.length / maxYearsToLabel));

    context.fillStyle = "rgba(1, 10, 18, 0.75)";
    context.font = "12px Roboto, sans-serif";

    for (let i = 0; i < serieGrafico.length; i += labelStep) {
      const x = padding.left + stepX * i;
      context.fillText(`Año ${serieGrafico[i].anio}`, x - 18, padding.top + height + 20);
    }
  }

  // Valida la coherencia de los datos antes de calcular resultados.
  function validarDatos(inputs) {
    if (inputs.precioVivienda <= 0) {
      return "El precio de la vivienda debe ser mayor que cero.";
    }

    if (inputs.entradaInicial < 0) {
      return "La entrada no puede ser negativa.";
    }

    if (inputs.entradaInicial > inputs.precioVivienda) {
      return "La entrada no puede ser mayor que el precio de la vivienda.";
    }

    if (inputs.gastosCompra < 0) {
      return "Los gastos no pueden ser negativos.";
    }

    if (inputs.capitalFinanciado <= 0) {
      return "El capital financiado debe ser mayor que cero.";
    }

    if (inputs.plazoMeses <= 0) {
      return "El plazo debe ser mayor que cero.";
    }

    if (inputs.interesAnual < 0) {
      return "El interés no puede ser negativo.";
    }

    if (inputs.ingresosMensuales <= 0) {
      return "Los ingresos deben ser mayores que cero para calcular el ratio de esfuerzo.";
    }

    if (inputs.pagoUnico > 0 && inputs.mesPagoUnico > inputs.plazoMeses) {
      return "El mes del pago único no puede superar el plazo total de la hipoteca.";
    }

    return "";
  }

  // Limpia y muestra un estado de error en la interfaz.
  function renderizarValidacion(message) {
    salidas.cuota.textContent = message;
    salidas.capital.textContent = "";
    salidas.intereses.textContent = "-";
    salidas.pagado.textContent = "-";
    salidas.ratio.textContent = "-";
    aplicarEstadoRatio("neutral");
    salidas.plazoFinal.textContent = "-";
    salidas.aportacionInicial.textContent = "-";
    salidas.costeOperacion.textContent = "-";
    salidas.ahorroIntereses.textContent = "-";
    salidas.ahorroPlazo.textContent = "-";
    estadoPaginacionAmortizacion.calendario = [];
    estadoPaginacionAmortizacion.paginaActual = 1;
    estadoPaginacionAmortizacion.totalPaginas = 1;
    renderizarPaginaAmortizacion(1);
    salidas.tablaEscenarios.innerHTML = "";

    const ctx = salidas.grafico.getContext("2d");
    ctx.clearRect(0, 0, salidas.grafico.width, salidas.grafico.height);
  }

  // Ejecuta validación, cálculo y pintado de todos los resultados.
  function calcularYRenderizar() {
    const inputs = obtenerEntradas();
    const errorValidacion = validarDatos(inputs);

    if (errorValidacion) {
      renderizarValidacion(errorValidacion);
      return;
    }

    const resultado = generarAmortizacion({
      capital: inputs.capitalFinanciado,
      interesAnual: inputs.interesAnual,
      meses: inputs.plazoMeses,
      aportacionExtraMensual: inputs.aportacionExtraMensual,
      pagoUnico: inputs.pagoUnico,
      mesPagoUnico: inputs.mesPagoUnico,
    });

    const metricasAmortizacionAnticipada = construirMetricasAmortizacionAnticipada(inputs, resultado);

    renderizarResumen(inputs, resultado, metricasAmortizacionAnticipada);
    renderizarAmortizacion(resultado.calendario);
    renderizarComparacionEscenarios(inputs);
    dibujarGrafico(resultado.calendario);
  }

  formulario.addEventListener("submit", function (event) {
    event.preventDefault();
    calcularYRenderizar();
  });

  formulario.addEventListener("input", function () {
    calcularYRenderizar();
  });

  if (salidas.botonAmortAnterior) {
    salidas.botonAmortAnterior.addEventListener("click", function () {
      renderizarPaginaAmortizacion(estadoPaginacionAmortizacion.paginaActual - 1);
    });
  }

  if (salidas.botonAmortSiguiente) {
    salidas.botonAmortSiguiente.addEventListener("click", function () {
      renderizarPaginaAmortizacion(estadoPaginacionAmortizacion.paginaActual + 1);
    });
  }

  botonRestablecer.addEventListener("click", function () {
    formulario.reset();

    formulario.elements.precioVivienda.value = "220000";
    formulario.elements.entradaInicial.value = "40000";
    formulario.elements.gastosCompra.value = "20000";
    formulario.elements.plazoAnos.value = "30";
    formulario.elements.interesAnual.value = "3.2";
    formulario.elements.tipoHipoteca.value = "fija";
    formulario.elements.ingresosMensuales.value = "2600";
    formulario.elements.deudasMensuales.value = "180";
    formulario.elements.extraMensual.value = "0";
    formulario.elements.pagoUnico.value = "0";
    formulario.elements.mesPagoUnico.value = "24";
    formulario.elements.variacionInteresBaja.value = "-0.5";
    formulario.elements.variacionInteresAlta.value = "0.75";
    formulario.elements.variacionPlazo.value = "-5";

    calcularYRenderizar();
  });

  calcularYRenderizar();
})();
