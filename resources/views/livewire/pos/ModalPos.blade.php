<!-- Modal Held Bills -->
@if($showHeldBillsModal)
<div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50" x-on:keydown.escape.window="$wire.set('showHeldBillsModal', false)">
    <div class="bg-white rounded-lg p-6 w-full max-w-md" @click.away="$wire.set('showHeldBillsModal', false)">
        <h3 class="text-lg font-bold mb-4">Bills yang Di-Hold</h3>
        <div class="max-h-96 overflow-y-auto space-y-2">
            @forelse($heldBills as $bill)
                <div class="flex justify-between items-center p-3 border rounded-lg hover:bg-gray-50">
                    <div>
                        <p class="font-semibold">{{ $bill['customerName'] }} ({{ $bill['orderType'] }})</p>
                        <p class="text-sm text-gray-500">{{ count($bill['cart']) }} item</p>
                    </div>
                    <button wire:click="restoreBill('{{ $bill['id'] }}')" class="px-3 py-1 bg-blue-500 text-white text-sm rounded-md hover:bg-blue-600">Restore</button>
                </div>
            @empty
                <p class="text-center text-gray-500 py-5">Tidak ada bill yang di-hold.</p>
            @endforelse
        </div>
        <div class="flex justify-end mt-6">
            <button wire:click="$set('showHeldBillsModal', false)" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Tutup</button>
        </div>
    </div>
</div>
@endif

<!-- Modal Pembayaran -->
@if($showPaymentModal)
<div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50" x-on:keydown.escape.window="$wire.set('showPaymentModal', false)">
    <div class="bg-white rounded-lg p-6 w-full max-w-md" @click.away="$wire.set('showPaymentModal', false)">
        <h3 class="text-lg font-bold mb-2">Konfirmasi Pembayaran</h3>
        <p class="text-sm text-gray-600 mb-6">Total Tagihan: <span class="font-bold text-blue-600 text-lg">Rp {{ number_format($this->total, 0, ',', '.') }}</span></p>
        <div class="space-y-4">
            <!-- Form untuk metode pembayaran, catatan, dll. bisa ditambahkan di sini -->
            <p>Apakah Anda yakin ingin menyelesaikan pembayaran ini?</p>
        </div>
        <div class="flex justify-end space-x-4 mt-8">
            <button wire:click="$set('showPaymentModal', false)" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Batal</button>
            <button wire:click="processPayment" wire:loading.attr="disabled" class="px-6 py-2 bg-green-600 rounded-md text-white hover:bg-green-700 disabled:bg-green-400">
                <span wire:loading.remove wire:target="processPayment">Konfirmasi</span>
                <span wire:loading wire:target="processPayment">Memproses...</span>
            </button>
        </div>
    </div>
</div>
@endif

<!-- Modal Diskon -->
@if($showDiscountModal)
<div class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50" x-on:keydown.escape.window="$wire.set('showDiscountModal', false)">
    <div class="bg-white rounded-lg p-6 w-full max-w-md" @click.away="$wire.set('showDiscountModal', false)">
        <h3 class="text-lg font-bold mb-4">{{ $discountTargetId ? 'Diskon per Item' : 'Diskon Keseluruhan' }}</h3>
        <form wire:submit.prevent="applyDiscount">
            <label for="discount-input" class="text-sm">Diskon (%)</label>
            <input type="number" id="discount-input" wire:model="discountValue" class="w-full p-2 border rounded-md mt-1" placeholder="Contoh: 10">
            @error('discountValue') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" wire:click="$set('showDiscountModal', false)" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 rounded-md text-white hover:bg-blue-700">Terapkan</button>
            </div>
        </form>
    </div>
</div>
@endif