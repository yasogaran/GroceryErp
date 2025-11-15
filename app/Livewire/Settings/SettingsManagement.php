<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class SettingsManagement extends Component
{
    use WithFileUploads;

    public $activeTab = 'general';
    public $settings = [];
    public $logo;
    public $currentLogo;

    protected $listeners = ['settingsSaved' => '$refresh'];

    public function mount()
    {
        $this->loadSettings();
        $this->currentLogo = settings('shop_logo');
    }

    public function loadSettings()
    {
        $allSettings = Setting::all();

        foreach ($allSettings as $setting) {
            $this->settings[$setting->key] = $setting->value;
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function save()
    {
        $this->validate([
            'settings.shop_name' => 'required|string|max:255',
            'settings.shop_email' => 'required|email|max:255',
            'settings.shop_phone' => 'required|string|max:20',
            'settings.shop_address' => 'required|string|max:500',
            'settings.low_stock_threshold' => 'required|integer|min:0',
            'settings.expiry_alert_days' => 'required|integer|min:0',
            'settings.currency_symbol' => 'required|string|max:10',
            'settings.date_format' => 'required|string|max:50',
            'logo' => 'nullable|image|max:2048', // 2MB max
        ]);

        // Handle logo upload
        if ($this->logo) {
            // Delete old logo if exists
            if ($this->currentLogo && Storage::disk('public')->exists($this->currentLogo)) {
                Storage::disk('public')->delete($this->currentLogo);
            }

            // Store new logo
            $logoPath = $this->logo->store('logos', 'public');
            $this->settings['shop_logo'] = $logoPath;
            $this->currentLogo = $logoPath;
        }

        // Save all settings
        foreach ($this->settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                $setting->value = $value;
                $setting->save();
            }
        }

        // Clear settings cache
        Setting::clearCache();

        session()->flash('success', 'Settings saved successfully.');
        $this->dispatch('settingsSaved');
    }

    public function removeLogo()
    {
        if ($this->currentLogo && Storage::disk('public')->exists($this->currentLogo)) {
            Storage::disk('public')->delete($this->currentLogo);
        }

        $this->settings['shop_logo'] = '';
        $this->currentLogo = null;
        $this->logo = null;

        $setting = Setting::where('key', 'shop_logo')->first();
        if ($setting) {
            $setting->value = '';
            $setting->save();
        }

        Setting::clearCache();
        session()->flash('success', 'Logo removed successfully.');
    }

    public function render()
    {
        $groupedSettings = Setting::getAllGrouped();

        return view('livewire.settings.settings-management', [
            'groupedSettings' => $groupedSettings,
        ]);
    }
}
