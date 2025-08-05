<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use Illuminate\Support\Collection;

class Pos extends Component
{
    public collection $products;
    public collection $categories;

    public $activeCategory=0;
    public $searchTerm;

    public function mount(){

        $this->loadProducts();
    }
    public function loadProducts(): void
    {
        if (class_exists(Product::class) && Product::count() > 0) {
            $this->products = Product::query()
                ->when($this->activeCategory !== 'Semua Menu', fn ($q) => $q->where('category_id', $this->activeCategory))
                ->when($this->searchTerm, fn ($q) => $q->where('name', 'like', '%' . $this->searchTerm . '%'))
                ->get();

               // Tambahkan "Semua Menu" ke awal list kategori
            $allCategories = collect([
                (object)['id' => 0, 'name' => 'Semua Menu']
            ]);
            
            $this->categories = $allCategories->merge(
                Category::select('id', 'name')->get()
            );
           
        } 
    }

    public function render()
    {
        return view('livewire.pos');
    }
}
