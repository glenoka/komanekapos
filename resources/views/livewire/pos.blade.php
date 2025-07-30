<div>
    <div class="grid grid-cols-1 dark:bg-gray-900 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <div class="mb-4">
                <nav class="flex-shrink-0 mb-8">
                    <div class="relative">
                        <!-- Gradient fade edges for better scroll indication -->
                        <div class="absolute left-0 top-0 bottom-0 w-6 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
                        <div class="absolute right-0 top-0 bottom-0 w-6 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>
                        
                        <!-- Scrollable container -->
                        <div class="flex gap-4 overflow-x-auto pb-3 px-6 overflow-y-hidden">
                            @foreach ($categories as $category)
                            <button
                                wire:click="$set('activeCategory', {{ $category->id }})"
                                class="relative px-6 py-3 text-sm font-semibold rounded-full transition-all duration-300 whitespace-nowrap transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-300
                                    {{ $activeCategory === $category->id 
                                        ? 'shadow-lg' 
                                        : 'shadow-md hover:shadow-lg border border-gray-200' }}"
                                style="{{ $activeCategory === $category->id 
                                    ? 'background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white;' 
                                    : 'background: white; color: #374151;' }}"
                                onmouseover="{{ $activeCategory !== $category->id 
                                    ? 'this.style.background=\'linear-gradient(135deg, #dbeafe, #bfdbfe)\'; this.style.color=\'#1d4ed8\'; this.style.borderColor=\'#3b82f6\';' 
                                    : '' }}"
                                onmouseout="{{ $activeCategory !== $category->id 
                                    ? 'this.style.background=\'white\'; this.style.color=\'#374151\'; this.style.borderColor=\'#e5e7eb\';' 
                                    : '' }}"
                            >
                                @if($activeCategory === $category->id)
                                <span class="absolute -top-1 -right-1 w-3 h-3 rounded-full animate-pulse" 
                                      style="background-color: #fbbf24; box-shadow: 0 1px 3px rgba(0,0,0,0.2);"></span>
                                @endif
                                {{ $category->name }}
                            </button>
                            @endforeach
                        </div>
                    </div>
                </nav>
            </div>
            <div class="flex-grow">
                <div class="grid grid-cols-8 sm:grid-cols-3 md:grid-cols-8 lg:grid-cols- gap-4">
                    <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow cursor-pointer">
                        <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                            alt="Product Image" class="w-full h-16 object-cover rounded-lg mb-2">
                        <h3 class="text-sm font-semibold">Nama</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-xs">Rp. 10.000</p>
                        <p class="text-gray-600 dark:text-gray-400 text-xs">Stok: 3</p>
                    </div>
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
                        <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Product Image"
                            class="w-10 h-10 object-cover rounded-lg mr-2">
                        <div class="px-2">
                            <h3 class="text-sm font-semibold">Nama</h3>
                            <p class="text-gray-600 dark:text-gray-400 text-xs">Rp 10.000</p>
                        </div>
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
</div>
