// let url = 'http://localhost/peoples_bakers';
let url = '../controllers';

function login() {
    let lg_u_name = document.getElementById('lg_u_name').value;
    let lg_pass = document.getElementById('lg_pass').value;
    let lg_warn = document.getElementById('lg_warn');
    console.log(lg_u_name + ' ' + lg_pass);
    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'Please enter username') {
                lg_warn.innerHTML = t;
            } else if (t == 'Please enter password') {
                lg_warn.innerHTML = t;
            } else if (t == '1') {
                window.location.href = 'super_admin.php';
            } else if (t == '2') {
                window.location.href = 'ordering_admin.php';
            } else if (t == '3') {
                window.location.href = 'shop.php';
            } else if (t == '4') {
                window.location.href = 'rep.php';
            } else {
                lg_warn.innerHTML = t;
            }
        }
    }
    var f = new FormData();
    f.append('lg_u_name', lg_u_name);
    f.append('lg_pass', lg_pass);
    r.open("POST", url + "/login_process.php", true);
    r.send(f);
};

function log_out() {
    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            }
        }
    }

    r.open("POST", url + "/logout_process.php", true);
    r.send();
};

function add_products_ordering_admin() {
    // Retrieve form input values
    let fileInput = document.getElementById('file');
    let itemNumInput = document.getElementById('item_num');
    let itemNameEInput = document.getElementById('item_name_e');
    let itemNameSInput = document.getElementById('item_name_s');
    let categoryInput = document.getElementById('category');
    let visibilityInput = document.getElementById('visibility');
    let pbUnitPriceInput = document.getElementById('pb_unit_price');
    let pbMrpInput = document.getElementById('pb_mrp');
    let pbDirectSalePriceInput = document.getElementById('pb_direct_sale_price');

    if (fileInput.files.length == 0) {
        alert('Pleace select a item image');
    } else {
        // Create FormData object
        let formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('item_num', itemNumInput.value);
        formData.append('item_name_e', itemNameEInput.value);
        formData.append('item_name_s', itemNameSInput.value);
        formData.append('category', categoryInput.value);
        formData.append('visibility', visibilityInput.value);
        formData.append('pb_unit_price', pbUnitPriceInput.value);
        formData.append('pb_mrp', pbMrpInput.value);
        formData.append('pb_direct_sale_price', pbDirectSalePriceInput.value);

        // Create XMLHttpRequest object
        let xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4) {
                if (xhr.status == 200) {
                    let responseText = xhr.responseText;
                    console.log(responseText);
                    if (responseText == 'Success') {
                        alert(responseText);
                        location.reload();
                    } else {
                        alert(responseText);
                    }
                } else {
                    alert("Error: " + xhr.status);
                }
            }
        };

        // Set up and send the request
        xhr.open("POST", url + "/ordering_admin_add_product_process.php", true);
        xhr.send(formData);
    }

}

function addProductCategory_ordering_admin() {
    let categoryNameInput = document.getElementById('item_catgry');

    // Create FormData object
    let formData = new FormData();
    formData.append('category_name', categoryNameInput.value);

    // Create XMLHttpRequest object
    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4) {
            if (xhr.status == 200) {
                let responseText = xhr.responseText;
                if (responseText == 'success') {
                    alert(responseText);
                    location.reload();
                } else {
                    alert(responseText);
                }
                // Optionally, you can redirect or perform additional actions on success
            } else {
                alert("Error: " + xhr.status);
            }
        }
    };

    // Set up and send the request
    xhr.open("POST", url + "/addProductCategory_ordering_admin.php", true);
    xhr.send(formData);

}

function delete_item(pr_id) {
    // alert(item_id);
    let text = "Are you sure want to delete";
    if (confirm(text) == true) {
        let r = new XMLHttpRequest();
        r.onreadystatechange = () => {
            if (r.readyState == 4 && r.status == 200) {
                let t = r.responseText;
                console.log(t);
                if (t == 'success') {
                    window.location.reload();
                }
            }
        }
        var f = new FormData();
        f.append('pr_id', pr_id);
        r.open("POST", url + "/delete_item_process.php", true);
        r.send(f);
    }
}

function ordering_admin_all_items_srch() {
    var ordering_admin_all_items_srch = document.getElementById('ordering_admin_all_items_srch');
    window.location.href = 'ordering_admin_all_products.php?srch=' + ordering_admin_all_items_srch.value;
}

