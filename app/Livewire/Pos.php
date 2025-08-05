<?php

namespace App\Livewire;

use Filament\Forms;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Livewire\Component;

use App\Models\Category;
use App\Models\Customer;
use Filament\Forms\Form;
use Livewire\WithPagination;

use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;

class Pos extends Component implements HasForms
{
    use InteractsWithForms;
    use WithPagination;

    public $activeCategory = 0; // Default category
    public $search = '';
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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Form Checkout')
                    ->schema([
                       Select::make('order_type')
    ->label('Order Type')
    ->live() // Tambahkan ini
    ->options([
        'dine_in' => 'Dine In',
        'room_service' => 'Room Service', 
        'take_away' => 'Take Away',
        'other' => 'Other'
    ]),

Select::make('tabel_no')
    ->label('No Table')
    ->visible(fn (Get $get): bool => $get('order_type') === 'dine_in')
    ->options(['1', '2', '3', '4'])
    ->reactive(), // Tambahkan ini jika perlu
                        Select::make('nameCustomer')
                            ->label('Customer Name')
                            ->options(Customer::pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
            ]);
    }


    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn($q) => $q->search($this->search))
            ->when($this->activeCategory > 0, function ($q) {
                $q->where('category_id', $this->activeCategory);
            })
            ->with('category') // Eager load kategori untuk optimasi
            ->paginate($this->perPage === 'all' ? Product::count() : $this->perPage);

        $categories = Category::select('id', 'name')->get();
        $categories->prepend((object)['id' => 0, 'name' => 'All']); // Tambahkan di awal


        return view('livewire.pos', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
