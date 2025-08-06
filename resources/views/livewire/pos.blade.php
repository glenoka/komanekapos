<div class="grid grid-cols-1 dark:bg-gray-900 md:grid-cols-3 gap-4">
    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <!-- Bagian Kategori + Search -->
        <div class="flex gap-4 items-center justify-between mb-4">
            <div class="flex gap-4 overflow-x-auto pb-3 px-5 overflow-y-hidden flex-1">
                @foreach ($categories as $category)
                    <div class="relative">
                        <x-filament::button class="whitespace-nowrap mb-2"
                            wire:click="$set('activeCategory', {{ $category->id }})" :color="$activeCategory === $category->id ? 'primary' : 'info'" size="sm">
                            {{ $category->name }}
                        </x-filament::button>
                    </div>
                @endforeach
            </div>



        </div>
        <!-- Search Input -->
        <div class="flex-shrink-0 mb-3">
            <x-filament::input.wrapper>
                <x-filament::input type="search" wire:model.live.debounce.500ms="search" placeholder="Cari produk..."
                    icon="heroicon-o-magnifying-glass" class="h-9 text- mb-3" />
            </x-filament::input.wrapper>
        </div>
        <!-- Daftar Produk -->
        <div class="flex-grow mt-3">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 p-1">
                @foreach ($products as $product)
                    <x-filament::section
                        class="!p-3 !m-0 cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group"
                        wire:click="addToOrder({{ $product->id }})">

                        <div class="space-y-3">
                            <!-- Icon placeholder -->
                            <div class="w-full flex justify-center">
                                <div
                                    class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 transition-colors duration-200">
                                    <x-heroicon-o-cube
                                        class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-primary-500 dark:group-hover:text-primary-400" />
                                </div>
                            </div>

                            <!-- Product details -->
                            <div class="text-center space-y-1">
                                <h3
                                    class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-200">
                                    {{ $product->name ?? 'Nama Produk' }}
                                </h3>

                                <x-filament::badge color="primary" size="sm">
                                    Rp {{ number_format($product->price ?? 10000, 0, ',', '.') }}
                                </x-filament::badge>
                            </div>

                        </div>
                    </x-filament::section>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="py-4">
                <x-filament::pagination :paginator="$products" :page-options="[5, 10, 20, 50, 100, 'all']" :current-page-option-property="$perPage" />
            </div>
        </div>
    </div>

    <!-- Sidebar Cart -->
    <div class="md:col-span-1 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center py-4">
            @if(count($order_items)>0)
            <h3 class="text-lg font-semibold">Total:  Rp {{ number_format($this->calculateTotal(), 0, ',', '.') }}</h3>
            <x-filament::button wire:click="clearOrder" color="danger" size="sm" class="my-1.5">
                
                <span>Clear</span>
            </x-filament::button>
            @endif
        </div>
        @foreach ($order_items as $item)
            <div class="mb-4">
                <div class="flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow">
                    <div class="flex items-center">
                        <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                            alt="Product Image" class="w-10 h-10 object-cover rounded-lg mr-2">
                        <div class="px-2">
                            <h3 class="text-sm font-semibold">{{ $item['name'] }}</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-xs">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <x-filament::button wire:click="decreaseQuantity({{ $item['product_id'] }})"
                            color="warning">-</x-filament::button>
                        <span class="px-4">{{ $item['quantity'] }}</span>
                        <x-filament::button wire:click="increaseQuantity({{ $item['product_id'] }})"
                            color="success">+</x-filament::button>
                    </div>
                </div>
            </div>
        @endforeach
        <form>
            {{ $this->form }}

            <div class="flex justify-end gap-3 mt-6">
                <!-- Hold Bill Button -->
                <x-filament::button wire:click="openHoldBillModal" color="gray" icon="heroicon-o-clock"
                    icon-alias="hold-bill" size="lg" class="!px-6 !py-3 font-semibold"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>Hold Bill</span>
                    <span wire:loading>
                        <x-filament::loading-indicator class="w-5 h-5" />
                    </span>
                </x-filament::button>

                <!-- Payment Button -->
                <x-filament::button wire:click="openPaymentModal" color="primary" icon="heroicon-o-credit-card"
                    icon-alias="payment" size="lg" class="!px-8 !py-3 font-bold shadow-lg"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>Process Payment</span>
                    <span wire:loading>
                        <x-filament::loading-indicator class="w-5 h-5 text-white" />
                    </span>
                </x-filament::button>
            </div>
        </form>
    </div>


</div>
