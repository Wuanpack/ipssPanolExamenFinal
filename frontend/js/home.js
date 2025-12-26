// ============================
// FUNCION PRINCIPAL PARA CARGAR DATOS DEL DASHBOARD
// ============================
async function loadHomeData() {
    const cardsIds = [
        'card-total-herramientas',
        'card-prestamos-activos',
        'card-solicitudes-pendientes'
    ];
    const tableBody = document.getElementById('tabla-prestamos-body');

    // Mostrar mensajes de carga
    cardsIds.forEach(id => {
        const el = document.getElementById(id);
        if(el) el.textContent = 'Cargando...';
    });
    if(tableBody) tableBody.innerHTML = `<tr><td colspan="9" class="text-center">Cargando...</td></tr>`;

    try {
        const token = 'ipss.2025.T3';

        const res = await fetch('/PROYECTO_PANOL/API_PANOL/v1/endpoints/dashboard/dashboard.php', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        if (!res.ok) throw new Error('Error en la respuesta de la API');

        const json = await res.json();
        if (json.status !== 200) throw new Error('API devolvió status diferente de 200');

        const { cards, tabla } = json.data;

        // Mapeo explícito de IDs a keys de la API
        const cardKeysMap = {
            'card-total-herramientas': 'total_herramientas_activas',
            'card-prestamos-activos': 'prestamos_activos',
            'card-solicitudes-pendientes': 'solicitudes_pendientes'
        };

        // Actualizar cards
        cardsIds.forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                const key = cardKeysMap[id];
                const value = cards && cards[key] !== undefined ? parseInt(cards[key], 10) : 0;
                el.textContent = value;
            }
        });

        // Generar tabla de movimientos
        if(tableBody){
            tableBody.innerHTML = '';
            const movimientos = tabla || [];
            if(movimientos.length === 0){
                tableBody.innerHTML = `<tr><td colspan="9" class="text-center">No hay datos disponibles</td></tr>`;
            } else {
                movimientos.forEach(row => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="centrado">${row.n_movimiento || ''}</td>
                        <td class="centrado">${row.n_parte || ''}</td>
                        <td class="centrado">${row.herramienta || ''}</td>
                        <td class="centrado">${row.usuario || ''}</td>
                        <td class="centrado">${row.lugar || ''}</td>
                        <td class="centrado">${row.tipo_movimiento || ''}</td>
                        <td class="centrado">${row.fecha_prestamo || ''}</td>
                        <td class="centrado">${row.fecha_devolucion || ''}</td>
                        <td class="centrado">${row.cantidad !== undefined ? parseInt(row.cantidad,10) : ''}</td>
                    `;
                    tableBody.appendChild(tr);
                });
            }
        }

    } catch (err) {
        console.error(err);
        cardsIds.forEach(id => {
            const el = document.getElementById(id);
            if(el) el.textContent = 'Error al cargar';
        });
        if(tableBody) tableBody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">No se pudieron cargar los datos</td></tr>`;
    }
}

// Exportamos globalmente para que dashboard.js pueda llamarla
window.loadHomeData = loadHomeData;
