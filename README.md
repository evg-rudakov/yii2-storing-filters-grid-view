Storing Filters GridView
========================
Widget which extends by **yii\grid\GridView**. This widget can store selected filters(**queryParams**) in a GridView that will
not be lost when you return from another page. 
QueryParams are stored in **\Yii::$app->session**.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require "evg-rudakov/yii2-storing-query-params-grid-view": "^0.1"
```

or add

```
"evg-rudakov/yii2-storing-query-params-grid-view": "^0.1"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \EvgRudakov\StoringQueryParamsGridView\StoringQueryParamsGridView::widget([
        'linkContainer' => [
            'tag' => 'p',
            'options' => ['class' => 'hello'],
        ],
        'link' => [
            'text' => 'Reset',
            'options' => ['class' => 'btn btn-success']
        ],
        'renderResetLink' => true,
        'storingQueryParams' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'name',
            'description:ntext',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
```


