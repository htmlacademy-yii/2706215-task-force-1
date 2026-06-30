<?php

declare(strict_types=1);

class Task
{
    const STATUS_NEW = 'new';
    const STATUS_CANCELED = 'canceled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    const ACTION_CREATE = 'create';
    const ACTION_CANCEL = 'cancel';
    const ACTION_BID = 'bid'; // ?
    const ACTION_ASSIGN = 'assign';
    const ACTION_COMPLETE = 'complete';
    const ACTION_REFUSE = 'refuse';

    const USER_ROLE_CUSTOMER = 'customer';
    const USER_ROLE_EXECUTOR = 'executor';

    private static array $statusMap = [
        self::STATUS_NEW => 'Новое',
        self::STATUS_CANCELED => 'Отменено',
        self::STATUS_IN_PROGRESS => 'В работе',
        self::STATUS_COMPLETED => 'Выполнено',
        self::STATUS_FAILED => 'Провалено',
    ];

    private static array $actionMap = [
        self::ACTION_CREATE => 'Создать',
        self::ACTION_CANCEL => 'Отменить',
        self::ACTION_BID => 'Откликнуться',
        self::ACTION_ASSIGN => 'Назначить',
        self::ACTION_COMPLETE => 'Завершить',
        self::ACTION_REFUSE => 'Отказаться',
    ];

    private static array $actionNextStatusMap = [
        self::ACTION_CREATE => self::STATUS_NEW,
        self::ACTION_CANCEL => self::STATUS_CANCELED,
        //self::ACTION_BID => self::STATUS_NEW,
        self::ACTION_ASSIGN => self::STATUS_IN_PROGRESS,
        self::ACTION_COMPLETE => self::STATUS_COMPLETED,
        self::ACTION_REFUSE => self::STATUS_FAILED,
    ];

    /**
     * @todo Move to appropriate place
     */
    private static array $userRoleMap = [
        self::USER_ROLE_CUSTOMER => 'Заказчик',
        self::USER_ROLE_EXECUTOR => 'Исполнитель',
    ];

    private string $status;
    private int $customerId;
    private ?int $executorId;

    public static function getStatuses(): array
    {
        return self::$statusMap;
    }

    public static function getStatusLabel(string $status): ?string
    {
        return self::$statusMap[$status] ?? null;
    }

    public static function getActions(): array
    {
        return self::$actionMap;
    }

    public static function getActionNextStatus(string $action): ?string
    {
        return self::$actionNextStatusMap[$action] ?? null;
    }

    public function __construct(string $status, int $customerId, ?int $executorId = null)
    {
        if (isset(self::$statusMap[$status]) && $customerId > 0) {
            $this->status = $status;
            $this->customerId = $customerId;
            $this->executorId = $executorId > 0 ? $executorId : null;
        } else {
            throw new InvalidArgumentException('Invalid task data');
        }
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAvailableActions(string $userRole): array
    {
        $availableActions = [];

        if ($this->status === self::STATUS_NEW) {
            if ($userRole === self::USER_ROLE_CUSTOMER) {
                $availableActions[] = self::ACTION_CANCEL;
            } else {
                $availableActions[] = self::ACTION_BID;
            }
        } elseif ($this->status === self::STATUS_IN_PROGRESS) {
            if ($userRole === self::USER_ROLE_CUSTOMER) {
                $availableActions[] = self::ACTION_COMPLETE;
            } else {
                $availableActions[] = self::ACTION_REFUSE;
            }
        }

        return $availableActions;
    }

    public function create(): string|false
    {
        return $this->status = self::STATUS_NEW;
    }

    public function cancel(): string|false
    {
        return $this->status = self::STATUS_CANCELED;
    }

    public function bid(): string|false
    {
        return $this->status;
    }

    public function assign(int $executorId): string|false
    {
        if ($executorId > 0) {
            $this->executorId = $executorId;
            return $this->status = self::STATUS_IN_PROGRESS;
        }

        return $this->executorId === $executorId ? $this->status : false;
    }

    public function complete(): string|false
    {
        return $this->status = self::STATUS_COMPLETED;
    }

    public function refuse(): string|false
    {
        return $this->status = self::STATUS_FAILED;
    }
}
