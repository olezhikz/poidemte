<?php
/*
 * LiveStreet CMS
 * Copyright © 2013 OOO "ЛС-СОФТ"
 *
 * ------------------------------------------------------
 *
 * Official site: www.livestreetcms.com
 * Contact e-mail: office@livestreetcms.com
 *
 * GNU General Public License, version 2:
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * ------------------------------------------------------
 *
 * @link http://www.livestreetcms.com
 * @copyright 2013 OOO "ЛС-СОФТ"
 * @author Maxim Mzhelskiy <rus.engine@gmail.com>
 *
 */

/**
 * Обрабатывает авторизацию/регистрацию
 *
 * @package actions
 * @since 2.0
 */
class ActionAuth extends Action
{
    /**
     * Инициализация
     */
    public function Init()
    {
        /**
         * Устанавливаем дефолтный евент
         */
        $this->SetDefaultEvent('login');
        /**
         * Отключаем отображение статистики выполнения
         */
        Router::SetIsShowStats(false);
    }

    /**
     * Регистрация евентов
     */
    protected function RegisterEvent()
    {
        $this->AddEvent('login', 'EventLogin');
        $this->AddEvent('logout', 'EventLogout');
        $this->AddEvent('password-reset', 'EventPasswordReset');
        $this->AddEvent('reactivation', 'EventReactivation');
        $this->AddEvent('register', 'EventRegister');
        $this->AddEvent('register-confirm', 'EventRegisterConfirm');
        $this->AddEvent('activate', 'EventActivate');


        $this->AddEvent('ajax-login', 'EventAjaxLogin');
        $this->AddEvent('ajax-password-reset', 'EventAjaxPasswordReset');
        $this->AddEvent('ajax-reactivation', 'EventAjaxReactivation');
        $this->AddEvent('ajax-validate-fields', 'EventAjaxValidateFields');
        $this->AddEvent('ajax-validate-email', 'EventAjaxValidateEmail');
        $this->AddEvent('ajax-validate-login', 'EventAjaxValidateLogin');
        $this->AddEvent('ajax-register', 'EventAjaxRegister');
    }


    /**********************************************************************************
     ************************ РЕАЛИЗАЦИЯ ЭКШЕНА ***************************************
     **********************************************************************************
     */

    /**
     * Ajax авторизация
     */
    protected function EventAjaxLogin()
    {
        /**
         * Устанвливаем формат Ajax ответа
         */
        $this->setResponseType(self::RESPONSE_TYPE_JSON);
        
        $validator = Engine::GetEntity("User_ValidateLogin");
        $validator->_setValidateScenario('login');
                
        $validator->_setDataSafe($this->getRequest('data'));
        
        if ($validator->_Validate()) 
        {
            $bRemember = getRequest('remember', false) ? true : false;
            /**
             * Авторизуем
             */
            $this->User_Authorization($validator->getUser(), $bRemember);

            /**
             * Определяем редирект
             */
            $this->assign('sUrlRedirect',  $validator->getUser()->getProfileUrl());
        } else {
            /**
             * Получаем ошибки
             */
            $this->assign('errors', $validator->_getValidateErrors());
            return;
        }
    }

    /**
     * Повторный запрос активации
     */
    protected function EventReactivation()
    {
        if ($this->User_IsAuthorization()) {
            Router::LocationAction('/');
        }

        $this->Viewer_AddHtmlTitle($this->Lang_Get('auth.reactivation.title'));
    }

    /**
     *  Ajax повторной активации
     */
    protected function EventAjaxReactivation()
    {
        $this->setResponseType(self::RESPONSE_TYPE_JSON);
        
        $validator = Engine::GetEntity("User_ValidateReactivation");
        $validator->_setValidateScenario('reactivation');
                
        $validator->_setDataSafe($this->getRequest('data'));
        
        if ($validator->_Validate()) 
        {
            $user = $validator->getUser();
            if ($user->getActivate()) {
                $this->Message_AddErrorSingle($this->Lang_Get('auth.registration.notices.error_reactivate'));
                return;
            } else {
                $user->setActivateKey(func_generator(32));
                if ($user->Update()) {
                    $this->Message_AddNotice($this->Lang_Get('auth.reactivation.notices.success'));
                    $this->User_SendNotifyReactivationCode($user);
                    return;
                }
            }
        } else {
            /**
             * Получаем ошибки
             */
            $this->assign('errors', $validator->_getValidateErrors());
            return;
        }

    }

    /**
     * Обрабатываем процесс залогинивания
     * По факту только отображение шаблона, дальше вступает в дело Ajax
     *
     */
    protected function EventLogin()
    {
        /**
         * Если уже авторизирован
         */
        if ($this->User_IsAuthorization()) {
            Router::LocationAction('/');
        }
        $this->Viewer_AddHtmlTitle($this->Lang_Get('auth.login.title'));
    }

