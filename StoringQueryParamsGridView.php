<?php

namespace EvgRudakov\StoringQueryParamsGridView;

use yii\grid\GridView;
use yii\helpers\Html;


/**
 * Class StoringQueryParamsGridView extends yii\grid\GridView has ability to saved queryParams to session
 *
 * @example
 * ```
 * <?= \EvgRudakov\StoringQueryParamsGridView\StoringQueryParamsGridView::widget([
 *  'linkContainer' => [
 *      'tag' => 'p',
 *      'options' => ['class' => 'hello'],
 *  ],
 *  'link' => [
 *      'text' => 'Reset',
 *      'options' => ['class' => 'btn btn-success']
 *  ],
 *  'renderResetLink' => true,
 *  'storingQueryParams' => true,
 *  'dataProvider' => $dataProvider,
 *  'filterModel' => $searchModel,
 *  'columns' => [
 *      'id',
 *      'name',
 *      ...
 *  ],
 *  ]); ?>
 *  ```
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
     * Is need to user storing queryParams to \Yii::$app->session
     * @var bool
     */
    public $storingQueryParams = true;
    /**
     * Key for searching current model in \Yii::$app->session data for loading queryParams of GridView
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
     * Reload beforeRun method for reset or load queryParams from \Yii::$app->session
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
        \Yii::$app->session->set(self::SESSION_KEY_QUERY_PARAMS, [$this->filterModelKey => \Yii::$app->request->queryParams[$this->filterModelKey]]);
    }

    /**
     *
     * @throws \yii\base\ExitException
     */
    private function resetSessionQueryParams()
    {
        if (!empty(\Yii::$app->session->get(self::SESSION_KEY_QUERY_PARAMS)[$this->filterModelKey])) {
            \Yii::$app->session->set(self::SESSION_KEY_QUERY_PARAMS, [$this->filterModelKey => null]);
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
            $this->layout = '{resetLink}' . $this->layout;
        }

        parent::init();
    }

    /**
     * Method for render reset
     */
    public function renderResetLinkSection()
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
            case '{resetLink}':
                return $this->renderResetLinkSection();
            default:
                return parent::renderSection($name);
        }
    }

    /**
     * QueryParams stores in \Yii::$app->session for each filterModel, this method returns the key that will be used to store queryParams
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
