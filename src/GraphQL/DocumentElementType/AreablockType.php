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
use Pimcore\Model\Document\Editable;
use Pimcore\Model\Document\Editable\Areablock;

class AreablockType extends ObjectType
{
    protected static $instance;

    public function __construct(array $config)
    {
        parent::__construct($config);
    }

    public static function getInstance(AreablockDataType $areablockDataType, BrickDataType $documentBrickType)
    {
        if (!self::$instance) {
            $config =
                [
                    'name' => 'document_editableAreablock',
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
                        'data' => [
                            'type' => Type::listOf($areablockDataType),

                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                                if ($value instanceof Areablock) {
                                    return $value->getData();
                                }
                            }
                        ],
                        'bricks' => [
                            'args' => ['brickType' => ['type' => Type::nonNull(Type::string()), 'description' => 'Type of the brick']],
                            'type' => Type::listOf($documentBrickType),
                            'resolve' => static function ($value = null, $args = [], $context = [], ResolveInfo $resolveInfo = null) {
                                if ($value instanceof Areablock) {
                                    $data = $value->getData();
                                    return array_values(
                                        array_filter(
                                            array_map(fn(array $brickData) => array_merge($brickData, ['areablock' => $value]), $data),
                                            fn (array $brickData) => $brickData['type'] === $args['brickType']
                                        )
                                    );
                                }
                                return null;
                            }
                        ],
                    ],
                ];
            self::$instance = new static($config);
        }

        return self::$instance;
    }
}
