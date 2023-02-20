<?php

namespace app\widgets\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\Response;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

use DevGroup\TagDependencyHelper\NamingHelper;

/**
 * Helper action for retrieving tree data for jstree by ajax.
 * Example use in controller:
 *
 * ``` php
 * public function actions()
 * {
 *     return [
 *         'getTree' => [
 *             'class' => AdjacencyFullTreeDataAction::class,
 *             'className' => Category::class,
 *             'modelLabelAttribute' => 'defaultTranslation.name',
 *
 *         ],
 *     ...
 *     ];
 * }
 * ```
 * @property string $className
 * @property string $modelIdAttribute
 * @property string $modelLabelAttribute
 * @property string $modelParentAttribute
 * @property string $varyByTypeAttribute
 * @property string $queryParentAttribute
 * @property string $querySortOrder
 * @property string $querySelectedAttribute
 * @property array $whereCondition
 * @property string $cacheKey
 * @property boolean $cacheActive
 * @property boolean $showGroupType
 * @property integer $cacheLifeTime
 */
class FullTreeDataAction extends Action
{

    public $className = null;

    public $modelIdAttribute = 'id';

    public $modelLabelAttribute = 'name';

    public $modelParentAttribute = 'parent_id';

    public $varyByTypeAttribute = null;

    public $queryParentAttribute = 'id';

    public $querySortOrder = 'sort_order';

    public $querySelectedAttribute = 'selected_id';
    /**
     * Additional conditions for retrieving tree(ie. don't display nodes marked as deleted)
     * @var array
     */
    public $whereCondition = [];

    /**
     * Cache key prefix. Should be unique if you have multiple actions with different $whereCondition
     * @var string
     */
    public $cacheKey = 'FullTree';

    public $cacheActive = true;
    /**
     * Показать или скрыть груповую принадлежность каталога и его состояние в системе
     * @var bool
     */
    public $showGroupType = false;

    /**
     * Cache lifetime for the full tree
     * @var int
     */
    public $cacheLifeTime = 86400;

    public function init()
    {
        if (!isset($this->className)) {
            throw new InvalidConfigException("Model name should be set in controller actions");
        }
        if (!class_exists($this->className)) {
            throw new InvalidConfigException("Model class does not exists");
        }
    }

    public function run()
    {
        header("Content-Type: text/html; charset=utf-8");

        Yii::$app->response->format = Response::FORMAT_JSON;

        $class = $this->className;

        if (null === $current_selected_id = Yii::$app->request->get($this->querySelectedAttribute)) {
            $current_selected_id = Yii::$app->request->get($this->queryParentAttribute);
        }

        $cacheKey = "AdjacencyFullTreeData:{$this->cacheKey}:{$class}:{$this->querySortOrder}";

        if (false === $result = Yii::$app->cache->get($cacheKey)) {
            $query = $class::findByAll()
                ->orderBy([$this->querySortOrder => SORT_ASC]);

            if (count($this->whereCondition) > 0) {
                $query = $query->where($this->whereCondition);
            }

            if (null === $rows = $query->asArray()->all()) {
                return [];
            }

            $result = [];

            foreach ($rows as $row) {
                $parent = ArrayHelper::getValue($row, $this->modelParentAttribute, 0);

                $groupBy = null;
                if($this->showGroupType) {
                    $removeStatus = ArrayHelper::getValue($row, 'status_system') === $class::STATUS_SYSTEM_DELETED ? ' => ' . Yii::t('app', $class::STATUS_SYSTEM_DELETED) : null;
                    $groupBy = !empty($groupBy = ArrayHelper::getValue(ArrayHelper::getValue($row, 'usersGroupRelation'), 'name')) ? ' [' . $groupBy . ']' . $removeStatus : ' [null]';
                    $groupBy = ArrayHelper::getValue($row, 'level', 'item') === '0' ? $groupBy : null;
                }

                // Protection against xss
                $name = ArrayHelper::getValue($row, $this->modelLabelAttribute, 'item');
                if(Html::encode($name) !== $name){ $name = HtmlPurifier::process($name); }

                $item = [
                    'id' => (int)ArrayHelper::getValue($row, $this->modelIdAttribute, 0),
                    'parent' => ($parent) ? (int)$parent : '#',
                    'text' => Html::encode($name),
                    'a_attr' => [
                        'data-id' => (int)$row[$this->modelIdAttribute],
                        'data-parent_id' => (int)$row[$this->modelParentAttribute]
                    ],
                ];

                if (null !== $this->varyByTypeAttribute) {
                    $item['type'] = $row[$this->varyByTypeAttribute];
                }

                $result[$row[$this->modelIdAttribute]] = $item;
            }

            if($this->cacheActive){ // Проверка активации cache
                Yii::$app->cache->set(
                    $cacheKey,
                    $result,
                    $this->cacheLifeTime,
                    new TagDependency([
                        'tags' => [
                            NamingHelper::getCommonTag($class),
                        ],
                    ])
                );
            }

        }

        if (array_key_exists($current_selected_id, $result)) {
            $result[$current_selected_id] = array_merge(
                $result[$current_selected_id],
                ['state' => ['opened' => true, 'selected' => true]]
            );
        }

        return array_values($result);
    }
}
