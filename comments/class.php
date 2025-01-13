<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class Comments extends CBitrixComponent
{
    /**
     * получаем комментарии для поста
     * (Родитель + потомки) - "уровень вложенности"
     * @return array
     */
    public function setArResult($postId, $COMMENTS_IBLOCK_ID, $parentId = null, $step = null)
    {

        // получаем количество родительских комментариев для вычисления шага
        $arFilter = [
            "IBLOCK_ID" => $COMMENTS_IBLOCK_ID,
            "GLOBAL_ACTIVE" => "Y",
            "ACTIVE" => "Y",
            "CODE" => $postId,
            "SECTION_ID" => null
        ];
        $this->arResult["STEP_COUNT"] = CIBlockSection::GetCount($arFilter);

        //получаем сколько всего комментариев есть в статье для вывода на странице
        $arFilter = [
            "IBLOCK_ID" => $COMMENTS_IBLOCK_ID,
            "GLOBAL_ACTIVE" => "Y",
            "ACTIVE" => "Y",
            "CODE" => $postId,
        ];
        $this->arResult["COMMENTS_COUNT"] = CIBlockSection::GetCount($arFilter);

        $this->arResult["COMMENTS"] = [];

        //получаем родителей
        $parentComments = $this->getParentComments($COMMENTS_IBLOCK_ID, $postId, $parentId, $step, $this->arParams["COMMENT_SIZE"]);

        foreach ($parentComments as $parentComment) {
            //получаем детей
            $parentComment["CHILD"] = $this->getChildComments($COMMENTS_IBLOCK_ID, $postId, $parentComment["ID"]);

            //формируем полный массив
            $this->arResult["COMMENTS"][$parentComment["ID"]] = $parentComment;
        }
    }

    /**
     * Получаем родительские комментарии
     * @param $COMMENTS_IBLOCK_ID
     * @param $postId - ИД поста для комментариев
     * @return array
     */
    protected function getParentComments($COMMENTS_IBLOCK_ID, $postId, $parentId = null, $step = null, $commentSize)
    {
        $comments = [];

        $arFilter = [
            "IBLOCK_ID" => $COMMENTS_IBLOCK_ID,
            "GLOBAL_ACTIVE" => "Y",
            "ACTIVE" => "Y",
            "CODE" => $postId,
            "SECTION_ID" => $parentId
        ];

        $arNavStartParams = [
            "iNumPage" => $step,
            "nPageSize" => $commentSize
        ];

        $arSort = ['DATE_CREATE' => 'DESC', 'ID' => 'ASC'];
        $arSelect = ["ID", "NAME", "LEFT_MARGIN", "RIGHT_MARGIN", "DEPTH_LEVEL", "IBLOCK_ID", "IBLOCK_SECTION_ID", "LIST_PAGE_URL", "SECTION_PAGE_URL", "CREATED_BY", "DATE_CREATE", "DESCRIPTION", "UF_DATE_TIME", "UF_FILES"];
        $rs = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect, $arNavStartParams);

        while ($ob = $rs->Fetch()) {
            //инфо пользователей
            $this->addUserAvatarLogic($ob);

            $comments[$ob["ID"]] = $ob;
        }
        return $comments;
    }

    /**
     * Получаем Дочерние комменатрии - ограничение по 2шт.
     * @param $COMMENTS_IBLOCK_ID
     * @param $postId - ИД поста для комментариев
     * @param $parentId - ИД родительского комментария
     * @return array
     */
    protected function getChildComments($COMMENTS_IBLOCK_ID, $postId, $parentId)
    {
        $comments = [];

        $arFilter = [
            "IBLOCK_ID" => $COMMENTS_IBLOCK_ID,
            "GLOBAL_ACTIVE" => "Y",
            "ACTIVE" => "Y",
            "CODE" => $postId,
            "SECTION_ID" => $parentId
        ];

        $arNavStartParams = [
            "nPageSize" => 2
        ];

        $arSort = ['DATE_CREATE' => 'DESC', 'ID' => 'ASC'];
        $arSelect = ["ID", "NAME", "LEFT_MARGIN", "RIGHT_MARGIN", "DEPTH_LEVEL", "IBLOCK_ID", "IBLOCK_SECTION_ID", "LIST_PAGE_URL", "SECTION_PAGE_URL", "CREATED_BY", "DATE_CREATE", "DESCRIPTION", "UF_DATE_TIME", "UF_FILES"];
        $rs = CIBlockSection::GetList($arSort, $arFilter, false, $arSelect, $arNavStartParams);

        while ($ob = $rs->Fetch()) {
            //инфо пользователей
            $this->addUserAvatarLogic($ob);

            $comments[] = $ob;
        }
        return $comments;
    }

    /**
     * устанавливаем имя, аватар
     * @param $comment - Комментарий
     */
    protected function addUserAvatarLogic(&$comment)
    {
        $rsUser = CUser::GetByID($comment["CREATED_BY"]);
        $arUser = $rsUser->Fetch();

        // Если пользователь существует, добавляем его данные и фото
        if ($arUser) {
            $comment["USER_NAME"] = $arUser["NAME"] . " " . $arUser["LAST_NAME"];

            $comment["USER_PHOTO"] = '/local/templates/svadba/img/profile-user.png'; // Путь к дефолтной аватарке

            if ($arUser["PERSONAL_PHOTO"]) {
                $imgPC = CFile::GetPath($arUser["PERSONAL_PHOTO"]);
                $comment["USER_PHOTO"] = $imgPC;

                $img = CFile::ResizeImageGet($arUser["PERSONAL_PHOTO"], ['width' => 38, 'height' => 38], BX_RESIZE_IMAGE_EXACT)['src'];
                $comment["USER_PHOTO"] = $img;
            }

            // Проверяем на админа
            if (array_intersect([1, 8], CUser::GetUserGroup($arUser['ID']))) {
                $comment["USER_IS_ADMIN"] = true;
            }
        } else {
            // Если пользователь не найден - пустые значения
            $comment["USER_NAME"] = false;
            $comment["USER_PHOTO"] = '/local/templates/svadba/img/profile-user.png';
        }

        //загрузка прикрепленных фото в комментарии
        if ($comment["UF_FILES"] && sizeof($comment["UF_FILES"]) > 0) {
            foreach ($comment["UF_FILES"] as $i => $fileId) {
                $arFile = [];

                $arFile['ID'] = $fileId;
                $arFile['SRC_DETAIL'] = CFile::GetPath($fileId);

                $img = CFile::ResizeImageGet($arFile['ID'], ['width' => 48, 'height' => 48], BX_RESIZE_IMAGE_EXACT)['src'];
                $arFile['SRC_PREVIEW'] = $img;

                $comment["UF_FILES_READY"][$i] = $arFile;

            }

        }
    }


    public function executeComponent()
    {
        $COMMENTS_IBLOCK_ID = IBLOCK_ID_COMMENT;
        $postId = $this->arParams["POST_ID"];
        $parentId = $this->arParams["PARENT_ID"];
        $stepCount = $this->arParams["STEP"];

        if ($parentId) {
            $this->setArResult($postId, $COMMENTS_IBLOCK_ID, $parentId);
        } elseif ($stepCount) {
            $this->setArResult($postId, $COMMENTS_IBLOCK_ID, null, $stepCount);
        } else {
            $this->setArResult($postId, $COMMENTS_IBLOCK_ID);
        }

        if($this->arParams["AJAX"]){
            //подгрузка комментариев
            $this->includeComponentTemplate("ajax");
        } else {
            //загрузка страницы
            $this->includeComponentTemplate();
        }

    }
}
