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

use Filament\Tables\Table;
use Livewire\WithPagination;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\Column;
use Filament\Forms\Components\Select;
use Filament\Forms\Contracts\HasForms;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class Pos extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    // use WithPagination;

    public $activeCategory = 0; // Default category
    public $search = '';
    public int | string $perPage = 10;
    public $sub_total;
    public $order_items = [];
    public $order_type;
    public $tax = 11;
    public $tax_amount;
    public $discount = 0;
    public $discount_amount;
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
        $this->discount_amount=0;
        $this->sub_total=0;
        $this->tax_amount=0;
        Notification::make()
            ->title('Cart cleared successfully')
            ->success()
            ->send();
    }

 public function table(Table $table): Table
{
    return $table
        ->query(Product::query()->limit(5)) // Menambahkan limit(5)
        ->columns([
            TextColumn::make('id')->sortable(),
            TextColumn::make('name')->sortable(),
            TextColumn::make('price')->sortable(),
        ]);
}
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
                     
                                TextInput::make('sub_total')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->columnSpan(1),
    
                                   
                                TextInput::make('discount')
                                    ->label('Discount Amount')
                                    ->numeric()
                                   ->suffix('%')
                                    ->live()
                                    ->default(0)
                                    ->hint(fn () => '- Rp. '.$this->discount_amount)
                                    ->hintColor('success')
                                    ->hintIcon('heroicon-s-tag')
                                    ->columnSpan(1),
    
                                TextInput::make('tax')
                                    ->label('Tax Amount')
                                    ->numeric()
                                  ->suffix('%')
                                  ->readOnly()
                                    ->live()
                                    ->hint(fn () => '+ Rp. '.$this->tax_amount)
                                    ->hintColor('danger')
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
        $total = 0;
        foreach ($this->order_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        $this->sub_total = $total;//sebelum pajak 
        return $total;
    }
    
    public function calculateTax()
    {
        return $this->tax_amount= $this->calculateSubtotal() * ($this->tax / 100);
    }
    
    public function calculateTotal()
    {
        $subtotal = $this->calculateSubtotal();
        $tax = $this->calculateTax();
        if($this->discount>0){
            $this->discount_amount=$this->sub_total*$this->discount/100;
        }else{
            $this->discount_amount=0;
        }
        return $this->grand_total=$subtotal + $tax - $this->discount_amount;
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
