<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 01.05.17
 * Time: 17:09
 */

namespace app\commands;

use app\modules\security\models\EditContentUsersRule;
use app\modules\security\models\EditUsersRule;
use app\modules\security\models\EditHisProfileRule;

use app\modules\security\models\User;
//use app\modules\shops\models\ShopStuff;
//use app\modules\shops\models\ShopTree;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Class RbacMyRulesController
 * @package app\commands
 *
 * @property string $roleAdmin
 * @property string $roleStaff
 * @property string $roleDevice
 */
class RbacMyRulesController extends Controller
{
    public  $roleAdmin  = 'Администратор',
            $roleStaff  = 'Сотрудник',
            $roleDevice = 'Устройство';

    /**
     * Создание ролей
     */
    public function actionInit()
    {
        Yii::$app->set('request', new \yii\web\Request());

        $auth = Yii::$app->getAuthManager(); // подключаемся к библиотеке
        $auth->removeAll(); // удалить все правила

        /**
         * добавляем разрешение "EditContentRule"
         * Разрешающая вносить изменения автору-сотруднику контента
         */
        $editContentUsersRule = new EditContentUsersRule();
        $auth->add($editContentUsersRule);

        /**
         * добавляем разрешение "EditHisProfileRule"
         * Разрешающая изменять свой профиль пользователя
         */
        $editHisProfile = new EditHisProfileRule();
        $auth->add($editHisProfile);
        /**
         * добавляем разрешение "EditUsersRule"
         * Разрешающая изменять добавленых пользователей
         */
        $editUsersRule= new EditUsersRule();
        $auth->add($editUsersRule);

        /**
         * #### Содаем роли ####
         */
        // ### Администраторы
        $admin = $auth->createRole($this->roleAdmin);
        $admin->description = 'Администраторы системы';
        $auth->add($admin);
        // ### Сотрудники
        $usersAuthor = $auth->createRole($this->roleStaff);
        $usersAuthor->description = 'Пользователи системы "Изменение авторского контента"';
        $auth->add($usersAuthor);
        // ### Оборудование
        $roleDevice = $auth->createRole($this->roleDevice);
        $roleDevice->description = 'Авторизоавнное оборудование';
        $auth->add($roleDevice);
        /** ##### **/

        /**
         * Связи ролей по иерархии
         */
        $auth->addChild($admin, $usersAuthor);
        $auth->addChild($usersAuthor, $roleDevice);
        /** ##### **/
        /*** #### >>> Создаем разрешения и их связи <<< #### ***/
        /** ##### **/

        /*** #### >>> Действия с пользователями <<< #### ***/
        // добавляем разрешение "createUser"
        $createUser = $auth->createPermission('createUser');
        $createUser->description = 'Добавление "Пользователей"';
        $auth->add($createUser);

        // добавляем разрешение "updateUser"
        $updateUser = $auth->createPermission('updateUser');
        $updateUser->description = 'Обновление всех "Пользователей"';
        $auth->add($updateUser);
        // добавляем разрешение "updateHisProfile"
        $updateHisProfile = $auth->createPermission('updateHisProfile');
        $updateHisProfile->description = 'Обновление своего "Профиля"';
        $updateHisProfile->ruleName = $editHisProfile->name;
        $auth->add($updateHisProfile);
        // добавляем разрешение "updateOwnUser"
        $updateOwnUser = $auth->createPermission('updateOwnUser');
        $updateOwnUser->description = 'Обновление добавленных "Пользователей"';
        $updateOwnUser->ruleName = $editUsersRule->name;
        $auth->add($updateOwnUser);

        // добавляем разрешение "deleteUser"
        $deleteUser = $auth->createPermission('deleteUser');
        $deleteUser->description = 'Полное удаление всех "Пользователей"';
        $auth->add($deleteUser);
        // добавляем разрешение "deleteHisProfile"
        $deleteHisProfile = $auth->createPermission('deleteHisProfile');
        $deleteHisProfile->description = 'Удаление своего "Профиля"';
        $deleteHisProfile->ruleName = $editHisProfile->name;
        $auth->add($deleteHisProfile);
        // добавляем разрешение "deleteOwnUser"
        $deleteOwnUser = $auth->createPermission('deleteOwnUser');
        $deleteOwnUser->description = 'Удаление добавленных "Пользователей"';
        $deleteOwnUser->ruleName = $editUsersRule->name;
        $auth->add($deleteOwnUser);

        // регистрация статических данных устройств "addLogDevice"
        $addLogDevice = $auth->createPermission('addLogDevice');
        $addLogDevice->description = 'Регистрация статических данных устройств';
        $auth->add($addLogDevice);

        // >>> связи разрешений с ролями
        $auth->addChild($updateHisProfile,      $updateUser);
        $auth->addChild($updateOwnUser,         $updateUser);

        $auth->addChild($deleteHisProfile,      $deleteUser);
        $auth->addChild($deleteOwnUser,         $deleteUser);

        $auth->addChild($usersAuthor, $updateHisProfile);
        $auth->addChild($usersAuthor, $deleteHisProfile);

        $auth->addChild($roleDevice, $addLogDevice);

        $auth->addChild($admin, $createUser);
        $auth->addChild($admin, $updateUser);
        $auth->addChild($admin, $deleteUser);
        /** #### >>> END:Действия с пользователями <<< #### */

        /*** #### >>> Действия с сотрудниками <<< #### ***/
        // добавляем разрешение "createStaff"
        $createStaff = $auth->createPermission('createStaff');
        $createStaff->description = 'Добавление "Сотрудников" в систему';
        $auth->add($createStaff);

        // добавляем разрешение "updateStaff"
        $updateStaff = $auth->createPermission('updateStaff');
        $updateStaff->description = 'Обновление "Сотрудников" в системе';
        $auth->add($updateStaff);

        // добавляем разрешение "deleteStaff"
        $deleteStaff = $auth->createPermission('deleteStaff');
        $deleteStaff->description = 'Полное удаление всех "Сотрудников"';
        $auth->add($deleteStaff);

        $auth->addChild($admin, $createStaff);
        $auth->addChild($admin, $updateStaff);
        $auth->addChild($admin, $deleteStaff);
        /** #### >>> END:Действия с сотрудниками <<< #### */

        /*** #### >>> Действия с контентами <<< #### ***/
        // добавляем разрешение "createContent"
        $createContent = $auth->createPermission('createContent');
        $createContent->description = 'Добавление "Контентов"';
        $auth->add($createContent);

        // добавляем разрешение "updateContent"
        $updateContent = $auth->createPermission('updateContent');
        $updateContent->description = 'Обновление "Контентов"';
        $auth->add($updateContent);

        // добавляем разрешение "updateOwnContent"
        $updateOwnContent = $auth->createPermission('updateOwnContent');
        $updateOwnContent->description = 'Обновление добавленных "Контентов"';
        $updateOwnContent->ruleName = $editContentUsersRule->name;
        $auth->add($updateOwnContent);

        // добавляем разрешение "deleteContent"
        $deleteContent = $auth->createPermission('deleteContent');
        $deleteContent->description = 'Полное удаление всех "Контентов"';
        $auth->add($deleteContent);
        // добавляем разрешение "deleteOwnContent"
        $deleteOwnContent = $auth->createPermission('deleteOwnContent');
        $deleteOwnContent->description = 'Удаление добавленных "Контентов"';
        $deleteOwnContent->ruleName = $editContentUsersRule->name;
        $auth->add($deleteOwnContent);

        // >>> связи разрешений с ролями
        $auth->addChild($updateOwnContent, $updateContent);
        $auth->addChild($deleteOwnContent, $deleteContent);

        $auth->addChild($usersAuthor, $createContent);
        $auth->addChild($usersAuthor, $updateOwnContent);
        $auth->addChild($usersAuthor, $deleteOwnContent);

        $auth->addChild($admin, $updateContent);
        $auth->addChild($admin, $deleteContent);
        /** #### >>> END:Действия с контентами <<< #### */

        $this->stdout("Выполненно!\n\n", Console::FG_YELLOW);

    }

