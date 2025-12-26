// ============================
// INVENTARIO DE HERRAMIENTAS
// ============================

const token = 'ipss.2025.T3';
const baseUrl = '/PROYECTO_PANOL/API_PANOL/v1/endpoints/herramientas';

let herramientaActual = null;
let tablaBody = null;
let modalModificar = null;
let modalCrear = null;
let currentPage = 1;
const limitPerPage = 30;
let paginatorContainer = null;

// ============================
// TOASTS
// ============================
function mostrarMensaje(texto, tipo = 'success') {
    const maxToasts = 5;
    const toastWidth = 300;
    const gap = 3;

    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        Object.assign(toastContainer.style, {
            position: 'fixed',
            top: '20px',
            right: '20px',
            display: 'flex',
            flexDirection: 'column',
            gap: `${gap}px`,
            zIndex: 9999
        });
        document.body.appendChild(toastContainer);
    }

    if (toastContainer.children.length >= maxToasts) {
        eliminarToast(toastContainer.children[toastContainer.children.length - 1]);
    }

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${tipo} border-0`;
    toast.role = 'alert';
    toast.style.opacity = '0';
    toast.style.transform = `translateX(${toastWidth}px)`;
    toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    toast.style.width = `${toastWidth}px`;
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${texto}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"></button>
        </div>
    `;
    toast.querySelector('.btn-close')?.addEventListener('click', () => eliminarToast(toast));
    toastContainer.prepend(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    });

    setTimeout(() => eliminarToast(toast), 3000);
}

function eliminarToast(toast) {
    if (!toast || toast.removing) return;
    toast.removing = true;
    const toastWidth = toast.offsetWidth;
    toast.style.opacity = '0';
    toast.style.transform = `translateX(${toastWidth}px)`;
    setTimeout(() => toast.remove(), 300);
}

// ============================
// MANEJO DE RESPUESTAS DE API
// ============================
function manejarRespuestaAPI(json, { exitoMsg = '' } = {}) {
    if (!json) return false;

    // Normalizar mensajes largos de error
    const normalizarMensaje = (msg) => {
        if (!msg) return '';
        // Ejemplo: recortar mensajes que contienen "No se pudo cambiar el estado" y similares
        if (msg.includes('No se pudo cambiar el estado de la herramienta')) {
            return 'No se puede desactivar la herramienta porque tiene préstamos activos';
        }
        // Otros recortes o reemplazos posibles
        return msg;
    };

    switch (json.status) {
        case 200:
        case 201:
            if (json.data?.no_changes) {
                mostrarMensaje('No se han realizado cambios', 'info');
                return false;
            }
            mostrarMensaje(exitoMsg || normalizarMensaje(json.data?.message) || 'Operación exitosa', 'success');
            return true;

        case 400:
        case 422:
            mostrarMensaje(normalizarMensaje(json.message) || 'Error de validación', 'danger');
            return false;

        case 409:
            mostrarMensaje(normalizarMensaje(json.message) || 'Conflicto al actualizar la herramienta', 'warning');
            return false;

        case 500:
            mostrarMensaje('Error interno del servidor', 'danger');
            return false;

        default:
            mostrarMensaje('Error desconocido', 'danger');
            return false;
    }
}


// ============================
// INICIALIZACIÓN
// ============================
function initInventario() {
    console.log('DOM ready:', document.readyState);
    console.log('Modal crear:', modalCrear);
console.log('Botón crear:', document.getElementById('btn-crear-herramienta'));

    tablaBody = document.getElementById('tabla-inventario-body');
    paginatorContainer = document.getElementById('inventario-paginator');
    modalModificar = new bootstrap.Modal(document.getElementById('modalModificar'));
    modalCrear = new bootstrap.Modal(document.getElementById('modalCrear'));

    cargarInventario(currentPage, limitPerPage);

    // Botón crear herramienta
    document.getElementById('btn-crear-herramienta')?.addEventListener('click', () => modalCrear.show());

    // Validaciones numéricas
    const camposNumericos = [
        'modificar-figura','modificar-cantidad','modificar-cantidad-disponible',
        'crear-figura','crear-cantidad','crear-cantidad-disponible'
    ];

    camposNumericos.forEach(id => {
        const input = document.getElementById(id);
        if (!input) return;
        input.addEventListener('input', () => {
            input.value = input.value.replace(/\D/g, '');
            if (!input.value) return;
            input.value = Math.max(0, parseInt(input.value, 10));

            if (id.includes('cantidad-disponible')) {
                const cantidadTotalId = id.includes('modificar') ? 'modificar-cantidad' : 'crear-cantidad';
                const cantidadTotal = parseInt(document.getElementById(cantidadTotalId).value) || 0;
                if (parseInt(input.value) > cantidadTotal) input.value = cantidadTotal;
            }
        });
    });

    // Listeners de guardar
    document.getElementById('btn-guardar-modificacion')?.addEventListener('click', guardarModificacion);
    document.getElementById('btn-guardar-crear')?.addEventListener('click', guardarCreacion);
}

