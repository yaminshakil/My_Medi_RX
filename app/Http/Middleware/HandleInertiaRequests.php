<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    protected $menus = '';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        if (Auth::check()) {
            $userRoles = auth()->user()->roles->pluck('id');

            $this->menus = Menu::whereHas('roles', function ($query) use ($userRoles) {
                $query->whereIn('roles.id', $userRoles);
            })->where('parent_id', '=', null)->orderBy('order_by', 'ASC')->get()->map(function ($format) {
                return [
                    'id'          => $format->id,
                    'title'       => $format->name,
                    'href'        => $format->slug,
                    'order_by'    => $format->order_by,
                    'menu_method' => $format->menu_method,
                    'icon'        => $format->menu_icon,
                    'submenu'     => $format->getChildMenus(),
                    'roles'       => $format->roles,
                ];
            });
        }

        return [
            ...parent::share($request),
            'name'  => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth'  => [
                'user'        => $request->user(),
                'permissions' => fn () => $request->user()?->getAllPermissions()->pluck('name') ?? [],
            ],
            'ziggy' => fn (): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash'       => [
                'message' => [
                    'success' => $request->session()->get('success'),
                    'error'   => $request->session()->get('error'),
                ],
            ],
            'menus'         => $this->menus,
            'notifications' => fn () => [
                'unread_count' => Auth::check()
                    ? Auth::user()->unreadNotifications()->count()
                    : 0,

                'items' => Auth::check() && Auth::user()->hasRole('Admin')
                    ? Auth::user()
                        ->unreadNotifications()
                        ->latest()
                        ->take(10)
                        ->get()
                        ->map(fn ($notification) => [
                            'id'         => $notification->id,
                            'title'      => $notification->data['title'],
                            'message'    => $notification->data['message'],
                            'url'        => $notification->data['url'],
                            'created_at' => $notification->created_at,
                        ])
                    : [],
            ],
        ];
    }
}
