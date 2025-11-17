<?php

namespace App\Livewire\Notifications;

use App\Services\AlertService;
use Livewire\Component;
use Livewire\Attributes\On;

class NotificationBell extends Component
{
    public $unreadCount = 0;
    public $notifications = [];
    public $showDropdown = false;

    protected $alertService;

    public function boot(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        if (auth()->check()) {
            $this->unreadCount = $this->alertService->getUnreadCount(auth()->id());
            $this->notifications = $this->alertService->getRecentNotifications(auth()->id(), 10);
        }
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;

        if ($this->showDropdown) {
            $this->loadNotifications();
        }
    }

    public function markAsRead($notificationId)
    {
        $this->alertService->markAsRead($notificationId, auth()->id());
        $this->loadNotifications();
    }

    public function markAllAsRead()
    {
        $this->alertService->markAllAsRead(auth()->id());
        $this->loadNotifications();
        session()->flash('success', 'All notifications marked as read.');
    }

    #[On('notification-created')]
    public function refreshNotifications()
    {
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notifications.notification-bell');
    }
}