// ============================
// FUNCIONES DE GUARDADO
// ============================
async function guardarModificacion() {
    if (!herramientaActual) return;

    const data = {
        id: herramientaActual.id,
        nombre: document.getElementById('modificar-nombre').value,
        n_parte: document.getElementById('modificar-n-parte').value,
        figura: parseInt(document.getElementById('modificar-figura').value) || 0,
        indice: document.getElementById('modificar-indice').value,
        pagina: document.getElementById('modificar-pagina').value,
        cantidad: parseInt(document.getElementById('modificar-cantidad').value) || 0,
        cantidad_disponible: parseInt(document.getElementById('modificar-cantidad-disponible').value) || 0
    };

    if (data.cantidad_disponible > data.cantidad) {
        mostrarMensaje('La cantidad disponible no puede ser mayor a la cantidad total', 'danger');
        return;
    }

    try {
        const res = await fetch(`${baseUrl}/modificar.php?id=${herramientaActual.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(data)
        });

        const json = await res.json();

        if (manejarRespuestaAPI(json, { exitoMsg: 'Herramienta modificada con éxito' })) {
            modalModificar.hide();
            cargarInventario(currentPage, limitPerPage);
        }

    } catch (err) {
        console.error(err);
        mostrarMensaje('Error de conexión al modificar herramienta', 'danger');
    }
}

async function guardarCreacion() {
    const nombre = document.getElementById('crear-nombre').value.trim();
    const n_parte = document.getElementById('crear-n-parte').value.trim();
    const figura = parseInt(document.getElementById('crear-figura').value);
    const indice = document.getElementById('crear-indice').value.trim();
    const pagina = document.getElementById('crear-pagina').value.trim();
    const cantidad = parseInt(document.getElementById('crear-cantidad').value);
    const cantidad_disponible = parseInt(document.getElementById('crear-cantidad-disponible').value);

    if (!nombre || !n_parte || isNaN(figura) || !indice || !pagina || isNaN(cantidad) || isNaN(cantidad_disponible)) {
        mostrarMensaje('Todos los campos son obligatorios y deben ser válidos', 'danger');
        return;
    }

    if (cantidad_disponible > cantidad) {
        mostrarMensaje('La cantidad disponible no puede ser mayor a la cantidad total', 'danger');
        return;
    }

    const data = { nombre, n_parte, figura, indice, pagina, cantidad, cantidad_disponible };

    try {
        const res = await fetch(`${baseUrl}/anadirHerramienta.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${token}` },
            body: JSON.stringify(data)
        });

        const json = await res.json();

        if (manejarRespuestaAPI(json, { exitoMsg: 'Herramienta creada con éxito' })) {
            ['crear-nombre','crear-n-parte','crear-figura','crear-indice','crear-pagina','crear-cantidad','crear-cantidad-disponible']
                .forEach(id => document.getElementById(id).value = '');
            modalCrear.hide();
            cargarInventario(currentPage, limitPerPage);
        }

    } catch (err) {
        console.error(err);
        mostrarMensaje('Error de conexión al crear herramienta', 'danger');
    }
}

// ============================
// CARGAR INVENTARIO
// ============================
async function cargarInventario(page = 1, limit = limitPerPage) {
    if (!tablaBody) return;

    currentPage = page;
    tablaBody.innerHTML = `<tr><td colspan="10" class="text-center">Cargando...</td></tr>`;
    if (paginatorContainer) paginatorContainer.innerHTML = '';

    try {
        const res = await fetch(`${baseUrl}/herramientas.php?page=${page}&limit=${limit}`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const json = await res.json();
        if (json.status !== 200) throw new Error('Error en la respuesta de la API');

        const herramientas = json.data?.herramientas || [];
        const pagination = json.data?.pagination || { page: 1, total_pages: 1 };

        tablaBody.innerHTML = '';

        if (!herramientas.length) {
            tablaBody.innerHTML = `<tr><td colspan="10" class="text-center">No hay herramientas disponibles</td></tr>`;
        } else {
            herramientas.forEach(herr => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${herr.id}</td>
                    <td>${herr.n_parte || ''}</td>
                    <td>${herr.nombre}</td>
                    <td>${herr.figura || ''}</td>
                    <td>${herr.indice || ''}</td>
                    <td>${herr.pagina || ''}</td>
                    <td>${herr.cantidad || ''}</td>
                    <td>${herr.cantidad_disponible || ''}</td>
                    <td>${herr.activo ? 'Activo' : 'Desactivado'}</td>
                    <td class="d-flex gap-2">
                        <button class="btn btn-sm btn-modificar-custom">Modificar</button>
                        <button class="btn btn-sm btn-activar-custom ${herr.activo ? 'desactivar' : 'activar'}">
                            ${herr.activo ? 'Desactivar' : 'Activar'}
                        </button>
                    </td>
                `;
                tablaBody.appendChild(tr);

                tr.querySelector('.btn-modificar-custom')?.addEventListener('click', () => abrirModal(herr));
                tr.querySelector('.btn-activar-custom')?.addEventListener('click', () => toggleActivo(herr));
            });
        }

        if (paginatorContainer) {
            const prevDisabled = pagination.page <= 1 ? 'disabled' : '';
            const nextDisabled = pagination.page >= pagination.total_pages ? 'disabled' : '';

            paginatorContainer.innerHTML = `
                <button class="btn btn-sm btn-primary me-2" ${prevDisabled} id="prev-page">Anterior</button>
                <span class="align-self-center">Página ${pagination.page} de ${pagination.total_pages}</span>
                <button class="btn btn-sm btn-primary ms-2" ${nextDisabled} id="next-page">Siguiente</button>
            `;

            document.getElementById('prev-page')?.addEventListener('click', () => cargarInventario(pagination.page - 1, limit));
            document.getElementById('next-page')?.addEventListener('click', () => cargarInventario(pagination.page + 1, limit));
        }

    } catch (err) {
        tablaBody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">Error al cargar inventario</td></tr>`;
        console.error(err);
    }
}

