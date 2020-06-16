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

use Cocur\Slugify\Slugify;

/**
 * UniqueSluggifier creates slugs from text without repeating the same slug twice per instance.
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class UniqueSluggifier
{
    /**
     * @var Slugify
     */
    private $slugify;

    /**
     * @var array
     */
    private $used;

    /**
     * Constructor.
     *
     * @param Slugify $slugify
     */
    public function __construct(Slugify $slugify = null)
    {
        $this->used = [];
        $this->slugify = $slugify ?: new Slugify();
    }

    /**
     * Slugify.
     */
    public function slugify(string $text): string
    {
        $slugged = $this->slugify->slugify($text);

        $count = 1;
        $orig = $slugged;
        while (in_array($slugged, $this->used)) {
            $slugged = $orig.'-'.$count;
            ++$count;
        }

        $this->used[] = $slugged;

        return $slugged;
    }
}
