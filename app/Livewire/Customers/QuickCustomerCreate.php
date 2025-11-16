<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Component;

class QuickCustomerCreate extends Component
{
    public $name = '';
    public $phone = '';

    protected $rules = [
        'name' => 'required|max:255',
        'phone' => 'required|unique:customers,phone|max:20',
    ];

    /**
     * Create a new customer
     */
    public function createCustomer()
    {
        $this->validate();

        $customer = Customer::create([
            'customer_code' => Customer::generateCustomerCode(),
            'name' => $this->name,
            'phone' => $this->phone,
            'is_active' => true,
        ]);

        session()->flash('success', 'Customer created successfully!');

        $this->dispatch('customer-created');
        $this->reset(['name', 'phone']);
    }

    /**
     * Render the component
     */
    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.customers.quick-customer-create');
    }
}
