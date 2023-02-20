<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 28.04.17
 * Time: 12:00
 */

namespace app\modules\security\models;
use yii\rbac\Rule;

/**
 * Проверяем created_by на соответствие с пользователем, переданным через параметры
 * Class EditUsersRule
 * @package app\modules\security\models
 */
class   EditUsersRule extends Rule
{
    public $name = 'isEditOnwUsers';

    /**
     * @param string|integer $user the user ID.
     * @param \yii\rbac\Item $item the role or permission that this rule is associated width.
     * @param array $params parameters passed to ManagerInterface::checkAccess().
     * @return boolean a value indicating whether the rule permits the role or permission it is associated with.
     */
    public function execute($user, $item, $params)
    {
        return isset($params['post']) ? $params['post']->created_by == $user : false;
    }
}