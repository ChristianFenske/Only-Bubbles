<?php declare(strict_types=1);

namespace OnlyBubblesTheme;

use Shopware\Core\Framework\Plugin;
use Shopware\Storefront\Framework\ThemeInterface;

/**
 * Only Bubbles — Sales-Channel-Theme.
 *
 * Reines Theme-Plugin: erbt die komplette Storefront- (und ggf. Eltern-Theme-)
 * Struktur und legt nur die Markenschicht (Farben, Schriften, Logo) darüber.
 * Die Produkte werden NICHT hier zugewiesen, sondern im Admin dem
 * Verkaufskanal "Only Bubbles" hinzugefügt.
 */
class OnlyBubblesTheme extends Plugin implements ThemeInterface
{
}
