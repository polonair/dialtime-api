<?php

namespace Polonairs\Dialtime\WebBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Polonairs\Dialtime\WebBundle\DependencyInjection\WebExtension;

class WebBundle extends Bundle
{
	public function getContainerExtension()
	{
		return new WebExtension();
	}
}
