<?php

declare(strict_types=1);

namespace app\controllers;

use app\dto\TaskFilterDto;
use app\repositories\CategoryRepository;
use app\repositories\TaskRepository;
use app\requests\TaskCreateRequest;
use app\requests\TaskFilterRequest;
use app\services\FileStorage;
use app\services\TaskService;
use Sanweb\Taskforce\exception\TaskCreateException;
use Yii;
use yii\data\ActiveDataProvider;
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
     * Displays the task list.
     */
    public function actionIndex(): string
    {
        $filterForm = new TaskFilterRequest();
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
        $task = $this->taskRepository->findDetailsById($id);

        if ($task === null) {
            throw new NotFoundHttpException('Задание не найдено.');
        }

        return $this->render('view', [
            'task' => $task,
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

        $path = $this->fileStorage->find($attachment->file_path);

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
        $form = new TaskCreateRequest();

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
}
