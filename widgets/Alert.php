<?php

declare(strict_types=1);

namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * Renders supported session flash messages as dismissible alerts.
 */
class Alert extends Widget
{
    /**
     * @var array<string, string> Flash message types mapped to their CSS classes.
     */
    public array $alertTypes = [
        'error'   => 'alert-danger',
        'danger'  => 'alert-danger',
        'success' => 'alert-success',
        'info'    => 'alert-info',
        'warning' => 'alert-warning'
    ];

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function run(): void
    {
        $session = Yii::$app->session;

        if (!$session->getIsActive() && !$session->getHasSessionId()) {
            return;
        }

        foreach (array_keys($this->alertTypes) as $type) {
            foreach ((array) $session->getFlash($type) as $message) {
                echo Html::tag('div', Html::button('×', [
                    'class' => 'alert-close',
                    'type' => 'button',
                    'aria-label' => 'Закрыть',
                    'onclick' => 'this.parentElement.remove()',
                ]) . (string) $message, [
                    'class' => 'alert ' . $this->alertTypes[$type],
                    'role' => 'alert',
                ]);
            }

            $session->removeFlash($type);
        }
    }
}
