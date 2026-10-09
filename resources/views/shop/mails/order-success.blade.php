<!-- END config_lk.htm -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="en" style="opacity: 1;">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="ie=edge" http-equiv="X-UA-Compatible" />
    <title> Your parcel(s) have been delivered!
    </title>
    <style type="text/css">
        /* @author: Trung Dao */
        /* @version: 1.2 - 2018-03-05 */
        /* General */
        ul {
            margin: 0;
            padding-left: 30px;
            list-style-type: square;
        }

        li {
            padding-left: 10px;
            margin-top: 5px;
        }

        li:first-child {
            margin-top: 0 !important;
        }

        a {
            color: #f15722 !important;
            text-decoration: none;
        }

        a:visited {
            color: #f15722 !important;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .main-content {
            margin: auto;
            max-width: 730px;
        }

        h2 {
            font-size: 16px !important;
            /*18px*/
            font-weight: normal;
            margin-top: 0;
            margin-bottom: 0;
        }

        /* Helper */
        .hide {
            display: none;
        }

        .text-orange-normal {
            color: #f27c24;
            font-size: 14px;
            /*16px*/
        }

        .text-blue-normal {
            color: #f15722;
            font-size: 14px;
        }

        .text-red-normal {
            color: #DD3937;
            font-size: 14px;
            /*16px*/
        }

        .text-blue-big {
            color: #173948;
            font-size: 20px;
            /*22px*/
        }

        /* Button */
        .button {
            background: #f15722 !important;
            /*#f36e20*/
            color: #fff !important;
            padding: 12px 25px !important;
            display: block !important;
            text-align: center;
            text-transform: uppercase;
            min-width: 140px;
            cursor: pointer;
            border-bottom: 1px solid #8f8f8f;
            border-right: 1px solid #8f8f8f;
        }

        .button:visited {
            background: #f15722 !important;
            /*#f36e20*/
            color: #fff !important;
            padding: 12px 25px !important;
            display: block !important;
            text-align: center;
            text-transform: uppercase;
            min-width: 140px;
            cursor: pointer;
            border-bottom: 1px solid #8f8f8f;
            border-right: 1px solid #8f8f8f;
        }

        .button:hover {
            background: #C2FFFA !important;
            /*#db5609*/
            color: #15722 !important;
            text-decoration-color: #f15722 !important;
            text-decoration: #f15722 !important;
        }

        .button--blue {
            background: #FC2FFFA !important;
            text-decoration-color: #f15722 !important;
        }

        .button--blue:visited {
            background: #C2FFFA !important;
            text-decoration-color: #f15722 !important;
        }

        .button--blue:hover {
            background: #C2FFFA !important;
            text-decoration-color: #f15722 !important;
        }

        /* Columns */
        .two_col {
            width: 100% !important;
            float: none !important;
            max-width: none !important;
        }

        .two-column-left {
            float: left;
            width: 49%;
            overflow-wrap: break-word;
        }

        .two-column-right {
            float: right;
            width: 49%;
            overflow-wrap: break-word;
        }

        @media screen and (max-width:640px) {

            .two-column-left,
            .two-column-right {
                width: 100%;
            }

            .two-column-left {
                margin-bottom: 15px;
            }
        }

        /* Header */
        .header {
            margin-bottom: 25px;
        }

        .header-title {
            color: #f15722;
            text-align: center;
            padding: 10px 30px 10px 30px;
            font-size: 23px;
            /*25px*/
            font-weight: normal;
        }

        .header-progressBar img {
            max-height: 140px !important;
            width: auto;
            max-width: 95%;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }

        .header-subText {
            text-align: center;
            font-style: italic;
            font-size: 12px;
            /*14px*/
        }

        /* Section */
        .section {
            padding: 30px;
            background: #fff;
            /* box-shadow: 1px 2px 5px #888888; */
            /* border-left: 1px solid #f0f0f0; */
            /* border-right: 1px solid #f0f0f0; */
            border-bottom: 10px solid #f0f0f0;
        }

        .section:last-child {
            border-bottom: none !important;
        }

        .section--dark {
            background: #f0f0f0 !important;
        }

        .section-header {
            background-size: 30px;
            background-repeat: no-repeat;
            height: 30px;
            padding: 5px 0px 15px 45px;
            font-size: 16px;
            /*18px*/
        }

        .section-header-delivery {
            background-size: 30px;
            background-repeat: no-repeat;
            height: auto;
            padding: 5px 0px 15px 45px;
            font-size: 16px;
            /*18px*/
        }

        .section-header--yourPackage {
            background-image: url("https://img.alicdn.com/tfs/TB1Y5JLyhn1gK0jSZKPXXXvUXXa-30-30.png");
        }

        .section-header--whatsNext {
            background-image: url("https://img.alicdn.com/tfs/TB17_BJyfb2gK0jSZK9XXaEgFXa-35-27.jpg");
        }

        .section-header--deliveredTo {
            background-image: url("https://img.alicdn.com/tfs/TB1ciNPybj1gK0jSZFOXXc7GpXa-48-48.png");
        }

        .section-header--deliveredTo2 {
            background-image: url("https://img.alicdn.com/tfs/TB1ciNPybj1gK0jSZFOXXc7GpXa-48-48.png");
        }

        .section-header--itemsDetails {
            background-image: url("https://img.alicdn.com/tfs/TB1tOXQyeH2gK0jSZJnXXaT1FXa-30-30.png");
        }

        .section-header--notes {
            background-image: url("https://img.alicdn.com/tfs/TB1F.pMyeH2gK0jSZFEXXcqMpXa-30-30.png");
        }

        .section-content {
            /* display:inline-block; */
            width: 100%;
        }

        .section-content p:first-child {
            margin-top: 0;
        }

        .section-content p:last-child {
            margin-bottom: 0;
        }

        .section-content--justify {
            text-align: justify !important;
        }

        /* Shipment Index */
        .shipmentIndex {
            width: 100%;
            background-color: #e6edfe !important;
            font-size: 13px;
            /*15px*/
            border-left: 5px solid #42537d;
        }

        .shipmentIndex p {
            padding: 10px;
        }

        /* Product */
        .product {
            padding-top: 20px;
            padding-bottom: 20px;
            /* display: inline-block; */
            display: flex;
            width: 100%;
        }

        .product:first-child {
            padding-top: 0 !important;
        }

        .product-productImage {
            width: 40%;
            vertical-align: top;
            float: left;
            /* margin-right: 40px;  */
        }

        .product-productImage img {
            max-width: 160px;
            height: auto;
            margin-left: auto;
        }

        .product-productInfo {
            vertical-align: top;
            width: 60%;
            overflow-wrap: break-word;
            float: left;
        }

        /*
                    @media screen and (max-width:720px) {
                      .product-productInfo {
                        width: 100%;
                        margin-bottom: 15px;
                      }
                    } */
        .product-productInfo-name {
            margin-bottom: 7px;
        }

        .product-productInfo-name a {
            color: #173948 !important;
            font-size: 14px !important;
            /*16px*/
            text-decoration: none !important;
        }

        .product-productInfo-price {
            color: #f27c24;
            font-size: 14px;
            /*16px*/
        }

        .product-productInfo-subInfo {
            color: #585858;
            font-size: 13px;
            /*15px*/
        }

        .product-productInfo-button {
            margin-top: 2px;
        }

        .product-productInfo-button a {
            background-size: 18px;
            background-repeat: no-repeat;
            background-position: bottom left;
            height: 20px;
            padding-left: 25px;
            padding-right: 15px;
            font-size: 13px;
            /*15px*/
            text-decoration: none !important;
            vertical-align: middle;
            display: inline-block !important;
        }

        .product-productInfo-button a:hover {
            text-decoration: underline !important;
        }

        .product-productInfo-button a.product-productInfo-button--fbShare {
            background-image: url("https://img.alicdn.com/tfs/TB1jwVTylr0gK0jSZFnXXbRRXXa-18-18.png");
            color: #3b5998 !important;
        }

        .product-productInfo-button a.product-productInfo-button--fbMessenger {
            background-image: url("https://img.alicdn.com/tfs/TB1dRxLyoY1gK0jSZFMXXaWcVXa-30-30.png");
            color: #3b5998 !important;
        }

        /* Check out */
        .checkout {
            display: inline-block;
            width: 100%;
            margin-top: 20px;
        }

        .checkout-info {
            background-size: 30px;
            background-repeat: no-repeat;
            height: 30px;
            color: #9AA439;
            padding: 5px 0 5px 40px;
            overflow-wrap: normal;
        }

        .checkout-info:first-child {
            margin-top: 15px;
        }

        .checkout-info--deliveryType {
            background-image: url("https://img.alicdn.com/tfs/TB1lDlNyi_1gK0jSZFqXXcpaXXa-30-30.png");
        }

        .checkout-info--paymentMethod {
            background-image: url("https://img.alicdn.com/tfs/TB1I98OybH1gK0jSZFwXXc7aXXa-30-30.png");
        }

        .checkout-amount {
            width: 100%;
            line-height: 24px;
        }

        .checkout-amount td {
            height: 35px;
        }

        .checkout-amount tr.total td {
            color: red !important;
        }

        .checkout-amount tr.total:last-child td {
            height: 24px !important;
        }

        .checkout-amount-subtext {
            text-align: right !important;
            font-size: 10px;
            /*12px*/
        }

        .footer {
            /*margin-top: 15px;*/
        }
    </style>
