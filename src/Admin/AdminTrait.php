<?php

namespace PiedWeb\CMSBundle\Admin;

trait AdminTrait
{
    abstract public function setListMode($mode);

    /**
     * Must be a cookie to check before to do that
     * If you click one time to list, stay in liste mode.
     * Yes it's in the session
     * TODO.
     * */
    protected function setMosaicDefaultListMode(): self
    {
        if (null !== $this->request) {
            if ($mode = $this->request->query->get('_list_mode')) {
                $this->setListMode($mode);
            } else {
                $this->setListMode('mosaic');
            }
        }

        return $this;
    }
}
