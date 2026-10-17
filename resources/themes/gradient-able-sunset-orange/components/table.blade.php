{{-- <x-ui::table :headers="['Name', 'Email', '']"> <tr><td>..</td></tr> </x-ui::table> --}}
@props(['headers' => []])
<div class="table-responsive">
    <table {{ $attributes->class(['table', 'table-hover', 'align-middle', 'mb-0']) }}>
        @if ($headers)
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
