<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\base\Exception;
use yii\web\AssetBundle as BaseAdminLteAsset;
use igor162\adminlte\ColorCSS;

/**
 * Class AppAsset
 * @package app\assets
 */
class AppAsset extends BaseAdminLteAsset
{
//    public $basePath = '@webroot';
//    public $baseUrl = '@web';
//    public $sourcePath = '@vendor/almasaeed2010/adminlte';
    public $sourcePath = '@vendor/igor162/AdminLteStyle/dist';

    public $css = [
        'css/AdminLTE.min.css',
//        'plugins/datatables/dataTables.bootstrap.css',
        '/css/site.css',
    ];
    public $js = [
        'js/app.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
        'rmrevin\yii\fontawesome\AssetBundle',
        'kartik\icons\FlagIconAsset',   // Стили flag-icon
//        'rmrevin\yii\fontawesome\AssetBundle',         // новые стили framework
    ];

    /**
     * @var string|bool Choose skin color, eg. `'skin-blue'` or set `false` to disable skin loading
     * @see https://almsaeedstudio.com/themes/AdminLTE/documentation/index.html#layout
     */
//    public $skin = '_all-skins';
    public $skin = ColorCSS::SKIN_GREEN_LIGHT;

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Append skin color file if specified
        if ($this->skin) {
            if (('_all-skins' !== $this->skin) && (strpos($this->skin, 'skin-') !== 0)) {
                throw new Exception('Invalid skin specified');
            }

            $this->css[] = sprintf('css/skins/%s.min.css', $this->skin);
        }

        parent::init();
    }
}
