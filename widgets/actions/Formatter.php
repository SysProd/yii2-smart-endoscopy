<?php
/**
 * Created by PhpStorm.
 * User: ig
 * Date: 24.06.18
 * Time: 10:29
 */

namespace app\widgets\actions;


/**
 * Class Formatter
 * Formatter provides a set of commonly used data formatting methods.
 * @package app\widgets\actions
 */
class Formatter extends \yii\i18n\Formatter
{

    /**
     * date change: 11-05-2016::Igor
     * @param $phone
     * @param bool|true $prefix
     * @return bool|string $phoneFormatter  - возвращает отформатированный номер телефона
     */
    public function asPhoneFormatter ($phone, $prefix = true)
    {
        $phone_clear = $this->asPhoneClear($phone);
        switch (intval(strlen($phone_clear))) {
            case 11: {
                $phone = '' . $phone_clear;
                break;
            }
            case 10: {
                $phone = '8' . $phone_clear;
                break;
            }
            default: {
                return false;
            }
        }
        $n = strval($phone);

        return ($prefix ? '+7' : '') . '(' . $n[1] . $n[2] . $n[3] . ') ' . $n[4] . $n[5] . $n[6] . '-' . $n[7] . $n[8] . '-' . $n[9] . $n[10];
    }

    /**
     * date change: 11-05-2016::Igor
     * @param $phone_raw
     * @param bool|true $prefix
     * @return bool|mixed|string $phoneClear  - очищает номер телефона от всех символов
     */
    public function asPhoneClear ($phone_raw, $prefix = false)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone_raw);
        if (!$prefix && (strlen($phone) > 10)) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }
}