<?php

namespace frontend\tests\unit\forms;

use frontend\services\auth\ResetPasswordService;
use Yii;
use frontend\forms\PasswordResetRequestForm;
use common\fixtures\UserFixture as UserFixture;
use common\entities\User;

class PasswordResetRequestFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;


    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class' => UserFixture::className(),
                'dataFile' => codecept_data_dir() . 'user.php'
            ]
        ]);
    }

    public function testSendMessageWithWrongEmailAddress()
    {
        $form = new PasswordResetRequestForm();
        $form->email = 'not-existing-email@example.com';
        expect_not((new ResetPasswordService)->request($form->email));
    }

    public function testNotSendEmailsToInactiveUser()
    {
        $user = $this->tester->grabFixture('user', 1);
        $form = new PasswordResetRequestForm();
        $form->email = $user['email'];
        expect_not((new ResetPasswordService)->request($form->email));
    }

    public function testSendEmailSuccessfully()
    {
        $userFixture = $this->tester->grabFixture('user', 0);

        $form = new PasswordResetRequestForm();
        $form->email = $userFixture['email'];
        $user = User::findOne(['password_reset_token' => $userFixture['password_reset_token']]);

        expect_that((new ResetPasswordService)->request($form->email));
        expect_that($user->password_reset_token);

        $emailMessage = $this->tester->grabLastSentEmail();
        expect('valid email is sent', $emailMessage)->isInstanceOf('yii\mail\MessageInterface');
        expect($emailMessage->getTo())->hasKey($form->email);
        expect($emailMessage->getFrom())->hasKey(Yii::$app->params['supportEmail']);
    }
}