</head>

<body
    style="margin: 0px; padding: 0px; color: rgb(32, 32, 32); font-size: 16px; font-weight: normal; font-family: Helvetica, Arial, sans-serif !important; line-height: 150% !important;">
    <div class="main-content">
        <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tbody>
                <tr>
                    <td colspan="2"></td>
                    <td bgcolor="#E8E8E8" colspan="3" height="1px"></td>
                    <td colspan="3"></td>
                </tr>
                <tr>
                    <!--
                        <td bgcolor="#F8F8F8" width="1px"></td>
                        <td bgcolor="#E8E8E8" width="1px"></td>
                        <td bgcolor="#D1D1D1" width="1px"></td>
                        -->
                    <!-- Main Email Content -->
                    <td>
                        <!-- Header -->
                        <div class="header">
                            <div class="header-banner">
                                <table class="header" lang="header" cellpadding="0" cellspacing="0" width="100%" border="0"
                                    style="width:100%;" align="center">
                                    <tr>
                                        <td width="100%" height="70" valign="top" bgcolor="#FFFFFF"
                                            style="padding-top: 30px; padding-bottom: 5px;background: #FFFFFF; height:70px;">
                                            <table cellpadding="0" cellspacing="0" width="100%" height="70" border="0"
                                                style="width:100%; height:70px;">
                                                <tr>
                                                    <td class="space40" style="width:20px" width="20">
                                                        <div lang="space40"></div>
                                                    </td>
                                                    <td valign="middle" align="center">
                                                        <div class="spacer" style="font-size:5px;line-height:5px; height:5px;">&nbsp; </div>
                                                        <a href="https://c.daraz.lk/t/c.b3?sub_id1=Trade&sub_id2=MY_VOYAGER_OrderConfirmation_COD&sub_id3=20250108&sub_id4=top-logo&url=http%3A%2F%2Fwww.daraz.lk%2F"
                                                            style="text-decoration:none" target="_blank">
                                                            <img
                                                                src="https://peoplesbakers.com/assets/images/logo.png"
                                                                width="209" style="display: block; max-width: 209px; border: none;" />
                                                        </a>
                                                        <div class="spacer" style="font-size:5px;line-height:5px; height:5px;">&nbsp; </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>

                                </table>
                            </div>
                            <!-- Content -->
                            <!-- Content -->
                            <!--<div class="section section--dark">-->
                            <div class="section" style="padding-top: 0px">
                                <div class="header-title" style="color: #f15722; text-align: center;">Thanks for shopping with us!
                                </div>
                                <div class="section" style="padding-top: 0px">

                                    <div class="section-content section-content--justify">
                                        <h2>Hi {{ $details['shop'] }},</h2>

                                        <p>We received your <b>{{ $details['time_period'] }}</b> order and order id is <b>{{ $details['order_id'] }}</b> on <b>{{ $details['date'].' '.$details['time'] }}</b>. We wish you enjoy shopping with us and hope to see you again real soon!</p>
                                    </div>
                                    <div class="spacer" style="font-size: 10px; line-height: 20px; height:20px;">&nbsp;</div>
                                    <div class="two_col" align="center">
                                        <a href="https://peoplesbakers.com/order-admin/dashboard"
                                            target="_blank"><img
                                                src="https://img.alicdn.com/imgextra/i2/O1CN01n5iMjs1HU7y1Xfjmj_!!6000000000760-0-tps-300-51.jpg"
                                                border="0"></a>
                                    </div>
                                </div>

                                <!-- START deliveryto_2019_my.htm -->
                                <div class="section">
                                    <div class="section-header section-header--deliveredTo">Delivery Details</div>
                                    <div class="section-content">
                                        <!--table cellpadding="2" cellspacing="0" style="width: 100%; border-collapse: collapse; "-->
                                        <table cellpadding="2" cellspacing="0" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td width="25%" valign="top" style="color: #f15722; font-weight: bold;">Name:</td>
                                                    <td width="75%" valign="top">{{ $details['shop'] }} </td>
                                                </tr>
                                                <tr>
                                                    <td valign="top" style="color: #f15722; font-weight: bold;">Phone:</td>
                                                    <td valign="top">{{ $details['contact'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td valign="top" style="color: #f15722; font-weight: bold;">Email:</td>
                                                    <td valign="top">{{ $details['email'] }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- END deliveryto_2019_my.htm -->

                                <!-- START orderdetail_2019_my.htm -->
                                <div class="section" style="padding-bottom: 0px">
                                    <div class="section-content">

                                        <div class="section-header section-header--yourPackage">Your Items</div>
                                        @foreach ($details['items'] as $item)
                                        <div class="product" style="border-bottom: 0px none">
                                            <table cellpadding="0" cellspacing="0" width="100%">
                                                <tr style="border: 1px solid black; border-radius: 8px;">
                                                    <td width="40%">
                                                        <div class="" style="padding-right: 10px; ">
                                                            <a><img
                                                                    src="https://peoplesbakers.com/assets/images/item-images/{{$item->img}}"
                                                                    style="width: 200px; height: 200px;" /></a>
                                                        </div>
                                                    </td>
                                                    <td width="60%">
                                                        <div class="product-productInfo-name">
                                                            <a><span
                                                                    style="font-size:14px;">{{ $item->name }}</span></a>
                                                        </div>
                                                        <div class="product-productInfo-price"><span style="font-size:14px;">Rs. {{ number_format($item->price) }}</span></div>
                                                        <div class="product-productInfo-subInfo"><span style="font-size:14px;">Quantity: {{ $item->qty }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!--<div class="section">-->
                                <div class="section" style="padding-top: 0px">
                                    <div class="section-content">
                                        <div class="checkout">
                                            <div class="two_col">

                                                <table cellpadding="0" cellspacing="0" class="checkout-amount"
                                                    style="border-bottom: 1px solid #D8D8D8;">
                                                    <tr>
                                                        <td valign="top" style="color: #585858;">Subtotal:</td>
                                                        <td align="right" valign="top">Rs. </td>
                                                        <td align="right" valign="top">{{ number_format($details['total_price'])}}</td>
                                                    </tr>
                                                    <td valign="top" style="color: #585858;">Total (inclusive of tax, if any):</td>
                                                    <td align="right" valign="top">
                                                        <div style="color:#f27c24;font-weight:bold;">Rs.</div>
                                                    </td>
                                                    <td align="right" valign="top">
                                                        <div style="color:#f27c24;font-weight:bold;">{{ number_format($details['total_price'])}}</div>
                                                    </td>
                </tr>

        </table>
        <br />

        <table cellpadding="0" cellspacing="0" class="checkout-amount">
            <tr>
                <td valign="top" style="color: #585858; width: 49%">Shipping option:</td>
                <td align="right" valign="top" colspan="2">Standard</td>
            </tr>
            <tr>
                <td valign="top" style="color: #585858; width: 49%">Paid by:</td>
                <td align="right" valign="top" colspan="2">Cash On Delivery</td>
            </tr>
        </table>
    </div>
    </div>
    </div>
    </div>
    <!-- END orderdetail_2019_my.htm -->

    <tr>
        <td style="margin-bottom:10px;" align="center">
            <table class="responsive" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody>
                    <tr>
                        <td style="border-collapse:collapse; text-size-adjust:100%;">
                            <table class="responsive" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tbody>
                                    <tr>
                                        <td align="center" style="padding:10px 5px 10px 5px;">
                                            <font class="footer" size="2"
                                                style="color:#202020; font-family:Helvetica, Arial, sans-serif; line-height:1.4em;">
                                                This is an automatically generated email from our subscription list. Please do not
                                                reply to this email.
                                                <!-- Click here if you wish to 
                <a href="https%3A%2F%2Fmember.daraz.lk%2Fuser%2Faccount%23%2F" style="text-decoration:underline; color:#f15722;"><strong>unsubscribe</strong></a>./-->
                                            </font>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
    </table>
    </td>
    </tr>
    <!-- Footer -->
    </div>
</body>

</html>
<!-- Order Being Processed #218259050618775 -->