<?php

namespace AlexGeno\PhoneVerificationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('alex_geno_phone_verification');

        $treeBuilder->getRootNode() /* @phpstan-ignore-line */
            ->children()
                ->booleanNode('enabled')->defaultTrue()->end()
                ->arrayNode('storage')->isRequired()
                    ->children()
                        ->enumNode('driver')->isRequired()->cannotBeEmpty()
                            ->values(['redis', 'mongodb'])
                        ->end()
                        ->arrayNode('redis')
                            ->children()
                                ->scalarNode('connection')->isRequired()->cannotBeEmpty()->end()
                                ->arrayNode('settings')
                                    ->ignoreExtraKeys()
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('prefix')->defaultValue('pv:1')->end()
                                        ->scalarNode('session_key')->defaultValue('session')->end()
                                        ->scalarNode('session_counter_key')->defaultValue('session_counter')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('mongodb')
                            ->children()
                                ->scalarNode('connection')->isRequired()->cannotBeEmpty()->end()
                                ->arrayNode('settings')
                                    ->ignoreExtraKeys()
                                    ->addDefaultsIfNotSet()
                                    ->children()
                                        ->scalarNode('collection_session')->defaultValue('session')->end()
                                        ->scalarNode('collection_session_counter')->defaultValue('session_counter')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sender')->isRequired()
                    ->children()
                    ->scalarNode('transport')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
                ->arrayNode('manager')->isRequired()
                    ->children()
                    ->arrayNode('otp')->isRequired()
                        ->children()
                            ->scalarNode('length')->isRequired()->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                    ->arrayNode('rate_limits')->isRequired()
                        ->children()
                            ->arrayNode('initiate')->isRequired()
                                ->children()
                                    ->scalarNode('period_secs')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('count')->isRequired()->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                            ->arrayNode('complete')->isRequired()
                                ->children()
                                    ->scalarNode('period_secs')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('count')->isRequired()->cannotBeEmpty()->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()

            ->end()
        ;

        return $treeBuilder;
    }
}
