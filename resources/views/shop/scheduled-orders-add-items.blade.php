@extends('layouts.shop-dashboard')

@section('title', 'Shop | Scheduled Order Add Items')

@section('content')
    <div class="dashboard-wrapper">
        <div class="dashboard-ecommerce">
            <div class="container-fluid dashboard-content ">
                <div class="row">
                    <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="page-header">
                            <h2 class="pageheader-title"> 🧁 Scheduled Order Add Items </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/shop/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('shop.scheduled-orders') }}" class="breadcrumb-link">Scheduled Orders</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('shop.scheduled-orders-view', ['id' => $order->unique_id]) }}" class="breadcrumb-link">Scheduled Order View</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Scheduled Order Add Items</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ecommerce-widget">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
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
                                @elseif (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                                @endif
                                <h5 class="card-header" id="bakery-order-header">
                                    @include('shop.partials.scheduled-order-add-items-header')
                                </h5>
                                <form action="{{ route('shop.scheduled-orders-add-items') }}" method="GET" class="row g-2 p-2 bakery-filters-bar" id="bakery-filters-form">
                                    @csrf
                                    <input type="hidden" name="order_number" value="{{ $order_number }}">
                                    <div class="col-12 col-xl-7">
                                        <label for="search" class="bakery-form-label"><i class="fa fa-search"></i> Search Products</label>
                                        <input type="text" class="form-control" id="search" placeholder="Type a product name...." aria-describedby="addon-wrapping"
                                            name="search" value="{{ $search }}">
                                    </div>
                                    <div class="col-12 col-xl-5">
                                        <label for="category" class="bakery-form-label"><i class="fa fa-tags"></i> Category</label>
                                        <select class="form-control" id="category" name="category">
                                            <option value="" @selected($category === '')>All category</option>
                                            @foreach ( $categories as $cat )
                                            <option value="{{$cat->category}}" @selected($category===$cat->category)>{{$cat->category}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>

                                <div id="bakery-results">
                                    @include('shop.partials.scheduled-order-add-items-results')
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="bakery-selection-bar" id="bakery-selection-bar">
        <span><strong id="bakery-selection-count">0</strong> <span id="bakery-selection-label">items</span> selected</span>
        <button type="button" class="btn btn-outline-success" onclick="document.getElementById('bakery-order-form').requestSubmit();">
            <i class="fa fa-cart-plus"></i> Add to Scheduled Order
        </button>
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        var STORAGE_KEY = 'bakery-scheduled-order-selections-{{ $order_number }}';
        var bar = document.getElementById('bakery-selection-bar');
        var countEl = document.getElementById('bakery-selection-count');
        var labelEl = document.getElementById('bakery-selection-label');
        var resultsEl = document.getElementById('bakery-results');
        var headerEl = document.getElementById('bakery-order-header');
        var filtersForm = document.getElementById('bakery-filters-form');
        var searchInput = document.getElementById('search');
        var categorySelect = document.getElementById('category');

        function readStore() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
            } catch (e) {
                return {};
            }
        }

        function writeStore(store) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(store));
            } catch (e) {}
        }

        function updateSelectionBar() {
            var selected = Object.keys(readStore()).length;
            countEl.textContent = selected;
            labelEl.textContent = selected === 1 ? 'item' : 'items';
            bar.classList.toggle('is-active', selected > 0);
        }

        function qtyInputFor(checkbox) {
            var stepper = checkbox.closest('.bakery-label-bottom')?.querySelector('.bakery-qty-stepper');
            return stepper ? stepper.querySelector('.bakery-label-qty') : null;
        }

        function syncStoreFromCheckbox(checkbox) {
            var qtyInput = qtyInputFor(checkbox);
            var qty = qtyInput ? (parseFloat(qtyInput.value) || 0) : 0;
            var store = readStore();

            if (checkbox.checked && qty > 0) {
                store[checkbox.value] = qty;
            } else {
                delete store[checkbox.value];
            }

            writeStore(store);
            updateSelectionBar();
        }

        function bindForm() {
            var form = document.getElementById('bakery-order-form');
            if (!form || form.dataset.bound) return;
            form.dataset.bound = '1';

            form.addEventListener('submit', function () {
                var onThisPage = new Set(Array.from(form.querySelectorAll('.bakery-select-checkbox')).map(function (cb) { return cb.value; }));
                var pending = readStore();

                Object.keys(pending).forEach(function (itemNumber) {
                    if (onThisPage.has(itemNumber)) {
                        return;
                    }

                    var qtyField = document.createElement('input');
                    qtyField.type = 'hidden';
                    qtyField.name = 'qty[' + itemNumber + ']';
                    qtyField.value = pending[itemNumber];
                    form.appendChild(qtyField);

                    var selectedField = document.createElement('input');
                    selectedField.type = 'hidden';
                    selectedField.name = 'selected_items[]';
                    selectedField.value = itemNumber;
                    form.appendChild(selectedField);
                });
            });
        }

        function initResults() {
            var checkboxes = resultsEl.querySelectorAll('.bakery-select-checkbox');
            var store = readStore();

            checkboxes.forEach(function (checkbox) {
                if (Object.prototype.hasOwnProperty.call(store, checkbox.value)) {
                    var qtyInput = qtyInputFor(checkbox);
                    if (qtyInput) {
                        qtyInput.value = store[checkbox.value];
                    }
                    checkbox.checked = true;
                }

                checkbox.addEventListener('change', function () {
                    syncStoreFromCheckbox(checkbox);
                });
            });

            if (window.BakeryTheme) window.BakeryTheme.initQtySteppers(resultsEl);
            bindForm();
            updateSelectionBar();
        }

        initResults();

        @if (session('success'))
        writeStore({});
        updateSelectionBar();
        @endif

        // ----- AJAX filtering: search / category -----
        var debounceTimer = null;

        function buildParams() {
            return new URLSearchParams({
                order_number: '{{ $order_number }}',
                search: searchInput.value || '',
                category: categorySelect.value || '',
            });
        }

        function refresh() {
            var params = buildParams();
            var url = filtersForm.getAttribute('action') + '?' + params.toString();

            history.replaceState(null, '', url);

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    headerEl.innerHTML = data.header_html;
                    resultsEl.innerHTML = data.results_html;
                    initResults();
                });
        }

        function refreshDebounced() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(refresh, 400);
        }

        categorySelect.addEventListener('change', refresh);
        searchInput.addEventListener('input', refreshDebounced);
        filtersForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            refresh();
        });
    })();
</script>
@endpush
