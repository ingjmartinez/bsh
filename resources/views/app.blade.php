<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none">

<head>

    <meta charset="utf-8" />
    <title>Business Support Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">

    <!-- One of the following themes -->
    <link rel="stylesheet" href="{{asset('assets/libs/@simonwep/pickr/themes/classic.min.css')}}" /> <!-- 'classic' theme -->
    <link rel="stylesheet" href="{{asset('assets/libs/@simonwep/pickr/themes/monolith.min.css')}}" /> <!-- 'monolith' theme -->
    <link rel="stylesheet" href="{{asset('assets/libs/@simonwep/pickr/themes/nano.min.css')}}" /> <!-- 'nano' theme -->

    <!--datatable css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" />
    <!--datatable responsive css-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <link href="{{ asset('libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Layout config Js -->
    <script src="{{ asset('js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Mobile Responsive CSS -->
    <link href="{{ asset('css/mobile.css') }}" rel="stylesheet" type="text/css" />

    <style>
        :root {
            --crm-sidebar-height: 100vh;
            --crm-sidebar-brand-height: 70px;
        }

        @supports (height: 100dvh) {
            :root {
                --crm-sidebar-height: 100dvh;
            }
        }

        html {
            scroll-behavior: smooth;
        }

        body,
        .main-content,
        .page-content,
        #scrollbar,
        #scrollbar .simplebar-content-wrapper,
        [data-simplebar] .simplebar-content-wrapper {
            -webkit-overflow-scrolling: touch;
            scroll-behavior: smooth;
        }

        #scrollbar,
        #scrollbar .simplebar-content-wrapper {
            overscroll-behavior: contain;
            will-change: scroll-position;
        }

        html[data-layout="vertical"] .app-menu.navbar-menu,
        html[data-layout="twocolumn"] .app-menu.navbar-menu {
            display: flex;
            flex-direction: column;
            height: var(--crm-sidebar-height);
            max-height: var(--crm-sidebar-height);
            overflow: hidden;
        }

        html[data-layout="vertical"] .app-menu.navbar-menu .navbar-brand-box,
        html[data-layout="twocolumn"] .app-menu.navbar-menu .navbar-brand-box {
            flex: 0 0 var(--crm-sidebar-brand-height);
        }

        html[data-layout="vertical"] #scrollbar,
        html[data-layout="twocolumn"] #scrollbar {
            flex: 1 1 auto;
            min-height: 0;
            height: calc(var(--crm-sidebar-height) - var(--crm-sidebar-brand-height)) !important;
            max-height: calc(var(--crm-sidebar-height) - var(--crm-sidebar-brand-height));
            overflow-y: auto;
            overflow-x: hidden;
        }

        html[data-layout="vertical"] #scrollbar .container-fluid,
        html[data-layout="twocolumn"] #scrollbar .container-fluid {
            height: 100%;
            min-height: 0;
            padding-bottom: 2rem;
        }

        html[data-layout="vertical"] #navbar-nav,
        html[data-layout="twocolumn"] #navbar-nav {
            min-height: 0;
            padding-bottom: 1.25rem;
        }

        html[data-layout="vertical"] #navbar-nav[data-simplebar],
        html[data-layout="twocolumn"] #navbar-nav[data-simplebar] {
            height: 100%;
            max-height: 100%;
            overflow: hidden;
        }

        html[data-layout="vertical"] #scrollbar .simplebar-content-wrapper,
        html[data-layout="twocolumn"] #scrollbar .simplebar-content-wrapper,
        html[data-layout="vertical"] #navbar-nav .simplebar-content-wrapper,
        html[data-layout="twocolumn"] #navbar-nav .simplebar-content-wrapper {
            max-height: 100%;
        }

        html[data-layout="vertical"] #navbar-nav .simplebar-content,
        html[data-layout="twocolumn"] #navbar-nav .simplebar-content {
            padding-bottom: 1.25rem !important;
        }

        #navbar-nav .nav-link,
        #navbar-nav .menu-link,
        #navbar-nav .menu-dropdown {
            transition: background-color 0.18s ease, color 0.18s ease, padding-left 0.18s ease, transform 0.18s ease;
        }

        #navbar-nav .menu-dropdown .nav-link:hover {
            transform: translateX(2px);
        }

        #navbar-nav .sidebar-brand-title {
            padding: 1.05rem 1.5rem 0.7rem;
        }

        #navbar-nav .sidebar-brand-title .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0;
            line-height: 1.1;
        }

        #navbar-nav .sidebar-brand-title .brand-mark i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            color: var(--bsh-yellow, #ECBE13);
            background: rgba(236, 190, 19, 0.14);
            font-size: 1rem;
        }

        html[data-sidebar-size="sm"] #navbar-nav .sidebar-brand-title,
        html[data-sidebar-size="sm-hover"] #navbar-nav .sidebar-brand-title {
            display: none;
        }

        #navbar-nav .menu-dropdown.show {
            max-height: none;
            overflow: visible;
            overscroll-behavior: contain;
            padding-right: 0;
        }

        #navbar-nav .menu-dropdown.show::-webkit-scrollbar {
            width: 10px;
        }

        #navbar-nav .menu-dropdown.show::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.28);
            background-clip: content-box;
            border: 3px solid transparent;
            border-radius: 999px;
        }

        #navbar-nav .menu-dropdown.show::-webkit-scrollbar-track {
            background: transparent;
        }

        #scrollbar,
        #navbar-nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.28) transparent;
        }

        #scrollbar::-webkit-scrollbar,
        #navbar-nav::-webkit-scrollbar {
            width: 10px;
        }

        #scrollbar::-webkit-scrollbar-thumb,
        #navbar-nav::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.28);
            background-clip: content-box;
            border: 3px solid transparent;
            border-radius: 999px;
        }

        #scrollbar::-webkit-scrollbar-track,
        #navbar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        #scrollbar .simplebar-track.simplebar-vertical,
        #navbar-nav .simplebar-track.simplebar-vertical {
            right: 1px;
            width: 10px;
            background: transparent;
        }

        #scrollbar .simplebar-scrollbar::before,
        #navbar-nav .simplebar-scrollbar::before {
            left: 3px;
            right: 3px;
            background: rgba(255, 255, 255, 0.30);
            border-radius: 999px;
            opacity: 0.55;
        }

        #scrollbar .simplebar-track.simplebar-vertical:hover .simplebar-scrollbar::before,
        #navbar-nav .simplebar-track.simplebar-vertical:hover .simplebar-scrollbar::before {
            background: rgba(255, 255, 255, 0.46);
            opacity: 0.75;
        }

        .layout-width,
        .vertical-menu,
        .main-content {
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        .task-notif-item {
            white-space: normal;
        }

        .topbar-task-notif-list {
            max-height: min(60vh, 360px);
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior: contain;
        }

        .task-notif-item .flex-grow-1 {
            min-width: 0;
        }

        .task-notif-item h6,
        .task-notif-item p,
        .task-notif-item small {
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
            margin-right: 0;
        }

        .main-content > .footer {
            display: none !important;
        }

        @media (prefers-reduced-motion: reduce) {
            html,
            body,
            .main-content,
            .page-content,
            #scrollbar,
            #scrollbar .simplebar-content-wrapper,
            [data-simplebar] .simplebar-content-wrapper {
                scroll-behavior: auto;
            }

            #navbar-nav .nav-link,
            #navbar-nav .menu-link,
            #navbar-nav .menu-dropdown {
                transition: none;
            }
        }
    </style>