// ============================
// MODAL MODIFICACIÓN
// ============================
function abrirModal(herr) {
    herramientaActual = herr;

    const fields = [
        'modificar-id', 'modificar-nombre', 'modificar-n-parte',
        'modificar-figura', 'modificar-indice', 'modificar-pagina',
        'modificar-cantidad', 'modificar-cantidad-disponible'
    ];
    for (const f of fields) {
        if (!document.getElementById(f)) {
            console.error(`Campo ${f} no existe en el DOM`);
            return;
        }
    }

    document.getElementById('modificar-id').value = herr.id;
    document.getElementById('modificar-nombre').value = herr.nombre;
    document.getElementById('modificar-n-parte').value = herr.n_parte;
    document.getElementById('modificar-figura').value = herr.figura;
    document.getElementById('modificar-indice').value = herr.indice;
    document.getElementById('modificar-pagina').value = herr.pagina;
    document.getElementById('modificar-cantidad').value = herr.cantidad;
    document.getElementById('modificar-cantidad-disponible').value = herr.cantidad_disponible;

    modalModificar.show();
}

// ============================
// ACTIVAR / DESACTIVAR
// ============================
async function toggleActivo(herr) {
    if (!herr) return;

    const endpoint = herr.activo ? 'desactivar.php' : 'activar.php';
    const method = herr.activo ? 'DELETE' : 'PATCH';

    try {
        const res = await fetch(`${baseUrl}/${endpoint}?id=${herr.id}`, {
            method,
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const json = await res.json();

        if (manejarRespuestaAPI(json, { 
            exitoMsg: herr.activo ? 'Herramienta desactivada con éxito' : 'Herramienta activada con éxito'
        })) {
            cargarInventario(currentPage, limitPerPage);
        }

    } catch (err) {
        console.error(err);
        mostrarMensaje('Error de conexión al actualizar estado', 'danger');
    }
}

// ============================
// EXPORT
// ============================
window.initInventario = initInventario;
