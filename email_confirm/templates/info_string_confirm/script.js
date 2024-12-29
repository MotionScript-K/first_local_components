$(document).ready(function () {
    createConfirmInfo();
})

//создаем предупреждение для подтверждения e-mail
function createConfirmInfo() {
    let emailInput = document.querySelector("input[name='EMAIL']");
    if (emailInput) {
        let label = emailInput.previousElementSibling; // Находим тег label
        let span = label.querySelector("span");
        let newDiv = document.createElement("div");

        label.style.width = "100%";
        label.style.display = "flex";
        label.style.flexDirection = "row";
        label.style.alignItems = "center";

        // Устанавливаем стили для выравнивания элементов в одну строку
        newDiv.style.display = "flex";
        newDiv.style.alignItems = "center";
        newDiv.style.gap = "5px";
        newDiv.style.fontSize = "10px";

        // Добавляем картинку
        let img = document.createElement("img");
        img.src = "/images/ico_popup_notification.png";
        img.alt = "info";
        img.style.width = "15px";
        img.style.height = "15px";
        img.style.margin = "0 0 0 5px";

        // Добавляем текст
        let text = document.createElement("span");
        text.textContent = "Подтвердите e-mail, перейдя по ссылке в письме. ";

        // Добавляем ссылку
        let link = document.createElement("a");
        link.href = "#";
        link.textContent = "Отправить ссылку";
        link.style.textDecoration = "underline";
        // Добавляем обработчик события клика отправить письмо
        link.addEventListener("click", function(event) {
            event.preventDefault();
            sendAjax();
        });

        // Добавляем созданные элементы в новый div
        newDiv.appendChild(img);
        newDiv.appendChild(text);
        newDiv.appendChild(link);

        // Вставляем новый div в нужное место
        label.insertBefore(newDiv, span.nextSibling);
    }

}

function sendAjax() {
    fetch("/ajax/email_confirm.php?EMAIL_CONFIRM=Y")
        .then(response => {
            if (!response.ok) { // если статус не ok
                throw new Error("Ошибка отправки письма");
            }
        })
        .then(() => {
            showPopUp();
        })
        .catch(error => {
            console.log(error.message);
        });
}

// модальное окно - Письмо отправлено!
function showPopUp() {
    let profileConfirmedPopup = document.createElement('div');
    profileConfirmedPopup.setAttribute('class', 'modal is-open');
    profileConfirmedPopup.setAttribute('id', 'userConfirmed');
    profileConfirmedPopup.innerHTML = `
		<div class="modal-dialog">
			<div class="modal-container">
				<div class="modal-close"></div>
				<div class="modal-content">Письмо отправлено. Проверьте пожалуйста вашу почту</div>
			</div>
		</div>
	`;
    document.body.appendChild(profileConfirmedPopup);

    // закрытие модальных окон на крестик
    let closeBtns = document.querySelectorAll('.modal-close');
    closeBtns.forEach(btn => {
        btn.onclick = function () {
            let modalOpened = btn.closest('.modal');
            modalOpened.classList.remove('is-open');
        }
    });
}