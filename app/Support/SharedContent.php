<?php

namespace App\Support;

/** Copy blocks that appear on more than one page (taken verbatim from the design handoff). */
final class SharedContent
{
    /**
     * @return list<array{0: string, 1: string}>
     */
    public static function livestockLifecycle(): array
    {
        return [
            ['Breeding', 'Selected stock, recorded lineage.'],
            ['Rearing', 'Housing and stocking density control.'],
            ['Feeding', 'Ration planning by growth stage.'],
            ['Health Monitoring', 'Vaccination and veterinary review.'],
            ['Growth', 'Weight tracking to target.'],
            ['Market', 'Scheduled supply to buyers.'],
        ];
    }

    /**
     * Oil palm production chain: [title, description, accent] where accent is green (field), sky (mill) or navy (buyers).
     *
     * @return list<array{0: string, 1: string, 2: string}>
     */
    public static function palmChain(): array
    {
        return [
            ['Plantation', 'Nursery, seedlings, establishment', 'green'],
            ['Harvest', 'Cut at bunch maturity', 'green'],
            ['Fresh Fruit Bunches', 'Collected and transported same day', 'green'],
            ['Processing', 'Sterilising, threshing, pressing', 'sky'],
            ['Palm Oil', 'Clarified and stored', 'sky'],
            ['Packaging', 'Bulk and retail formats', 'sky'],
            ['Distribution', 'Delivered to buyers', 'navy'],
        ];
    }
}
