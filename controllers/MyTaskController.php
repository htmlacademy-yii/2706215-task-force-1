<?php

declare(strict_types=1);

namespace app\controllers;

use Sanweb\Taskforce\enum\MyTaskFilter;
use Sanweb\Taskforce\repositories\TaskRepository;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * Handles the authenticated user's task list.
 */
class MyTaskController extends AuthorizedController
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        mixed $id,
        mixed $module,
        private readonly TaskRepository $taskRepository,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * Displays tasks created by or bid on by the current user.
     */
    public function actionIndex(): string
    {
        $user = $this->getCurrentUser();
        $isExecutor = (bool) $user->is_executor;
        $requestedFilter = $this->request->get('filter');
        $filter = MyTaskFilter::fromRequest(
            is_string($requestedFilter) ? $requestedFilter : null,
            $isExecutor,
        );

        $dataProvider = new ActiveDataProvider([
            'query' => $this->taskRepository->findMyTasksQuery(
                $user->id,
                $isExecutor,
                $filter,
            ),
            'pagination' => [
                'pageSize' => (int) (Yii::$app->params['pagination']['tasksPageSize'] ?? 5),
                'pageSizeLimit' => false,
            ],
            'sort' => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'activeFilter' => $filter,
            'filters' => MyTaskFilter::availableFor($isExecutor),
        ]);
    }
}