</head>

<body>
    @php
        $appVersion = config('app.version', '1.0.0');
    @endphp
    <!-- Begin page -->
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <!-- LOGO -->
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="index.html" class="logo logo-dark">
                                <span class="logo-sm">
                                    {{-- <img src="images/logo-sm.png" alt="" height="22"> --}}
                                </span>
                                <span class="logo-lg">
                                    {{-- <img src="images/logo-dark.png" alt="" height="17"> --}}
                                </span>
                            </a>

                            <a href="index.html" class="logo logo-light">
                                <span class="logo-sm">
                                    {{-- <img src="images/logo-sm.png" alt="" height="22"> --}}
                                </span>
                                <span class="logo-lg">
                                    {{-- <img src="images/logo-light.png" alt="" height="17"> --}}
                                </span>
                            </a>
                        </div>

                        <button type="button"
                            class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger"
                            id="topnav-hamburger-icon">
                            <span class="hamburger-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                        </button>

                        <!-- App Search-->
                        <form class="app-search d-none">
                            <div class="position-relative">
                                <input type="text" class="form-control" placeholder="Search..." autocomplete="off"
                                    id="search-options" value="">
                                <span class="mdi mdi-magnify search-widget-icon"></span>
                                <span class="mdi mdi-close-circle search-widget-icon search-widget-icon-close d-none"
                                    id="search-close-options"></span>
                            </div>
                            <div class="dropdown-menu dropdown-menu-lg" id="search-dropdown">
                                <div data-simplebar style="max-height: 320px;">
                                    <!-- item-->
                                    <div class="dropdown-header">
                                        <h6 class="text-overflow text-muted mb-0 text-uppercase">Recent Searches</h6>
                                    </div>

                                    <div class="dropdown-item bg-transparent text-wrap">
                                        <a href="index.html" class="btn btn-soft-secondary btn-sm btn-rounded">how to
                                            setup <i class="mdi mdi-magnify ms-1"></i></a>
                                        <a href="index.html" class="btn btn-soft-secondary btn-sm btn-rounded">buttons
                                            <i class="mdi mdi-magnify ms-1"></i></a>
                                    </div>
                                    <!-- item-->
                                    <div class="dropdown-header mt-2">
                                        <h6 class="text-overflow text-muted mb-1 text-uppercase">Pages</h6>
                                    </div>

                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                                        <i class="ri-bubble-chart-line align-middle fs-18 text-muted me-2"></i>
                                        <span>Analytics Dashboard</span>
                                    </a>

                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                                        <i class="ri-lifebuoy-line align-middle fs-18 text-muted me-2"></i>
                                        <span>Help Center</span>
                                    </a>

                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                                        <i class="ri-user-settings-line align-middle fs-18 text-muted me-2"></i>
                                        <span>My account settings</span>
                                    </a>

                                    <!-- item-->
                                    <div class="dropdown-header mt-2">
                                        <h6 class="text-overflow text-muted mb-2 text-uppercase">Members</h6>
                                    </div>

                                    <div class="notification-list">
                                        <!-- item -->
                                        <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                            <div class="d-flex">
                                                <img src="images/users/avatar-2.jpg"
                                                    class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                <div class="flex-1">
                                                    <h6 class="m-0">Angela Bernier</h6>
                                                    <span class="fs-11 mb-0 text-muted">Manager</span>
                                                </div>
                                            </div>
                                        </a>
                                        <!-- item -->
                                        <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                            <div class="d-flex">
                                                <img src="images/users/avatar-3.jpg"
                                                    class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                <div class="flex-1">
                                                    <h6 class="m-0">David Grasso</h6>
                                                    <span class="fs-11 mb-0 text-muted">Web Designer</span>
                                                </div>
                                            </div>
                                        </a>
                                        <!-- item -->
                                        <a href="javascript:void(0);" class="dropdown-item notify-item py-2">
                                            <div class="d-flex">
                                                <img src="images/users/avatar-5.jpg"
                                                    class="me-3 rounded-circle avatar-xs" alt="user-pic">
                                                <div class="flex-1">
                                                    <h6 class="m-0">Mike Bunch</h6>
                                                    <span class="fs-11 mb-0 text-muted">React Developer</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <div class="text-center pt-3 pb-1">
                                    <a href="pages-search-results.html" class="btn btn-primary btn-sm">View All
                                        Results <i class="ri-arrow-right-line ms-1"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="d-flex align-items-center">

                        <div class="dropdown d-none topbar-head-dropdown header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                                id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <i class="bx bx-search fs-22"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                                aria-labelledby="page-header-search-dropdown">
                                <form class="p-3">
                                    <div class="form-group m-0">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search ..."
                                                aria-label="Recipient's username">
                                            <button class="btn btn-primary" type="submit"><i
                                                    class="mdi mdi-magnify"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="dropdown d-none ms-1 topbar-head-dropdown header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <img id="header-lang-img" src="images/flags/us.svg" alt="Header Language"
                                    height="20" class="rounded">
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language py-2"
                                    data-lang="en" title="English">
                                    <img src="images/flags/us.svg" alt="user-image" class="me-2 rounded"
                                        height="18">
                                    <span class="align-middle">English</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language"
                                    data-lang="sp" title="Spanish">
                                    <img src="images/flags/spain.svg" alt="user-image" class="me-2 rounded"
                                        height="18">
                                    <span class="align-middle">Espanol</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language"
                                    data-lang="gr" title="German">
                                    <img src="images/flags/germany.svg" alt="user-image" class="me-2 rounded"
                                        height="18"> <span class="align-middle">Deutsche</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language"
                                    data-lang="it" title="Italian">
                                    <img src="images/flags/italy.svg" alt="user-image" class="me-2 rounded"
                                        height="18">
                                    <span class="align-middle">Italiana</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language"
                                    data-lang="ru" title="Russian">
                                    <img src="images/flags/russia.svg" alt="user-image" class="me-2 rounded"
                                        height="18">
                                    <span class="align-middle">Russian</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language"
                                    data-lang="ch" title="Chinese">
                                    <img src="images/flags/china.svg" alt="user-image" class="me-2 rounded"
                                        height="18">
                                    <span class="align-middle">Chinese</span>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item language"
                                    data-lang="fr" title="French">
                                    <img src="images/flags/french.svg" alt="user-image" class="me-2 rounded"
                                        height="18">
                                    <span class="align-middle">French</span>
                                </a>
                            </div>
                        </div>

                        <div class="dropdown d-none topbar-head-dropdown ms-1 header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class='bx bx-category-alt fs-22'></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg p-0 dropdown-menu-end">
                                <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0 fw-semibold fs-15"> Web Apps </h6>
                                        </div>
                                        <div class="col-auto">
                                            <a href="#!" class="btn btn-sm btn-soft-info"> View All Apps
                                                <i class="ri-arrow-right-s-line align-middle"></i></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-2">
                                    <div class="row g-0">
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="images/brands/github.png" alt="Github">
                                                <span>GitHub</span>
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="images/brands/bitbucket.png" alt="bitbucket">
                                                <span>Bitbucket</span>
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="images/brands/dribbble.png" alt="dribbble">
                                                <span>Dribbble</span>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="row g-0">
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="images/brands/dropbox.png" alt="dropbox">
                                                <span>Dropbox</span>
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="images/brands/mail_chimp.png" alt="mail_chimp">
                                                <span>Mail Chimp</span>
                                            </a>
                                        </div>
                                        <div class="col">
                                            <a class="dropdown-icon-item" href="#!">
                                                <img src="images/brands/slack.png" alt="slack">
                                                <span>Slack</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown d-none topbar-head-dropdown ms-1 header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                                id="page-header-cart-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside"
                                aria-haspopup="true" aria-expanded="false">
                                <i class='bx bx-shopping-bag fs-22'></i>
                                <span
                                    class="position-absolute topbar-badge cartitem-badge fs-10 translate-middle badge rounded-pill bg-info">5</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-xl dropdown-menu-end p-0 dropdown-menu-cart"
                                aria-labelledby="page-header-cart-dropdown">
                                <div class="p-3 border-top-0 border-start-0 border-end-0 border-dashed border">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h6 class="m-0 fs-16 fw-semibold"> My Cart</h6>
                                        </div>
                                        <div class="col-auto">
                                            <span class="badge badge-soft-warning fs-13"><span
                                                    class="cartitem-badge">7</span>
                                                items</span>
                                        </div>
                                    </div>
                                </div>
                                <div data-simplebar style="max-height: 300px;">
                                    <div class="p-2">
                                        <div class="text-center empty-cart" id="empty-cart">
                                            <div class="avatar-md mx-auto my-3">
                                                <div class="avatar-title bg-soft-info text-info fs-36 rounded-circle">
                                                    <i class='bx bx-cart'></i>
                                                </div>
                                            </div>
                                            <h5 class="mb-3">Your Cart is Empty!</h5>
                                            <a href="apps-ecommerce-products.html"
                                                class="btn btn-success w-md mb-3">Shop Now</a>
                                        </div>
                                        <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2">
                                            <div class="d-flex align-items-center">
                                                <img src="images/products/img-1.png"
                                                    class="me-3 rounded-circle avatar-sm p-2 bg-light" alt="user-pic">
                                                <div class="flex-1">
                                                    <h6 class="mt-0 mb-1 fs-14">
                                                        <a href="apps-ecommerce-product-details.html"
                                                            class="text-reset">Branded
                                                            T-Shirts</a>
                                                    </h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        Quantity: <span>10 x $32</span>
                                                    </p>
                                                </div>
                                                <div class="px-2">
                                                    <h5 class="m-0 fw-normal">$<span
                                                            class="cart-item-price">320</span></h5>
                                                </div>
                                                <div class="ps-2">
                                                    <button type="button"
                                                        class="btn btn-icon btn-sm btn-ghost-secondary remove-item-btn"><i
                                                            class="ri-close-fill fs-16"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2">
                                            <div class="d-flex align-items-center">
                                                <img src="images/products/img-2.png"
                                                    class="me-3 rounded-circle avatar-sm p-2 bg-light" alt="user-pic">
                                                <div class="flex-1">
                                                    <h6 class="mt-0 mb-1 fs-14">
                                                        <a href="apps-ecommerce-product-details.html"
                                                            class="text-reset">Bentwood Chair</a>
                                                    </h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        Quantity: <span>5 x $18</span>
                                                    </p>
                                                </div>
                                                <div class="px-2">
                                                    <h5 class="m-0 fw-normal">$<span class="cart-item-price">89</span>
                                                    </h5>
                                                </div>
                                                <div class="ps-2">
                                                    <button type="button"
                                                        class="btn btn-icon btn-sm btn-ghost-secondary remove-item-btn"><i
                                                            class="ri-close-fill fs-16"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2">
                                            <div class="d-flex align-items-center">
                                                <img src="images/products/img-3.png"
                                                    class="me-3 rounded-circle avatar-sm p-2 bg-light" alt="user-pic">
                                                <div class="flex-1">
                                                    <h6 class="mt-0 mb-1 fs-14">
                                                        <a href="apps-ecommerce-product-details.html"
                                                            class="text-reset">
                                                            Borosil Paper Cup</a>
                                                    </h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        Quantity: <span>3 x $250</span>
                                                    </p>
                                                </div>
                                                <div class="px-2">
                                                    <h5 class="m-0 fw-normal">$<span
                                                            class="cart-item-price">750</span></h5>
                                                </div>
                                                <div class="ps-2">
                                                    <button type="button"
                                                        class="btn btn-icon btn-sm btn-ghost-secondary remove-item-btn"><i
                                                            class="ri-close-fill fs-16"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2">
                                            <div class="d-flex align-items-center">
                                                <img src="images/products/img-6.png"
                                                    class="me-3 rounded-circle avatar-sm p-2 bg-light" alt="user-pic">
                                                <div class="flex-1">
                                                    <h6 class="mt-0 mb-1 fs-14">
                                                        <a href="apps-ecommerce-product-details.html"
                                                            class="text-reset">Gray
                                                            Styled T-Shirt</a>
                                                    </h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        Quantity: <span>1 x $1250</span>
                                                    </p>
                                                </div>
                                                <div class="px-2">
                                                    <h5 class="m-0 fw-normal">$ <span
                                                            class="cart-item-price">1250</span></h5>
                                                </div>
                                                <div class="ps-2">
                                                    <button type="button"
                                                        class="btn btn-icon btn-sm btn-ghost-secondary remove-item-btn"><i
                                                            class="ri-close-fill fs-16"></i></button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-block dropdown-item dropdown-item-cart text-wrap px-3 py-2">
                                            <div class="d-flex align-items-center">
                                                <img src="images/products/img-5.png"
                                                    class="me-3 rounded-circle avatar-sm p-2 bg-light" alt="user-pic">
                                                <div class="flex-1">
                                                    <h6 class="mt-0 mb-1 fs-14">
                                                        <a href="apps-ecommerce-product-details.html"
                                                            class="text-reset">Stillbird Helmet</a>
                                                    </h6>
                                                    <p class="mb-0 fs-12 text-muted">
                                                        Quantity: <span>2 x $495</span>
                                                    </p>
                                                </div>
                                                <div class="px-2">
                                                    <h5 class="m-0 fw-normal">$<span
                                                            class="cart-item-price">990</span></h5>
                                                </div>
                                                <div class="ps-2">
                                                    <button type="button"
                                                        class="btn btn-icon btn-sm btn-ghost-secondary remove-item-btn"><i
                                                            class="ri-close-fill fs-16"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 border-bottom-0 border-start-0 border-end-0 border-dashed border"
                                    id="checkout-elem">
                                    <div class="d-flex justify-content-between align-items-center pb-3">
                                        <h5 class="m-0 text-muted">Total:</h5>
                                        <div class="px-2">
                                            <h5 class="m-0" id="cart-item-total">$1258.58</h5>
                                        </div>
                                    </div>

                                    <a href="apps-ecommerce-checkout.html" class="btn btn-success text-center w-100">
                                        Checkout
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="ms-1 header-item d-none d-sm-flex">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                                data-toggle="fullscreen">
                                <i class='bx bx-fullscreen fs-22'></i>
                            </button>
                        </div>

                        <div class="ms-1 header-item d-none d-sm-flex">
                            <button type="button"
                                class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle light-dark-mode">
                                <i class='bx bx-moon fs-22'></i>
                            </button>
                        </div>

                        <div class="dropdown topbar-head-dropdown ms-1 header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-secondary rounded-circle"
                                id="page-header-notifications-dropdown" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <i class='bx bx-bell fs-22'></i>
                                <span id="topbar-task-notif-count"
                                    class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger d-none">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                                aria-labelledby="page-header-notifications-dropdown">
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 fs-16 fw-semibold">Notificaciones</h6>
                                    <button class="btn btn-sm btn-soft-primary" id="btn-marcar-todas-leidas" type="button">
                                        Marcar todas
                                    </button>
                                </div>
                                <div id="topbar-task-notif-list" class="topbar-task-notif-list"></div>
                            </div>
                        </div>

                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn" id="page-header-user-dropdown"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    <span class="rounded-circle header-profile-user bg-primary text-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:14px;">
                                        {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'U' }}
                                    </span>
                                    <span class="text-start ms-xl-2">
                                        <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">
                                            {{ auth()->check() ? auth()->user()->name : 'Usuario' }}
                                        </span>
                                        <span class="d-none d-xl-block ms-1 fs-12 text-muted user-name-sub-text">
                                            {{ auth()->check() ? auth()->user()->email : '' }}
                                        </span>
                                    </span>
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header">Bienvenido{{ auth()->check() ? ' ' . auth()->user()->name : '' }}!</h6>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                                        <span class="align-middle">Cerrar Sesion</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <!-- Dark Logo-->
                <a href="index.html" class="logo logo-dark">
                    <span class="logo-sm">
                        {{-- <img src="images/logo-sm.png" alt="" height="22"> --}}
                    </span>
                    <span class="logo-lg">
                        {{-- <img src="images/logo-dark.png" alt="" height="17"> --}}
                    </span>
                </a>
                <!-- Light Logo-->
                <a href="index.html" class="logo logo-light">
                    <span class="logo-sm">
                        {{-- <img src="images/logo-sm.png" alt="" height="22"> --}}
                    </span>
                    <span class="logo-lg">
                        {{-- <img src="images/logo-light.png" alt="" height="17"> --}}
                    </span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
                    id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>

            <div id="scrollbar">
                <div class="container-fluid">
                    <div id="two-column-menu"></div>
                    @php
                        $showAllModulesForAdmin = auth()->check() && auth()->user()->hasAnyRole(['superadmin', 'admin']);
                        $canModule = fn (string $module): bool => $showAllModulesForAdmin || (bool) auth()->user()?->can("module.{$module}.view");
                    @endphp
                    <ul class="navbar-nav" id="navbar-nav">
                        <li class="sidebar-brand-title">
                            <a href="{{ url('/') }}" class="brand-mark text-decoration-none">
                                <i class="ri-shield-user-line"></i>
                                <span>BSH Support</span>
                            </a>
                        </li>

                        <li class="menu-title">
                            <a href="{{ url('/') }}" class="text-reset text-decoration-none">
                                <span data-key="t-menu">Inicio</span>
                            </a>
                        </li>

                        @if ($canModule('dashboard'))
                            <li class="nav-item">
                                <a href="{{ route('dashboard.index') }}"
                                    class="nav-link menu-link {{ request()->routeIs('inicio.index') || request()->is('dashboard*') || request()->is('ventas-lotobet-dashboard*') || request()->is('ventas-lotonet-dashboard*') || request()->is('ventas-lotobet-flash-dashboard*') || request()->is('ventas-mar-dashboard*') || request()->is('kpi-lotobet*') ? 'active' : '' }}">
                                    <i class="ri-apps-2-line"></i> <span data-key="t-apps">Dashboard</span>
                                </a>
                            </li>
                        @endif

                        @if ($canModule('procesos'))
                            <li class="nav-item">
                                <a href="{{ route('procesos.index') }}"
                                    class="nav-link menu-link {{ request()->is('procesos*') ? 'active' : '' }}">
                                    <i class="ri-flow-chart"></i> <span data-key="t-procesos">Procesos</span>
                                </a>
                            </li>
                        @endif
                        @if ($canModule('recursos_humanos'))
                            <li class="nav-item">
                                <a href="{{ route('recursos-humanos.index') }}"
                                    class="nav-link menu-link {{ request()->is('recursos-humanos*') || request()->is('empleados*') || request()->is('registro-empleados*') || request()->is('entrevistas-online*') || request()->is('ventas-sin-empleado*') ? 'active' : '' }}">
                                    <i class="ri-team-line"></i> <span data-key="t-recursos-humanos">Recursos Humanos</span>
                                </a>
                            </li>
                        @endif
                        @if ($canModule('contabilidad'))
                            <li class="nav-item">
                                <a href="{{ route('contabilidad.index') }}"
                                    class="nav-link menu-link {{ request()->is('contabilidad*') ? 'active' : '' }}">
                                    <i class="ri-dashboard-2-line"></i> <span data-key="t-contabilidad">Contabilidad</span>
                                </a>
                            </li>
                        @endif
                        @if ($canModule('tareas'))
                            <li class="nav-item">
                                <a href="{{ route('tareas.index') }}"
                                    class="nav-link menu-link {{ request()->is('tareas') || request()->is('tareas-list*') || (request()->is('tareas/*') && !request()->routeIs('tareas.proyecto')) ? 'active' : '' }}">
                                    <i class="ri-task-line"></i> <span data-key="t-tareas">Tareas</span>
                                </a>
                            </li>
                        @endif
                        @if ($canModule('ticket') || auth()->user()?->can('tickets.view'))
                            <li class="nav-item">
                                <a href="{{ route('tickets.index') }}"
                                    class="nav-link menu-link {{ request()->is('tickets*') ? 'active' : '' }}">
                                    <i class="ri-ticket-2-line"></i> <span data-key="t-tickets">Ticket</span>
                                </a>
                            </li>
                        @endif
                        @if ($canModule('proyecto'))
                            <li class="nav-item">
                                <a href="{{ route('tareas.proyecto') }}"
                                    class="nav-link menu-link {{ request()->routeIs('tareas.proyecto') ? 'active' : '' }}">
                                    <i class="ri-stack-line"></i> <span data-key="t-proyecto">Proyecto</span>
                                </a>
                            </li>
                        @endif
                        @if ($showAllModulesForAdmin || auth()->user()?->can('servicios_generales.view'))
                            <li class="nav-item">
                                <a href="{{ route('servicios-generales.index') }}"
                                    class="nav-link menu-link {{ request()->is('servicios-generales*') ? 'active' : '' }}">
                                    <i class="ri-tools-line"></i> <span data-key="t-servicios-generales">Servicios Generales</span>
                                </a>
                            </li>
                        @endif
                        @if ($canModule('tecnologia'))
                            <li class="nav-item">
                                <a href="{{ route('tecnologia.index') }}"
                                    class="nav-link menu-link {{ request()->is('tecnologia*') ? 'active' : '' }}">
                                    <i class="ri-computer-line"></i> <span data-key="t-tecnologia">Tecnologia</span>
                                </a>
                            </li>
                        @endif
                        @if ($canModule('mantenimiento'))
                            <li class="nav-item">
                                <a href="{{ route('mantenimiento.index') }}"
                                    class="nav-link menu-link {{ request()->is('mantenimiento*') || request()->is('agencias*') || request()->is('usuarios*') || request()->is('coordinador-operador*') || request()->is('roles*') || request()->is('permissions*') ? 'active' : '' }}">
                                    <i class="ri-settings-2-line"></i> <span data-key="t-apps">Mantenimientos</span>
                                </a>
                            </li>
                        @endif

                        @if ($canModule('dashboard'))
                            <li class="nav-item">
                            <a class="nav-link menu-link collapsed" href="#sidebarApps" data-bs-toggle="collapse"
                                role="button" aria-expanded="true" aria-controls="sidebarApps">
                                <i class="ri-apps-2-line"></i> <span data-key="t-apps">Apis de ventas</span>
                            </a>
                            <div class="collapse menu-dropdown" id="sidebarApps">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ url('/generar-lotobet') }}" class="nav-link">
                                            <span data-key="t-dashboards">Generar Lotobet</span>
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ url('/generar-lotonet') }}" class="nav-link">
                                            <span data-key="t-dashboards">Generar Lotonet</span>
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a href="#sidebarEmail" class="nav-link collapsed" data-bs-toggle="collapse"
                                            role="button" aria-expanded="false" aria-controls="sidebarEmail"
                                            data-key="t-email">
                                            Lotobet
                                        </a>
                                        <div class="collapse menu-dropdown" id="sidebarEmail">
                                            <ul class="nav nav-sm flex-column">
                                                <li class="nav-item">
                                                    <a href="{{ url('/ventas-por-usuario-lotobet') }}"
                                                        class="nav-link" data-key="t-mailbox"> Ventas por usuario </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/faltantes-lotobet') }}" class="nav-link"
                                                        data-key="t-mailbox"> Faltantes </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/ventas-por-producto-lotobet') }}"
                                                        class="nav-link" data-key="t-mailbox"> Ventas por producto
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/recargas-lotobet') }}" class="nav-link"
                                                        data-key="t-mailbox"> Recargas </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/premios-lotobet') }}" class="nav-link"
                                                        data-key="t-mailbox"> Premios </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/pagos-misma-empresa-lotobet') }}"
                                                        class="nav-link" data-key="t-mailbox"> Pagos Misma Empresa
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/pagos-aotra-empresa-lotobet') }}"
                                                        class="nav-link" data-key="t-mailbox"> Pagos A Otra Empresa
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/pagos-porotra-empresa-lotobet') }}"
                                                        class="nav-link" data-key="t-mailbox"> Pagos Por Otra Empresa
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/asistencias-lotobet') }}" class="nav-link"
                                                        data-key="t-mailbox"> Asistencias
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#sidebarInvoices" class="nav-link collapsed"
                                            data-bs-toggle="collapse" role="button" aria-expanded="false"
                                            aria-controls="sidebarInvoices" data-key="t-invoices">
                                            Lotonet
                                        </a>
                                        <div class="collapse menu-dropdown" id="sidebarInvoices">
                                            <ul class="nav nav-sm flex-column">
                                                <li class="nav-item">
                                                    <a href="{{ url('/ventas-por-usuario-lotonet') }}"
                                                        class="nav-link" data-key="t-mailbox"> Ventas por usuario </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/faltantes-lotonet') }}" class="nav-link"
                                                        data-key="t-mailbox"> Faltantes </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/paquetico-lotonet') }}" class="nav-link"
                                                        data-key="t-mailbox"> Paquetico </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/recargas-lotonet') }}" class="nav-link"
                                                        data-key="t-mailbox"> Recargas </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/ventas-por-producto-lotonet') }}"
                                                        class="nav-link" data-key="t-mailbox"> Ventas Por Producto
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/premios-lotonet') }}" class="nav-link"
                                                        data-key="t-mailbox"> Premios </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/pagos-misma-empresa-lotonet') }}"
                                                        class="nav-link" data-key="t-mailbox"> Pagos Misma Empresa
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/pagos-aotra-empresa-lotonet') }}"
                                                        class="nav-link" data-key="t-mailbox"> Pagos A Otra Empresa
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/pagos-porotra-empresa-lotonet') }}"
                                                        class="nav-link" data-key="t-mailbox"> Pagos Por Otra Empresa
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ url('/asistencias-lotonet') }}" class="nav-link"
                                                        data-key="t-mailbox"> Asistencias
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#sidebarMar" class="nav-link collapsed" data-bs-toggle="collapse"
                                            role="button" aria-expanded="false" aria-controls="sidebarMar"
                                            data-key="t-invoices">
                                            Mar
                                        </a>
                                        <div class="collapse menu-dropdown" id="sidebarMar">
                                            <ul class="nav nav-sm flex-column">
                                                <li class="nav-item">
                                                    <a href="{{ url('/mar-ventas') }}" class="nav-link"
                                                        data-key="t-mailbox"> Ventas </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>

                                    <li class="nav-item">
                                        <a href="{{ url('/ventas-flash-lotobet') }}" class="nav-link">
                                            <span data-key="t-dashboards">Ventas Flash Lotobet</span>
                                        </a>
                                    </li>
                                    <li class="nav-item d-none">
                                        <a href="{{ url('/ventas-flash-lotonet') }}" class="nav-link">
                                            <span data-key="t-dashboards">Ventas Flash Lotonet</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            </li>
                        @endif

                        @if ($canModule('reportes'))
                            <li class="nav-item">
                                <a href="{{ route('reportes.index') }}"
                                    class="nav-link menu-link {{ request()->is('reportes') || request()->is('reportes-*') ? 'active' : '' }}">
                                    <i class="ri-apps-2-line"></i> <span data-key="t-apps">Reportes</span>
                                </a>
                            </li>
                        @endif
                        @if ($canModule('incentivos'))
                            <li class="nav-item">
                                <a href="{{ route('incentivos.index') }}"
                                    class="nav-link menu-link {{ request()->is('incentivos*') ? 'active' : '' }}">
                                    <i class="ri-award-line"></i> <span data-key="t-apps">Incentivos</span>
                                </a>
                            </li>
                        @endif
                        @if ($canModule('operaciones'))
                            <li class="nav-item">
                                <a href="{{ route('operaciones.index') }}"
                                    class="nav-link menu-link {{ request()->is('operaciones*') ? 'active' : '' }}">
                                    <i class="ri-settings-3-line"></i> <span data-key="t-apps">Operaciones</span>
                                </a>
                            </li>
                        @endif
                        @if ($canModule('comercial'))
                            <li class="nav-item">
                                <a href="{{ route('comercial.index') }}"
                                    class="nav-link menu-link {{ request()->is('comercial*') ? 'active' : '' }}">
                                    <i class="ri-line-chart-line"></i> <span data-key="t-apps">Comercial</span>
                                </a>
                            </li>
                        @endif

                        @if ($canModule('gerencia'))
                            <li class="nav-item">
                                <a href="{{ route('gerencia.index') }}"
                                    class="nav-link menu-link {{ request()->is('gerencia*') ? 'active' : '' }}">
                                    <i class="ri-briefcase-line"></i> <span data-key="t-gerencia">Gerencia</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
                <!-- Sidebar -->
            </div>

            <div class="sidebar-background"></div>
        </div>
        <!-- Left Sidebar End -->
        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        @yield('content')

        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> &copy; ERP.
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">
                            Version {{ $appVersion }}
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    <!-- END layout-wrapper -->

    <!--start back-to-top-->
    <button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
        <i class="ri-arrow-up-line"></i>
    </button>
    <!--end back-to-top-->

    <div class="customizer-setting d-none">
        <div class="btn-info btn-rounded shadow-lg btn btn-icon btn-lg p-2" data-bs-toggle="offcanvas"
            data-bs-target="#theme-settings-offcanvas-disabled" aria-controls="theme-settings-offcanvas-disabled">
            <i class='mdi mdi-spin mdi-cog-outline fs-22'></i>
        </div>
    </div>

    <!-- Theme Settings -->
    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-settings-offcanvas">
        <div class="d-flex align-items-center bg-primary bg-gradient p-3 offcanvas-header">
            <h5 class="m-0 me-2 text-white">Theme Customizer</h5>

            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas"
                aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div data-simplebar class="h-100">
                <div class="p-4">
                    <h6 class="mb-0 fw-semibold text-uppercase">Layout</h6>
                    <p class="text-muted">Choose your layout</p>

                    <div class="row">
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input id="customizer-layout01" name="data-layout" type="radio"
                                    value="vertical" class="form-check-input">
                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout01">
                                    <span class="d-flex gap-1 h-100">
                                        <span class="flex-shrink-0">
                                            <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                            </span>
                                        </span>
                                        <span class="flex-grow-1">
                                            <span class="d-flex h-100 flex-column">
                                                <span class="bg-light d-block p-1"></span>
                                                <span class="bg-light d-block p-1 mt-auto"></span>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                            <h5 class="fs-13 text-center mt-2">Vertical</h5>
                        </div>
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input id="customizer-layout02" name="data-layout" type="radio"
                                    value="horizontal" class="form-check-input">
                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout02">
                                    <span class="d-flex h-100 flex-column gap-1">
                                        <span class="bg-light d-flex p-1 gap-1 align-items-center">
                                            <span class="d-block p-1 bg-soft-primary rounded me-1"></span>
                                            <span class="d-block p-1 pb-0 px-2 bg-soft-primary ms-auto"></span>
                                            <span class="d-block p-1 pb-0 px-2 bg-soft-primary"></span>
                                        </span>
                                        <span class="bg-light d-block p-1"></span>
                                        <span class="bg-light d-block p-1 mt-auto"></span>
                                    </span>
                                </label>
                            </div>
                            <h5 class="fs-13 text-center mt-2">Horizontal</h5>
                        </div>
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input id="customizer-layout03" name="data-layout" type="radio"
                                    value="twocolumn" class="form-check-input">
                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout03">
                                    <span class="d-flex gap-1 h-100">
                                        <span class="flex-shrink-0">
                                            <span class="bg-light d-flex h-100 flex-column gap-1">
                                                <span class="d-block p-1 bg-soft-primary mb-2"></span>
                                                <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                            </span>
                                        </span>
                                        <span class="flex-shrink-0">
                                            <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                            </span>
                                        </span>
                                        <span class="flex-grow-1">
                                            <span class="d-flex h-100 flex-column">
                                                <span class="bg-light d-block p-1"></span>
                                                <span class="bg-light d-block p-1 mt-auto"></span>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                            <h5 class="fs-13 text-center mt-2">Two Column</h5>
                        </div>
                        <!-- end col -->
                    </div>

                    <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Color Scheme</h6>
                    <p class="text-muted">Choose Light or Dark Scheme.</p>

                    <div class="colorscheme-cardradio">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-check card-radio">
                                    <input class="form-check-input" type="radio" name="data-layout-mode"
                                        id="layout-mode-light" value="light">
                                    <label class="form-check-label p-0 avatar-md w-100" for="layout-mode-light">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                    <span
                                                        class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Light</h5>
                            </div>

                            <div class="col-4">
                                <div class="form-check card-radio dark">
                                    <input class="form-check-input" type="radio" name="data-layout-mode"
                                        id="layout-mode-dark" value="dark">
                                    <label class="form-check-label p-0 avatar-md w-100 bg-dark"
                                        for="layout-mode-dark">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-soft-light d-flex h-100 flex-column gap-1 p-1">
                                                    <span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-soft-light d-block p-1"></span>
                                                    <span class="bg-soft-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Dark</h5>
                            </div>
                        </div>
                    </div>

                    <div id="layout-width">
                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Layout Width</h6>
                        <p class="text-muted">Choose Fluid or Boxed layout.</p>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-check card-radio">
                                    <input class="form-check-input" type="radio" name="data-layout-width"
                                        id="layout-width-fluid" value="fluid">
                                    <label class="form-check-label p-0 avatar-md w-100" for="layout-width-fluid">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                    <span
                                                        class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Fluid</h5>
                            </div>
                            <div class="col-4">
                                <div class="form-check card-radio">
                                    <input class="form-check-input" type="radio" name="data-layout-width"
                                        id="layout-width-boxed" value="boxed">
                                    <label class="form-check-label p-0 avatar-md w-100 px-2"
                                        for="layout-width-boxed">
                                        <span class="d-flex gap-1 h-100 border-start border-end">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                    <span
                                                        class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Boxed</h5>
                            </div>
                        </div>
                    </div>

                    <div id="layout-position">
                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Layout Position</h6>
                        <p class="text-muted">Choose Fixed or Scrollable Layout Position.</p>

                        <div class="btn-group radio" role="group">
                            <input type="radio" class="btn-check" name="data-layout-position"
                                id="layout-position-fixed" value="fixed">
                            <label class="btn btn-light w-sm" for="layout-position-fixed">Fixed</label>

                            <input type="radio" class="btn-check" name="data-layout-position"
                                id="layout-position-scrollable" value="scrollable">
                            <label class="btn btn-light w-sm ms-0"
                                for="layout-position-scrollable">Scrollable</label>
                        </div>
                    </div>
                    <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Topbar Color</h6>
                    <p class="text-muted">Choose Light or Dark Topbar Color.</p>

                    <div class="row">
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input class="form-check-input" type="radio" name="data-topbar"
                                    id="topbar-color-light" value="light">
                                <label class="form-check-label p-0 avatar-md w-100" for="topbar-color-light">
                                    <span class="d-flex gap-1 h-100">
                                        <span class="flex-shrink-0">
                                            <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                            </span>
                                        </span>
                                        <span class="flex-grow-1">
                                            <span class="d-flex h-100 flex-column">
                                                <span class="bg-light d-block p-1"></span>
                                                <span class="bg-light d-block p-1 mt-auto"></span>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                            <h5 class="fs-13 text-center mt-2">Light</h5>
                        </div>
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input class="form-check-input" type="radio" name="data-topbar"
                                    id="topbar-color-dark" value="dark">
                                <label class="form-check-label p-0 avatar-md w-100" for="topbar-color-dark">
                                    <span class="d-flex gap-1 h-100">
                                        <span class="flex-shrink-0">
                                            <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                <span class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                            </span>
                                        </span>
                                        <span class="flex-grow-1">
                                            <span class="d-flex h-100 flex-column">
                                                <span class="bg-primary d-block p-1"></span>
                                                <span class="bg-light d-block p-1 mt-auto"></span>
                                            </span>
                                        </span>
                                    </span>
                                </label>
                            </div>
                            <h5 class="fs-13 text-center mt-2">Dark</h5>
                        </div>
                    </div>

                    <div id="sidebar-size">
                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Sidebar Size</h6>
                        <p class="text-muted">Choose a size of Sidebar.</p>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar-size"
                                        id="sidebar-size-default" value="lg">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-default">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                    <span
                                                        class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Default</h5>
                            </div>

                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar-size"
                                        id="sidebar-size-compact" value="md">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-compact">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                    <span class="d-block p-1 bg-soft-primary rounded mb-2"></span>
                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Compact</h5>
                            </div>

                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar-size"
                                        id="sidebar-size-small" value="sm">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-size-small">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 flex-column gap-1">
                                                    <span class="d-block p-1 bg-soft-primary mb-2"></span>
                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Small (Icon View)</h5>
                            </div>

                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar-size"
                                        id="sidebar-size-small-hover" value="sm-hover">
                                    <label class="form-check-label p-0 avatar-md w-100"
                                        for="sidebar-size-small-hover">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 flex-column gap-1">
                                                    <span class="d-block p-1 bg-soft-primary mb-2"></span>
                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 pb-0 bg-soft-primary"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Small Hover View</h5>
                            </div>
                        </div>
                    </div>

                    <div id="sidebar-view">
                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Sidebar View</h6>
                        <p class="text-muted">Choose Default or Detached Sidebar view.</p>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-layout-style"
                                        id="sidebar-view-default" value="default">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-view-default">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                    <span
                                                        class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Default</h5>
                            </div>
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-layout-style"
                                        id="sidebar-view-detached" value="detached">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-view-detached">
                                        <span class="d-flex h-100 flex-column">
                                            <span class="bg-light d-flex p-1 gap-1 align-items-center px-2">
                                                <span class="d-block p-1 bg-soft-primary rounded me-1"></span>
                                                <span class="d-block p-1 pb-0 px-2 bg-soft-primary ms-auto"></span>
                                                <span class="d-block p-1 pb-0 px-2 bg-soft-primary"></span>
                                            </span>
                                            <span class="d-flex gap-1 h-100 p-1 px-2">
                                                <span class="flex-shrink-0">
                                                    <span class="bg-light d-flex h-100 flex-column gap-1 p-1">
                                                        <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                        <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                        <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    </span>
                                                </span>
                                            </span>
                                            <span class="bg-light d-block p-1 mt-auto px-2"></span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Detached</h5>
                            </div>
                        </div>
                    </div>
                    <div id="sidebar-color">
                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Sidebar Color</h6>
                        <p class="text-muted">Choose a color of Sidebar.</p>

                        <div class="row">
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio" data-bs-toggle="collapse"
                                    data-bs-target="#collapseBgGradient.show">
                                    <input class="form-check-input" type="radio" name="data-sidebar"
                                        id="sidebar-color-light" value="light">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-light">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-white border-end d-flex h-100 flex-column gap-1 p-1">
                                                    <span
                                                        class="d-block p-1 px-2 bg-soft-primary rounded mb-2"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-primary"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Light</h5>
                            </div>
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio" data-bs-toggle="collapse"
                                    data-bs-target="#collapseBgGradient.show">
                                    <input class="form-check-input" type="radio" name="data-sidebar"
                                        id="sidebar-color-dark" value="dark">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-dark">
                                        <span class="d-flex gap-1 h-100">
                                            <span class="flex-shrink-0">
                                                <span class="bg-primary d-flex h-100 flex-column gap-1 p-1">
                                                    <span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                    <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                </span>
                                            </span>
                                            <span class="flex-grow-1">
                                                <span class="d-flex h-100 flex-column">
                                                    <span class="bg-light d-block p-1"></span>
                                                    <span class="bg-light d-block p-1 mt-auto"></span>
                                                </span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                                <h5 class="fs-13 text-center mt-2">Dark</h5>
                            </div>
                            <div class="col-4">
                                <button class="btn btn-link avatar-md w-100 p-0 overflow-hidden border collapsed"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#collapseBgGradient"
                                    aria-expanded="false" aria-controls="collapseBgGradient">
                                    <span class="d-flex gap-1 h-100">
                                        <span class="flex-shrink-0">
                                            <span class="bg-vertical-gradient d-flex h-100 flex-column gap-1 p-1">
                                                <span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                                <span class="d-block p-1 px-2 pb-0 bg-soft-light"></span>
                                            </span>
                                        </span>
                                        <span class="flex-grow-1">
                                            <span class="d-flex h-100 flex-column">
                                                <span class="bg-light d-block p-1"></span>
                                                <span class="bg-light d-block p-1 mt-auto"></span>
                                            </span>
                                        </span>
                                    </span>
                                </button>
                                <h5 class="fs-13 text-center mt-2">Gradient</h5>
                            </div>
                        </div>
                        <!-- end row -->

                        <div class="collapse" id="collapseBgGradient">
                            <div class="d-flex gap-2 flex-wrap img-switch p-2 px-3 bg-light rounded">

                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar"
                                        id="sidebar-color-gradient" value="gradient">
                                    <label class="form-check-label p-0 avatar-xs rounded-circle"
                                        for="sidebar-color-gradient">
                                        <span class="avatar-title rounded-circle bg-vertical-gradient"></span>
                                    </label>
                                </div>
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar"
                                        id="sidebar-color-gradient-2" value="gradient-2">
                                    <label class="form-check-label p-0 avatar-xs rounded-circle"
                                        for="sidebar-color-gradient-2">
                                        <span class="avatar-title rounded-circle bg-vertical-gradient-2"></span>
                                    </label>
                                </div>
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar"
                                        id="sidebar-color-gradient-3" value="gradient-3">
                                    <label class="form-check-label p-0 avatar-xs rounded-circle"
                                        for="sidebar-color-gradient-3">
                                        <span class="avatar-title rounded-circle bg-vertical-gradient-3"></span>
                                    </label>
                                </div>
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar"
                                        id="sidebar-color-gradient-4" value="gradient-4">
                                    <label class="form-check-label p-0 avatar-xs rounded-circle"
                                        for="sidebar-color-gradient-4">
                                        <span class="avatar-title rounded-circle bg-vertical-gradient-4"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="sidebar-img">
                        <h6 class="mt-4 mb-0 fw-semibold text-uppercase">Sidebar Images</h6>
                        <p class="text-muted">Choose a image of Sidebar.</p>

                        <div class="d-flex gap-2 flex-wrap img-switch">
                            <div class="form-check sidebar-setting card-radio">
                                <input class="form-check-input" type="radio" name="data-sidebar-image"
                                    id="sidebarimg-none" value="none">
                                <label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-none">
                                    <span
                                        class="avatar-md w-auto bg-light d-flex align-items-center justify-content-center">
                                        <i class="ri-close-fill fs-20"></i>
                                    </span>
                                </label>
                            </div>

                            <div class="form-check sidebar-setting card-radio">
                                <input class="form-check-input" type="radio" name="data-sidebar-image"
                                    id="sidebarimg-01" value="img-1">
                                <label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-01">
                                    <img src="images/sidebar/img-1.jpg" alt=""
                                        class="avatar-md w-auto object-cover">
                                </label>
                            </div>

                            <div class="form-check sidebar-setting card-radio">
                                <input class="form-check-input" type="radio" name="data-sidebar-image"
                                    id="sidebarimg-02" value="img-2">
                                <label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-02">
                                    <img src="images/sidebar/img-2.jpg" alt=""
                                        class="avatar-md w-auto object-cover">
                                </label>
                            </div>
                            <div class="form-check sidebar-setting card-radio">
                                <input class="form-check-input" type="radio" name="data-sidebar-image"
                                    id="sidebarimg-03" value="img-3">
                                <label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-03">
                                    <img src="images/sidebar/img-3.jpg" alt=""
                                        class="avatar-md w-auto object-cover">
                                </label>
                            </div>
                            <div class="form-check sidebar-setting card-radio">
                                <input class="form-check-input" type="radio" name="data-sidebar-image"
                                    id="sidebarimg-04" value="img-4">
                                <label class="form-check-label p-0 avatar-sm h-auto" for="sidebarimg-04">
                                    <img src="images/sidebar/img-4.jpg" alt=""
                                        class="avatar-md w-auto object-cover">
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <div class="offcanvas-footer border-top p-3 text-center">
            <div class="row">
                <div class="col-6">
                    <button type="button" class="btn btn-light w-100" id="reset-layout">Reset</button>
                </div>
                <div class="col-6">
                    <a href="https://1.envato.market/velzon-admin" target="_blank"
                        class="btn btn-primary w-100">Buy Now</a>
                </div>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('js/plugins.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!--datatable js-->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <!-- Modern colorpicker bundle -->
    <script src="{{ asset('assets/libs/@simonwep/pickr/pickr.min.js') }}"></script>

    <script src="{{ asset('libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- App js -->
    <script src="{{ asset('js/app.js') }}"></script>
    <!-- Mobile Optimization JS -->
    <script src="{{ asset('js/mobile-optimization.js') }}"></script>

    <script>
        (function () {
            function recalculateMenuScroll() {
                const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
                document.documentElement.style.setProperty('--crm-sidebar-height', viewportHeight + 'px');

                window.requestAnimationFrame(function () {
                    if (!window.SimpleBar || !window.SimpleBar.instances || typeof window.SimpleBar.instances.get !== 'function') {
                        return;
                    }

                    ['scrollbar', 'navbar-nav'].forEach(function (elementId) {
                        const element = document.getElementById(elementId);
                        const instance = element ? window.SimpleBar.instances.get(element) : null;

                        if (instance && typeof instance.recalculate === 'function') {
                            instance.recalculate();
                        }
                    });
                });
            }

            function keepMenuSectionVisible(element) {
                if (!element || !element.closest || !element.closest('#navbar-nav')) {
                    return;
                }

                window.requestAnimationFrame(function () {
                    const scrollbar = document.getElementById('scrollbar');
                    const scrollElement = document.querySelector('#scrollbar .simplebar-content-wrapper') || scrollbar;

                    if (!scrollElement) {
                        return;
                    }

                    const sectionRect = element.getBoundingClientRect();
                    const scrollRect = scrollElement.getBoundingClientRect();
                    const bottomGap = sectionRect.bottom - scrollRect.bottom + 24;
                    const topGap = scrollRect.top - sectionRect.top + 16;

                    if (bottomGap > 0) {
                        scrollElement.scrollTop += bottomGap;
                    } else if (topGap > 0) {
                        scrollElement.scrollTop -= topGap;
                    }
                });
            }

            recalculateMenuScroll();
            window.addEventListener('resize', recalculateMenuScroll);
            window.addEventListener('orientationchange', recalculateMenuScroll);
            document.addEventListener('shown.bs.collapse', function (event) {
                recalculateMenuScroll();
                keepMenuSectionVisible(event.target);
            });
            document.addEventListener('hidden.bs.collapse', recalculateMenuScroll);
        })();
    </script>

    <script>
        const TASK_NOTIF_URL = '{{ url('/tareas/notificaciones') }}';
        const TASK_NOTIF_MARK_ALL_URL = '{{ url('/tareas/notificaciones/leer-todas') }}';
        const TASK_NOTIF_MARK_ONE_BASE = '{{ url('/tareas/notificaciones') }}';
        const APP_CSRF = '{{ csrf_token() }}';

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderTaskNotifications(response) {
            const badge = document.getElementById('topbar-task-notif-count');
            const list = document.getElementById('topbar-task-notif-list');

            if (!badge || !list) return;

            const unread = Number(response?.unread || 0);
            badge.textContent = unread;
            badge.classList.toggle('d-none', unread <= 0);

            const items = response?.items || [];
            if (!items.length) {
                list.innerHTML = '<div class="p-4 text-center text-muted">No tienes notificaciones.</div>';
                return;
            }

            list.innerHTML = items.map(item => {
                const isUnread = !item.read_at;
                const rowClass = isUnread ? 'bg-light' : '';
                return `
                    <a href="javascript:void(0)" class="dropdown-item py-3 border-bottom task-notif-item ${rowClass}"
                        data-id="${escapeHtml(item.id)}" data-url="${escapeHtml(item.url || '/tareas')}" data-task-id="${escapeHtml(item.task_id || '')}">
                        <div class="d-flex align-items-start gap-2">
                            <div class="avatar-xs">
                                <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-14">
                                    <i class="ri-notification-3-line"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1 fs-13 fw-semibold">${escapeHtml(item.title)}</h6>
                                <p class="mb-1 fs-12 text-muted">${escapeHtml(item.message)}</p>
                                <small class="text-muted">${escapeHtml(item.created_at)}</small>
                            </div>
                        </div>
                    </a>
                `;
            }).join('');

            document.querySelectorAll('.task-notif-item').forEach(element => {
                element.addEventListener('click', function() {
                    const notificationId = this.dataset.id;
                    let targetUrl = this.dataset.url || '/tareas';
                    const taskId = this.dataset.taskId;

                    if (taskId && !targetUrl.includes('tarea_id=')) {
                        const separator = targetUrl.includes('?') ? '&' : '?';
                        targetUrl = `${targetUrl}${separator}tarea_id=${encodeURIComponent(taskId)}`;
                    }

                    fetch(`${TASK_NOTIF_MARK_ONE_BASE}/${notificationId}/leer`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': APP_CSRF,
                            'Accept': 'application/json',
                        },
                    }).finally(() => {
                        window.location.href = targetUrl;
                    });
                });
            });
        }

        function cargarTaskNotifications() {
            fetch(TASK_NOTIF_URL, {
                headers: {
                    'Accept': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => renderTaskNotifications(data))
            .catch(() => {});
        }

        document.addEventListener('DOMContentLoaded', function() {
            const btnMarkAll = document.getElementById('btn-marcar-todas-leidas');

            if (btnMarkAll) {
                btnMarkAll.addEventListener('click', function() {
                    fetch(TASK_NOTIF_MARK_ALL_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': APP_CSRF,
                            'Accept': 'application/json',
                        },
                    }).then(() => cargarTaskNotifications());
                });
            }

            cargarTaskNotifications();
            setInterval(cargarTaskNotifications, 30000);
        });
    </script>

    @yield('script')
</body>

</html>

