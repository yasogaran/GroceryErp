<?php

namespace App\Livewire\Offers;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Offer;

class OfferManagement extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public $statusFilter = 'active';
    public $typeFilter = '';

    public function toggleActive($offerId)
    {
        $offer = Offer::find($offerId);

        if (!$offer) {
            session()->flash('error', 'Offer not found');
            return;
        }

        $offer->update(['is_active' => !$offer->is_active]);

        session()->flash('success', 'Offer status updated');
    }

    public function deleteOffer($offerId)
    {
        $offer = Offer::find($offerId);

        if (!$offer) {
            session()->flash('error', 'Offer not found');
            return;
        }

        $offer->delete();

        session()->flash('success', 'Offer deleted successfully');
    }

    public function updatingSearchTerm()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = Offer::with('creator', 'products', 'categories');

        if ($this->statusFilter === 'active') {
            $query->active();
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if ($this->typeFilter) {
            $query->where('offer_type', $this->typeFilter);
        }

        if ($this->searchTerm) {
            $query->where('name', 'like', '%' . $this->searchTerm . '%');
        }

        $offers = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.offers.offer-management', [
            'offers' => $offers,
        ]);
    }
}
