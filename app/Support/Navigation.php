<?php

namespace App\Support;

/** Site navigation (handoff §Header): order and labels are fixed by the design. */
final class Navigation
{
    /**
     * Primary navigation. `active` lists route patterns that highlight the item.
     *
     * @return list<array{label: string, route: string, active: list<string>}>
     */
    public static function primary(): array
    {
        return [
            ['label' => 'Home', 'route' => 'home', 'active' => ['home']],
            ['label' => 'About Us', 'route' => 'about', 'active' => ['about']],
            ['label' => 'Livestock', 'route' => 'livestock', 'active' => ['livestock']],
            ['label' => 'Poultry', 'route' => 'poultry', 'active' => ['poultry']],
            ['label' => 'Palm Oil', 'route' => 'palm', 'active' => ['palm']],
            ['label' => 'Products', 'route' => 'products.*', 'active' => ['products.*']],
            ['label' => 'Gallery', 'route' => 'gallery', 'active' => ['gallery']],
            ['label' => 'Partners', 'route' => 'partners', 'active' => ['partners']],
            ['label' => 'Contact', 'route' => 'contact', 'active' => ['contact']],
        ];
    }

    /**
     * Footer link columns.
     *
     * @return array<string, list<array{label: string, route: string}>>
     */
    public static function footer(): array
    {
        return [
            'Explore' => [
                ['label' => 'Home', 'route' => 'home'],
                ['label' => 'About', 'route' => 'about'],
                ['label' => 'Livestock', 'route' => 'livestock'],
                ['label' => 'Palm Production', 'route' => 'palm'],
                ['label' => 'Sustainability', 'route' => 'sustainability'],
                ['label' => 'Gallery', 'route' => 'gallery'],
            ],
            'Commercial' => [
                ['label' => 'Products', 'route' => 'products.index'],
                ['label' => 'Request a Quote', 'route' => 'quote'],
                ['label' => 'Partnerships', 'route' => 'partners'],
                ['label' => 'News', 'route' => 'news.index'],
                ['label' => 'Contact', 'route' => 'contact'],
            ],
        ];
    }

    public static function href(string $route): string
    {
        return route(str_replace('.*', '.index', $route));
    }

    /**
     * @param  list<string>  $patterns
     */
    public static function isActive(array $patterns): bool
    {
        return request()->routeIs(...$patterns);
    }
}
