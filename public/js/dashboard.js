/**
 * Script para actualizar el balance del dashboard automáticamente cuando cambia el mes
 */

// Verificar si el balance ha cambiado de mes y recargar si es necesario
function verificarCambioMes() {
    // Guardar el mes y año actual en localStorage
    const hoy = new Date();
    const mesActual = hoy.getMonth() + 1; // getMonth() devuelve 0-11
    const anoActual = hoy.getFullYear();
    
    const claveStorage = 'dashboard_mes_ano';
    const mesAnoActual = `${mesActual}-${anoActual}`;
    const mesAnoAlmacenado = localStorage.getItem(claveStorage);
    
    // Si el mes ha cambiado, recargar la página
    if (mesAnoAlmacenado && mesAnoAlmacenado !== mesAnoActual) {
        console.log(`Cambio de mes detectado. Recargando dashboard...`);
        location.reload();
    }
    
    // Guardar el mes actual
    localStorage.setItem(claveStorage, mesAnoActual);
}

// Ejecutar verificación al cargar la página
document.addEventListener('DOMContentLoaded', verificarCambioMes);

// Verificar cada minuto si cambió el mes
setInterval(verificarCambioMes, 60000);

/**
 * Renderiza el gráfico de tarta (pie) del balance mensual
 */
function renderizarBalanceChart() {
    const chartContainer = document.getElementById('dashboard-balance-chart');
    if (!chartContainer || typeof ApexCharts === 'undefined') return;
    
    const dataJson = chartContainer.getAttribute('data-balance');
    if (!dataJson) return;
    
    try {
        const balanceData = JSON.parse(dataJson);
        const ingresos = parseFloat(balanceData.ingresos) || 0;
        const gastos = parseFloat(balanceData.gastos) || 0;
        
        // Calcular el porcentaje de ingresos/gastos
        const total = ingresos + gastos;
        if (total === 0) {
            chartContainer.innerHTML = '<p style="text-align: center; color: rgba(1, 10, 18, 0.5); padding: 40px 0;">Sin movimientos este mes</p>';
            return;
        }
        
        const options = {
            series: [ingresos, gastos],
            chart: {
                type: 'pie',
                height: 280,
                toolbar: {
                    show: false,
                },
            },
            labels: ['Ingresos', 'Gastos'],
            colors: ['#22A06B', '#D64545'],
            dataLabels: {
                enabled: true,
                formatter(val) {
                    return `${parseFloat(val).toFixed(1)}%`;
                },
                style: {
                    fontSize: '13px',
                    fontWeight: 600,
                    colors: ['#fff'],
                },
            },
            tooltip: {
                y: {
                    formatter(val) {
                        return `${val.toLocaleString('es-ES', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        })} €`;
                    },
                },
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['#F6FBFF'],
            },
            legend: {
                position: 'bottom',
            },
        };
        
        const chart = new ApexCharts(chartContainer, options);
        chart.render();
        
    } catch (e) {
        console.error('Error al renderizar balance chart:', e);
        chartContainer.innerHTML = '<p style="text-align: center; color: red; padding: 40px 0;">Error al cargar el gráfico</p>';
    }
}

// Renderizar el gráfico al cargar la página
document.addEventListener('DOMContentLoaded', renderizarBalanceChart);

/**
 * Actualiza el color de la etiqueta de balance según su valor
 */
function actualizarColorBalance() {
    const balanceValue = document.querySelector('.dashboard-balance-value');
    if (!balanceValue) return;
    
    const textoValor = balanceValue.textContent.trim();
    const valor = parseFloat(textoValor.replace(/[^\d,-]/g, '').replace(',', '.'));
    
    // Obtener el valor de ingresos del atributo data
    const chartContainer = document.getElementById('dashboard-balance-chart');
    const dataJson = chartContainer?.getAttribute('data-balance');
    if (!dataJson) return;
    
    try {
        const balanceData = JSON.parse(dataJson);
        const ingresos = parseFloat(balanceData.ingresos) || 0;
        
        // Remover todas las clases de color
        balanceValue.classList.remove('is-negative', 'is-warning', 'is-success');
        
        // Aplicar la clase correcta
        if (valor > 0) {
            balanceValue.classList.add('is-success');
        } else if (valor > -(ingresos / 2)) {
            balanceValue.classList.add('is-warning');
        } else {
            balanceValue.classList.add('is-negative');
        }
    } catch (e) {
        console.error('Error al actualizar color de balance:', e);
    }
}

// Actualizar color al cargar la página
document.addEventListener('DOMContentLoaded', actualizarColorBalance);

