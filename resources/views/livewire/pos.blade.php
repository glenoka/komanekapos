<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    
    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <!-- Bagian Kategori + Search -->
        <div class="flex gap-4 items-center justify-between mb-4">
            
            <div class="flex gap-4 overflow-x-auto pb-3 px-5 overflow-y-hidden flex-1">
                @foreach ($categories as $category)
                    <div class="relative">
                        <x-filament::button class="whitespace-nowrap mb-2 transition-all duration-200 hover:scale-105"
                            wire:click="$set('activeCategory', {{ $category->id }})" 
                            :color="$activeCategory === $category->id ? 'primary' : 'info'" 
                            size="sm">
                            {{ $category->name }}
                        </x-filament::button>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Search Input -->
        <div class="flex-shrink-0 mb-3">
            <x-filament::input.wrapper>
                <x-filament::input type="search" 
                    wire:model.live.debounce.500ms="search" 
                    placeholder="Cari produk..."
                    icon="heroicon-o-magnifying-glass" 
                    class="h-9 mb-3 transition-all duration-200" />
            </x-filament::input.wrapper>
        </div>

        <!-- Daftar Produk -->
        <div class="flex-grow mt-3">
            <div class="flex-grow mt-3">
    <!-- Ubah class grid dari grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 -->
    <!-- Menjadi grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 -->
    <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 gap-3 p-1">
        @foreach ($products as $product)
            <x-filament::section
                class="!p-4 !m-0 cursor-pointer transition-all duration-300 hover:shadow-lg hover:-translate-y-1 hover:scale-105 group border-2 border-transparent hover:border-primary-200 dark:hover:border-primary-700"
                wire:click="addToOrder({{ $product->id }})">

                <div class="space-y-3">
                    <!-- Icon placeholder -->
                    <div class="w-full flex justify-center">
                        <div class="w-14 h-14 bg-primary-50 dark:bg-primary-900/20 rounded-xl flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/40 transition-all duration-300 group-hover:rotate-3 group-hover:scale-110">
                            <x-heroicon-o-cube class="w-7 h-7 text-primary-600 dark:text-primary-400 group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors duration-300" />
                        </div>
                    </div>

                    <!-- Product details -->
                    <div class="text-center space-y-2">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-300 leading-tight">
                            {{ $product->name ?? 'Nama Produk' }}
                        </h3>

                        <x-filament::badge color="success" size="sm" class="font-semibold group-hover:scale-105 transition-transform duration-200">
                            Rp {{ number_format($product->price ?? 10000, 0, ',', '.') }}
                        </x-filament::badge>
                    </div>
                </div>
            </x-filament::section>
        @endforeach
    </div>

            </div>

            <!-- Pagination -->
            <div class="py-4">
                <x-filament::pagination :paginator="$products"  :page-options="[5, 10, 20, 50, 100]"  extreme-links :current-page-option-property="$perPage" />
            </div>
        </div>
    </div>

    <!-- Sidebar Cart -->
    <div class="md:col-span-1 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <!-- Cart Header -->
       <div class="flex justify-between items-center py-4 border-b border-gray-200 dark:border-gray-700 mb-4">
    @if(count($order_items) > 0)
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Cart</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ count($order_items) }} item</p>
        </div>
    @else
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Cart</h3>
    @endif
    
    <div class="flex gap-3"> <!-- Menggunakan gap-3 untuk jarak yang optimal -->
        @if(count($order_items) > 0)
            <x-filament::button 
                wire:click="clearOrder" 
                color="danger" 
                size="sm" 
                class="transition-all duration-200 hover:scale-105">
                <span>Clear</span>
            </x-filament::button>
        @endif
        <x-filament::modal width="5xl">
    <x-slot name="trigger">
        <x-filament::button 
        badge-color="danger"
            
            color="gray"
            size="sm">
             <x-slot name="badge">
        3
    </x-slot>
            <span>Hold Bill</span>
        </x-filament::button>
    </x-slot>

   {{$this->table}}
