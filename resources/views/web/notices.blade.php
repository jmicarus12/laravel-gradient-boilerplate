{{-- App-wide notices for the user area; included by the theme's user layout. --}}
@auth('web')
    @unless (auth('web')->user()->hasVerifiedEmail())
        <x-ui::alert type="warning" :dismissible="false" class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span><i class="ph ph-envelope-simple me-1"></i> Please verify your email address — check your inbox for the link.</span>
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-ui::button size="sm" variant="warning" icon="paper-plane-tilt">Resend link</x-ui::button>
            </form>
        </x-ui::alert>
    @endunless
@endauth
