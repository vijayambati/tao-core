<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\theme;

use oat\oatbox\Configurable;
use oat\tao\helpers\Template;
use Jig\Utils\StringUtils;

/**
 * Class ConfigurablePlatformTheme
 *
 * Class to easily configure a theme
 * To use it, declare into tao/theming.conf themes:
 *
 * return new oat\tao\model\theme\ThemeService(array(
 * 'available' => array(
 *    [...],
 *    'test' => new \oat\tao\model\theme\ConfigurableTheme(),
 *    'testConfigured' => new \oat\tao\model\theme\ConfigurableTheme(array(
 *       'data' => array(
 *          'logo-url' => 'http://lorempixel.com/400/200,
 *          'link' => 'http://taotesting.com',
 *          'message' => 'Tao Platform',
 *          'label' => 'Default Theme',
 *          'extensionId' => 'taoMyExtension'
 *        ),
 *        'stylesheet' => 'http://tao.dev/tao/views/css/tao-3.css'
 *     )
 *  )
 *  [...]
 *
 * @package oat\tao\model\theme
 */
class ConfigurablePlatformTheme extends Configurable implements Theme
{
    const THEME_DATA = 'data';
    const THEME_CSS = 'stylesheet';

    const THEME_DATA_LOGO_URL = 'logo-url';
    const THEME_DATA_LINK     = 'link';
    const THEME_DATA_MESSAGE  = 'message';
    const THEME_DATA_LABEL    = 'label';
    const THEME_DATA_ID       = 'id';


    private $label;

    private $id;

    /**
     * Usually the extension ID of the class calling this on
     */
    const THEME_DATA_PREFIX   = 'prefix';


    public function __construct($options=[]) {

        parent::__construct($options);

        $this->setLabel();
        $this->setId();
    }

    /**
     * Get a template associated to given $id
     *
     * @param string $id
     * @param string $context
     * @return null|string
     */
    public function getTemplate($id, $context = Theme::CONTEXT_BACKOFFICE)
    {
        switch ($id) {
            case 'header-logo' :
                $template = Template::getTemplate('blocks/header-logo.tpl', 'tao');
                break;
            case 'footer' :
                $template = Template::getTemplate('blocks/footer.tpl', 'tao');
                break;
            case 'login-message' :
                $template = Template::getTemplate('blocks/login-message.tpl', 'tao');
                break;
            default:
                \common_Logger::w('Unknown template '.$id);
                $template = null;
        }
        return $template;
    }

    /**
     * Get options under data key
     * Options to configure header & footer template
     *
     * @return array
     */
    public function getThemeData()
    {
        if ($this->hasOption(self::THEME_DATA) && is_array($this->getOption(self::THEME_DATA))) {
            return $this->getOption(self::THEME_DATA);
        } else {
            return [];
        }
    }

    /**
     * Get the url of stylesheet associated to current theme configuration
     *
     * @param string $context
     * @return string
     */
    public function getStylesheet($context = Theme::CONTEXT_BACKOFFICE)
    {
        if ($this->hasOption(self::THEME_CSS)) {
            return $this->getOption(self::THEME_CSS);
        } else {
            return Template::css('tao-3.css', 'tao');
        }
    }

    /**
     * Get the logo url of current theme
     * Logo url is used into header
     *
     * @return string
     */
    public function getLogoUrl()
    {
        $data = $this->getThemeData();
        if (isset($data[self::THEME_DATA_LOGO_URL])) {
            return $data[self::THEME_DATA_LOGO_URL];
        } else {
            return Template::img('tao-logo.png', 'tao');
        }
    }

    /**
     * Get the url link of current theme
     * Url is used into header, to provide link to logo
     * Url is used into footer, to provide link to footer message
     *
     * @return string
     */
    public function getLink()
    {
        $data = $this->getThemeData();
        if (isset($data[self::THEME_DATA_LINK])) {
            return $data[self::THEME_DATA_LINK];
        } else {
            return 'http://taotesting.com';
        }
    }

    /**
     * Get the message of current theme
     * Message is used in the header as title of the logo
     * Message is used in the footer as footer message
     *
     * @return string
     */
    public function getMessage()
    {
        $data = $this->getThemeData();
        if (isset($data[self::THEME_DATA_MESSAGE])) {
            return $data[self::THEME_DATA_MESSAGE];
        } else {
            return '';
        }
    }

    /**
     * Get the label of current theme
     * Labels are useful in situations where you can choose between multiple themes
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the id of current theme
     * IDs are used to register the theme
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set the theme label
     *
     * @throws \Exception
     */
    protected function setLabel() {
        if(!$this->hasOption(self::THEME_DATA_LABEL)) {
            throw new \Exception('Missing option "' . self::THEME_DATA_LABEL . '"');
        }
        $this->label = $this->getOption(self::THEME_DATA_LABEL);
    }

    /**
     * set the theme id
     *
     * @throws \Exception
     */
    protected function setId() {
        if(!$this->hasOption(self::THEME_DATA_PREFIX)) {
            throw new \Exception('Missing option "' . self::THEME_DATA_PREFIX . '"');
        }
        $oldLocale = setlocale(LC_ALL, '0');
        setlocale(LC_ALL, 'en_US.UTF-8');
        $id = iconv('UTF-8', 'ASCII//TRANSLIT', $this->getLabel());
        $id = preg_replace("~[^\w/|+ -]~", '', trim(strtolower($id)));
        $id = preg_replace("~[\W]+~", ' ', $id);
        $id = str_replace(' ', '', ucwords($id));
        $this->id = $this->getOption(self::THEME_DATA_PREFIX) . ucfirst($id);
        setlocale(LC_ALL, $oldLocale);
    }
}
