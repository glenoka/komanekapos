<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;

class PosPage extends Component
{


    
    public collection $products;
    public collection $categories;
    public string $activeCategory='16';
    public string $searchTerm='';

    //cart
    public array $cart=[];
    public ?string $customerName='';
    public string $orderType='';
    public float $overallDiscount=0;

    // hold bill
    public array $heldBills=[];
    public ?string $restoredBillId=null;

    // Properti untuk mengontrol modal
    public bool $showDiscountModal = false;
    public bool $showPaymentModal = false;
    public bool $showHeldBillsModal = false;
    public ?int $discountTargetId = null; // null untuk diskon keseluruhan
    public float $discountValue = 0;

    protected $rules = [
        'discountValue' => 'required|numeric|min:0|max:100',
    ];

    //Modal Overall Diskon 
    public $ModalOverallDiscount = false;
    public $discount_overall;



    public function mount(): void
    {
        $this->loadProducts();
    }
public function openModal()
{
    $this->ModalOverallDiscount = true;
}

public function closeModal()
{
    $this->ModalOverallDiscount = false;
}
    public function loadProducts(): void
    {
        if (class_exists(Product::class) && Product::count() > 0) {
            $this->products = Product::query()
                ->when($this->activeCategory !== 'Semua Menu', fn ($q) => $q->where('category_id', $this->activeCategory))
                ->when($this->searchTerm, fn ($q) => $q->where('name', 'like', '%' . $this->searchTerm . '%'))
                ->get();

                $this->categories = Category::select('id', 'name')->get();
        } 
    }

    public function updated($propertyName): void
    {
        if (in_array($propertyName, ['activeCategory', 'searchTerm'])) {
            $this->loadProducts();
        }
    }

    public function addToCart(int $productId): void
    {
        $product = $this->findProductById($productId);
        if (!$product) return;

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
        } else {
            $this->cart[$productId] = [
                'productId' => $product['id'],
                'name'      => $product['name'],
                'price'     => $product['price'],
                'quantity'  => 1,
                'discount'  => 0,
            ];
        }
    }

    public function updateQuantity(int $productId, int $change): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] += $change;
            if ($this->cart[$productId]['quantity'] < 1) {
                unset($this->cart[$productId]);
            }
        }
    }

    public function holdBill(): void
    {
        if (empty($this->cart)) return;

        $billId = 'BILL-' . time();
        $this->heldBills[$billId] = [
            'id' => $billId,
            'customerName' => $this->customerName ?: 'Pelanggan',
            'orderType' => $this->orderType,
            'cart' => $this->cart,
            'overallDiscount' => $this->overallDiscount,
        ];

        $this->resetOrderState();
        Notification::make()->title('Bill berhasil di-hold!')->success()->send();
    }

    public function restoreBill(string $billId): void
    {
        if (!isset($this->heldBills[$billId])) return;

        $bill = $this->heldBills[$billId];
        $this->customerName = $bill['customerName'];
        $this->orderType = $bill['orderType'];
        $this->cart = $bill['cart'];
        $this->overallDiscount = $bill['overallDiscount'];
        $this->restoredBillId = $billId;

        unset($this->heldBills[$billId]);
        $this->showHeldBillsModal = false;
    }

     public function showDiscountModal(?int $productId = null): void
    {
        $this->discountTargetId = $productId;
        $this->discountValue = $productId
            ? $this->cart[$productId]['discount']
            : $this->overallDiscount;
        $this->showDiscountModal = true;
    }

    public function applyDiscount(): void
    {
        $this->validate();
        if ($this->discountTargetId) {
            $this->cart[$this->discountTargetId]['discount'] = $this->discountValue;
        } else {
            $this->overallDiscount = $this->discountValue;
        }
        $this->showDiscountModal = false;
        Notification::make()->title('Diskon diterapkan!')->success()->send();
    }

    public function resetOrderState(): void
    {
        $this->reset(['cart', 'customerName', 'orderType', 'overallDiscount', 'restoredBillId']);
    }

     public function getSubtotalProperty(): float
    {
        return collect($this->cart)->sum(function ($item) {
            $itemTotal = $item['price'] * $item['quantity'];
            return $itemTotal * (1 - $item['discount'] / 100);
        });
    }

     /**
     * Computed property untuk menghitung nilai diskon keseluruhan.
     */
    public function getDiscountValueProperty(): float
    {
        return $this->subtotal * ($this->overallDiscount / 100);
    }

     /**
     * Computed property untuk menghitung pajak.
     */
    public function getTaxProperty(): float
    {
        return ($this->subtotal - $this->discountValue) * 0.11;
    }

    /**
     * Computed property untuk menghitung total akhir.
     */
    public function getTotalProperty(): float
    {
        return ($this->subtotal - $this->discountValue) + $this->tax;
    }

    public function processPayment(): void
    {
        // Di sini Anda akan menambahkan logika untuk menyimpan transaksi ke database.
        // Contoh: Sale::create([...]);

        Notification::make()
            ->title('Pembayaran Berhasil!')
            ->body('Transaksi telah disimpan.')
            ->success()
            ->send();

        $this->resetOrderState();
        $this->showPaymentModal = false;
    }


   /**
     * Render komponen ke view.
     */
    public function render()
    {
        return view('livewire.pos-page');
    }

    /**
     * Helper untuk mencari produk berdasarkan ID.
     */
    private function findProductById(int $productId)
    {
        // Cek di collection produk yang sudah dimuat
        $product = $this->products->firstWhere('id', $productId);
        if ($product) return $product;
        
        // Jika tidak ada, coba cari di database (fallback)
        if (class_exists(Product::class)) {
            return Product::find($productId);
        }
        
        return null;
    }
}
