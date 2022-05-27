<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace Pimcore\Bundle\DataHubBundle\GraphQL\DocumentElementType;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Pimcore\Bundle\DataHubBundle\GraphQL\DocumentType\DocumentElementType;
use Pimcore\Bundle\DataHubBundle\GraphQL\Service;
use Pimcore\Bundle\DataHubBundle\GraphQL\Traits\ServiceTrait;
use Pimcore\Model\Document\Editable;
use Pimcore\Model\Document\Editable\Areablock;

class BrickDataType extends ObjectType
{
    use ServiceTrait;

    /**
     * @param Service $graphQlService
     */
    public function __construct(Service $graphQlService, DocumentElementType $documentElementType)
    {
        $this->graphQlService = $graphQlService;

        $config =
            [
                'name' => 'document_editableAreablock_brick',
                'fields' => [
                    '_editableType' => [
                        'type' => Type::string(),
                        'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                            if ($value) {
                                return $value->getType();
                            }
                        }
                    ],
                    '_editableName' => [
                        'type' => Type::string(),
                        'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                            if ($value) {
                                return $value->getName();
                            }
                        }
                    ],
                    'key' => [
                        'type' => Type::string(),
                        'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                            if (is_array($value)) {
                                return $value['key'];
                            }

                            return null;
                        }
                    ],
                    'type' => [
                        'type' => Type::string(),
                        'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                            if (is_array($value)) {
                                return $value['type'];
                            }

                            return null;
                        }
                    ],
                    'hidden' => [
                        'type' => Type::boolean(),
                        'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                            if (is_array($value)) {
                                return $value['hidden'];
                            }
                        }
                    ],
                    'elements' => [
                        'type' => Type::listOf($documentElementType),
                        'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                            if (is_array($value) && key_exists('areablock', $value) && $value['areablock'] instanceof Areablock) {
                                $areablock = $value['areablock'];
                                $editables = $areablock->getDocument()->getEditables();
                                $key = $value['key'];
                                $areaBlockEditables = array_filter($editables, fn(Editable $e) => strpos($e->getName(), "{$areablock->getName()}:$key") !== false);

                                return array_values($areaBlockEditables);
                            }
                            return [];
                        }
                    ],
                ],
            ];
        parent::__construct($config);
    }
}
