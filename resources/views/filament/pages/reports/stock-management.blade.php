<!-- resources/views/filament/pages/reports/stock-management.blade.php -->

<x-filament::page>
    <form wire:submit.prevent="loadReportData">
        <x-filament::card>
            <x-filament::form>
                {{ $this->getDateRangeFormComponent()->render() }}
                <x-filament::button type="submit">
                    Load Report
                </x-filament::button>
            </x-filament::form>
        </x-filament::card>
    </form>

    @if ($reportLoaded)
        <x-filament::table>
            <thead>
                <tr>
                    @foreach ($this->getTable() as $column)
                        <th>{{ Str::title(str_replace('_', ' ', $column)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($this->report()->data as $row)
                    <tr>
                        @foreach ($this->getTable() as $column)
                            <td>{{ $row[$column] }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </x-filament::table>
    @endif
</x-filament::page>
