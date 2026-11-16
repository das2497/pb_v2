<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RouteRequest;
use App\Models\OrderTime;
use App\Models\Routes;
use Illuminate\Http\Request;

class RouteProcess extends Controller
{
    public function index(Request $request)
    {
        $route_times = OrderTime::orderBy('id')->pluck('name');

        $sortable = ['index', 'name', 'type', 'time'];
        $sort = in_array($request->query('sort'), $sortable, true) ? $request->query('sort') : null;
        $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';

        $query = Routes::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($sort) {
            $query->orderBy($sort, $direction);
        }

        $routes = $query->paginate(10)->withQueryString();

        return view('order-admin.routes', [
            'routes' => $routes,
            'route_times' => $route_times,
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function store(Request $request)
    {

        $request->validate((new RouteRequest())->rules());

        Routes::create([
            'index' => $request->index,
            'name' => $request->name,
            'type' => $request->type,
            'time' => $request->time,
        ]);

        return redirect('/order-admin/routes')->with('success', 'Successfully Added!');
    }

    public function update(Request $request, $id)
    {
        $request->validate((new RouteRequest())->rules());

        $route = Routes::findOrFail($id);
        $route->index = $request->index;
        $route->name = $request->name;
        $route->type = $request->type;
        $route->time = $request->time;

        $route->save();

        return redirect()->back()->with('success', 'Route updated successfully');
    }

    public function delete(Request $request, $id)
    {
        $route = Routes::findOrFail($id);
        $route->delete();
        return redirect()->back()->with('success', 'Route deleted successfully ');
    }
}
