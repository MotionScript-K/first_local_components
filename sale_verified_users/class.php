<?php

use Services\SMS;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock\HighloadBlockTable;


if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class SaleVerifiedUsers extends CBitrixComponent {


    public function executeComponent() {

        $phone = $this->arParams["PHONE"];
        $secret = $this->arParams["SECRET"];
        $name = $this->arParams["NAME"];
        $code = $this->arParams["CODE"];
        $smsError = $this->arParams["SMS_ERROR"];
        $isUserVerified = $this->arParams["IS_USER_VERIFIED"];


        if ($phone && !$secret && !$smsError && !$code) {
            $sms = new SMS();
            $sms->getCode($phone);
        } elseif ($phone && $secret && $name && $code && !$smsError) {
            if(md5("*****".$code) == $secret){
                //записываем данные в хайлоад-Блок
                $this->addUser($name, $phone);
                $_SESSION["IS_CURRENT_USER_SMS_COUPON"] = true;
                // из инфоблока получаем данные и возвращаем JSON с этими данными
                echo json_encode($this->getCoupon());
            } else {
                http_response_code(403);
            }
        } elseif ($phone && $name && $smsError) {
            $phone = MyTools::FormatPhoneNumber($phone);

            \Bitrix\Main\Mail\Event::send(array(
                "EVENT_NAME" => "SALE_VERIFIED_ERROR_FOR_MANAGER",
                "LID" => "cz",
                "C_FIELDS" => array(
                    "NAME" => htmlspecialchars($name),
                    "PHONE" => $phone
                ),
            ));
        } elseif ($isUserVerified) {
            if ($_SESSION["IS_CURRENT_USER_SMS_COUPON"]) {
                echo json_encode($this->getCoupon());
            } else {
                http_response_code(403);
            }
        }
        else {
            //если пользователь неАвторизирован тогда подключаем шаблон
            global $USER;
            if(!$USER->IsAuthorized()){
                $this->arResult['PERCENT'] = $this->getPercentSale();
                $this->includeComponentTemplate();
            }
        }
    }

    protected function addUser($name, $phone)
    {
        Loader::includeModule("highloadblock");

        $phone = MyTools::FormatPhoneNumber($phone);

        $hlBlock = HighloadBlockTable::getList([
            'select' => ['*'],
            'filter' => ['=NAME' => 'SaleVerifiedUsers']
        ])->fetch();

        $entity = HighloadBlockTable::compileEntity($hlBlock)->getDataClass();

        $existingUser = $entity::getList([
            'select' => ['ID'],
            'filter' => ['=UF_PHONE' => $phone]
        ])->fetch();

        // Поиск пользователя по номеру телефона
        if ($existingUser) {
            // если пользователь существует
            return false;
        } else {
            // Добавление новой записи, если пользователь не существует
            $entity::add([
                'UF_NAME' => $name,
                'UF_PHONE' => $phone,
            ]);
            return true;
        }
    }

    //вернуть массив с данными - картинка, оканчание активности, скидка
    protected function getCoupon() {
        Loader::includeModule('iblock');

        $result = \Bitrix\Iblock\Elements\ElementCouponTable::getList([
            'filter' => ['ACTIVE' => 'Y'],
            'select' => ['ACTIVE_TO', 'DETAIL_PICTURE', 'SALE_PERCENT_' => 'SALE_PERCENT'],
        ]);

        // Получение первого элемента (если он есть)
        if ($element = $result->fetch()) {
            // Получение полей
            $saleValue = $element['SALE_PERCENT_VALUE'];
            $endDate = $element['ACTIVE_TO'];
            $imageId = $element['DETAIL_PICTURE'];

            // Получение пути к файлу изображения
            $imageUrl = \CFile::GetPath($imageId);

            // Собираем данные в массив
            $data = [
                'saleValue' => (int)$saleValue,
                'endDate' => $endDate->format('d.m.Y'),
                'imageUrl' => $imageUrl,
            ];

            // Возвращаем JSON
            return $data;
        } else {
            // Если элемент не найден
            return ['error' => 'Элемент не найден'];
        }
    }

    protected function getPercentSale() {
        Loader::includeModule('iblock');

        $result = \Bitrix\Iblock\Elements\ElementCouponTable::getList([
            'filter' => ['ACTIVE' => 'Y'],
            'select' => ['SALE_PERCENT_' => 'SALE_PERCENT'],
        ]);

        // Получение первого элемента (если он есть)
        if ($element = $result->fetch()) {

            $percent = $element['SALE_PERCENT_VALUE'];
            return (int)$percent;

        } else {
            // Если элемент не найден
            return ['error' => 'Элемент не найден'];
        }
    }

}


//https://catalog.585my.ru/ajax/saleVerifiedUsers.php?PHONE=+79220061146