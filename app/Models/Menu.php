<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Menu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'order_by',
        'menu_method',
        'menu_icon',
        'parent_id',
    ];

    public function format()
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'order_by'    => $this->order_by,
            'menu_method' => $this->menu_method,
            'menu_icon'   => $this->menu_icon,
            'submenu'     => $this->getChildMenus(),
        ];
    }

    public function childmenus()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order_by', 'ASC');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function getChildMenus()
    {
        $userRoles = auth()->user()->roles->pluck('id');
        $child = DB::table('menu_role')->where('role_id', Auth::user()->roles()->first()->id)
            ->where('menu_id', $this->id)->get()->pluck('menu_id')->toArray();

        return Menu::whereHas('roles', function ($query) use ($userRoles) {
            $query->whereIn('roles.id', $userRoles);
        })->where('parent_id', '=', $child)->orderBy('order_by', 'ASC')->get()->map(function ($format) {
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
}
