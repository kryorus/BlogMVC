<?php

/**
 * This class holds all functionality regarding the service en masse: it
 * provides service-related information and (currently) serves as an
 * autoloader for Yii-incompatible classes.
 *
 * @version    Release: 0.1.0
 * @since      0.1.0
 * @package    BlogMVC
 * @subpackage Yii
 * @author     Fike Etki <etki@etki.name>
 */
class ApplicationService
{
    /**
     * Standard initialization method.
     * 
     * @return void
     * @since 0.1.0
     */
    public function init()
    {
    }
    /**
     * Returns basic service info, such as uptime, Yii version, etc. Uses
     * cache to store that information for a minute.
     * 
     * @return string[] Server and software information in :title => :value
     * format.
     * @since 0.1.0
     */
    public function getServiceInfo()
    {
        $cache = \Yii::app()->cacheHelper->get('serviceStatus');
        if (!$cache) {
            $cache = array(
                'yiiVersion'  => \Yii::getVersion(),
                'twigVersion' => $this->getTwigVersion(),
                'phpVersion'  => PHP_VERSION,
                'os'          => php_uname(),
                'uptime'      => $this->getUptime(),
                'serverTime'  => date('Y-m-d H:i'),
            );
            \Yii::app()->cacheHelper->set('serviceStatus', $cache, 60);
        }
        foreach ($cache as $key => $value) {
            unset($cache[$key]);
            $cache[\Yii::t('templates', 'status.'.$key)] = $value;
        }
        return $cache;
    }
    /**
     * Returns server uptime (if /proc/uptime can be found and read).
     * 
     * @return string 'Days:hours:minutes' or 'unknown' on failure.
     * @since 0.1.0
     */
    protected function getUptime()
    {
        if (!file_exists('/proc/uptime') || !is_readable('/proc/uptime')) {
            return 'unknown';
        }
        $data = file_get_contents('/proc/uptime');
        if (!$data) {
            return 'unknown';
        }
        $seconds = (int) $data;
        return sprintf(
            '%dd:%dh:%dm',
            floor($seconds/86400),
            $seconds/3600 % 24,
            $seconds/60 % 60
        );
    }
    /**
     * Returns twig version.
     * 
     * @return string Actual or supposed Twig version.
     * @since 0.1.0
     */
    protected function getTwigVersion()
    {
        $fallback = 'unknown (presumably 1.15)';
        $alias = 'application.vendor.twig.twig.composer';
        $filePath = \Yii::getPathOfAlias($alias).'.json';
        if (!file_exists($filePath)) {
            return $fallback;
        }
        $json = file_get_contents($filePath);
        if (!$json) {
            return $fallback;
        }
        $data = \CJSON::decode($json, true);
        foreach (array('extra', 'branch-alias') as $key) {
            if (!isset($data[$key])) {
                return $fallback;
            }
            $data = reset($data[$key]);
        }
        return $data;
    }
}
