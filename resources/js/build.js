const customBuildBtn = document.getElementById('customBuildBtn');
const generateBuildBtn = document.getElementById('generateBuildBtn');
const amdBtn = document.getElementById('amdBtn');
const intelBtn = document.getElementById('intelBtn');
const budgetSection = document.querySelector('.budget-section');
const generateButton = document.querySelector('.generate-button');
const generalUseBtn = document.getElementById('generalUseBtn');
const gamingBtn = document.getElementById('gamingBtn');
const graphicsIntensiveBtn = document.getElementById('graphicsIntensiveBtn');
const budget = document.getElementById('budget');
const generateBtn = document.getElementById('generateBtn');
const loadingSpinner = document.getElementById('loadingSpinner');
const buildSectionButtons = document.querySelectorAll('#buildSection button');
const catalogList = document.querySelector('.catalog-list');
const catalogItem = document.querySelectorAll('.catalog-item');
const buildSection = document.getElementById('buildSection');
const remarksTab = document.getElementById('remarksTab');
const remarksSection = document.getElementById('remarksSection');
const componentsSection = document.getElementById('componentsSection');
const summarySection = document.getElementById('summarySection');
const componentsTab = document.getElementById('componentsTab');
const summaryTab = document.getElementById('summaryTab');
const cartForm = document.getElementById("cartForm");

window.selectedComponents = {};
window.selectPayment = selectPayment;

let currentBrandFilter = '';     // e.g. "amd" or "intel"
let currentCategoryFilter = '';  // e.g. "gaming"
let currentTypeFilter = '';      // e.g. "cpu"
let currentBudget = null;

function applyAllFilters() {
    catalogItem.forEach(item => {
        const itemType = item.getAttribute('data-type');
        const itemName = item.getAttribute('data-name').toLowerCase();
        const itemCategory = item.getAttribute('data-category').toLowerCase();
        const itemPrice = parseFloat(item.getAttribute('data-price'));

        let show = true;

        // Type filter (e.g. cpu, gpu, etc.)
        if (currentTypeFilter && itemType !== currentTypeFilter) {
            show = false;
        }

        // Brand filter (e.g. amd, intel, but only applies to CPU)
        if (currentBrandFilter) {
            const brand = currentBrandFilter.toLowerCase();
            if (itemType === 'cpu' && !itemName.includes(brand)) {
                show = false;
            }
        }

        // Category filter (e.g. gaming, general use, etc.)
        if (currentCategoryFilter) {
            const category = currentCategoryFilter.toLowerCase();
            if (itemCategory !== category) {
                show = false;
            }
        }

        // Budget filter
        if (currentBudget !== null && itemPrice > currentBudget) {
            show = false;
        }

        item.classList.toggle('hidden', !show);
    });
}

