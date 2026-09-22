<?php

declare(strict_types=1);

namespace app\controllers;

use Sanweb\Taskforce\services\geocoding\GeocoderInterface;
use Sanweb\Taskforce\services\geocoding\GeocodingException;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Serves address suggestions for authenticated users.
 */
final class GeoController extends AuthorizedController
{
    /**
     * Creates the geocoding controller.
     *
     * @param mixed $id
     * @param mixed $module
     * @param GeocoderInterface $geocoder
     * @param array $config
     */
    public function __construct(
        mixed $id,
        mixed $module,
        private readonly GeocoderInterface $geocoder,
        array $config = [],
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
            'actions' => ['suggestions' => ['get']],
        ];

        return $behaviors;
    }

    /**
     * Returns address, latitude, and longitude for the autocomplete field.
     *
     * @param string $query
     *
     * @return list<array{value: string, latitude: float, longitude: float}>
     */
    public function actionSuggestions(string $query = ''): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = trim($query);

        if (mb_strlen($query) < 3) {
            return [];
        }

        try {
            $suggestions = $this->geocoder->suggest($query);
        } catch (GeocodingException $exception) {
            Yii::warning([
                'message' => $exception->getMessage(),
                'cause' => $exception->getPrevious()?->getMessage(),
            ], __METHOD__);
            Yii::$app->response->statusCode = 502;

            return [];
        }

        return $suggestions;
    }
}
