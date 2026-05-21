<?php

namespace Aether\Admin\Http\Controllers\Settings;

use Aether\Admin\Http\Controllers\Controller;
use Aether\Core\Menu\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Mostrar una lista del recurso.
     *
     * @return View
     */
    public function index()
    {
        return view('admin::settings.index');
    }

    /**
     * Busque configuraciones.
     */
    public function search(): ?JsonResponse
    {
        $query = strtolower(request()->query('query'));

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        $results = $this->searchMenuItems($this->getSettingsConfig(), $query);

        return response()->json([
            'data' => $results->values(),
        ]);
    }

    /**
     * Busque recursivamente entre elementos del menú y elementos secundarios.
     *
     * @param  Collection<int, MenuItem>  $menuItems
     * @return Collection<int, array<string, mixed>>
     */
    protected function searchMenuItems(Collection $menuItems, string $query): Collection
    {
        $results = collect();

        foreach ($menuItems as $item) {
            if ($this->matchesQuery($item, $query)) {
                $results->push([
                    'name' => $item->getName(),
                    'url' => $item->getUrl(),
                    'icon' => $item->getIcon(),
                    'key' => $item->getKey(),
                ]);
            }

            if ($item->haveChildren()) {
                $childResults = $this->searchMenuItems($item->getChildren(), $query);

                $results = $results->merge($childResults);
            }
        }

        return $results;
    }

    /**
     * Determine si el elemento del menú coincide con la consulta.
     */
    protected function matchesQuery(MenuItem $item, string $query): bool
    {
        $query = strtolower($query);
        $url = strtolower($item->getUrl());

        if (
            ! $url
            || ! str_contains($url, $query)
        ) {
            return false;
        }

        return true;
    }

    /**
     * Obtenga la configuración de los ajustes.
     */
    protected function getSettingsConfig(): Collection
    {
        return menu()
            ->getItems('admin')
            ->filter(fn (MenuItem $item) => $item->getKey() === 'settings');
    }
}
