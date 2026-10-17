<x-ui::layouts.admin title="UI Components" :breadcrumbs="['Home' => route('admin.dashboard'), 'UI Components' => null]">
    <x-ui::alert type="info" :dismissible="false">
        Every block on this page is a theme component (<code>&lt;x-ui::...&gt;</code>). A new theme only has to provide these files.
    </x-ui::alert>

    <div class="row">
        <div class="col-lg-6">
            <x-ui::card title="Buttons">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <x-ui::button type="button">Primary</x-ui::button>
                    <x-ui::button type="button" variant="secondary">Secondary</x-ui::button>
                    <x-ui::button type="button" variant="success">Success</x-ui::button>
                    <x-ui::button type="button" variant="danger">Danger</x-ui::button>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <x-ui::button type="button" outline icon="plus">Create</x-ui::button>
                    <x-ui::button type="button" outline variant="secondary" icon="pencil-simple">Edit</x-ui::button>
                    <x-ui::button type="button" size="sm" icon="trash" variant="danger">Delete</x-ui::button>
                </div>
            </x-ui::card>

            <x-ui::card title="Badges & alerts">
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <x-ui::badge>Primary</x-ui::badge>
                    <x-ui::badge variant="success">Active</x-ui::badge>
                    <x-ui::badge variant="warning">Pending</x-ui::badge>
                    <x-ui::badge variant="danger">Deactivated</x-ui::badge>
                    <x-ui::badge variant="secondary">Draft</x-ui::badge>
                </div>
                <x-ui::alert type="success">Admin saved successfully.</x-ui::alert>
                <x-ui::alert type="danger">Something went wrong.</x-ui::alert>
            </x-ui::card>
        </div>

        <div class="col-lg-6">
            {{-- Plain Bootstrap pieces, shown here to check them in light and dark mode --}}
            <x-ui::card title="Bootstrap extras (dark-mode check)">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="dropdown-menu show position-static w-100">
                            <h6 class="dropdown-header">Test User</h6>
                            <span class="dropdown-item-text text-muted small">test@example.com</span>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="#">Profile</a>
                            <a class="dropdown-item" href="#">Logout</a>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <ul class="list-group">
                            <li class="list-group-item">First item</li>
                            <li class="list-group-item active">Active item</li>
                            <li class="list-group-item text-muted">Muted item</li>
                        </ul>
                    </div>
                    <div class="col-12">
                        <nav>
                            <ul class="pagination mb-3">
                                <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                <li class="page-item active"><span class="page-link">1</span></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">Next</a></li>
                            </ul>
                        </nav>
                        <div class="progress"><div class="progress-bar" style="width: 60%"></div></div>
                    </div>
                </div>
            </x-ui::card>
        </div>

        <div class="col-lg-6">
            <x-ui::card title="Form">
                <form onsubmit="return false">
                    <x-ui::input name="demo_name" label="Name" value="Juan dela Cruz" />
                    <x-ui::input name="demo_email" type="email" label="Email" help="We'll never share your email." />
                    <x-ui::input name="demo_password" type="password" label="Password" />
                    <x-ui::checkbox name="demo_active" label="Active" checked />
                    <x-ui::button type="button" icon="floppy-disk">Save</x-ui::button>
                </form>
            </x-ui::card>
        </div>
    </div>

    {{-- Charts: the page passes data only, the theme draws them (ApexCharts in gradient-able) --}}
    @php($months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'])
    <div class="row">
        <div class="col-lg-8">
            <x-ui::card title="Line / area chart">
                <x-ui::chart type="area" :labels="$months" :series="[
                    ['name' => 'Logins', 'data' => [31, 40, 28, 51, 42, 64]],
                    ['name' => 'New clients', 'data' => [11, 18, 15, 22, 19, 30]],
                ]" />
            </x-ui::card>
        </div>
        <div class="col-lg-4">
            <x-ui::card title="Donut chart">
                <x-ui::chart type="donut" :labels="['Active', 'Pending', 'Deactivated']" :series="[64, 22, 14]" />
            </x-ui::card>
        </div>
        <div class="col-12">
            <x-ui::card title="Bar chart">
                <x-ui::chart type="bar" height="260" :labels="$months" :series="[
                    ['name' => 'Created', 'data' => [12, 19, 14, 25, 21, 30]],
                    ['name' => 'Deleted', 'data' => [2, 4, 3, 5, 2, 6]],
                ]" />
            </x-ui::card>
        </div>
    </div>
</x-ui::layouts.admin>