function displayBuildRemarks(budgetSummary, userBudget, totalPrice, category, cpuBrand) {
    const remarksContainer = document.getElementById('buildRemarks');
    let remaining = null;
    
    if (!remarksContainer) {
        console.error('Remarks container not found');
        return;
    }
    
    // Hide remarks if no budget was set
    if (!userBudget && !budgetSummary) {
        remarksContainer.style.display = 'none';
        return;
    }
    
    // Show remarks container
    remarksContainer.style.display = 'block';
    
    let remarksHTML = '<div class="remarks-content">';
    remarksHTML += '<h4 class="remarks-title">📊 Build Analysis & Recommendations</h4>';
    
    // Budget Analysis Section
    if (userBudget) {
        const remaining = userBudget - totalPrice;
        const percentUsed = (totalPrice / userBudget * 100).toFixed(1);
        
        remarksHTML += '<div class="remark-item">';
        remarksHTML += '<p class="remark-label">💰 Budget Status</p>';
        
        if (remaining >= 0) {
            const savingsPercent = (remaining / userBudget * 100).toFixed(1);
            remarksHTML += `<p class="remark-value success">
                ✓ Within Budget (${percentUsed}% utilized)<br>
                <strong>Remaining: ₱${remaining.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong> (${savingsPercent}% saved)
            </p>`;
        } else {
            const overPercent = (Math.abs(remaining) / userBudget * 100).toFixed(1);
            remarksHTML += `<p class="remark-value warning">
                ⚠ Over Budget by <strong>₱${Math.abs(remaining).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong> (${overPercent}% over)
            </p>`;
        }
        remarksHTML += '</div>';
    }
    
    // Build Configuration Section
    remarksHTML += '<div class="remark-item">';
    remarksHTML += '<p class="remark-label">⚙️ Configuration Details</p>';
    remarksHTML += `<p class="remark-value">
        <strong>Build Type:</strong> ${category ? category.charAt(0).toUpperCase() + category.slice(1) : 'General Purpose'}<br>
        <strong>Processor Brand:</strong> ${cpuBrand ? cpuBrand.toUpperCase() : 'Any'}<br>
        <strong>Total Components:</strong> 8 items<br>
        <strong>Total Cost:</strong> ₱${totalPrice.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}
    </p>`;
    remarksHTML += '</div>';
    
    // Performance Tier Section
    const tierInfo = getPerformanceTier(totalPrice);
    remarksHTML += '<div class="remark-item">';
    remarksHTML += '<p class="remark-label">🎯 Performance Tier</p>';
    remarksHTML += `<p class="remark-value">
        <strong>${tierInfo.tier}</strong> (₱${tierInfo.range})<br>
        ${tierInfo.description}
    </p>`;
    remarksHTML += '</div>';
    
    // Recommendations Section
    const recommendations = getRecommendations(category, userBudget, totalPrice, remaining);
    remarksHTML += '<div class="remark-item recommendations">';
    remarksHTML += '<p class="remark-label">💡 Smart Recommendations</p>';
    remarksHTML += '<ul class="remark-list">';
    
    recommendations.forEach(rec => {
        remarksHTML += `<li>${rec}</li>`;
    });
    
    remarksHTML += '</ul>';
    remarksHTML += '</div>';
    
    remarksHTML += '</div>';
    
    remarksContainer.innerHTML = remarksHTML;
}

function getPerformanceTier(totalPrice) {
    if (totalPrice > 100000) {
        return {
            tier: '🚀 Enthusiast/Extreme',
            range: '100,000+',
            description: 'Top-tier performance for demanding workloads and high-end gaming at maximum settings.'
        };
    } else if (totalPrice > 80000) {
        return {
            tier: '💎 High-End',
            range: '80,000 - 100,000',
            description: 'Excellent performance for professional work, content creation, and high-FPS gaming.'
        };
    } else if (totalPrice > 50000) {
        return {
            tier: '⭐ Mid-High Range',
            range: '50,000 - 80,000',
            description: 'Great balance of performance and value for gaming and productivity tasks.'
        };
    } else if (totalPrice > 30000) {
        return {
            tier: '✨ Mid-Range',
            range: '30,000 - 50,000',
            description: 'Solid performance for 1080p gaming and general computing needs.'
        };
    } else {
        return {
            tier: '📌 Budget-Friendly',
            range: 'Under 30,000',
            description: 'Entry-level build suitable for basic tasks and light gaming.'
        };
    }
}

function getRecommendations(category, userBudget, totalPrice, remaining) {
    const recommendations = [];
    
    // Category-specific recommendations
    if (category === 'gaming') {
        recommendations.push('Optimized for gaming performance with balanced GPU and CPU selection');
        recommendations.push('Consider pairing with a 144Hz+ monitor for the best experience');
        recommendations.push('Ensure adequate airflow for sustained gaming sessions');
    } else if (category === 'graphics intensive') {
        recommendations.push('Configured for content creation, 3D rendering, and video editing');
        recommendations.push('High-performance GPU selected for GPU-accelerated workloads');
        recommendations.push('Consider additional cooling for extended rendering tasks');
    } else {
        recommendations.push('Balanced build suitable for everyday computing tasks');
        recommendations.push('Can handle office work, web browsing, and light multitasking');
        if (totalPrice < 40000) {
            recommendations.push('Upgrade GPU for improved gaming capabilities');
        }
    }
    
    // Budget-based recommendations
    if (userBudget && remaining !== undefined) {
        if (remaining > 10000) {
            recommendations.push(`You have ₱${remaining.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')} remaining - consider upgrading storage, adding RGB peripherals, or a better monitor`);
        } else if (remaining > 5000) {
            recommendations.push(`Consider using the remaining ₱${remaining.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')} for a quality keyboard/mouse combo or additional storage`);
        } else if (remaining > 0) {
            recommendations.push('Budget well-optimized! Consider saving for future upgrades or peripherals');
        } else if (remaining < -5000) {
            recommendations.push('Consider selecting more budget-friendly components to stay within budget');
        }
    }
    
    // General recommendations
    recommendations.push('Verify all components are compatible before purchasing');
    
    return recommendations;
}

