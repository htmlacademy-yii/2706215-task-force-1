<?php

declare(strict_types=1);

namespace app\controllers;

use app\forms\BidCreateForm;
use app\forms\TaskCompleteForm;
use app\forms\TaskCreateForm;
use app\forms\TaskFilterForm;
use app\models\Task;
use Sanweb\Taskforce\dto\TaskFilterDto;
use Sanweb\Taskforce\enum\StorageArea;
use Sanweb\Taskforce\enum\TaskAction;
use Sanweb\Taskforce\exception\EntityNotFoundException;
use Sanweb\Taskforce\exception\TaskActionException;
use Sanweb\Taskforce\exception\TaskCreateException;
use Sanweb\Taskforce\repositories\CategoryRepository;
use Sanweb\Taskforce\repositories\TaskRepository;
use Sanweb\Taskforce\services\FileStorage;
use Sanweb\Taskforce\services\TaskService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Handles task listing, creation, details, and workflow actions.
 */
class TaskController extends AuthorizedController
{
    /**
     * {@inheritdoc}
     *
     * @param mixed $id
     * @param mixed $module
     * @param TaskRepository $taskRepository
     * @param CategoryRepository $categoryRepository
     * @param TaskService $taskService
     * @param FileStorage $fileStorage
     * @param array $config
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
     *
     * @return array
     */
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'create-bid' => ['post'],
                'accept-bid' => ['post'],
                'reject-bid' => ['post'],
                'complete' => ['post'],
                'refuse' => ['post'],
                'cancel' => ['post'],
            ],
        ];

        return $behaviors;
    }

    /**
     * Displays the task list.
     *
     * @return string
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
     * @param int $id
     *
     * @return string
     *
     * @throws NotFoundHttpException
     */
    public function actionView(int $id): string
    {
        return $this->renderTaskDetails($this->findTaskDetails($id));
    }

    /**
     * Downloads a task attachment.
     * Any authenticated user can download an attachment due to AuthorizedController.
     *
     * @param int $id
     *
     * @return Response
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
     * @return Response|string
     *
     * @throws ForbiddenHttpException
     * @throws TaskCreateException
     */
    public function actionCreate(): Response|string
    {
        $user = $this->getCurrentUser();

        if ((bool) $user->is_executor) {
            throw new ForbiddenHttpException('Создавать задания могут только заказчики.');
        }

        $form = new TaskCreateForm();

        if ($this->request->isPost) {
            $form->load($this->request->post());
            $form->files = UploadedFile::getInstances($form, 'files');

            if ($form->validate()) {
                $task = $this->taskService->create(
                    $form->toDto(),
                    $user->id,
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
     * @param int $id
     *
     * @return Response
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionAcceptBid(int $id): Response
    {
        try {
            $taskId = $this->taskService->acceptBid($id, $this->getCurrentUser());
        } catch (EntityNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), 0, $exception);
        } catch (TaskActionException $exception) {
            throw new ForbiddenHttpException($exception->getMessage(), 0, $exception);
        }

        return $this->redirect(['task/view', 'id' => $taskId]);
    }

    /**
     * Rejects a bid and redirects to its task.
     *
     * @param int $id
     *
     * @return Response
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionRejectBid(int $id): Response
    {
        try {
            $taskId = $this->taskService->rejectBid($id, $this->getCurrentUser());
        } catch (EntityNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), 0, $exception);
        } catch (TaskActionException $exception) {
            throw new ForbiddenHttpException($exception->getMessage(), 0, $exception);
        }

        return $this->redirect(['task/view', 'id' => $taskId]);
    }

    /**
     * Creates a bid for a task.
     *
     * @param int $id
     *
     * @return Response|string
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionCreateBid(int $id): Response|string
    {
        $user = $this->getCurrentUser();
        $form = new BidCreateForm();
        $form->load($this->request->post());

        if (!$form->validate()) {
            $task = $this->findTaskDetails($id);

            if (!$this->taskService->isActionAvailable(TaskAction::Bid, $task, $user)) {
                throw new ForbiddenHttpException(
                    'Действие недоступно для текущего пользователя или статуса задания.',
                );
            }

            return $this->renderTaskDetails(
                $task,
                bidForm: $form,
                activeModal: 'act_response',
            );
        }

        try {
            $taskId = $this->taskService->createBid(
                $id,
                $user,
                (int) $form->price,
                $form->comment,
            );
        } catch (EntityNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), 0, $exception);
        } catch (TaskActionException $exception) {
            throw new ForbiddenHttpException($exception->getMessage(), 0, $exception);
        }

        return $this->redirect(['task/view', 'id' => $taskId]);
    }

    /**
     * Completes a task and creates its review.
     *
     * @param int $id
     *
     * @return Response|string
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionComplete(int $id): Response|string
    {
        $user = $this->getCurrentUser();
        $form = new TaskCompleteForm();
        $form->load($this->request->post());

        if (!$form->validate()) {
            $task = $this->findTaskDetails($id);

            if (!$this->taskService->isActionAvailable(TaskAction::Complete, $task, $user)) {
                throw new ForbiddenHttpException(
                    'Действие недоступно для текущего пользователя или статуса задания.',
                );
            }

            return $this->renderTaskDetails(
                $task,
                completeForm: $form,
                activeModal: 'completion',
            );
        }

        try {
            $taskId = $this->taskService->complete(
                $id,
                $user,
                (int) $form->score,
                $form->comment,
            );
        } catch (EntityNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), 0, $exception);
        } catch (TaskActionException $exception) {
            throw new ForbiddenHttpException($exception->getMessage(), 0, $exception);
        }

        return $this->redirect(['task/view', 'id' => $taskId]);
    }

    /**
     * Marks a task as failed after its executor refuses it.
     *
     * @param int $id
     *
     * @return Response
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionRefuse(int $id): Response
    {
        try {
            $taskId = $this->taskService->refuse($id, $this->getCurrentUser());
        } catch (EntityNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), 0, $exception);
        } catch (TaskActionException $exception) {
            throw new ForbiddenHttpException($exception->getMessage(), 0, $exception);
        }

        return $this->redirect(['task/view', 'id' => $taskId]);
    }

    /**
     * Cancels a new task by its customer.
     *
     * @param int $id
     *
     * @return Response
     *
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionCancel(int $id): Response
    {
        try {
            $taskId = $this->taskService->cancel($id, $this->getCurrentUser());
        } catch (EntityNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), 0, $exception);
        } catch (TaskActionException $exception) {
            throw new ForbiddenHttpException($exception->getMessage(), 0, $exception);
        }

        return $this->redirect(['task/view', 'id' => $taskId]);
    }

    /**
     * Finds task details visible to the current user.
     *
     * @param int $id
     *
     * @return Task
     *
     * @throws NotFoundHttpException
     */
    private function findTaskDetails(int $id): Task
    {
        $task = $this->taskRepository->findDetailsById($id, (int) Yii::$app->user->id);

        if ($task === null) {
            throw new NotFoundHttpException('Задание не найдено.');
        }

        return $task;
    }

    /**
     * Renders task details and its action forms.
     *
     * @param Task $task
     * @param ?BidCreateForm $bidForm
     * @param ?TaskCompleteForm $completeForm
     * @param ?string $activeModal
     *
     * @return string
     */
    private function renderTaskDetails(
        Task $task,
        ?BidCreateForm $bidForm = null,
        ?TaskCompleteForm $completeForm = null,
        ?string $activeModal = null,
    ): string {
        $user = $this->getCurrentUser();

        return $this->render('view', [
            'task' => $task,
            'isCustomer' => $task->customer_id === $user->id,
            'availableActions' => $this->taskService->getAvailableActions($task, $user),
            'bidForm' => $bidForm ?? new BidCreateForm(),
            'completeForm' => $completeForm ?? new TaskCompleteForm(),
            'activeModal' => $activeModal,
        ]);
    }
}
