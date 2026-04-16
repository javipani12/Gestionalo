(() => {
  const selectorTipo = document.getElementById('id_tipo');
  const selectorCategoria = document.getElementById('id_categoria');
  const selectorSubcategoria = document.getElementById('id_subcategoria');
  const selectorObjetivo = document.getElementById('id_objetivo');
  const campoObjetivo = document.getElementById('id_objetivo_field');
  const campoCategoria = document.getElementById('id_categoria_field');
  const campoSubcategoria = document.getElementById('id_subcategoria_field');

  if (!selectorTipo || !selectorCategoria || !selectorSubcategoria) {
    return;
  }

  // Conserva las opciones originales para poder reconstruir la lista.
  const opcionesSubcategoria = Array.from(selectorSubcategoria.options).slice(1).map((opcion) => ({
    value: opcion.value,
    text: opcion.text,
    categoriaId: opcion.dataset.categoriaId || '',
  }));

  const opcionPlaceholder = selectorSubcategoria.options[0]?.cloneNode(true) || new Option('Selecciona una subcategoría', '');
  const subcategoriaInicialSeleccionada = selectorSubcategoria.value;
  const categoriaInicialSeleccionada = selectorCategoria.value;

  let ultimaCategoriaSeleccionada = categoriaInicialSeleccionada;
  let ultimaSubcategoriaSeleccionada = subcategoriaInicialSeleccionada;
  let ultimoObjetivoSeleccionado = selectorObjetivo ? selectorObjetivo.value : '';

  function esTipoInternoSeleccionado() {
    const opcionSeleccionada = selectorTipo.options[selectorTipo.selectedIndex];
    return opcionSeleccionada?.dataset?.isInternal === '1';
  }

  // Filtra las subcategorias segun la categoria elegida.
  function pintarSubcategorias() {
    const categoriaIdSeleccionada = selectorCategoria.value;

    selectorSubcategoria.innerHTML = '';
    selectorSubcategoria.appendChild(opcionPlaceholder.cloneNode(true));

    opcionesSubcategoria
      .filter((elemento) => elemento.categoriaId === categoriaIdSeleccionada)
      .forEach((elemento) => {
        const opcion = new Option(elemento.text, elemento.value);
        opcion.dataset.categoriaId = elemento.categoriaId;
        selectorSubcategoria.appendChild(opcion);
      });

    if (subcategoriaInicialSeleccionada && categoriaIdSeleccionada) {
      const existe = opcionesSubcategoria.some((elemento) =>
        elemento.value === subcategoriaInicialSeleccionada && elemento.categoriaId === categoriaIdSeleccionada
      );

      if (existe) {
        selectorSubcategoria.value = subcategoriaInicialSeleccionada;
      }
    }

    if (!selectorSubcategoria.value) {
      selectorSubcategoria.selectedIndex = 0;
    }

    selectorSubcategoria.disabled = !categoriaIdSeleccionada;
  }

  function actualizarVisibilidadCampos() {
    const esInterna = esTipoInternoSeleccionado();

    if (esInterna) {
      ultimaCategoriaSeleccionada = selectorCategoria.value;
      ultimaSubcategoriaSeleccionada = selectorSubcategoria.value;

      if (campoCategoria) {
        campoCategoria.style.display = 'none';
      }

      if (campoSubcategoria) {
        campoSubcategoria.style.display = 'none';
      }

      if (selectorCategoria) {
        selectorCategoria.required = false;
        selectorCategoria.disabled = true;
        selectorCategoria.value = '';
      }

      if (selectorSubcategoria) {
        selectorSubcategoria.required = false;
        selectorSubcategoria.disabled = true;
        selectorSubcategoria.value = '';
      }

      if (campoObjetivo && selectorObjetivo) {
        campoObjetivo.style.display = '';
        selectorObjetivo.disabled = false;
        selectorObjetivo.required = true;

        if (!selectorObjetivo.value && ultimoObjetivoSeleccionado) {
          selectorObjetivo.value = ultimoObjetivoSeleccionado;
        }
      }

      return;
    }

    if (campoCategoria) {
      campoCategoria.style.display = '';
    }

    if (campoSubcategoria) {
      campoSubcategoria.style.display = '';
    }

    selectorCategoria.disabled = false;
    selectorCategoria.required = true;

    if (ultimaCategoriaSeleccionada) {
      selectorCategoria.value = ultimaCategoriaSeleccionada;
    }

    pintarSubcategorias();

    selectorSubcategoria.required = true;
    if (ultimaSubcategoriaSeleccionada) {
      selectorSubcategoria.value = ultimaSubcategoriaSeleccionada;
    }

    if (campoObjetivo && selectorObjetivo) {
      ultimoObjetivoSeleccionado = selectorObjetivo.value;
      campoObjetivo.style.display = 'none';
      selectorObjetivo.required = false;
      selectorObjetivo.disabled = true;
      selectorObjetivo.value = '';
    }
  }

  selectorCategoria.addEventListener('change', () => {
    ultimaCategoriaSeleccionada = selectorCategoria.value;
    pintarSubcategorias();
    ultimaSubcategoriaSeleccionada = selectorSubcategoria.value;
  });

  selectorSubcategoria.addEventListener('change', () => {
    ultimaSubcategoriaSeleccionada = selectorSubcategoria.value;
  });

  selectorTipo.addEventListener('change', () => {
    actualizarVisibilidadCampos();
  });

  if (selectorObjetivo) {
    selectorObjetivo.addEventListener('change', () => {
      ultimoObjetivoSeleccionado = selectorObjetivo.value;
    });
  }

  actualizarVisibilidadCampos();
})();