// Simple toggle function for storage
function toggleStorage(selectedType) {
    const otherType = selectedType === 'ssd' ? 'hdd' : 'ssd';
    
    // If the other type was previously selected, clear it
    if (window.selectedComponents[otherType]) {
        // Clear the other type from UI
        const otherButton = document.querySelector(`#buildSection button[data-type="${otherType}"]`);
        if (otherButton) {
            const span = otherButton.querySelector('.selected-name');
            if (span) span.textContent = 'None';
            otherButton.removeAttribute('data-selected-id');
        }
        
        // Clear from selectedComponents
        delete window.selectedComponents[otherType];
        
        // Clear hidden input
        const otherHiddenInput = document.getElementById(`hidden_${otherType}`);
        if (otherHiddenInput) otherHiddenInput.value = '';
        
        // Reset draggable
        const otherDraggable = document.getElementById(otherType);
        if (otherDraggable) {
            otherDraggable.innerHTML = `<p>${otherType.toUpperCase()}</p>`;
        }
    }
}

//Components & Summary active tab
document.querySelectorAll('.catalog-button button').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelector('.catalog-button .active')?.classList.remove('active');
    btn.classList.add('active');
  });
});

// FILTER CATEGORY 
// function filterByBuildCategory(category) {
//     catalogItem.forEach(item => {
//         const build = item.getAttribute('data-category').toLowerCase();

//         if (build === category.toLowerCase()) {
//             item.classList.remove('hidden');
//         }
//         else {
//             item.classList.add('hidden');
//         }
//     })
// }

//Components & Summary active tab
document.querySelectorAll('.catalog-button button').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelector('.catalog-button .active')?.classList.remove('active');
    btn.classList.add('active');
  });
});

customBuildBtn.addEventListener('click', function() {
    const remarksContainer = document.getElementById('buildRemarks');
    
    currentBudget = null;

    if (remarksContainer) {
        remarksContainer.style.display = 'none';
    }

    generateBuildBtn.classList.remove('active');
    buildSection.classList.remove('hidden');

    customBuildBtn.classList.add('active');
    budgetSection.classList.add('hidden');
    generateButton.classList.add('hidden');
});

generateBuildBtn.addEventListener('click', function() {
    generateBuildBtn.classList.add('active');
    buildSection.classList.add('hidden');

    customBuildBtn.classList.remove('active');
    budgetSection.classList.remove('hidden');
    generateButton.classList.remove('hidden');
});

amdBtn.addEventListener('click', function() {
    currentBrandFilter = 'amd';
    amdBtn.classList.add('active');

    intelBtn.classList.remove('active');

    applyAllFilters();
});

intelBtn.addEventListener('click', function() {
    currentBrandFilter = 'intel';

    intelBtn.classList.add('active');

    amdBtn.classList.remove('active');
    
    applyAllFilters();
});

generalUseBtn.addEventListener('click', function() {
    currentCategoryFilter = 'general use';

    generalUseBtn.classList.add('active');

    gamingBtn.classList.remove('active');
    graphicsIntensiveBtn.classList.remove('active');

    applyAllFilters();
});

gamingBtn.addEventListener('click', function() {
    currentCategoryFilter = 'gaming';

    gamingBtn.classList.add('active');

    generalUseBtn.classList.remove('active');
    graphicsIntensiveBtn.classList.remove('active');
    
    applyAllFilters();
});

graphicsIntensiveBtn.addEventListener('click', function() {
    currentCategoryFilter = 'graphics intensive';

    graphicsIntensiveBtn.classList.add('active');

    gamingBtn.classList.remove('active');
    generalUseBtn.classList.remove('active');

    applyAllFilters();
});

