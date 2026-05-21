<?php

namespace Aether\Admin\Http\Controllers\Settings;

use Aether\Admin\DataGrids\Settings\UserDataGrid;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Admin\Http\Requests\MassDestroyRequest;
use Aether\Admin\Http\Requests\MassUpdateRequest;
use Aether\Admin\Http\Resources\UserResource;
use Aether\Admin\Notifications\User\Create as UserCreatedNotification;
use Aether\User\Repositories\GroupRepository;
use Aether\User\Repositories\RoleRepository;
use Aether\User\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Prettus\Repository\Criteria\RequestCriteria;

class UserController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(
        protected UserRepository $userRepository,
        protected GroupRepository $groupRepository,
        protected RoleRepository $roleRepository
    ) {}

    /**
     * Mostrar una lista del recurso.
     */
    public function index(): View|JsonResponse
    {
        if (request()->ajax()) {
            return datagrid(UserDataGrid::class)->process();
        }

        $roles = $this->roleRepository->all();

        $groups = $this->groupRepository->all();

        return view('admin::settings.users.index', compact('roles', 'groups'));
    }

    /**
     * Mostrar el formulario para crear un nuevo recurso.
     */
    public function create(): View
    {
        $roles = $this->roleRepository->all();

        $groups = $this->groupRepository->all();

        return view('admin::settings.users.create', compact('roles', 'groups'));
    }

    public function store(): View|JsonResponse
    {
        $this->validate(request(), [
            'email' => 'required|email|unique:users,email',
            'name' => 'required',
            'password' => 'nullable',
            'confirm_password' => 'nullable|required_with:password|same:password',
            'role_id' => 'required',
            'status' => 'boolean|in:0,1',
            'view_permission' => 'string|in:global,group,individual',
        ]);

        $data = request()->all();

        if (request()->has('create_new_role') && request()->input('create_new_role')) {
            $this->validate(request(), [
                'role_name' => 'required|unique:roles,name',
                'role_description' => 'required',
                'permission_type' => 'required|in:custom,all',
            ]);

            $role = $this->roleRepository->create([
                'name' => $data['role_name'],
                'description' => $data['role_description'],
                'permission_type' => $data['permission_type'],
                'permissions' => $data['permissions'] ?? [],
            ]);

            $data['role_id'] = $role->id;
        }

        if (
            isset($data['password'])
            && $data['password']
        ) {
            $data['password'] = bcrypt($data['password']);
        }

        Event::dispatch('settings.user.create.before');

        $admin = $this->userRepository->create($data);

        $admin->groups()->sync($data['groups'] ?? []);

        try {
            Mail::queue(new UserCreatedNotification($admin));
        } catch (\Exception $e) {
            report($e);
        }

        Event::dispatch('settings.user.create.after', $admin);

        return new JsonResponse([
            'data' => $admin,
            'message' => trans('admin::app.settings.users.index.create-success'),
        ]);
    }

    /**
     * Muestra el formulario para editar el recurso especificado.
     */
    public function edit(int $id): View|JsonResponse
    {
        $admin = $this->userRepository->with(['role', 'groups'])->findOrFail($id);

        $roles = $this->roleRepository->all();

        $groups = $this->groupRepository->all();

        if (request()->ajax()) {
            return new JsonResponse([
                'data' => $admin,
            ]);
        }

        return view('admin::settings.users.edit', compact('admin', 'roles', 'groups'));
    }

    /**
     * Actualice el recurso especificado en el almacenamiento.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'email' => 'required|email|unique:users,email,'.$id,
            'name' => 'required|string',
            'password' => 'nullable|string|min:6',
            'confirm_password' => 'nullable|required_with:password|same:password',
            'role_id' => 'required|integer|exists:roles,id',
            'status' => 'nullable|boolean|in:0,1',
            'view_permission' => 'required|string|in:global,group,individual',
        ]);

        $data = request()->all();

        if (request()->has('create_new_role') && request()->input('create_new_role')) {
            $this->validate(request(), [
                'role_name' => 'required|unique:roles,name',
                'role_description' => 'required',
                'permission_type' => 'required|in:custom,all',
            ]);

            $role = $this->roleRepository->create([
                'name' => $data['role_name'],
                'description' => $data['role_description'],
                'permission_type' => $data['permission_type'],
                'permissions' => $data['permissions'] ?? [],
            ]);

            $data['role_id'] = $role->id;
        }

        if (empty($data['password'])) {
            $data = Arr::except($data, ['password', 'confirm_password']);
        } else {
            $data['password'] = bcrypt($data['password']);
        }

        $authUser = auth()->guard('user')->user();

        if ($authUser->id == $id) {
            $data['status'] = true;
        }

        Event::dispatch('settings.user.update.before', $id);

        $admin = $this->userRepository->update($data, $id);

        $admin->groups()->sync($data['groups'] ?? []);

        Event::dispatch('settings.user.update.after', $admin);

        return new JsonResponse([
            'data' => $admin,
            'message' => trans('admin::app.settings.users.index.update-success'),
        ]);
    }

    /**
     * Buscar resultados de usuarios.
     */
    public function search(): JsonResource
    {
        $users = $this->userRepository
            ->pushCriteria(app(RequestCriteria::class))
            ->all();

        return UserResource::collection($users);
    }

    /**
     * Destruye el usuario especificado.
     */
    public function destroy(int $id): JsonResponse
    {
        if ($this->userRepository->count() == 1) {
            return new JsonResponse([
                'message' => trans('admin::app.settings.users.index.last-delete-error'),
            ], 400);
        }

        try {
            Event::dispatch('user.admin.delete.before', $id);

            $this->userRepository->delete($id);

            Event::dispatch('user.admin.delete.after', $id);

            return new JsonResponse([
                'message' => trans('admin::app.settings.users.index.delete-success'),
            ], 200);
        } catch (\Exception $e) {
        }

        return new JsonResponse([
            'message' => trans('admin::app.settings.users.index.delete-failed'),
        ], 500);
    }

    /**
     * Actualización masiva de los recursos especificados.
     */
    public function massUpdate(MassUpdateRequest $massDestroyRequest): JsonResponse
    {
        $count = 0;

        $users = $this->userRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        foreach ($users as $users) {
            if (auth()->guard('user')->user()->id == $users->id) {
                continue;
            }

            Event::dispatch('settings.user.update.before', $users->id);

            $this->userRepository->update([
                'status' => $massDestroyRequest->input('value'),
            ], $users->id);

            Event::dispatch('settings.user.update.after', $users->id);

            $count++;
        }

        if (! $count) {
            return response()->json([
                'message' => trans('admin::app.settings.users.index.mass-update-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('admin::app.settings.users.index.mass-update-success'),
        ]);
    }

    /**
     * Elimina en masa los recursos especificados.
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $count = 0;

        $users = $this->userRepository->findWhereIn('id', $massDestroyRequest->input('indices'));

        foreach ($users as $user) {
            if (auth()->guard('user')->user()->id == $user->id) {
                continue;
            }

            Event::dispatch('settings.user.delete.before', $user->id);

            $this->userRepository->delete($user->id);

            Event::dispatch('settings.user.delete.after', $user->id);

            $count++;
        }

        if (! $count) {
            return response()->json([
                'message' => trans('admin::app.settings.users.index.mass-delete-failed'),
            ], 400);
        }

        return response()->json([
            'message' => trans('admin::app.settings.users.index.mass-delete-success'),
        ]);
    }
}
