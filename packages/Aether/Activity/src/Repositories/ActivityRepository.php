<?php

namespace Aether\Activity\Repositories;

use Aether\Activity\Contracts\Activity;
use Aether\Core\Eloquent\Repository;

class ActivityRepository extends Repository
{
    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\Activity\Contracts\Activity';
    }

    /**
     * Crear canalización.
     *
     * @return Activity
     */
    public function create(array $data)
    {
        $activity = parent::create($data);

        if (isset($data['file'])) {
            app(FileRepository::class)->create([
                'name' => $data['name'] ?? $data['file']->getClientOriginalName(),
                'path' => $data['file']->store('activities/'.$activity->id),
                'activity_id' => $activity->id,
            ]);
        }

        if (! isset($data['participants'])) {
            return $activity;
        }

        foreach ($data['participants']['users'] ?? [] as $userId) {
            $activity->participants()->create([
                'user_id' => $userId,
            ]);
        }

        foreach ($data['participants']['persons'] ?? [] as $personId) {
            $activity->participants()->create([
                'person_id' => $personId,
            ]);
        }

        return $activity;
    }

    /**
     * Actualizar canalización.
     *
     * @param  int  $id
     * @param  string  $attribute
     * @return Activity
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        $activity = parent::update($data, $id);

        if (isset($data['participants'])) {
            $activity->participants()->delete();

            foreach ($data['participants']['users'] ?? [] as $userId) {
                /**
                 * En algunos casos, el componente existe en formato HTML y el HTML tradicional no envía una matriz vacía.
                 * Por lo tanto, debemos buscar una cadena vacía.
                 * Este escenario ocurre solo cuando se eliminan todos los participantes.
                 */
                if (empty($userId)) {
                    break;
                }

                $activity->participants()->create([
                    'user_id' => $userId,
                ]);
            }

            foreach ($data['participants']['persons'] ?? [] as $personId) {
                /**
                 * En algunos casos, el componente existe en formato HTML y el HTML tradicional no envía una matriz vacía.
                 * Por lo tanto, debemos buscar una cadena vacía.
                 * Este escenario ocurre solo cuando se eliminan todos los participantes.
                 */
                if (empty($personId)) {
                    break;
                }

                $activity->participants()->create([
                    'person_id' => $personId,
                ]);
            }
        }

        return $activity;
    }

    /**
     * @param  string  $dateRange
     * @return mixed
     */
    public function getActivities($dateRange)
    {
        $tablePrefix = \DB::getTablePrefix();

        return $this->select(
            'activities.id',
            'activities.created_at',
            'activities.title',
            'activities.schedule_from as start',
            'activities.schedule_to as end',
            'users.name as user_name',
        )
            ->addSelect(\DB::raw('CASE WHEN '.$tablePrefix.'activities.is_done THEN \'done\' ELSE \'\' END as class'))
            ->leftJoin('activity_participants', 'activities.id', '=', 'activity_participants.activity_id')
            ->leftJoin('users', 'activities.user_id', '=', 'users.id')
            ->whereIn('type', ['call', 'meeting', 'lunch'])
            ->whereBetween('activities.schedule_from', $dateRange)
            ->where(function ($query) {
                if ($userIds = bouncer()->getAuthorizedUserIds()) {
                    $query->whereIn('activities.user_id', $userIds)
                        ->orWhereIn('activity_participants.user_id', $userIds);
                }
            })
            ->distinct()
            ->get();
    }

    /**
     * @param  string  $startFrom
     * @param  string  $endFrom
     * @param  array  $participants
     * @param  int  $id
     * @return bool
     */
    public function isDurationOverlapping($startFrom, $endFrom, $participants, $id)
    {
        $queryBuilder = $this->leftJoin('activity_participants', 'activities.id', '=', 'activity_participants.activity_id')
            ->where(function ($query) use ($startFrom, $endFrom) {
                $query->where([
                    ['activities.schedule_from', '<=', $startFrom],
                    ['activities.schedule_to', '>=', $startFrom],
                ])->orWhere([
                    ['activities.schedule_from', '>=', $startFrom],
                    ['activities.schedule_from', '<=', $endFrom],
                ]);
            })
            ->where(function ($query) use ($participants) {
                if (is_null($participants)) {
                    return;
                }

                if (isset($participants['users'])) {
                    $query->orWhereIn('activity_participants.user_id', $participants['users']);
                }

                if (isset($participants['persons'])) {
                    $query->orWhereIn('activity_participants.person_id', $participants['persons']);
                }
            })
            ->groupBy('activities.id');

        if (! is_null($id)) {
            $queryBuilder->where('activities.id', '!=', $id);
        }

        return $queryBuilder->count() ? true : false;
    }
}