    /**
     * Обрабатываем процесс разлогинивания
     *
     */
    protected function EventLogout()
    {
        $this->Security_ValidateSendForm();
        if ($this->User_GetUserCurrent()) {
            $this->User_Logout();
        }
        Router::LocationAction('/');
    }

    /**
     * Ajax запрос на восстановление пароля
     */
    protected function EventAjaxPasswordReset()
    {
        /**
         * Устанвливаем формат Ajax ответа
         */
        $this->setResponseType(self::RESPONSE_TYPE_JSON);
        /**
         * Создаем объект пользователя и устанавливаем сценарий валидации
         */
        $validator = Engine::GetEntity('User_ValidatePasswordReset');
        $validator->_setValidateScenario('reset');
        /**
         * Заполняем поля (данные)
         */
        $validator->_setDataSafe($this->getRequest('data'));
                
        if ($validator->_Validate()) {
            /**
             * Удаляем старые записи
             */
            $aReminderItems = $this->User_GetReminderItemsByUserId($validator->getUser()->getId());
            foreach ($aReminderItems as $oReminder) {
                $oReminder->Delete();
            }
            /**
             * Формируем и отправляем ссылку на смену пароля
             */
            $oReminder = Engine::GetEntity('User_Reminder');
            $oReminder->setCode(func_generator(32));
            $oReminder->setDateExpire(date("Y-m-d H:i:s", time() + 60 * 60 * 24 * 7));
            $oReminder->setDateUsed(null);
            $oReminder->setIsUsed(0);
            $oReminder->setUserId($validator->getUser()->getId());
            if ($oReminder->Add()) {
                $this->Message_AddNotice($this->Lang_Get('auth.reset.notices.success_send_link'));
                $this->User_SendNotifyReminderCode($validator->getUser(), $oReminder);
                return;
            }
        }else{
            /**
             * Получаем ошибки
             */
            $this->assign('errors', $validator->_getValidateErrors());
        }        
    }

    /**
     * Обработка напоминания пароля, подтверждение смены пароля
     *
     */
    protected function EventPasswordReset()
    {
        if ($this->User_IsAuthorization()) {
            Router::LocationAction('/');
        }
        $this->SetTemplateAction('reset');
        /**
         * Устанавливаем title страницы
         */
        $this->Viewer_AddHtmlTitle($this->Lang_Get('auth.reset.title'));
        /**
         * Проверка кода на восстановление пароля и генерация нового пароля
         */
        if (func_check($this->GetParam(0), 'md5')) {
            /**
             * Проверка кода подтверждения
             */
            if ($oReminder = $this->User_GetReminderByCode($this->GetParam(0))) {
                if (!$oReminder->getIsUsed() and strtotime($oReminder->getDateExpire()) > time() and $oUser = $oReminder->getUser()) {
                    $sNewPassword = func_generator(7);
                    $oUser->setPassword(func_encrypt($sNewPassword));
                    if ($oUser->Update()) {
                        $oReminder->setDateUsed(date("Y-m-d H:i:s"));
                        $oReminder->setIsUsed(1);
                        $oReminder->Update();
                        $this->SetTemplateAction('reset_confirm');
                        $this->User_SendNotifyReminderPassword($oUser, $sNewPassword);
                        
                        return;
                    }
                }
            }
            $this->Message_AddErrorSingle($this->Lang_Get('auth.reset.alerts.error_bad_code'),
                $this->Lang_Get('common.error.error'));
            return Router::Action('error');
        }
    }

    /**
     * Ajax валидация форму регистрации
     */
    protected function EventAjaxValidateFields()
    {
        $this->ValidateFields(getRequest('fields'));
    }

    /**
     * Ajax валидация емэйла
     */
    protected function EventAjaxValidateEmail()
    {
        $this->ValidateFields(array(array('field' => 'mail', 'value' => getRequest('mail'))));
    }
    
    /**
     * Ajax валидация емэйла
     */
    protected function EventAjaxValidateLogin()
    {
        $this->ValidateFields(array(array('field' => 'login', 'value' => getRequest('login'))));
    }
    
    
    /**
     * Ajax валидация форму регистрации
     */
    protected function ValidateFields($aFields)
    {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->setResponseType('json');
        /**
         * Создаем объект пользователя и устанавливаем сценарий валидации
         */
        $oUser = Engine::GetEntity('ModuleUser_EntityUser');
        $oUser->_setValidateScenario('registration');
        /**
         * Пробегаем по переданным полям/значениям и валидируем их каждое в отдельности
         */
        if (is_array($aFields)) {
            foreach ($aFields as $aField) {
                if (isset($aField['field']) and isset($aField['value'])) {
                    $this->Hook_Run('registration_validate_field', array('aField' => &$aField, 'oUser' => $oUser));

                    $sField = $aField['field'];
                    $sValue = $aField['value'];
                    /**
                     * Список полей для валидации
                     */
                    switch ($sField) {
                        case 'mail':
                            $oUser->setMail($sValue);
                            break;
                        case 'name':
                            $oUser->setName($sValue);
                            break;
                        case 'login':
                            $oUser->setLogin($sValue);
                            break;
                        case 'captcha':
                            $oUser->setCaptcha($sValue);
                            break;
                        case 'password':
                            $oUser->setPassword($sValue);
                            break;
                        case 'password_confirm':
                            $oUser->setPasswordConfirm($sValue);
                            $oUser->setPassword(isset($aField['params']['password']) ? $aField['params']['password'] : null);
                            break;
                        
                    }
                    /**
                     * Валидируем поле
                     */
                    $oUser->_Validate(array($sField), false);
                }
            }
        }
        /**
         * Возникли ошибки?
         */
        if ($oUser->_hasValidateErrors()) {
            /**
             * Получаем ошибки
             */
            $this->assign('errors', $oUser->_getValidateErrors());
        }
    }

