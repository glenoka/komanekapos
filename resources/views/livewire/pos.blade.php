<div class="grid grid-cols-1 dark:bg-gray-900 md:grid-cols-3 gap-4">
    <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
      
    <div class="flex gap-4 overflow-x-auto pb-3 px-5 overflow-y-hidden">
        @foreach ($categories as $category)
            <div class="relative">
                <x-filament::button 
                    wire:click="$set('activeCategory', {{ $category->id }})"
                    :color="$activeCategory === $category->id ? 'primary' : 'info'"
                    size="sm"
                   >
                    {{ $category->name }}
                </x-filament::button>
            </div>
        @endforeach
    </div>
    <div class="flex-grow">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 p-1">
            @foreach($products as $product)
            <x-filament::section 
                class="!p-3 !m-0 cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group"
                wire:click="selectProduct({{ $product->id }})">
                
                <div class="space-y-3">
                    <!-- Icon placeholder -->
                    <div class="w-full flex justify-center">
                        <div class="w-10 h-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/30 transition-colors duration-200">
                            <x-heroicon-o-cube class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-primary-500 dark:group-hover:text-primary-400" />
                        </div>
                    </div>
                    
                    <!-- Product details -->
                    <div class="text-center space-y-1">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors duration-200">
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
    </div>
    </div>
    <div class="md:col-span-1 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
        
        <div class="py-4">
            <h3 class="text-lg font-semibold text-center">Total: Rp 25.0000</h3>
        </div>
        <div class="mb-4">
            <div class="flex justify-between items-center bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow">
                <div class="flex items-center">
                    {{ $activeCategory  }}
                    @forelse($products as $product)
                        <div wire:click="" class="bg-white rounded-xl shadow-md p-3 flex flex-col cursor-pointer hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
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
                <div class="flex items-center">
                    <x-filament::button color="warning">-</x-filament::button>
                    <span class="px-4">1</span>
                    <x-filament::button color="success">+</x-filament::button>
                </div>
            </div>
        </div>
       
        <div class="mt-2">

        </div>
    </div>
</div>