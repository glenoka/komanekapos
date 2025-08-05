<div>
    <div class="flex flex-col lg:flex-row h-screen font-sans bg-gray-100 text-gray-800" x-data>

        <!-- Bagian Kiri: Daftar Produk -->
        <div class="w-full lg:w-2/3 flex flex-col h-screen p-4 sm:p-6">
            <header class="flex-shrink-0 flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">POS Komaneka</h1>
                    <p class="text-sm sm:text-base text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button wire:click="$set('showHeldBillsModal', true)" class="relative bg-white p-2 rounded-full text-gray-600 hover:bg-gray-200 transition">
                        <x-heroicon-o-inbox-stack class="h-6 w-6"/>
                        @if(count($heldBills) > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center animate-pulse">{{ count($heldBills) }}</span>
                        @endif
                    </button>
                    <div class="relative w-48 lg:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3"><x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400"/></span>
                        <input type="text" wire:model.live.debounce.300ms="searchTerm" placeholder="Cari menu..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    </div>
                </div>
            </header>
    
            <nav class="flex-shrink-0 mb-6">
                <div class="flex space-x-2 sm:space-x-4 overflow-x-auto pb-3 -mx-4 px-4">
                  
                    @foreach($categories as $category)
                        <button wire:click="$set('activeCategory', {{ $category->id }})" class="px-4 py-2 text-sm font-medium rounded-full transition-colors duration-300 whitespace-nowrap {{ $activeCategory === $category->id ? 'bg-blue-600 text-white shadow-lg' : 'bg-white text-gray-600 hover:bg-blue-100' }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </nav>
    
            <main class="flex-1 overflow-y-auto" wire:loading.class="opacity-50">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-5">
                    @forelse($products as $product)
                   
                        <div wire:click="addToCart({{ $product['id'] }})" class="bg-white rounded-xl shadow-md p-3 flex flex-col cursor-pointer hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                            <img src="{{ $product['image'] ?? 'https://placehold.co/300x300' }}" alt="{{ $product['name'] }}" class="w-full h-24 sm:h-32 object-cover rounded-lg mb-3">
                            <div class="flex-1 flex flex-col">
                                <h3 class="font-semibold text-sm text-gray-800 flex-grow">{{ $product['name'] }}</h3>
                                <p class="text-gray-600 mt-2 font-bold">Rp {{ number_format($product['price'], 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 col-span-full text-center py-10">Tidak ada produk yang cocok dengan pencarian Anda.</p>
                    @endforelse
                </div>
            </main>
        </div>
       
        <!-- Bagian Kanan: Ringkasan Pesanan -->
        <div class="w-full lg:w-1/3 bg-white h-auto lg:h-screen flex flex-col shadow-2xl">
            <div class="p-5 flex-1 flex flex-col">
                <h2 class="text-xl font-bold text-gray-800 border-b pb-4 mb-4">
                    {{ $restoredBillId ? 'Pesanan: ' . ($customerName ?: 'Pelanggan') : 'Pesanan Baru' }}
                </h2>
                
    
                <div class="grid grid-cols-2 gap-4 mb-4">
                    
                    <Select wire:model.live="customerName" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option>Guest 101</option>
                        <option>Guest 102</option>
                        <option>Guest 103</option>
                    </Select>
                    <select wire:model.live="orderType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option>Dine-In</option>
                        <option>Take Away</option>
                        <option>Room Service</option>
                    </select>
                </div>
    
                <div class="flex-1 overflow-y-auto -mr-4 pr-4">
                    @forelse($cart as $id => $item)
                        <div class="flex justify-between items-center mb-3 p-2 rounded-lg hover:bg-gray-50">
                            <div class="flex-1 pr-2">
                                <p class="font-semibold text-sm text-gray-800">{{ $item['name'] }}</p>
                                <p class="text-xs text-gray-500">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                @if($item['discount'] > 0)
                                    <span class="text-xs bg-green-100 text-green-700 font-medium px-2 py-0.5 rounded-full">{{ $item['discount'] }}%</span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2">
                                <button wire:click="updateQuantity({{ $id }}, -1)" class="w-6 h-6 rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300">-</button>
                                <span class="font-medium w-5 text-center text-sm">{{ $item['quantity'] }}</span>
                                <button wire:click="updateQuantity({{ $id }}, 1)" class="w-6 h-6 rounded-full bg-gray-200 text-gray-700 hover:bg-gray-300">+</button>
                            </div>
                            <span class="w-24 text-right font-semibold text-sm text-gray-800">Rp {{ number_format($item['price'] * $item['quantity'] * (1 - $item['discount']/100), 0, ',', '.') }}</span>
                            <button wire:click="showDiscountModal({{ $id }})" class="ml-2 text-gray-400 hover:text-blue-500"><x-heroicon-o-pencil-square class="w-5 h-5"/></button>
                        </div>
                    @empty
                        <div class="h-full flex flex-col justify-center items-center text-center text-gray-400">
                            <x-heroicon-o-shopping-cart class="w-16 h-16 mb-4"/>
                            <p class="font-medium">Keranjang kosong</p>
                        </div>
                    @endforelse
                </div>
    
                <div class="border-t pt-5 mt-auto flex-shrink-0">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between">
                            <button wire:click="showDiscountModal(null)" class="text-blue-600 hover:underline">Diskon Keseluruhan</button>
                        @if($overallDiscount > 0)
                            <span class="text-green-600">- Rp {{ number_format($this->discountValue, 0, ',', '.') }} ({{$overallDiscount}}%)</span>
                        @endif
                        </div>
                        <div class="flex justify-between"><span>Pajak (11%)</span><span>Rp {{ number_format($this->tax, 0, ',', '.') }}</span></div>
                    </div>
                    <div class="flex justify-between text-gray-800 font-bold text-xl border-t pt-4 mt-4">
                        <span>Total</span><span>Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                    </div> 
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <button wire:click="holdBill" @disabled(empty($cart)) class="w-full bg-gray-200 text-gray-800 py-3 rounded-lg font-semibold hover:bg-gray-300 transition disabled:opacity-50">Hold Bill</button>
                        <button wire:click="$set('showPaymentModal', true)" @disabled(empty($cart)) class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition disabled:bg-blue-300">Bayar Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- Modal Section -->
        @include('livewire.pos.ModalPos')
    </div>

    <button
    wire:click="openModal"
    class="bg-blue-600 text-white px-4 py-2 rounded">
    Tambah Data
    </button>

    @if ($ModalOverallDiscount)
    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-800 bg-opacity-50 z-40"></div>

    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
            <h2 class="text-xl font-semibold mb-4">Input Diskon Keseluruhan</h2>

            <!-- Form -->
            <div class="mb-4">
                <label for="discount" class="block text-sm font-medium text-gray-700">Diskon (%)</label>
                <input
                    type="number"
                    id="discount"
                    wire:model="discount_overall"
                    class="mt-1 block w-full w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Masukkan diskon (0-100)"
                    min="0"
                    max="100"
                >
                @error('discount_overall') 
                    <span class="text-sm text-red-600">{{ $message }}</span> 
                @enderror
            </div>

            <!-- Tombol -->
            <div class="text-right">
                <button
                    wire:click="closeModal"
                    class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
                >
                    Batal
                </button>
                <button
                    wire:click="save"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 ml-2"
                >
                    Simpan
                </button>
            </div>
        </div>
    </div>
@endif

    
</div>

