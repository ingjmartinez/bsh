<?php

namespace Tests\Feature;

use Tests\TestCase;

class SidebarPerformanceTest extends TestCase
{
    public function test_sidebar_uses_one_non_blocking_scroll_container(): void
    {
        $layout = file_get_contents(resource_path('views/app.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringContainsString('<div id="scrollbar" data-simplebar data-simplebar-auto-hide="false">', $layout);
        $this->assertSame(1, substr_count($layout, 'id="scrollbar" data-simplebar'));
        $this->assertStringNotContainsString("scrollbar.addEventListener('wheel'", $layout);
        $this->assertStringNotContainsString('will-change: scroll-position', $layout);
        $this->assertStringNotContainsString("style.setProperty('--crm-sidebar-height'", $layout);
        $this->assertStringNotContainsString("window.addEventListener('resize', recalculateMenuScroll)", $layout);
        $this->assertStringNotContainsString("max-height: calc(var(--crm-sidebar-height) - var(--crm-sidebar-brand-height));\n            overflow: hidden;", str_replace("\r\n", "\n", $layout));
        $this->assertStringContainsString(
            '<script src="{{ asset(\'js/app.js\') }}?v={{ $appVersion }}"></script>',
            $layout,
        );
        $themeScript = file_get_contents(public_path('js/app.js'));

        $this->assertIsString($themeScript);
        $this->assertStringContainsString(
            'document.getElementById("navbar-nav").removeAttribute("data-simplebar")',
            $themeScript,
        );
        $this->assertStringNotContainsString(
            'document.getElementById("navbar-nav").setAttribute("data-simplebar","")',
            $themeScript,
        );
        $this->assertStringContainsString(
            '!document.getElementById("scrollbar")||(e=new SimpleBar(document.getElementById("scrollbar")))',
            $themeScript,
        );
    }

    public function test_sidebar_styles_are_versioned_and_support_native_and_enhanced_scrolling(): void
    {
        $layout = file_get_contents(resource_path('views/app.blade.php'));
        $publicStyles = file_get_contents(public_path('css/sidebar.css'));

        $this->assertIsString($layout);
        $this->assertIsString($publicStyles);
        $this->assertStringContainsString(
            "{{ asset('css/sidebar.css') }}?v={{ filemtime(public_path('css/sidebar.css')) }}",
            $layout,
        );
        $this->assertStringContainsString('flex: 1 1 0;', $publicStyles);
        $this->assertStringContainsString('height: auto !important;', $publicStyles);
        $this->assertStringContainsString('overflow-y: auto;', $publicStyles);
        $this->assertStringContainsString('overflow-y: scroll !important;', $publicStyles);
        $this->assertStringContainsString('touch-action: pan-y;', $publicStyles);
        $this->assertStringContainsString('height: auto;', $publicStyles);
        $this->assertStringNotContainsString('.simplebar-mask,', $publicStyles);
    }

    public function test_tablet_sidebar_keeps_the_theme_navigation_behavior_and_icons_visible(): void
    {
        $publicStyles = file_get_contents(public_path('css/sidebar.css'));
        $mobileOptimization = file_get_contents(public_path('js/mobile-optimization.js'));

        $this->assertIsString($publicStyles);
        $this->assertIsString($mobileOptimization);
        $this->assertStringContainsString('@media (min-width: 768px) and (max-width: 1024px)', $publicStyles);
        $this->assertStringContainsString(
            'html[data-layout="vertical"][data-sidebar-size="sm"] .app-menu.navbar-menu .navbar-nav .nav-link i',
            $publicStyles,
        );
        $this->assertStringContainsString('visibility: visible;', $publicStyles);
        $this->assertStringNotContainsString('const tabletSidebar = window.matchMedia', $mobileOptimization);
        $this->assertStringNotContainsString('event.stopImmediatePropagation();', $mobileOptimization);
        $this->assertStringNotContainsString("document.body.classList.toggle('vertical-sidebar-enable');", $mobileOptimization);
        $this->assertStringNotContainsString("document.addEventListener('touchend'", $mobileOptimization);
        $this->assertStringNotContainsString('preventDoubleTapZoom', $mobileOptimization);
    }

    public function test_sales_api_menu_reports_the_current_accessibility_state(): void
    {
        $layout = file_get_contents(resource_path('views/app.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringContainsString(
            'href="#sidebarApps" data-bs-toggle="collapse" role="button"',
            $layout,
        );
        $this->assertStringContainsString(
            'aria-expanded="{{ $isVentasDsVirtualActive ? \'true\' : \'false\' }}"',
            $layout,
        );
        $this->assertStringContainsString('aria-controls="sidebarApps"', $layout);
    }
}
