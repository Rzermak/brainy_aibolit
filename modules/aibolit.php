<?php
/**
 * AiBolit - module for brainycp.
 * Using the aibolit scanner code https://revisium.com/ai/
 *
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author      rzermak <rzermak@yandex.ru>
 * @link		https://github.com/Rzermak/brainy_aibolit
 * @version		1.0
 */

require_once('/etc/brainy/modules/aibolit/load.module.php');
AiBolitController::getInstance()->init($smarty, $tpl);