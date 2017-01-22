<?php

namespace Polonairs\Dialtime\MasterBundle\Twig;

use Polonairs\Dialtime\MasterBundle\Utils\Markdown;

class MdExtension extends \Twig_Extension
{
    private $parser;

    public function __construct(Markdown $parser)
    {
        $this->parser = $parser;
    }
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'md2html',
                array($this, 'markdownToHtml'),
                array('is_safe' => array('html'))
            ),
        );
    }
    public function markdownToHtml($content)
    {
        return $this->parser->toHtml($content);
    }
    public function getName()
    {
        return 'md_extension';
    }
}