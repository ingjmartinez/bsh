<?php

namespace Tests\Feature;

use Tests\TestCase;

class SidebarPerformanceTest extends TestCase
{
    public function test_sidebar_uses_one_non_blocking_scroll_container(): void
    {
        $layout = file_get_contents(resource_path('views/app.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringContainsString('<div id="scrollbar" data-simplebar>', $layout);
        $this->assertSame(1, substr_count($layout, 'id="scrollbar" data-simplebar'));
        $this->assertStringNotContainsString("scrollbar.addEventListener('wheel'", $layout);
        $this->assertStringNotContainsString('will-change: scroll-position', $layout);
        $this->assertStringNotContainsString("style.setProperty('--crm-sidebar-height'", $layout);
        $this->assertStringNotContainsString("window.addEventListener('resize', recalculateMenuScroll)", $layout);
        $this->assertStringContainsString('overflow-y: auto;', $layout);
        $this->assertStringContainsString('overflow-x: hidden;', $layout);
        $this->assertStringNotContainsString("max-height: calc(var(--crm-sidebar-height) - var(--crm-sidebar-brand-height));\n            overflow: hidden;", str_replace("\r\n", "\n", $layout));
        $this->assertStringContainsString(
            '<script src="{{ asset(\'js/app.js\') }}?v={{ $appVersion }}"></script>',
            $layout,
        );
        $this->assertStringContainsString(
            "#scrollbar .simplebar-content-wrapper {\n            -webkit-overflow-scrolling: touch;\n            overscroll-behavior: contain;\n            scroll-behavior: auto;",
            str_replace("\r\n", "\n", $layout),
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

    public function test_tablet_sidebar_keeps_the_theme_navigation_behavior_and_icons_visible(): void
    {
        $layout = file_get_contents(resource_path('views/app.blade.php'));
        $mobileOptimization = file_get_contents(public_path('js/mobile-optimization.js'));

        $this->assertIsString($layout);
        $this->assertIsString($mobileOptimization);
        $this->assertStringContainsString('@media (min-width: 768px) and (max-width: 1024px)', $layout);
        $this->assertStringContainsString('html[data-layout="vertical"][data-sidebar-size="sm"] #scrollbar', $layout);
        $this->assertStringContainsString('visibility: visible;', $layout);
        $this->assertStringNotContainsString('const tabletSidebar = window.matchMedia', $layout);
        $this->assertStringNotContainsString('event.stopImmediatePropagation();', $layout);
        $this->assertStringNotContainsString("document.body.classList.toggle('vertical-sidebar-enable');", $layout);
        $this->assertStringNotContainsString("document.addEventListener('touchend'", $mobileOptimization);
        $this->assertStringNotContainsString('preventDoubleTapZoom', $mobileOptimization);
    }

    public function test_collapsed_sales_api_menu_reports_the_correct_accessibility_state(): void
    {
        $layout = file_get_contents(resource_path('views/app.blade.php'));

        $this->assertIsString($layout);
        $this->assertStringContainsString(
            'class="nav-link menu-link collapsed" href="#sidebarApps" data-bs-toggle="collapse"',
            $layout,
        );
        $this->assertStringContainsString(
            'role="button" aria-expanded="false" aria-controls="sidebarApps"',
            $layout,
        );
    }
}
