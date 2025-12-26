// ============================
// CONSTANTES Y UTILIDADES
// ============================
const themeKey = "theme";
const defaultPage = "home";

// ============================
// ROUTER CENTRALIZADO
// ============================
const routes = {
    home: {
        file: 'pages/home.html',
        onLoad: 'loadHomeData'
    },
    inventario: {
        file: 'pages/inventario.html',
        onLoad: 'initInventario'
    }
    // futuras rutas aquí
};

// ============================
// MODO DÍA / NOCHE
// ============================
function updateThemeUI() {
    const isLight = document.body.classList.contains("light");
    document.querySelectorAll(".tema-texto").forEach(el => {
        el.textContent = isLight ? "Día" : "Noche";
    });
    document.querySelectorAll(".tema-icono").forEach(icon => {
        icon.classList.toggle("fa-sun", isLight);
        icon.classList.toggle("fa-moon", !isLight);
    });
}

if (localStorage.getItem(themeKey) === "light") {
    document.body.classList.add("light");
}

document.addEventListener("click", e => {
    const btn = e.target.closest(".cambiar-modo");
    if (!btn) return;
    document.body.classList.toggle("light");
    localStorage.setItem(
        themeKey,
        document.body.classList.contains("light") ? "light" : "dark"
    );
    updateThemeUI();
});

// ============================
// CARGA DE COMPONENTES
// ============================
function loadPartial(url, containerId) {
    return fetch(url)
        .then(res => res.ok ? res.text() : Promise.reject())
        .then(html => {
            const container = document.getElementById(containerId);
            if (container) container.innerHTML = html;
        })
        .catch(() => {
            const container = document.getElementById(containerId);
            if (container) {
                container.innerHTML =
                    `<p class="text-danger text-center">No se pudo cargar el componente.</p>`;
            }
        });
}

// ============================
// NAVLINKS ACTIVOS
// ============================
function setActiveNavLink(page) {
    document.querySelectorAll('[data-page]').forEach(link => {
        link.classList.toggle(
            "active",
            link.getAttribute("data-page") === page
        );
    });
    localStorage.setItem("activePage", page);
}

function attachNavEvents() {
    document.querySelectorAll('[data-page]').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const page = link.getAttribute('data-page');
            loadPage(page);
        });
    });
}

// ============================
// CARGAR PÁGINA DINÁMICA (ROUTER)
// ============================
function loadPage(page) {
    const route = routes[page];
    const main = document.getElementById('main-content');

    if (!route || !main) {
        if (main) {
            main.innerHTML =
                `<p class="text-danger text-center">Página no encontrada.</p>`;
        }
        return;
    }

    fetch(route.file)
        .then(res => res.ok ? res.text() : Promise.reject())
        .then(html => {
            main.innerHTML = html;
            setActiveNavLink(page);

            if (route.onLoad && typeof window[route.onLoad] === 'function') {
                window[route.onLoad]();
            }
        })
        .catch(() => {
            main.innerHTML =
                `<p class="text-danger text-center">No se pudo cargar la página.</p>`;
        });
}

// ============================
// CARGAR COMPONENTES PRINCIPALES
// ============================
Promise.all([
    loadPartial('components/navbar.html', 'navbar-container'),
    loadPartial('components/sidebar.html', 'sidebar-container'),
    loadPartial('components/offcanvas.html', 'offcanvas-container')
])
.then(() => {
    return Promise.all([
        loadPartial('components/navlinks.html', 'sidebar-links-container'),
        loadPartial('components/navlinks.html', 'offcanvas-links-container')
    ]);
})
.then(() => {
    attachNavEvents();
    updateThemeUI();

    const lastPage = localStorage.getItem("activePage") || defaultPage;
    loadPage(lastPage);
});
