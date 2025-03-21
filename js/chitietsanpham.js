document.addEventListener('DOMContentLoaded', function() {
   const quantityInput = document.getElementById('quantity');
   const decreaseBtn = document.getElementById('decrease-btn');
   const increaseBtn = document.getElementById('increase-btn');
   const maxQuantity = parseInt(quantityInput.getAttribute('max'));
   
   // Xử lý nút giảm số lượng
   decreaseBtn.addEventListener('click', function() {
      let currentValue = parseInt(quantityInput.value);
      if (currentValue > 1) {
         currentValue--;
         quantityInput.value = currentValue;
         
         // Cập nhật trạng thái nút
         updateButtonStates(currentValue);
      }
   });
   
   // Xử lý nút tăng số lượng
   increaseBtn.addEventListener('click', function() {
      let currentValue = parseInt(quantityInput.value);
      if (currentValue < maxQuantity) {
         currentValue++;
         quantityInput.value = currentValue;
         
         // Cập nhật trạng thái nút
         updateButtonStates(currentValue);
      }
   });
   
   // Hàm cập nhật trạng thái nút
   function updateButtonStates(currentValue) {
      // Cập nhật nút giảm
      if (currentValue <= 1) {
         decreaseBtn.disabled = true;
      } else {
         decreaseBtn.disabled = false;
      }
      
      // Cập nhật nút tăng
      if (currentValue >= maxQuantity) {
         increaseBtn.disabled = true;
      } else {
         increaseBtn.disabled = false;
      }
   }
   
   // Đảm bảo giá trị nhập vào hợp lệ khi người dùng thay đổi trực tiếp
   quantityInput.addEventListener('change', function() {
      let value = parseInt(this.value);
      if (isNaN(value) || value < 1) {
         value = 1;
      } else if (value > maxQuantity) {
         value = maxQuantity;
      }
      
      this.value = value;
      updateButtonStates(value);
   });
});