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
use App\Models\Sales;
use App\Models\SalesDetail;
use Filament\Forms\Form;

use Filament\Tables\Table;
use Livewire\WithPagination;
use Illuminate\Support\HtmlString;
use Filament\Tables\Columns\Column;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;

use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Support\Facades\Auth;

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

    public $tax = 11;
    public $tax_amount;
    public $discount = 0;
    public $discount_amount;
    public $grand_total;


    public $order_type;
    public $customer_id;
    public $number_table;
    public $activity;

    // Property untuk paymentForm
    public $sales_type;
    public $payment_method;
    public $notes;



    public function mount(): void
    {
        if (session()->has('orderItems')) {
            $this->order_items = session('orderItems');
        }
    }
    protected function getForms(): array
    {
        return [
            'paymentForm',
            'form'
        ];
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
        $this->grand_total = 0;
        $this->discount = 0;
        $this->discount_amount = 0;
        $this->sub_total = 0;
        $this->tax_amount = 0;

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
                                        'takeaway' => 'Take Away',
                                        'other' => 'Other'
                                    ])
                                    ->columnSpan(1),

                                Select::make('number_table')
                                    ->label('Table Number')
                                    ->options([
                                        //create tabel 1-21
                                        '1' => '1',
                                        '2' => '2',
                                        '3' => '3',
                                        '4' => '4',
                                    ])
                                    ->visible(fn(Get $get) => $get('order_type') === 'dine_in')
                                    ->required(fn(Get $get) => $get('order_type') === 'dine_in')
                                    ->columnSpan(1),
                            ]),
                        Select::make('activity')
                            ->label('Activity')
                            ->options([
                                'breakfast' => 'Breakfast',
                                'lunch' => 'Lunch',
                                'dinner' => 'Dinner',
                                'afternoon_tea' => 'Afternoon Tea'
                            ]),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::pluck('name', 'id'))
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
                            ->hint(fn() => '- Rp. ' . $this->discount_amount)
                            ->hintColor('success')
                            ->hintIcon('heroicon-s-tag')
                            ->columnSpan(1),

                        TextInput::make('tax')
                            ->label('Tax Amount')
                            ->numeric()
                            ->suffix('%')
                            ->readOnly()
                            ->live()
                            ->hint(fn() => '+ Rp. ' . $this->tax_amount)
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
                    ->default(fn() => $this->calculateTotal())
                    ->extraAttributes(['class' => 'font-bold text-lg'])
                    ->columnSpanFull(),
            ]);
    }


    public function paymentForm(Form $form): Form
    {
        return $form

            ->schema([
                // Sales Type Section
                Section::make('Transaction Details')
                    ->description('Configure transaction type and payment method')
                    ->schema([
                        Select::make('sales_type')
                            ->label('Sales Type')
                            ->required()
                            ->live()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select sales type...')
                            ->options([
                                'regular' => 'Regular Sale',
                                'complimentary' => 'Complimentary',
                                'owner_guest' => 'Owner Guest',
                                'staff_meal' => 'Staff Meal',
                                'vip_guest' => 'VIP Guest',
                                'business_entertainment' => 'Business Entertainment',
                                'banquet/wedding' => 'Banquet/Wedding',
                            ])

                            ->native(false)
                            ->suffixIcon('heroicon-o-tag')
                            ->columnSpanFull(),

                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->placeholder('Select payment method...')
                            ->options([
                                'cash' => 'Cash',
                                'card' => 'Credit/Debit Card',
                                'room_charge' => 'Room Charge',
                                'complimentary' => 'Complimentary',
                                'qris' => 'QRIS',

                            ])
                            ->native(false)
                            ->suffixIcon('heroicon-o-credit-card')
                            ->reactive()
                            ->afterStateUpdated(
                                fn(callable $set, $state) =>
                                $state === 'complimentary'
                                    ? $set('sales_type', 'complimentary')
                                    : null
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->persistCollapsed(),

                // Payment Details Section (conditional based on payment method)

                Section::make('Additional Information')
                    ->description('Optional notes and comments')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                          
                            ->placeholder('Add any special notes or instructions...')
                            ->rows(3)
                            ->required(fn(Get $get) => $get('sales_type') === 'complimentary')
                            ->maxLength(500)
                            ->columnSpanFull(),


                    ])
                    ->collapsible()
                    ->persistCollapsed()
                    ->collapsed(),

            ])
            ->columns(1);
    }

    // Helper methods
    public function calculateSubtotal()
    {
        $total = 0;
        foreach ($this->order_items as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        $this->sub_total = $total; //sebelum pajak 
        return $total;
    }

    public function calculateTax()
    {
        return $this->tax_amount = $this->calculateSubtotal() * ($this->tax / 100);
    }

    public function calculateTotal()
    {
        $subtotal = $this->calculateSubtotal();
        $tax = $this->calculateTax();
        if ($this->discount > 0) {
            $this->discount_amount = $this->sub_total * $this->discount / 100;
        } else {
            $this->discount_amount = 0;
        }
        return $this->grand_total = $subtotal + $tax - $this->discount_amount;
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
        Notification::make('addToOrder_' . now()->timestamp)
            ->title('Add item Success')
            ->success()
            ->send();
    }

    public function loadOrderItem($orderItems)
    {
        $this->order_items = $orderItems;
        session()->put('orderItems', $this->order_items);
    }
    public function processPayment()
    {
     
       

        // Validasi payment form
        $paymentData = $this->validate([
            'sales_type' => 'required',
            'payment_method' => 'required',
            'notes' => 'required_if:sales_type,complimentary'
        ]);

        // $dataTest=[
        //     'customer_id' => $this->customer_id,
        //     'sale_date' => now(),
        //     'table_no' => $this->number_table,
        //     'sales_type' => $this->sales_type,
        //     'order_type' => $this->order_type,
        //     'subtotal' => $this->sub_total,
        //     'tax_amount' => $this->tax_amount,
        //     'discount_amount' => $this->discount_amount,
        //     'total_amount' => $this->grand_total,
        //     'payment_method' => $this->payment_method,
        //     'total_items' => count($this->order_items),
        //     'status' => 'completed',
        //     'user_id' => Auth::user()->id,
        //     'notes' => $this->notes,
        // ];
        // dd($dataTest);
       
        $sales = Sales::create([
            'customer_id' => $this->customer_id,
            'sale_date' => now(),
            'table_no' => $this->number_table,
            'sales_type' => $this->sales_type,
            'order_type' => $this->order_type,
            'subtotal' => $this->sub_total,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->grand_total,
            'payment_method' => $this->payment_method,
            'total_items' => count($this->order_items),
            'status' => 'completed',
            'user_id' => Auth::user()->id,
            'notes' => $this->notes,
        ]);

        foreach ($this->order_items as $item) {

            $detailSales = SalesDetail::create([
                'sale_id' => $sales->id,
                'product_id' => $item['product_id'],
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'original_price' => $item['price'],
                'discount_amount' => 0,
                'total_price' => $item['price'] * $item['quantity'],
                'is_complimentary' => false,
            ]);
        }
     
        Notification::make('payment')
            ->title('Paymanet Success')
            ->success()
            ->send();

        $this->order_items = [];
        $this->sub_total = 0;
        $this->tax_amount = 0;
        $this->discount_amount = 0;
        $this->grand_total = 0;
       
     $this->order_type='';
     $this->customer_id='';
     $this->number_table='';
     $this->activity='';      
       

       
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
