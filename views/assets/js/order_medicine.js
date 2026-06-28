document.addEventListener('DOMContentLoaded', function() {
    function calculateTotal(row) {
        const quantity = parseFloat(row.querySelector('input[name="quantity[]"]').value) || 0;
        const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;
        const total = quantity * price;
        row.querySelector('.total-price').value = '$' + total.toFixed(2);
        
        let grandTotal = 0;
        document.querySelectorAll('.medicine-item').forEach(item => {
            const itemQuantity = parseFloat(item.querySelector('input[name="quantity[]"]').value) || 0;
            const itemPrice = parseFloat(item.querySelector('input[name="price[]"]').value) || 0;
            grandTotal += itemQuantity * itemPrice;
        });
        
        const grandTotalElement = document.getElementById('grandTotal');
        if (grandTotalElement) {
            grandTotalElement.textContent = '$' + grandTotal.toFixed(2);
        }
    }

    async function fetchMedicinePrice(medicineName, pharmacyId) {
        try {
            const response = await fetch(`index.php?module=patient&action=get_medicine_price&medicine=${encodeURIComponent(medicineName)}&pharmacy_id=${pharmacyId}`);
            const data = await response.json();
            if (data.error) {
                console.error('Error fetching price:', data.error);
                return 0;
            }
            return data.price;
        } catch (error) {
            console.error('Error fetching price:', error);
            return 0;
        }
    }

    function addMedicineRow() {
        const template = document.querySelector('.medicine-item').cloneNode(true);
        template.querySelector('input[name="medicine[]"]').value = '';
        template.querySelector('input[name="quantity[]"]').value = '1';
        template.querySelector('input[name="price[]"]').value = '0.00';
        template.querySelector('.total-price').value = '$0.00';
        template.querySelector('.remove-medicine').style.display = 'block';
        
        // Add event listeners
        template.querySelector('input[name="medicine[]"]').addEventListener('change', async function() {
            const pharmacyId = document.getElementById('pharmacy_id').value;
            if (pharmacyId && this.value) {
                const price = await fetchMedicinePrice(this.value, pharmacyId);
                template.querySelector('input[name="price[]"]').value = price.toFixed(2);
                calculateTotal(template);
            }
        });
        
        template.querySelector('input[name="quantity[]"]').addEventListener('input', () => calculateTotal(template));
        
        template.querySelector('.remove-medicine').addEventListener('click', function() {
            template.remove();
            calculateTotal(document.querySelector('.medicine-item'));
        });
        
        document.getElementById('medicines').appendChild(template);
    }

    document.getElementById('addMedicine').addEventListener('click', addMedicineRow);

    const initialRow = document.querySelector('.medicine-item');
    if (initialRow) {
        // Add event listener for medicine name input
        initialRow.querySelector('input[name="medicine[]"]').addEventListener('change', async function() {
            const pharmacyId = document.getElementById('pharmacy_id').value;
            if (pharmacyId && this.value) {
                const price = await fetchMedicinePrice(this.value, pharmacyId);
                initialRow.querySelector('input[name="price[]"]').value = price.toFixed(2);
                calculateTotal(initialRow);
            }
        });
        
        initialRow.querySelector('input[name="quantity[]"]').addEventListener('input', () => calculateTotal(initialRow));
    }

    // Add event listener for pharmacy selection
    const pharmacySelect = document.getElementById('pharmacy_id');
    if (pharmacySelect) {
        pharmacySelect.addEventListener('change', function() {
            // Reset all prices when pharmacy changes
            document.querySelectorAll('.medicine-item').forEach(async (row) => {
                const medicineName = row.querySelector('input[name="medicine[]"]').value;
                if (this.value && medicineName) {
                    const price = await fetchMedicinePrice(medicineName, this.value);
                    row.querySelector('input[name="price[]"]').value = price.toFixed(2);
                    calculateTotal(row);
                } else {
                    row.querySelector('input[name="price[]"]').value = '0.00';
                    calculateTotal(row);
                }
            });
        });
    }
});