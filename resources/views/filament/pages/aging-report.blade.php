<x-filament::page>
    <div class="space-y-6">
        {{-- Filters --}}
        <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
            {{ $this->form }}
        </div>

        @php
            $labels = $this->bucketLabels();
            $totals = [
                'current' => collect($this->rows)->sum('current'),
                'b1'      => collect($this->rows)->sum('b1'),
                'b2'      => collect($this->rows)->sum('b2'),
                'b3'      => collect($this->rows)->sum('b3'),
                'total'   => collect($this->rows)->sum('total'),
            ];
        @endphp

        {{-- Table --}}
        <div class="rounded-xl border overflow-x-auto bg-white dark:bg-gray-900">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800">
                        <th class="px-4 py-3 text-left font-semibold">Name</th>
                        <th class="px-4 py-3 text-right font-semibold">{{ $labels['current'] }}</th>
                        <th class="px-4 py-3 text-right font-semibold">{{ $labels['b1'] }}</th>
                        <th class="px-4 py-3 text-right font-semibold">{{ $labels['b2'] }}</th>
                        <th class="px-4 py-3 text-right font-semibold">{{ $labels['b3'] }}</th>
                        <th class="px-4 py-3 text-right font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($this->rows as $row)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $row['name'] }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($row['current'], 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($row['b1'], 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($row['b2'], 2) }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($row['b3'], 2) }}</td>
                        <td class="px-4 py-2 text-right font-semibold">{{ number_format($row['total'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-gray-500" colspan="6">
                            No data for selected filters.
                        </td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t bg-gray-50 dark:bg-gray-800">
                        <td class="px-4 py-2 font-semibold text-right">Grand Total</td>
                        <td class="px-4 py-2 text-right font-semibold">{{ number_format($totals['current'], 2) }}</td>
                        <td class="px-4 py-2 text-right font-semibold">{{ number_format($totals['b1'], 2) }}</td>
                        <td class="px-4 py-2 text-right font-semibold">{{ number_format($totals['b2'], 2) }}</td>
                        <td class="px-4 py-2 text-right font-semibold">{{ number_format($totals['b3'], 2) }}</td>
                        <td class="px-4 py-2 text-right font-bold">{{ number_format($totals['total'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-filament::page>
