<?php

namespace App\Http\Controllers\OrderAdmin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use Illuminate\Http\Request;

class LogProccess extends Controller
{
    public function index(Request $request)
    {
        $logs = null;
        
        if ($request->has('search') && $request->search != '') {
            if ($request->date == null) {
                $logs = Logs::where(function ($query) use ($request) {
                    $query->where('type', 'like', '%' . $request->search . '%')
                        ->orWhere('user', 'like', '%' . $request->search . '%')
                        ->orWhere('message', 'like', '%' . $request->search . '%');
                })
                    ->orderBy('created_at', 'desc')
                    ->paginate(400);
            } else {
                $logs = Logs::whereDate('created_at', $request->date)
                ->where(function ($query) use ($request) {
                    $query->where('type', 'like', '%' . $request->search . '%')
                        ->orWhere('user', 'like', '%' . $request->search . '%')
                        ->orWhere('message', 'like', '%' . $request->search . '%');
                })
                    ->orderBy('created_at', 'desc')
                    ->paginate(400);
            }
        } else {
            if ($request->date == null) {
                $logs = Logs::orderBy('created_at', 'desc')->paginate(400);
            } else {
                $logs = Logs::whereDate('created_at', $request->date)
                ->orderBy('created_at', 'desc')->paginate(400);
            }
        }

        return view('order-admin.log', [
            'logs' => $logs
        ]);
    }
}
