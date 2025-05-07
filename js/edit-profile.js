let originalUserData = {};

const formatDateToDisplay = (dateStr) => {
  const parts = dateStr.split("-");
  if (parts.length === 3) {
    const [part1, part2, part3] = parts;

    return part1.length === 4 ? `${part3}-${part2}-${part1}` : dateStr;
  }
  return dateStr;
};

//=======================================================================================================================


const formatDateToInput = (dateStr) => {
  const parts = dateStr.split("-");

  return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateStr;
};

//=======================================================================================================================

const showMessage = (message, isSuccess) => {
  let messageDiv = document.getElementById("edit-message");
  const userInfoContainer = document.getElementById("user-info-container");

  if (!messageDiv) {
    messageDiv = document.createElement("div");
    messageDiv.id = "edit-message";
    messageDiv.style.textAlign = "center";
    messageDiv.style.marginBottom = "15px";

    if (userInfoContainer) {
      userInfoContainer.insertBefore(messageDiv, userInfoContainer.firstChild);
    } else {
      console.error("user-info-container not found");

      return;
    }
  }

  userInfoContainer.style.height = "380px";
  messageDiv.style.display = "block";
  messageDiv.style.color = isSuccess ? "green" : "red";
  messageDiv.innerHTML = `<div class="${
    isSuccess ? "success-message" : "error-message"
  }">${message}</div>`;
};

//=======================================================================================================================

const saveUserInfo = () => {
  const updatedData = {};
  const fields = ["user-name", "user-dob", "user-phone", "user-address"];
  const inputs = document.querySelectorAll(".edit-input");

  if (inputs.length !== fields.length) {
    console.error(
      "Mismatch in number of inputs:",
      inputs.length,
      "expected:",
      fields.length
    );
    showMessage("Lỗi: Không tìm thấy đầy đủ thông tin để cập nhật!", false);

    inputs.forEach((input, index) => {
      const span = document.createElement("span");
      let value = input.value.trim();
      if (fields[index] === "user-dob") value = formatDateToDisplay(value);
      span.textContent = value;
      input.replaceWith(span);
    });
    return;
  }

  let hasError = false;
  let hasChange = false;

  inputs.forEach((input, index) => {
    const field = fields[index];
    let value = input.value.trim();

    if (!value) {
      showMessage("Vui lòng điền đầy đủ thông tin!", false);

      hasError = true;
    }

    if (field === "user-phone" && !/^0[0-9]{9}$/.test(value)) {
      showMessage(
        "Số điện thoại phải bắt đầu bằng 0 và có đúng 10 chữ số!",
        false
      );

      hasError = true;
    }

    if (field === "user-dob") {
      const parts = value.split("-");

      if (parts.length === 3) value = `${parts[2]}-${parts[1]}-${parts[0]}`;
    }

    if (value !== originalUserData[field]) hasChange = true;

    updatedData[field] = field === "user-dob" ? input.value.trim() : value;
  });

  if (hasError) {
    inputs.forEach((input, index) => {
      const span = document.createElement("span");
      let value = input.value.trim();
      if (fields[index] === "user-dob") value = formatDateToDisplay(value);
      span.textContent = value;
      input.replaceWith(span);
    });

    return;
  }

  inputs.forEach((input, index) => {
    const span = document.createElement("span");
    let value = input.value.trim();

    if (fields[index] === "user-dob") value = formatDateToDisplay(value);

    span.textContent = value;
    input.replaceWith(span);
  });

  if (!hasChange) return;

  const editButton = document.querySelector(".btn-edit-user-info");
  const userInfoContainer = document.getElementById("user-info-container");

  fetch("update-user.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(updatedData),
  })
    .then((response) => {
      if (!response.ok) {
        return response.text().then((text) => {
          throw new Error(`Server error: ${response.status} - ${text}`);
        });
      }

      return response.json();
    })
    .then((data) => {
      showMessage(data.message, data.success);

      if (data.success) {
        Object.assign(originalUserData, updatedData);

        setTimeout(() => {
          const messageDiv = document.getElementById("edit-message");
          if (messageDiv) messageDiv.style.display = "none";

          userInfoContainer.style.height = "330px";
        }, 1000);
      } else {
        if (editButton) {
          editButton.textContent = "Chỉnh sửa";
          editButton.click();
        } else {
          console.error("btn-edit-user-info not found");
        }
      }
    })
    .catch((err) => {
      console.error("Lỗi fetch:", err);
      showMessage(
        "Đã xảy ra lỗi khi cập nhật thông tin! Vui lòng thử lại.",
        false
      );
    });
};

//=======================================================================================================================

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

    if (hasChanges) {
      const confirmed = confirm("Bạn có muốn thoát mà không lưu thay đổi?");

      if (!confirmed) {
        event.stopImmediatePropagation();

        return;
      }
    }

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

//=======================================================================================================================

document.addEventListener("DOMContentLoaded", () => {
  // Format ngày sinh sau khi load trang
  const dobElement = document.querySelector(".user-dob span");
  if (dobElement) {
    const currentVal = dobElement.textContent.trim();

    dobElement.textContent = formatDateToDisplay(currentVal);
  } else {
    console.warn("user-dob span not found");
  }

  const editButton = document.querySelector(".btn-edit-user-info");
  const userInfoContainer = document.getElementById("user-info-container");
  const btnLogOut = document.querySelector(".btn-log-out");

  if (!editButton) {
    console.error("btn-edit-user-info not found");

    return;
  }
  if (!userInfoContainer) {
    console.error("user-info-container not found");

    return;
  }
  if (!btnLogOut) console.warn("btn-log-out not found");

  editButton.addEventListener("click", () => {
    const isEditing = editButton.textContent.trim() === "Chỉnh sửa";
    editButton.textContent = isEditing ? "Lưu" : "Chỉnh sửa";

    const fields = ["user-name", "user-dob", "user-phone", "user-address"];

    if (isEditing) {
      const messageDiv = document.getElementById("edit-message");
      if (messageDiv) messageDiv.style.display = "none";

      userInfoContainer.style.height = "400px";
      if (btnLogOut) btnLogOut.style.marginTop = "15px";

      originalUserData = {};
      const spans = document.querySelectorAll(".user-info-item span");

      if (spans.length !== fields.length) {
        console.error(
          "Mismatch in number of spans:",
          spans.length,
          "expected:",
          fields.length
        );

        return;
      }

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

      if (btnLogOut) btnLogOut.style.marginTop = "20px";

      saveUserInfo();
    }
  });
});
