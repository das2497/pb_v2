<?php

use App\Http\Controllers\AIChatController;
use App\Http\Controllers\OrderAdmin\Dashboard;
use App\Http\Controllers\OrderAdmin\FinalReport;
use App\Http\Controllers\OrderAdmin\CartProcess;
use App\Http\Controllers\OrderAdmin\Complete_orders;
use App\Http\Controllers\OrderAdmin\ExportReport;
use App\Http\Controllers\OrderAdmin\GoogleSheetController as OrderAdminGoogleSheetController;
use App\Http\Controllers\OrderAdmin\GoogleSheetController2 as OrderAdminGoogleSheetController2;
use App\Http\Controllers\OrderAdmin\GoogleSheetController3 as OrderAdminGoogleSheetController3;
use App\Http\Controllers\OrderAdmin\GoogleSheetController4 as OrderAdminGoogleSheetController4;
use App\Http\Controllers\OrderAdmin\LogProccess;
use App\Http\Controllers\OrderAdmin\Pending_orders;
use App\Http\Controllers\OrderAdmin\Pending_orders_add_item;
use App\Http\Controllers\OrderAdmin\Processing_orders;
use App\Http\Controllers\OrderAdmin\Processing_orders_add_item;
use App\Http\Controllers\OrderAdmin\OrderTimeProcess;
use App\Http\Controllers\OrderAdmin\ProductCategory;
use App\Http\Controllers\OrderAdmin\ProductsProcess;
use App\Http\Controllers\OrderAdmin\Profile as OrderAdminProfile;
use App\Http\Controllers\OrderAdmin\RepAssignProcess;
use App\Http\Controllers\OrderAdmin\RepCreateOrder;
use App\Http\Controllers\OrderAdmin\RepProcess;
use App\Http\Controllers\OrderAdmin\RouteProcess;
use App\Http\Controllers\OrderAdmin\ShopProcess;
use App\Http\Controllers\OrderAdmin\ShopReport;
use App\Http\Controllers\OrderAdmin\Under_review_orders;
use App\Http\Controllers\OrderAdmin\Under_review_orders_add_item;
use App\Http\Controllers\Processing_to_complete;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Rep\CartProcess as RepCartProcess;
use App\Http\Controllers\Rep\CompleteOrders;
use App\Http\Controllers\Rep\Dashboard as RepDashboard;
use App\Http\Controllers\Rep\ExportReport as RepExportReport;
use App\Http\Controllers\Rep\FinalReport as RepFinalReport;
use App\Http\Controllers\Rep\OrderProcess;
use App\Http\Controllers\Rep\PendingOrders;
use App\Http\Controllers\Rep\PendingOrdersAddItems;
use App\Http\Controllers\Rep\ProcessingOrders;
use App\Http\Controllers\Rep\ProcessingOrdersAddItems;
use App\Http\Controllers\Rep\Profile as RepProfile;
use App\Http\Controllers\Rep\ShopReport as RepShopReport;
use App\Http\Controllers\Rep\Shops;
use App\Http\Controllers\Rep\UnderReviewOrders;
use App\Http\Controllers\Rep\UnderReviewOrdersAddItems;
use App\Http\Controllers\Shop\AllOrders;
use App\Http\Controllers\Shop\CartProcess as ShopCartProcess;
use App\Http\Controllers\Shop\CompleteOrders as ShopCompleteOrders;
use App\Http\Controllers\Shop\CreateOrders;
use App\Http\Controllers\Shop\Dashboard as ShopDashboard;
use App\Http\Controllers\Shop\DefaultOrders;
use App\Http\Controllers\Shop\DefaultOrdersAddItems;
use App\Http\Controllers\Shop\PendingOrders as ShopPendingOrders;
use App\Http\Controllers\Shop\PendingOrdersAddItems as ShopPendingOrdersAddItems;
use App\Http\Controllers\Shop\ProcessingOrders as ShopProcessingOrders;
use App\Http\Controllers\Shop\Profile;
use App\Http\Controllers\Shop\UnderReviewOrders as ShopUnderReviewOrders;
use App\Http\Controllers\test\Test;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
})->middleware('login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

Route::middleware('role:s_admin')->group(function () {
    Route::get('/super-admin/dashboard', function () {
        return view('super-admin.dashboard');
    })->name('super-admin');
});

Route::middleware('role:o_admin,admin,view,sales_admin')->group(function () {
    Route::get('/order-admin/dashboard', [Dashboard::class, 'index'])->name('order-admin.dashboard');
    Route::get('/order-admin/first-load-data', [Dashboard::class, 'firstLoading'])->name('order-admin.first-load-data');
    Route::get('/api/order-admin/dashboard-data', [Dashboard::class, 'getDashboardData'])->name('api.order-admin.dashboard-data');

    Route::get('/order-admin/log', [LogProccess::class, 'index'])->name('order-admin.log');

    Route::get('/order-admin/pending-orders', [Pending_orders::class, 'index']);
    Route::post('/order-admin/pending-note-update', [Pending_orders::class, 'note_update']);
    Route::get('/order-admin/pending-orders-view/{id}', [Pending_orders::class, 'view'])->name('order-admin-pending-orders-view');
    Route::post('/order-admin/pending-orders-update-order', [Pending_orders::class, 'update_order']);
    Route::post('/order-admin/pending-orders-update', [Pending_orders::class, 'update']);
    Route::post('/order-admin/pending-orders-delete', [Pending_orders::class, 'delete']);
    Route::post('/order-admin/pending-orders-accept', [Pending_orders::class, 'accept']);
    Route::get('/order-admin/pending-orders-add-item', [Pending_orders_add_item::class, 'index'])->name('order-admin-pending-orders-add-items');
    Route::post('/order-admin/pending-orders-add-item', [Pending_orders_add_item::class, 'store'])->name('order-admin-pending-orders-add-items-process');

    Route::get('/order-admin/processing-orders', [Processing_orders::class, 'index']);
    Route::get('/order-admin/processing-orders-view/{id}', [Processing_orders::class, 'view'])->name('order-admin-processing-orders-view');
    Route::post('/order-admin/processing-orders-update-order', [Processing_orders::class, 'update_order']);
    Route::post('/order-admin/processing-orders-update', [Processing_orders::class, 'update']);
    Route::post('/order-admin/processing-orders-delete', [Processing_orders::class, 'delete']);
    Route::get('/order-admin/processing-orders-add-item', [Processing_orders_add_item::class, 'index'])->name('order-admin-processing-orders-add-items');
    Route::post('/order-admin/processing-orders-add-item', [Processing_orders_add_item::class, 'store'])->name('order-admin-processing-orders-add-items-process');

    Route::get('/order-admin/complete-orders', [Complete_orders::class, 'index']);
    Route::get('/order-admin/under-complete-orders-view/{id}', [Complete_orders::class, 'view'])->name('order-admin-complete-orders-view');

    Route::get('/order-admin/under-review-orders', [Under_review_orders::class, 'index']);
    Route::post('/order-admin/under-review-orders-delete/{id}', [Under_review_orders::class, 'delete_order'])->name('order-admin-under-review-orders-delete');
    Route::get('/order-admin/under-review-orders-view/{id}', [Under_review_orders::class, 'view'])->name('order-admin-under-review-orders-view');
    Route::post('/order-admin/under-review-orders-update-order', [Under_review_orders::class, 'update_order']);
    Route::post('/order-admin/under-review-orders-update', [Under_review_orders::class, 'update']);
    Route::post('/order-admin/under-review-orders-delete', [Under_review_orders::class, 'delete']);
    Route::get('/order-admin/under-review-orders-add-item', [Under_review_orders_add_item::class, 'index'])->name('order-admin-under-review-orders-add-items');
    Route::post('/order-admin/under-review-orders-add-item', [Under_review_orders_add_item::class, 'store'])->name('order-admin-under-review-orders-add-items-process');

    Route::get('/order-admin/add-products', [ProductsProcess::class, 'add_products_view']);
    Route::post('/order-admin/add-products', [ProductsProcess::class, 'add_product_process'])->name('order-admin-add-products');
    Route::put('/products/{id}', [ProductsProcess::class, 'update'])->name('order-admin-products.update');
    Route::post('/products/delete/{id}', [ProductsProcess::class, 'delete'])->name('order-admin-products.delete');

    Route::post('/order-admin/add-order-time', [OrderTimeProcess::class, 'store'])->name('order-admin-add-order-time');
    Route::post('/order-admin/delete-order-time/{id}', [OrderTimeProcess::class, 'delete'])->name('order-admin-delete-order-time');
    Route::post('/order-admin/add-category', [ProductCategory::class, 'store'])->name('order-admin-add-product-category');
    Route::put('/order-admin/update-category/{id}', [ProductCategory::class, 'update'])->name('order-admin-update-product-category');
    Route::post('/order-admin/delete-category/{id}', [ProductCategory::class, 'delete'])->name('order-admin-delete-product-category');

    Route::get('/order-admin/add-shop', [ShopProcess::class, 'index']);
    Route::post('/order-admin/add-shop', [ShopProcess::class, 'store']);
    Route::put('/shops/{id}', [ShopProcess::class, 'update'])->name('shops.update');
    Route::get('/shops/delete/{id}', [ShopProcess::class, 'delete'])->name('shops.delete');

    Route::get('/order-admin/routes', [RouteProcess::class, 'index']);
    Route::post('/order-admin/add-route', [RouteProcess::class, 'store']);
    Route::put('/order-admin/update-route/{id}', [RouteProcess::class, 'update'])->name('order-admin-update-route');
    Route::post('/order-admin/delete-route/{id}', [RouteProcess::class, 'delete'])->name('order-admin-delete-route');

    Route::get('/order-admin/add-rep', [RepProcess::class, 'index']);
    Route::post('/order-admin/add-rep', [RepProcess::class, 'store']);
    Route::post('/order-admin/rep-update-access', [RepAssignProcess::class, 'update_access']);
    Route::post('/order-admin/rep-delete', [RepProcess::class, 'delete'])->name('reps.delete');   

    Route::get('/order-admin/rep-assign', [RepAssignProcess::class, 'index']);
    Route::post('/order-admin/rep-assign', [RepAssignProcess::class, 'store']);
    Route::post('/order-admin/rep-assign-update', [RepAssignProcess::class, 'update']);

    Route::get('/order-admin/create-order', [RepCreateOrder::class, 'index']);
    Route::post('/order-admin/create-order-add-to-cart', [RepCreateOrder::class, 'add_to_cart'])->name('add-to-cart');
    Route::post('/order-admin/add-to-cart-all', [RepCreateOrder::class, 'addAllToCart'])->name('order-admin.add-to-cart-all');
    Route::get('/order-admin/cart', [CartProcess::class, 'index'])->name('order-admin.cart');
    Route::post('/order-admin/cart/update-qty', [CartProcess::class, 'update_qty']);
    Route::post('/order-admin/cart/delete-item', [CartProcess::class, 'delete_item']);
    Route::post('/order-admin/cart/order-process', [CartProcess::class, 'create_order']);

    Route::get('/order-admin/export-report', [ExportReport::class, 'index']);

    Route::get('/order-admin/shop-report', [ShopReport::class, 'index']);
    Route::get('/order-admin/shop-normal-report', [ShopReport::class, 'index_normal']);
    Route::get('/order-admin/shop-report-evening', [ShopReport::class, 'index_evening']);

    Route::get('/order-admin/shop-report-full-screen', [ShopReport::class, 'fullScreen']);
    Route::get('/order-admin/shop-report-normal-full-screen', [ShopReport::class, 'fullScreen_normal']);
    Route::get('/order-admin/shop-report-full-screen-evening', [ShopReport::class, 'fullScreen_evening']);
    Route::get('/order-admin/shop-report-back-to-report', [ShopReport::class, 'back_to_report']);
    Route::get('/order-admin/shop-report-back-to-report-evening', [ShopReport::class, 'back_to_report_evening']);

    Route::get('/order-admin/final-report', [FinalReport::class, 'index']);
    Route::get('/order-admin/final-report-full-screen', [FinalReport::class, 'fullScreen']);
    Route::get('/order-admin/final-report-evening', [FinalReport::class, 'index_evening']);
    Route::get('/order-admin/final-report-evening-full-screen', [FinalReport::class, 'fullScreen_evening']);

    Route::get('/order-admin/refresh-morning-summary', [OrderAdminGoogleSheetController::class, 'updateGoogleSheet'])->name('morning-summary');
    Route::get('/order-admin/refresh-evening-summary', [OrderAdminGoogleSheetController2::class, 'updateGoogleSheet'])->name('evening-summary');
    Route::get('/order-admin/refresh-morning-shop-report', [OrderAdminGoogleSheetController3::class, 'updateGoogleSheet'])->name('morning-shop-report');
    Route::get('/order-admin/refresh-evening-shop-report', [OrderAdminGoogleSheetController4::class, 'updateGoogleSheet'])->name('evening-shop-report');

    Route::get('/order-admin/processing-transfer', [Processing_to_complete::class, 'index']);

    Route::get('/order-admin/profile', [OrderAdminProfile::class, 'index'])->name('order-admin.profile');
    Route::post('/order-admin/profile-update', [OrderAdminProfile::class, 'updateDetails'])->name('order-admin.profile-update');
    Route::post('/order-admin/profile-update-password', [OrderAdminProfile::class, 'updatePassword'])->name('order-admin.profile-update-password');

    Route::get('/chat', [AIChatController::class, 'index'])->name('order-admin.ai-chat');
    Route::post('/ai-chat', [AIChatController::class, 'chat'])->middleware('throttle:10,1')->name('order-admin.ai-chat.query');
});

Route::middleware('role:rep')->group(function () {
    Route::get('/rep/dashboard', [RepDashboard::class, 'index'])->name('rep.dashboard');
    Route::get('/rep/my-shops', [Shops::class, 'index'])->name('rep.my-shops');
    Route::get('/rep/create-order', [OrderProcess::class, 'index'])->name('rep.create-order');
    Route::get('/rep/clear-cart', [OrderProcess::class, 'clearcart']);

    Route::post('/rep/add-to-cart', [OrderProcess::class, 'add_to_cart'])->name('rep.add-to-cart');
    Route::post('/rep/add-to-cart-all', [OrderProcess::class, 'addAllToCart'])->name('rep.add-to-cart-all');
    Route::get('/rep/cart', [RepCartProcess::class, 'index'])->name('rep.cart');
    Route::post('/rep/cart/update-qty', [RepCartProcess::class, 'update_qty']);
    Route::post('/rep/cart/delete-item', [RepCartProcess::class, 'delete_item']);
    Route::post('/rep/cart/order-process', [RepCartProcess::class, 'create_order']);

    Route::get('/rep/pending-order', [PendingOrders::class, 'index'])->name('rep.pending-order');
    Route::post('/rep/pending-order-note-update', [PendingOrders::class, 'note_update']);
    Route::post('/rep/pending-orders-update', [PendingOrders::class, 'update'])->name('rep.pending-order-update');
    Route::post('/rep/pending-orders-delete', [PendingOrders::class, 'delete'])->name('rep.pending-order-delete');
    Route::post('/rep/pending-orders-accept', [PendingOrders::class, 'accept']);
    Route::get('/rep/pending-orders-view/{id}', [PendingOrders::class, 'view'])->name('rep.pending-orders-view');
    Route::get('/rep/pending-orders-add-item', [PendingOrdersAddItems::class, 'index'])->name('rep.pending-orders-add-items');
    Route::post('/rep/pending-orders-add-item', [PendingOrdersAddItems::class, 'store'])->name('rep.pending-orders-add-items-process');

    Route::get('/rep/processing-order', [ProcessingOrders::class, 'index'])->name('rep.processing-order');
    Route::post('/rep/processing-orders-note-update', [ProcessingOrders::class, 'note_update']);
    Route::post('/rep/processing-orders-update', [ProcessingOrders::class, 'update']);
    Route::post('/rep/processing-orders-delete', [ProcessingOrders::class, 'delete']);
    Route::get('/rep/processing-orders-view/{id}', [ProcessingOrders::class, 'view'])->name('rep.processing-orders-view');
    Route::get('/rep/processing-orders-add-item', [ProcessingOrdersAddItems::class, 'index'])->name('rep.processing-orders-add-items');
    Route::post('/rep/processing-orders-add-item', [ProcessingOrdersAddItems::class, 'store'])->name('rep.processing-orders-add-items-process');

    Route::get('/rep/under-review-order', [UnderReviewOrders::class, 'index'])->name('rep.under-review-order');
    Route::post('/rep/under-review-orders-delete/{id}', [UnderReviewOrders::class, 'delete_order'])->name('rep.under-review-orders-delete');
    Route::post('/rep/under-review-orders-update', [UnderReviewOrders::class, 'update']);
    Route::post('/rep/under-review-orders-delete', [UnderReviewOrders::class, 'delete']);
    Route::get('/rep/under-review-orders-view/{id}', [UnderReviewOrders::class, 'view'])->name('rep.under-review-orders-view');
    Route::get('/rep/under-review-orders-add-item', [UnderReviewOrdersAddItems::class, 'index'])->name('rep.under-review-orders-add-items');
    Route::post('/rep/under-review-orders-add-item', [UnderReviewOrdersAddItems::class, 'store'])->name('rep.under-review-orders-add-items-process');

    Route::get('/rep/complete-order', [CompleteOrders::class, 'index'])->name('rep.complete-order');
    Route::get('/rep/under-complete-orders-view/{id}', [CompleteOrders::class, 'view'])->name('rep.complete-orders-view');

    Route::get('/rep/export-report', [RepExportReport::class, 'index']);

    Route::get('/rep/shop-report-morning', [RepShopReport::class, 'index'])->name('rep.shop-report-morning');
    Route::get('/rep/shop-report-evening', [RepShopReport::class, 'index_evening']);
    Route::get('/rep/shop-report-full-screen', [RepShopReport::class, 'fullScreen']);
    Route::get('/rep/shop-report-full-screen-evening', [RepShopReport::class, 'fullScreen_evening']);
    Route::get('/rep/shop-report-back-to-report', [RepShopReport::class, 'back_to_report']);
    Route::get('/rep/shop-report-back-to-report-evening', [RepShopReport::class, 'back_to_report_evening']);

    Route::get('/rep/final-report-morning', [RepFinalReport::class, 'index'])->name('rep.final-report-morning');
    Route::get('/rep/final-report-morning-full-screen', [RepFinalReport::class, 'fullScreen']);
    Route::get('/rep/final-report-evening', [RepFinalReport::class, 'index_evening']);
    Route::get('/rep/final-report-evening-full-screen', [RepFinalReport::class, 'fullScreen_evening']);

    Route::get('/rep/profile', [RepProfile::class, 'view'])->name('rep.profile');
    Route::post('/rep/profile-update', [RepProfile::class, 'update'])->name('rep.profile-update');
});

Route::middleware('role:shop')->group(function () {
    Route::get('/shop/dashboard', [ShopDashboard::class, 'index'])->name('shop.dashboard');

    Route::get('/shop/profile', [Profile::class, 'index'])->name('shop.profile');
    Route::post('/shop/profile-update', [Profile::class, 'update'])->name('shop.profile-update');

    Route::get('/shop/all-orders', [AllOrders::class, 'index']);
    Route::get('/shop/all-orders-view', [AllOrders::class, 'view'])->name('shop.all-orders-view');

    Route::get('/shop/pending-orders', [ShopPendingOrders::class, 'index']);
    Route::get('/shop/pending-orders-view', [ShopPendingOrders::class, 'view'])->name('shop.pending-orders-view');
    Route::post('/shop/pending-orders-update', [ShopPendingOrders::class, 'update']);
    Route::post('/shop/pending-orders-delete', [ShopPendingOrders::class, 'delete']);
    Route::get('/shop/pending-orders-add-items', [ShopPendingOrdersAddItems::class, 'index'])->name('shop.pending-orders-add-items');
    Route::post('/shop/pending-orders-add-items-process', [ShopPendingOrdersAddItems::class, 'store'])->name('shop.pending-orders-add-items-process');

    Route::get('/shop/processing-orders', [ShopProcessingOrders::class, 'index']);
    Route::get('/shop/processing-orders-view', [ShopProcessingOrders::class, 'view'])->name('shop.processing-orders-view');

    Route::get('/shop/under-review-orders', [ShopUnderReviewOrders::class, 'index']);
    Route::get('/shop/under-review-orders-view', [ShopUnderReviewOrders::class, 'view'])->name('shop.under-review-orders-view');

    Route::get('/shop/complete-orders', [ShopCompleteOrders::class, 'index']);
    Route::get('/shop/complete-orders-view', [ShopCompleteOrders::class, 'view'])->name('shop.complete-orders-view');

    Route::get('/shop/default-orders', [DefaultOrders::class, 'index']);
    Route::get('/shop/default-orders-view', [DefaultOrders::class, 'view'])->name('shop.default-orders-view');
    Route::post('/shop/default-orders-delete', [DefaultOrders::class, 'delete'])->name('shop.default-orders-delete');
    Route::post('/shop/default-orders-update-item', [DefaultOrders::class, 'update_item'])->name('shop.default-orders-update-item');
    Route::post('/shop/default-orders-delete-item', [DefaultOrders::class, 'delete_item'])->name('shop.default-orders-delete-item');
    Route::post('/shop/default-orders-add-to-cart', [DefaultOrders::class, 'add_to_cart'])->name('shop.default-orders-add-to-cart');
    Route::get('/shop/default-orders-add-items', [DefaultOrdersAddItems::class, 'index'])->name('shop.default-orders-add-items');
    Route::post('/shop/default-orders-add-items-process', [DefaultOrdersAddItems::class, 'store'])->name('shop.default-orders-add-items-process');

    Route::get('/shop/create-order', [CreateOrders::class, 'index']);
    Route::post('/shop/add-to-cart', [CreateOrders::class, 'add_to_cart'])->name('shop.add-to-cart');
    Route::post('/shop/add-to-cart-all', [CreateOrders::class, 'addAllToCart'])->name('shop.add-to-cart-all');

    Route::get('/shop/cart', [ShopCartProcess::class, 'index']);
    Route::post('/shop/cart/update-qty', [ShopCartProcess::class, 'update_qty']);
    Route::post('/shop/cart/delete-item', [ShopCartProcess::class, 'delete_item']);
    Route::post('/shop/cart/order-process', [ShopCartProcess::class, 'create_order']);
});

Route::get('/404', function () {
    return view('404');
});



