@extends('layouts.dashboard', ['headerView' => 'order-admin.components.header', 'menuView' => 'order-admin.components.menu'])

@section('title', 'Order Admin | Create Order')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/order-admin-order-theme.css') }}">
@endpush

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
                            <h2 class="pageheader-title"> Create Order </h2>
                            <div class="page-breadcrumb">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/order-admin/dashboard" class="breadcrumb-link">Dashboard</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Create Order</li>
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
                        <div class="col-12">
                            <div class="card oa-order-card">
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
                                <h5 class="card-header" id="oa-order-header">
                                    @include('order-admin.partials.create-order-header')
                                </h5>
                                <form action="/order-admin/create-order" method="GET" class="row g-2 p-2 oa-order-filters-bar" id="oa-order-filters-form">
                                    @csrf
                                    <div class="col-12 col-xl-3">
                                        <label for="shop-search" class="oa-order-form-label"><i class="fa fa-store"></i> Shop</label>
                                        <div class="oa-shop-combo" id="oa-shop-combo">
                                            <input type="text" class="form-control oa-shop-combo-input" id="shop-search" placeholder="Select Shop (type to search)" autocomplete="off">
                                            <button type="button" class="oa-shop-combo-clear" id="oa-shop-combo-clear" aria-label="Clear shop" hidden>&times;</button>
                                            <div class="oa-shop-combo-list" id="oa-shop-combo-list" hidden></div>
                                            <select class="form-control" id="shop" name="shop" hidden>
                                                <option value="">Select Shop</option>
                                                @foreach($shops as $shop)
                                                <option value="{{ $shop->branch_code }}" @selected($my_shop === $shop->branch_code)>
                                                    {{ $shop->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6 col-xl-2">
                                        <label for="order_time" class="oa-order-form-label"><i class="fa fa-clock"></i> Order Time</label>
                                        <select class="form-control" id="order_time" name="order_time">
                                            @include('order-admin.partials.order-time-options')
                                        </select>
                                    </div>
                                    <div class="col-12 col-xl-3">
                                        <label for="search" class="oa-order-form-label"><i class="fa fa-search"></i> Search Products</label>
                                        <input type="text" class="form-control" id="search" placeholder="Type a product name...."
                                            name="search" value="{{ $search ?? '' }}">
                                    </div>
                                    <div class="col-6 col-xl-3">
                                        <label for="category" class="oa-order-form-label"><i class="fa fa-tags"></i> Category</label>
                                        <select class="form-control" id="category" name="category">
                                            <option value="" @selected(($category ?? '') === '')>All category</option>
                                            @foreach ( $categories as $cat )
                                            <option value="{{$cat->category}}" @selected(($category ?? '')===$cat->category)>{{$cat->category}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>

                                <div id="oa-order-results">
                                    @include('order-admin.partials.create-order-results')
                                </div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="oa-order-selection-bar" id="oa-order-selection-bar">
        <span><strong id="oa-order-selection-count">0</strong> <span id="oa-order-selection-label">items</span> selected</span>
        <button type="button" class="btn btn-outline-success" onclick="document.getElementById('oa-order-form').requestSubmit();">
            <i class="fa fa-cart-plus"></i> Add to Cart
        </button>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/order-admin-order-theme.js') }}?v={{ filemtime(public_path('assets/js/order-admin-order-theme.js')) }}"></script>
<script>
    (function () {
        // Same cross-page persistence pattern as the rep role's create-order
        // page (assets/js/rep-order-theme.js's stepper + this page-level
        // script), keyed per shop: an admin can switch between any shop in
        // the system, and picks for one shop must never bleed into another
        // shop's cart.
        var STORAGE_KEY_PREFIX = 'oa-create-order-selections:';
        var bar = document.getElementById('oa-order-selection-bar');
        var countEl = document.getElementById('oa-order-selection-count');
        var labelEl = document.getElementById('oa-order-selection-label');
        var resultsEl = document.getElementById('oa-order-results');
        var headerEl = document.getElementById('oa-order-header');
        var orderTimeSelect = document.getElementById('order_time');
        var filtersForm = document.getElementById('oa-order-filters-form');
        var shopSelect = document.getElementById('shop');
        var searchInput = document.getElementById('search');
        var categorySelect = document.getElementById('category');

        function currentShop() {
            return shopSelect.value || '';
        }

        function storageKey() {
            return STORAGE_KEY_PREFIX + currentShop();
        }

        function readStore() {
            try {
                return JSON.parse(localStorage.getItem(storageKey()) || '{}');
            } catch (e) {
                return {};
            }
        }

        function writeStore(store) {
            try {
                localStorage.setItem(storageKey(), JSON.stringify(store));
            } catch (e) {}
        }

        function updateSelectionBar() {
            var selected = Object.keys(readStore()).length;
            countEl.textContent = selected;
            labelEl.textContent = selected === 1 ? 'item' : 'items';
            bar.classList.toggle('is-active', selected > 0);
        }

        function qtyInputFor(checkbox) {
            var stepper = checkbox.closest('.oa-order-label-bottom')?.querySelector('.oa-order-qty-stepper');
            return stepper ? stepper.querySelector('.oa-order-label-qty') : null;
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
            var form = document.getElementById('oa-order-form');
            if (!form || form.dataset.bound) return;
            form.dataset.bound = '1';

            form.addEventListener('submit', function () {
                var onThisPage = new Set(Array.from(form.querySelectorAll('.oa-order-select-checkbox')).map(function (cb) { return cb.value; }));
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
            var checkboxes = resultsEl.querySelectorAll('.oa-order-select-checkbox');
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

            if (window.OAOrderTheme) window.OAOrderTheme.initQtySteppers(resultsEl);
            bindForm();
            updateSelectionBar();
        }

        initResults();

        @if (session('success'))
        writeStore({});
        updateSelectionBar();
        @endif

        // ----- AJAX filtering: shop / order time / search / category -----
        var debounceTimer = null;

        function buildParams() {
            return new URLSearchParams({
                shop: currentShop(),
                order_time: orderTimeSelect.value || '',
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
                    orderTimeSelect.innerHTML = data.order_time_options_html;
                    resultsEl.innerHTML = data.results_html;
                    initResults();
                });
        }

        function refreshDebounced() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(refresh, 400);
        }

        shopSelect.addEventListener('change', refresh);
        orderTimeSelect.addEventListener('change', refresh);
        categorySelect.addEventListener('change', refresh);
        searchInput.addEventListener('input', refreshDebounced);
        filtersForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            refresh();
        });

        // ----- Searchable shop dropdown (no external dependency - the CDN
        // select2 this used to be relied on jQuery/select2 loading from a
        // CDN, which isn't reliable in every environment; this is a small
        // self-contained combobox over the real, hidden <select id="shop">
        // so every existing listener/value read above keeps working) -----
        (function () {
            var combo = document.getElementById('oa-shop-combo');
            var input = document.getElementById('shop-search');
            var list = document.getElementById('oa-shop-combo-list');
            var clearBtn = document.getElementById('oa-shop-combo-clear');

            var options = Array.from(shopSelect.options)
                .filter(function (o) { return o.value !== ''; })
                .map(function (o) { return { value: o.value, label: o.textContent.trim() }; });

            function labelForValue(value) {
                var match = options.filter(function (o) { return o.value === value; })[0];
                return match ? match.label : '';
            }

            function renderList(filterText) {
                var q = (filterText || '').toLowerCase();
                var matches = q ? options.filter(function (o) { return o.label.toLowerCase().indexOf(q) !== -1; }) : options;
                list.innerHTML = '';

                if (matches.length === 0) {
                    var empty = document.createElement('div');
                    empty.className = 'oa-shop-combo-empty';
                    empty.textContent = 'No shops found';
                    list.appendChild(empty);
                    return;
                }

                matches.slice(0, 200).forEach(function (o) {
                    var item = document.createElement('div');
                    item.className = 'oa-shop-combo-item' + (o.value === shopSelect.value ? ' is-active' : '');
                    item.textContent = o.label;
                    item.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        selectShop(o.value, o.label);
                    });
                    list.appendChild(item);
                });
            }

            function selectShop(value, label) {
                shopSelect.value = value;
                input.value = label;
                clearBtn.hidden = !value;
                list.hidden = true;
                shopSelect.dispatchEvent(new Event('change'));
            }

            input.addEventListener('focus', function () {
                input.select();
                renderList(input.value === labelForValue(shopSelect.value) ? '' : input.value);
                list.hidden = false;
            });

            input.addEventListener('input', function () {
                clearBtn.hidden = !input.value;
                renderList(input.value);
                list.hidden = false;
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    list.hidden = true;
                    input.blur();
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    var first = list.querySelector('.oa-shop-combo-item');
                    if (first) selectShop(options.filter(function (o) { return o.label === first.textContent; })[0].value, first.textContent);
                }
            });

            clearBtn.addEventListener('click', function () {
                selectShop('', '');
                input.focus();
            });

            document.addEventListener('click', function (e) {
                if (!combo.contains(e.target)) list.hidden = true;
            });

            input.value = labelForValue(shopSelect.value);
            clearBtn.hidden = !shopSelect.value;
        })();
    })();
</script>
@endpush
