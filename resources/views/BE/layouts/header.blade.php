<div class="ms-auto">
    <ul class="list-unstyled">
        <li class="dropdown pc-h-item header-user-profile">
            <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" data-bs-auto-close="outside" aria-expanded="false">
                <div class="avtar avtar-s bg-light-primary rounded-circle">
                    <i class="ti ti-user f-18"></i>
                </div>
            </a>
            <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
                <div class="dropdown-header d-flex align-items-center justify-content-between">
                    <h5 class="m-0">Profil</h5>
                </div>
                <div class="dropdown-body">
                    <div class="profile-notification-scroll position-relative" style="max-height: calc(100vh - 225px)">
                        <div class="d-flex mb-1">
                            <div class="flex-shrink-0">
                                <div class="avtar avtar-s bg-light-primary rounded-circle">
                                    <i class="ti ti-user f-18"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ Auth::user()->name }}</h6>
                                <span>{{ Auth::user()->email }}</span>
                                <br><span class="badge bg-light-primary mt-1">{{ ucfirst(Auth::user()->role) }}</span>
                            </div>
                        </div>
                        <hr class="border-secondary border-opacity-50" />
                        <div class="d-grid mb-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-power me-2"></i>Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</div>
