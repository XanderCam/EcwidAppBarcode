<!DOCTYPE html>
<html>
<head>
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://d35z3p2poghz10.cloudfront.net/apps/ecwid-sdk/sdk.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6"><?php echo APP_NAME; ?></h1>
            
            <div id="loading" class="hidden">
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                    <span class="ml-2">Processing...</span>
                </div>
            </div>
            
            <div id="products-list" class="mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Products</h2>
                    <button id="generate-all" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Generate All Barcodes
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2">Product</th>
                                <th class="px-4 py-2">SKU/Barcode</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="products-table">
                            <!-- Products will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            </div>
            
            <div id="success-message" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            </div>
        </div>
    </div>

    <script>
        EcwidApp.init({
            app_id: "<?php echo CLIENT_ID; ?>",
            autoloadedflag: true,
            autoheight: true
        });

        const app = {
            init: function() {
                this.bindEvents();
                this.loadProducts();
            },

            bindEvents: function() {
                $('#generate-all').on('click', () => this.generateAllBarcodes());
                $(document).on('click', '.generate-single', (e) => {
                    const productId = $(e.target).data('product-id');
                    this.generateBarcode(productId);
                });
            },

            loadProducts: function() {
                $('#loading').show();
                $.get('?action=products')
                    .done((response) => {
                        if (response.success) {
                            this.renderProducts(response.products);
                        } else {
                            this.showError(response.error || 'Failed to load products');
                        }
                    })
                    .fail(() => this.showError('Failed to load products'))
                    .always(() => $('#loading').hide());
            },

            renderProducts: function(products) {
                const rows = products.map(product => `
                    <tr>
                        <td class="border px-4 py-2">${product.name}</td>
                        <td class="border px-4 py-2">${product.sku || '-'}</td>
                        <td class="border px-4 py-2">
                            <button 
                                class="generate-single bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded"
                                data-product-id="${product.id}">
                                Generate
                            </button>
                        </td>
                    </tr>
                `).join('');
                
                $('#products-table').html(rows);
            },

            generateAllBarcodes: function() {
                $('#loading').show();
                $.post('?action=generate-all')
                    .done((response) => {
                        if (response.success) {
                            this.showSuccess('Barcodes generated successfully');
                            this.loadProducts();
                        } else {
                            this.showError(response.error || 'Failed to generate barcodes');
                        }
                    })
                    .fail(() => this.showError('Failed to generate barcodes'))
                    .always(() => $('#loading').hide());
            },

            generateBarcode: function(productId) {
                $('#loading').show();
                $.post('?action=generate', { product_id: productId })
                    .done((response) => {
                        if (response.success) {
                            this.showSuccess('Barcode generated successfully');
                            this.loadProducts();
                        } else {
                            this.showError(response.error || 'Failed to generate barcode');
                        }
                    })
                    .fail(() => this.showError('Failed to generate barcode'))
                    .always(() => $('#loading').hide());
            },

            showError: function(message) {
                $('#error-message').text(message).removeClass('hidden');
                setTimeout(() => $('#error-message').addClass('hidden'), 3000);
            },

            showSuccess: function(message) {
                $('#success-message').text(message).removeClass('hidden');
                setTimeout(() => $('#success-message').addClass('hidden'), 3000);
            }
        };

        $(document).ready(() => app.init());
    </script>
</body>
</html>
