<?php

namespace EvgRudakov\StoringFiltersGridView;

use yii\grid\GridView;
use yii\helpers\Html;


/**
 * Class StoringQueryParamsGridView
 * @package StoringFiltersGridView
 */
class StoringQueryParamsGridView extends GridView
{
    /**
     * Session key query params
     */
    const SESSION_KEY_QUERY_PARAMS = 'queryParams';

    /**
     * key for $_GET request for reset filters
     */
    const GET_REQUEST_RESET_FILTER_PARAM = 'resetQueryParams';
    /**
     *
     * @var boolean
     */
    public $useStoringQueryParams;
    /**
     * Key for searching current model in $_SESSION data for loading queryParams of GridView
     * @var
     */
    private $filterModelKey;

    /**
     * Reload beforeRun method for reset or load queryParams from $_SESSION
     * @return bool
     */
    public function beforeRun()
    {
        try {
            if ($this->useStoringQueryParams === false) {
                return parent::beforeRun();
            }

            if (request()->get(self::GET_REQUEST_RESET_FILTER_PARAM)) {
                $this->resetSessionFilter();
            }

            if (empty(request()->queryParams)) {
                $this->loadQueryParamsFromSession();
            }
        } catch (\Throwable $exception) {
            \Yii::error(alert($exception->getMessage(), true));
        }

        return parent::beforeRun();
    }

    /**
     * Reload afterRun method for storing filters
     * @param mixed $result
     * @return mixed
     */
    public function afterRun($result)
    {
        try {
            if ($this->useStoringQueryParams === false) {
                return parent::afterRun($result);
            }

            if (!empty(request()->queryParams) && isset($this->filterModelKey)) {
                $this->saveQueryParamsToSession();
            }
        } catch (\Throwable $exception) {
            \Yii::error($exception->getMessage(), true);
        }

        return parent::afterRun($result);
    }

    /**
     *
     */
    private function saveQueryParamsToSession()
    {
        $_SESSION[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey] = request()->queryParams[$this->filterModelKey];
    }

    /**
     * @throws \yii\base\ExitException
     */
    private function resetSessionFilter()
    {
        if (!empty($_SESSION[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey])) {
            $_SESSION[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey] = null;
        }

        app()->response->redirect([app()->controller->id . '/index'])->send();
        app()->end();
    }

    /**
     * @throws \yii\base\ExitException
     */
    private function loadQueryParamsFromSession()
    {
        if (!empty(session()[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey])) {
            $queryArray[$this->filterModelKey] = session()[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey];
            app()->response->redirect([app()->controller->id . '/index?' . http_build_query($queryArray)])->send();
            app()->end();
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        try {
            if (app()->controller->action->id === 'index') {
                $this->useStoringQueryParams = true;
            } else {
                $this->useStoringQueryParams = false;
            }
            $this->filterModelKey = $this->getFilterModelKey();

            if ($this->useStoringQueryParams) {
                $this->layout = '{resetButton}' . $this->layout;
            }
        } catch (\Throwable $exception) {
            \Yii::error(alert($exception->getMessage(), true));
        }

        parent::init();
    }

    /**
     *
     */
    public function renderResetButton()
    {
        $url = '?' . self::GET_REQUEST_RESET_FILTER_PARAM . '=1';

        echo '<p>' . Html::a(\Yii::t('website', '/backend/reset-filter'), $url,
                ['class' => 'btn btn-primary']) . '</p>';
    }

    /**
     * @inheritdoc
     * Reload renderSection method for add {resetButton}
     */
    public function renderSection($name)
    {
        switch ($name) {
            case '{resetButton}':
                return $this->renderResetButton();
            default:
                return parent::renderSection($name);
        }
    }

    /**
     * @return string|null
     */
    private function getFilterModelKey()
    {
        if (isset($this->filterModel)) {
            $filterModelClassExplode = explode('\\', get_class($this->filterModel));
            return array_pop($filterModelClassExplode);
        }

        return null;
    }
}
