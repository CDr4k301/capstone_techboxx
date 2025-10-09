// Function to calculate total based on checked items
function updateTotal() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    let total = 0;
    checkboxes.forEach(cb => {
        if (cb.checked) {
            total += parseFloat(cb.dataset.price);
        }
    });
    document.getElementById('totalPrice').textContent = total.toLocaleString('en-PH', {minimumFractionDigits: 2});
}

// Function to update checked items hidden field
function updateCheckedItems() {
    const checkedItems = [];
    const checkboxes = document.querySelectorAll('.item-checkbox.bundle-item:checked');
    
    checkboxes.forEach(cb => {
        checkedItems.push({
            id: cb.dataset.id,
            type: cb.dataset.type,
            table: cb.dataset.table,
            price: cb.dataset.price,
            name: cb.dataset.name
        });
    });
    
    // Add main product (always included)
    const mainProduct = document.querySelector('.item-checkbox.main-product');
    checkedItems.unshift({
        id: mainProduct.dataset.id,
        type: mainProduct.dataset.type,
        table: mainProduct.dataset.table,
        price: mainProduct.dataset.price,
        name: mainProduct.dataset.name
    });
    
    document.getElementById('checkedItems').value = JSON.stringify(checkedItems);
}

// Initial calculation and setup
updateTotal();
updateCheckedItems();

// Update total and checked items whenever a checkbox changes
const checkboxes = document.querySelectorAll('.item-checkbox');
checkboxes.forEach(cb => {
    cb.addEventListener('change', function() {
        updateTotal();
        updateCheckedItems();
    });
});

// Update form submission to use checked items
document.getElementById('bundleForm').addEventListener('submit', function(e) {
    const checkedCheckboxes = document.querySelectorAll('.item-checkbox.bundle-item:checked');
    if (checkedCheckboxes.length === 0) {
        if (!confirm('No additional items selected. Only the main product will be added to cart. Continue?')) {
            e.preventDefault();
        }
    }
});