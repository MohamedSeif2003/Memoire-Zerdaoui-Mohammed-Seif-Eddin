document.addEventListener('DOMContentLoaded', function () {
    // DOM Elements
    const quantityInput = document.getElementById('quantity');
    const increaseBtn = document.querySelector('.increase');
    const decreaseBtn = document.querySelector('.decrease');
    const priceElement = document.getElementById('price');
    const orderButton = document.getElementById('dedouna');
    const productImage = document.getElementById('product-image');
    const designationElement = document.getElementById('designation');
    const typeElement = document.getElementById('type');
    const stockElement = document.getElementById('stock-info');
    const breadcrumbElement = document.getElementById('Dd');

    // Product data with all required fields
    let productData = {
        id: null,
        price: 0,
        stock: 0,
        type: '',
        designation: '',
        image: '',
        description: ''
    };

    // Initialize the page
    initPage();

    function initPage() {
        setupEventListeners();
        loadProductFromURL();
        updateTotalPrice();
    }

    function setupEventListeners() {
        // Quantity controls
        increaseBtn.addEventListener('click', increaseQuantity);
        decreaseBtn.addEventListener('click', decreaseQuantity);
        quantityInput.addEventListener('input', validateQuantity);
        quantityInput.addEventListener('change', updateTotalPrice);

        // Order button
        orderButton.addEventListener('click', processOrder);

        // Navigation
        document.getElementById('Mm').addEventListener('click', () => {
            window.location.href = 'market.html';
        });
        document.getElementById('Hh').addEventListener('click', () => {
            window.location.href = 'index.html';
        });
    }

    function loadProductFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Load all required product data from URL parameters
        productData = {
            id: urlParams.get('product_id') || 'default_id',
            price: parseFloat(urlParams.get('price')) || 0,
            stock: parseInt(urlParams.get('stock')) || 0,
            type: urlParams.get('type') || 'Not specified',
            designation: decodeURIComponent(urlParams.get('designation') || 'Unknown Product'),
            image: urlParams.get('imagePath') || 'Medications/default-image.png',
            description: decodeURIComponent(urlParams.get('description') || 'No description available')
        };

        updateProductDisplay();
    }

    function updateProductDisplay() {
        productImage.src = productData.image;
        productImage.alt = productData.designation;
        designationElement.textContent = productData.designation;
        typeElement.textContent = productData.type;
        breadcrumbElement.textContent = productData.designation;
        priceElement.textContent = `${productData.price.toFixed(2)} DA`;
        updateStockStatus(parseInt(quantityInput.value), productData.stock);
    }

    // Quantity functions
    function increaseQuantity() {
        let quantity = parseInt(quantityInput.value) || 1;
        quantityInput.value = quantity + 1;
        updateTotalPrice();
    }

    function decreaseQuantity() {
        let quantity = parseInt(quantityInput.value) || 1;
        if (quantity > 1) {
            quantityInput.value = quantity - 1;
            updateTotalPrice();
        }
    }

    function validateQuantity() {
        let quantity = parseInt(quantityInput.value) || 1;
        if (quantity < 1) quantityInput.value = 1;
        if (quantity > 999) quantityInput.value = 999; // Added maximum limit
    }

    function updateTotalPrice() {
        const quantity = parseInt(quantityInput.value) || 1;
        const totalPrice = quantity * productData.price;
        priceElement.textContent = `${totalPrice.toFixed(2)} DA`;
        updateStockStatus(quantity, productData.stock);
    }

    function updateStockStatus(quantity, stock) {
        if (quantity > stock) {
            stockElement.textContent = 'Out of Stock';
            stockElement.style.color = '#ff0000';
            orderButton.disabled = true;
            orderButton.classList.add('disabled');
        } else {
            stockElement.textContent = `In Stock`;
            stockElement.style.color = '#1CAE72';
            orderButton.disabled = false;
            orderButton.classList.remove('disabled');
        }
    }

    function processOrder() {
        const quantity = parseInt(quantityInput.value) || 1;
        
        if (quantity > productData.stock) {
            alert('Cannot order more than available stock');
            return;
        }

        // Prepare ALL required data for delivery.php
        const orderData = {
            productId: productData.id,
            productName: productData.designation,
            quantity: quantity,
            unitPrice: productData.price,
            totalPrice: (quantity * productData.price).toFixed(2),
            productType: productData.type,
            image: productData.image,
            description: productData.description
        };

        redirectToDelivery(orderData);
    }

    function redirectToDelivery(orderData) {
        // Encode all parameters for URL
        const params = new URLSearchParams();
        params.append('product_id', orderData.productId);
        params.append('product_name', encodeURIComponent(orderData.productName));
        params.append('quantity', orderData.quantity);
        params.append('unit_price', orderData.unitPrice);
        params.append('total_price', orderData.totalPrice);
        params.append('product_type', encodeURIComponent(orderData.productType));
        params.append('image_path', orderData.image);
        params.append('description', encodeURIComponent(orderData.description));
        
        window.location.href = `delivery.php?${params.toString()}`;
    }
});