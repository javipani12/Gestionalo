(() => {
  const dataNode = document.getElementById("graphics-data");

  if (!dataNode) {
    return;
  }

  if (typeof ApexCharts === "undefined") {
    return;
  }

  let payload = {
    transacciones: [],
  };

  // Cargar datos mediante AJAX
  fetch("index.php?controller=chart&action=obtenerDatosGraficosAjax", {
    method: "GET",
    headers: {
      Accept: "application/json",
    },
    credentials: "same-origin",
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      payload = data;
      inicializarGraficos();
    })
    .catch((error) => {
      console.error("Error al cargar datos de gráficos:", error);
      // Inicializar con datos vacíos si hay error
      payload = { transacciones: [] };
      inicializarGraficos();
    });

  function inicializarGraficos() {
    const state = {
      transacciones: Array.isArray(payload.transacciones)
        ? payload.transacciones
        : [],
    };

    const filtros = {
      fechaDesde: document.getElementById("graphics-fecha-desde"),
      fechaHasta: document.getElementById("graphics-fecha-hasta"),
      tipo: document.getElementById("graphics-tipo"),
      categoria: document.getElementById("graphics-categoria"),
      subcategoria: document.getElementById("graphics-subcategoria"),
      metodo: document.getElementById("graphics-metodo"),
      limpiar: document.getElementById("graphics-limpiar"),
    };

    const botonGuardarInforme = document.getElementById("graphics-save-report");
    let estadoReporteActual = null;

    const opcionesSubcategoria = filtros.subcategoria
      ? Array.from(filtros.subcategoria.options)
          .slice(1)
          .map((opcion) => ({
            value: opcion.value,
            text: opcion.text,
            categoriaId: opcion.dataset.categoriaId || "",
          }))
      : [];

    const opcionSubcategoriaPlaceholder =
      filtros.subcategoria?.options[0]?.cloneNode(true) ||
      new Option("Subcategoria", "");

    const kpis = {
      ingresos: document.getElementById("graphics-kpi-ingresos"),
      gastos: document.getElementById("graphics-kpi-gastos"),
      balance: document.getElementById("graphics-kpi-balance"),
    };

    const chartBalanceContainer = document.getElementById(
      "graphics-balance-donut",
    );
    const chartEvolutionContainer =
      document.getElementById("graphics-evolution");
    const chartGoalsEvolutionContainer = document.getElementById(
      "graphics-goals-evolution",
    );
    const chartCategoriesContainer = document.getElementById(
      "graphics-categories",
    );
    const chartIncomeCategoriesContainer = document.getElementById(
      "graphics-income-categories",
    );

    if (
      !chartBalanceContainer ||
      !chartEvolutionContainer ||
      !chartGoalsEvolutionContainer ||
      !chartCategoriesContainer ||
      !chartIncomeCategoriesContainer
    ) {
      return;
    }

    const cardIncomeCategories = document.getElementById(
      "graphics-income-card",
    );
    const cardExpensesCategories = document.getElementById(
      "graphics-expenses-card",
    );
    const cardGoalsEvolution = document.getElementById("graphics-goals-card");

    const chartBalance = new ApexCharts(chartBalanceContainer, {
      chart: {
        type: "donut",
        height: 320,
      },
      labels: ["Ingresos", "Gastos"],
      series: [0, 0],
      colors: ["#22A06B", "#D64545"],
      legend: {
        position: "bottom",
      },
      dataLabels: {
        formatter(val) {
          return `${val.toFixed(1)}%`;
        },
      },
    });

    const chartEvolution = new ApexCharts(chartEvolutionContainer, {
      chart: {
        type: "bar",
        height: 320,
        stacked: false,
        toolbar: { show: false },
      },
      colors: ["#22A06B", "#D64545"],
      series: [
        { name: "Ingresos", data: [] },
        { name: "Gastos", data: [] },
      ],
      dataLabels: {
        enabled: true,
        formatter(value) {
          return formatearMonedaConEuro(value);
        },
        style: {
          fontSize: "12px",
          fontWeight: 600,
        },
      },
      xaxis: {
        categories: [],
        labels: {
          rotate: -35,
        },
      },
      yaxis: {
        min: 0,
        stepSize: 300,
        labels: {
          formatter(value) {
            return formatearMonedaConEuro(value);
          },
        },
      },
      tooltip: {
        y: {
          formatter(value) {
            return formatearMonedaConEuro(value);
          },
        },
      },
    });

    const chartGoalsEvolution = new ApexCharts(chartGoalsEvolutionContainer, {
      chart: {
        type: "bar",
        height: 320,
        stacked: true,
        toolbar: { show: false },
      },
      colors: ["#22A06B", "#D64545"],
      series: [
        { name: "Aporte", data: [] },
        { name: "Retiro", data: [] },
      ],
      dataLabels: {
        enabled: true,
        formatter(value) {
          return formatearMonedaConEuro(value);
        },
        style: {
          fontSize: "12px",
          fontWeight: 600,
        },
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: "55%",
        },
      },
      xaxis: {
        categories: [],
        labels: {
          rotate: -35,
        },
      },
      yaxis: {
        min: 0,
        stepSize: 300,
        labels: {
          formatter(value) {
            return formatearMonedaConEuro(value);
          },
        },
      },
      tooltip: {
        y: {
          formatter(value) {
            return formatearMonedaConEuro(value);
          },
        },
      },
      legend: {
        position: "bottom",
      },
    });

    const chartCategories = new ApexCharts(chartCategoriesContainer, {
      chart: {
        type: "bar",
        height: 360,
        toolbar: { show: false },
      },
      plotOptions: {
        bar: {
          horizontal: true,
          borderRadius: 4,
        },
      },
      series: [{ name: "Gasto", data: [] }],
      dataLabels: {
        enabled: true,
        formatter(value) {
          return formatearMonedaConEuro(value);
        },
        style: {
          fontSize: "12px",
          fontWeight: 600,
        },
      },
      xaxis: {
        categories: [],
        min: 0,
        stepSize: 50,
        forceNiceScale: false,
        labels: {
          formatter(value) {
            return formatearMonedaConEuro(value);
          },
          hideOverlappingLabels: true,
          trim: true,
        },
      },
      colors: ["#D64545"],
      tooltip: {
        y: {
          formatter(value) {
            return formatearMoneda(value);
          },
        },
      },
    });

    const chartIncomeCategories = new ApexCharts(
      chartIncomeCategoriesContainer,
      {
        chart: {
          type: "bar",
          height: 360,
          toolbar: { show: false },
        },
        plotOptions: {
          bar: {
            horizontal: true,
            borderRadius: 4,
          },
        },
        series: [{ name: "Ingreso", data: [] }],
        dataLabels: {
          enabled: true,
          formatter(value) {
            return formatearMonedaConEuro(value);
          },
          style: {
            fontSize: "12px",
            fontWeight: 600,
          },
        },
        xaxis: {
          categories: [],
          min: 0,
          stepSize: 500,
          labels: {
            formatter(value) {
              return formatearMonedaConEuro(value);
            },
            hideOverlappingLabels: true,
            trim: true,
          },
        },
        colors: ["#22A06B"],
        tooltip: {
          y: {
            formatter(value) {
              return formatearMoneda(value);
            },
          },
        },
      },
    );

    chartBalance.render();
    chartEvolution.render();
    chartGoalsEvolution.render();
    chartIncomeCategories.render();
    chartCategories.render();

    if (botonGuardarInforme) {
      botonGuardarInforme.addEventListener("click", async () => {
        if (!estadoReporteActual) {
          actualizarPanel();
        }

        if (!estadoReporteActual) {
          return;
        }

        botonGuardarInforme.disabled = true;
        botonGuardarInforme.textContent = "Guardando...";

        try {
          const imagenes = await capturarImagenesGraficos({
            chartBalance,
            chartEvolution,
            chartGoalsEvolution,
            chartIncomeCategories,
            chartCategories,
          });

          const payload = {
            nombreInforme: `Graficos ${new Date().toLocaleString("es-ES")}`,
            datos: {
              filtros: estadoReporteActual.filtros,
              resumen: estadoReporteActual.resumen,
              imagenes,
            },
          };

          const response = await fetch(
            "index.php?controller=report&action=generarInformeGraficosAjax",
            {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
              },
              credentials: "same-origin",
              body: JSON.stringify(payload),
            },
          );

          const contentType = response.headers.get("content-type") || "";
          let json = null;
          let textoRespuesta = "";

          if (contentType.includes("application/json")) {
            json = await response.json();
          } else {
            textoRespuesta = await response.text();
          }

          if (!response.ok) {
            const mensajeServidor =
              (json && json.mensaje) ||
              extraerMensajePlano(textoRespuesta) ||
              `Error HTTP ${response.status}`;
            throw new Error(mensajeServidor);
          }

          if (!json || !json.ok) {
            throw new Error(
              (json && json.mensaje) ||
                extraerMensajePlano(textoRespuesta) ||
                "No se pudo guardar el informe.",
            );
          }

          window.location.reload();
          return;
        } catch (error) {
          console.error("Error al guardar informe de gráficos:", error);
          window.location.reload();
          return;
        } finally {
          botonGuardarInforme.disabled = false;
          botonGuardarInforme.textContent = "Guardar informe PDF";
        }
      });
    }

    if (filtros.categoria) {
      filtros.categoria.addEventListener("change", () => {
        pintarSubcategorias();
        actualizarPanel();
      });
    }

    Object.values(filtros)
      .filter(
        (elemento) =>
          elemento &&
          elemento !== filtros.limpiar &&
          elemento !== filtros.categoria,
      )
      .forEach((elemento) => {
        elemento.addEventListener("change", actualizarPanel);
      });

    if (filtros.limpiar) {
      filtros.limpiar.addEventListener("click", () => {
        if (filtros.fechaDesde) filtros.fechaDesde.value = "";
        if (filtros.fechaHasta) filtros.fechaHasta.value = "";
        if (filtros.tipo) filtros.tipo.value = "";
        if (filtros.categoria) filtros.categoria.value = "";
        if (filtros.subcategoria) filtros.subcategoria.value = "";
        if (filtros.metodo) filtros.metodo.value = "";

        pintarSubcategorias();

        actualizarPanel();
      });
    }

    pintarSubcategorias();
    actualizarPanel();

    function pintarSubcategorias() {
      if (!filtros.subcategoria) {
        return;
      }

      const categoriaSeleccionada = filtros.categoria
        ? filtros.categoria.value
        : "";
      const valorActual = filtros.subcategoria.value;

      filtros.subcategoria.innerHTML = "";
      filtros.subcategoria.appendChild(
        opcionSubcategoriaPlaceholder.cloneNode(true),
      );

      if (!categoriaSeleccionada) {
        filtros.subcategoria.value = "";
        filtros.subcategoria.disabled = true;
        return;
      }

      opcionesSubcategoria
        .filter((opcion) => opcion.categoriaId === categoriaSeleccionada)
        .forEach((opcion) => {
          const optionElement = new Option(opcion.text, opcion.value);
          optionElement.dataset.categoriaId = opcion.categoriaId;
          filtros.subcategoria.appendChild(optionElement);
        });

      const sigueDisponible = opcionesSubcategoria.some(
        (opcion) =>
          opcion.value === valorActual &&
          opcion.categoriaId === categoriaSeleccionada,
      );

      filtros.subcategoria.value = sigueDisponible ? valorActual : "";
      filtros.subcategoria.disabled = false;
    }

    function actualizarPanel() {
      const transaccionesFiltradas = filtrarTransacciones(state.transacciones);

      const totales = calcularTotales(transaccionesFiltradas);
      pintarKpis(totales);

      estadoReporteActual = {
        filtros: {
          tipo: obtenerTextoSeleccionado(filtros.tipo),
          categoria: obtenerTextoSeleccionado(filtros.categoria),
          subcategoria: obtenerTextoSeleccionado(filtros.subcategoria),
          fechaDesde: filtros.fechaDesde ? filtros.fechaDesde.value || "-" : "-",
          fechaHasta: filtros.fechaHasta ? filtros.fechaHasta.value || "-" : "-",
          metodo: obtenerTextoSeleccionado(filtros.metodo),
        },
        resumen: {
          ingresos: kpis.ingresos ? kpis.ingresos.textContent.trim() : formatearMoneda(totales.ingresos),
          gastos: kpis.gastos ? kpis.gastos.textContent.trim() : formatearMoneda(Math.abs(totales.gastos)),
          balance: kpis.balance ? kpis.balance.textContent.trim() : formatearMoneda(totales.ingresos - Math.abs(totales.gastos)),
        },
      };

      chartBalance.updateSeries([totales.ingresos, Math.abs(totales.gastos)]);

      const evolucion = agruparPorMes(transaccionesFiltradas);

      // Calcular máximo y establecer eje Y con intervalos de 300€
      const maxEvolucion = Math.max(
        ...evolucion.ingresos,
        ...evolucion.gastos.map((v) => Math.abs(v)),
        300,
      );
      const maxRedondeado = Math.ceil(maxEvolucion / 300) * 300;

      chartEvolution.updateOptions({
        xaxis: { categories: evolucion.etiquetas },
        yaxis: {
          min: 0,
          max: maxRedondeado,
          stepSize: 300,
          labels: {
            formatter(value) {
              return formatearMonedaConEuro(value);
            },
          },
        },
      });
      chartEvolution.updateSeries([
        { name: "Ingresos", data: evolucion.ingresos },
        {
          name: "Gastos",
          data: evolucion.gastos.map((valor) => Math.abs(valor)),
        },
      ]);

      const evolucionObjetivos = agruparEvolucionObjetivos(
        transaccionesFiltradas,
      );
      const maxObjetivos = Math.max(
        ...evolucionObjetivos.aportes,
        ...evolucionObjetivos.retiros,
        300,
      );
      const maxRedondeadoObjetivos = Math.ceil(maxObjetivos / 300) * 300;

      chartGoalsEvolution.updateOptions({
        xaxis: { categories: evolucionObjetivos.etiquetas },
        yaxis: {
          min: 0,
          max: maxRedondeadoObjetivos,
          stepSize: 300,
          labels: {
            formatter(value) {
              return formatearMonedaConEuro(value);
            },
          },
        },
      });
      chartGoalsEvolution.updateSeries([
        { name: "Aporte", data: evolucionObjetivos.aportes },
        { name: "Retiro", data: evolucionObjetivos.retiros },
      ]);

      const categoriaSeleccionada = filtros.categoria
        ? filtros.categoria.value
        : "";

      const topIngresos = agruparIngresoPorCategoria(
        transaccionesFiltradas,
        8,
        categoriaSeleccionada,
      );
      const hayDatosIngresos = topIngresos.importes.length > 0;
      const maxIngresos = Math.max(...topIngresos.importes, 500);
      const maxRedondeadoIngresos = Math.ceil(maxIngresos / 500) * 500;
      const esPantallaMovilVertical = esMovilVertical();
      chartIncomeCategories.updateOptions({
        xaxis: {
          categories: topIngresos.categorias,
          min: 0,
          max: hayDatosIngresos ? maxRedondeadoIngresos : undefined,
          stepSize: 500,
          forceNiceScale: false,
          tickAmount: esPantallaMovilVertical ? 4 : undefined,
          labels: {
            show: hayDatosIngresos,
            formatter(value) {
              return esPantallaMovilVertical
                ? formatearMonedaAbreviadaConEuro(value)
                : formatearMonedaConEuro(value);
            },
            hideOverlappingLabels: true,
            trim: true,
          },
        },
        yaxis: {
          labels: {
            show: hayDatosIngresos,
          },
        },
      });
      chartIncomeCategories.updateSeries([
        { name: "Ingreso", data: topIngresos.importes },
      ]);

      const topCategorias = agruparGastoPorCategoria(
        transaccionesFiltradas,
        8,
        categoriaSeleccionada,
      );
      const hayDatosGastos = topCategorias.importes.length > 0;
      const maxGastos = Math.max(...topCategorias.importes, 50);
      const maxRedondeadoGastos = Math.ceil(maxGastos / 50) * 50;

      chartCategories.updateOptions({
        xaxis: {
          categories: topCategorias.categorias,
          min: 0,
          max: hayDatosGastos ? maxRedondeadoGastos : undefined,
          stepSize: 50,
          forceNiceScale: false,
          tickAmount: esPantallaMovilVertical ? 4 : undefined,
          labels: {
            show: hayDatosGastos,
            formatter(value) {
              return esPantallaMovilVertical
                ? formatearMonedaAbreviadaConEuro(value)
                : formatearMonedaConEuro(value);
            },
            hideOverlappingLabels: true,
            trim: true,
          },
        },
        yaxis: {
          labels: {
            show: hayDatosGastos,
          },
        },
      });
      chartCategories.updateSeries([
        { name: "Gasto", data: topCategorias.importes },
      ]);

      // Deshabilitar cards si no hay datos
      if (cardIncomeCategories) {
        cardIncomeCategories.classList.toggle(
          "is-disabled",
          topIngresos.importes.length === 0,
        );
      }

      if (cardExpensesCategories) {
        cardExpensesCategories.classList.toggle(
          "is-disabled",
          topCategorias.importes.length === 0,
        );
      }

      if (cardGoalsEvolution) {
        cardGoalsEvolution.classList.toggle(
          "is-disabled",
          evolucionObjetivos.aportes.length === 0 &&
            evolucionObjetivos.retiros.length === 0,
        );
      }
    }

    function filtrarTransacciones(transacciones) {
      const fechaDesde = filtros.fechaDesde ? filtros.fechaDesde.value : "";
      const fechaHasta = filtros.fechaHasta ? filtros.fechaHasta.value : "";
      const idTipo = filtros.tipo ? Number.parseInt(filtros.tipo.value, 10) : 0;
      const idCategoria = filtros.categoria
        ? Number.parseInt(filtros.categoria.value, 10)
        : 0;
      const idSubcategoria = filtros.subcategoria
        ? Number.parseInt(filtros.subcategoria.value, 10)
        : 0;
      const idMetodo = filtros.metodo
        ? Number.parseInt(filtros.metodo.value, 10)
        : 0;

      return transacciones.filter((tx) => {
        const fechaTx = (tx.fecha_movimiento || "").slice(0, 10);

        if (fechaDesde && fechaTx < fechaDesde) {
          return false;
        }

        if (fechaHasta && fechaTx > fechaHasta) {
          return false;
        }

        if (idTipo > 0 && Number.parseInt(tx.id_tipo, 10) !== idTipo) {
          return false;
        }

        if (
          idCategoria > 0 &&
          Number.parseInt(tx.id_categoria, 10) !== idCategoria
        ) {
          return false;
        }

        if (
          idSubcategoria > 0 &&
          Number.parseInt(tx.id_subcategoria, 10) !== idSubcategoria
        ) {
          return false;
        }

        if (idMetodo > 0 && Number.parseInt(tx.id_metodo, 10) !== idMetodo) {
          return false;
        }

        return true;
      });
    }

    function calcularTotales(transacciones) {
      return transacciones.reduce(
        (acumulado, tx) => {
          const tipo = normalizarTipo(tx.tipo_movimiento);
          const importe = Number.parseFloat(tx.importe) || 0;

          if (tipo === "ingreso") {
            acumulado.ingresos += importe;
          }

          if (tipo === "gasto") {
            acumulado.gastos += importe;
          }

          return acumulado;
        },
        {
          ingresos: 0,
          gastos: 0,
          balance: 0,
        },
      );
    }

    function pintarKpis(totalesBase) {
      const ingresos = totalesBase.ingresos;
      const gastos = Math.abs(totalesBase.gastos);
      const balance = ingresos - gastos;

      if (kpis.ingresos) {
        kpis.ingresos.textContent = formatearMoneda(ingresos);
      }

      if (kpis.gastos) {
        kpis.gastos.textContent = formatearMoneda(gastos);
      }

      if (kpis.balance) {
        kpis.balance.textContent = formatearMoneda(balance);

        // Remover todas las clases de estado
        kpis.balance.classList.remove(
          "is-negative",
          "is-warning",
          "is-success",
        );

        // Aplicar clase según el balance
        if (balance < 0) {
          kpis.balance.classList.add("is-negative");
        } else if (balance <= ingresos / 2) {
          kpis.balance.classList.add("is-warning");
        } else {
          kpis.balance.classList.add("is-success");
        }
      }
    }

    function agruparPorMes(transacciones) {
      const mapa = new Map();

      transacciones.forEach((tx) => {
        const fecha = new Date(`${tx.fecha_movimiento}T00:00:00`);
        if (Number.isNaN(fecha.getTime())) {
          return;
        }

        const llave = `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, "0")}`;
        const actual = mapa.get(llave) || { ingresos: 0, gastos: 0 };
        const importe = Number.parseFloat(tx.importe) || 0;
        const tipo = normalizarTipo(tx.tipo_movimiento);

        if (tipo === "ingreso") {
          actual.ingresos += importe;
        }

        if (tipo === "gasto") {
          actual.gastos += importe;
        }

        mapa.set(llave, actual);
      });

      const llavesOrdenadas = Array.from(mapa.keys()).sort((a, b) =>
        a.localeCompare(b),
      );

      return {
        etiquetas: llavesOrdenadas,
        ingresos: llavesOrdenadas.map(
          (llave) => (mapa.get(llave) || {}).ingresos || 0,
        ),
        gastos: llavesOrdenadas.map(
          (llave) => (mapa.get(llave) || {}).gastos || 0,
        ),
      };
    }

    function agruparEvolucionObjetivos(transacciones) {
      const mapa = new Map();

      transacciones.forEach((tx) => {
        const nombreTipo = String(tx.tipo_movimiento || "").toLowerCase();
        if (!nombreTipo.includes("transferencia interna")) {
          return;
        }

        const fecha = new Date(`${tx.fecha_movimiento}T00:00:00`);
        if (Number.isNaN(fecha.getTime())) {
          return;
        }

        const llave = `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, "0")}`;
        const actual = mapa.get(llave) || { aporte: 0, retiro: 0 };
        const importe = Number.parseFloat(tx.importe) || 0;

        if (nombreTipo.includes("aporte")) {
          actual.aporte += importe;
        }

        if (nombreTipo.includes("retiro")) {
          actual.retiro += importe;
        }

        mapa.set(llave, actual);
      });

      const llavesOrdenadas = Array.from(mapa.keys()).sort((a, b) =>
        a.localeCompare(b),
      );

      return {
        etiquetas: llavesOrdenadas,
        aportes: llavesOrdenadas.map(
          (llave) => (mapa.get(llave) || {}).aporte || 0,
        ),
        retiros: llavesOrdenadas.map(
          (llave) => (mapa.get(llave) || {}).retiro || 0,
        ),
      };
    }

    function agruparGastoPorCategoria(
      transacciones,
      limite,
      categoriaSeleccionada,
    ) {
      const mapa = new Map();

      transacciones.forEach((tx) => {
        const tipo = normalizarTipo(tx.tipo_movimiento);

        if (tipo !== "gasto") {
          return;
        }

        // Si hay categoría seleccionada, agrupar por subcategoría; si no, por categoría
        let llave;
        if (categoriaSeleccionada) {
          // Agrupar por subcategoría solo si coincide la categoría
          if (String(tx.id_categoria) === categoriaSeleccionada) {
            llave =
              (tx.nombre_subcategoria || "Sin subcategoría").trim() ||
              "Sin subcategoría";
          } else {
            return;
          }
        } else {
          llave =
            (tx.nombre_categoria || "Sin categoría").trim() || "Sin categoría";
        }

        const importe = Number.parseFloat(tx.importe) || 0;
        const acumulado = mapa.get(llave) || 0;
        mapa.set(llave, acumulado + importe);
      });

      const ordenado = Array.from(mapa.entries())
        .sort((a, b) => b[1] - a[1])
        .slice(0, limite);

      return {
        categorias: ordenado.map(([categoria]) => categoria),
        importes: ordenado.map(([, importe]) => importe),
      };
    }

    function agruparIngresoPorCategoria(
      transacciones,
      limite,
      categoriaSeleccionada,
    ) {
      const mapa = new Map();

      transacciones.forEach((tx) => {
        const tipo = normalizarTipo(tx.tipo_movimiento);

        if (tipo !== "ingreso") {
          return;
        }

        // Si hay categoría seleccionada, agrupar por subcategoría; si no, por categoría
        let llave;
        if (categoriaSeleccionada) {
          // Agrupar por subcategoría solo si coincide la categoría
          if (String(tx.id_categoria) === categoriaSeleccionada) {
            llave =
              (tx.nombre_subcategoria || "Sin subcategoría").trim() ||
              "Sin subcategoría";
          } else {
            return;
          }
        } else {
          llave =
            (tx.nombre_categoria || "Sin categoría").trim() || "Sin categoría";
        }

        const importe = Number.parseFloat(tx.importe) || 0;
        const acumulado = mapa.get(llave) || 0;
        mapa.set(llave, acumulado + importe);
      });

      const ordenado = Array.from(mapa.entries())
        .sort((a, b) => b[1] - a[1])
        .slice(0, limite);

      return {
        categorias: ordenado.map(([categoria]) => categoria),
        importes: ordenado.map(([, importe]) => importe),
      };
    }

    function normalizarTipo(tipoMovimiento) {
      const valor = String(tipoMovimiento || "").toLowerCase();

      if (valor.includes("ingreso")) {
        return "ingreso";
      }

      if (valor.includes("gasto")) {
        return "gasto";
      }

      return "otro";
    }

    async function capturarImagenesGraficos(charts) {
      const resultado = {};

      resultado.balance = await exportarDataUri(charts.chartBalance);
      resultado.evolucion = await exportarDataUri(charts.chartEvolution);
      resultado.objetivos = await exportarDataUri(charts.chartGoalsEvolution);
      resultado.ingresos = await exportarDataUri(charts.chartIncomeCategories);
      resultado.gastos = await exportarDataUri(charts.chartCategories);

      return resultado;
    }

    async function exportarDataUri(chartInstance) {
      try {
        if (!chartInstance || typeof chartInstance.dataURI !== "function") {
          return "";
        }
        const exported = await chartInstance.dataURI();
        return (exported && exported.imgURI) || "";
      } catch (error) {
        console.error("No se pudo exportar un gráfico a imagen:", error);
        return "";
      }
    }

    function obtenerTextoSeleccionado(select) {
      if (!select || !select.options || select.selectedIndex < 0) {
        return "Todos";
      }

      const texto = select.options[select.selectedIndex].text || "";
      return texto.trim() === "" ? "Todos" : texto.trim();
    }

    function extraerMensajePlano(texto) {
      const limpio = String(texto || "")
        .replace(/<[^>]*>/g, " ")
        .replace(/\s+/g, " ")
        .trim();
      return limpio.slice(0, 180);
    }
  } // FIN de inicializarGraficos()

  function formatearMoneda(valor) {
    return new Intl.NumberFormat("es-ES", {
      style: "currency",
      currency: "EUR",
      maximumFractionDigits: 2,
    }).format(Number(valor) || 0);
  }

  function formatearMonedaConEuro(valor) {
    const numero = Number(valor) || 0;
    const absValor = Math.abs(numero);
    const formateado = new Intl.NumberFormat("es-ES", {
      maximumFractionDigits: 2,
      minimumFractionDigits: 0,
    }).format(absValor);
    return formateado + " €";
  }

  function formatearMonedaAbreviadaConEuro(valor) {
    const numero = Number(valor) || 0;
    const absValor = Math.abs(numero);

    if (absValor >= 1000) {
      const abreviado = new Intl.NumberFormat("es-ES", {
        maximumFractionDigits: 1,
        minimumFractionDigits: 0,
      }).format(absValor / 1000);
      return `${abreviado}k €`;
    }

    return formatearMonedaConEuro(absValor);
  }

  function esMovilVertical() {
    return window.matchMedia("(max-width: 767px) and (orientation: portrait)")
      .matches;
  }
})();
