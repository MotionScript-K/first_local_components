$(document).ready(function () {

        // модальное окно - Необходимо подтвердить e-mail
        let confirmProfilePopup = document.createElement('div');
        confirmProfilePopup.setAttribute('class', 'modal is-open');
        confirmProfilePopup.setAttribute('id', 'confirmUser');
        confirmProfilePopup.innerHTML = `
		<div class="modal-dialog">
			<div class="modal-container">
				<div class="modal-close"></div>
				<div class="modal-content">
					<div class="modal-notification">
						Для получения информации о статусах заказа, просим подтвердить e-mail, перейдя по ссылке в письме.
						<a class="modal-close-link" href="#">Отправить ссылку</a>
					</div>
				</div>
			</div>
		</div>
	`;
        // Добавляем моадльное окно на страницу
        document.body.appendChild(confirmProfilePopup);
        //клик отправить письмо
        document.querySelector('.modal-close-link').onclick = function(event) {
            event.preventDefault();
            fetch("/ajax/email_confirm.php?EMAIL_CONFIRM=Y")
                .then(response => {
                    if (!response.ok) { // если статус не ok
                        throw new Error("Ошибка отправки письма");
                    }
                    document.cookie = "CONFIRM_EMAIL_POPUP=true; path=/"; // Устанавливаем cookie что бы не показывать
                })
                .catch(error => {
                    console.log(error.message);
                });
            this.closest('.modal').classList.remove('is-open'); // Закрываем модальное окно
        };


    // закрытие модальных окон на крестик
    let closeBtns = document.querySelectorAll('.modal-close');
    closeBtns.forEach(btn => {
        btn.onclick = function () {
            let modalOpened = btn.closest('.modal');
            modalOpened.classList.remove('is-open');
            //устанавливаем cookie что бы не показывать пока есть сессия в браузере
            document.cookie = "CONFIRM_EMAIL_POPUP=true; path=/";
        }
    });

})
