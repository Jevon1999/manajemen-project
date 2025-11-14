<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Services\UserRoleService;
use Illuminate\Support\Facades\Auth;

class SidebarComposer
{
    protected $userRoleService;

    public function __construct(UserRoleService $userRoleService)
    {
        $this->userRoleService = $userRoleService;
    }

    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        if (Auth::check()) {
            $roleData = $this->userRoleService->getUserRoleData();
            $menuItems = $this->userRoleService->getMenuItems();

            $view->with([
                'userRoleData' => $roleData,
                'sidebarMenuItems' => $menuItems,
                'userPermissions' => $roleData['permissions']
            ]);
        }
    }
}