</x-filament::modal>
       
    </div>
</div>

        <!-- Total Section -->
        @if(count($order_items) > 0)
            <div class="bg-primary-50 dark:bg-primary-900/20 p-4 rounded-lg mb-4 border border-primary-200 dark:border-primary-700">
                <div class="text-center">
                    <p class="text-sm text-primary-600 dark:text-primary-400 font-medium">Total Payment</p>
                    <h3 class="text-xl font-bold text-primary-700 dark:text-primary-300">
                        Rp {{ number_format($this->calculateTotal(), 0, ',', '.') }}
                    </h3>
                </div>
            </div>
        @endif

        <!-- Cart Items -->
        <div class="space-y-3 max-h-80 overflow-y-auto">
            @forelse ($order_items as $item)
                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600 transition-all duration-200 hover:shadow-md">
                    <!-- Item Header with Quantity Controls Inline -->
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex-1 pr-2 flex items-center gap-2">
                            <x-filament::button 
                                wire:click="openDiscountModal({{ $item['product_id'] }})"
                                color="info" 
                                size="xs"
                                class="transition-all duration-200 hover:scale-105">
                                <x-heroicon-o-receipt-percent class="w-3 h-3" />
                            </x-filament::button>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $item['name'] }}</h3>
                                <p class="text-success-600 dark:text-success-300 text-xs font-medium">
                                    Rp {{ number_format($item['price'], 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Quantity Controls Inline -->
                        <div class="flex items-center gap-2">
                            <x-filament::button 
                                wire:click="decreaseQuantity({{ $item['product_id'] }})"
                                color="warning" 
                                size="sm">
                                <x-heroicon-o-minus class="w-3 h-3" />
                            </x-filament::button>
                            
                            <span class="px-3 py-1 bg-white dark:bg-gray-800 rounded border border-gray-300 dark:border-gray-500 font-semibold text-gray-900 dark:text-gray-100 min-w-[2.5rem] text-center">{{ $item['quantity'] }}</span>
                            
                            <x-filament::button 
                                wire:click="increaseQuantity({{ $item['product_id'] }})"
                                color="success" 
                                size="sm">
                                <x-heroicon-o-plus class="w-3 h-3" />
                            </x-filament::button>
                        </div>
                    </div>
                    
                    <!-- Subtotal -->
                    <div class="mt-2">
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">
                            Subtotal: Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            @empty
                <!-- Empty Cart State -->
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <x-heroicon-o-shopping-cart class="w-8 h-8 text-gray-400" />
                    </div>
                   <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Your cart is empty</p>
<p class="text-gray-400 dark:text-gray-500 text-xs mt-1">Add items to get started</p>
                </div>
            @endforelse
        </div>

        <!-- Form and Action Buttons -->
        @if(count($order_items) > 0)
            <form class="mt-6">
                {{ $this->form }}

                <div class="flex flex-col gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <!-- Hold Bill Button -->
                    <x-filament::button 
                        wire:click="openHoldBillModal" 
                        color="gray" 
                        icon="heroicon-o-clock"
                        size="lg" 
                        class="justify-center transition-all duration-200 hover:scale-105"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>Hold Bill</span>
                        <span wire:loading class="flex items-center">
                            <x-filament::loading-indicator class="w-4 h-4 mr-2" />
                            Processing...
                        </span>
                    </x-filament::button>

                    <!-- Payment Button -->
                    <x-filament::button 
                        wire:click="openPaymentModal" 
                        color="primary" 
                        icon="heroicon-o-credit-card"
                        size="lg" 
                        class="justify-center font-semibold transition-all duration-200 hover:scale-105 shadow-lg"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>Process Payment</span>
                        <span wire:loading class="flex items-center">
                            <x-filament::loading-indicator class="w-4 h-4 text-white mr-2" />
                            Processing...
                        </span>
                    </x-filament::button>
                </div>
            </form>
        @endif
    </div>
</div>