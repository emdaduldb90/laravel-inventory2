<x-filament::page>
    <div
        x-data
        x-on:keydown.window.prevent.slash="$refs.sku?.focus()"
        x-on:pos-saved.window="$store?.toast?.show({message: 'Sale completed'})"
        class="grid md:grid-cols-3 gap-4"
    >
        {{-- Left: Scan & Cart --}}
        <div class="md:col-span-2 space-y-4">
            <div class="rounded-xl border p-4 bg-white dark:bg-gray-900">
                <div class="flex gap-2">
                    <x-filament::input
                        x-ref="sku"
                        wire:model.defer="sku"
                        placeholder="Scan / type SKU then press Enter"
                        class="w-full"
                        wire:keydown.enter.prevent="addBySku"
                    />
                    <x-filament::input
                        type="number" min="1"
                        wire:model.defer="qty"
                        class="w-28"
                    />
                    <x-filament::button wire:click="addBySku" icon="heroicon-o-plus">Add</x-filament::button>
                </div>
            </div>

            <div class="rounded-xl border bg-white dark:bg-gray-900 overflow-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left">SKU</th>
                            <th class="px-3 py-2 text-left">Product</th>
                            <th class="px-3 py-2 text-right">Price</th>
                            <th class="px-3 py-2 text-center">Qty</th>
                            <th class="px-3 py-2 text-right">Line</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($this->cart as $row)
                        <tr class="border-t">
                            <td class="px-3 py-2">{{ $row['sku'] }}</td>
                            <td class="px-3 py-2">{{ $row['name'] }}</td>
                            <td class="px-3 py-2 text-right">{{ number_format($row['price'],2) }}</td>
                            <td class="px-3 py-2 text-center">
                                <div class="inline-flex items-center gap-1">
                                    <x-filament::button size="xs" color="gray" icon="heroicon-o-minus" wire:click="dec({{ $row['product_id'] }})" />
                                    <span class="w-10 inline-block text-center">{{ $row['qty'] }}</span>
                                    <x-filament::button size="xs" color="gray" icon="heroicon-o-plus" wire:click="inc({{ $row['product_id'] }})" />
                                </div>
                            </td>
                            <td class="px-3 py-2 text-right font-medium">{{ number_format($row['line'],2) }}</td>
                            <td class="px-3 py-2 text-right">
                                <x-filament::button size="xs" color="danger" icon="heroicon-o-trash" wire:click="remove({{ $row['product_id'] }})" />
                            </td>
                        </tr>
                    @empty
                        <tr><td class="px-3 py-6 text-center text-gray-500" colspan="6">Cart is empty</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Right: Summary & Checkout --}}
        <div class="space-y-4">
            <div class="rounded-xl border p-4 bg-white dark:bg-gray-900 space-y-3">
                <x-filament::fieldset legend="Customer / Warehouse">
                    <div class="grid grid-cols-1 gap-2">
                        <x-filament::select
                            wire:model="customer_id"
                            placeholder="Choose customer"
                            :options="\App\Models\Customer::where('company_id',auth()->user()->company_id)->pluck('name','id')->toArray()"
                            searchable
                        />
                        <x-filament::select
                            wire:model="warehouse_id"
                            placeholder="Warehouse"
                            :options="\App\Models\Warehouse::where('company_id',auth()->user()->company_id)->pluck('code','id')->toArray()"
                            searchable
                        />
                    </div>
                </x-filament::fieldset>

                <x-filament::fieldset legend="Totals">
                    <div class="space-y-2">
                        <div class="flex justify-between"><span>Subtotal</span><span>{{ number_format($subtotal,2) }}</span></div>
                        <div class="flex justify-between items-center">
                            <span>Discount</span>
                            <x-filament::input type="number" step="0.01" wire:model.live="discount" class="w-28" />
                        </div>
                        <div class="flex justify-between items-center">
                            <span>Tax</span>
                            <x-filament::input type="number" step="0.01" wire:model.live="tax" class="w-28" />
                        </div>
                        <div class="flex justify-between font-semibold text-lg">
                            <span>Total</span><span>{{ number_format($total,2) }}</span>
                        </div>
                    </div>
                </x-filament::fieldset>

                <x-filament::fieldset legend="Payment">
                    <div class="space-y-2">
                        <x-filament::select
                            wire:model="method_id"
                            placeholder="Method"
                            :options="\App\Models\PaymentMethod::where('company_id',auth()->user()->company_id)->pluck('name','id')->toArray()"
                            searchable
                        />
                        <div class="flex justify-between items-center">
                            <span>Paid</span>
                            <x-filament::input type="number" step="0.01" wire:model.live="paid" class="w-32" />
                        </div>
                        <div class="flex justify-between font-semibold">
                            <span>Change</span><span>{{ number_format($change,2) }}</span>
                        </div>
                    </div>
                </x-filament::fieldset>

                <div class="flex gap-2">
                    <x-filament::button color="gray" wire:click="clearCart" icon="heroicon-o-x-mark">Clear</x-filament::button>
                    <x-filament::button class="flex-1" color="success" wire:click="checkout" icon="heroicon-o-check-circle">
                        Checkout (Enter)
                    </x-filament::button>
                </div>

                @if (session()->has('pos_last_sale_id'))
                    <div class="pt-2 border-t">
                        <a class="text-primary-600 dark:text-primary-400 underline" target="_blank"
                           href="{{ route('print.pos', ['id'=>session('pos_last_sale_id')]) }}">
                            Print last receipt
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- focus স্ক্যান ইনপুট --}}
    <script>
        window.addEventListener('livewire:navigated', () => {
            setTimeout(()=>document.querySelector('[x-ref=sku]')?.focus(), 100);
        });
    </script>
</x-filament::page>
