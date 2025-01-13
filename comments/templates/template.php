<div class="card-comments__top">
    <div class="card-comments__top_value"><?= (int)$arResult["COMMENTS_COUNT"] ?> <?= sklonenie($arResult["COMMENTS_COUNT"], array('комментарий', 'комментария', 'комментариев')); ?></div>
</div>

<? if ($GLOBALS["IS_AUTH"]) { ?>
    <div class="centered">
        <button class="button button-outlined to-comment modal-call"
                data-modal="addComment" data-elementId="<?= (int)$arParams["POST_ID"] ?>"><span>Комментировать</span>
        </button>
    </div>
<? } else { ?>
    <div class="centered need_auth_block">
        Вы не авторизированы. Для добавления комментария <a href="#" data-modal="authorization" class="modal-call">авторизируйтесь</a>.
    </div>
<? } ?>
<div class="card-comments__container">
    <?
    showComments($arResult["COMMENTS"], (int)$arParams["LEVEL"], (int)$arParams["POST_ID"]);
    ?>
</div>

<?
//при первой загрузке страницы проверяем возможность догрузить родительские комменты
if ($arResult["STEP_COUNT"] / $arParams["COMMENT_SIZE"] > $arParams["STEP"]) { ?>
    <div class="show_next_comments">
        <button class="next_comments" data-modal="addComment" data-postid="<?= (int)$arParams["POST_ID"] ?>"
                data-step="<?= (int)$arParams["STEP"] ?>">
            <span>Смотреть ещё</span>
        </button>
    </div>
<? } ?>

<?
// Переменная для количества комментариев на счетчик
$this->SetViewTarget('comments_count');
echo (int)$arResult["COMMENTS_COUNT"];
$this->EndViewTarget();
?>
