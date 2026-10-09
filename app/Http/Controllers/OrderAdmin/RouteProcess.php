<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Routes;
use Illuminate\Http\Request;

class RouteProcess extends Controller
{
    public function index(Request $request)
    {

        if ($request->has('search')) {
            $routes = Routes::where('name', 'like', '%' . $request->search . '%')
            ->paginate(10);        

            return view('order-admin.routes', [
                'routes' => $routes
            ]);
        }

        $routes = Routes::paginate(10);

        return view('order-admin.routes', [
            'routes' => $routes
        ]);
    }

    public function store(Request $request)
    {

        $request->validate([
            'index' => 'required',
            'name' => 'required',
            'type' => 'required',
            'time' => 'required',
        ]);

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
        $request->validate([
            'index' => 'required',
            'name' => 'required',
            'type' => 'required',
            'time' => 'required',
        ]);

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
