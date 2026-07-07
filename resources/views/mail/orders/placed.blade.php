<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #7c4435 0%, #8b5a44 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 1px;
        }

        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .section {
            margin-bottom: 30px;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-header {
            background-color: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 2px solid #7c4435;
        }

        .section-header h2 {
            margin: 0;
            color: #7c4435;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-content {
            padding: 20px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-table td:first-child {
            font-weight: 600;
            color: #495057;
            width: 40%;
        }

        .info-table td:last-child {
            text-align: right;
            color: #212529;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            background-color: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .items-table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            font-size: 14px;
        }

        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .product-name {
            font-weight: 600;
            color: #212529;
            margin-bottom: 4px;
        }

        .product-variant {
            font-size: 12px;
            color: #6c757d;
            font-style: italic;
        }

        .total-row {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .total-amount {
            color: #7c4435;
            font-size: 18px;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #7c4435 0%, #8b5a44 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(124, 68, 53, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 68, 53, 0.4);
        }

        .next-steps {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #7c4435;
        }

        .next-steps h3 {
            color: #7c4435;
            margin: 0 0 15px 0;
            font-size: 16px;
        }

        .next-steps ul {
            margin: 0;
            padding-left: 20px;
            color: #6c757d;
        }

        .next-steps li {
            margin-bottom: 8px;
        }

        .footer {
            background-color: #343a40;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .footer h3 {
            margin: 0 0 15px 0;
            font-size: 18px;
            font-weight: 300;
        }

        .footer p {
            margin: 5px 0;
            font-size: 14px;
            opacity: 0.8;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-links a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
            transition: opacity 0.3s ease;
        }

        .social-links a:hover {
            opacity: 0.7;
        }

        .highlight {
            color: #7c4435;
            font-weight: 600;
        }

        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .content {
                padding: 20px 15px !important;
            }

            .header {
                padding: 20px 15px !important;
            }

            .header h1 {
                font-size: 24px !important;
            }

            .info-table td {
                display: block;
                width: 100% !important;
                text-align: left !important;
            }

            .info-table td:last-child {
                text-align: left !important;
                margin-bottom: 10px;
            }

            .items-table {
                font-size: 14px !important;
            }

            .items-table th,
            .items-table td {
                padding: 8px !important;
            }
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>ORDER CONFIRMATION</h1>
            <p>Thank you for your purchase from {{ config('app.name') }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Thank You Message -->
            <div
                style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
                <h2 style="color: #155724; margin: 0 0 10px 0; font-size: 20px;">✓ Order Received Successfully</h2>
                <p style="color: #155724; margin: 0; font-size: 16px;">
                    We've received your order and are preparing it for shipment. You'll receive a tracking notification
                    once your order has been dispatched.
                </p>
            </div>

            <!-- Order Details -->
            <div class="section">
                <div class="section-header">
                    <h2>Order Information</h2>
                </div>
                <div class="section-content">
                    <table class="info-table">
                        <tr>
                            <td>Order Number:</td>
                            <td class="highlight">#{{ $order->id }}</td>
                        </tr>
                        <tr>
                            <td>Order Date:</td>
                            <td>{{ $order->created_at->format('F j, Y g:i A') }}</td>
                        </tr>
                        <tr>
                            <td>Payment Method:</td>
                            <td>
                                @if ($order->payment_method === 'cod')
                                    Cash on Delivery
                                @else
                                    Paystack (Paid)
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Order Status:</td>
                            <td><span class="status-badge">{{ ucfirst($order->status) }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Shipping Address -->
            @if ($order->address)
                <div class="section">
                    <div class="section-header">
                        <h2>Shipping Address</h2>
                    </div>
                    <div class="section-content">
                        <p style="margin: 0 0 5px 0; font-weight: 600; color: #212529;">{{ $order->address->full_name }}
                        </p>
                        <p style="margin: 0 0 5px 0; color: #6c757d;">{{ $order->address->street_address }}</p>
                        <p style="margin: 0 0 5px 0; color: #6c757d;">
                            {{ $order->address->city }}, {{ $order->address->state }} {{ $order->address->zip_code }}
                        </p>
                        <p style="margin: 0; color: #6c757d;">{{ $order->address->country }}</p>
                        @if ($order->address->phone)
                            <p style="margin: 10px 0 0 0; color: #6c757d;">
                                <span style="color: #7c4435;">📞</span> {{ $order->address->phone }}
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Order Items -->
            @if ($order->items && $order->items->count() > 0)
                <div class="section">
                    <div class="section-header">
                        <h2>Order Items</h2>
                    </div>
                    <div class="section-content">
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th style="text-align: center;">Quantity</th>
                                    <th style="text-align: right;">Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            <div class="product-name">{{ $item->name }}</div>
                                            @if ($item->color || $item->size)
                                                <div class="product-variant">
                                                    @if ($item->color)
                                                        Color: {{ $item->color }}
                                                    @endif
                                                    @if ($item->size)
                                                        Size: {{ $item->size }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td style="text-align: center; color: #6c757d;">{{ $item->quantity }}</td>
                                        <td style="text-align: right; font-weight: 600; color: #212529;">
                                            {{ app(\App\Services\CurrencyService::class)->formatForDisplay($item->unit_amount * $item->quantity) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Order Summary -->
            <div class="section">
                <div class="section-header">
                    <h2>Order Summary</h2>
                </div>
                <div class="section-content">
                    <table class="info-table">
                        <tr>
                            <td>Subtotal:</td>
                            <td>{{ app(\App\Services\CurrencyService::class)->formatForDisplay($order->grand_total - $order->shipping_amount) }}
                            </td>
                        </tr>
                        <tr>
                            <td>Shipping:</td>
                            <td>{{ app(\App\Services\CurrencyService::class)->formatForDisplay($order->shipping_amount) }}
                            </td>
                        </tr>
                        <tr class="total-row">
                            <td style="font-size: 18px;">Total:</td>
                            <td class="total-amount">
                                {{ app(\App\Services\CurrencyService::class)->formatForDisplay($order->grand_total) }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="button-container">
                <a href="{{ $url }}" class="cta-button">Track Your Order</a>
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>What's Next?</h3>
                <ul>
                    <li>We'll send you a tracking notification once your order ships</li>
                    <li>Delivery typically takes 3-7 business days</li>
                    <li>Questions? Contact us at info@maadan.com or +234 807 795 5804</li>
                </ul>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <h3>Thank you for choosing {{ config('app.name') }}!</h3>
            <p>Lekki Phase 1, Lagos, Nigeria</p>
            <p>info@maadan.com | +234 807 795 5804</p>
            <div class="social-links">
                <a href="https://www.facebook.com/maadanofficial">Facebook</a>
                <a href="https://twitter.com/maadanofficial">Twitter</a>
                <a href="https://instagram.com/maadanofficial">Instagram</a>
            </div>
        </div>
    </div>
</body>

</html>
