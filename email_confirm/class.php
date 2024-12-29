<?php

use Bitrix\Main\Mail\Event;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class EmailConfirm extends CBitrixComponent {

    /**
     * обновляет статус подтверждения почты
     */
    protected function setEmailConfirm(): bool
    {
        $rsUser = CUser::GetByID($this->arParams['USER_ID']);
        $arUser = $rsUser->Fetch();

        if ($arUser["CONFIRM_CODE"] === $this->arParams['CONFIRM_CODE']) {
            // меняем статус
            $user = new CUser;
            $fields = [ "UF_EMAIL_CONFIRM" => "Y" ];
            if($user->Update($arUser["ID"], $fields)) {
                return true;
            }
        }
        return false;
    }

    public function executeComponent()
    {

    global $USER;

    $templateName = $this->getTemplateName(); //получаем имя подключаемого шаблона

    if($templateName) { //если имя шаблона задано
        $rsUser = CUser::GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();
        if(!$arUser["UF_EMAIL_CONFIRM"]) { // Если у пользователя не подтверждена почта
            if (!$_COOKIE["CONFIRM_EMAIL_POPUP"] && $templateName == "personal_popup") { //нет куки на сессию и имя шаблона personal_popup
                $this->includeComponentTemplate();
            } elseif ($templateName == "info_string_confirm") { // имя шаблона info_string_confirm
                $this->includeComponentTemplate();
            }
        }

    } elseif (isset($this->arParams['CONFIRM_CODE']) && isset($this->arParams['USER_ID'])) { //если перешли из письма
        if( $this->setEmailConfirm() ) { //пробуем изменить статус подтверждения почты
            $this->includeComponentTemplate();
        }
    } elseif (isset($this->arParams["EMAIL_CONFIRM"])) { // если ajax запрос на отправить письмо
        $rsUser = CUser::GetByID($USER->GetID());
        $arUser = $rsUser->Fetch();
        Event::send([
            "EVENT_NAME" => "USER_INFO",
            "LID" => "cz",
            "C_FIELDS" => [
                "EMAIL" => $arUser["EMAIL"],
                "USER_ID" => $arUser["ID"],
                "NAME" => $arUser["NAME"],

                "EMAIL_CONFIRM" => "?CONFIRM_CODE=".$arUser["CONFIRM_CODE"]."&USER_ID=".$arUser["ID"],
            ],
            "MESSAGE_ID" => 1041,
        ]);
    }

    }
}
