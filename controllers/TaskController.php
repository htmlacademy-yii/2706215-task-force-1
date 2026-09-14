<?php

declare(strict_types=1);

namespace app\controllers;

use app\dto\TaskFilterDto;
use app\forms\TaskCreateForm;
use app\forms\TaskFilterForm;
use app\repositories\CategoryRepository;
use app\repositories\TaskRepository;
use app\services\FileStorage;
use app\services\TaskService;
use Sanweb\Taskforce\enum\StorageArea;
use Sanweb\Taskforce\exception\TaskActionException;
use Sanweb\Taskforce\exception\TaskCreateException;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class TaskController extends AuthorizedController
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        mixed $id,
        mixed $module,
        private readonly TaskRepository $taskRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly TaskService $taskService,
        private readonly FileStorage $fileStorage,
        array $config = []
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'accept-bid' => ['post'],
                'reject-bid' => ['post'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Displays the task list.
     */
    public function actionIndex(): string
    {
        $filterForm = new TaskFilterForm();
        $filterForm->load(Yii::$app->request->queryParams);

        $filter = new TaskFilterDto();

        if ($filterForm->validate()) {
            $filter = $filterForm->toDto();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $this->taskRepository->findNewQuery($filter),
            'pagination' => [
                'pageSize' => (int) (Yii::$app->params['pagination']['tasksPageSize'] ?? 5),
                'pageSizeLimit' => false,
            ],
        ]);

        return $this->render('index', [
            'tasks' => $dataProvider->models,
            'pagination' => $dataProvider->pagination,
            'categories' => $this->categoryRepository->findAll(),
            'filterForm' => $filterForm,
        ]);
    }

    /**
     * Displays a single task.
     *
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        $currentUserId = (int) Yii::$app->user->id;
        $task = $this->taskRepository->findDetailsById($id, $currentUserId);

        if ($task === null) {
            throw new NotFoundHttpException('Задание не найдено.');
        }

        return $this->render('view', [
            'task' => $task,
            'isCustomer' => $task->customer_id === $currentUserId,
        ]);
    }

    /**
     * Downloads a task attachment.
     * Any authenticated user can download an attachment due to AuthorizedController.
     *
     * @throws NotFoundHttpException
     */
    public function actionDownload(int $id): Response
    {
        $attachment = $this->taskRepository->findAttachmentById($id);

        if ($attachment === null) {
            throw new NotFoundHttpException('Файл не найден.');
        }

        $path = $this->fileStorage->find(
            StorageArea::TaskAttachments,
            $attachment->file_path,
        );

        if ($path === null) {
            throw new NotFoundHttpException('Файл не найден.');
        }

        $options = $attachment->mime_type === null
            ? []
            : ['mimeType' => $attachment->mime_type];

        return $this->response->sendFile($path, $attachment->original_name, $options);
    }

    /**
     * Creates a new task and redirects to its details page.
     *
     * @throws TaskCreateException
     */
    public function actionCreate(): Response|string
    {
        $form = new TaskCreateForm();

        if ($this->request->isPost) {
            $form->load($this->request->post());
            $form->files = UploadedFile::getInstances($form, 'files');

            if ($form->validate()) {
                $task = $this->taskService->create(
                    $form->toDto(),
                    (int) Yii::$app->user->id,
                    $form->files,
                );

                return $this->redirect(['task/view', 'id' => $task->id]);
            }
        }

        return $this->render('create', [
            'model' => $form,
            'categories' => $this->categoryRepository->findAllForSelect(),
        ]);
    }

    /**
     * Accepts a bid and redirects to its task.
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionAcceptBid(int $id): Response
    {
        $bid = $this->taskRepository->findBidById($id);

        if ($bid === null) {
            throw new NotFoundHttpException('Отклик не найден.');
        }

        try {
            $this->taskService->acceptBid($bid, (int) Yii::$app->user->id);
        } catch (TaskActionException $exception) {
            throw new ForbiddenHttpException($exception->getMessage(), 0, $exception);
        }

        return $this->redirect(['task/view', 'id' => $bid->task_id]);
    }

    /**
     * Rejects a bid and redirects to its task.
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionRejectBid(int $id): Response
    {
        $bid = $this->taskRepository->findBidById($id);

        if ($bid === null) {
            throw new NotFoundHttpException('Отклик не найден.');
        }

        try {
            $this->taskService->rejectBid($bid, (int) Yii::$app->user->id);
        } catch (TaskActionException $exception) {
            throw new ForbiddenHttpException($exception->getMessage(), 0, $exception);
        }

        return $this->redirect(['task/view', 'id' => $bid->task_id]);
    }
}
