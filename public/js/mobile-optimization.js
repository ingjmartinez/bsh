/**
 * Mobile Optimization Utilities
 * Utilidades para mejorar la experiencia en móvil
 * 
 * Este archivo proporciona funciones auxiliares para detectar
 * dispositivos móviles, cambios de orientación y ajustar la UI
 * en tiempo de ejecución.
 */

(function() {
    'use strict';

    const MobileOptimization = {
        /**
         * Detectar si es dispositivo móvil
         */
        isMobile() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        },

        /**
         * Detectar si es tablet
         */
        isTablet() {
            return /(tablet|iPad|PlayBook|silk)|(android(?!.*mobi))/i.test(navigator.userAgent);
        },

        /**
         * Detectar si es desktop
         */
        isDesktop() {
            return !this.isMobile();
        },

        /**
         * Obtener ancho de la ventana
         */
        getWindowWidth() {
            return window.innerWidth || document.documentElement.clientWidth;
        },

        /**
         * Obtener altura de la ventana
         */
        getWindowHeight() {
            return window.innerHeight || document.documentElement.clientHeight;
        },

        /**
         * Detectar orientación actual
         */
        getOrientation() {
            if (window.matchMedia('(orientation: portrait)').matches) {
                return 'portrait';
            }
            if (window.matchMedia('(orientation: landscape)').matches) {
                return 'landscape';
            }
            return 'unknown';
        },

        /**
         * Escuchar cambios de orientación
         */
        onOrientationChange(callback) {
            const mediaQuery = window.matchMedia('(orientation: portrait)');
            mediaQuery.addEventListener('change', (e) => {
                const orientation = e.matches ? 'portrait' : 'landscape';
                if (callback) callback(orientation);
            });
        },

        /**
         * Detectar navegador
         */
        getBrowser() {
            const ua = navigator.userAgent;
            if (ua.indexOf('Chrome') > -1) return 'chrome';
            if (ua.indexOf('Safari') > -1) return 'safari';
            if (ua.indexOf('Firefox') > -1) return 'firefox';
            if (ua.indexOf('MSIE') > -1 || ua.indexOf('Trident') > -1) return 'ie';
            return 'unknown';
        },

        /**
         * Inicializar optimizaciones mobile
         */
        init() {
            if (!this.isMobile()) {
                return;
            }

            // Agregar clase al body
            document.body.classList.add('is-mobile');
            
            if (this.isTablet()) {
                document.body.classList.add('is-tablet');
            }

            // Detectar navegador
            const browser = this.getBrowser();
            document.body.classList.add(`browser-${browser}`);

            // Escuchar cambios de orientación
            this.onOrientationChange((orientation) => {
                document.body.classList.remove('orientation-portrait', 'orientation-landscape');
                document.body.classList.add(`orientation-${orientation}`);
            });

            // Establecer orientación inicial
            const initialOrientation = this.getOrientation();
            document.body.classList.add(`orientation-${initialOrientation}`);

            // Prevenir zoom en doble tap
            this.preventDoubleTapZoom();

            // Ajustar modales
            this.optimizeModals();

            // Ajustar tablas
            this.optimizeTables();

            console.log('✓ Mobile Optimization initialized');
        },

        /**
         * Prevenir zoom con doble tap
         */
        preventDoubleTapZoom() {
            let lastTap = 0;
            document.addEventListener('touchend', (e) => {
                const currentTime = new Date().getTime();
                const tapLength = currentTime - lastTap;
                
                if (tapLength < 500 && tapLength > 0) {
                    e.preventDefault();
                }
                lastTap = currentTime;
            }, false);
        },

        /**
         * Optimizar modales para móvil
         */
        optimizeModals() {
            // Hacer modales fullscreen en móvil
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                const dialog = modal.querySelector('.modal-dialog');
                if (dialog) {
                    dialog.style.maxHeight = 'calc(100vh - 20px)';
                }
            });

            // Escuchar cuando se abra modal
            document.addEventListener('show.bs.modal', function(e) {
                const modal = e.target;
                const scrollPosition = window.scrollY;
                document.body.style.position = 'fixed';
                document.body.style.top = `-${scrollPosition}px`;
                document.body.style.width = '100%';
            });

            // Restaurar posición cuando se cierre modal
            document.addEventListener('hide.bs.modal', function(e) {
                const scrollPosition = parseInt(document.body.style.top || '0') * -1;
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                window.scrollTo(0, scrollPosition);
            });
        },

        /**
         * Optimizar tablas para móvil
         */
        optimizeTables() {
            const tables = document.querySelectorAll('table.table');
            tables.forEach(table => {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    cells.forEach((cell, index) => {
                        const headerCell = table.querySelector(`thead tr th:nth-child(${index + 1})`);
                        if (headerCell) {
                            cell.setAttribute('data-label', headerCell.textContent.trim());
                        }
                    });
                });
            });
        },

        /**
         * Ajustar altura de elementos a viewport
         */
        setViewportHeight() {
            document.documentElement.style.setProperty('--viewport-height', `${this.getWindowHeight()}px`);
        },

        /**
         * Agregar listener de resize
         */
        onResize(callback, delay = 250) {
            let timeout;
            window.addEventListener('resize', () => {
                clearTimeout(timeout);
                timeout = setTimeout(callback, delay);
            });
        },

        /**
         * Detectar teclado virtual (iOS)
         */
        onKeyboardShow(callback) {
            const initialHeight = window.innerHeight;
            
            window.addEventListener('resize', () => {
                const currentHeight = window.innerHeight;
                if (currentHeight < initialHeight * 0.75) {
                    if (callback) callback(true);
                } else {
                    if (callback) callback(false);
                }
            });
        },

        /**
         * Scroll suave a elemento
         */
        smoothScrollTo(element, offset = 20) {
            if (!element) return;
            
            const top = element.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({
                top: top,
                behavior: 'smooth'
            });
        },

        /**
         * Obtener altura del header
         */
        getHeaderHeight() {
            const header = document.querySelector('#page-topbar');
            return header ? header.offsetHeight : 0;
        },

        /**
         * Ajustar contenido a debajo del header
         */
        adjustContentTop() {
            const headerHeight = this.getHeaderHeight();
            const content = document.querySelector('.main-content');
            if (content) {
                content.style.marginTop = `${headerHeight}px`;
            }
        },

        /**
         * Activar modo de alta contraste
         */
        enableHighContrast(enable = true) {
            if (enable) {
                document.body.classList.add('high-contrast');
            } else {
                document.body.classList.remove('high-contrast');
            }
        },

        /**
         * Obtener información del dispositivo
         */
        getDeviceInfo() {
            return {
                userAgent: navigator.userAgent,
                isMobile: this.isMobile(),
                isTablet: this.isTablet(),
                isDesktop: this.isDesktop(),
                browser: this.getBrowser(),
                orientation: this.getOrientation(),
                width: this.getWindowWidth(),
                height: this.getWindowHeight(),
                isRetina: window.devicePixelRatio > 1
            };
        },

        /**
         * Log información del dispositivo (desarrollo)
         */
        logDeviceInfo() {
            console.table(this.getDeviceInfo());
        }
    };

    // Inicializar cuando el DOM está listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => MobileOptimization.init());
    } else {
        MobileOptimization.init();
    }

    // Exponer globalmente
    window.MobileOptimization = MobileOptimization;

    // Compatibilidad jQuery si está disponible
    if (typeof jQuery !== 'undefined') {
        jQuery.extend({
            MobileOptimization: MobileOptimization
        });
    }

})();
