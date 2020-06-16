<?php

/**
 * PHP TableOfContents Library.
 *
 * @license http://opensource.org/licenses/MIT
 *
 * @see https://github.com/caseyamcl/toc
 *
 * @version 2
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 *
 * ------------------------------------------------------------------
 */

declare(strict_types=1);

namespace PiedWeb\CMSBundle\Service\toc;

use ArrayIterator;
use DOMDocument;
use DomElement;
use DOMXPath;

/**
 * Trait that helps with HTML-related operations.
 */
trait HtmlHelper
{
    /**
     * Convert a topLevel and depth to H1..H6 tags array.
     *
     * @return array|string[] Array of header tags; ex: ['h1', 'h2', 'h3']
     */
    protected function determineHeaderTags(int $topLevel, int $depth): array
    {
        $desired = range((int) $topLevel, (int) $topLevel + ((int) $depth - 1));
        $allowed = [1, 2, 3, 4, 5, 6];

        return array_map(function ($val) {
            return 'h'.$val;
        }, array_intersect($desired, $allowed));
    }

    /**
     * Traverse Header Tags in DOM Document.
     *
     * @return ArrayIterator|DomElement[]
     */
    protected function traverseHeaderTags(DOMDocument $domDocument, int $topLevel, int $depth): ArrayIterator
    {
        $xpath = new DOMXPath($domDocument);

        $xpathQuery = sprintf(
            '//*[%s]',
            implode(' or ', array_map(function ($v) {
                return sprintf('local-name() = "%s"', $v);
            }, $this->determineHeaderTags($topLevel, $depth)))
        );

        $nodes = [];
        foreach ($xpath->query($xpathQuery) as $node) {
            $nodes[] = $node;
        }

        return new ArrayIterator($nodes);
    }

    /**
     * Is this a full HTML document.
     *
     * Guesses, based on presence of <body>...</body> tags
     */
    protected function isFullHtmlDocument(string $markup): bool
    {
        return false !== strpos($markup, '<body') && false !== strpos($markup, '</body>');
    }
}
