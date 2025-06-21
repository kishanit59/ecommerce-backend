document.addEventListener('DOMContentLoaded', function() {
    const userIdSelect = document.querySelector('select[name="user_id"]');
    
    if (userIdSelect) {
        userIdSelect.addEventListener('change', function() {
            const userId = this.value;
            if (!userId) return;
            
            // Fetch user's cart items via AJAX
            fetch(`/admin/get-user-cart/${userId}`)
                .then(response => response.json())
                .then(data => {
                    // Clear existing items
                    const repeater = document.querySelector('[data-slot="root"] [data-repeater]');
                    if (repeater) {
                        const removeButtons = repeater.querySelectorAll('[data-repeater-delete]');
                        removeButtons.forEach(button => button.click());
                    }
                    
                    // Add new items from cart
                    data.items.forEach(item => {
                        const addButton = document.querySelector('[data-repeater-create]');
                        if (addButton) {
                            addButton.click();
                            
                            // Wait for the new item to be added
                            setTimeout(() => {
                                const lastItem = document.querySelector('[data-slot="root"] [data-repeater-item]:last-child');
                                if (lastItem) {
                                    const productSelect = lastItem.querySelector('select[name$="[product_id]"]');
                                    const quantityInput = lastItem.querySelector('input[name$="[quantity]"]');
                                    
                                    if (productSelect && quantityInput) {
                                        productSelect.value = item.product_id;
                                        productSelect.dispatchEvent(new Event('change'));
                                        
                                        quantityInput.value = item.quantity;
                                        quantityInput.dispatchEvent(new Event('change'));
                                    }
                                }
                            }, 100);
                        }
                    });
                });
        });
    }
});