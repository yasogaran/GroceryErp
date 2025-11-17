<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Component;

class SupplierForm extends Component
{
    public $supplierId;
    public $name = '';
    public $contact_person = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $city = '';
    public $credit_terms = 0;
    public $is_active = true;

    public $isEditMode = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'contact_person' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
        'city' => 'nullable|string|max:100',
        'credit_terms' => 'required|integer|min:0|max:365',
        'is_active' => 'boolean',
    ];

    public function mount($id = null)
    {
        if ($id) {
            $this->isEditMode = true;
            $this->supplierId = $id;
            $this->loadSupplier();
        }
    }

    public function loadSupplier()
    {
        $supplier = Supplier::findOrFail($this->supplierId);

        $this->name = $supplier->name;
        $this->contact_person = $supplier->contact_person;
        $this->email = $supplier->email;
        $this->phone = $supplier->phone;
        $this->address = $supplier->address;
        $this->city = $supplier->city;
        $this->credit_terms = $supplier->credit_terms;
        $this->is_active = $supplier->is_active;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'contact_person' => $this->contact_person,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'credit_terms' => $this->credit_terms,
            'is_active' => $this->is_active,
        ];

        if ($this->isEditMode) {
            $supplier = Supplier::findOrFail($this->supplierId);
            $supplier->update($data);
            session()->flash('success', 'Supplier updated successfully');
        } else {
            Supplier::create($data);
            session()->flash('success', 'Supplier created successfully');
        }

        return redirect()->route('suppliers.index');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.suppliers.supplier-form');
    }
}
