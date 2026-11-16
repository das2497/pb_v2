@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Google Sheets')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> Google Sheets Setup </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard"
                                                class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Google Sheets</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ecommerce-widget">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- Step 1: the service account --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">1. Service account credentials</h3>
                                    <p class="text-muted">
                                        Upload the Google service-account JSON key file. Every spreadsheet below must be
                                        shared with that account's email address as an <strong>Editor</strong>, otherwise
                                        the refresh buttons cannot write to it.
                                    </p>

                                    @if ($credentialsExist && $credentialsInfo)
                                        <div class="alert alert-success">
                                            <strong>Connected as:</strong> {{ $credentialsInfo['client_email'] }}
                                            @if ($credentialsInfo['project_id'])
                                                <br><small>Project: {{ $credentialsInfo['project_id'] }}</small>
                                            @endif
                                            <br><small>File: {{ $credentialsPath }}</small>
                                        </div>
                                    @elseif ($credentialsExist)
                                        <div class="alert alert-warning">
                                            A credentials file exists at <code>{{ $credentialsPath }}</code>, but it could
                                            not be read as a service-account key. Upload a valid one below.
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            No credentials file in place yet
                                            (<code>{{ $credentialsPath }}</code> not found). The Google Sheet refresh
                                            buttons will not work until one is uploaded.
                                        </div>
                                    @endif

                                    <form action="{{ route('order-admin.google-sheets.credentials') }}" method="POST"
                                        enctype="multipart/form-data" class="row align-items-end">
                                        @csrf
                                        <div class="col-12 col-md-6">
                                            <div class="form-group">
                                                <label for="credentials">Service-account JSON key</label>
                                                <input type="file" name="credentials" id="credentials" accept=".json,application/json"
                                                    class="form-control-file" required>
                                                <small class="form-text text-muted">
                                                    Google Cloud Console &rarr; IAM &amp; Admin &rarr; Service Accounts &rarr;
                                                    Keys &rarr; Add key &rarr; JSON. Max 64 KB.
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">Upload credentials</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: the spreadsheets --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">2. Spreadsheets</h3>
                                    <p class="text-muted">
                                        Paste the spreadsheet ID or just the full Google Sheets URL &mdash; the ID is picked
                                        out of the URL automatically. Leave a field empty to fall back to the value in
                                        <code>.env</code>. Each sheet is written starting at <code>Sheet1!A1</code>.
                                    </p>

                                    <form action="{{ route('order-admin.google-sheets.update') }}" method="POST">
                                        @csrf
                                        <div class="table-responsive">
                                            <table class="table table-striped table-bordered">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th style="width: 20%">Report</th>
                                                        <th style="width: 45%">Spreadsheet ID or URL</th>
                                                        <th style="width: 20%">Currently in use</th>
                                                        <th style="width: 15%">Test</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($reports as $report)
                                                        <tr>
                                                            <td>{{ $report['label'] }}</td>
                                                            <td>
                                                                <input type="text"
                                                                    name="spreadsheets[{{ $report['type'] }}]"
                                                                    class="form-control"
                                                                    value="{{ old('spreadsheets.' . $report['type'], $report['saved_id']) }}"
                                                                    placeholder="{{ $report['env_id'] ? 'From .env: ' . $report['env_id'] : 'Not configured' }}">
                                                            </td>
                                                            <td>
                                                                @if ($report['effective_id'])
                                                                    <code class="small">{{ $report['effective_id'] }}</code>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        {{ $report['saved_id'] ? 'set here' : 'from .env' }}
                                                                    </small>
                                                                @else
                                                                    <span class="badge badge-warning">Not configured</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                                    form="test-{{ $report['type'] }}"
                                                                    @disabled(!$report['effective_id'] || !$credentialsExist)>
                                                                    Test
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Save spreadsheets</button>
                                        <a href="/order-admin/dashboard" class="btn btn-link">Back to dashboard</a>
                                    </form>

                                    {{-- One tiny form per row, kept outside the save form since forms cannot nest --}}
                                    @foreach ($reports as $report)
                                        <form id="test-{{ $report['type'] }}"
                                            action="{{ route('order-admin.google-sheets.test') }}" method="POST"
                                            class="d-none">
                                            @csrf
                                            <input type="hidden" name="report_type" value="{{ $report['type'] }}">
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: what to do next --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">3. Refresh</h3>
                                    <p class="text-muted mb-2">
                                        Once a report shows a spreadsheet and its test passes, refresh it from the
                                        dashboard's buttons.
                                    </p>
                                    <a href="/order-admin/dashboard" class="btn btn-outline-primary">Go to refresh
                                        buttons</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