function shop_create_order_item_srch() {
    var shop_create_order_item_srch = document.getElementById('shop_create_order_item_srch');
    window.location.href = 'shop_create_orders.php?srch=' + shop_create_order_item_srch.value;
}

function rep_create_order_item_srch() {
    var rep_create_order_item_srch = document.getElementById('rep_create_order_item_srch');
    window.location.href = 'rep_create_orders.php?srch=' + rep_create_order_item_srch.value;
}

function ordering_admin_create_order_item_srch() {
    var ordering_admin_create_order_item_srch = document.getElementById('ordering_admin_create_order_item_srch');
    window.location.href = 'ordering_admin_create_order.php?srch=' + ordering_admin_create_order_item_srch.value;
}

function shop_pending_order_add_item_srch(id) {
    var shop_pending_order_add_item_ctgr = document.getElementById('shop_pending_order_add_item_ctgr');
    var shop_pending_order_add_item_srch = document.getElementById('shop_pending_order_add_item_srch');
    window.location.href = 'shop_all_pending_orders_view_add_Items.php?id=' + id + '&srch=' + shop_pending_order_add_item_srch.value + '&ctgr=' + shop_pending_order_add_item_ctgr.value;
}

function shop_create_order_item_ctgr() {
    var shop_create_order_item_ctgr = document.getElementById('shop_create_order_item_ctgr');
    window.location.href = 'shop_create_orders.php?ctgr=' + shop_create_order_item_ctgr.value;
}

function rep_create_order_item_ctgr() {
    var rep_create_order_item_ctgr = document.getElementById('rep_create_order_item_ctgr');
    window.location.href = 'rep_create_orders.php?ctgr=' + rep_create_order_item_ctgr.value;
}

function ordering_create_order_item_ctgr() {
    var ordering_create_order_item_ctgr = document.getElementById('ordering_create_order_item_ctgr');
    window.location.href = 'ordering_admin_create_order.php?ctgr=' + ordering_create_order_item_ctgr.value;
}

function rep_pending_order_all_items_ctgr(id, sp_id) {
    var rep_pending_order_all_items_ctgr = document.getElementById('rep_pending_order_all_items_ctgr');
    window.location.href = 'rep_all_pending_orders_view_add_Items.php?id=' + id + '&sp_id=' + sp_id + '&ctgr=' + rep_pending_order_all_items_ctgr.value;
}

function rep_processing_order_all_items_ctgr(id, sp_id) {
    var rep_processing_order_all_items_ctgr = document.getElementById('rep_processing_order_all_items_ctgr');
    window.location.href = 'rep_all_processing_orders_view_add_Items.php?id=' + id + '&sp_id=' + sp_id + '&ctgr=' + rep_processing_order_all_items_ctgr.value;
}

function shop_pending_order_add_item_ctgr(id) {
    var shop_pending_order_add_item_ctgr = document.getElementById('shop_pending_order_add_item_ctgr');
    window.location.href = 'shop_all_pending_orders_view_add_Items.php?id=' + id + '&ctgr=' + shop_pending_order_add_item_ctgr.value;
}

function ordering_admin_all_items_catgry() {
    var ordering_admin_all_items_catgry = document.getElementById('ordering_admin_all_items_catgry');
    if (ordering_admin_all_items_catgry.value == 'x') {
        window.location.href = 'ordering_admin_all_products.php';
    } else {
        window.location.href = 'ordering_admin_all_products.php?ctgr=' + ordering_admin_all_items_catgry.value;
    }
}

function add_route() {
    var route_index = document.getElementById('route_index');
    var route_name = document.getElementById('route_name');
    var route_type = document.getElementById('route_type');

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('route_index', route_index.value);
    f.append('route_name', route_name.value);
    f.append('route_type', route_type.value);
    r.open("POST", url + "/add_route_process.php", true);
    r.send(f);
}

