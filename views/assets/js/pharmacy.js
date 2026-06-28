document.addEventListener('DOMContentLoaded', function() {
    const pharmacySelect = document.getElementById('pharmacy_id');
    const pharmacyDetails = document.getElementById('pharmacyDetails');
    const prescriptionSelect = document.getElementById('prescription_id');
    const prescriptionDetails = document.getElementById('prescriptionDetails');
    const prescriptionContent = document.getElementById('prescriptionContent');
    const deliveryCheckbox = document.getElementById('delivery_requested');
    const deliveryAddressGroup = document.getElementById('deliveryAddressGroup');
    
    // Show pharmacy details when a pharmacy is selected
    if (pharmacySelect) {
        pharmacySelect.addEventListener('change', function() {
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const pharmacyName = selectedOption.textContent.trim();
                const deliveryAvailable = selectedOption.dataset.delivery === '1' ? 'Yes' : 'No';
                
                pharmacyDetails.querySelector('.pharmacy-name').textContent = pharmacyName;
                pharmacyDetails.querySelector('.pharmacy-delivery').textContent = 'Delivery Available: ' + deliveryAvailable;
                
                pharmacyDetails.classList.remove('d-none');
                
                // If delivery is not available, uncheck and disable the delivery checkbox
                if (deliveryAvailable === 'No') {
                    deliveryCheckbox.checked = false;
                    deliveryCheckbox.disabled = true;
                    deliveryAddressGroup.classList.add('d-none');
                } else {
                    deliveryCheckbox.disabled = false;
                }
            } else {
                pharmacyDetails.classList.add('d-none');
            }
        });
    }
    
    // Show prescription details when a prescription is selected
    if (prescriptionSelect) {
        prescriptionSelect.addEventListener('change', function() {
            if (this.value) {
                // In a real application, you would fetch the prescription details via AJAX
                // For now, we'll just show a placeholder
                prescriptionContent.innerHTML = '<p>Loading prescription details...</p>';
                prescriptionDetails.classList.remove('d-none');
                
                // Simulate loading prescription details
                setTimeout(() => {
                    const selectedOption = this.options[this.selectedIndex];
                    const prescriptionText = selectedOption.textContent.trim();
                    prescriptionContent.innerHTML = '<p>' + prescriptionText + '</p>';
                }, 500);
            } else {
                prescriptionDetails.classList.add('d-none');
            }
        });
    }
    
    // Toggle delivery address field
    if (deliveryCheckbox) {
        deliveryCheckbox.addEventListener('change', function() {
            if (this.checked) {
                deliveryAddressGroup.classList.remove('d-none');
            } else {
                deliveryAddressGroup.classList.add('d-none');
            }
        });
    }
    
    // Trigger change events to initialize the form state
    if (pharmacySelect && pharmacySelect.value) {
        pharmacySelect.dispatchEvent(new Event('change'));
    }
    
    if (prescriptionSelect && prescriptionSelect.value) {
        prescriptionSelect.dispatchEvent(new Event('change'));
    }
}); 