// POPULATE CHIPSET BUTTON
document.addEventListener('DOMContentLoaded', function() {
    const amdBtn = document.getElementById('amdBtn');
    const intelBtn = document.getElementById('intelBtn');
    const chipsetName = document.getElementById('chipsetName');
    
    if (amdBtn) {
        amdBtn.addEventListener('click', function() {
            chipsetName.textContent = 'AMD';
        });
    }
    
    if (intelBtn) {
        intelBtn.addEventListener('click', function() {
            chipsetName.textContent = 'Intel';
        });
    }
});

generateBtn.addEventListener('click', () => {
    const value = parseFloat(budget.value);

    if (!isNaN(value)) {
        currentBudget = value;
    } else {
        currentBudget = null;
    }

    applyAllFilters();
    
    budgetSection.classList.add('hidden');
    generateButton.classList.add('hidden');
    buildSection.classList.remove('hidden');
    loadingSpinner.classList.remove('hidden');

    const formattedBudget = currentBudget?.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' }) || "any";
    const category = currentCategoryFilter || "any category";
    const brand = currentBrandFilter || "any";

    loadingText.textContent = `Getting recommendations for ${category} with ${brand} CPU within ${formattedBudget} budget\nUser Budget: ${formattedBudget}`;

    // DATA ANALYTICS
    fetch("/techboxx/build/generate-build", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            category : currentCategoryFilter,
            cpuBrand: currentBrandFilter,
            userBudget: currentBudget
        })
    })
    .then(res => res.json())
    .then(data => { 
        loadingSpinner.classList.add('hidden');

        console.log(data);
        summaryTableBody.innerHTML = '';

        let totalPrice = 0;

        // Filter out the budget_summary and only process components
        const components = Object.entries(data).filter(([key, item]) => {
            return key !== 'budget_summary' && item && item.price !== undefined;
        });

        components.forEach(([key, item]) => {
            const price = parseFloat(item.price.toString().replace(/,/g, ''));
            if (isNaN(price)) {
                console.warn(`Invalid price for item:`, item);
                return;
            }

            totalPrice += price;

            let row = '';
            row += `<tr>`;
            row += `<td><p>${item.name}</p></td>`;
            row += `<td><p>1</p></td>`;
            row += `<td><p>₱${price.toFixed(2)}</p></td>`;
            row += `</tr>`;

            summaryTableBody.innerHTML += row;
        });

        // Add total price row
        let totalRow = '';
        totalRow += `<tr>`;
        totalRow += `<td colspan="2"><p><strong>Total</strong></p></td>`;
        totalRow += `<td><p><strong>₱${totalPrice.toFixed(2)}</strong></p></td>`;
        totalRow += `</tr>`;

        summaryTableBody.innerHTML += totalRow;

        // ** NEW: Display remarks if budget_summary exists **
        displayBuildRemarks(data.budget_summary, currentBudget, totalPrice, currentCategoryFilter, currentBrandFilter);

        // Show summary UI
        summarySection.classList.add("hidden");
        remarksSection.classList.remove("hidden");
        componentsSection.classList.add("hidden");

        summaryTab.classList.remove('active');
        componentsTab.classList.remove('active');
        remarksTab.classList.add('active');

        // Update component mapping
        Object.entries(data).forEach(([key, item]) => {
            if (key === 'budget_summary') return;
            
            let componentType = key;
            if (key === 'pc_case') {
                key = 'case';
                componentType = 'case';
            }
            if (key === 'storage') {
                componentType = item.type;
            }

            let buttonSelector = key === 'storage' 
                ? `button[data-type="${item.type}"]`
                : `button[data-type="${key}"]`;
            
            selectedComponents[componentType] = {
                componentId: item.id,
                name: item.name,
                price: parseFloat(item.price.toString().replace(/,/g, '')),
                imageUrl: item.image || '' // Add image URL if available from API
            };

             // --- NEW: Send generated component to Laravel session ---
            fetch('/store-component', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    type: componentType,
                    name: item.name,
                    price: parseFloat(item.price.toString().replace(/,/g, '')),
                    componentId: item.id,
                    imageUrl: item.image || ''
                })
            })
            .then(response => response.json())
            .then(data => console.log('Session stored:', data.message))
            .catch(error => console.error('Error storing session:', error));

            // UPDATE HIDDEN INPUTS FOR CART FORM
            if (componentType === 'hdd' || componentType === 'ssd') {
                // For storage components, update the storage input
                const storageInput = document.getElementById('hidden_storage');
                if (storageInput) {
                    storageInput.value = item.id;
                }
            } else {
                // For regular components
                const hiddenInput = document.getElementById(`hidden_${componentType}`);
                if (hiddenInput) {
                    hiddenInput.value = item.id;
                }
            }

            const button = document.querySelector(buttonSelector);
            if (button) {
                const selectedName = button.querySelector('.selected-name');
                if (selectedName) {
                    selectedName.textContent = item.name;
                }
            }
        });
    })
    .catch(err => {
        console.error("Error:", err);
        loadingSpinner.classList.add('hidden');
    });
});

