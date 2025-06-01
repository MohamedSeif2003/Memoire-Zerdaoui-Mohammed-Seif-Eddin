const urlParams = new URLSearchParams(window.location.search);
const username = urlParams.get('username');
console.log("Username from URL:", username);

if (username) {
    const adminUsernameElement = document.querySelector('.info b');
    if (adminUsernameElement) {
        adminUsernameElement.textContent = username; // Update the <b> tag with the username
    }
}

const sideMenu = document.querySelector("aside");
const menuBtn = document.getElementById('menu-btn');
const closeBtn = document.getElementById('close-btn');
const addBtn = document.getElementById('addBtn');
const modalOverlay = document.querySelector('.modal-overlay');
const closeModal = document.getElementById('closeModal');
const saveProduct = document.getElementById('saveProduct');
const imag = document.getElementById('imag');
const desig = document.getElementById('desig');
const stock = document.getElementById('stock');
const sell = document.getElementById('sell');
const buy = document.getElementById('buy');
const exp = document.getElementById('exp');
const types = document.getElementById('types');

const moodalOverlay = document.querySelector('.moodal-overlay');
const closeMoodal = document.getElementById('closeMoodal');
const saveEditProduct = document.getElementById('saveEditProduct');
const imagEd = document.getElementById('imagEd');
const desigEd = document.getElementById('desigEd');
const stockEd = document.getElementById('stockEd');
const sellEd = document.getElementById('sellEd');
const buyEd = document.getElementById('buyEd');
const expEd = document.getElementById('expEd');
const typeEd = document.getElementById('typeEd');

let dataArray = []; 
let currentEditId = null;
menuBtn.addEventListener('click', function () {
    sideMenu.style.display = 'block';
});

closeBtn.addEventListener('click', function () {
    sideMenu.style.display = 'none';
});

addBtn.onclick = function () {
    modalOverlay.style.display = 'flex';
};

closeModal.onclick = function () {
    modalOverlay.style.display = 'none';
};

imag.addEventListener('change', () => {
    const file = imag.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imag.dataset.image = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

saveProduct.addEventListener("click", function (event) {
    event.preventDefault();

    const formData = new FormData();
    formData.append('imag', imag.files[0]);
    formData.append('desig', desig.value);
    formData.append('stock', stock.value);
    formData.append('sell', sell.value);
    formData.append('buy', buy.value);
    formData.append('exp', exp.value);
    formData.append('types', types.value);

    fetch('admin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product added successfully!');
            dataArray = data.products; 
            showData(data.products); 
            clearInputs();
            modalOverlay.style.display = 'none';
        } else {
            alert('Failed to add product: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

function showData(products) {
    let body = '';
    products.forEach((product) => {
        body += `
        <tr>
            <td><img src="${product.imag}" alt=""></td>
            <td>${product.desig}</td>
            <td>${product.stock}</td>
            <td>${product.buy} DA</td>
            <td>${product.sell} DA</td>
            <td>${product.exp}</td>
            <td>${product.types}</td>
            <td id="editBtn" onclick="editProduct('${product.product_id}')">Edit</td>
            <td id="deleteBtn" onclick="deleteProduct('${product.product_id}')">Delete</td>
        </tr>`;
    });
    document.getElementById('tbody').innerHTML = body;
}

fetch('admin.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            dataArray = data.products; 
            showData(data.products);
        } else {
            alert('Failed to fetch products!');
        }
    })
    .catch(error => console.error('Error:', error));


function clearInputs() {
    imag.value = '';
    imag.dataset.image = '';
    desig.value = '';
    stock.value = '';
    buy.value = '';
    sell.value = '';
    exp.value = '';
    types.value = '';
}


function deleteProduct(product_id) {
    if (confirm('Are you sure you want to delete this product?')) {
        fetch(`admin.php?action=delete&product_id=${product_id}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product deleted successfully!');
                dataArray = dataArray.filter(product => product.product_id !== product_id); 
                showData(dataArray); 
            } else {
                alert('Failed to delete product: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }
}


function editProduct(product_id) {
    const product = dataArray.find(product => product.product_id === product_id); 
    if (!product) {
        alert('Product not found!');
        return;
    }

    
    desigEd.value = product.desig;
    stockEd.value = product.stock;
    sellEd.value = product.sell;
    buyEd.value = product.buy;
    expEd.value = product.exp;
    typeEd.value = product.types;

    const imagPreview = document.getElementById('imagPreview');
    imagPreview.src = product.imag; 

    currentEditId = product_id; 

    moodalOverlay.style.display = 'flex'; 
}

saveEditProduct.onclick = function () {
    if (!desigEd.value || !stockEd.value || !sellEd.value || !buyEd.value || !expEd.value || !typeEd.value) {
        alert('Please fill all fields!');
        return;
    }

    const formData = new FormData();
    formData.append('product_id', currentEditId); 
    formData.append('desig', desigEd.value);
    formData.append('stock', stockEd.value);
    formData.append('sell', sellEd.value);
    formData.append('buy', buyEd.value);
    formData.append('exp', expEd.value);
    formData.append('types', typeEd.value);

    if (imagEd.files[0]) {
        formData.append('imag', imagEd.files[0]); 
    }

    // Send the updated data to the server
    fetch('admin.php?action=edit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Product updated successfully!');
            dataArray = data.products; 
            showData(data.products); 
            moodalOverlay.style.display = 'none'; 
        } else {
            alert('Failed to update product: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
};


closeMoodal.onclick = function () {
    moodalOverlay.style.display = 'none';
};

const searchInput = document.querySelector('.search-input');
searchInput.addEventListener('input', function () {
    const searchText = searchInput.value.toLowerCase();
    const filteredProducts = dataArray.filter(product =>
        product.desig.toLowerCase().includes(searchText)
    );
    showData(filteredProducts);
});
