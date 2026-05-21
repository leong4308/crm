<?php

namespace Aether\Admin\Http\Controllers\Products;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Aether\Admin\Http\Controllers\Controller;
use Aether\Product\Repositories\ProductRepository;

class TagController extends Controller
{
    /**
     * Cree una nueva instancia de controlador.
     *
     * @return void
     */
    public function __construct(protected ProductRepository $productRepository) {}

    /**
     * Almacene un recurso recién creado en el almacenamiento.
     *
     * @param  int  $id
     * @return Response
     */
    public function attach($id)
    {
        Event::dispatch('products.tag.create.before', $id);

        $product = $this->productRepository->findOrFail($id);

        if (! $product->tags->contains(request()->input('tag_id'))) {
            $product->tags()->attach(request()->input('tag_id'));
        }

        Event::dispatch('products.tag.create.after', $product);

        return response()->json([
            'message' => trans('admin::app.leads.view.tags.create-success'),
        ]);
    }

    /**
     * Elimine el recurso especificado del almacenamiento.
     *
     * @param  int  $productId
     * @return Response
     */
    public function detach($productId)
    {
        Event::dispatch('products.tag.delete.before', $productId);

        $product = $this->productRepository->find($productId);

        $product->tags()->detach(request()->input('tag_id'));

        Event::dispatch('products.tag.delete.after', $product);

        return response()->json([
            'message' => trans('admin::app.leads.view.tags.destroy-success'),
        ]);
    }
}
