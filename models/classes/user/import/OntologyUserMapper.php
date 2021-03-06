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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 */

namespace oat\tao\model\user\import;

use oat\generis\model\OntologyAwareTrait;
use oat\generis\model\user\UserRdf;
use oat\oatbox\service\ConfigurableService;
use tao_models_classes_LanguageService;
use oat\generis\model\user\PasswordConstraintsException;

class OntologyUserMapper extends ConfigurableService implements UserMapper
{
    use OntologyAwareTrait;

    /** @var array */
    protected $userMapped = [];

    /** @var string */
    protected $plainPassword;

    /**
     * @param array $data
     * @return $this|UserMapper
     * @throws MandatoryFieldException
     * @throws PasswordConstraintsException
     */
    public function map(array $data = [])
    {
        $schema = $this->getOption(static::OPTION_SCHEMA);
        $mandatoryFields = isset($schema[static::OPTION_SCHEMA_MANDATORY]) ? $schema[static::OPTION_SCHEMA_MANDATORY] : [];

        foreach ($mandatoryFields as $key => $propertyKey) {
            if (!isset($data[$key])) {
                throw new MandatoryFieldException('Mandatory field "' . $key . '" should exists.');
            }
            if (empty($data[$key])) {
                throw new MandatoryFieldException('Mandatory field "' . $key . '" should not be empty.');
            }

            if ($propertyKey === UserRdf::PROPERTY_PASSWORD) {
                $this->plainPassword = $data[$key];
            }

            $this->userMapped[$propertyKey] = $this->formatValue($propertyKey, $data[$key]);
        }

        $optionalFields = isset($schema[static::OPTION_SCHEMA_OPTIONAL]) ? $schema[static::OPTION_SCHEMA_OPTIONAL] : [];

        foreach ($optionalFields as $key => $propertyKey) {
            if (!isset($data[$key]) || empty($data[$key])) {
                continue;
            }

            $this->userMapped[$propertyKey] = $this->formatValue($propertyKey, $data[$key]);
        }

        return $this;
    }

    /**
     * @param array $extraProperties
     * @return array|mixed
     */
    public function combine(array $extraProperties)
    {
        $this->userMapped = array_merge($this->userMapped, $extraProperties);

        return $this;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->userMapped);
    }

    /**
     * @return string|null
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->userMapped;
    }

    /**
     * @param string $key
     * @param string $value
     * @return string
     * @throws \oat\generis\model\user\PasswordConstraintsException
     */
    protected function formatValue($key, $value)
    {
        switch ($key) {
            case UserRdf::PROPERTY_PASSWORD:
                return $this->getPasswordHashService()->encrypt($value);
            case UserRdf::PROPERTY_UILG:
            case UserRdf::PROPERTY_DEFLG:
                $val = $this->getLanguageService()->getLanguageByCode($value);
                return $val === null ? $value : $val->getUri();
            default:
                return $value;
        }
    }

    /**
     * @return \helpers_PasswordHash
     */
    protected function getPasswordHashService()
    {
        return \core_kernel_users_Service::getPasswordHash();
    }

    /**
     * @return tao_models_classes_LanguageService
     */
    protected function getLanguageService()
    {
        return tao_models_classes_LanguageService::singleton();
    }
}