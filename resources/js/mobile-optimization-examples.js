/**
 * Ejemplos de Uso - Mobile Optimization
 * 
 * Este archivo contiene ejemplos de cómo usar el objeto MobileOptimization
 * en sus vistas y scripts JavaScript.
 */

// ============================================
// EJEMPLO 1: Detectar tipo de dispositivo
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si es mobile
    if (MobileOptimization.isMobile()) {
        console.log('Es un dispositivo móvil');
        // Aquí puedes ejecutar código específico para móvil
    }

    // Verificar si es tablet
    if (MobileOptimization.isTablet()) {
        console.log('Es una tablet');
    }

    // Verificar si es desktop
    if (MobileOptimization.isDesktop()) {
        console.log('Es un desktop');
    }
});

// ============================================
// EJEMPLO 2: Detectar cambios de orientación
// ============================================
MobileOptimization.onOrientationChange(function(orientation) {
    console.log('Orientación cambiada a: ' + orientation);
    
    if (orientation === 'portrait') {
        // Hacer algo cuando está en portrait
        console.log('Modo vertical');
    } else if (orientation === 'landscape') {
        // Hacer algo cuando está en landscape
        console.log('Modo horizontal');
    }
});

// ============================================
// EJEMPLO 3: Scroll dinámico a un elemento
// ============================================
function irAlElemento(selector) {
    const elemento = document.querySelector(selector);
    MobileOptimization.smoothScrollTo(elemento, 60);
}

// Uso:
// irAlElemento('#mi-formulario');

// ============================================
// EJEMPLO 4: Detectar teclado virtual (iOS)
// ============================================
MobileOptimization.onKeyboardShow(function(isVisible) {
    if (isVisible) {
        console.log('Teclado virtual mostrado');
        // Hacer scroll automático al campo de entrada
    } else {
        console.log('Teclado virtual ocultado');
    }
});

// ============================================
// EJEMPLO 5: Obtener información del dispositivo
// ============================================
function mostrarInfoDispositivo() {
    const info = MobileOptimization.getDeviceInfo();
    console.log('Información del dispositivo:', info);
    
    // Ejemplo de salida:
    // {
    //   userAgent: "Mozilla/5.0 ...",
    //   isMobile: true,
    //   isTablet: false,
    //   isDesktop: false,
    //   browser: "chrome",
    //   orientation: "portrait",
    //   width: 375,
    //   height: 812,
    //   isRetina: true
    // }
}

// ============================================
// EJEMPLO 6: Listener de resize con debounce
// ============================================
MobileOptimization.onResize(function() {
    console.log('Ventana redimensionada');
    const width = MobileOptimization.getWindowWidth();
    const height = MobileOptimization.getWindowHeight();
    console.log(`Nuevo tamaño: ${width}x${height}`);
}, 250); // Espera 250ms después del último resize

// ============================================
// EJEMPLO 7: Usar clases agregadas al body
// ============================================
// El script agrega automáticamente clases al <body>:
// - is-mobile (si es móvil)
// - is-tablet (si es tablet)
// - browser-chrome, browser-firefox, browser-safari, etc.
// - orientation-portrait, orientation-landscape

// En CSS puedes usarlas:
/*
body.is-mobile .elemento {
    font-size: 14px;
}

body.is-desktop .elemento {
    font-size: 16px;
}

body.orientation-landscape .elemento {
    height: calc(100vh - 50px);
    /* Account for header */
}

body.browser-safari input[type="text"] {
    /* Estilos específicos para Safari */
}
*/

// ============================================
// EJEMPLO 8: Habilitar alto contraste
// ============================================
function activarAltoContraste() {
    MobileOptimization.enableHighContrast(true);
}

function desactivarAltoContraste() {
    MobileOptimization.enableHighContrast(false);
}

// ============================================
// EJEMPLO 9: Ajustar height CSS variable
// ============================================
// El script establece automáticamente una variable CSS:
// --viewport-height que puedes usar en CSS

/*
CSS:
.modal-body {
    max-height: calc(var(--viewport-height) - 100px);
    overflow-y: auto;
}
*/

// ============================================
// EJEMPLO 10: Aplicar en DataTables
// ============================================
$(document).ready(function() {
    // Obtener el ancho de la ventana
    const width = MobileOptimization.getWindowWidth();
    
    // Configurar columnas ocultas según el device
    let columnDefs = [];
    
    if (width < 768) {
        // En móvil, oculta columnas menos importantes
        columnDefs = [
            { targets: [3, 4, 5], visible: false }
        ];
    }
    
    $('#tabla').DataTable({
        columnDefs: columnDefs,
        responsive: true,
    });
    
    // Reajustar cuando cambie tamaño
    MobileOptimization.onResize(function() {
        $('#tabla').DataTable().columns.adjust().draw();
    });
});

// ============================================
// EJEMPLO 11: Formularios adaptativos
// ============================================
function adaptarFormulario() {
    const isMobile = MobileOptimization.isMobile();
    
    if (isMobile) {
        // En móvil: inputs full-width
        document.querySelectorAll('input[type="text"]').forEach(input => {
            input.classList.add('w-100');
        });
    }
}

// ============================================
// EJEMPLO 12: Cambiar comportamiento de modales
// ============================================
/*
En móvil, el modalautomáticamente ajusta:
- max-height al viewport
- Manejo del scroll
- Presión en el body para evitar scroll

Esto se hace automáticamente en la inicialización.
*/

// ============================================
// EJEMPLO 13: Log de información (desarrollo)
// ============================================
// Para desarrollo, puedes logear info del device:
// MobileOptimization.logDeviceInfo();

// Esto mostrará una tabla en la consola con toda la información

// ============================================
// EJEMPLO 14: Usar con jQuery
// ============================================
// El objeto también está disponible a través de jQuery:
// $.MobileOptimization.isMobile()
// $.MobileOptimization.getDeviceInfo()

// ============================================
// EJEMPLO 15: Casos de uso comunes
// ============================================

// A. Ocultar elementos en móvil (MEJOR USAR CSS)
function escondeEnMovil(selector) {
    if (MobileOptimization.isMobile()) {
        document.querySelector(selector).style.display = 'none';
    }
}

// B. Aplicar estilos dinámicamente
function aplicarEstilosMovil() {
    if (MobileOptimization.isMobile()) {
        document.body.style.fontSize = '14px';
    }
}

// C. Cargar contenido diferente
function cargarContenido() {
    if (MobileOptimization.isMobile()) {
        // Cargar versión móvil
        fetch('/api/contenido?device=mobile')
            .then(response => response.json())
            .then(data => renderizar(data));
    }
}

// D. Ajustar timeout de animaciones
var animationTimeout = MobileOptimization.isMobile() ? 300 : 0;

// E. Desactivar ciertas funciones en móvil
document.addEventListener('hover-feature', function(e) {
    if (MobileOptimization.isDesktop()) {
        // Solo en desktop
        aplicarHoverEffect(e);
    }
});

// ============================================
// NOTAS IMPORTANTES
// ============================================
/*
1. MobileOptimization se inicializa automáticamente
2. Las clases en el body se agregan al cargar
3. Los listeners se activan cuando están listos
4. El objeto está disponible globalmente: window.MobileOptimization
5. Usar CSS media queries cuando sea posible
6. JavaScript debe ser para casos complejos
7. Siempre testear en dispositivos reales
8. Usar DevTools para emular diferentes tamaños
*/
