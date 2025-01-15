<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseObject;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ApiController extends Controller
{
    public function handleResponse($data = null, $message = null)
    {
        $response = new ResponseObject();
        $response->data = $data;
        $response->message = $message;
        $response->statusCode = Response::HTTP_OK;
        return response()->json($response,$response->statusCode);
    }

    public function handleResponseWithCount($data = null, $count)
    {
        $response = new ResponseObject();
        $response->data['items'] = $data;
        $response->data['total'] = $count;
        $response->statusCode = empty($data) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        return response()->json($response,$response->statusCode);
    }

    public function handleResponseMessage($message)
    {
        $response = new ResponseObject();
        $response->message = $message;
        $response->statusCode = Response::HTTP_OK;
        return response()->json($response,$response->statusCode);
    }

    public function handleCreated($message)
    {
        $response = new ResponseObject();
        $response->message = $message;
        $response->statusCode = Response::HTTP_CREATED;
        return response()->json($response,$response->statusCode);
    }

    public function handleResponseError($message)
    {
        $response = new ResponseObject();
        $response->errors = ['message' => $message] ;
        $response->statusCode = Response::HTTP_BAD_REQUEST;
        return response()->json($response,$response->statusCode);
    }

    public function handlePaginateResponse($data)
    {
        $response = new ResponseObject();
        $response->data['items'] = $data->items();
        $response->data['total'] = $data->total();
        $response->statusCode = empty($data) ? Response::HTTP_NO_CONTENT : Response::HTTP_OK;
        return response()->json($response,$response->statusCode);
    }

    public function getFilters($request, $defaultLimit = 100)
    {
        $filters['limit'] = ($request->has('limit')) ? $request->limit : $defaultLimit;
        $filters['order_by'] = ($request->has('order_by')) ? $request->order_by : 'id';
        $filters['search'] = ($request->has('search')) ? $request->search : '';
        $filters['order_type'] = ($request->has('order_type') && in_array($request->order_type, ['desc', 'asc'])) ? $request->order_type : 'desc';

        return $filters;
    }

    public function paginateCollection($items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }

    public function getItemsFromCollectionArrays($collectionItems)
    {
        $items = [];
        foreach ($collectionItems as $item) {
            $items[] = $item;
        }
        return collect($items);
    }

    public function resourcePermissionsMiddleware($resource)
    {
        $this->middleware('auth-permission:get_all_'.$resource.'s')->only('index');
        $this->middleware('auth-permission:show_'.$resource)->only('show');
        $this->middleware('auth-permission:create_'.$resource)->only('store');
        $this->middleware('auth-permission:update_'.$resource)->only(['update', 'updateImage']);
        $this->middleware('auth-permission:delete_'.$resource)->only('destroy');
    }

}
