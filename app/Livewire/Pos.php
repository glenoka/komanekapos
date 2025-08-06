<?php

namespace App\Livewire;

use Log;
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
    public $total_price;
    public $order_items = [];
    public $order_type;
    public $tax = 21;
    public $discount = 0;
    public $grand_total;



    // Tambahkan property untuk form data
    public ?array $data = [];

    public function mount(): void
    {
        if (session()->has('orderItems')) {
            $this->order_items = session('orderItems');
        }
    }

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

    private function getSessionKey(): string
    {
        return 'orderItem'  . '_' . session()->getId();
    }


    public function increaseQuantity($productId)
    {
        $product = Product::find($productId);

        foreach ($this->order_items as $key => $item) {
            if ($item['product_id'] == $product->id) {
                $this->order_items[$key]['quantity']++;
            }
        }
        session()->put('orderItems', $this->order_items);
    }

    public function decreaseQuantity($productId)
    {
        $product = Product::find($productId);

        foreach ($this->order_items as $key => $item) {
            if ($item['product_id'] == $product->id) {
                if ($this->order_items[$key]['quantity'] > 1) {

                    $this->order_items[$key]['quantity']--;
                } else {
                    unset($this->order_items[$key]);
                    $this->order_items = array_values($this->order_items);
                }
                break;
            }
        }
        session()->put('orderItems', $this->order_items);
    }

    public function clearOrder()
    {
        $this->order_items = [];
        session()->forget('orderItems');
        $this->grand_total=0;
        $this->discount=0;
        Notification::make()
            ->title('Cart cleared successfully')
            ->success()
            ->send();
    }

    // public function calculateTotal()
    // {
    //     $total = 0;
    //     foreach ($this->order_items as $item) {
    //         $total += $item['price'] * $item['quantity'];
    //     }
    //     $this->total_price = $total;//sebelum pajak 
    //     $this->grand_total = $total + ($total * $this->tax / 100);

    //     if($this->discount > 0){
    //         $this->grand_total = $this->grand_total - ($this->grand_total * $this->discount / 100);
    //     }
    //     return  $this->grand_total;//sesudah pajak dan diskon 
    // }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order Summary')
                    ->schema([
                        // Customer and Order Info
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Select::make('order_type')
                                    ->label('Order Type')
                                    ->required()
                                    ->live()
                                    ->options([
                                        'dine_in' => 'Dine In',
                                        'room_service' => 'Room Service',
                                        'take_away' => 'Take Away',
                                        'other' => 'Other'
                                    ])
                                    ->columnSpan(1),
    
                                Select::make('tabel_no')
                                    ->label('Table Number')
                                    ->options(range(1, 10))
                                    ->options(collect(range(1, 10))->mapWithKeys(fn ($num) => [$num => "Table $num"]))
                                    ->visible(fn (Get $get) => $get('order_type') === 'dine_in')
                                    ->required(fn (Get $get) => $get('order_type') === 'dine_in')
                                    ->columnSpan(1),
                            ]),
    
                        Select::make('customer_id')
                            ->label('Customer')
                      ->options(Customer::pluck('name','id'))
                            ->searchable()
                            ->preload()
                           
                            ->required()
                            ->columnSpanFull(),
                    ]),
    
                // Payment Summary Section
                Forms\Components\Section::make('Payment Summary')
                    ->schema([
                     
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->default(fn () => $this->calculateSubtotal())
                                    ->columnSpan(1),
    
                                TextInput::make('discount')
                                    ->label('Discount Amount')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->live()
                                    ->default(0)
                                    ->hint(fn () => $this->discount > 0 ? 
                                        'Discount: '.number_format($this->discount, 0, ',', '.').' ('.round(($this->discount/$this->calculateSubtotal())*100, 2).'%)' : 
                                        'Enter discount amount')
                                    ->hintIcon('heroicon-s-tag')
                                    ->columnSpan(1),
    
                                TextInput::make('tax_amount')
                                    ->label('Tax Amount')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->live()
                                    ->default(fn () => $this->calculateTax())
                                    ->hint(fn () => 'Tax Rate: '.$this->tax.'%')
                                    ->hintIcon('heroicon-s-receipt-percent')
                                    ->columnSpan(1),
                            ]),
    
                        // Grand Total
                        TextInput::make('grand_total')
                            ->label('Grand Total')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled()
                            ->default(fn () => $this->calculateTotal())
                            ->extraAttributes(['class' => 'font-bold text-lg'])
                            ->columnSpanFull(),
                        ]);
            
    }
    
    // Helper methods
    public function calculateSubtotal()
    {
        return collect($this->order_items)->sum(fn ($item) => $item['price'] * $item['quantity']);
    }
    
    public function calculateTax()
    {
        return $this->calculateSubtotal() * ($this->tax / 100);
    }
    
    public function calculateTotal()
    {
        $subtotal = $this->calculateSubtotal();
        $tax = $this->calculateTax();
        return $subtotal + $tax - $this->discount;
    }
    public function addToOrder($productId)
    {
        $product = Product::find($productId);
        $existingItemkey = null;

        foreach ($this->order_items as $key => $item) {
            if ($item['product_id'] == $product->id) {
                $existingItemkey = $key;
                break;
            }
        }

        if ($existingItemkey !== null) {
            $this->order_items[$existingItemkey]['quantity']++;
        } else {
            $this->order_items[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
            ];
        }

        session()->put('orderItems', $this->order_items);
        Notification::make('addToOrder')
            ->title('Add item Success')
            ->success()
            ->send();
    }

    public function loadOrderItem($orderItems)
    {
        $this->order_items = $orderItems;
        session()->put('orderItems', $this->order_items);
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