function order_admin_add_shop() {
    var sp_uname = document.getElementById('sp_uname');
    var sp_branch_code = document.getElementById('sp_branch_code');
    var sp_email = document.getElementById('sp_email');
    var sp_contact = document.getElementById('sp_contact');
    var sp_price_range = document.getElementById('sp_price_range');
    var sp_route = document.getElementById('sp_route');
    var sp_shop_type = document.getElementById('sp_shop_type');
    var sp_order_time = document.getElementById('sp_order_time');
    var sp_pass = document.getElementById('sp_pass');
    var sp_confirm_pass = document.getElementById('sp_confirm_pass');

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('sp_uname', sp_uname.value);
    f.append('sp_branch_code', sp_branch_code.value);
    f.append('sp_email', sp_email.value);
    f.append('sp_contact', sp_contact.value);
    f.append('sp_price_range', sp_price_range.value);
    f.append('sp_route', sp_route.value);
    f.append('sp_shop_type', sp_shop_type.value);
    f.append('sp_order_time', sp_order_time.value);
    f.append('sp_pass', sp_pass.value);
    f.append('sp_confirm_pass', sp_confirm_pass.value);
    r.open("POST", url + "/add_shop_process.php", true);
    r.send(f);
}

function add_rep() {
    var rp_uname = document.getElementById('rp_uname');
    var rp_fname = document.getElementById('rp_fname');
    var rp_lname = document.getElementById('rp_lname');
    var rp_type = document.getElementById('rp_type');
    var rp_pass = document.getElementById('rp_pass');
    var rp_cpass = document.getElementById('rp_cpass');

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('rp_uname', rp_uname.value);
    f.append('rp_fname', rp_fname.value);
    f.append('rp_lname', rp_lname.value);
    f.append('rp_type', rp_type.value);
    f.append('rp_pass', rp_pass.value);
    f.append('rp_cpass', rp_cpass.value);
    r.open("POST", url + "/add_rep_process.php", true);
    r.send(f);
}

function add_to_cart(item_id, qty_id, price) {
    var qty = document.getElementById(qty_id).value;
    console.log(item_id + ' ' + qty_id + ' ' + price);

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('item_id', item_id);
    f.append('qty', qty);
    f.append('price', price);
    r.open("POST", url + "/add_to_cart_process.php", true);
    r.send(f);
}

function rep_add_to_cart(item_id, qty_id) {
    var qty = document.getElementById(qty_id).value;
    var shop_id = document.getElementById('rep_create_order_shop_id').value;
    console.log(item_id + ' ' + qty_id + ' ');

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('item_id', item_id);
    f.append('qty', qty);
    f.append('shop_id', shop_id);
    r.open("POST", url + "/rep_add_to_cart_process.php", true);
    r.send(f);
}

function ordering_admin_add_to_cart(item_id, qty_id) {
    var qty = document.getElementById(qty_id).value;
    var shop_id = document.getElementById('ordering_admin_create_order_shop_id').value;
    console.log(item_id + ' ' + qty_id + ' ');

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('item_id', item_id);
    f.append('qty', qty);
    f.append('shop_id', shop_id);
    r.open("POST", url + "/ordering_admin_add_to_cart_process.php", true);
    r.send(f);
}

function add_all_to_cart(items) {
    var itemsArr = [];
    for (let index = 0; index < items.length; index++) {
        var x = document.getElementById(items[index]).value;
        if (x !== '') {
            itemsArr.push(x);
        }
    }
    console.log(itemsArr);
}

function add_to_pending_orders(item_id, qty_id, price_id, sp_id, order_id) {
    var qty = document.getElementById(qty_id).value;
    var price = document.getElementById(price_id).innerHTML;
    console.log(item_id + ' ' + qty);

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                alert(t);
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('item_id', item_id);
    f.append('qty', qty);
    f.append('price', price);
    f.append('sp_id', sp_id);
    f.append('order_id', order_id);
    r.open("POST", url + "/add_to_pending_orders_process.php", true);
    r.send(f);
}

function rep_add_to_pending_orders(item_id, qty_id, price_id, sp_id, order_id) {
    var qty = document.getElementById(qty_id).value;
    var price = document.getElementById(price_id).innerHTML;
    console.log(item_id + ' ' + qty + ' ' + price + ' ' + sp_id + ' ' + order_id);

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                alert(t);
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('item_id', item_id);
    f.append('qty', qty);
    f.append('price', price);
    f.append('sp_id', sp_id);
    f.append('order_id', order_id);
    r.open("POST", url + "/rep_add_to_pending_orders_process.php", true);
    r.send(f);
}

