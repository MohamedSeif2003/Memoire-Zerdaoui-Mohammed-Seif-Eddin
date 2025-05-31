document.addEventListener('DOMContentLoaded', function () {
    console.log('Market page loaded.');
    fetchProducts();

    // Function to fetch products from the server
    function fetchProducts(sortParam = '') {
        console.log('Fetching products...');
        let url = 'admin.php';
        if (sortParam) {
            url += `?sort=${sortParam}`;
        }

        fetch(url)
            .then(response => {
                console.log('Response received from server:', response);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('Data received from server:', data);
                if (data.success) {
                    const products = data.products;
                    const cardsContainer = document.getElementById('cards-container');
                    cardsContainer.innerHTML = '';

                    // Generate cards for each product
                    products.forEach(product => {
                        console.log('Generating card for product:', product);
                        const card = document.createElement('div');
                        card.className = 'card';
                        card.innerHTML = `
                            <img src="${product.imag}" alt="${product.desig}">
                            <div class="txt">
                                <p class="designation">${product.desig}</p>
                                <p class="details">Lorem ipsum dolor sit amet consectetur adipisicing.</p>
                                <p class="cont">Type</p>
                                <p class="type" style="border: 0.1px solid #333; display: inline-block; border-radius: 5px; font-size: 14px; font-weight: 0; color: #333; margin: 5px 0; padding: 0 5px;">
                                    ${product.types || 'Not specified'}
                                </p>
                            </div>
                            <p class="price" style="font-weight: 600; margin-right: 3px;">${product.sell} DA</p>
                        `;
                        
                        // Add click handler with all product data
                        card.addEventListener('click', () => {
                            redirectToOrderPage(
                                product.product_id,
                                product.desig,
                                product.sell,
                                product.types,
                                product.imag,
                                product.stock
                            );
                        });
                        
                        cardsContainer.appendChild(card);
                    });

                    // Update product count
                    document.getElementById('nbrProd').textContent = `${products.length} Medications`;
                    addEventListeners();
                } else {
                    console.error('Failed to fetch products:', data.message);
                    alert('Failed to load products. Please try again later.');
                }
            })
            .catch(error => {
                console.error('Error fetching products:', error);
                alert('Error loading products. Please check your connection.');
            });
    }

    // Enhanced redirect function with all product data
    function redirectToOrderPage(product_id, designation, price, type, imagePath, stock) {
        console.log(`Redirecting to order page with product: ${designation}`);
        const params = new URLSearchParams();
        params.append('product_id', product_id);
        params.append('designation', designation);
        params.append('price', price);
        params.append('type', type || 'Not specified');
        params.append('imagePath', imagePath);
        params.append('stock', stock);
        
        window.location.href = `order.html?${params.toString()}`;
    }

    // Function to add event listeners
    function addEventListeners() {
        console.log('Adding event listeners...');

        // Dropdown functionality
        const dropBtns = document.querySelectorAll('.dropbtn');
        const ddContents = document.querySelectorAll('.dropdown-content');

        dropBtns.forEach((dropBtn, index) => {
            dropBtn.addEventListener('click', function (event) {
                event.stopPropagation();
                ddContents[index].style.display = ddContents[index].style.display === 'block' ? 'none' : 'block';
            });
        });

        document.addEventListener('click', function (event) {
            ddContents.forEach(ddContent => {
                if (!ddContent.contains(event.target)) {
                    ddContent.style.display = 'none';
                }
            });
        });

        // Sorting functionality
        const sortOptions = document.querySelectorAll('#sort a');
        sortOptions.forEach(option => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                const sortBy = this.textContent.trim();
                let sortParam = '';
                
                if (sortBy === 'Ascending Price') sortParam = 'price_asc';
                else if (sortBy === 'Descending Price') sortParam = 'price_desc';
                else if (sortBy === 'Ascending Alphabetical') sortParam = 'alphabetical_asc';
                else if (sortBy === 'Descending Alphabetical') sortParam = 'alphabetical_desc';

                fetchProducts(sortParam);
            });
        });

        // Filtering functionality
        const filterOptions = document.querySelectorAll('.dropdown-content:not(#sort) a');
        filterOptions.forEach(option => {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                const filterBy = this.textContent.trim().toLowerCase();
                filterCards(filterBy);
            });
        });

        // Search functionality
        const searchInput = document.querySelector('.search-input');
        searchInput.addEventListener('input', function () {
            const searchText = this.value.trim().toLowerCase();
            filterCardsBySearch(searchText);
        });
    }

    // Filter functions
    function filterCards(filterBy) {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            const type = card.querySelector('.type').textContent.trim().toLowerCase();
            card.style.display = type === filterBy ? 'flex' : 'none';
        });
    }

    function filterCardsBySearch(searchText) {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            const designation = card.querySelector('.designation').textContent.toLowerCase();
            card.style.display = designation.includes(searchText) ? 'flex' : 'none';
        });
    }

    // Make redirect function available globally
    window.redirectToOrderPage = redirectToOrderPage;
});