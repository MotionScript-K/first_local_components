$(document).ready(function () {

    // Обработчик для кнопки "Показать еще комментарии"
    $(document).on('click', '.card-comments__load_more', function () {
        let button = $(this),
            postID = button.data("postid"),
            parentID = button.data("parentid"),
            url = '/ajax/load_more_comments.php';

        $.get(url, {POST_ID: postID, PARENT_ID: parentID, AJAX: "Y"})
            .done(function (data) {
                let target = button.closest(".parent_card-comments__item");
                document.querySelector(`div[data-parentid="${parentID}"]`).remove();
                target.append(data)
                document.querySelector(`button[data-parentid="${parentID}"]`).remove();
            })
            .fail(function () {
                console.error("Не удалось загрузить комментарии.");
            });
    });

    // Обработчик для кнопки "загрузить ещё" родительские комменты
    $(document).on('click', '.next_comments', function () {
        let button = $(this),
            postID = button.data("postid"),
            stepCount = parseInt(button.data("step")),
            url = '/ajax/load_more_comments.php';

        stepCount++;

        $.get(url, {AJAX: "Y", POST_ID: postID, STEP: stepCount})
            .done(function (data) {
                document.querySelector('.show_next_comments').remove();
                let container = $('.card-comments__container');
                container.append(data);
            })
            .fail(function () {
                console.error("Не удалось загрузить комментарии.");
            });

    });

});


