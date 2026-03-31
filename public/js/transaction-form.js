(() => {
  const categoriaSelect = document.getElementById('id_categoria');
  const subcategoriaSelect = document.getElementById('id_subcategoria');

  if (!categoriaSelect || !subcategoriaSelect) {
    return;
  }

  const subcategoriaOptions = Array.from(subcategoriaSelect.options).slice(1).map((option) => ({
    value: option.value,
    text: option.text,
    categoriaId: option.dataset.categoriaId || '',
  }));

  const optionPlaceholder = subcategoriaSelect.options[0]?.cloneNode(true) || new Option('Selecciona una subcategoría', '');
  const selectedSubcategoriaInicial = subcategoriaSelect.value;

  function repintarSubcategorias() {
    const categoriaIdSeleccionada = categoriaSelect.value;

    subcategoriaSelect.innerHTML = '';
    subcategoriaSelect.appendChild(optionPlaceholder.cloneNode(true));

    subcategoriaOptions
      .filter((item) => item.categoriaId === categoriaIdSeleccionada)
      .forEach((item) => {
        const option = new Option(item.text, item.value);
        option.dataset.categoriaId = item.categoriaId;
        subcategoriaSelect.appendChild(option);
      });

    if (selectedSubcategoriaInicial && categoriaIdSeleccionada) {
      const existe = subcategoriaOptions.some((item) =>
        item.value === selectedSubcategoriaInicial && item.categoriaId === categoriaIdSeleccionada
      );

      if (existe) {
        subcategoriaSelect.value = selectedSubcategoriaInicial;
      }
    }

    if (!subcategoriaSelect.value) {
      subcategoriaSelect.selectedIndex = 0;
    }

    subcategoriaSelect.disabled = !categoriaIdSeleccionada;
  }

  categoriaSelect.addEventListener('change', () => {
    repintarSubcategorias();
  });

  repintarSubcategorias();
})();
