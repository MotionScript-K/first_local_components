<?php
//если в запросе был AJAX=Y то просто грузим доп комменты
showComments($arResult["COMMENTS"], (int)$arParams["LEVEL"], (int)$arParams["POST_ID"]);

//если не указывали шаг то не рисуем кнопку загрузки доп комментов
if ($arParams["STEP"] != null) {
    //проверяем возможность получить родительские комментарии
    if ($arResult["STEP_COUNT"] / $arParams["COMMENT_SIZE"] > $arParams["STEP"]) { ?>
        <div class="show_next_comments">
            <button class="next_comments" data-modal="addComment" data-postid="<?= (int)$arParams["POST_ID"] ?>"
                    data-step="<?= (int)$arParams["STEP"] ?>">
                <span>Смотреть ещё</span>
            </button>
        </div>
    <? }
} ?>