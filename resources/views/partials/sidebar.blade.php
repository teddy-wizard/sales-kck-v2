<aside class="menu-sidebar d-none d-lg-block">
    <div class="logo">
        <a href="#">
            <img src="{{asset('images/KCK-logo.png')}}" alt="Cool Admin" />
        </a>
    </div>
    <div class="menu-sidebar__content js-scrollbar1">
        <nav class="navbar-sidebar">
            <ul class="list-unstyled navbar__list">
                <li class="{{Str::contains(Route::currentRouteName(), 'user') ? 'active' : ''}}"><a href="{{route('user.index')}}"><i class="fas fa-user"></i>Users</a></li>
                <li class="has-sub">
                    <a class="js-arrow" href="#"><i class="fas fa-file-text"></i>Sales Reports</a>
                    <ul class="navbar-mobile-sub__list list-unstyled js-sub-list" style="display:{{Str::contains(Route::currentRouteName(), 'reports') ? 'block' : 'none'}};">
                        <li class="{{Str::contains(Route::currentRouteName(), 'reports.thrupt') ? 'active' : ''}}"><a href="{{ route('reports.thruput') }}"><i class="fas fa-dot-circle-o"></i>Monthly Thruput Archivement</a></li>
                        <li class="{{Str::contains(Route::currentRouteName(), 'reports.retail') ? 'active' : ''}}"><a href="{{ route('reports.retail') }}"><i class="fas fa-dollar"></i>Monthly Retail</a></li>
                        <li class="{{Str::contains(Route::currentRouteName(), 'reports.salesman') ? 'active' : ''}}"><a href="{{ route('reports.salesman') }}"><i class="fas fa-qrcode"></i>Product Salesman</a></li>
                    </ul>
                </li>
                <li class="{{Str::contains(Route::currentRouteName(), 'changePassword') ? 'active' : ''}}"><a href="{{route('changePassword')}}"><i class="fas fa-lock"></i>Change Password</a></li>
            </ul>
        </nav>
    </div>
</aside>
