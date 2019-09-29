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
     * Is need to user storing queryParams to $_SESSION
     * @var bool
     */
    public $storingQueryParams = true;
    /**
     * Key for searching current model in $_SESSION data for loading queryParams of GridView
     * @var
     */
    private $filterModelKey;

    /**
     * Is need to render reset link
     * @var bool
     */
    public $renderResetLink = true;

    /**
     * Action where there is table
     * @var string
     */
    public $mainAction = 'index';

    /**
     * Array of options to custom link for reset query params
     * @var array
     */
    public $link = [
        'text' => 'Reset Filters',
        'options' => ['class' => 'btn btn-primary'],
    ];

    /**
     * Array of  options  to custom link container for reset query params link
     * @var array
     */

    public $linkContainer = [
        'tag' => 'p',
        'options' => [],
    ];

    /**
     * Reload beforeRun method for reset or load queryParams from $_SESSION
     * @throws \yii\base\ExitException
     * @return mixed
     */
    public function beforeRun()
    {
        if ($this->storingQueryParams === false) {
            return parent::beforeRun();
        }

        if (\Yii::$app->request->get(self::GET_REQUEST_RESET_FILTER_PARAM)) {
            $this->resetSessionQueryParams();
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
        if ($this->storingQueryParams === false) {
            return parent::afterRun($result);
        }

        if (!empty(\Yii::$app->request->queryParams) && isset($this->filterModelKey)) {
            $this->storeQueryParamsToSession();
        }

        return parent::afterRun($result);
    }

    /**
     * Method for storing queryParams to session
     */
    private function storeQueryParamsToSession()
    {
        $_SESSION[self::SESSION_KEY_QUERY_PARAMS][$this->filterModelKey] = \Yii::$app->request->queryParams[$this->filterModelKey];
    }

    /**
     *
     * @throws \yii\base\ExitException
     */
    private function resetSessionQueryParams()
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
            \Yii::$app->response->redirect([\Yii::$app->controller->id . '/' . $this->mainAction . '?' . http_build_query($queryArray)])->send();
            \Yii::$app->end();
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        if (\Yii::$app->controller->action->id === $this->mainAction) {
            $this->storingQueryParams = true;
        } else {
            $this->storingQueryParams = false;
        }
        $this->filterModelKey = $this->getFilterModelKey();

        if ($this->storingQueryParams && $this->renderResetLink) {
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

        echo Html::tag(
            $this->linkContainer['tag'],
            Html::a(
                $this->link['text'],
                $url,
                $this->link['options']
            ),
            $this->linkContainer['options']
        );
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
