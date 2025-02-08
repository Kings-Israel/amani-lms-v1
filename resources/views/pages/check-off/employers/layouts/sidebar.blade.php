<nav class="pcoded-navbar">
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigatio-lavel">Navigation</div>
        <ul class="pcoded-item pcoded-left-item">


            <li class=" {{Request::path() == 'home' ? 'active' : ''}}">
                <a href="{{route('check-off.employer.dashboard')}}">
                    <span class="pcoded-micon"><i class="feather icon-home"></i></span>
                    <span class="pcoded-mtext">Dashboard</span>
                </a>
            </li>
            <li class=" {{Request::path() == 'home' ? 'active' : ''}}">
                <a href="{{route('check-off.employer.loans')}}">
                    <span class="pcoded-micon"><i class="feather icon-list"></i></span>
                    <span class="pcoded-mtext">Loans</span>
                </a>
            </li>

        </ul>

    </div>
</nav>
