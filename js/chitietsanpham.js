document.addEventListener('DOMContentLoaded', function () {
  const decreaseBtn = document.getElementById('decrease-btn');
  const increaseBtn = document.getElementById('increase-btn');
  const quantityInput = document.getElementById('quantity');
  const productForm = document.getElementById('product-form');

  decreaseBtn.addEventListener('click', function () {
      let currentValue = parseInt(quantityInput.value);
      if (currentValue > 1) {
          quantityInput.value = currentValue - 1;
          decreaseBtn.disabled = (currentValue - 1) <= 1;
          increaseBtn.disabled = false;
          submitForm();
      }
  });

  increaseBtn.addEventListener('click', function () {
      let currentValue = parseInt(quantityInput.value);
      let maxValue = parseInt(quantityInput.getAttribute('max'));
      if (currentValue < maxValue) {
          quantityInput.value = currentValue + 1;
          increaseBtn.disabled = (currentValue + 1) >= maxValue;
          decreaseBtn.disabled = false;
          submitForm();
      }
  });

  quantityInput.addEventListener('change', function () {
      let value = parseInt(quantityInput.value);
      let maxValue = parseInt(quantityInput.getAttribute('max'));
      let minValue = parseInt(quantityInput.getAttribute('min'));

      if (value < minValue || isNaN(value)) {
          quantityInput.value = minValue;
      } else if (value > maxValue) {
          quantityInput.value = maxValue;
      }
      decreaseBtn.disabled = quantityInput.value <= minValue;
      increaseBtn.disabled = quantityInput.value >= maxValue;
      submitForm();
  });

  function submitForm() {
      // Tạo một form ẩn để gửi yêu cầu cập nhật số lượng
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = window.location.href;
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'quantity';
      input.value = quantityInput.value;
      form.appendChild(input);
      document.body.appendChild(form);
      form.submit();
  }
});