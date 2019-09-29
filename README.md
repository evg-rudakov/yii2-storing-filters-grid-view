Storing Filters GridView
========================
GridView with the ability to store filters to the session

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist evg-rudakov/yii2-storing-query-params-grid-view "*"
```

or add

```
"evg-rudakov/yii2-storing-query-params-grid-view": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= \evgRudakov\StoringFiltersGridView\StoringQueryParamsGridView::widget(); ?>```