<?php

namespace EvgRudakov\StoringQueryParamsGridView;

use yii\grid\GridView;
use yii\helpers\Html;


/**
 * Class StoringQueryParamsGridView
 * @package StoringQueryParamsGridView
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
    * @throws \yii\base\ExitException
     * @return mixed
     */

    public $mainAction = 'index';
    public function beforeRun()
    {
            if ($this->useStoringQueryParams === false) {
                return parent::beforeRun();
            }

            if (\Yii::$app->request->get(self::GET_REQUEST_RESET_FILTER_PARAM)) {
                $this->resetSessionFilter();
            }

            if (empty(\Yii::$app->request->queryParams)) {
                $this->loadQueryParamsFromSession();
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
        if ($this->useStoringQueryParams === false) {
            return parent::afterRun($result);
        }

        if (!empty(\Yii::$app->request->queryParams) && isset($this->filterModelKey)) {
            $this->saveQueryParamsToSession();
        }

        return parent::afterRun($result);
    }

    /**
     *
     */
    private function saveQueryParamsToSession()
    {
        $_SESSION[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey] = \Yii::$app->request->queryParams[$this->filterModelKey];
    }

    /**
     * @throws \yii\base\ExitException
     */
    private function resetSessionFilter()
    {
        if (!empty($_SESSION[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey])) {
            $_SESSION[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey] = null;
        }

        \Yii::$app->response->redirect([\Yii::$app->controller->id . '/' . $this->mainAction])->send();
        \Yii::$app->end();
    }

    /**
     * @throws \yii\base\ExitException
     */
    private function loadQueryParamsFromSession()
    {
        if (!empty(\Yii::$app->session[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey])) {
            $queryArray[$this->filterModelKey] = \Yii::$app->session[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey];
            \Yii::$app->response->redirect([\Yii::$app->controller->id . '/' . $this->mainAction . http_build_query($queryArray)])->send();
            \Yii::$app->end();
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        if (\Yii::$app->controller->action->id === $this->mainAction) {
            $this->useStoringQueryParams = true;
        } else {
            $this->useStoringQueryParams = false;
        }
        $this->filterModelKey = $this->getFilterModelKey();

        if ($this->useStoringQueryParams) {
            $this->layout = '{resetButton}' . $this->layout;
        }

        parent::init();
    }

    /**
     *
     */
    public function renderResetButton()
    {
        $url = '?' . self::GET_REQUEST_RESET_FILTER_PARAM . '=1';

        echo '<p>' . Html::a('Reset filters', $url,
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
