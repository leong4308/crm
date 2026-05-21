<?php

namespace Aether\Attribute\Repositories;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Aether\Attribute\Contracts\Attribute;
use Aether\Core\Eloquent\Repository;

class AttributeRepository extends Repository
{
    /**
     * Cree una nueva instancia de repositorio.
     *
     * @return void
     */
    public function __construct(
        protected AttributeOptionRepository $attributeOptionRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Especificar el nombre de la clase del modelo
     *
     * @return mixed
     */
    public function model()
    {
        return 'Aether\Attribute\Contracts\Attribute';
    }

    /**
     * @return Attribute
     */
    public function create(array $data)
    {
        $options = isset($data['options']) ? $data['options'] : [];

        $attribute = $this->model->create($data);

        if (in_array($attribute->type, ['select', 'multiselect', 'checkbox']) && count($options)) {
            $sortOrder = 1;

            foreach ($options as $optionInputs) {
                $this->attributeOptionRepository->create(array_merge([
                    'attribute_id' => $attribute->id,
                    'sort_order'   => $sortOrder++,
                ], $optionInputs));
            }
        }

        return $attribute;
    }

    /**
     * @param  int  $id
     * @param  string  $attribute
     * @return Attribute
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        $attribute = $this->find($id);

        $attribute->update($data);

        if (! in_array($attribute->type, ['select', 'multiselect', 'checkbox'])) {
            return $attribute;
        }

        if (! isset($data['options'])) {
            return $attribute;
        }

        foreach ($data['options'] as $optionId => $optionInputs) {
            $isNew = $optionInputs['isNew'] == 'true';

            if ($isNew) {
                $this->attributeOptionRepository->create(array_merge([
                    'attribute_id' => $attribute->id,
                ], $optionInputs));
            } else {
                $isDelete = $optionInputs['isDelete'] == 'true';

                if ($isDelete) {
                    $this->attributeOptionRepository->delete($optionId);
                } else {
                    $this->attributeOptionRepository->update($optionInputs, $optionId);
                }
            }
        }

        return $attribute;
    }

    /**
     * @param  string  $code
     * @return Attribute
     */
    public function getAttributeByCode($code)
    {
        static $attributes = [];

        if (array_key_exists($code, $attributes)) {
            return $attributes[$code];
        }

        return $attributes[$code] = $this->findOneByField('code', $code);
    }

    /**
     * @param  string  $lookup
     * @param  string  $query
     * @param  array   $columns
     * @return mixed
     */
    public function getLookUpOptions($lookup, $query = '', $columns = [])
    {
        $lookup = config('attribute_lookups.'.$lookup);

        if (! count($columns)) {
            $columns = [
                ($lookup['value_column'] ?? 'id').' as id',
                ($lookup['label_column'] ?? 'name').' as name',
            ];
        }

        if (Str::contains($lookup['repository'], 'UserRepository')) {
            $userRepository = app($lookup['repository'])->where('status', 1);

            $currentUser = auth()->guard('user')->user();

            if ($currentUser?->view_permission === 'group') {
                $query   = urldecode($query);
                $userIds = bouncer()->getAuthorizedUserIds();

                return $userRepository
                    ->when(! empty($userIds), fn ($q) => $q->whereIn('users.id', $userIds))
                    ->when(! empty($query), fn ($q) => $q->where('users.name', 'like', "%{$query}%"))
                    ->get();
            } elseif ($currentUser?->view_permission === 'individual') {
                return $userRepository->where('users.id', $currentUser->id)->get();
            }

            return $userRepository->where('users.name', 'like', '%'.urldecode($query).'%')->get();
        }

        // Cuando el lookup config define 'table', se usa DB::table directamente
        // para evitar el bug del CustomAttribute trait que sobreescribe 'name'
        // leyendo desde attribute_values (que puede estar vacío).
        if (! empty($lookup['table'])) {
            return $this->getLookUpOptionsDirectQuery($lookup, $query);
        }

        return app($lookup['repository'])->findWhere([
            [$lookup['label_column'] ?? 'name', 'like', '%'.urldecode($query).'%'],
        ], $columns);
    }

    /**
     * Realiza la búsqueda de lookup con una consulta SQL directa.
     * Se usa para entidades con el CustomAttribute trait donde el campo 'name'
     * puede ser sobreescrito desde attribute_values, dejándolo vacío.
     */
    protected function getLookUpOptionsDirectQuery(array $lookup, ?string $query): \Illuminate\Support\Collection
    {
        $table       = $lookup['table'];
        $labelColumn = $lookup['label_column'] ?? 'name';
        $valueColumn = $lookup['value_column'] ?? 'id';
        $search      = '%' . urldecode($query ?? '') . '%';

        return DB::table($table)
            ->select("{$valueColumn} as id", "{$labelColumn} as name")
            ->where($labelColumn, 'ilike', $search)
            ->orderBy($labelColumn)
            ->limit(50)
            ->get();
    }

    /**
     * @param  string       $lookup
     * @param  int|array    $entityId
     * @param  array        $columns
     * @return mixed
     */
    public function getLookUpEntity($lookup, $entityId = null, $columns = [])
    {
        if (! $entityId) {
            return;
        }

        $lookup = config('attribute_lookups.'.$lookup);

        if (! count($columns)) {
            $columns = [
                ($lookup['value_column'] ?? 'id').' as id',
                ($lookup['label_column'] ?? 'name').' as name',
            ];
        }

        // Usar consulta directa cuando está definida la 'table' para evitar
        // el override del CustomAttribute trait en el campo 'name'.
        if (! empty($lookup['table'])) {
            $table       = $lookup['table'];
            $labelColumn = $lookup['label_column'] ?? 'name';
            $valueColumn = $lookup['value_column'] ?? 'id';

            if (is_array($entityId)) {
                return DB::table($table)
                    ->select("{$valueColumn} as id", "{$labelColumn} as name")
                    ->whereIn($valueColumn, $entityId)
                    ->get();
            } else {
                return DB::table($table)
                    ->select("{$valueColumn} as id", "{$labelColumn} as name")
                    ->where($valueColumn, $entityId)
                    ->first();
            }
        }

        if (is_array($entityId)) {
            return app($lookup['repository'])->findWhereIn(
                'id',
                $entityId,
                $columns
            );
        } else {
            return app($lookup['repository'])->find($entityId, $columns);
        }
    }
}
