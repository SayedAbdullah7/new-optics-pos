<!--begin::Sidebar-->
<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
     data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="{{ route('dashboard') }}">
            <img alt="Logo" src="{{ asset('assets/media/logos/default-dark.svg') }}" class="h-25px app-sidebar-logo-default"/>
            <img alt="Logo" src="{{ asset('assets/media/logos/default-small.svg') }}" class="h-20px app-sidebar-logo-minimize"/>
        </a>
        <!--end::Logo image-->
        <!--begin::Sidebar toggle-->
        <div id="kt_app_sidebar_toggle"
             class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
             data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
             data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
                 data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                 data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                 data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                 data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                     data-kt-menu="true" data-kt-menu-expand="false">

                    <!--begin:Menu item - Dashboard-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('dashboard') ? 'active' : '' }}"
                           href="{{ route('dashboard') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-element-11 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Section Header-->
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Sales</span>
                        </div>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Clients-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.clients.*') ? 'active' : '' }}"
                           href="{{ route('admin.clients.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-people fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                            </span>
                            <span class="menu-title">Clients</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Invoices-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.invoices.*') ? 'active' : '' }}"
                           href="{{ route('admin.invoices.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-bill fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                    <span class="path6"></span>
                                </i>
                            </span>
                            <span class="menu-title">Invoices</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Section Header-->
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Inventory</span>
                        </div>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Categories-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.categories.*') ? 'active' : '' }}"
                           href="{{ route('admin.categories.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-category fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">Categories</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Products-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.products.*') ? 'active' : '' }}"
                           href="{{ route('admin.products.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-package fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Products</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Stock-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.stock.*') ? 'active' : '' }}"
                           href="{{ route('admin.stock.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-abstract-26 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">Stock</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Lenses-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.lenses.*') ? 'active' : '' }}"
                           href="{{ route('admin.lenses.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-eye fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Lenses</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Lens Types-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.lens-types.*') ? 'active' : '' }}"
                           href="{{ route('admin.lens-types.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-category fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">Lens Types</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Lens Brands-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.lens-brands.*') ? 'active' : '' }}"
                           href="{{ route('admin.lens-brands.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-abstract-38 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Lens Brands</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Lens Power Presets-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.lens-power-presets.*') || Route::is('admin.multi-select-table*') ? 'active' : '' }}"
                           href="{{ route('admin.lens-power-presets.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-grid fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">محفوظات قوى العدسات</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Section Header-->
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Purchases</span>
                        </div>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Vendors-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.vendors.*') ? 'active' : '' }}"
                           href="{{ route('admin.vendors.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-truck fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                            </span>
                            <span class="menu-title">Vendors</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Bills-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.bills.*') ? 'active' : '' }}"
                           href="{{ route('admin.bills.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-document fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Bills</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Section Header-->
                    <div class="menu-item pt-5">
                        <div class="menu-content">
                            <span class="menu-heading fw-bold text-uppercase fs-7">Finance</span>
                        </div>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Transactions-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.transactions.*') ? 'active' : '' }}"
                           href="{{ route('admin.transactions.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-dollar fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <span class="menu-title">Transactions</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                    <!--begin:Menu item - Expenses-->
                    <div class="menu-item">
                        <a class="menu-link {{ Route::is('admin.expenses.*') ? 'active' : '' }}"
                           href="{{ route('admin.expenses.index') }}">
                            <span class="menu-icon">
                                <i class="ki-duotone ki-wallet fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">Expenses</span>
                        </a>
                    </div>
                    <!--end:Menu item-->

                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
    <!--begin::Footer-->
    <div class="app-sidebar-footer flex-column-auto pt-2 pb-6 px-6" id="kt_app_sidebar_footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-flex flex-center btn-custom btn-primary overflow-hidden text-nowrap px-0 h-40px w-100">
                <span class="btn-label">Logout</span>
                <i class="ki-duotone ki-exit-right btn-icon fs-2 m-0">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </button>
        </form>
    </div>
    <!--end::Footer-->
</div>
<!--end::Sidebar-->





