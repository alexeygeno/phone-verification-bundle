<?php
namespace AlexGeno\PhoneVerificationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('alex_geno_phone_verification');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('storage')
                    ->children()
                        ->scalarNode('driver')->end()
                        ->arrayNode('redis')
                            ->children()
                                ->scalarNode('connection')->end()
                                ->arrayNode('settings')
                                    ->children()
                                        ->scalarNode('prefix')->end()
                                        ->scalarNode('session_key')->end()
                                        ->scalarNode('session_counter_key')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('mongodb')
                            ->children()
                                ->scalarNode('connection')->end()
                                ->arrayNode('settings')
                                    ->children()
                                        ->scalarNode('collection_session')->end()
                                        ->scalarNode('collection_session_counter')->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sender')
                    ->children()
                    ->scalarNode('transport')->end()
                    ->end()
                ->end()
                ->arrayNode('manager')
                    ->children()
                    ->arrayNode('otp')
                        ->children()
                            ->scalarNode('length')->end()
                        ->end()
                    ->end()
                    ->arrayNode('rate_limits')
                        ->children()
                            ->arrayNode('initiate')
                                ->children()
                                    ->scalarNode('period_secs')->end()
                                    ->scalarNode('count')->end()
                                ->end()
                            ->end()
                            ->arrayNode('complete')
                                ->children()
                                    ->scalarNode('period_secs')->end()
                                    ->scalarNode('count')->end()
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