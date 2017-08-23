<?php

namespace frontend\services\auth;


use common\entities\User;
use frontend\forms\PasswordResetRequestForm;
use frontend\forms\ResetPasswordForm;
use Yii;

class ResetPasswordService
{
    public function reset(string $token, ResetPasswordForm $form)
    {
        $user = User::findByPasswordResetToken($token);
        if (!$user) {
            throw new \DomainException('User not find.');
        }

        $user->setPassword($form->password);
        $user->removePasswordResetToken();
        
        if(!$user->validate())
        {
            throw new \RuntimeException('Saving error.');
        }
    }
    
    public function request(PasswordResetRequestForm $form) : void
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $form->email,
        ]);

        if (!$user) {
            throw new \DomainException('User not find.');
        }

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save()) {
                return false;
            }
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($form->email)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();
    }

    public function validateToken($token)
    {
        if (empty($token) || !is_string($token)) {
            throw new \DomainException('Password reset token cannot be blank.');
        }
        
        if(!User::findByPasswordResetToken($token)){
            throw new \DomainException('Wrong password reset token.');
        }
    }
}