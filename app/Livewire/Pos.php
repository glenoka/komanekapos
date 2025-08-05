<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;

class Pos extends Component
{
    use WithPagination;
    
    public $activeCategory= 0; // Default category
    public $search='';
     public int | string $perPage = 20;

    
   
    public function updatedSearch($value)
{
    if (!empty($value)) {
        $this->activeCategory = 0; // Otomatis reset kategori saat mencari
    }
    $this->resetPage();
}
     public function updatedActiveCategory()
    {
        $this->resetPage();
    }

    

    public function render()
    {
         $products = Product::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->activeCategory > 0, function($q) {
                $q->where('category_id', $this->activeCategory);
            })
            ->with('category') // Eager load kategori untuk optimasi
            ->paginate($this->perPage === 'all' ? Product::count() : $this->perPage);
    
$categories = Category::select('id', 'name')->get();
    $categories->prepend((object)['id' => 0, 'name' => 'All']); // Tambahkan di awal
        

        return view('livewire.pos',[
            'products' => $products
            ,
            'categories' => $categories,
        ]);
    }
}
