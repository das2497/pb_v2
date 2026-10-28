@extends('layouts.shop-dashboard')

@section('title', 'Shop')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <!-- ============================================================== -->
                <!-- pageheader  -->
                <!-- ============================================================== -->
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Shop Profile </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#" class="breadcrumb-link">Profile</a></li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- ============================================================== -->
                <!-- end pageheader  -->
                <!-- ============================================================== -->
                <div class="ecommerce-widget">

                    <div class="row">
                        <div class="col-12 col-lg-4">
                            <div class="card">
                                <h5 class="card-header">Shop Details</h5>
                                <div class="card-body p-0">
                                    <form action="{{route('shop.profile-update')}}" method="POST" class="p-2">
                                        @csrf
                                        @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        @endif
                                        @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                        @endif
                                        <div class="form-group">
                                            <label for="inputName" class="col-form-label">Shop Name</label>
                                            <input id="inputName" type="text" class="form-control" placeholder="Shop name" value="{{Auth::user()->name}}" name="name">
                                        </div>
                                        <div class="form-group">
                                            <label for="inputEmail">Shop Email</label>
                                            <input id="inputEmail" type="email" placeholder="Shop email" class="form-control" value="{{Auth::user()->email}}" name="email">
                                        </div>
                                        <div class="form-group">
                                            <label for="inputPass">Password</label>
                                            <input id="inputPass" type="text" placeholder="Password" class="form-control" name="password">
                                        </div>
                                        <div class="form-group">
                                            <label for="inputPass2">Confirm Password</label>
                                            <input id="inputPass2" type="text" placeholder="Confirm password" class="form-control" name="confirm">
                                        </div>
                                        <div>
                                            <button type="submit" class="btn btn-primary">Update</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- end footer -->
        <!-- ============================================================== -->
    </div>

    
@endsection