    /**
     * Обработка Ajax регистрации
     */
    protected function EventAjaxRegister()
    {
        /**
         * Устанавливаем формат Ajax ответа
         */
        $this->setResponseType(self::RESPONSE_TYPE_JSON);
        /**
         * Создаем объект пользователя и устанавливаем сценарий валидации
         */
        $oUser = Engine::GetEntity('ModuleUser_EntityUser');
        $oUser->_setValidateScenario('registration');
        /**
         * Заполняем поля (данные)
         */
        $oUser->_setDataSafe($this->getRequest('data'));
                
        $this->Hook_Run('registration_validate_before', array('oUser' => $oUser));
        /**
         * Запускаем валидацию
         */
        if ($oUser->_Validate()) {
            $this->Hook_Run('registration_validate_after', array('oUser' => $oUser));
            
            $oUser->setPassword($this->User_MakeHashPassword($oUser->getPassword()));
            $oUser->setDateRegister(date("Y-m-d H:i:s"));
            $oUser->setIpRegister(func_getIp());
            $oUser->setActivate(0);
            $oUser->setActivateKey(md5(func_generator() . time()));  
            
            if ($oUser->Add()) {
                $this->Hook_Run('registration_after', array('oUser' => $oUser));
                /**
                 * Отправляем на мыло письмо о подтверждении регистрации
                 */
                $this->User_SendNotifyRegistrationActivate($oUser, $this->getRequest('password'));
                /*
                 * Перенаправление на подтверждение
                 */
                $this->assign('sUrlRedirect', Router::GetPath('auth/register-confirm'));
            } else {
                $this->Message_AddErrorSingle($this->Lang_Get('common.error.system.base'));
                return;
            }
        } else {
            /**
             * Получаем ошибки
             */
            $this->assign('errors', $oUser->_getValidateErrors());
        }
    }

    /**
     * Показывает страничку регистрации
     * Просто вывод шаблона
     */
    protected function EventRegister()
    {
        if ($this->User_IsAuthorization()) {
            Router::LocationAction('/');
        }
    }

    /**
     * Обрабатывает активацию аккаунта
     */
    protected function EventActivate()
    {
        if ($this->User_IsAuthorization()) {
            Router::LocationAction('/');
        }
        $bError = false;
        /**
         * Проверяет передан ли код активации
         */
        $sActivateKey = $this->GetParam(0);
        if (!func_check($sActivateKey, 'md5')) {
            $bError = true;
        }
        /**
         * Проверяет верный ли код активации
         */
        if (!($oUser = $this->User_GetUserByActivateKey($sActivateKey))) {
            $bError = true;
        }
        /**
         *
         */
        if ($oUser and $oUser->getActivate()) {
            $this->Message_AddErrorSingle($this->Lang_Get('auth.registration.notices.error_reactivate'),
                $this->Lang_Get('common.error.error'));
            return Router::Action('error');
        }
        /**
         * Если что то не то
         */
        if ($bError) {
            $this->Message_AddErrorSingle($this->Lang_Get('auth.registration.notices.error_code'),
                $this->Lang_Get('common.error.error'));
            return Router::Action('error');
        }
        /**
         * Активируем
         */
        $oUser->setActivate(1);
        $oUser->setDateActivate(date("Y-m-d H:i:s"));
        /**
         * Сохраняем юзера
         */
        if ($oUser->Update()) {
            $this->User_Authorization($oUser, true);
            $this->Message_AddNotice($this->Lang_Get('auth.activation.notice.success'),null,true);
            Router::Location($oUser->getProfileUrl());
        } else {
            $this->Message_AddErrorSingle($this->Lang_Get('common.error.system.base'));
            return Router::Action('error');
        }
    }

    /**
     * Просто выводит шаблон для подтверждения регистрации
     *
     */
    protected function EventRegisterConfirm()
    {
        $this->SetTemplateAction('confirm');
    }
}