function rep_add_to_processing_orders(item_id, qty_id, price_id, sp_id, order_id) {
    var qty = document.getElementById(qty_id).value;
    var price = document.getElementById(price_id).innerHTML;
    console.log(item_id + ' ' + qty + ' ' + price + ' ' + sp_id + ' ' + order_id);

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                alert(t);
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('item_id', item_id);
    f.append('qty', qty);
    f.append('price', price);
    f.append('sp_id', sp_id);
    f.append('order_id', order_id);
    r.open("POST", url + "/rep_add_to_processing_orders_process.php", true);
    r.send(f);
}

function shop_add_to_pending_orders(item_id, qty_id, price, sp_id, order_id) {
    var qty = document.getElementById(qty_id).value;
    console.log(item_id + ' ' + ' ' + qty_id + qty + ' ' + price + ' ' + sp_id + ' ' + order_id);

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                alert(t);
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('item_id', item_id);
    f.append('qty', qty);
    f.append('price', price);
    f.append('order_id', order_id);
    f.append('sp_id', sp_id);
    r.open("POST", url + "/shop_add_to_pending_orders_process.php", true);
    r.send(f);
}

function add_to_processing_orders(item_id, qty_id, price_id, sp_id, order_id) {
    var qty = document.getElementById(qty_id).value;
    var price = document.getElementById(price_id).innerHTML;
    console.log(item_id + ' ' + qty);

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                alert(t);
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('item_id', item_id);
    f.append('qty', qty);
    f.append('price', price);
    f.append('sp_id', sp_id);
    f.append('order_id', order_id);
    console.log(f);
    r.open("POST", url + "/add_to_processing_orders_process.php", true);
    r.send(f);
}

function assign_shop_rep() {
    var assign_rep = document.getElementById('assign_rep');
    var assign_shop = document.getElementById('assign_shop');

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('assign_rep', assign_rep.value);
    f.append('assign_shop', assign_shop.value);
    r.open("POST", url + "/assign_shop_rep_process.php", true);
    r.send(f);
}

function delete_from_cart(cart_id) {
    let text = "Are you sure want to delete this cart item ?";
    if (confirm(text) == true) {
        let r = new XMLHttpRequest();
        r.onreadystatechange = () => {
            if (r.readyState == 4 && r.status == 200) {
                let t = r.responseText;
                console.log(t);
                if (t == 'success') {
                    window.location.reload();
                } else {
                    alert(t);
                }
            }
        }
        var f = new FormData();
        f.append('cart_id', cart_id);
        r.open("POST", url + "/delete_cart_item_process.php", true);
        r.send(f);
    }
}

function rep_pending_order_delete(cart_id, qty_id, price) {
    let text = "Are you sure want to delete this cart item ?";
    if (confirm(text) == true) {
        var qty = document.getElementById(qty_id).value;
        let r = new XMLHttpRequest();
        r.onreadystatechange = () => {
            if (r.readyState == 4 && r.status == 200) {
                let t = r.responseText;
                console.log(t);
                if (t == 'success') {
                    window.location.reload();
                } else {
                    alert(t);
                }
            }
        }
        var f = new FormData();
        f.append('cart_id', cart_id);
        f.append('qty', qty);
        f.append('price', price);
        r.open("POST", url + "/rep_pending_order_delete_process.php", true);
        r.send(f);
    }
}

function rep_processing_order_delete(cart_id, qty_id, price) {
    let text = "Are you sure want to delete this cart item ?";
    if (confirm(text) == true) {
        var qty = document.getElementById(qty_id).value;
        let r = new XMLHttpRequest();
        r.onreadystatechange = () => {
            if (r.readyState == 4 && r.status == 200) {
                let t = r.responseText;
                console.log(t);
                if (t == 'success') {
                    window.location.reload();
                } else {
                    alert(t);
                }
            }
        }
        var f = new FormData();
        f.append('cart_id', cart_id);
        f.append('qty', qty);
        f.append('price', price);
        r.open("POST", url + "/rep_processing_order_delete_process.php", true);
        r.send(f);
    }
}

