let originalUserData = {};

const formatDateToDisplay = (dateStr) => {
  const parts = dateStr.split("-");
  if (parts.length === 3) {
    const [part1, part2, part3] = parts;
    return part1.length === 4 ? `${part3}-${part2}-${part1}` : dateStr;
  }
  return dateStr;
};

const formatDateToInput = (dateStr) => {
  const parts = dateStr.split("-");
  return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateStr;
};

const saveUserInfo = () => {
  const updatedData = {};
  const fields = ["user-name", "user-dob", "user-phone", "user-address"];

  document.querySelectorAll(".edit-input").forEach((input, index) => {
    const span = document.createElement("span");
    let value = input.value.trim();

    if (fields[index] === "user-dob") {
      const parts = value.split("-");
      if (parts.length === 3) value = `${parts[2]}-${parts[1]}-${parts[0]}`;
    }

    span.textContent = value;
    input.replaceWith(span);

    updatedData[fields[index]] =
      fields[index] === "user-dob" ? input.value.trim() : span.textContent;
  });

  fetch("update-user.php", {
    method: "POST",
    body: JSON.stringify(updatedData),
  })
    .then((response) => {
      if (!response.ok)
        throw new Error("Đã xảy ra lỗi với phản hồi từ server.");
      return response.json();
    })
    .then((data) => {
      alert(data.success ? "Cập nhật thành công!" : "Cập nhật thất bại!");
    })
    .catch((err) => console.error("Lỗi fetch:", err));
};

document.getElementById("modal").addEventListener("click", function (event) {
  if (event.target !== this) return;

  const btnEdit = document.querySelector(".btn-edit-user-info");

  if (btnEdit && btnEdit.textContent.trim() === "Lưu") {
    const inputs = document.querySelectorAll(".edit-input");
    const fields = ["user-name", "user-dob", "user-phone", "user-address"];
    let hasChanges = false;

    inputs.forEach((input, index) => {
      const field = fields[index];
      const currentValue = input.value.trim();

      if (field === "user-dob") {
        const parts = currentValue.split("-");
        const formatted =
          parts.length === 3
            ? `${parts[2]}-${parts[1]}-${parts[0]}`
            : currentValue;
        if (formatted !== originalUserData[field]) hasChanges = true;
      } else {
        if (currentValue !== originalUserData[field]) hasChanges = true;
      }
    });

    if (hasChanges && !confirm("Bạn có muốn thoát mà không lưu thay đổi?")) {
      return;
    }

    // Reset lại UI về dữ liệu gốc
    btnEdit.textContent = "Chỉnh sửa";
    inputs.forEach((input, index) => {
      const span = document.createElement("span");
      const field = fields[index];
      let value = originalUserData[field] || "";

      if (field === "user-dob") value = formatDateToDisplay(value);

      span.textContent = value;
      input.replaceWith(span);
    });
  }

  document.body.style.overflow = "auto";
  this.classList.remove("active");

  const userInfoContainer = document.getElementById("user-info-container");
  if (userInfoContainer) {
    userInfoContainer.style.display = "none";
    userInfoContainer.style.height = "330px";
  }

  const btnLogOut = document.querySelector(".btn-log-out");
  if (btnLogOut) btnLogOut.style.marginTop = "20px";
});

document.addEventListener("DOMContentLoaded", () => {
  // Format ngày sinh sau khi load trang
  const dobElement = document.querySelector(".user-dob span");
  if (dobElement) {
    const currentVal = dobElement.textContent.trim();
    dobElement.textContent = formatDateToDisplay(currentVal);
  }

  const editButton = document.querySelector(".btn-edit-user-info");
  const userInfoContainer = document.getElementById("user-info-container");
  const btnLogOut = document.querySelector(".btn-log-out");

  editButton.addEventListener("click", () => {
    const isEditing = editButton.textContent.trim() === "Chỉnh sửa";
    editButton.textContent = isEditing ? "Lưu" : "Chỉnh sửa";

    const fields = ["user-name", "user-dob", "user-phone", "user-address"];

    if (isEditing) {
      userInfoContainer.style.height = "410px";
      btnLogOut.style.marginTop = "15px";

      originalUserData = {};
      const spans = document.querySelectorAll(".user-info-item span");

      spans.forEach((span, index) => {
        const value = span.textContent.trim();
        const input = document.createElement("input");
        const field = fields[index];

        input.type = field === "user-dob" ? "date" : "text";
        input.value = field === "user-dob" ? formatDateToInput(value) : value;
        input.className = "edit-input";

        originalUserData[field] = value;
        span.replaceWith(input);
      });
    } else {
      userInfoContainer.style.height = "330px";
      btnLogOut.style.marginTop = "20px";
      saveUserInfo();
    }
  });
});