buildSectionButtons.forEach(button => {
    button.addEventListener('click', () => {
        currentTypeFilter = button.getAttribute('data-type');

        // UPDATE CATALOG HEADER TITLE
        const catalogTitle = document.getElementById('catalogTitle');
        catalogTitle.textContent = currentTypeFilter.toUpperCase();

        buildSectionButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        catalogList.classList.remove('hidden');

        remarksSection.classList.add("hidden");
        remarksTab.classList.remove('active');
        remarksSection.classList.add("hidden");
        remarksTab.classList.remove('active');
        summarySection.classList.add("hidden");
        summaryTab.classList.remove('active');
        summaryTab.classList.remove('active');
        componentsSection.classList.remove("hidden");
        componentsTab.classList.add('active');
        componentsTab.classList.add('active');

        applyAllFilters();
    })
});

document.querySelectorAll('.catalog-item').forEach(item => {
    item.addEventListener('click', () => {
        const type = item.getAttribute('data-type');
        const name = item.getAttribute('data-name');
        const category = item.getAttribute('data-category');
        const price = parseFloat(item.getAttribute('data-price'));
        const componentId = item.getAttribute('data-id');
        const imageUrl = item.getAttribute('data-image');
        const model = item.getAttribute('data-model');

        // Console log all the data
        console.log('Selected Component:', {
            type: type,
            name: name,
            category: category,
            price: price,
            id: componentId,
            image: imageUrl,
            model3d: model
        });
        // STORE SELECTED COMPONENT
        window.selectedComponents[type] = { componentId, name, price, imageUrl };

        // CALL TOGGLE STORAGE IF SSD OR HDD IS SELECTED
        if (type === 'ssd' || type === 'hdd') {
            toggleStorage(type);
        }
        
                // --- SEND TO LARAVEL SESSION ---
        fetch('/store-component', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                type,
                name,
                price,
                componentId,
                imageUrl
            })
        })
        .then(response => response.json())
        .then(data => console.log(data.message))
        .catch(error => console.error('Error storing session:', error));

        // UPDATE HIDDEN INPUT
        const hiddenInput = document.getElementById(`hidden_${type}`);
        if (hiddenInput) {
            hiddenInput.value = componentId;
        }

        // FIND THE MATCHING BUTTON
        const targetButton = document.querySelector(`#buildSection button[data-type="${type}"]`);
        if (targetButton) {
            const span = targetButton.querySelector('.selected-name');
            if (span) {
                span.textContent = name;
            }

            // STORE SELECTED ID ON BUTTON FOR VALIDATIONS
            targetButton.setAttribute('data-selected-id', componentId);
        }

        // UPDATE DRAGGABLE IMAGE
        const draggable = document.getElementById(type);
        if (draggable && imageUrl) {
            draggable.innerHTML = `
                <img src="${imageUrl}" alt="${name}" >
                <p>${type.toUpperCase()}</p>
                `;
        }

        updateSummaryTable();
    })
});

componentsTab.addEventListener('click', () => {
    componentsSection.classList.remove('hidden');
    summarySection.classList.add('hidden');
    remarksSection.classList.add('hidden');

    componentsTab.classList.add('active');
    summaryTab.classList.remove('active');
    remarksTab.classList.remove('active');
});