function o_admin_processing_order_delete(cart_id, qty_id, price) {
    let text = "Are you sure want to delete this cart item ?";
    if (confirm(text) == true) {
        var qty = document.getElementById(qty_id).value;
        let r = new XMLHttpRequest();
        r.onreadystatechange = () => {
            if (r.readyState == 4 && r.status == 200) {
                let t = r.responseText;
                console.log(t);
                if (t == 'success') {
                    window.location.reload();
                } else {
                    alert(t);
                }
            }
        }
        var f = new FormData();
        f.append('cart_id', cart_id);
        f.append('qty', qty);
        f.append('price', price);
        r.open("POST", url + "/o_admin_processing_order_delete_process.php", true);
        r.send(f);
    }
}

function update_cart_item(cart_id, qty_id) {
    var qty = document.getElementById(qty_id).value;

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('cart_id', cart_id);
    f.append('qty', qty);
    r.open("POST", url + "/update_cart_item_process.php", true);
    r.send(f);
}

function o_admin_pending_order_update(cart_id, qty_id, price_range) {
    var qty = document.getElementById(qty_id).value;

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('cart_id', cart_id);
    f.append('qty', qty);
    f.append('price_range', price_range);
    r.open("POST", url + "/o_admin_pending_order_update_process.php", true);
    r.send(f);
}

function o_admin_pending_order_delete(cart_id, qty_id, price) {
    let text = "Are you sure want to delete this cart item ?";
    if (confirm(text) == true) {
        var qty = document.getElementById(qty_id).value;
        let r = new XMLHttpRequest();
        r.onreadystatechange = () => {
            if (r.readyState == 4 && r.status == 200) {
                let t = r.responseText;
                console.log(t);
                if (t == 'success') {
                    window.location.reload();
                } else {
                    alert(t);
                }
            }
        }
        var f = new FormData();
        f.append('cart_id', cart_id);
        f.append('qty', qty);
        f.append('price', price);
        r.open("POST", url + "/o_admin_pending_order_delete_process.php", true);
        r.send(f);
    }
}

function shop_pending_order_update_item(cart_id, qty_id, price_range) {
    var qty = document.getElementById(qty_id).value;

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('cart_id', cart_id);
    f.append('qty', qty);
    f.append('price_range', price_range);
    r.open("POST", url + "/shop_pending_order_update_item_process.php", true);
    r.send(f);
}

function shop_pending_order_delete_item(cart_id, qty_id) {
    console.log('shop_pending_order_delete_item');
    var qty = document.getElementById(qty_id).value;

    var cnf = confirm('Are you sure to delete this item?');
    if (cnf) {
        let r = new XMLHttpRequest();
        r.onreadystatechange = () => {
            if (r.readyState == 4 && r.status == 200) {
                let t = r.responseText;
                console.log(t);
                if (t == 'success') {
                    window.location.reload();
                } else {
                    alert(t);
                }
            }
        }
        var f = new FormData();
        f.append('cart_id', cart_id);
        f.append('qty', qty);
        r.open("POST", url + "/shop_pending_order_delete_item_process.php", true);
        r.send(f);
    }
}

function o_admin_processing_order_update(cart_id, qty_id, price) {
    var qty = document.getElementById(qty_id).value;

    console.log(cart_id, qty, price);

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('cart_id', cart_id);
    f.append('qty', qty);
    f.append('price', price);
    r.open("POST", url + "/o_admin_processing_order_update_process.php", true);
    r.send(f);
}

function rep_pending_order_update(cart_id, qty_id, price) {
    var qty = document.getElementById(qty_id).value;

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('cart_id', cart_id);
    f.append('qty', qty);
    f.append('price', price);
    r.open("POST", url + "/rep_pending_order_update_process.php", true);
    r.send(f);
}

function rep_processing_order_update(cart_id, qty_id, price) {
    var qty = document.getElementById(qty_id).value;

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('cart_id', cart_id);
    f.append('qty', qty);
    f.append('price', price);
    r.open("POST", url + "/rep_processing_order_update_process.php", true);
    r.send(f);
}

function order_proceed(sp_id, total) {
    var order_time = document.getElementById('order_time');
    var order_note = document.getElementById('order_note');

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                alert(t);
                window.location.reload();
            } else if (t == 'Your order placed and estimated total is over Rs. 40,00000 and our admin will review it') {
                alert(t);
                location.reload();
            } else {
                alert(t);
            }
        }
    }

    var f = new FormData();
    f.append('sp_id', sp_id);
    f.append('total', total);
    if (order_time !== null) {
        f.append('order_time', order_time.value);
    }
    f.append('order_note', order_note.value);
    r.open("POST", url + "/order_proceed_process.php", true);
    r.send(f);
}

