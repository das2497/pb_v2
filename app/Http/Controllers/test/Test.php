<?php

namespace App\Http\Controllers\test;

use App\Http\Controllers\Controller;
use App\Mail\Shop\OrderCreated;
use App\Models\Cart;
use App\Models\Orders;
use App\Models\Products;
use App\Models\Routes;
use App\Models\Shops;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

use function PHPUnit\Framework\isEmpty;

class Test extends Controller
{

    public function index(Request $request)
    {
        return view('test');
    }
}