summaryTab.addEventListener('click', () => {
    remarksSection.classList.add('hidden');
    componentsSection.classList.add('hidden');
    summarySection.classList.remove('hidden');

    componentsTab.classList.remove('active');
    remarksTab.classList.remove('active');
    summaryTab.classList.add('active');
});

remarksTab.addEventListener('click', () => {
    summarySection.classList.add('hidden');
    componentsSection.classList.add('hidden');
    remarksSection.classList.remove('hidden');
    
    componentsTab.classList.remove('active');
    summaryTab.classList.remove('active');
    remarksTab.classList.add('active');
});


function updateSummaryTable() {
    const tbody = document.querySelector('#summaryTableBody');
    tbody.innerHTML = ''; // CLEAR OLD ENTRIES

    let totalPrice = 0;
    let hasComponents = false;

    for (const [type, component] of Object.entries(window.selectedComponents)) {
        if (!component || !component.componentId) continue;

        hasComponents = true;

        const row = document.createElement('tr');

        // const idCell = document.createElement('td');
        // idCell.innerHTML = `<p>${component.componentId}</p>`;
        
        const nameCell = document.createElement('td');
        nameCell.innerHTML = `<p>${component.name}</p>`;

        const qtyCell = document.createElement('td');
        qtyCell.innerHTML = `<p>1</p>`;

        const priceCell = document.createElement('td');
        priceCell.innerHTML = `<p>₱${component.price.toFixed(2)}</p>`;

        // row.appendChild(idCell);
        row.appendChild(nameCell);
        row.appendChild(qtyCell);
        row.appendChild(priceCell);

        tbody.appendChild(row);

        totalPrice += component.price;
    }

    if (hasComponents) {
        const totalRow = document.createElement('tr');

        const totalLabelCell = document.createElement('td');
        totalLabelCell.setAttribute('colspan', '2');
        totalLabelCell.innerHTML = `<p><strong>Total</strong></p>`;

        const totalPriceCell = document.createElement('td');
        totalPriceCell.innerHTML = `<p><strong>₱${totalPrice.toFixed(2)}</strong></p>`;

        totalRow.appendChild(totalLabelCell);
        totalRow.appendChild(totalPriceCell);

        tbody.appendChild(totalRow);
    }
}

