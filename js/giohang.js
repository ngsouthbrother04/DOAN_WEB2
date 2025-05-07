document.addEventListener('DOMContentLoaded', function() {
    const decreaseButtons = document.querySelectorAll('.decrease-quantity');
    const increaseButtons = document.querySelectorAll('.increase-quantity');
    const quantityInputs = document.querySelectorAll('.quantity input');

    decreaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.nextElementSibling;
            let value = parseInt(input.value);
            const min = parseInt(input.getAttribute('min'));
            if (value > min) {
                input.value = value - 1;
                this.disabled = (value - 1) <= min;
                updateIncreaseButton(input);
            }
        });
    });

    increaseButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            let value = parseInt(input.value);
            const max = parseInt(input.getAttribute('max'));
            if (value < max) {
                input.value = value + 1;
                this.disabled = (value + 1) >= max;
                updateDecreaseButton(input);
            }
        });
    });

    function updateDecreaseButton(input) {
        const decreaseButton = input.previousElementSibling;
        decreaseButton.disabled = parseInt(input.value) <= parseInt(input.getAttribute('min'));
    }

    function updateIncreaseButton(input) {
        const increaseButton = input.nextElementSibling;
        increaseButton.disabled = parseInt(input.value) >= parseInt(input.getAttribute('max'));
    }

    // Đảm bảo giá trị input không bị thay đổi thủ công
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            let value = parseInt(this.value);
            const max = parseInt(this.getAttribute('max'));
            const min = parseInt(this.getAttribute('min'));

            if (isNaN(value) || value < min) {
                this.value = min;
            } else if (value > max) {
                this.value = max;
            }

            updateDecreaseButton(this);
            updateIncreaseButton(this);
        });
    });
});