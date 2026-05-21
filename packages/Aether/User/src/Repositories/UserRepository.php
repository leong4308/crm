<?php

namespace Aether\User\Repositories;

use Aether\Core\Eloquent\Repository;

class UserRepository extends Repository
{
    /**
     * Campos buscables
     */
    protected $fieldSearchable = [
        'name',
        'email',
        'status',
        'view_permission',
        'role_id',
    ];

    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\User\Contracts\User';
    }

    /**
     * Esta función devolverá los ID de usuario de los grupos de usuarios actuales.
     *
     * @return array
     */
    public function getCurrentUserGroupsUserIds()
    {
        $userIds = $this->scopeQuery(function ($query) {
            return $query->select('users.*')
                ->leftJoin('user_groups', 'users.id', '=', 'user_groups.user_id')
                ->leftJoin('groups', 'user_groups.group_id', 'groups.id')
                ->whereIn('groups.id', auth()->guard('user')->user()->groups()->pluck('id'));
        })->get()->pluck('id')->toArray();

        return $userIds;
    }
}
