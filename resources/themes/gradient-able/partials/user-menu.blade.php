@php($user = auth()->user())
<li class="dropdown pc-h-item header-user-profile">
    <a class="pc-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
        @if ($user)
            <span class="user-initials">{{ str($user->name)->explode(' ')->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}</span>
        @else
            <i class="ph ph-user-circle"></i>
        @endif
    </a>
    <div class="dropdown-menu dropdown-user-profile dropdown-menu-end pc-h-dropdown">
        <div class="dropdown-body">
            <ul class="list-group list-group-flush w-100">
                @if ($user)
                    <li class="list-group-item">
                        <span class="d-block fw-semibold">{{ $user->name }}</span>
                        <small class="text-muted">{{ $user->email }}</small>
                    </li>
                @endif
                <li class="list-group-item">
                    @if ($account = $theme->url('account'))
                        <a href="{{ $account }}" class="dropdown-item">
                            <span class="d-flex align-items-center"><i class="ph ph-user-circle"></i><span>Profile</span></span>
                        </a>
                    @endif
                    @if ($logout = $theme->url('logout'))
                        <form method="POST" action="{{ $logout }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <span class="d-flex align-items-center"><i class="ph ph-power"></i><span>Logout</span></span>
                            </button>
                        </form>
                    @endif
                    @if (! $user)
                        <span class="dropdown-item text-muted">Not signed in</span>
                    @endif
                </li>
            </ul>
        </div>
    </div>
</li>
