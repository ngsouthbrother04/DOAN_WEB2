document.addEventListener('DOMContentLoaded', function () {
    const confirmBtn = document.querySelector('.confirm-btn');
    const modal = document.getElementById('order-summary-modal');
    const summaryDetails = document.getElementById('order-summary-details');
    const cancelBtn = document.getElementById('cancel-summary');

    confirmBtn.addEventListener('click', function (e) {
        e.preventDefault();

        // Lấy dữ liệu sản phẩm
        const rows = document.querySelectorAll('.items-table tbody tr');
        let productHTML = '<div class="section-title">Sản phẩm</div><ul>';
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            productHTML += `<li>${cells[0].innerText} - Số lượng: ${cells[1].innerText}, Tổng: ${cells[3].innerText}</li>`;
        });
        productHTML += '</ul>';

        // Lấy thông tin người nhận
        const hoTen = document.getElementById('ho_ten').value;
        const diaChi = document.getElementById('dia_chi').value;
        const diaChiKhac = document.getElementById('dia_chi_khac').value;
        const sdt = document.getElementById('sdt').value;
        const sdtKhac = document.getElementById('sdt_khac').value;
        const ghiChu = document.getElementById('ghi_chu').value;

        let diaChiHienThi = diaChiKhac ? diaChiKhac : diaChi;
        let sdtHienThi = sdtKhac ? sdtKhac : sdt;

        let userInfoHTML = `
            <div class="section-title">Thông tin người nhận</div>
            <p><strong>Họ tên:</strong> ${hoTen}</p>
            <p><strong>Địa chỉ:</strong> ${diaChiHienThi}</p>
            <p><strong>Số điện thoại:</strong> ${sdtHienThi}</p>
        `;

        // Phương thức thanh toán
        const payment = document.querySelector('input[name="payment"]:checked').nextSibling.textContent.trim();
        let paymentHTML = `
            <div class="section-title">Phương thức thanh toán</div>
            <p>${payment}</p>
        `;

        // Ghi chú
        let noteHTML = '';
        if (ghiChu.trim() !== '') {
            noteHTML = `
                <div class="section-title">Ghi chú</div>
                <p>${ghiChu}</p>
            `;
        }

        summaryDetails.innerHTML = productHTML + userInfoHTML + paymentHTML + noteHTML;
        modal.style.display = 'block';
    });

    cancelBtn.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    window.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