// BUILD CART FORM SUBMISSION - UPDATED VERSION
function handleFormSubmit(e) {
    console.log('=== FORM SUBMISSION INTERCEPTED ===');
    e.preventDefault(); // PREVENT DEFAULT IMMEDIATELY
    
    // Get the form to check its action (order vs save)
    const form = e.target;
    const isOrder = form.action.includes('order');
    console.log('Form type:', isOrder ? 'ORDER' : 'SAVE');
    
    // Payment method validation - ONLY FOR ORDERS
    if (isOrder) {
        const paymentMethod = document.getElementById('payment_method').value;
        console.log('Payment method:', paymentMethod);
        if (!paymentMethod) {
            alert('Please select a payment method.');
            return false;
        }
    }

    // Build name validation
    const buildNameInput = document.querySelector('input[name="build_name"]');
    const buildName = buildNameInput ? buildNameInput.value : '';
    console.log('Build name:', buildName);
    if (!buildName.trim()) {
        alert('Please enter a build name.');
        return false;
    }

    // Check if any components are selected
    console.log('Selected components:', window.selectedComponents);
    if (Object.keys(window.selectedComponents).length === 0) {
        alert('Please select at least one component.');
        return false;
    }

    // Update all hidden inputs before submission
    console.log('=== UPDATING HIDDEN INPUTS ===');
    for (const [type, component] of Object.entries(window.selectedComponents)) {
        const hiddenInput = document.getElementById(`hidden_${type}`);
        if (hiddenInput && component && component.componentId) {
            hiddenInput.value = component.componentId;
            console.log(`Set ${type} to:`, component.componentId);
        } else {
            console.log(`Missing hidden input or component for: ${type}`);
        }
    }

    // Update storage components specifically
    const storageInput = document.getElementById('hidden_storage');
    if (storageInput) {
        if (window.selectedComponents.hdd && window.selectedComponents.hdd.componentId) {
            storageInput.value = window.selectedComponents.hdd.componentId;
        } else if (window.selectedComponents.ssd && window.selectedComponents.ssd.componentId) {
            storageInput.value = window.selectedComponents.ssd.componentId;
        }
        console.log('Storage set to:', storageInput.value);
    }

    // Update total price hidden input
    const totalPriceInput = document.getElementById('hidden_total_price');
    if (totalPriceInput) {
        let totalPrice = 0;
        for (const [type, component] of Object.entries(window.selectedComponents)) {
            if (component && component.price) {
                totalPrice += component.price;
            }
        }
        totalPriceInput.value = totalPrice.toFixed(2);
        console.log('Total price set to:', totalPriceInput.value);
    }

    // VALIDATE IF ALL COMPONENTS ARE SELECTED
    console.log('=== VALIDATING COMPONENTS ===');
    const requiredComponents = ['gpu', 'motherboard', 'cpu', 'psu', 'ram', 'cooler', 'case', 'storage'];
    const allComponentsSelected = requiredComponents.every(type => {
        if (type === 'storage') {
            return (window.selectedComponents.hdd && window.selectedComponents.hdd.componentId) || 
                   (window.selectedComponents.ssd && window.selectedComponents.ssd.componentId);
        }
        return window.selectedComponents[type] && window.selectedComponents[type].componentId;
    });

    console.log('All components selected:', allComponentsSelected);

    if (!allComponentsSelected) {
        const missingComponents = [];
        requiredComponents.forEach(type => {
            if (type === 'storage') {
                if (!window.selectedComponents.hdd?.componentId && !window.selectedComponents.ssd?.componentId) {
                    missingComponents.push('Storage (HDD or SSD)');
                }
            } else if (!window.selectedComponents[type]?.componentId) {
                const componentNames = {
                    'gpu': 'GPU',
                    'motherboard': 'Motherboard',
                    'cpu': 'CPU',
                    'psu': 'Power Supply',
                    'ram': 'RAM',
                    'cooler': 'Cooler',
                    'case': 'Case'
                };
                missingComponents.push(componentNames[type]);
            }
        });

        alert(`Please select the following components:\n\n${missingComponents.join('\n')}`);
        return false;
    }

    console.log('=== FORM VALIDATION PASSED - SUBMITTING ===');
    
    // If all validations pass, submit the form programmatically
    console.log('Submitting form...');
    form.submit(); // Use form.submit() instead of this.submit()
}

// Attach event listener properly
document.addEventListener('DOMContentLoaded', function() {
    const cartForm = document.getElementById('cartForm');
    console.log('DOM loaded - cartForm found:', !!cartForm);
    
    if (cartForm) {
        // Remove any existing event listeners
        cartForm.removeEventListener('submit', handleFormSubmit);
        // Add the event listener
        cartForm.addEventListener('submit', handleFormSubmit);
        console.log('Form submit event listener attached');
    } else {
        console.error('cartForm not found!');
    }

    // Ensure all payment buttons have initial gray style
    document.querySelectorAll('.payment-btn').forEach(btn => {
        if (!btn.style.backgroundColor) {
            btn.style.backgroundColor = '#e5e7eb';
            btn.style.color = '#374151';
        }
    });
});

export function selectPayment(method, button) {
    console.log('Selecting payment:', method);
    
    document.querySelectorAll('.payment-btn').forEach(btn => {
        btn.style.backgroundColor = '#e5e7eb';
        btn.style.color = '#374151';
        btn.style.border = '2px solid transparent';
    });
    
    button.style.backgroundColor = '#fbbf24';
    button.style.color = '#1f2937';
    button.style.border = '2px solid #f59e0b';
    
    document.getElementById('payment_method').value = method;
    console.log('Payment method set to:', document.getElementById('payment_method').value);
}



// ADD DATE TODAY ON THE SUMMARY TAB
window.addEventListener('DOMContentLoaded', () => {
    const dateElement = document.getElementById('buildDate');
    if (dateElement) {
        const today = new Date();
        const formatted = today.toLocaleDateString('en-PH', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
        }); 
        dateElement.textContent = formatted;
    }
});