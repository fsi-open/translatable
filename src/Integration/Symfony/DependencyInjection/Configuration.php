<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\Translatable\Integration\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('fsi_translatable');

        /** @var ArrayNodeDefinition $root */
        $root = $treeBuilder->getRootNode();

        /** @var NodeBuilder $rootChildren */
        $rootChildren = $root->children();

        /** @var NodeBuilder $entitiesChildren */
        $entitiesChildren = $rootChildren->arrayNode('entities')
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('class')
            ->arrayPrototype()
            ->children()
        ;

        $entitiesChildren->scalarNode('localeField')->defaultValue('locale')->end();
        $entitiesChildren->booleanNode('disabledAutoTranslationsUpdate')->defaultValue(false)->end();

        $translationNode = $entitiesChildren->arrayNode('translation');

        /** @var NodeBuilder $translationChildren */
        $translationChildren = $translationNode->children();
        $translationChildren->scalarNode('class')->cannotBeEmpty()->end();
        $translationChildren->scalarNode('localeField')->defaultValue('locale')->end();
        $translationChildren->scalarNode('relationField')->cannotBeEmpty()->end();
        $translationChildren->end();

        /** @var ArrayNodeDefinition $fieldsChildrenNode */
        $fieldsChildrenNode = $entitiesChildren->arrayNode('fields')->scalarPrototype()->end();

        $fieldsChildrenNode->end();
        $entitiesChildren->end();
        $rootChildren->end();
        $root->end();

        return $treeBuilder;
    }
}