    /**
     * Тестирование ролей для модуля пользольвателей
     *
     * для запуска:
     * cd /var/www/crg.ru/yii2/
     * clear && php yii rbac-my-rules/test-users
     */
    public function actionTestUsers()
    {

        Yii::$app->set('request', new \yii\web\Request());

        $auth = Yii::$app->getAuthManager();

        $admin  = new User(['id' => 1, 'username' => 'admin']);
        $staff  = new User(['id' => 3, 'username' => 'staff']);
        $device = new User(['id' => 2, 'username' => 'device']);

        /** удалить все правила **/
        $auth->revokeAll($admin->id);
        $auth->revokeAll($device->id);
        $auth->revokeAll($staff->id);

        /** Добавить роли пользователей **/
        $auth->assign($auth->getRole($this->roleAdmin), $admin->id);
        $auth->assign($auth->getRole($this->roleStaff), $staff->id);
        $auth->assign($auth->getRole($this->roleDevice), $device->id);


        $this->stdout("Добавлены роли!\n\n", Console::FG_YELLOW);

        die();

//        $auth->assign($auth->getRole($this->roleStaff), $userWitchRuleGroup->id);

//
//        /**
//         * // ####################################################
//         * // #### Проверка доступа к модулю [new User();]
//         * // ####################################################
//         */
//
//        /*        $userCreate = new User([
//            'id' => 5,
//            'username' => 'ппппп',
////            'created_by' => $admin->id,
//        ]);*/
//
        $userCreate = User::findOne(4);
//
////        $userCreate = $admin;
////        $userCreate = $user;
////        $userCreate = $userNotGroupRule;
////        $userCreate = $userNotRuleWitchGroup;
////        $userCreate = $userNotGroupWitchRule;
////        $userCreate = $userWitchRuleGroup;
//
        $this->stdout("Проверка доступа к модели \"Пользователи\" \n [ username >> {$userCreate->username} №{$userCreate->id}]\n [ Добавил >> \"{$userCreate->created_by}\" ]\n [ Принадлежит Группе Пользователей >> \"{$userCreate->group_by}\" ] \n\n", Console::FG_YELLOW);

        /**
         * // ####################################################
         * // #### $admin
         * // ####################################################
         */
        Yii::$app->user->login($admin);
        $this->stdout("Проверка доступа для:: {$admin->id} >> \"{$admin->username}\" >> Группа::\"{$admin->group_by}\"  >> Роль в системе::\"" . implode(',', ArrayHelper::map($admin->roleNameArray, 'name', 'name')) . "\"\n\n", Console::FG_YELLOW);

        $this->show('Добавление "Пользователя" [createUser]::', Yii::$app->user->can('createUser', ['post' => $userCreate]));
        $this->show('Добавление "Пользователей" в свою группу [createUserHisGroup]::', Yii::$app->user->can('createUserHisGroup', ['post' => $userCreate]));

        $this->show('Обновление всех "Пользователей" [updateUser]::', Yii::$app->user->can('updateUser'));
        $this->show('Обновление "Пользователей" [updateUser] через связи::', Yii::$app->user->can('updateUser', ['post' => $userCreate]));
        $this->show('Обновление своего "Профиля" [updateHisProfile]::', Yii::$app->user->can('updateHisProfile', ['post' => $userCreate]));
        $this->show('Обновление добавленных "Пользователей" [updateOwnUser]::', Yii::$app->user->can('updateOwnUser', ['post' => $userCreate]));
        $this->show('Обновление "Пользователей" своей группы [updateOwnUserHisGroup]::', Yii::$app->user->can('updateOwnUserHisGroup', ['post' => $userCreate]));

        $this->show('Полное удаление всех "Пользователей" [deleteUser]::', Yii::$app->user->can('deleteUser'));
        $this->show('Удаление "Пользователей" [deleteUser] через связи::', Yii::$app->user->can('deleteUser', ['post' => $userCreate]));
        $this->show('Удаление своего "Профиля" [deleteHisProfile]::', Yii::$app->user->can('deleteHisProfile', ['post' => $userCreate]));
        $this->show('Удаление добавленных "Пользователей" [deleteOwnUser]::', Yii::$app->user->can('deleteOwnUser', ['post' => $userCreate]));
        $this->show('Удаление "Пользователей" своей группы [deleteOwnUserHisGroup]::', Yii::$app->user->can('deleteOwnUserHisGroup', ['post' => $userCreate]));

        Yii::$app->user->logout();

        /**
         * // ####################################################
         * // #### $user
         * // ####################################################
         */
        Yii::$app->user->login($user);
        $this->stdout("Проверка доступа для:: {$user->id} >> \"{$user->username}\" >> Группа::\"{$user->group_by}\" >> Роль в системе::\"" . implode(',', ArrayHelper::map($user->roleNameArray, 'name', 'name')) . "\"\n\n", Console::FG_GREEN);

        $this->show('Добавление "Пользователя" [createUser]::', Yii::$app->user->can('createUser', ['post' => $userCreate]));
        $this->show('Добавление "Пользователей" в свою группу [createUserHisGroup]::', Yii::$app->user->can('createUserHisGroup', ['post' => $userCreate]));

        $this->show('Обновление всех "Пользователей" [updateUser]::', Yii::$app->user->can('updateUser'));
        $this->show('Обновление "Пользователей" [updateUser] через связи::', Yii::$app->user->can('updateUser', ['post' => $userCreate]));
        $this->show('Обновление своего "Профиля" [updateHisProfile]::', Yii::$app->user->can('updateHisProfile', ['post' => $userCreate]));
        $this->show('Обновление добавленных "Пользователей" [updateOwnUser]::', Yii::$app->user->can('updateOwnUser', ['post' => $userCreate]));
        $this->show('Обновление "Пользователей" своей группы [updateOwnUserHisGroup]::', Yii::$app->user->can('updateOwnUserHisGroup', ['post' => $userCreate]));

        $this->show('Полное удаление всех "Пользователей" [deleteUser]::', Yii::$app->user->can('deleteUser'));
        $this->show('Удаление "Пользователей" [deleteUser] через связи::', Yii::$app->user->can('deleteUser', ['post' => $userCreate]));
        $this->show('Удаление своего "Профиля" [deleteHisProfile]::', Yii::$app->user->can('deleteHisProfile', ['post' => $userCreate]));
        $this->show('Удаление добавленных "Пользователей" [deleteOwnUser]::', Yii::$app->user->can('deleteOwnUser', ['post' => $userCreate]));
        $this->show('Удаление "Пользователей" своей группы [deleteOwnUserHisGroup]::', Yii::$app->user->can('deleteOwnUserHisGroup', ['post' => $userCreate]));

        Yii::$app->user->logout();

        /**
         * // ####################################################
         * // #### $userWitchRuleGroup
         * // ####################################################
         */
        Yii::$app->user->login($userWitchRuleGroup);
        $this->stdout("Проверка доступа для:: {$userWitchRuleGroup->id} >> \"{$userWitchRuleGroup->username}\" >> Группа::\"{$userWitchRuleGroup->group_by}\" >> Роль в системе::\"" . implode(',', ArrayHelper::map($userWitchRuleGroup->roleNameArray, 'name', 'name')) . "\"\n\n", Console::FG_GREEN);

        $this->show('Добавление "Пользователя" [createUser]::', Yii::$app->user->can('createUser', ['post' => $userCreate]));
        $this->show('Добавление "Пользователей" в свою группу [createUserHisGroup]::', Yii::$app->user->can('createUserHisGroup', ['post' => $userCreate]));

        $this->show('Обновление всех "Пользователей" [updateUser]::', Yii::$app->user->can('updateUser'));
        $this->show('Обновление "Пользователей" [updateUser] через связи::', Yii::$app->user->can('updateUser', ['post' => $userCreate]));
        $this->show('Обновление своего "Профиля" [updateHisProfile]::', Yii::$app->user->can('updateHisProfile', ['post' => $userCreate]));
        $this->show('Обновление добавленных "Пользователей" [updateOwnUser]::', Yii::$app->user->can('updateOwnUser', ['post' => $userCreate]));
        $this->show('Обновление "Пользователей" своей группы [updateOwnUserHisGroup]::', Yii::$app->user->can('updateOwnUserHisGroup', ['post' => $userCreate]));

        $this->show('Полное удаление всех "Пользователей" [deleteUser]::', Yii::$app->user->can('deleteUser'));
        $this->show('Удаление "Пользователей" [deleteUser] через связи::', Yii::$app->user->can('deleteUser', ['post' => $userCreate]));
        $this->show('Удаление своего "Профиля" [deleteHisProfile]::', Yii::$app->user->can('deleteHisProfile', ['post' => $userCreate]));
        $this->show('Удаление добавленных "Пользователей" [deleteOwnUser]::', Yii::$app->user->can('deleteOwnUser', ['post' => $userCreate]));
        $this->show('Удаление "Пользователей" своей группы [deleteOwnUserHisGroup]::', Yii::$app->user->can('deleteOwnUserHisGroup', ['post' => $userCreate]));

        Yii::$app->user->logout();

        /**
         * // ####################################################
         * // #### $userNotRuleWitchGroup
         * // ####################################################
         */
        Yii::$app->user->login($userNotRuleWitchGroup);
        $this->stdout("Проверка доступа для:: {$userNotRuleWitchGroup->id} >> \"{$userNotRuleWitchGroup->username}\" >> Группа::\"{$userNotRuleWitchGroup->group_by}\" >> Роль в системе::\"" . implode(',', ArrayHelper::map($userNotRuleWitchGroup->roleNameArray, 'name', 'name')) . "\"\n\n", Console::FG_GREEN);

        $this->show('Добавление "Пользователя" [createUser]::', Yii::$app->user->can('createUser', ['post' => $userCreate]));
        $this->show('Добавление "Пользователей" в свою группу [createUserHisGroup]::', Yii::$app->user->can('createUserHisGroup', ['post' => $userCreate]));

        $this->show('Обновление всех "Пользователей" [updateUser]::', Yii::$app->user->can('updateUser'));
        $this->show('Обновление "Пользователей" [updateUser] через связи::', Yii::$app->user->can('updateUser', ['post' => $userCreate]));
        $this->show('Обновление своего "Профиля" [updateHisProfile]::', Yii::$app->user->can('updateHisProfile', ['post' => $userCreate]));
        $this->show('Обновление добавленных "Пользователей" [updateOwnUser]::', Yii::$app->user->can('updateOwnUser', ['post' => $userCreate]));
        $this->show('Обновление "Пользователей" своей группы [updateOwnUserHisGroup]::', Yii::$app->user->can('updateOwnUserHisGroup', ['post' => $userCreate]));

        $this->show('Полное удаление всех "Пользователей" [deleteUser]::', Yii::$app->user->can('deleteUser'));
        $this->show('Удаление "Пользователей" [deleteUser] через связи::', Yii::$app->user->can('deleteUser', ['post' => $userCreate]));
        $this->show('Удаление своего "Профиля" [deleteHisProfile]::', Yii::$app->user->can('deleteHisProfile', ['post' => $userCreate]));
        $this->show('Удаление добавленных "Пользователей" [deleteOwnUser]::', Yii::$app->user->can('deleteOwnUser', ['post' => $userCreate]));
        $this->show('Удаление "Пользователей" своей группы [deleteOwnUserHisGroup]::', Yii::$app->user->can('deleteOwnUserHisGroup', ['post' => $userCreate]));

        Yii::$app->user->logout();

        /**
         * // ####################################################
         * // #### $userNotGroupRule
         * // ####################################################
         */
        Yii::$app->user->login($userNotGroupRule);
        $this->stdout("Проверка доступа для:: {$userNotGroupRule->id} >> \"{$userNotGroupRule->username}\" >> Группа::\"{$userNotGroupRule->group_by}\" >> Роль в системе::\"" . implode(',', ArrayHelper::map($userNotGroupRule->roleNameArray, 'name', 'name')) . "\"\n\n", Console::FG_GREY);

        $this->show('Добавление "Пользователя" [createUser]::', Yii::$app->user->can('createUser', ['post' => $userCreate]));
        $this->show('Добавление "Пользователей" в свою группу [createUserHisGroup]::', Yii::$app->user->can('createUserHisGroup', ['post' => $userCreate]));

        $this->show('Обновление всех "Пользователей" [updateUser]::', Yii::$app->user->can('updateUser'));
        $this->show('Обновление "Пользователей" [updateUser] через связи::', Yii::$app->user->can('updateUser', ['post' => $userCreate]));
        $this->show('Обновление своего "Профиля" [updateHisProfile]::', Yii::$app->user->can('updateHisProfile', ['post' => $userCreate]));
        $this->show('Обновление добавленных "Пользователей" [updateOwnUser]::', Yii::$app->user->can('updateOwnUser', ['post' => $userCreate]));
        $this->show('Обновление "Пользователей" своей группы [updateOwnUserHisGroup]::', Yii::$app->user->can('updateOwnUserHisGroup', ['post' => $userCreate]));

        $this->show('Полное удаление всех "Пользователей" [deleteUser]::', Yii::$app->user->can('deleteUser'));
        $this->show('Удаление "Пользователей" [deleteUser] через связи::', Yii::$app->user->can('deleteUser', ['post' => $userCreate]));
        $this->show('Удаление своего "Профиля" [deleteHisProfile]::', Yii::$app->user->can('deleteHisProfile', ['post' => $userCreate]));
        $this->show('Удаление добавленных "Пользователей" [deleteOwnUser]::', Yii::$app->user->can('deleteOwnUser', ['post' => $userCreate]));
        $this->show('Удаление "Пользователей" своей группы [deleteOwnUserHisGroup]::', Yii::$app->user->can('deleteOwnUserHisGroup', ['post' => $userCreate]));

        Yii::$app->user->logout();

        /**
         * // ####################################################
         * // #### $userNotGroupWitchRule
         * // ####################################################
         */
        Yii::$app->user->login($userNotGroupWitchRule);
        $this->stdout("Проверка доступа для:: {$userNotGroupWitchRule->id} >> \"{$userNotGroupWitchRule->username}\" >> Группа::\"{$userNotGroupWitchRule->group_by}\" >> Роль в системе::\"" . implode(',', ArrayHelper::map($userNotGroupWitchRule->roleNameArray, 'name', 'name')) . "\"\n\n", Console::FG_GREY);

        $this->show('Добавление "Пользователя" [createUser]::', Yii::$app->user->can('createUser', ['post' => $userCreate]));
        $this->show('Добавление "Пользователей" в свою группу [createUserHisGroup]::', Yii::$app->user->can('createUserHisGroup', ['post' => $userCreate]));

        $this->show('Обновление всех "Пользователей" [updateUser]::', Yii::$app->user->can('updateUser'));
        $this->show('Обновление "Пользователей" [updateUser] через связи::', Yii::$app->user->can('updateUser', ['post' => $userCreate]));
        $this->show('Обновление своего "Профиля" [updateHisProfile]::', Yii::$app->user->can('updateHisProfile', ['post' => $userCreate]));
        $this->show('Обновление добавленных "Пользователей" [updateOwnUser]::', Yii::$app->user->can('updateOwnUser', ['post' => $userCreate]));
        $this->show('Обновление "Пользователей" своей группы [updateOwnUserHisGroup]::', Yii::$app->user->can('updateOwnUserHisGroup', ['post' => $userCreate]));

        $this->show('Полное удаление всех "Пользователей" [deleteUser]::', Yii::$app->user->can('deleteUser'));
        $this->show('Удаление "Пользователей" [deleteUser] через связи::', Yii::$app->user->can('deleteUser', ['post' => $userCreate]));
        $this->show('Удаление своего "Профиля" [deleteHisProfile]::', Yii::$app->user->can('deleteHisProfile', ['post' => $userCreate]));
        $this->show('Удаление добавленных "Пользователей" [deleteOwnUser]::', Yii::$app->user->can('deleteOwnUser', ['post' => $userCreate]));
        $this->show('Удаление "Пользователей" своей группы [deleteOwnUserHisGroup]::', Yii::$app->user->can('deleteOwnUserHisGroup', ['post' => $userCreate]));

        Yii::$app->user->logout();

    }

