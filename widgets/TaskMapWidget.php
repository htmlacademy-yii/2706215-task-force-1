<?php

declare(strict_types=1);

namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\web\View;

final class TaskMapWidget extends Widget
{
    public float|string|null $latitude = null;

    public float|string|null $longitude = null;

    public ?string $address = null;

    public function run(): string
    {
        $hasCoordinates = $this->latitude !== null && $this->longitude !== null;
        $apiKey = (string) (Yii::$app->params['yandex']['mapsApiKey'] ?? '');
        $showInteractiveMap = $hasCoordinates && $apiKey !== '';

        if (!$showInteractiveMap && $this->address === null) {
            return '';
        }

        $mapId = $this->getId() . '-map';

        if ($showInteractiveMap) {
            $this->registerMap($mapId, $apiKey);
        }

        return $this->render('task-map', [
            'address' => $this->address,
            'mapId' => $mapId,
            'showInteractiveMap' => $showInteractiveMap,
        ]);
    }

    /**
     * Registers the Yandex Maps API and initializes the map.
     */
    private function registerMap(string $mapId, string $apiKey): void
    {
        $view = $this->getView();
        $view->registerJsFile(
            'https://api-maps.yandex.ru/2.1/?' . http_build_query([
                'apikey' => $apiKey,
                'lang' => 'ru_RU',
            ]),
            ['position' => View::POS_HEAD],
        );

        $coordinates = json_encode(
            [(float) $this->latitude, (float) $this->longitude],
            JSON_THROW_ON_ERROR,
        );
        $encodedMapId = json_encode($mapId, JSON_THROW_ON_ERROR);

        $view->registerJs(<<<JS
            ymaps.ready(function () {
                const coordinates = {$coordinates};
                const map = new ymaps.Map({$encodedMapId}, {
                    center: coordinates,
                    zoom: 15,
                    controls: ['zoomControl']
                });
                map.geoObjects.add(new ymaps.Placemark(coordinates));
            });
            JS, key: $mapId);
    }
}