function rep_order_proceed(sp_id, total) {
    var order_time = document.getElementById('order_time');
    var order_note = document.getElementById('order_note');

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                alert(t);
                window.location.reload();
            } else if (t == 'Your order placed and estimated total is over Rs. 40,00000 and our admin will review it') {
                alert(t);
                location.reload();
            } else {
                alert(t);
            }
        }
    }

    var f = new FormData();
    f.append('sp_id', sp_id);
    f.append('total', total);
    if (order_time !== null) {
        f.append('order_time', order_time.value);
    }
    f.append('order_note', order_note.value);
    r.open("POST", url + "/rep_order_proceed_process.php", true);
    r.send(f);
}

function ordering_admin_order_proceed(sp_id, total) {
    var order_time = document.getElementById('order_time');
    var order_note = document.getElementById('order_note');

    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                alert(t);
                window.location.reload();
            } else if (t == 'Your order placed and estimated total is over Rs. 40,00000 and our admin will review it') {
                alert(t);
                location.reload();
            } else {
                alert(t);
            }
        }
    }

    var f = new FormData();
    f.append('sp_id', sp_id);
    f.append('total', total);
    if (order_time !== null) {
        f.append('order_time', order_time.value);
    }
    f.append('order_note', order_note.value);
    r.open("POST", url + "/ordering_admin_order_proceed_process.php", true);
    r.send(f);
}

function o_admin_accept_pending_orders(order_id) {
    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.href = 'ordering_admin_new_orders.php';
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('order_id', order_id);
    r.open("POST", url + "/o_admin_accept_pending_orders_process.php", true);
    r.send(f);
}

function rep_accept_pending_orders(order_id) {
    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.href = 'rep_all_pending_orders.php';
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('order_id', order_id);
    r.open("POST", url + "/rep_accept_pending_orders_process.php", true);
    r.send(f);
}

function rep_create_order_shop_id_select() {
    var sp_id = document.getElementById('rep_create_order_shop_id');
    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('sp_id', sp_id.value);
    r.open("POST", url + "/rep_create_order_shop_id_select_process.php", true);
    r.send(f);
}

function ordering_admin_create_order_shop_id_select() {
    var sp_id = document.getElementById('ordering_admin_create_order_shop_id');
    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('sp_id', sp_id.value);
    r.open("POST", url + "/ordering_admin_create_order_shop_id_select_process.php", true);
    r.send(f);
}

function create_default_order(total) {
    var default_order_name = document.getElementById('default_order_name').value;
    console.log(default_order_name);
    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('default_order_name', default_order_name);
    f.append('total', total);
    r.open("POST", url + "/create_default_order_process.php", true);
    r.send(f);
}

function add_to_cart_default_order(default_order_name) {
    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('default_order_name', default_order_name);
    r.open("POST", url + "/add_to_cart_default_order_process.php", true);
    r.send(f);
}

function delete_default_order(default_order_name) {
    let text = "Are you sure want to delete this order ?";
    if (confirm(text) == true) {
        let r = new XMLHttpRequest();
        r.onreadystatechange = () => {
            if (r.readyState == 4 && r.status == 200) {
                let t = r.responseText;
                console.log(t);
                if (t == 'success') {
                    window.location.reload();
                } else {
                    alert(t);
                }
            }
        }
        var f = new FormData();
        f.append('default_order_name', default_order_name);
        r.open("POST", url + "/delete_default_order_process.php", true);
        r.send(f);
    }
}

function update_note_rep(order_id, note_id) {
    var note = document.getElementById(note_id).value;
    let r = new XMLHttpRequest();
    r.onreadystatechange = () => {
        if (r.readyState == 4 && r.status == 200) {
            let t = r.responseText;
            console.log(t);
            if (t == 'success') {
                window.location.reload();
            } else {
                alert(t);
            }
        }
    }
    var f = new FormData();
    f.append('note', note);
    f.append('order_id', order_id);
    r.open("POST", url + "/update_note_rep_process.php", true);
    r.send(f);
}