    /**
     * Тестирование ролей для модуля магазинов
     *
     * для запуска:
     * cd /var/www/crg.ru/yii2/
     * clear && php yii rbac-my-rules/test-shop-stuff
     */
    public function actionTestShopStuff()
    {
        Yii::$app->set('request', new \yii\web\Request());

        $auth = Yii::$app->getAuthManager();

        $admin                  = new User(['id' => 1, 'username' => 'jog', 'group_by' => 1,]);
        $user                   = new User(['id' => 4, 'username' => 'mmm', 'group_by' => 2,]);
        $userNotGroupRule       = new User(['id' => 2, 'username' => 'userNotGroupRule', 'group_by' => NULL,]);
        $userNotRuleWitchGroup  = new User(['id' => 3, 'username' => 'userNotRuleWitchGroup', 'group_by' => 2,]);
        $userNotGroupWitchRule  = new User(['id' => 5, 'username' => 'userNotGroupWitchRule', 'group_by' => NULL,]);
        $userWitchRuleGroup     = new User(['id' => 6, 'username' => 'userWitchRuleGroup', 'group_by' => 1,]);


        /** удалить все правила **/
        $auth->revokeAll($admin->id);
        $auth->revokeAll($user->id);
        $auth->revokeAll($userNotGroupRule->id);
        $auth->revokeAll($userNotRuleWitchGroup->id);
        $auth->revokeAll($userNotGroupWitchRule->id);
        $auth->revokeAll($userWitchRuleGroup->id);

        $auth->assign($auth->getRole($this->roleAdmin), $admin->id);
        $auth->assign($auth->getRole($this->roleStaff), $userNotGroupWitchRule->id);
//        $auth->assign($auth->getRole($this->roleStaff), $userWitchRuleGroup->id);


        /** #################################################### */

        /**
         * // ####################################################
         * // #### Проверка доступа к модулю [new ShopStuff();]
         * // ####################################################
         */
/*
        $userCreate = $admin;
        $userCreate = $user;
        $userCreate = $userNotGroupRule;
        $userCreate = $userNotRuleWitchGroup;
        $userCreate = $userNotGroupWitchRule;
        $userCreate = $userWitchRuleGroup;
*/
/*
        $shopStuff = new ShopStuff([
                    'id_shop' => 4,
                    'name' => 'Super tuper',
                    'created_by' => $admin->id,
                    ]);
*/

        $shopStuff = ShopStuff::findOne(6);

        $this->stdout("Проверка доступа для \"Контента\" \n [ Контент >> {$shopStuff->name} №{$shopStuff->id_shop}]\n [ Добавил >> \"{$shopStuff->created_by}\" ]\n [ Принадлежит Группе Пользователей >> \"{$shopStuff->userGroupBy->name}\" ] \n\n", Console::FG_YELLOW);

        /**
         * // ####################################################
         * // #### $admin
         * // ####################################################
         */

        Yii::$app->user->login($admin);

        $this->stdout("Проверка доступа для:: {$admin->id} >> \"{$admin->username}\" >> Группа::\"{$admin->group_by} >> Роль в системе::\"".implode(',',ArrayHelper::map($admin->roleNameArray, 'name', 'name'))."\"\n\n", Console::FG_YELLOW);

        $this->show('Добавление "Контентов" [createContent]::', Yii::$app->user->can('createContent'));

        $this->show('Обновление "Контентов" [updateContent]::POST::', Yii::$app->user->can('updateContent'));
        $this->show('Обновление "Контентов" [updateContent]::', Yii::$app->user->can('updateContent', ['post' => $shopStuff]));
        $this->show('Обновление своего "Контента" [updateOwnContent]::', Yii::$app->user->can('updateOwnContent', ['post' => $shopStuff]));
        $this->show('Обновление "Контентов" от группы пользователей [updateOwnContentHisGroup]::', Yii::$app->user->can('updateOwnContentHisGroup', ['post' => $shopStuff]));

        $this->show('Удаление "Контентов" [deleteContent]::', Yii::$app->user->can('deleteContent'));
        $this->show('Удаление "Контентов" [deleteContent]::', Yii::$app->user->can('deleteContent', ['post' => $shopStuff]));
        $this->show('Удаление своего "Контента" [deleteOwnContent]::', Yii::$app->user->can('deleteOwnContent', ['post' => $shopStuff]));
        $this->show('Удаление "Контентов" от группы пользователей [deleteOwnContentHisGroup]::', Yii::$app->user->can('deleteOwnContentHisGroup', ['post' => $shopStuff]));

        Yii::$app->user->logout();

        /**
         * // ####################################################
         * // #### $user
         * // ####################################################
         */
        Yii::$app->user->login($user);

        $this->stdout("Проверка доступа для:: {$user->id} >> \"{$user->username}\" >> Группа::\"{$user->group_by} >> Роль в системе::\"".implode(',',ArrayHelper::map($user->roleNameArray, 'name', 'name'))."\"\n\n", Console::FG_GREEN);

        $this->show('Добавление "Контентов" [createContent]::', Yii::$app->user->can('createContent'));

        $this->show('Обновление "Контентов" [updateContent]::POST::', Yii::$app->user->can('updateContent'));
        $this->show('Обновление "Контентов" [updateContent]::', Yii::$app->user->can('updateContent', ['post' => $shopStuff]));
        $this->show('Обновление своего "Контента" [updateOwnContent]::', Yii::$app->user->can('updateOwnContent', ['post' => $shopStuff]));
        $this->show('Обновление "Контентов" от группы пользователей [updateOwnContentHisGroup]::', Yii::$app->user->can('updateOwnContentHisGroup', ['post' => $shopStuff]));

        $this->show('Удаление "Контентов" [deleteContent]::', Yii::$app->user->can('deleteContent', ['post' => $shopStuff]));
        $this->show('Удаление своего "Контента" [deleteOwnContent]::', Yii::$app->user->can('deleteOwnContent', ['post' => $shopStuff]));
        $this->show('Удаление "Контентов" от группы пользователей [deleteOwnContentHisGroup]::', Yii::$app->user->can('deleteOwnContentHisGroup', ['post' => $shopStuff]));

        Yii::$app->user->logout();

        /**
         * // ####################################################
         * // #### $userWitchRuleGroup
         * // ####################################################
         */
        Yii::$app->user->login($userWitchRuleGroup);

        $this->stdout("Проверка доступа для:: {$userWitchRuleGroup->id} >> \"{$userWitchRuleGroup->username}\" >> Группа::\"{$userWitchRuleGroup->group_by} >> Роль в системе::\"".implode(',',ArrayHelper::map($userWitchRuleGroup->roleNameArray, 'name', 'name'))."\"\n\n", Console::FG_GREEN);

        $this->show('Добавление "Контентов" [createContent]::', Yii::$app->user->can('createContent'));

        $this->show('Обновление "Контентов" [updateContent]::POST::', Yii::$app->user->can('updateContent'));
        $this->show('Обновление "Контентов" [updateContent]::', Yii::$app->user->can('updateContent', ['post' => $shopStuff]));
        $this->show('Обновление своего "Контента" [updateOwnContent]::', Yii::$app->user->can('updateOwnContent', ['post' => $shopStuff]));
        $this->show('Обновление "Контентов" от группы пользователей [updateOwnContentHisGroup]::', Yii::$app->user->can('updateOwnContentHisGroup', ['post' => $shopStuff]));

        $this->show('Удаление "Контентов" [deleteContent]::', Yii::$app->user->can('deleteContent', ['post' => $shopStuff]));
        $this->show('Удаление своего "Контента" [deleteOwnContent]::', Yii::$app->user->can('deleteOwnContent', ['post' => $shopStuff]));
        $this->show('Удаление "Контентов" от группы пользователей [deleteOwnContentHisGroup]::', Yii::$app->user->can('deleteOwnContentHisGroup', ['post' => $shopStuff]));

        Yii::$app->user->logout();

        /**
         * // ####################################################
         * // #### $userNotRuleWitchGroup
         * // ####################################################
         */
        Yii::$app->user->login($userNotRuleWitchGroup);

        $this->stdout("Проверка доступа для:: {$userNotRuleWitchGroup->id} >> \"{$userNotRuleWitchGroup->username}\" >> Группа::\"{$userNotRuleWitchGroup->group_by} >> Роль в системе::\"".implode(',',ArrayHelper::map($userNotRuleWitchGroup->roleNameArray, 'name', 'name'))."\"\n\n", Console::FG_GREEN);

        $this->show('Добавление "Контентов" [createContent]::', Yii::$app->user->can('createContent'));

        $this->show('Обновление "Контентов" [updateContent]::POST::', Yii::$app->user->can('updateContent'));
        $this->show('Обновление "Контентов" [updateContent]::', Yii::$app->user->can('updateContent', ['post' => $shopStuff]));
        $this->show('Обновление своего "Контента" [updateOwnContent]::', Yii::$app->user->can('updateOwnContent', ['post' => $shopStuff]));
        $this->show('Обновление "Контентов" от группы пользователей [updateOwnContentHisGroup]::', Yii::$app->user->can('updateOwnContentHisGroup', ['post' => $shopStuff]));

        $this->show('Удаление "Контентов" [deleteContent]::', Yii::$app->user->can('deleteContent', ['post' => $shopStuff]));
        $this->show('Удаление своего "Контента" [deleteOwnContent]::', Yii::$app->user->can('deleteOwnContent', ['post' => $shopStuff]));
        $this->show('Удаление "Контентов" от группы пользователей [deleteOwnContentHisGroup]::', Yii::$app->user->can('deleteOwnContentHisGroup', ['post' => $shopStuff]));

        Yii::$app->user->logout();

        /**
         * // ####################################################
         * // #### $userNotGroupRule
         * // ####################################################
         */
        Yii::$app->user->login($userNotGroupRule);

        $this->stdout("Проверка доступа для:: {$userNotGroupRule->id} >> \"{$userNotGroupRule->username}\" >> Группа::\"{$userNotGroupRule->group_by} >> Роль в системе::\"".implode(',',ArrayHelper::map($userNotGroupRule->roleNameArray, 'name', 'name'))."\"\n\n", Console::FG_GREY);

        $this->show('Добавление "Контентов" [createContent]::', Yii::$app->user->can('createContent'));

        $this->show('Обновление "Контентов" [updateContent]::POST::', Yii::$app->user->can('updateContent'));
        $this->show('Обновление "Контентов" [updateContent]::', Yii::$app->user->can('updateContent', ['post' => $shopStuff]));
        $this->show('Обновление своего "Контента" [updateOwnContent]::', Yii::$app->user->can('updateOwnContent', ['post' => $shopStuff]));
        $this->show('Обновление "Контентов" от группы пользователей [updateOwnContentHisGroup]::', Yii::$app->user->can('updateOwnContentHisGroup', ['post' => $shopStuff]));

        $this->show('Удаление "Контентов" [deleteContent]::', Yii::$app->user->can('deleteContent', ['post' => $shopStuff]));
        $this->show('Удаление своего "Контента" [deleteOwnContent]::', Yii::$app->user->can('deleteOwnContent', ['post' => $shopStuff]));
        $this->show('Удаление "Контентов" от группы пользователей [deleteOwnContentHisGroup]::', Yii::$app->user->can('deleteOwnContentHisGroup', ['post' => $shopStuff]));

        Yii::$app->user->logout();

        /**
         * // ####################################################
         * // #### $userNotGroupWitchRule
         * // ####################################################
         */
        Yii::$app->user->login($userNotGroupWitchRule);

        $this->stdout("Проверка доступа для:: {$userNotGroupWitchRule->id} >> \"{$userNotGroupWitchRule->username}\" >> Группа::\"{$userNotGroupWitchRule->group_by} >> Роль в системе::\"".implode(',',ArrayHelper::map($userNotGroupWitchRule->roleNameArray, 'name', 'name'))."\"\n\n", Console::FG_GREY);

        $this->show('Добавление "Контентов" [createContent]::', Yii::$app->user->can('createContent'));

        $this->show('Обновление "Контентов" [updateContent]::POST::', Yii::$app->user->can('updateContent'));
        $this->show('Обновление "Контентов" [updateContent]::', Yii::$app->user->can('updateContent', ['post' => $shopStuff]));
        $this->show('Обновление своего "Контента" [updateOwnContent]::', Yii::$app->user->can('updateOwnContent', ['post' => $shopStuff]));
        $this->show('Обновление "Контентов" от группы пользователей [updateOwnContentHisGroup]::', Yii::$app->user->can('updateOwnContentHisGroup', ['post' => $shopStuff]));

        $this->show('Удаление "Контентов" [deleteContent]::', Yii::$app->user->can('deleteContent', ['post' => $shopStuff]));
        $this->show('Удаление своего "Контента" [deleteOwnContent]::', Yii::$app->user->can('deleteOwnContent', ['post' => $shopStuff]));
        $this->show('Удаление "Контентов" от группы пользователей [deleteOwnContentHisGroup]::', Yii::$app->user->can('deleteOwnContentHisGroup', ['post' => $shopStuff]));

        Yii::$app->user->logout();
//        // ####################################################

    }

    /**
     * @param $message
     * @param $value
     * @return string
     */
    private function show($message, $value)
    {
        $result = $value ? 'true' : 'false';
        if($result === 'true'){
            $this->stdout("$message $result\n\n", Console::FG_RED);
        }
        return '';
    }
}