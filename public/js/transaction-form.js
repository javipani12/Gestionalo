(() => {
  const selectorCategoria = document.getElementById('id_categoria');
  const selectorSubcategoria = document.getElementById('id_subcategoria');

  if (!selectorCategoria || !selectorSubcategoria) {
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

  selectorCategoria.addEventListener('change', () => {
    pintarSubcategorias();
  });

  pintarSubcategorias();
})();
