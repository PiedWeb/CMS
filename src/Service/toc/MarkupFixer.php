<?php

// Todo, manage it via composer when sonata admin is upgraded to v4 (knp-menu is blocking)

/*
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

use Masterminds\HTML5;
use RuntimeException;

/**
 * TOC Markup Fixer adds `id` attributes to all H1...H6 tags where they do not
 * already exist.
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class MarkupFixer
{
    use HtmlHelper;

    /**
     * @var HTML5
     */
    private $htmlParser;

    /**
     * Constructor.
     *
     * @param HTML5 $htmlParser
     */
    public function __construct(HTML5 $htmlParser = null)
    {
        $this->htmlParser = $htmlParser ?: new HTML5();
    }

    /**
     * Fix markup.
     *
     * @return string Markup with added IDs
     *
     * @throws RuntimeException
     */
    public function fix(string $markup, int $topLevel = 1, int $depth = 6): string
    {
        if (!$this->isFullHtmlDocument($markup)) {
            $partialID = uniqid('toc_generator_');
            $markup = sprintf("<body id='%s'>%s</body>", $partialID, $markup);
        }

        $domDocument = $this->htmlParser->loadHTML($markup);
        $domDocument->preserveWhiteSpace = true; // do not clobber whitespace

        $sluggifier = new UniqueSluggifier();

        /** @var \DOMElement $node */
        foreach ($this->traverseHeaderTags($domDocument, $topLevel, $depth) as $node) {
            if ($node->getAttribute('id')) {
                continue;
            }

            $node->setAttribute('id', $sluggifier->slugify($node->getAttribute('title') ?: $node->textContent));
        }

        return $this->htmlParser->saveHTML(
            (isset($partialID)) ? $domDocument->getElementById($partialID)->childNodes : $domDocument
        );
    }
}
