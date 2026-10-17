@php
    $eventBadge = fn (?string $event) => match (true) {
        in_array($event, ['deleted', 'role-deleted', 'login-failed', 'deactivated']) => 'danger',
        in_array($event, ['created', 'role-created', 'restored', 'reactivated']) => 'success',
        in_array($event, ['login', 'logout']) => 'secondary',
        default => 'primary',
    };
    $show = fn ($value) => is_array($value) ? (implode(', ', array_map(fn ($v) => is_bool($v) ? ($v ? 'yes' : 'no') : (string) $v, $value)) ?: '—') : (is_bool($value) ? ($value ? 'yes' : 'no') : ($value ?? '—'));
@endphp
<x-ui::layouts.admin title="Activity Log" :breadcrumbs="['Home' => route('admin.dashboard'), 'Activity Log' => null]">
    <x-ui::card title="Who did what" flush>
        <form method="GET" class="row g-2 align-items-end px-4 pt-3 pb-1">
            <div class="col-md-6">
                <x-ui::input name="search" label="Search" :value="$search" placeholder="Admin name, email or action" />
            </div>
            <div class="col-md-3">
                <x-ui::select name="event" label="Event" :value="$event"
                    :options="['' => 'All events'] + $events->mapWithKeys(fn ($e) => [$e => str($e)->replace('-', ' ')->ucfirst()->toString()])->all()" />
            </div>
            <div class="col-md-3 mb-3 d-flex gap-2">
                <x-ui::button type="submit" icon="magnifying-glass" outline>Filter</x-ui::button>
                @if ($search !== '' || $event !== '')
                    <x-ui::button href="{{ route('admin.activity.index') }}" variant="secondary" outline>Clear</x-ui::button>
                @endif
            </div>
        </form>

        <x-ui::table :headers="['When', 'Who', 'What', 'Details']">
            @forelse ($activities as $activity)
                @php($details = collect($activity->properties)->except('subject_name'))
                <tr>
                    <td class="text-nowrap text-muted small" title="{{ $activity->created_at->format('M j, Y g:i:s A') }}">
                        {{ $activity->created_at->diffForHumans() }}
                    </td>
                    <td>
                        @if ($activity->causer)
                            <div class="fw-semibold">{{ $activity->causer->name }}</div>
                            <small class="text-muted">{{ $activity->causer->email }}</small>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        <x-ui::badge :variant="$eventBadge($activity->event)" class="me-1">{{ str($activity->event)->replace('-', ' ') }}</x-ui::badge>
                        {{ $activity->description }}
                    </td>
                    <td class="small">
                        @if ($details->isNotEmpty())
                            <details>
                                <summary class="link-primary">Show</summary>
                                <dl class="mb-0 mt-2">
                                    @foreach ($details as $key => $value)
                                        <dt class="text-capitalize">{{ str($key)->replace('_', ' ') }}</dt>
                                        <dd class="mb-1">
                                            @if ($key === 'changes')
                                                @foreach ($value as $field => $change)
                                                    <div><span class="text-muted">{{ $field }}:</span> {{ $show($change['from'] ?? null) }} → {{ $show($change['to'] ?? null) }}</div>
                                                @endforeach
                                            @elseif ($key === 'renamed')
                                                {{ $value['from'] }} → {{ $value['to'] }}
                                            @else
                                                {{ $show($value) }}
                                            @endif
                                        </dd>
                                    @endforeach
                                </dl>
                            </details>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
            @endforelse
        </x-ui::table>

        <x-ui::pagination :paginator="$activities" class="px-4 pt-3" />
    </x-ui::card>
</x-ui::layouts.admin>
